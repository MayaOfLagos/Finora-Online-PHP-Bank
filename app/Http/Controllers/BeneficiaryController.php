<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Beneficiary;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class BeneficiaryController extends Controller
{
    /**
     * Display a listing of user's beneficiaries.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $beneficiaries = Beneficiary::query()
            ->where('user_id', $user->id)
            ->with([
                'beneficiaryUser:id,name,email',
                'beneficiaryAccount:id,uuid,account_number,account_type_id,currency',
                'beneficiaryAccount.accountType:id,name',
            ])
            ->orderBy('is_favorite', 'desc')
            ->orderBy('last_used_at', 'desc')
            ->get()
            ->map(function ($beneficiary) {
                return [
                    'uuid' => $beneficiary->uuid,
                    'nickname' => $beneficiary->nickname,
                    'is_verified' => $beneficiary->is_verified,
                    'is_favorite' => $beneficiary->is_favorite,
                    'transfer_limit' => $beneficiary->transfer_limit,
                    'last_used_at' => $beneficiary->last_used_at?->format('Y-m-d H:i:s'),
                    'last_used_at_human' => $beneficiary->last_used_at?->diffForHumans(),
                    'created_at' => $beneficiary->created_at->format('Y-m-d'),
                    'beneficiary_user' => $beneficiary->beneficiaryUser ? [
                        'name' => $beneficiary->beneficiaryUser->name,
                        'email' => $beneficiary->beneficiaryUser->email,
                    ] : null,
                    'beneficiary_account' => $beneficiary->beneficiaryAccount ? [
                        'uuid' => $beneficiary->beneficiaryAccount->uuid,
                        'account_number' => $beneficiary->beneficiaryAccount->account_number,
                        'account_name' => $beneficiary->beneficiaryAccount->accountType?->name ?? 'Account',
                        'currency' => $beneficiary->beneficiaryAccount->currency,
                        'account_type' => $beneficiary->beneficiaryAccount->accountType?->name,
                    ] : null,
                ];
            });

        // Stats
        $stats = [
            'total_beneficiaries' => $beneficiaries->count(),
            'verified_beneficiaries' => $beneficiaries->where('is_verified', true)->count(),
            'favorite_beneficiaries' => $beneficiaries->where('is_favorite', true)->count(),
        ];

        return Inertia::render('Beneficiaries/Index', [
            'beneficiaries' => $beneficiaries,
            'stats' => $stats,
        ]);
    }

    /**
     * Store a new beneficiary.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'account_number' => ['required', 'string', 'exists:bank_accounts,account_number'],
            'nickname' => ['nullable', 'string', 'max:100'],
            'pin' => ['required', 'string'],
        ]);

        $user = $request->user();

        // Verify PIN
        if (! Hash::check($validated['pin'], $user->transaction_pin)) {
            return back()->withErrors(['pin' => 'Invalid transaction PIN.']);
        }

        // Find the beneficiary account
        $beneficiaryAccount = BankAccount::where('account_number', $validated['account_number'])->first();

        if (! $beneficiaryAccount) {
            return back()->withErrors(['account_number' => 'Account not found.']);
        }

        // Check if not own account
        if ($beneficiaryAccount->user_id === $user->id) {
            return back()->withErrors(['account_number' => 'You cannot add your own account as a beneficiary.']);
        }

        // Check if already exists
        $exists = Beneficiary::where('user_id', $user->id)
            ->where('beneficiary_account_id', $beneficiaryAccount->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['account_number' => 'This beneficiary already exists.']);
        }

        // Create beneficiary
        $beneficiary = Beneficiary::create([
            'user_id' => $user->id,
            'beneficiary_user_id' => $beneficiaryAccount->user_id,
            'beneficiary_account_id' => $beneficiaryAccount->id,
            'nickname' => $validated['nickname'] ?? $beneficiaryAccount->account_name,
            'is_verified' => true,
        ]);

        // Log beneficiary added
        ActivityLogger::logAccount('beneficiary_added', $beneficiary, $user, [
            'account_number' => $beneficiaryAccount->account_number,
            'nickname' => $beneficiary->nickname,
        ]);

        return back()->with('success', 'Beneficiary added successfully.');
    }

    /**
     * Update a beneficiary.
     */
    public function update(Request $request, Beneficiary $beneficiary): RedirectResponse
    {
        $this->authorize('update', $beneficiary);

        $validated = $request->validate([
            'nickname' => ['nullable', 'string', 'max:100'],
            'is_favorite' => ['boolean'],
            'transfer_limit' => ['nullable', 'integer', 'min:0'],
        ]);

        $beneficiary->update($validated);

        // Log beneficiary updated
        ActivityLogger::logAccount('beneficiary_updated', $beneficiary, $request->user(), [
            'updated_fields' => array_keys($validated),
        ]);

        return back()->with('success', 'Beneficiary updated successfully.');
    }

    /**
     * Toggle favorite status.
     */
    public function toggleFavorite(Beneficiary $beneficiary): RedirectResponse
    {
        $this->authorize('update', $beneficiary);

        $beneficiary->update([
            'is_favorite' => ! $beneficiary->is_favorite,
        ]);

        $message = $beneficiary->is_favorite
            ? 'Beneficiary added to favorites.'
            : 'Beneficiary removed from favorites.';

        return back()->with('success', $message);
    }

    /**
     * Delete a beneficiary.
     */
    public function destroy(Request $request, Beneficiary $beneficiary): RedirectResponse
    {
        $this->authorize('delete', $beneficiary);

        $validated = $request->validate([
            'pin' => ['required', 'string'],
        ]);

        $user = $request->user();

        // Verify PIN
        if (! Hash::check($validated['pin'], $user->transaction_pin)) {
            return back()->withErrors(['pin' => 'Invalid transaction PIN.']);
        }

        // Log beneficiary removal before deleting
        ActivityLogger::logAccount('beneficiary_removed', $beneficiary, $user, [
            'nickname' => $beneficiary->nickname,
        ]);

        $beneficiary->delete();

        return back()->with('success', 'Beneficiary deleted successfully.');
    }
}

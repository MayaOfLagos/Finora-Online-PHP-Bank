<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\MoneyRequest;
use App\Models\TransactionHistory;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MoneyRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the request money page
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $sentRequests = $user->moneyRequestsSent()
            ->with('responder')
            ->latest()
            ->paginate(10);

        $receivedRequests = $user->moneyRequestsReceived()
            ->with('requester')
            ->latest()
            ->paginate(10);

        return Inertia::render('MoneyRequest/Index', [
            'sentRequests' => $sentRequests,
            'receivedRequests' => $receivedRequests,
        ]);
    }

    /**
     * Store a new money request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'responder_email' => 'required|email|exists:users,email',
            'amount' => 'required|integer|min:1',
            'currency' => 'required|string|max:3',
            'reason' => 'required|string|max:500',
            'type' => 'nullable|string|max:50',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $user = $request->user();
        $responder = User::where('email', $validated['responder_email'])->firstOrFail();

        // Prevent self-requests
        if ($user->id === $responder->id) {
            return back()->withErrors(['responder_email' => 'You cannot request money from yourself.']);
        }

        $moneyRequest = MoneyRequest::create([
            'id' => Str::uuid(),
            'requester_id' => $user->id,
            'responder_id' => $responder->id,
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'reason' => $validated['reason'],
            'type' => $validated['type'] ?? 'personal',
            'expires_at' => $validated['expires_at'] ?? now()->addDays(7),
            'status' => 'pending',
        ]);

        // Log activity
        ActivityLogger::logTransaction('money_request_created', $moneyRequest, $user, [
            'responder_id' => $responder->id,
            'responder_email' => $responder->email,
            'amount' => $validated['amount'],
        ]);

        return back()->with('success', 'Money request sent successfully!');
    }

    /**
     * Accept a money request
     */
    public function accept(Request $request, string $id)
    {
        $moneyRequest = MoneyRequest::where('id', $id)
            ->where('responder_id', $request->user()->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $moneyRequest->loadMissing('requester.bankAccounts');

        $targetAccount = $moneyRequest->requester->bankAccounts()
            ->where('is_active', true)
            ->where('currency', $moneyRequest->currency)
            ->orderByDesc('is_primary')
            ->orderByDesc('id')
            ->first()
            ?? $moneyRequest->requester->bankAccounts()
                ->where('is_active', true)
                ->orderByDesc('is_primary')
                ->orderByDesc('id')
                ->first();

        if (! $targetAccount) {
            return back()->withErrors([
                'account' => 'Requester has no active account to credit.',
            ]);
        }

        DB::transaction(function () use ($moneyRequest, $targetAccount, $request) {
            $targetAccount->balance += $moneyRequest->amount;
            $targetAccount->save();

            $moneyRequest->update([
                'status' => 'completed',
                'accepted_at' => now(),
                'completed_at' => now(),
            ]);

            TransactionHistory::create([
                'user_id' => $moneyRequest->requester_id,
                'bank_account_id' => $targetAccount->id,
                'transaction_type' => TransactionType::Credit,
                'transactionable_type' => MoneyRequest::class,
                'transactionable_id' => $moneyRequest->id,
                'amount' => $moneyRequest->amount / 100,
                'type' => 'credit',
                'balance_after' => $targetAccount->balance / 100,
                'currency' => $targetAccount->currency,
                'status' => 'completed',
                'description' => 'Money request approved and credited',
                'processed_at' => now(),
            ]);

            ActivityLogger::logTransaction('money_request_accepted', $moneyRequest, $request->user(), [
                'credited_account_id' => $targetAccount->id,
                'credited_user_id' => $moneyRequest->requester_id,
            ]);
        });

        return back()->with('success', 'Money request approved and credited.');
    }

    /**
     * Reject a money request
     */
    public function reject(Request $request, string $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $moneyRequest = MoneyRequest::where('id', $id)
            ->where('responder_id', $request->user()->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $moneyRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'rejected_at' => now(),
        ]);

        // Log activity
        ActivityLogger::logTransaction('money_request_rejected', $moneyRequest, $request->user(), [
            'reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', 'Money request rejected.');
    }

    /**
     * Cancel a money request (by requester only)
     */
    public function cancel(Request $request, string $id)
    {
        $moneyRequest = MoneyRequest::where('id', $id)
            ->where('requester_id', $request->user()->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $moneyRequest->update([
            'status' => 'cancelled',
        ]);

        // Log activity
        ActivityLogger::logTransaction('money_request_cancelled', $moneyRequest, $request->user());

        return back()->with('success', 'Money request cancelled.');
    }
}

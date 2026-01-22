<?php

namespace App\Http\Controllers;

use App\Models\MoneyRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
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
        ActivityLogger::logTransaction('money_request_created', $user, $moneyRequest, [
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

        $moneyRequest->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        // Log activity
        ActivityLogger::logTransaction('money_request_accepted', $request->user(), $moneyRequest);

        return back()->with('success', 'Money request accepted! Payment pending.');
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
        ActivityLogger::logTransaction('money_request_rejected', $request->user(), $moneyRequest, [
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
        ActivityLogger::logTransaction('money_request_cancelled', $request->user(), $moneyRequest);

        return back()->with('success', 'Money request cancelled.');
    }
}

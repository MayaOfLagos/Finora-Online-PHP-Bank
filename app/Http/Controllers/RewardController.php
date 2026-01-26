<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class RewardController extends Controller
{
    /**
     * Display rewards page
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $rewards = $user->rewards()
            ->latest()
            ->paginate(20);

        // Calculate totals from points (earned are positive, redeemed status tracks spent points)
        $earnedPoints = $user->rewards()
            ->where('status', 'earned')
            ->sum('points');
            
        $redeemedPoints = $user->rewards()
            ->where('status', 'redeemed')
            ->sum('points');

        $stats = [
            'total_earned' => $earnedPoints + $redeemedPoints, // Total ever earned
            'total_redeemed' => $redeemedPoints,
            'pending' => $user->rewards()->where('status', 'pending')->count(),
            'available' => $earnedPoints, // Points with status 'earned' are available
        ];

        $bankAccounts = $user->bankAccounts()
            ->select('id', 'account_number', 'account_name', 'currency')
            ->get();

        return Inertia::render('Reward/Index', [
            'rewards' => $rewards,
            'stats' => $stats,
            'bankAccounts' => $bankAccounts,
        ]);
    }

    /**
     * Redeem reward points
     */
    public function redeem(Request $request)
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:100',
            'redemption_type' => 'required|in:cash,voucher,discount',
            'bank_account_id' => 'required_if:redemption_type,cash|nullable|exists:bank_accounts,id',
            'pin' => 'required|string|size:6',
        ]);

        $user = $request->user();

        // Verify PIN
        if (!$user->transaction_pin || !Hash::check($validated['pin'], $user->transaction_pin)) {
            return back()->withErrors(['pin' => 'Invalid transaction PIN. Please try again.']);
        }

        // Calculate available points (those with status 'earned')
        $availablePoints = $user->rewards()
            ->where('status', 'earned')
            ->sum('points');

        if ($availablePoints < $validated['points']) {
            return back()->withErrors(['points' => 'Insufficient reward points. You have ' . $availablePoints . ' points available.']);
        }

        return DB::transaction(function () use ($validated, $user) {
            // Conversion rate: 100 points = $1
            $cashValue = $validated['points'] / 100;
            $pointsToRedeem = $validated['points'];
            
            // Mark existing earned rewards as redeemed until we've used enough points
            $earnedRewards = $user->rewards()
                ->where('status', 'earned')
                ->orderBy('earned_date', 'asc') // Use oldest first (FIFO)
                ->get();
            
            $redeemedRewardIds = [];
            $pointsUsed = 0;
            
            foreach ($earnedRewards as $earnedReward) {
                if ($pointsUsed >= $pointsToRedeem) {
                    break;
                }
                
                $pointsUsed += $earnedReward->points;
                $redeemedRewardIds[] = $earnedReward->id;
            }
            
            // Update the rewards to 'redeemed' status
            Reward::whereIn('id', $redeemedRewardIds)->update([
                'status' => 'redeemed',
                'redeemed_at' => now(),
            ]);
            
            // Create a redemption activity record
            $redemptionRecord = Reward::create([
                'user_id' => $user->id,
                'title' => ucfirst($validated['redemption_type']) . ' Redemption',
                'description' => 'Redeemed ' . $pointsToRedeem . ' points for ' . $validated['redemption_type'],
                'points' => $pointsToRedeem,
                'type' => 'cashback', // Using 'cashback' as the closest valid type
                'status' => 'redeemed',
                'earned_date' => now(),
                'redeemed_at' => now(),
                'source' => 'Points redemption - ' . $validated['redemption_type'],
                'metadata' => [
                    'redemption_type' => $validated['redemption_type'],
                    'cash_value' => $cashValue,
                    'redeemed_reward_ids' => $redeemedRewardIds,
                ],
            ]);

            // If cash redemption, credit to bank account
            if ($validated['redemption_type'] === 'cash' && $validated['bank_account_id']) {
                $bankAccount = $user->bankAccounts()->findOrFail($validated['bank_account_id']);
                $bankAccount->increment('balance', $cashValue * 100); // Convert to cents
                
                $redemptionRecord->update([
                    'metadata' => array_merge($redemptionRecord->metadata ?? [], [
                        'bank_account_id' => $bankAccount->id,
                        'credited_amount' => $cashValue,
                    ]),
                ]);
            }

            // Log activity
            ActivityLogger::logTransaction('reward_redeemed', $redemptionRecord, $user, [
                'points' => $pointsToRedeem,
                'type' => $validated['redemption_type'],
                'cash_value' => $cashValue,
            ]);

            $message = $validated['redemption_type'] === 'cash' 
                ? "Successfully redeemed {$pointsToRedeem} points for \${$cashValue}!"
                : "Successfully redeemed {$pointsToRedeem} points for {$validated['redemption_type']}!";

            return back()->with('success', $message);
        });
    }
}

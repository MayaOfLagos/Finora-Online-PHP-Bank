<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RewardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display rewards page
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $rewards = $user->rewards()
            ->latest()
            ->paginate(20);

        $stats = [
            'total_earned' => $user->rewards()->where('status', 'earned')->sum('points'),
            'total_redeemed' => $user->rewards()->where('status', 'redeemed')->sum('points'),
            'pending' => $user->rewards()->where('status', 'pending')->count(),
            'available' => $user->rewards()->where('status', 'earned')->count(),
        ];

        return Inertia::render('Reward/Index', [
            'rewards' => $rewards,
            'stats' => $stats,
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
        ]);

        $user = $request->user();

        // Check available points
        $availablePoints = $user->rewards()
            ->where('status', 'earned')
            ->sum('points');

        if ($availablePoints < $validated['points']) {
            return back()->withErrors(['points' => 'Insufficient reward points.']);
        }

        // Create redemption record
        $reward = Reward::create([
            'user_id' => $user->id,
            'title' => ucfirst($validated['redemption_type']).' Redemption',
            'points' => $validated['points'],
            'type' => 'redemption',
            'status' => 'redeemed',
            'redeemed_at' => now(),
            'source' => 'User redeemed '.$validated['points'].' points for '.$validated['redemption_type'],
        ]);

        // Log activity
        ActivityLogger::logTransaction('reward_redeemed', $user, $reward, [
            'points' => $validated['points'],
            'type' => $validated['redemption_type'],
        ]);

        return back()->with('success', 'Rewards redeemed successfully!');
    }
}

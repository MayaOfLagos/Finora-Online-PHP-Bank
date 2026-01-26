<?php

namespace Database\Seeders;

use App\Models\Reward;
use App\Models\User;
use Illuminate\Database\Seeder;

class RewardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Type options: referral, cashback, loyalty, bonus, achievement
     * Status options: pending, earned, redeemed, expired
     */
    public function run(): void
    {
        // Get the first user or create one
        $user = User::first();
        
        if (!$user) {
            $this->command->warn('No users found. Please create a user first.');
            return;
        }

        $this->command->info("Creating rewards for user: {$user->email}");

        // Sample reward entries with valid enum types
        $rewards = [
            [
                'title' => 'Welcome Bonus',
                'description' => 'Welcome to Finora Bank! Enjoy your first reward.',
                'points' => 500,
                'type' => 'bonus',
                'status' => 'earned',
                'earned_date' => now()->subDays(30),
                'source' => 'Account registration',
            ],
            [
                'title' => 'First Transfer Bonus',
                'description' => 'Completed your first money transfer',
                'points' => 200,
                'type' => 'achievement',
                'status' => 'earned',
                'earned_date' => now()->subDays(25),
                'source' => 'Wire transfer milestone',
            ],
            [
                'title' => 'Bill Payment Cashback',
                'description' => 'Cashback for bill payment',
                'points' => 50,
                'type' => 'cashback',
                'status' => 'earned',
                'earned_date' => now()->subDays(20),
                'source' => 'Bill payment',
            ],
            [
                'title' => 'Referral Bonus',
                'description' => 'Referred a friend who joined Finora Bank',
                'points' => 1000,
                'type' => 'referral',
                'status' => 'earned',
                'earned_date' => now()->subDays(15),
                'source' => 'Referral program',
            ],
            [
                'title' => 'Monthly Loyalty Reward',
                'description' => 'Maintained active account for the month',
                'points' => 150,
                'type' => 'loyalty',
                'status' => 'earned',
                'earned_date' => now()->subDays(10),
                'source' => 'Monthly activity',
            ],
            [
                'title' => 'Card Spending Cashback',
                'description' => 'Spent $100+ using your Finora card',
                'points' => 100,
                'type' => 'cashback',
                'status' => 'earned',
                'earned_date' => now()->subDays(7),
                'source' => 'Card spending',
            ],
            [
                'title' => 'Profile Completion Achievement',
                'description' => 'Completed your profile with all required information',
                'points' => 100,
                'type' => 'achievement',
                'status' => 'earned',
                'earned_date' => now()->subDays(5),
                'source' => 'Profile completion',
            ],
            [
                'title' => 'Direct Deposit Bonus',
                'description' => 'Set up direct deposit to your account',
                'points' => 300,
                'type' => 'bonus',
                'status' => 'earned',
                'earned_date' => now()->subDays(3),
                'source' => 'Direct deposit setup',
            ],
            [
                'title' => 'Pending Transfer Bonus',
                'description' => 'International transfer reward - pending verification',
                'points' => 250,
                'type' => 'bonus',
                'status' => 'pending',
                'earned_date' => now()->subDay(),
                'source' => 'International transfer',
            ],
        ];

        foreach ($rewards as $rewardData) {
            Reward::create([
                'user_id' => $user->id,
                ...$rewardData,
            ]);
        }

        // Calculate totals
        $totalEarned = collect($rewards)->where('status', 'earned')->sum('points');
        $pending = collect($rewards)->where('status', 'pending')->count();

        $this->command->info("Created " . count($rewards) . " rewards");
        $this->command->info("Total earned points: {$totalEarned}");
        $this->command->info("Pending rewards: {$pending}");
        $this->command->newLine();
        
        $this->command->table(
            ['Title', 'Points', 'Type', 'Status'],
            collect($rewards)->map(fn($r) => [
                $r['title'],
                $r['points'],
                $r['type'],
                $r['status'],
            ])->toArray()
        );
    }
}

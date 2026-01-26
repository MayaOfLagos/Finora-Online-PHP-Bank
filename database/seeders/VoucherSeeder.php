<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Single-use voucher - $50 bonus
        Voucher::create([
            'id' => Str::uuid(),
            'code' => 'WELCOME50',
            'description' => 'Welcome bonus - $50 credit for new users',
            'amount' => 5000, // $50.00 in cents
            'currency' => 'USD',
            'type' => 'bonus',
            'status' => 'active',
            'usage_limit' => 1,
            'times_used' => 0,
            'is_used' => false,
            'expires_at' => now()->addMonths(6),
        ]);

        // Single-use voucher - $100 referral
        Voucher::create([
            'id' => Str::uuid(),
            'code' => 'REFER100',
            'description' => 'Referral bonus - $100 credit',
            'amount' => 10000, // $100.00 in cents
            'currency' => 'USD',
            'type' => 'referral',
            'status' => 'active',
            'usage_limit' => 1,
            'times_used' => 0,
            'is_used' => false,
            'expires_at' => now()->addYear(),
        ]);

        // Multi-use/Unlimited voucher - $25 cashback (can be used 100 times)
        Voucher::create([
            'id' => Str::uuid(),
            'code' => 'CASHBACK25',
            'description' => 'Cashback promotion - $25 credit (multi-use)',
            'amount' => 2500, // $25.00 in cents
            'currency' => 'USD',
            'type' => 'cashback',
            'status' => 'active',
            'usage_limit' => 100, // Can be used 100 times
            'times_used' => 0,
            'is_used' => false,
            'expires_at' => now()->addMonths(3),
        ]);

        // Unlimited use voucher - $10 discount
        Voucher::create([
            'id' => Str::uuid(),
            'code' => 'UNLIMITED10',
            'description' => 'Unlimited use promo - $10 credit',
            'amount' => 1000, // $10.00 in cents
            'currency' => 'USD',
            'type' => 'discount',
            'status' => 'active',
            'usage_limit' => 999999, // Essentially unlimited
            'times_used' => 0,
            'is_used' => false,
            'expires_at' => now()->addYear(),
        ]);

        // Single-use high-value voucher - $500 bonus
        Voucher::create([
            'id' => Str::uuid(),
            'code' => 'VIP500',
            'description' => 'VIP exclusive - $500 credit',
            'amount' => 50000, // $500.00 in cents
            'currency' => 'USD',
            'type' => 'bonus',
            'status' => 'active',
            'usage_limit' => 1,
            'times_used' => 0,
            'is_used' => false,
            'expires_at' => now()->addMonths(12),
        ]);

        $this->command->info('Test vouchers created:');
        $this->command->table(
            ['Code', 'Amount', 'Type', 'Usage Limit', 'Expires'],
            [
                ['WELCOME50', '$50.00', 'Single-use Bonus', '1', now()->addMonths(6)->format('Y-m-d')],
                ['REFER100', '$100.00', 'Single-use Referral', '1', now()->addYear()->format('Y-m-d')],
                ['CASHBACK25', '$25.00', 'Multi-use (100x)', '100', now()->addMonths(3)->format('Y-m-d')],
                ['UNLIMITED10', '$10.00', 'Unlimited', '999999', now()->addYear()->format('Y-m-d')],
                ['VIP500', '$500.00', 'Single-use VIP', '1', now()->addMonths(12)->format('Y-m-d')],
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use App\Enums\VerificationCodeType;
use App\Models\AccountType;
use App\Models\BankAccount;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'uuid' => 'admin-uuid-0001-0001-000000000001',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@finorabank.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone_number' => '+1234567890',
            'date_of_birth' => '1985-01-15',
            'address_line_1' => '123 Admin Street',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'United States',
            'transaction_pin' => Hash::make('1234'),
            'is_active' => true,
            'is_verified' => true,
            'kyc_level' => 3,
        ]);

        // Create admin verification codes (for demo purposes)
        $codes = [
            ['type' => VerificationCodeType::Imf, 'code' => 'IMF-123456'],
            ['type' => VerificationCodeType::Tax, 'code' => 'TAX-789012'],
            ['type' => VerificationCodeType::Cot, 'code' => 'COT-345678'],
        ];

        foreach ($codes as $code) {
            VerificationCode::create([
                'user_id' => $admin->id,
                'type' => $code['type'],
                'code' => $code['code'],
                'is_active' => true,
            ]);
        }

        // Create demo user
        $demoUser = User::create([
            'uuid' => 'demo-uuid-0001-0001-000000000001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'demo@finorabank.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone_number' => '+1987654321',
            'date_of_birth' => '1990-06-15',
            'address_line_1' => '456 Demo Avenue',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'postal_code' => '90001',
            'country' => 'United States',
            'transaction_pin' => Hash::make('1234'),
            'is_active' => true,
            'is_verified' => true,
            'kyc_level' => 2,
        ]);

        // Create demo user verification codes
        foreach ($codes as $code) {
            VerificationCode::create([
                'user_id' => $demoUser->id,
                'type' => $code['type'],
                'code' => $code['code'],
                'is_active' => true,
            ]);
        }

        // Get account types
        $savingsType = AccountType::where('code', 'SAV')->first();
        $checkingType = AccountType::where('code', 'CHK')->first();

        // Create bank accounts for demo user
        if ($savingsType && $checkingType) {
            BankAccount::create([
                'uuid' => 'ba-uuid-0001-0001-000000000001',
                'user_id' => $demoUser->id,
                'account_type_id' => $checkingType->id,
                'account_number' => '10000000001',
                'balance' => 2500000, // $25,000
                'currency' => 'USD',
                'is_primary' => true,
                'is_active' => true,
                'opened_at' => now()->subYear(),
            ]);

            BankAccount::create([
                'uuid' => 'ba-uuid-0001-0001-000000000002',
                'user_id' => $demoUser->id,
                'account_type_id' => $savingsType->id,
                'account_number' => '10000000002',
                'balance' => 7500000, // $75,000
                'currency' => 'USD',
                'is_primary' => false,
                'is_active' => true,
                'opened_at' => now()->subMonths(6),
            ]);
        }
    }
}

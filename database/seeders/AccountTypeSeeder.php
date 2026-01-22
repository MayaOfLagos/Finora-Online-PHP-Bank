<?php

namespace Database\Seeders;

use App\Models\AccountType;
use Illuminate\Database\Seeder;

class AccountTypeSeeder extends Seeder
{
    public function run(): void
    {
        $accountTypes = [
            [
                'name' => 'Savings Account',
                'code' => 'SAV',
                'description' => 'A basic savings account with competitive interest rates.',
                'minimum_balance' => 10000, // $100
                'is_active' => true,
            ],
            [
                'name' => 'Checking Account',
                'code' => 'CHK',
                'description' => 'A standard checking account for everyday transactions.',
                'minimum_balance' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Business Account',
                'code' => 'BUS',
                'description' => 'A business account designed for entrepreneurs and companies.',
                'minimum_balance' => 50000, // $500
                'is_active' => true,
            ],
            [
                'name' => 'Premium Account',
                'code' => 'PRM',
                'description' => 'A premium account with exclusive benefits and higher limits.',
                'minimum_balance' => 100000, // $1000
                'is_active' => true,
            ],
        ];

        foreach ($accountTypes as $type) {
            AccountType::create($type);
        }
    }
}

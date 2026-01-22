<?php

namespace Database\Seeders;

use App\Models\LoanType;
use Illuminate\Database\Seeder;

class LoanTypeSeeder extends Seeder
{
    public function run(): void
    {
        $loanTypes = [
            [
                'name' => 'Personal Loan',
                'code' => 'PL01',
                'description' => 'Unsecured personal loans for various personal needs including debt consolidation, home improvements, or major purchases.',
                'min_amount' => 100000, // $1,000
                'max_amount' => 5000000, // $50,000
                'min_term_months' => 12,
                'max_term_months' => 60,
                'interest_rate' => 12.99,
                'is_active' => true,
            ],
            [
                'name' => 'Business Loan',
                'code' => 'BL01',
                'description' => 'Financing solutions for small and medium businesses to support growth, equipment purchases, or working capital.',
                'min_amount' => 500000, // $5,000
                'max_amount' => 25000000, // $250,000
                'min_term_months' => 12,
                'max_term_months' => 84,
                'interest_rate' => 10.50,
                'is_active' => true,
            ],
            [
                'name' => 'Mortgage Loan',
                'code' => 'MG01',
                'description' => 'Home financing with competitive rates for purchasing or refinancing residential properties.',
                'min_amount' => 5000000, // $50,000
                'max_amount' => 100000000, // $1,000,000
                'min_term_months' => 60,
                'max_term_months' => 360,
                'interest_rate' => 6.75,
                'is_active' => true,
            ],
            [
                'name' => 'Auto Loan',
                'code' => 'AL01',
                'description' => 'Vehicle financing for new and used cars, motorcycles, and recreational vehicles.',
                'min_amount' => 500000, // $5,000
                'max_amount' => 10000000, // $100,000
                'min_term_months' => 24,
                'max_term_months' => 72,
                'interest_rate' => 8.50,
                'is_active' => true,
            ],
            [
                'name' => 'Education Loan',
                'code' => 'EL01',
                'description' => 'Student loans to cover tuition, books, and living expenses for higher education.',
                'min_amount' => 100000, // $1,000
                'max_amount' => 15000000, // $150,000
                'min_term_months' => 36,
                'max_term_months' => 180,
                'interest_rate' => 7.25,
                'is_active' => true,
            ],
        ];

        foreach ($loanTypes as $type) {
            LoanType::create($type);
        }
    }
}

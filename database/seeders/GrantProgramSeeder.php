<?php

namespace Database\Seeders;

use App\Enums\GrantStatus;
use App\Models\GrantProgram;
use Illuminate\Database\Seeder;

class GrantProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            [
                'name' => 'Small Business Development Grant',
                'description' => 'Supporting small business owners with funding for business expansion, equipment purchases, or operational improvements. This grant is designed to help entrepreneurs grow their businesses and create jobs in their communities.',
                'amount' => 500000, // $5,000
                'currency' => 'USD',
                'eligibility_criteria' => [
                    'min_account_age_months' => 6,
                    'min_balance' => 50000,
                    'kyc_level' => 2,
                    'requirements' => [
                        'Must have an active business account',
                        'Business must be registered for at least 1 year',
                        'Must demonstrate business revenue',
                    ],
                ],
                'required_documents' => [
                    'Business Registration Certificate',
                    'Financial Statements (Last 6 months)',
                    'Business Plan',
                    'ID Document',
                ],
                'start_date' => now(),
                'end_date' => now()->addMonths(6),
                'max_recipients' => 100,
                'status' => GrantStatus::Open,
            ],
            [
                'name' => 'Education Support Fund',
                'description' => 'Helping students and families cover educational expenses including tuition, books, and supplies. This fund supports the pursuit of higher education and skill development.',
                'amount' => 250000, // $2,500
                'currency' => 'USD',
                'eligibility_criteria' => [
                    'min_account_age_months' => 3,
                    'min_balance' => 10000,
                    'kyc_level' => 1,
                    'requirements' => [
                        'Must be enrolled in an educational institution',
                        'Must maintain good academic standing',
                    ],
                ],
                'required_documents' => [
                    'Student ID',
                    'Enrollment Verification Letter',
                    'Academic Transcript',
                    'Personal ID Document',
                ],
                'start_date' => now(),
                'end_date' => now()->addMonths(12),
                'max_recipients' => 200,
                'status' => GrantStatus::Open,
            ],
            [
                'name' => 'Emergency Relief Assistance',
                'description' => 'Providing financial assistance to customers facing unexpected emergencies such as medical expenses, natural disasters, or other unforeseen circumstances.',
                'amount' => 100000, // $1,000
                'currency' => 'USD',
                'eligibility_criteria' => [
                    'min_account_age_months' => 1,
                    'min_balance' => 0,
                    'kyc_level' => 1,
                    'requirements' => [
                        'Must demonstrate emergency situation',
                        'Must provide supporting documentation',
                    ],
                ],
                'required_documents' => [
                    'Emergency Documentation',
                    'Personal ID Document',
                    'Proof of Address',
                ],
                'start_date' => now(),
                'end_date' => now()->addMonths(12),
                'max_recipients' => 500,
                'status' => GrantStatus::Open,
            ],
        ];

        foreach ($programs as $program) {
            GrantProgram::create($program);
        }
    }
}

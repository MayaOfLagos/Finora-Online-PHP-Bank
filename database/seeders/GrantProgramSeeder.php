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
                    'Active business account with Finora (min 6 months)',
                    'Maintain minimum balance of $500',
                    'KYC Level 2 verified',
                    'Business registered for at least 1 year',
                    'Demonstrate existing business revenue',
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
                    'Active Finora account (min 3 months)',
                    'KYC Level 1 verified',
                    'Currently enrolled in an educational institution',
                    'Maintain satisfactory academic standing',
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
                    'Active Finora account (min 1 month)',
                    'KYC Level 1 verified',
                    'Provide documentation of the emergency need',
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

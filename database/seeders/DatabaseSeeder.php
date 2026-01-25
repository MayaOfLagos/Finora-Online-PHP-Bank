<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Static/Reference Data (order matters)
            AccountTypeSeeder::class,
            BankSeeder::class,
            CardTypeSeeder::class,
            LoanTypeSeeder::class,
            CryptocurrencySeeder::class,
            SupportCategorySeeder::class,
            PaymentGatewaySeeder::class,
            GrantProgramSeeder::class,
            SettingSeeder::class,

            // Beneficiary Field Templates and Settings
            BeneficiaryFieldTemplateSeeder::class,
            // BeneficiaryFieldSettingSeeder::class, // TODO: Model BeneficiaryFieldSetting needs to be created

            // Users and Accounts (depends on account types)
            AdminUserSeeder::class,

            // Support Content (depends on categories)
            FaqSeeder::class,
            KnowledgeBaseSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\SupportCategory;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $accountCategory = SupportCategory::where('name', 'Account Management')->first();
        $transferCategory = SupportCategory::where('name', 'Transfers & Payments')->first();
        $cardCategory = SupportCategory::where('name', 'Cards')->first();
        $securityCategory = SupportCategory::where('name', 'Security')->first();

        $faqs = [
            // Account FAQs
            [
                'category_id' => $accountCategory?->id,
                'question' => 'How do I open a new bank account?',
                'answer' => 'To open a new account, log in to your dashboard and navigate to "Accounts" > "Open New Account". Select the account type you want and follow the verification steps. Most accounts can be opened instantly once your KYC verification is complete.',
                'sort_order' => 1,
            ],
            [
                'category_id' => $accountCategory?->id,
                'question' => 'How can I update my personal information?',
                'answer' => 'You can update your personal information by going to "Settings" > "Profile". Some changes may require additional verification for security purposes. If you need to update your legal name or date of birth, please contact customer support.',
                'sort_order' => 2,
            ],
            [
                'category_id' => $accountCategory?->id,
                'question' => 'What are the minimum balance requirements?',
                'answer' => 'Minimum balance requirements vary by account type: Checking accounts have no minimum balance, Savings accounts require $100, Business accounts require $500, and Premium accounts require $1,000.',
                'sort_order' => 3,
            ],

            // Transfer FAQs
            [
                'category_id' => $transferCategory?->id,
                'question' => 'How long do wire transfers take?',
                'answer' => 'International wire transfers typically take 3-5 business days to complete. The exact time depends on the destination country and receiving bank. You can track the status of your transfer in the "Transfers" section of your dashboard.',
                'sort_order' => 1,
            ],
            [
                'category_id' => $transferCategory?->id,
                'question' => 'What are the transfer fees?',
                'answer' => 'Transfer fees vary by type: Internal transfers within Finora Bank are free, Domestic transfers have a 0.5% fee, and International wire transfers have a 2.5% fee with a minimum of $25. You can view the exact fee before confirming any transfer.',
                'sort_order' => 2,
            ],
            [
                'category_id' => $transferCategory?->id,
                'question' => 'What is the daily transfer limit?',
                'answer' => 'Daily transfer limits depend on your account type and KYC level. Standard accounts can transfer up to $50,000 per day for wire transfers and $25,000 for domestic transfers. Premium accounts have higher limits. You can view your limits in Account Settings.',
                'sort_order' => 3,
            ],

            // Card FAQs
            [
                'category_id' => $cardCategory?->id,
                'question' => 'How do I activate my new card?',
                'answer' => 'To activate your card, go to "Cards" in your dashboard, select the card you want to activate, and click "Activate Card". You will need to verify your identity through OTP. Virtual cards are activated instantly upon creation.',
                'sort_order' => 1,
            ],
            [
                'category_id' => $cardCategory?->id,
                'question' => 'How can I freeze my card temporarily?',
                'answer' => 'You can freeze your card instantly from the "Cards" section. Select the card and click "Freeze Card". This will temporarily disable all transactions. You can unfreeze it anytime from the same menu.',
                'sort_order' => 2,
            ],
            [
                'category_id' => $cardCategory?->id,
                'question' => 'How do I change my card PIN?',
                'answer' => 'To change your card PIN, go to "Cards" > select your card > "Manage PIN". You will need to enter your transaction PIN and verify via OTP. You can then set a new 4-digit PIN for your card.',
                'sort_order' => 3,
            ],

            // Security FAQs
            [
                'category_id' => $securityCategory?->id,
                'question' => 'How do I enable two-factor authentication?',
                'answer' => 'To enable 2FA, go to "Settings" > "Security" > "Two-Factor Authentication". You can choose between email OTP or authenticator app. We highly recommend enabling 2FA for enhanced account security.',
                'sort_order' => 1,
            ],
            [
                'category_id' => $securityCategory?->id,
                'question' => 'What should I do if I suspect fraudulent activity?',
                'answer' => 'If you suspect any fraudulent activity, immediately freeze your cards from the dashboard and contact our security team through the "Security" support category. We recommend changing your password and transaction PIN as a precaution.',
                'sort_order' => 2,
            ],
            [
                'category_id' => $securityCategory?->id,
                'question' => 'How do I reset my transaction PIN?',
                'answer' => 'To reset your transaction PIN, go to "Settings" > "Security" > "Transaction PIN". You will receive an OTP to your registered email. After verification, you can set a new 4-6 digit transaction PIN.',
                'sort_order' => 3,
            ],
        ];

        foreach ($faqs as $faq) {
            if ($faq['category_id']) {
                Faq::create([
                    'category_id' => $faq['category_id'],
                    'question' => $faq['question'],
                    'answer' => $faq['answer'],
                    'is_published' => true,
                    'sort_order' => $faq['sort_order'],
                    'view_count' => rand(10, 500),
                ]);
            }
        }
    }
}

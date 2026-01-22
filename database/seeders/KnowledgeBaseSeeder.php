<?php

namespace Database\Seeders;

use App\Models\KnowledgeBaseArticle;
use App\Models\SupportCategory;
use Illuminate\Database\Seeder;

class KnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = SupportCategory::all()->keyBy('name');

        $articles = [
            // Account Management
            [
                'category_id' => $categories->get('Account Management')?->id,
                'title' => 'Getting Started with Finora Bank',
                'slug' => 'getting-started-with-finora-bank',
                'content' => "# Getting Started with Finora Bank\n\nWelcome to Finora Bank! This guide will help you get started with your new account.\n\n## Step 1: Complete Your Profile\n\nAfter registration, complete your profile by adding:\n- Your full legal name\n- Date of birth\n- Contact information\n- Residential address\n\n## Step 2: Verify Your Identity (KYC)\n\nTo unlock all features, complete KYC verification:\n1. Go to Settings > KYC Verification\n2. Upload a valid government ID\n3. Take a selfie for verification\n4. Submit and wait for approval\n\n## Step 3: Set Up Security\n\n- Create a strong transaction PIN\n- Enable two-factor authentication\n- Review your security settings\n\n## Step 4: Open Your First Account\n\nNavigate to Accounts and choose from:\n- Savings Account\n- Checking Account\n- Business Account\n\n## Need Help?\n\nContact our support team anytime through the Support section.",
                'is_published' => true,
            ],
            [
                'category_id' => $categories->get('Account Management')?->id,
                'title' => 'Understanding Account Types',
                'slug' => 'understanding-account-types',
                'content' => "# Understanding Account Types\n\nFinora Bank offers several account types to meet your financial needs.\n\n## Savings Account\n\n**Best for:** Building savings and earning interest\n- Competitive interest rates\n- Minimum balance: \$100\n- Limited monthly transactions\n- No monthly fees with minimum balance\n\n## Checking Account\n\n**Best for:** Daily transactions and bill payments\n- Unlimited transactions\n- No minimum balance\n- Debit card included\n- Direct deposit available\n\n## Business Account\n\n**Best for:** Entrepreneurs and business owners\n- Higher transaction limits\n- Minimum balance: \$500\n- Multiple user access\n- Business tools and analytics\n\n## Premium Account\n\n**Best for:** High-net-worth individuals\n- Priority customer service\n- Higher transfer limits\n- Premium card options\n- Exclusive benefits",
                'is_published' => true,
            ],

            // Transfers & Payments
            [
                'category_id' => $categories->get('Transfers & Payments')?->id,
                'title' => 'How to Make International Wire Transfers',
                'slug' => 'how-to-make-international-wire-transfers',
                'content' => "# How to Make International Wire Transfers\n\nSend money internationally with our secure wire transfer service.\n\n## Requirements\n\n- Verified account (KYC Level 2+)\n- Sufficient balance\n- Recipient's bank details\n- SWIFT/BIC code\n\n## Step-by-Step Guide\n\n### Step 1: Start Transfer\n\nGo to Transfers > Wire Transfer > New Transfer\n\n### Step 2: Enter Recipient Details\n\n- Full name (as it appears on bank account)\n- Bank name and address\n- SWIFT/BIC code\n- Account number or IBAN\n\n### Step 3: Enter Amount\n\n- Select currency\n- Enter amount\n- Review fees and exchange rate\n\n### Step 4: Verification\n\nFor security, you'll need to verify:\n1. Transaction PIN\n2. IMF Code\n3. Tax Code\n4. COT Code\n5. Email OTP\n\n### Step 5: Confirm\n\nReview all details and confirm the transfer.\n\n## Processing Time\n\nInternational transfers typically take 3-5 business days.\n\n## Fees\n\n- 2.5% of transfer amount\n- Minimum fee: \$25",
                'is_published' => true,
            ],

            // Cards
            [
                'category_id' => $categories->get('Cards')?->id,
                'title' => 'Managing Your Finora Bank Cards',
                'slug' => 'managing-your-finora-bank-cards',
                'content' => "# Managing Your Finora Bank Cards\n\nLearn how to manage your cards effectively.\n\n## Card Types\n\n### Debit Cards\n- Visa Classic Debit\n- Visa Gold Debit\n- Mastercard Platinum Debit\n\n### Credit Cards\n- Visa Credit Card\n- Mastercard Credit Gold\n\n### Virtual Cards\nInstant digital cards for online purchases.\n\n## Card Features\n\n### Freeze/Unfreeze\nTemporarily disable your card from the dashboard.\n\n### Set Spending Limits\nControl daily and monthly spending limits.\n\n### Transaction Alerts\nGet instant notifications for all transactions.\n\n### PIN Management\nChange your card PIN securely online.\n\n## Request a New Card\n\n1. Go to Cards > Request New Card\n2. Select card type\n3. Choose delivery address\n4. Confirm request\n\n## Report Lost/Stolen\n\nImmediately freeze your card and contact support to report a lost or stolen card.",
                'is_published' => true,
            ],

            // Security
            [
                'category_id' => $categories->get('Security')?->id,
                'title' => 'Security Best Practices',
                'slug' => 'security-best-practices',
                'content' => "# Security Best Practices\n\nKeep your Finora Bank account secure with these tips.\n\n## Password Security\n\n- Use a strong, unique password\n- Include numbers, symbols, and mixed case\n- Never share your password\n- Change it regularly\n\n## Two-Factor Authentication (2FA)\n\nEnable 2FA for an extra layer of security:\n1. Go to Settings > Security\n2. Enable Two-Factor Authentication\n3. Choose your preferred method\n\n## Transaction PIN\n\n- Keep your PIN confidential\n- Don't use obvious numbers (1234, birth year)\n- Change it if you suspect compromise\n\n## Recognize Phishing\n\n- We'll never ask for your password via email\n- Check URLs carefully\n- Report suspicious emails\n\n## Device Security\n\n- Keep your devices updated\n- Use antivirus software\n- Log out from shared computers\n\n## Review Activity\n\n- Check your transaction history regularly\n- Review login history\n- Set up transaction alerts\n\n## Report Suspicious Activity\n\nContact support immediately if you notice any unauthorized activity.",
                'is_published' => true,
            ],

            // Deposits
            [
                'category_id' => $categories->get('Deposits')?->id,
                'title' => 'Deposit Methods Guide',
                'slug' => 'deposit-methods-guide',
                'content' => "# Deposit Methods Guide\n\nFinora Bank offers multiple convenient deposit methods.\n\n## Check Deposit\n\n### Mobile Check Deposit\n1. Go to Deposits > Check Deposit\n2. Take photos of front and back\n3. Enter check amount\n4. Submit for processing\n\n**Hold Period:** 5 business days for new accounts\n\n## Mobile Deposit (Payment Gateways)\n\nInstantly fund your account using:\n- Stripe (Credit/Debit cards)\n- PayPal\n- Paystack\n\n### How to Deposit:\n1. Select Deposits > Mobile Deposit\n2. Choose payment gateway\n3. Enter amount\n4. Complete payment\n\n## Cryptocurrency Deposit\n\n### Supported Cryptocurrencies:\n- Bitcoin (BTC)\n- Ethereum (ETH)\n- USDT (TRC20/ERC20)\n- USDC\n\n### How to Deposit:\n1. Go to Deposits > Crypto Deposit\n2. Select cryptocurrency\n3. Copy wallet address or scan QR code\n4. Send funds from your wallet\n5. Wait for confirmations\n\n**Note:** Crypto deposits require manual verification.",
                'is_published' => true,
            ],
        ];

        foreach ($articles as $article) {
            if ($article['category_id']) {
                KnowledgeBaseArticle::create([
                    'category_id' => $article['category_id'],
                    'title' => $article['title'],
                    'slug' => $article['slug'],
                    'content' => $article['content'],
                    'is_published' => $article['is_published'],
                    'view_count' => rand(50, 1000),
                ]);
            }
        }
    }
}

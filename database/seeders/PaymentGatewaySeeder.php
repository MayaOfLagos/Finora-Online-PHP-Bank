<?php

namespace Database\Seeders;

use App\Models\PaymentGateway as PaymentGatewayModel;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $gateways = [
            [
                'name' => 'Stripe',
                'code' => 'stripe',
                'logo' => null,
                'credentials' => [
                    'public_key' => 'pk_test_xxxxxxxxxxxxxxxxxxxxxxxx',
                    'secret_key' => 'sk_test_xxxxxxxxxxxxxxxxxxxxxxxx',
                    'webhook_secret' => 'whsec_xxxxxxxxxxxxxxxxxxxxxxxx',
                ],
                'is_active' => true,
                'is_test_mode' => true,
            ],
            [
                'name' => 'PayPal',
                'code' => 'paypal',
                'logo' => null,
                'credentials' => [
                    'client_id' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
                    'client_secret' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
                    'mode' => 'sandbox',
                ],
                'is_active' => true,
                'is_test_mode' => true,
            ],
            [
                'name' => 'Paystack',
                'code' => 'paystack',
                'logo' => null,
                'credentials' => [
                    'public_key' => 'pk_test_xxxxxxxxxxxxxxxxxxxxxxxx',
                    'secret_key' => 'sk_test_xxxxxxxxxxxxxxxxxxxxxxxx',
                ],
                'is_active' => true,
                'is_test_mode' => true,
            ],
            [
                'name' => 'Flutterwave',
                'code' => 'flutterwave',
                'logo' => null,
                'credentials' => [
                    'public_key' => 'FLWPUBK_TEST-xxxxxxxxxxxxxxxx',
                    'secret_key' => 'FLWSECK_TEST-xxxxxxxxxxxxxxxx',
                    'encryption_key' => 'FLWSECK_TEST-xxxxxxxx',
                ],
                'is_active' => false,
                'is_test_mode' => true,
            ],
            [
                'name' => 'Razorpay',
                'code' => 'razorpay',
                'logo' => null,
                'credentials' => [
                    'key_id' => 'rzp_test_xxxxxxxxxxxxxxxx',
                    'key_secret' => 'xxxxxxxxxxxxxxxxxxxxxxxx',
                ],
                'is_active' => false,
                'is_test_mode' => true,
            ],
        ];

        foreach ($gateways as $gateway) {
            PaymentGatewayModel::create($gateway);
        }
    }
}

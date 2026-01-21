<?php

namespace App\Enums;

enum PaymentGateway: string
{
    case Stripe = 'stripe';
    case PayPal = 'paypal';
    case Paystack = 'paystack';
    case Flutterwave = 'flutterwave';
    case Razorpay = 'razorpay';

    public function label(): string
    {
        return match ($this) {
            self::Stripe => 'Stripe',
            self::PayPal => 'PayPal',
            self::Paystack => 'Paystack',
            self::Flutterwave => 'Flutterwave',
            self::Razorpay => 'Razorpay',
        };
    }

    public function logo(): string
    {
        return match ($this) {
            self::Stripe => 'stripe.svg',
            self::PayPal => 'paypal.svg',
            self::Paystack => 'paystack.svg',
            self::Flutterwave => 'flutterwave.svg',
            self::Razorpay => 'razorpay.svg',
        };
    }
}

<?php

namespace Database\Seeders;

use App\Models\CardType;
use Illuminate\Database\Seeder;

class CardTypeSeeder extends Seeder
{
    public function run(): void
    {
        $cardTypes = [
            [
                'name' => 'Visa Classic Debit',
                'code' => 'VCLD',
                'is_virtual' => false,
                'is_credit' => false,
                'default_limit' => 500000000, // $5,000
                'is_active' => true,
            ],
            [
                'name' => 'Visa Gold Debit',
                'code' => 'VGLD',
                'is_virtual' => false,
                'is_credit' => false,
                'default_limit' => 1000000000, // $10,000
                'is_active' => true,
            ],
            [
                'name' => 'Mastercard Platinum Debit',
                'code' => 'MCPD',
                'is_virtual' => false,
                'is_credit' => false,
                'default_limit' => 2000000000, // $20,000
                'is_active' => true,
            ],
            [
                'name' => 'Virtual Debit Card',
                'code' => 'VIRT',
                'is_virtual' => true,
                'is_credit' => false,
                'default_limit' => 100000000, // $1,000
                'is_active' => true,
            ],
            [
                'name' => 'Visa Credit Card',
                'code' => 'VCCR',
                'is_virtual' => false,
                'is_credit' => true,
                'default_limit' => 500000000, // $5,000
                'is_active' => true,
            ],
            [
                'name' => 'Mastercard Credit Gold',
                'code' => 'MCCG',
                'is_virtual' => false,
                'is_credit' => true,
                'default_limit' => 1500000000, // $15,000
                'is_active' => true,
            ],
        ];

        foreach ($cardTypes as $type) {
            CardType::create($type);
        }
    }
}

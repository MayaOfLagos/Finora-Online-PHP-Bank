<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            [
                'name' => 'Chase Bank',
                'code' => 'CHAS',
                'routing_number' => '021000021',
                'swift_code' => 'CHASUS33XXX',
                'is_active' => true,
            ],
            [
                'name' => 'Bank of America',
                'code' => 'BOFA',
                'routing_number' => '026009593',
                'swift_code' => 'BOFAUS3NXXX',
                'is_active' => true,
            ],
            [
                'name' => 'Wells Fargo',
                'code' => 'WFBK',
                'routing_number' => '121000248',
                'swift_code' => 'WFBIUS6SXXX',
                'is_active' => true,
            ],
            [
                'name' => 'Citibank',
                'code' => 'CITI',
                'routing_number' => '021000089',
                'swift_code' => 'CITIUS33XXX',
                'is_active' => true,
            ],
            [
                'name' => 'US Bank',
                'code' => 'USBA',
                'routing_number' => '091000022',
                'swift_code' => 'USBKUS44XXX',
                'is_active' => true,
            ],
            [
                'name' => 'PNC Bank',
                'code' => 'PNCB',
                'routing_number' => '043000096',
                'swift_code' => 'PNCCUS33XXX',
                'is_active' => true,
            ],
            [
                'name' => 'Capital One',
                'code' => 'CPOL',
                'routing_number' => '056073502',
                'swift_code' => 'NFBKUS33XXX',
                'is_active' => true,
            ],
            [
                'name' => 'TD Bank',
                'code' => 'TDBA',
                'routing_number' => '031101266',
                'swift_code' => 'TDOMUS33XXX',
                'is_active' => true,
            ],
        ];

        foreach ($banks as $bank) {
            Bank::create($bank);
        }
    }
}

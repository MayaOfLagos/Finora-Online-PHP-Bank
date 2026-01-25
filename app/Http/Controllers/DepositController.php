<?php

namespace App\Http\Controllers;

use App\Enums\PaymentGatewayType;
use App\Models\Cryptocurrency;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DepositController extends Controller
{
    /**
     * Display deposits hub
     */
    public function index()
    {
        $user = Auth::user();

        // Get enabled gateways for mobile deposits (from payment_gateways table)
        $enabledGateways = PaymentGateway::where('is_active', true)
            ->where('type', PaymentGatewayType::AUTOMATIC->value)
            ->get()
            ->map(fn ($gw) => [
                'code' => $gw->code,
                'name' => $gw->name,
                'has_credentials' => isset($gw->credentials['public_key'])
                    || isset($gw->credentials['publishable_key'])
                    || isset($gw->credentials['client_id']),
            ])
            ->toArray();

        // Get active cryptocurrencies and wallets
        $cryptocurrencies = Cryptocurrency::where('is_active', true)
            ->with(['wallets' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get();

        // Check user permissions
        $canDeposit = $user->can_deposit;

        $depositMethods = [
            [
                'id' => 'mobile',
                'name' => 'Mobile Deposit',
                'description' => 'Deposit via payment gateways',
                'icon' => 'pi-credit-card',
                'color' => 'from-blue-500 to-blue-600',
                'route' => '/deposits/mobile',
                'enabled' => $canDeposit && count($enabledGateways) > 0,
                'features' => ['Instant funding', 'Multiple gateways', 'Secure payment'],
            ],
            [
                'id' => 'check',
                'name' => 'Check Deposit',
                'description' => 'Deposit by check via mobile',
                'icon' => 'pi-image',
                'color' => 'from-green-500 to-green-600',
                'route' => '/deposits/check',
                'enabled' => $canDeposit,
                'features' => ['Quick upload', 'Admin approval', 'Hold period'],
            ],
            [
                'id' => 'crypto',
                'name' => 'Crypto Deposit',
                'description' => 'Deposit cryptocurrency',
                'icon' => 'pi-bitcoin',
                'color' => 'from-orange-500 to-orange-600',
                'route' => '/deposits/crypto',
                'enabled' => $canDeposit && count($cryptocurrencies) > 0,
                'features' => ['Multiple coins', 'Admin verified', 'Auto-credited'],
            ],
        ];

        return Inertia::render('Deposits/Index', [
            'depositMethods' => $depositMethods,
            'enabledGateways' => $enabledGateways,
            'cryptocurrencies' => $cryptocurrencies,
        ]);
    }
}

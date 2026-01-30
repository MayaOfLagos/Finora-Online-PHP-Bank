<?php

namespace App\Http\Controllers;

use App\Enums\CardStatus;
use App\Http\Requests\StoreCardRequest;
use App\Models\BankAccount;
use App\Models\Card;
use App\Models\CardType;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class CardPageController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        $user->load([
            'cards.cardType',
            'cards.bankAccount.accountType',
            'bankAccounts.accountType',
        ]);

        $cards = $user->cards->map(function (Card $card) {
            return [
                'id' => $card->id,
                'uuid' => $card->uuid,
                'card_type' => $card->cardType?->name ?? 'Debit Card',
                'card_number' => $card->masked_card_number,
                'card_holder_name' => $card->card_holder_name,
                'expiry' => $card->expiry_month && $card->expiry_year
                    ? str_pad($card->expiry_month, 2, '0', STR_PAD_LEFT).'/'.substr($card->expiry_year, -2)
                    : null,
                'status' => $card->status->value,
                'status_label' => $card->status->label(),
                'status_color' => $card->status->color(),
                'is_virtual' => $card->is_virtual,
                'spending_limit' => $card->spending_limit,
                'daily_limit' => $card->daily_limit,
                'bank_account' => $card->bankAccount ? [
                    'id' => $card->bankAccount->id,
                    'uuid' => $card->bankAccount->uuid,
                    'name' => $card->bankAccount->accountType?->name,
                    'number' => $card->bankAccount->account_number,
                    'currency' => $card->bankAccount->currency,
                ] : null,
            ];
        });

        $cardTypes = CardType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (CardType $type) => [
                'id' => $type->id,
                'name' => $type->name,
                'code' => $type->code,
                'is_virtual' => $type->is_virtual,
                'is_credit' => $type->is_credit,
                'default_limit' => $type->default_limit,
                'default_limit_display' => $type->default_limit ? $type->default_limit / 100 : null,
            ]);

        $accounts = $user->bankAccounts
            ->where('is_active', true)
            ->map(fn (BankAccount $account) => [
                'id' => $account->id,
                'uuid' => $account->uuid,
                'name' => $account->accountType?->name ?? 'Account',
                'number' => $account->account_number,
                'currency' => $account->currency,
                'balance' => $account->balance,
                'is_primary' => $account->is_primary,
            ])
            ->values();

        $cardStats = [
            'total' => $user->cards->count(),
            'active' => $user->cards->where('status', CardStatus::Active)->count(),
            'virtual' => $user->cards->where('is_virtual', true)->count(),
            'physical' => $user->cards->where('is_virtual', false)->count(),
        ];

        return Inertia::render('Cards/Index', [
            'cards' => $cards,
            'cardTypes' => $cardTypes,
            'accounts' => $accounts,
            'cardStats' => $cardStats,
        ]);
    }

    public function show(Card $card): Response
    {
        abort_unless($card->user_id === Auth::id(), 404);

        $card->load(['cardType', 'bankAccount.accountType']);

        return Inertia::render('Cards/Show', [
            'card' => [
                'id' => $card->id,
                'uuid' => $card->uuid,
                'card_type' => $card->cardType?->name ?? 'Card',
                'card_type_code' => $card->cardType?->code,
                'card_number' => $card->masked_card_number,
                'card_holder_name' => $card->card_holder_name,
                'expiry' => $card->expiry_month && $card->expiry_year
                    ? str_pad($card->expiry_month, 2, '0', STR_PAD_LEFT).'/'.substr($card->expiry_year, -2)
                    : null,
                'status' => $card->status->value,
                'status_label' => $card->status->label(),
                'status_color' => $card->status->color(),
                'is_virtual' => $card->is_virtual,
                'spending_limit' => $card->spending_limit,
                'daily_limit' => $card->daily_limit,
                'bank_account' => $card->bankAccount ? [
                    'id' => $card->bankAccount->id,
                    'uuid' => $card->bankAccount->uuid,
                    'name' => $card->bankAccount->accountType?->name,
                    'number' => $card->bankAccount->account_number,
                    'currency' => $card->bankAccount->currency,
                ] : null,
            ],
        ]);
    }

    public function store(StoreCardRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $account = BankAccount::query()
            ->where('id', $validated['bank_account_id'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        $cardType = CardType::query()
            ->where('id', $validated['card_type_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $expiry = now()->addYears(3);

        $card = Card::create([
            'user_id' => $user->id,
            'bank_account_id' => $account->id,
            'card_type_id' => $cardType->id,
            'card_number' => $this->generateCardNumber($cardType->code),
            'card_holder_name' => $user->name,
            'expiry_month' => $expiry->format('m'),
            'expiry_year' => $expiry->format('Y'),
            'cvv' => str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT),
            'pin' => null,
            'spending_limit' => $cardType->default_limit,
            'daily_limit' => $cardType->default_limit,
            'status' => CardStatus::Active,
            'is_virtual' => $cardType->is_virtual,
            'issued_at' => now(),
            'expires_at' => $expiry,
        ]);

        // Notify admins about new card request
        AdminNotificationService::cardRequested($card, $user);

        return Redirect::back()->with('success', 'Your card request has been submitted.');
    }

    public function toggleFreeze(Card $card): RedirectResponse
    {
        abort_unless($card->user_id === Auth::id(), 404);

        $card->status = $card->status === CardStatus::Frozen
            ? CardStatus::Active
            : CardStatus::Frozen;

        $card->save();

        // Notify admins about card status change
        AdminNotificationService::cardStatusChanged($card, Auth::user(), $card->status->value);

        return Redirect::back()->with('success', $card->status === CardStatus::Frozen
            ? 'Card has been frozen.'
            : 'Card has been unfrozen.');
    }

    private function generateCardNumber(string $code): string
    {
        $prefix = str_starts_with(strtoupper($code), 'V') ? '4' : '5'.random_int(1, 5);

        do {
            $number = $prefix.str_pad((string) random_int(0, 99999999999999), 15 - strlen($prefix), '0', STR_PAD_LEFT);
            $number = $this->applyLuhnChecksum($number);
        } while (Card::where('card_number', $number)->exists());

        return $number;
    }

    private function applyLuhnChecksum(string $partial): string
    {
        $sum = 0;
        $reverseDigits = array_reverse(str_split($partial));

        foreach ($reverseDigits as $index => $digit) {
            $n = (int) $digit;
            if ($index % 2 === 1) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return $partial.$checkDigit;
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CardStatus;
use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Card;
use App\Models\CardTransaction;
use App\Models\CardType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CardController extends Controller
{
    /**
     * Get user's cards.
     */
    public function index(Request $request): JsonResponse
    {
        $cards = Card::where('user_id', $request->user()->id)
            ->with(['cardType', 'account'])
            ->latest()
            ->get()
            ->map(fn ($card) => $this->formatCard($card));

        return response()->json([
            'success' => true,
            'data' => [
                'cards' => $cards,
            ],
        ]);
    }

    /**
     * Request a new card.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'account_id' => 'required|exists:bank_accounts,uuid',
            'card_type_id' => 'required|exists:card_types,id',
        ]);

        $account = BankAccount::where('uuid', $request->account_id)
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        if (! $account) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid account.',
            ], 422);
        }

        $cardType = CardType::findOrFail($request->card_type_id);

        // Generate card number (for virtual cards, it's immediate; for physical, it would be assigned later)
        $cardNumber = $this->generateCardNumber($cardType);
        $expiryDate = now()->addYears(3);

        $card = Card::create([
            'uuid' => Str::uuid(),
            'user_id' => $request->user()->id,
            'account_id' => $account->id,
            'card_type_id' => $cardType->id,
            'card_number' => $cardNumber,
            'masked_number' => $this->maskCardNumber($cardNumber),
            'expiry_date' => $expiryDate,
            'cvv' => str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT),
            'cardholder_name' => strtoupper($request->user()->full_name),
            'status' => $cardType->is_virtual ? CardStatus::Active : CardStatus::Pending,
            'daily_limit' => $cardType->default_daily_limit,
            'monthly_limit' => $cardType->default_monthly_limit,
            'is_contactless_enabled' => true,
            'is_online_enabled' => true,
            'is_international_enabled' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => $cardType->is_virtual
                ? 'Virtual card created successfully.'
                : 'Card request submitted. You will receive your card within 7-10 business days.',
            'data' => [
                'card' => $this->formatCard($card),
            ],
        ], 201);
    }

    /**
     * Get card details.
     */
    public function show(Request $request, Card $card): JsonResponse
    {
        if ($card->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'card' => $this->formatCard($card->load(['cardType', 'account']), true),
            ],
        ]);
    }

    /**
     * Activate card.
     */
    public function activate(Request $request, Card $card): JsonResponse
    {
        if ($card->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($card->status !== CardStatus::Pending) {
            return response()->json([
                'success' => false,
                'message' => 'Card cannot be activated in its current state.',
            ], 422);
        }

        $request->validate([
            'last_four_digits' => 'required|string|size:4',
        ]);

        if (substr($card->card_number, -4) !== $request->last_four_digits) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid card digits.',
            ], 422);
        }

        $card->update([
            'status' => CardStatus::Active,
            'activated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Card activated successfully.',
            'data' => [
                'card' => $this->formatCard($card),
            ],
        ]);
    }

    /**
     * Freeze card.
     */
    public function freeze(Request $request, Card $card): JsonResponse
    {
        if ($card->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($card->status !== CardStatus::Active) {
            return response()->json([
                'success' => false,
                'message' => 'Card cannot be frozen in its current state.',
            ], 422);
        }

        $card->update([
            'status' => CardStatus::Frozen,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Card frozen successfully.',
            'data' => [
                'card' => $this->formatCard($card),
            ],
        ]);
    }

    /**
     * Unfreeze card.
     */
    public function unfreeze(Request $request, Card $card): JsonResponse
    {
        if ($card->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($card->status !== CardStatus::Frozen) {
            return response()->json([
                'success' => false,
                'message' => 'Card is not frozen.',
            ], 422);
        }

        $card->update([
            'status' => CardStatus::Active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Card unfrozen successfully.',
            'data' => [
                'card' => $this->formatCard($card),
            ],
        ]);
    }

    /**
     * Block card permanently.
     */
    public function block(Request $request, Card $card): JsonResponse
    {
        if ($card->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'reason' => 'required|string|in:lost,stolen,damaged,other',
            'description' => 'nullable|string|max:500',
        ]);

        $card->update([
            'status' => CardStatus::Blocked,
            'blocked_reason' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Card blocked permanently. Please request a new card.',
            'data' => [
                'card' => $this->formatCard($card),
            ],
        ]);
    }

    /**
     * Update card limits.
     */
    public function updateLimits(Request $request, Card $card): JsonResponse
    {
        if ($card->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'daily_limit' => 'nullable|numeric|min:0',
            'monthly_limit' => 'nullable|numeric|min:0',
            'is_contactless_enabled' => 'nullable|boolean',
            'is_online_enabled' => 'nullable|boolean',
            'is_international_enabled' => 'nullable|boolean',
        ]);

        $card->update([
            'daily_limit' => $request->daily_limit ? $request->daily_limit * 100 : $card->daily_limit,
            'monthly_limit' => $request->monthly_limit ? $request->monthly_limit * 100 : $card->monthly_limit,
            'is_contactless_enabled' => $request->is_contactless_enabled ?? $card->is_contactless_enabled,
            'is_online_enabled' => $request->is_online_enabled ?? $card->is_online_enabled,
            'is_international_enabled' => $request->is_international_enabled ?? $card->is_international_enabled,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Card limits updated successfully.',
            'data' => [
                'card' => $this->formatCard($card),
            ],
        ]);
    }

    /**
     * Change card PIN.
     */
    public function changePin(Request $request, Card $card): JsonResponse
    {
        if ($card->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'current_pin' => 'required|string|size:4',
            'new_pin' => 'required|string|size:4|confirmed',
        ]);

        if (! Hash::check($request->current_pin, $card->pin)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid current PIN.',
            ], 422);
        }

        $card->update([
            'pin' => Hash::make($request->new_pin),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Card PIN changed successfully.',
        ]);
    }

    /**
     * Get card transactions.
     */
    public function transactions(Request $request, Card $card): JsonResponse
    {
        if ($card->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $transactions = CardTransaction::where('card_id', $card->id)
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => [
                'transactions' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ],
            ],
        ]);
    }

    // ==================== HELPERS ====================

    private function formatCard(Card $card, bool $detailed = false): array
    {
        $data = [
            'uuid' => $card->uuid,
            'masked_number' => $card->masked_number,
            'cardholder_name' => $card->cardholder_name,
            'expiry_date' => $card->expiry_date->format('m/Y'),
            'type' => $card->cardType?->name,
            'brand' => $card->cardType?->brand,
            'is_virtual' => $card->cardType?->is_virtual ?? false,
            'status' => $card->status->value,
            'daily_limit' => $card->daily_limit / 100,
            'monthly_limit' => $card->monthly_limit / 100,
            'settings' => [
                'contactless' => $card->is_contactless_enabled,
                'online' => $card->is_online_enabled,
                'international' => $card->is_international_enabled,
            ],
        ];

        if ($detailed && $card->cardType?->is_virtual) {
            $data['card_number'] = $card->card_number;
            $data['cvv'] = $card->cvv;
        }

        return $data;
    }

    private function generateCardNumber(CardType $cardType): string
    {
        // Generate card number based on brand
        $prefix = match ($cardType->brand) {
            'Visa' => '4',
            'Mastercard' => '5'.rand(1, 5),
            'Amex' => '34',
            default => '4',
        };

        do {
            $number = $prefix.str_pad(rand(0, 999999999999999), 16 - strlen($prefix), '0', STR_PAD_LEFT);
            // Apply Luhn algorithm for valid card number
            $number = $this->generateLuhnValid($prefix);
        } while (Card::where('card_number', $number)->exists());

        return $number;
    }

    private function generateLuhnValid(string $prefix): string
    {
        $length = 16;
        $number = $prefix.str_pad(rand(0, pow(10, $length - strlen($prefix) - 1) - 1), $length - strlen($prefix) - 1, '0', STR_PAD_LEFT);

        // Calculate Luhn check digit
        $sum = 0;
        $length = strlen($number);
        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int) $number[$i];
            if (($length - $i) % 2 === 0) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return $number.$checkDigit;
    }

    private function maskCardNumber(string $number): string
    {
        return substr($number, 0, 4).' **** **** '.substr($number, -4);
    }
}

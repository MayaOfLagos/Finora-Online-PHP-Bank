<?php

namespace App\Observers;

use App\Models\CryptoDeposit;
use App\Models\TransactionHistory;

class CryptoDepositObserver
{
    public function created(CryptoDeposit $deposit): void
    {
        $this->logTransaction($deposit);
    }

    public function updated(CryptoDeposit $deposit): void
    {
        // Update transaction history when status changes
        if ($deposit->isDirty('status')) {
            $history = TransactionHistory::where('transactionable_type', CryptoDeposit::class)
                ->where('transactionable_id', $deposit->id)
                ->first();

            if ($history) {
                // Map deposit status to transaction history status
                $mappedStatus = match($deposit->status->value) {
                    'approved' => 'processing',
                    'rejected' => 'failed',
                    default => $deposit->status->value,
                };
                
                $history->update([
                    'status' => $mappedStatus,
                    'processed_at' => $deposit->status->value === 'completed' ? now() : null,
                ]);
            }
        }
    }

    protected function logTransaction(CryptoDeposit $deposit): void
    {
        TransactionHistory::create([
            'user_id' => $deposit->user_id,
            'transaction_type' => 'crypto_deposit',
            'reference_number' => $deposit->reference_number,
            'transactionable_type' => CryptoDeposit::class,
            'transactionable_id' => $deposit->id,
            'amount' => $deposit->usd_amount / 100, // Convert cents to dollars
            'currency' => 'USD',
            'status' => $deposit->status->value,
            'description' => "Crypto deposit - {$deposit->crypto_amount} crypto",
            'metadata' => [
                'cryptocurrency_id' => $deposit->cryptocurrency_id,
                'crypto_amount' => (string) $deposit->crypto_amount,
                'transaction_hash' => $deposit->transaction_hash,
            ],
        ]);
    }
}

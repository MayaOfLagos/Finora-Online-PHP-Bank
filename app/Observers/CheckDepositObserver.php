<?php

namespace App\Observers;

use App\Models\CheckDeposit;
use App\Models\TransactionHistory;

class CheckDepositObserver
{
    public function created(CheckDeposit $deposit): void
    {
        $this->logTransaction($deposit);
    }

    public function updated(CheckDeposit $deposit): void
    {
        // Update transaction history when status changes
        if ($deposit->isDirty('status')) {
            $history = TransactionHistory::where('transactionable_type', CheckDeposit::class)
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

    protected function logTransaction(CheckDeposit $deposit): void
    {
        TransactionHistory::create([
            'user_id' => $deposit->user_id,
            'transaction_type' => 'check_deposit',
            'reference_number' => $deposit->reference_number,
            'transactionable_type' => CheckDeposit::class,
            'transactionable_id' => $deposit->id,
            'amount' => $deposit->amount / 100, // Convert cents to dollars
            'currency' => $deposit->currency,
            'status' => $deposit->status->value,
            'description' => "Check deposit - Check #{$deposit->check_number}",
            'metadata' => [
                'check_number' => $deposit->check_number,
                'hold_until' => $deposit->hold_until?->toDateTimeString(),
            ],
        ]);
    }
}

<?php

namespace App\Observers;

use App\Models\MobileDeposit;
use App\Models\TransactionHistory;

class MobileDepositObserver
{
    public function created(MobileDeposit $deposit): void
    {
        $this->logTransaction($deposit);
    }

    public function updated(MobileDeposit $deposit): void
    {
        // Update transaction history when status changes
        if ($deposit->isDirty('status')) {
            $history = TransactionHistory::where('transactionable_type', MobileDeposit::class)
                ->where('transactionable_id', $deposit->id)
                ->first();

            if ($history) {
                // Map deposit status to transaction history status
                $mappedStatus = match ($deposit->status->value) {
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

    protected function logTransaction(MobileDeposit $deposit): void
    {
        TransactionHistory::create([
            'user_id' => $deposit->user_id,
            'transaction_type' => 'mobile_deposit',
            'reference_number' => $deposit->reference_number,
            'transactionable_type' => MobileDeposit::class,
            'transactionable_id' => $deposit->id,
            'amount' => $deposit->amount / 100, // Convert cents to dollars
            'currency' => $deposit->currency,
            'status' => $deposit->status->value,
            'description' => "Mobile deposit via {$deposit->gateway}",
            'metadata' => [
                'gateway' => $deposit->gateway,
                'gateway_transaction_id' => $deposit->gateway_transaction_id,
            ],
        ]);
    }
}

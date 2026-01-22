<?php

namespace App\Observers;

use App\Models\DomesticTransfer;
use App\Models\TransactionHistory;

class DomesticTransferObserver
{
    public function created(DomesticTransfer $transfer): void
    {
        $this->logTransaction($transfer);
    }

    public function updated(DomesticTransfer $transfer): void
    {
        // Update transaction history when status changes
        if ($transfer->isDirty('status')) {
            $history = TransactionHistory::where('transactionable_type', DomesticTransfer::class)
                ->where('transactionable_id', $transfer->id)
                ->first();

            if ($history) {
                $history->update([
                    'status' => $transfer->status->value,
                    'processed_at' => $transfer->status->value === 'completed' ? now() : null,
                ]);
            }
        }
    }

    protected function logTransaction(DomesticTransfer $transfer): void
    {
        TransactionHistory::create([
            'user_id' => $transfer->user_id,
            'transaction_type' => 'domestic_transfer',
            'reference_number' => $transfer->reference_number,
            'transactionable_type' => DomesticTransfer::class,
            'transactionable_id' => $transfer->id,
            'amount' => $transfer->amount / 100, // Convert cents to dollars
            'currency' => $transfer->currency,
            'status' => $transfer->status->value,
            'description' => "Domestic transfer to {$transfer->beneficiary_name} - {$transfer->beneficiary_account}",
            'metadata' => [
                'beneficiary_name' => $transfer->beneficiary_name,
                'beneficiary_account' => $transfer->beneficiary_account,
                'bank_id' => $transfer->bank_id,
            ],
        ]);
    }
}

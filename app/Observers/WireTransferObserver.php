<?php

namespace App\Observers;

use App\Models\TransactionHistory;
use App\Models\WireTransfer;

class WireTransferObserver
{
    public function created(WireTransfer $wireTransfer): void
    {
        $this->logTransaction($wireTransfer);
    }

    public function updated(WireTransfer $wireTransfer): void
    {
        // Update transaction history when status changes
        if ($wireTransfer->isDirty('status')) {
            $history = TransactionHistory::where('transactionable_type', WireTransfer::class)
                ->where('transactionable_id', $wireTransfer->id)
                ->first();

            if ($history) {
                $history->update([
                    'status' => $wireTransfer->status->value,
                    'processed_at' => $wireTransfer->status->value === 'completed' ? now() : null,
                ]);
            }
        }
    }

    protected function logTransaction(WireTransfer $wireTransfer): void
    {
        TransactionHistory::create([
            'user_id' => $wireTransfer->user_id,
            'transaction_type' => 'wire_transfer',
            'reference_number' => $wireTransfer->reference_number,
            'transactionable_type' => WireTransfer::class,
            'transactionable_id' => $wireTransfer->id,
            'amount' => $wireTransfer->amount / 100, // Convert cents to dollars
            'currency' => $wireTransfer->currency,
            'status' => $wireTransfer->status->value,
            'description' => "Wire transfer to {$wireTransfer->beneficiary_name} - {$wireTransfer->beneficiary_bank_name}",
            'metadata' => [
                'beneficiary_name' => $wireTransfer->beneficiary_name,
                'beneficiary_account' => $wireTransfer->beneficiary_account,
                'beneficiary_bank' => $wireTransfer->beneficiary_bank_name,
                'swift_code' => $wireTransfer->swift_code,
            ],
        ]);
    }
}

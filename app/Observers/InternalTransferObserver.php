<?php

namespace App\Observers;

use App\Models\InternalTransfer;
use App\Models\TransactionHistory;

class InternalTransferObserver
{
    public function created(InternalTransfer $transfer): void
    {
        $this->logTransaction($transfer);
    }

    public function updated(InternalTransfer $transfer): void
    {
        // Update transaction history when status changes
        if ($transfer->isDirty('status')) {
            $history = TransactionHistory::where('transactionable_type', InternalTransfer::class)
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

    protected function logTransaction(InternalTransfer $transfer): void
    {
        $receiverName = 'User ID: ' . $transfer->receiver_id;
        
        TransactionHistory::create([
            'user_id' => $transfer->sender_id,
            'transaction_type' => 'internal_transfer',
            'reference_number' => $transfer->reference_number,
            'transactionable_type' => InternalTransfer::class,
            'transactionable_id' => $transfer->id,
            'amount' => $transfer->amount / 100, // Convert cents to dollars
            'currency' => $transfer->currency,
            'status' => $transfer->status->value,
            'description' => "Internal transfer to {$receiverName}",
            'metadata' => [
                'sender_id' => $transfer->sender_id,
                'receiver_id' => $transfer->receiver_id,
                'sender_account_id' => $transfer->sender_account_id,
                'receiver_account_id' => $transfer->receiver_account_id,
            ],
        ]);
    }
}

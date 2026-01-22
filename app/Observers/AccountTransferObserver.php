<?php

namespace App\Observers;

use App\Models\AccountTransfer;
use App\Models\TransactionHistory;

class AccountTransferObserver
{
    public function created(AccountTransfer $transfer): void
    {
        $this->logTransaction($transfer);
    }

    public function updated(AccountTransfer $transfer): void
    {
        // Update transaction history when status changes
        if ($transfer->isDirty('status')) {
            $history = TransactionHistory::where('transactionable_type', AccountTransfer::class)
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

    protected function logTransaction(AccountTransfer $transfer): void
    {
        TransactionHistory::create([
            'user_id' => $transfer->user_id,
            'transaction_type' => 'account_transfer',
            'reference_number' => $transfer->reference_number,
            'transactionable_type' => AccountTransfer::class,
            'transactionable_id' => $transfer->id,
            'amount' => $transfer->amount / 100, // Convert cents to dollars
            'currency' => $transfer->currency,
            'status' => $transfer->status->value,
            'description' => "Account to account transfer - {$transfer->description}",
            'metadata' => [
                'from_account_id' => $transfer->from_account_id,
                'to_account_id' => $transfer->to_account_id,
            ],
        ]);
    }
}

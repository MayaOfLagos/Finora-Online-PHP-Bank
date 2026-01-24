<?php

namespace App\Policies;

use App\Models\TransactionHistory;
use App\Models\User;

class TransactionHistoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TransactionHistory $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }
}

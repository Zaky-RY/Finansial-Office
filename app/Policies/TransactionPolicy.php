<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isFinance() || $user->isDirector() || $user->isAdmin() || $user->isManager();
    }

    public function view(User $user, Transaction $transaction): bool
    {
        if ($user->isFinance() || $user->isDirector() || $user->isAdmin()) {
            return true;
        }

        if ($user->id === $transaction->created_by) {
            return true;
        }

        if ($user->isManager() && $user->department && $user->department === $transaction->department) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isFinance();
    }

    public function cancel(User $user, Transaction $transaction): bool
    {
        if ($transaction->isCancelled()) {
            return false;
        }

        return $user->isFinance() || $user->isDirector() || $user->isAdmin();
    }

    /**
     * DILARANG KERAS HARD DELETE pada data transaksi keuangan.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return false;
    }
}

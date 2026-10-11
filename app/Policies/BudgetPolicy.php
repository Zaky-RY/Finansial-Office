<?php

namespace App\Policies;

use App\Models\Budget;
use App\Models\User;

class BudgetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isFinance() || $user->isDirector() || $user->isAdmin() || $user->isManager();
    }

    public function view(User $user, Budget $budget): bool
    {
        if ($user->isFinance() || $user->isDirector() || $user->isAdmin()) {
            return true;
        }

        if ($user->isManager() && $user->department && $user->department === $budget->department) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isFinance() || $user->isAdmin();
    }

    public function update(User $user, Budget $budget): bool
    {
        return $user->isFinance() || $user->isAdmin();
    }

    public function close(User $user, Budget $budget): bool
    {
        return $user->isFinance() || $user->isDirector() || $user->isAdmin();
    }

    /**
     * Dilarang menghapus anggaran yang sudah memiliki realisasi pemakaian.
     */
    public function delete(User $user, Budget $budget): bool
    {
        if (bccomp((string) $budget->used_amount, '0.00', 2) > 0) {
            return false;
        }

        return ($user->isFinance() || $user->isAdmin()) && $budget->isDraft();
    }
}

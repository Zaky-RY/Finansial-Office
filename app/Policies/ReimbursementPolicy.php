<?php

namespace App\Policies;

use App\Enums\ReimbursementStatus;
use App\Models\Reimbursement;
use App\Models\User;

class ReimbursementPolicy
{
    /**
     * Hak melihat data reimbursement (IDOR prevention).
     */
    public function view(User $user, Reimbursement $reimbursement): bool
    {
        if ($user->id === $reimbursement->user_id) {
            return true;
        }

        if ($user->isFinance() || $user->isDirector() || $user->isAdmin()) {
            return true;
        }

        if ($user->isManager() && $user->department && $user->department === $reimbursement->department) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->status === 'active';
    }

    public function update(User $user, Reimbursement $reimbursement): bool
    {
        return $user->id === $reimbursement->user_id && $reimbursement->isEditable();
    }

    public function submit(User $user, Reimbursement $reimbursement): bool
    {
        return $user->id === $reimbursement->user_id && $reimbursement->isEditable();
    }

    /**
     * Evaluasi hak approval dengan penegakan mutlak ANTI-SELF-APPROVAL.
     */
    public function approve(User $user, Reimbursement $reimbursement): bool
    {
        // Pejabat dilarang menyetujui pengajuannya sendiri
        if ($user->id === $reimbursement->user_id) {
            return false;
        }

        if ($reimbursement->isPendingManagerApproval()) {
            return $user->isManager() || $user->isDirector();
        }

        if ($reimbursement->isPendingFinanceVerification()) {
            return $user->isFinance();
        }

        if ($reimbursement->isPendingDirectorApproval()) {
            return $user->isDirector();
        }

        return false;
    }

    public function reject(User $user, Reimbursement $reimbursement): bool
    {
        return $this->approve($user, $reimbursement);
    }

    public function returnForRevision(User $user, Reimbursement $reimbursement): bool
    {
        return $this->approve($user, $reimbursement);
    }

    public function pay(User $user, Reimbursement $reimbursement): bool
    {
        return $user->isFinance() && $reimbursement->status === ReimbursementStatus::APPROVED->value;
    }
}

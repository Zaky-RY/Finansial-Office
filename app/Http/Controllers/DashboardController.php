<?php

namespace App\Http\Controllers;

use App\Models\Reimbursement;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        // Metrik reimbursement pribadi
        $myReimbursementsCount = $user->reimbursements()->count();
        $myDraftCount = $user->reimbursements()->where('status', 'draft')->count();
        $myPendingCount = $user->reimbursements()->whereNotIn('status', ['draft', 'approved', 'paid', 'rejected', 'cancelled'])->count();
        $myApprovedCount = $user->reimbursements()->whereIn('status', ['approved', 'paid'])->count();
        $myRecentReimbursements = $user->reimbursements()->latest()->take(5)->get();

        // Metrik finansial kantor aktual (tersedia untuk Finance, Director, Admin, dan Manager)
        $financialMetrics = null;
        if ($user->isFinance() || $user->isDirector() || $user->isAdmin() || $user->isManager()) {
            $departmentFilter = $user->isManager() ? $user->department : null;
            $financialMetrics = $this->transactionService->getDashboardMetrics($departmentFilter);
        }

        // Hitungan dan daftar antrean approval spesifik role
        $rolePendingApprovalsCount = 0;
        $rolePendingApprovalsList = collect();
        if ($user->isManager() || $user->isFinance() || $user->isDirector()) {
            $approvalQuery = Reimbursement::query()->with(['user']);
            if ($user->isManager()) {
                $approvalQuery->where('status', 'waiting_manager_approval')
                    ->where('department', $user->department)
                    ->where('user_id', '!=', $user->id);
            } elseif ($user->isFinance()) {
                $approvalQuery->where('status', 'waiting_finance_verification')
                    ->where('user_id', '!=', $user->id);
            } elseif ($user->isDirector()) {
                $approvalQuery->where('status', 'waiting_director_approval')
                    ->where('user_id', '!=', $user->id);
            }
            $rolePendingApprovalsCount = (clone $approvalQuery)->count();
            $rolePendingApprovalsList = $approvalQuery->latest()->take(5)->get();
        }

        return view('dashboard', compact(
            'user',
            'myReimbursementsCount',
            'myDraftCount',
            'myPendingCount',
            'myApprovedCount',
            'myRecentReimbursements',
            'financialMetrics',
            'rolePendingApprovalsCount',
            'rolePendingApprovalsList'
        ));
    }
}

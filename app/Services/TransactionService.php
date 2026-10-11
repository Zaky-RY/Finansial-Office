<?php

namespace App\Services;

use App\Models\Reimbursement;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionService
{
    public function __construct(
        protected ?AuditLogService $auditLogService = null,
        protected ?BudgetService $budgetService = null
    ) {
        $this->auditLogService ??= app(AuditLogService::class);
        $this->budgetService ??= app(BudgetService::class);
    }

    /**
     * Hitung total pemasukan aktif, pengeluaran aktif, dan saldo berjalan.
     * Formula: Saldo = Total Pemasukan Aktif - Total Pengeluaran Aktif
     * Transaksi dengan status 'cancelled' tidak dihitung ke saldo.
     *
     * @return array{
     *     total_income: string,
     *     total_expense: string,
     *     balance: string,
     *     formatted_income: string,
     *     formatted_expense: string,
     *     formatted_balance: string
     * }
     */
    public function calculateBalance(?string $department = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $baseQuery = Transaction::query()->active();

        if ($department) {
            $baseQuery->where('department', $department);
        }

        if ($startDate && $endDate) {
            $baseQuery->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        $incomeQuery = (clone $baseQuery)->income();
        $expenseQuery = (clone $baseQuery)->expense();

        $rawIncome = (string) ($incomeQuery->sum('amount') ?? '0');
        $rawExpense = (string) ($expenseQuery->sum('amount') ?? '0');

        $totalIncome = bcadd($rawIncome, '0', 2);
        $totalExpense = bcadd($rawExpense, '0', 2);

        // Gunakan bcsub untuk presisi mutlak tanpa floating-point error
        $balance = bcsub($totalIncome, $totalExpense, 2);

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance' => $balance,
            'formatted_income' => $this->formatRupiah($totalIncome),
            'formatted_expense' => $this->formatRupiah($totalExpense),
            'formatted_balance' => $this->formatRupiah($balance),
        ];
    }

    /**
     * Format nominal ke format standar Rupiah Indonesia (contoh: Rp 1.500.000,00).
     */
    public function formatRupiah(string|float|int $amount): string
    {
        $number = (float) $amount;
        $isNegative = $number < 0;
        $formatted = number_format(abs($number), 2, ',', '.');

        return ($isNegative ? '-Rp ' : 'Rp ').$formatted;
    }

    /**
     * Catat transaksi pemasukan atau pengeluaran kas kantor secara aman.
     */
    public function createTransaction(array $data, User $creator, ?UploadedFile $attachment = null): Transaction
    {
        return DB::transaction(function () use ($data, $creator, $attachment) {
            $attachmentPath = null;
            if ($attachment) {
                $storedName = Str::uuid().'.'.$attachment->getClientOriginalExtension();
                $attachmentPath = $attachment->storeAs('transactions', $storedName, 'local');
            }

            $amount = bcadd('0.00', (string) $data['amount'], 2);

            if (bccomp($amount, '0.00', 2) <= 0) {
                throw new \InvalidArgumentException('Nominal transaksi harus berupa angka positif lebih besar dari 0.');
            }

            $budgetId = $data['budget_id'] ?? null;
            if ($budgetId && $data['type'] === 'expense') {
                $budget = \App\Models\Budget::find($budgetId);
                if ($budget) {
                    $this->budgetService->recordUsage($budget, $amount);
                }
            }

            $transaction = Transaction::create([
                'type' => $data['type'],
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'amount' => $amount,
                'currency' => $data['currency'] ?? 'IDR',
                'category' => $data['category'],
                'department' => $data['department'] ?? null,
                'project' => $data['project'] ?? null,
                'vendor' => $data['vendor'] ?? null,
                'payment_method' => $data['payment_method'] ?? 'Bank Transfer',
                'payment_status' => $data['payment_status'] ?? 'paid',
                'description' => $data['description'] ?? null,
                'source_reference' => $data['source_reference'] ?? null,
                'reimbursement_id' => $data['reimbursement_id'] ?? null,
                'budget_id' => $budgetId,
                'created_by' => $creator->id,
                'attachment_path' => $attachmentPath,
            ]);

            $this->auditLogService->log(
                $creator->id,
                'created',
                'transaction',
                $transaction,
                null,
                [
                    'transaction_number' => $transaction->transaction_number,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'payment_status' => $transaction->payment_status,
                    'budget_id' => $budgetId,
                ]
            );

            return $transaction;
        });
    }

    /**
     * Membatalkan transaksi keuangan dengan alasan wajib (dilarang keras hard delete).
     */
    public function cancelTransaction(Transaction $transaction, User $canceller, string $reason): Transaction
    {
        $reason = trim($reason);
        if (empty($reason)) {
            throw new \InvalidArgumentException('Alasan pembatalan transaksi wajib diisi.');
        }

        if ($transaction->isCancelled()) {
            throw new \DomainException('Transaksi ini sudah berstatus dibatalkan sebelumnya.');
        }

        return DB::transaction(function () use ($transaction, $canceller, $reason) {
            $oldValues = [
                'payment_status' => $transaction->payment_status,
                'cancelled_by' => $transaction->cancelled_by,
                'cancelled_at' => $transaction->cancelled_at,
            ];

            // Kembalikan pemakaian anggaran jika transaksi pengeluaran ini memotong anggaran
            if ($transaction->type === 'expense' && $transaction->budget_id) {
                $budget = \App\Models\Budget::find($transaction->budget_id);
                if ($budget) {
                    $this->budgetService->releaseUsage($budget, (string) $transaction->amount);
                }
            }

            $transaction->update([
                'payment_status' => 'cancelled',
                'cancelled_by' => $canceller->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            $this->auditLogService->log(
                $canceller->id,
                'cancelled',
                'transaction',
                $transaction,
                $oldValues,
                [
                    'payment_status' => 'cancelled',
                    'cancellation_reason' => $reason,
                    'cancelled_by' => $canceller->id,
                ]
            );

            return $transaction->fresh();
        });
    }

    /**
     * Mengumpulkan metrik ringkasan untuk dashboard eksekutif dan operasional.
     */
    public function getDashboardMetrics(?string $department = null): array
    {
        $balanceData = $this->calculateBalance($department);
        $budgetMetrics = $this->budgetService->getOverallBudgetMetrics($department);

        $pendingApprovalsQuery = Reimbursement::query()->whereIn('status', [
            'waiting_manager_approval',
            'waiting_finance_verification',
            'waiting_director_approval',
            'submitted',
        ]);

        if ($department) {
            $pendingApprovalsQuery->where('department', $department);
        }

        $pendingApprovalsCount = $pendingApprovalsQuery->count();

        $recentTransactions = Transaction::query()
            ->with(['creator', 'reimbursement', 'budget'])
            ->latest('transaction_date')
            ->latest('id')
            ->take(8)
            ->get();

        return array_merge($balanceData, [
            'pending_approvals_count' => $pendingApprovalsCount,
            'recent_transactions' => $recentTransactions,
            'budget_metrics' => $budgetMetrics,
        ]);
    }
}

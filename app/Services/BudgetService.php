<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    public function __construct(
        protected ?AuditLogService $auditLogService = null
    ) {
        $this->auditLogService ??= app(AuditLogService::class);
    }

    /**
     * Hitung ringkasan sisa anggaran dan status peringatan.
     *
     * @return array{
     *     allocated_amount: string,
     *     used_amount: string,
     *     remaining_amount: string,
     *     usage_percentage: float,
     *     alert_status: string,
     *     formatted_allocated: string,
     *     formatted_used: string,
     *     formatted_remaining: string
     * }
     */
    public function getBudgetSummary(Budget $budget): array
    {
        $allocated = (string) $budget->allocated_amount;
        $used = (string) $budget->used_amount;
        $remaining = $budget->remaining_amount;
        $percentage = $budget->usage_percentage;
        $alert = $budget->alert_status;

        return [
            'allocated_amount' => $allocated,
            'used_amount' => $used,
            'remaining_amount' => $remaining,
            'usage_percentage' => $percentage,
            'alert_status' => $alert,
            'formatted_allocated' => $this->formatRupiah($allocated),
            'formatted_used' => $this->formatRupiah($used),
            'formatted_remaining' => $this->formatRupiah($remaining),
        ];
    }

    /**
     * Buat alokasi anggaran baru secara aman dan atomik.
     */
    public function createBudget(array $data, User $creator): Budget
    {
        return DB::transaction(function () use ($data, $creator) {
            $allocated = bcadd('0.00', (string) $data['allocated_amount'], 2);

            if (bccomp($allocated, '0.00', 2) <= 0) {
                throw new \InvalidArgumentException('Nominal alokasi anggaran harus berupa angka positif lebih besar dari 0.');
            }

            $budget = Budget::create([
                'name' => $data['name'],
                'department' => $data['department'],
                'category' => $data['category'] ?? null,
                'project' => $data['project'] ?? null,
                'period_start' => $data['period_start'],
                'period_end' => $data['period_end'],
                'allocated_amount' => $allocated,
                'used_amount' => '0.00',
                'status' => $data['status'] ?? 'active',
                'created_by' => $creator->id,
            ]);

            $this->auditLogService->log(
                $creator->id,
                'created',
                'budget',
                $budget,
                null,
                [
                    'name' => $budget->name,
                    'department' => $budget->department,
                    'allocated_amount' => $budget->allocated_amount,
                    'period' => "{$budget->period_start} to {$budget->period_end}",
                ]
            );

            return $budget;
        });
    }

    /**
     * Perbarui alokasi atau informasi anggaran dengan audit trail.
     */
    public function updateBudget(Budget $budget, array $data, User $updater): Budget
    {
        return DB::transaction(function () use ($budget, $data, $updater) {
            $oldValues = [
                'name' => $budget->name,
                'department' => $budget->department,
                'allocated_amount' => (string) $budget->allocated_amount,
                'status' => $budget->status,
                'period_start' => (string) $budget->period_start,
                'period_end' => (string) $budget->period_end,
            ];

            if (isset($data['allocated_amount'])) {
                $newAllocated = bcadd('0.00', (string) $data['allocated_amount'], 2);
                if (bccomp($newAllocated, '0.00', 2) <= 0) {
                    throw new \InvalidArgumentException('Alokasi anggaran harus bernilai positif.');
                }

                // Jangan izinkan penurunan alokasi di bawah nilai yang sudah terserap/terpakai
                if (bccomp($newAllocated, (string) $budget->used_amount, 2) < 0) {
                    throw new \DomainException('Alokasi anggaran baru tidak boleh lebih kecil dari nominal yang sudah terpakai (Rp '.number_format((float) $budget->used_amount, 2, ',', '.').').');
                }

                $data['allocated_amount'] = $newAllocated;
            }

            $budget->update($data);

            $this->auditLogService->log(
                $updater->id,
                'updated',
                'budget',
                $budget,
                $oldValues,
                [
                    'name' => $budget->name,
                    'allocated_amount' => (string) $budget->allocated_amount,
                    'status' => $budget->status,
                ]
            );

            return $budget->fresh();
        });
    }

    /**
     * Catat penggunaan anggaran secara atomik.
     */
    public function recordUsage(Budget $budget, string $amount): Budget
    {
        return DB::transaction(function () use ($budget, $amount) {
            $newUsed = bcadd((string) $budget->used_amount, (string) $amount, 2);
            $budget->update([
                'used_amount' => $newUsed,
            ]);

            return $budget->fresh();
        });
    }

    /**
     * Kembalikan alokasi anggaran saat transaksi pengeluaran dibatalkan.
     */
    public function releaseUsage(Budget $budget, string $amount): Budget
    {
        return DB::transaction(function () use ($budget, $amount) {
            $diff = bcsub((string) $budget->used_amount, (string) $amount, 2);
            $newUsed = bccomp($diff, '0.00', 2) < 0 ? '0.00' : $diff;

            $budget->update([
                'used_amount' => $newUsed,
            ]);

            return $budget->fresh();
        });
    }

    /**
     * Tutup periode anggaran (status = closed).
     */
    public function closeBudget(Budget $budget, User $user): Budget
    {
        return DB::transaction(function () use ($budget, $user) {
            $oldStatus = $budget->status;
            $budget->update(['status' => 'closed']);

            $this->auditLogService->log(
                $user->id,
                'closed',
                'budget',
                $budget,
                ['status' => $oldStatus],
                ['status' => 'closed']
            );

            return $budget->fresh();
        });
    }

    /**
     * Ambil metrik ringkasan anggaran keseluruhan atau divisi tertentu.
     */
    public function getOverallBudgetMetrics(?string $department = null): array
    {
        $query = Budget::query()->active();
        if ($department) {
            $query->where('department', $department);
        }

        $budgets = $query->get();

        $totalAllocated = '0.00';
        $totalUsed = '0.00';
        $warningCount = 0;
        $exhaustedCount = 0;

        foreach ($budgets as $b) {
            $totalAllocated = bcadd($totalAllocated, (string) $b->allocated_amount, 2);
            $totalUsed = bcadd($totalUsed, (string) $b->used_amount, 2);

            if ($b->alert_status === Budget::ALERT_EXHAUSTED) {
                $exhaustedCount++;
            } elseif (in_array($b->alert_status, [Budget::ALERT_WARNING, Budget::ALERT_HIGH_WARNING], true)) {
                $warningCount++;
            }
        }

        $totalRemaining = bcsub($totalAllocated, $totalUsed, 2);
        $overallPercentage = 0.0;
        if (bccomp($totalAllocated, '0.00', 2) > 0) {
            $ratio = (float) bcdiv($totalUsed, $totalAllocated, 4);
            $overallPercentage = round($ratio * 100, 2);
        }

        return [
            'total_allocated' => $totalAllocated,
            'total_used' => $totalUsed,
            'total_remaining' => $totalRemaining,
            'overall_percentage' => $overallPercentage,
            'warning_count' => $warningCount,
            'exhausted_count' => $exhaustedCount,
            'formatted_total_allocated' => $this->formatRupiah($totalAllocated),
            'formatted_total_used' => $this->formatRupiah($totalUsed),
            'formatted_total_remaining' => $this->formatRupiah($totalRemaining),
            'active_budgets_count' => $budgets->count(),
        ];
    }

    /**
     * Format nominal ke format Rupiah standar Indonesia.
     */
    public function formatRupiah(string|float|int $amount): string
    {
        $number = (float) $amount;
        $isNegative = $number < 0;
        $formatted = number_format(abs($number), 2, ',', '.');

        return ($isNegative ? '-Rp ' : 'Rp ').$formatted;
    }
}

<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Reimbursement;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Terapkan pembatasan hak akses divisi (Manager hanya boleh mengakses divisi naungannya).
     */
    protected function applyDepartmentScope(array &$filters, User $user): void
    {
        if ($user->isManager() && ! $user->isFinance() && ! $user->isDirector() && ! $user->isAdmin()) {
            $filters['department'] = $user->department;
        }
    }

    /**
     * Ambil data laporan arus kas (pemasukan & pengeluaran kas).
     */
    public function getCashFlowReport(array $filters, User $user, bool $paginate = true, int $perPage = 20, bool $cursor = false): array
    {
        $this->applyDepartmentScope($filters, $user);

        $query = Transaction::query()->with(['creator', 'reimbursement', 'budget']);

        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->whereBetween('transaction_date', [$filters['start_date'], $filters['end_date']]);
        } elseif (! empty($filters['start_date'])) {
            $query->where('transaction_date', '>=', $filters['start_date']);
        } elseif (! empty($filters['end_date'])) {
            $query->where('transaction_date', '<=', $filters['end_date']);
        }

        if (! empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['vendor'])) {
            $query->where('vendor', 'like', "%{$filters['vendor']}%");
        }

        if (! empty($filters['status'])) {
            $query->where('payment_status', $filters['status']);
        }

        if (! empty($filters['q'])) {
            $search = $filters['q'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('vendor', 'like', "%{$search}%")
                    ->orWhere('source_reference', 'like', "%{$search}%");
            });
        }

        // Kalkulasi Agregasi Moneter (Hanya transaksi aktif / tidak dibatalkan yang masuk saldo)
        $activeQuery = (clone $query)->active();

        $rawIncome = (string) ((clone $activeQuery)->income()->sum('amount') ?? '0');
        $rawExpense = (string) ((clone $activeQuery)->expense()->sum('amount') ?? '0');

        $totalIncome = bcadd($rawIncome, '0', 2);
        $totalExpense = bcadd($rawExpense, '0', 2);
        $netBalance = bcsub($totalIncome, $totalExpense, 2);

        $orderedQuery = $query->latest('transaction_date')->latest('id');

        $items = $cursor
            ? $orderedQuery->cursor()
            : ($paginate ? $orderedQuery->paginate($perPage)->withQueryString() : $orderedQuery->get());

        return [
            'items' => $items,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_balance' => $netBalance,
            'formatted_income' => $this->formatRupiah($totalIncome),
            'formatted_expense' => $this->formatRupiah($totalExpense),
            'formatted_balance' => $this->formatRupiah($netBalance),
            'total_count' => $cursor ? (clone $query)->count() : ($paginate ? $items->total() : $items->count()),
        ];
    }

    /**
     * Ambil data laporan reimbursement karyawan.
     */
    public function getReimbursementReport(array $filters, User $user, bool $paginate = true, int $perPage = 20, bool $cursor = false): array
    {
        $this->applyDepartmentScope($filters, $user);

        $query = Reimbursement::query()->with(['user', 'approvals.approver', 'items', 'transaction']);

        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->whereBetween('created_at', [
                Carbon::parse($filters['start_date'])->startOfDay(),
                Carbon::parse($filters['end_date'])->endOfDay(),
            ]);
        } elseif (! empty($filters['start_date'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['start_date'])->startOfDay());
        } elseif (! empty($filters['end_date'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['end_date'])->endOfDay());
        }

        if (! empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['q'])) {
            $search = $filters['q'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        // Agregasi Laporan Reimbursement
        $totalCount = (clone $query)->count();
        $rawTotalAmount = (string) ((clone $query)->sum('total_amount') ?? '0');
        $totalAmount = bcadd($rawTotalAmount, '0', 2);

        $rawPaidAmount = (string) ((clone $query)->whereIn('status', ['paid', 'completed'])->sum('total_amount') ?? '0');
        $paidAmount = bcadd($rawPaidAmount, '0', 2);

        $pendingCount = (clone $query)->whereNotIn('status', ['draft', 'approved', 'paid', 'completed', 'rejected', 'cancelled'])->count();

        $orderedQuery = $query->latest('created_at')->latest('id');
        $items = $cursor
            ? $orderedQuery->cursor()
            : ($paginate ? $orderedQuery->paginate($perPage)->withQueryString() : $orderedQuery->get());

        return [
            'items' => $items,
            'total_count' => $totalCount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'pending_count' => $pendingCount,
            'formatted_total_amount' => $this->formatRupiah($totalAmount),
            'formatted_paid_amount' => $this->formatRupiah($paidAmount),
        ];
    }

    /**
     * Ambil data laporan realisasi anggaran divisi.
     */
    public function getBudgetReport(array $filters, User $user, bool $paginate = true, int $perPage = 20, bool $cursor = false): array
    {
        $this->applyDepartmentScope($filters, $user);

        $query = Budget::query()->with('creator');

        if (! empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['q'])) {
            $search = $filters['q'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('project', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $allBudgets = (clone $query)->get();
        $totalAllocated = '0.00';
        $totalUsed = '0.00';

        foreach ($allBudgets as $b) {
            $totalAllocated = bcadd($totalAllocated, (string) $b->allocated_amount, 2);
            $totalUsed = bcadd($totalUsed, (string) $b->used_amount, 2);
        }

        $totalRemaining = bcsub($totalAllocated, $totalUsed, 2);
        $overallPercentage = 0.0;
        if (bccomp($totalAllocated, '0.00', 2) > 0) {
            $ratio = (float) bcdiv($totalUsed, $totalAllocated, 4);
            $overallPercentage = round($ratio * 100, 2);
        }

        $orderedQuery = $query->latest('period_start')->latest('id');
        $items = $cursor
            ? $orderedQuery->cursor()
            : ($paginate ? $orderedQuery->paginate($perPage)->withQueryString() : $orderedQuery->get());

        return [
            'items' => $items,
            'total_allocated' => $totalAllocated,
            'total_used' => $totalUsed,
            'total_remaining' => $totalRemaining,
            'overall_percentage' => $overallPercentage,
            'formatted_allocated' => $this->formatRupiah($totalAllocated),
            'formatted_used' => $this->formatRupiah($totalUsed),
            'formatted_remaining' => $this->formatRupiah($totalRemaining),
        ];
    }

    /**
     * Ekspor laporan ke format CSV / Excel secara streaming dengan UTF-8 BOM.
     */
    public function exportToCsv(string $reportType, array $filters, User $user): StreamedResponse
    {
        $startDateStr = $filters['start_date'] ?? 'awal';
        $endDateStr = $filters['end_date'] ?? 'akhir';
        $filename = "laporan-{$reportType}-{$startDateStr}-sampai-{$endDateStr}.csv";

        // Catat aktivitas ekspor ke audit log
        $this->auditLogService->log(
            $user->id,
            'exported',
            'report',
            null,
            null,
            [
                'report_type' => $reportType,
                'filters' => $filters,
                'filename' => $filename,
            ]
        );

        $response = new StreamedResponse(function () use ($reportType, $filters, $user) {
            $handle = fopen('php://output', 'w');

            // Tambahkan UTF-8 Byte Order Mark (BOM) agar simbol Rupiah dan karakter terbaca rapi di Microsoft Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            if ($reportType === 'cashflow') {
                $report = $this->getCashFlowReport($filters, $user, false, 20, true);
                fputcsv($handle, ['LAPORAN ARUS KAS DAN MUTASI TRANSAKSI']);
                fputcsv($handle, ['Waktu Unduh', now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s').' WIB']);
                fputcsv($handle, ['Pengunduh', $user->name.' ('.$user->role.')']);
                fputcsv($handle, ['Total Pemasukan', $report['formatted_income']]);
                fputcsv($handle, ['Total Pengeluaran', $report['formatted_expense']]);
                fputcsv($handle, ['Saldo Kas Berjalan', $report['formatted_balance']]);
                fputcsv($handle, []);
                fputcsv($handle, [
                    'No. Transaksi',
                    'Tanggal',
                    'Jenis',
                    'Kategori',
                    'Divisi',
                    'Vendor / Penerima',
                    'Metode Pembayaran',
                    'Nominal (IDR)',
                    'Status',
                    'Keterangan',
                    'No. Referensi',
                ]);

                foreach ($report['items'] as $trx) {
                    fputcsv($handle, [
                        $trx->transaction_number,
                        $trx->transaction_date ? Carbon::parse($trx->transaction_date)->format('d/m/Y') : '',
                        $trx->isIncome() ? 'Pemasukan' : 'Pengeluaran',
                        $trx->category,
                        $trx->department ?? 'Umum',
                        $trx->vendor ?? '-',
                        $trx->payment_method ?? '-',
                        ($trx->isIncome() ? '' : '-').(float) $trx->amount,
                        $trx->status_label,
                        $trx->description ?? '',
                        $trx->source_reference ?? '',
                    ]);
                }
            } elseif ($reportType === 'reimbursement') {
                $report = $this->getReimbursementReport($filters, $user, false, 20, true);
                fputcsv($handle, ['LAPORAN PENGAJUAN REIMBURSEMENT']);
                fputcsv($handle, ['Waktu Unduh', now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s').' WIB']);
                fputcsv($handle, ['Pengunduh', $user->name.' ('.$user->role.')']);
                fputcsv($handle, ['Total Nilai Pengajuan', $report['formatted_total_amount']]);
                fputcsv($handle, ['Total Terealisasi (Paid)', $report['formatted_paid_amount']]);
                fputcsv($handle, []);
                fputcsv($handle, [
                    'Nomor Pengajuan',
                    'Tanggal Submit',
                    'Nama Pemohon',
                    'Divisi',
                    'Kategori',
                    'Keperluan',
                    'Total Nominal (IDR)',
                    'Status Approval',
                    'Metode Bayar',
                ]);

                foreach ($report['items'] as $r) {
                    fputcsv($handle, [
                        $r->reimbursement_number,
                        $r->created_at ? $r->created_at->format('d/m/Y') : '',
                        $r->user->name ?? '-',
                        $r->department ?? '-',
                        $r->category ?? '-',
                        $r->purpose,
                        (float) $r->total_amount,
                        $r->status_label,
                        $r->payment_method ?? '-',
                    ]);
                }
            } elseif ($reportType === 'budget') {
                $report = $this->getBudgetReport($filters, $user, false, 20, true);
                fputcsv($handle, ['LAPORAN REALISASI ANGGARAN (BUDGET)']);
                fputcsv($handle, ['Waktu Unduh', now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s').' WIB']);
                fputcsv($handle, ['Pengunduh', $user->name.' ('.$user->role.')']);
                fputcsv($handle, ['Total Pagu Alokasi', $report['formatted_allocated']]);
                fputcsv($handle, ['Total Dana Terpakai', $report['formatted_used']]);
                fputcsv($handle, ['Sisa Pagu', $report['formatted_remaining']]);
                fputcsv($handle, ['Rata-rata Serapan', $report['overall_percentage'].'%']);
                fputcsv($handle, []);
                fputcsv($handle, [
                    'Nama Pos Anggaran',
                    'Divisi',
                    'Kategori',
                    'Proyek',
                    'Periode Mulai',
                    'Periode Selesai',
                    'Alokasi (IDR)',
                    'Terpakai (IDR)',
                    'Sisa Dana (IDR)',
                    'Serapan (%)',
                    'Peringatan',
                    'Status',
                ]);

                foreach ($report['items'] as $b) {
                    fputcsv($handle, [
                        $b->name,
                        $b->department,
                        $b->category ?? '-',
                        $b->project ?? '-',
                        $b->period_start ? $b->period_start->format('d/m/Y') : '',
                        $b->period_end ? $b->period_end->format('d/m/Y') : '',
                        (float) $b->allocated_amount,
                        (float) $b->used_amount,
                        (float) $b->remaining_amount,
                        $b->usage_percentage.'%',
                        $b->alert_label,
                        $b->status_label,
                    ]);
                }
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * Format Rupiah standar Indonesia.
     */
    public function formatRupiah(string|float|int $amount): string
    {
        $number = (float) $amount;
        $isNegative = $number < 0;
        $formatted = number_format(abs($number), 2, ',', '.');

        return ($isNegative ? '-Rp ' : 'Rp ').$formatted;
    }
}

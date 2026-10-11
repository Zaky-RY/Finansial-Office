<?php

namespace App\Services;

use App\Enums\ReimbursementStatus;
use App\Models\Approval;
use App\Models\Reimbursement;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    public const DIRECTOR_THRESHOLD = '5000000.00'; // Rp 5.000.000,00

    public function __construct(
        protected AuditLogService $auditLogService,
        protected NotificationService $notificationService,
        protected BudgetService $budgetService
    ) {}

    /**
     * Ajukan draft reimbursement ke antrean approval manager.
     */
    public function submit(Reimbursement $reimbursement, User $actor): void
    {
        if ($actor->id !== $reimbursement->user_id) {
            throw new AuthorizationException('Hanya pemohon yang dapat mengirimkan pengajuan reimbursement miliknya.');
        }

        if (! $reimbursement->isEditable()) {
            throw new \DomainException('Pengajuan ini sedang diproses atau sudah disetujui sehingga tidak dapat dikirim ulang.');
        }

        // Hitung ulang total amount dari item-item
        $reimbursement->calculateTotal();

        if (bccomp((string) $reimbursement->total_amount, '0.00', 2) <= 0) {
            throw new \InvalidArgumentException('Nominal pengajuan reimbursement harus lebih besar dari 0.');
        }

        if (! $reimbursement->hasAttachment()) {
            throw new \InvalidArgumentException('Bukti transaksi atau nota wajib diunggah sebelum pengajuan dikirim.');
        }

        $oldStatus = $reimbursement->status;

        DB::transaction(function () use ($reimbursement, $oldStatus, $actor) {
            $reimbursement->status = ReimbursementStatus::WAITING_MANAGER_APPROVAL->value;
            $reimbursement->submitted_at = now();
            $reimbursement->submission_date = now();
            $reimbursement->save();

            $this->auditLogService->log(
                $actor->id,
                'submitted',
                'reimbursement',
                $reimbursement,
                ['status' => $oldStatus],
                [
                    'status' => $reimbursement->status,
                    'total_amount' => $reimbursement->total_amount,
                    'is_manager_claim' => $actor->isManager(),
                ]
            );

            if ($actor->isManager()) {
                // Notifikasi ke Pemohon (Manager) mengenai pengalihan ke Direktur
                $this->notificationService->notify(
                    $reimbursement->user_id,
                    'Pengajuan Dikirim (Eskalasi Direktur)',
                    "Pengajuan {$reimbursement->number} berhasil dikirim. Sesuai kebijakan Anti-Self-Approval, persetujuan klaim Anda dialihkan langsung ke Direktur.",
                    route('reimbursements.show', $reimbursement)
                );

                // Notifikasi ke seluruh Direktur yang aktif
                $directors = User::where('role', 'director')->where('status', 'active')->get();
                foreach ($directors as $director) {
                    $this->notificationService->notify(
                        $director->id,
                        'Eskalasi Approval: Klaim Manager',
                        "Pengajuan {$reimbursement->number} (Rp ".number_format((float) $reimbursement->total_amount, 2, ',', '.').") diajukan oleh Manager {$actor->name} ({$actor->department}) dan membutuhkan persetujuan Direktur.",
                        route('reimbursements.show', $reimbursement)
                    );
                }
            } else {
                // Notifikasi reguler ke pemohon
                $this->notificationService->notify(
                    $reimbursement->user_id,
                    'Pengajuan Dikirim',
                    "Pengajuan {$reimbursement->number} berhasil dikirim dan menunggu persetujuan Manager.",
                    route('reimbursements.show', $reimbursement)
                );

                // Notifikasi ke Manager divisi terkait
                $managers = User::where('role', 'manager')
                    ->where('department', $reimbursement->department)
                    ->where('status', 'active')
                    ->get();
                foreach ($managers as $manager) {
                    $this->notificationService->notify(
                        $manager->id,
                        'Pengajuan Baru Menunggu Approval',
                        "Pengajuan {$reimbursement->number} dari {$actor->name} menunggu persetujuan Anda.",
                        route('reimbursements.show', $reimbursement)
                    );
                }
            }
        });
    }

    /**
     * Setujui reimbursement pada tingkat approval aktif.
     */
    public function approve(Reimbursement $reimbursement, User $approver, ?string $comment = null): void
    {
        // Penegakan Mutlak Anti-Self-Approval
        if ($approver->id === $reimbursement->user_id) {
            throw new AuthorizationException('Anti-Self-Approval: Pejabat atau pemohon dilarang menyetujui pengajuannya sendiri.');
        }

        $currentStatus = $reimbursement->status;
        $level = null;
        $nextStatus = null;
        $isDirectorEscalation = false;

        if ($currentStatus === ReimbursementStatus::WAITING_MANAGER_APPROVAL->value || $currentStatus === 'submitted') {
            if (! $approver->isManager() && ! $approver->isDirector()) {
                throw new AuthorizationException('Hanya Manager Divisi atau Direktur yang dapat memberikan persetujuan tahap Manager.');
            }

            if ($approver->isDirector() && $reimbursement->isManagerClaim()) {
                $isDirectorEscalation = true;
            }

            $level = 'manager';
            $nextStatus = ReimbursementStatus::WAITING_FINANCE_VERIFICATION->value;
        } elseif ($currentStatus === ReimbursementStatus::WAITING_FINANCE_VERIFICATION->value) {
            if (! $approver->isFinance()) {
                throw new AuthorizationException('Hanya Tim Finance yang dapat melakukan verifikasi keuangan.');
            }

            $level = 'finance';

            // Evaluasi Threshold Direktur (> Rp5.000.000)
            if (bccomp((string) $reimbursement->total_amount, self::DIRECTOR_THRESHOLD, 2) > 0) {
                $nextStatus = ReimbursementStatus::WAITING_DIRECTOR_APPROVAL->value;
            } else {
                $nextStatus = ReimbursementStatus::APPROVED->value;
            }
        } elseif ($currentStatus === ReimbursementStatus::WAITING_DIRECTOR_APPROVAL->value) {
            if (! $approver->isDirector()) {
                throw new AuthorizationException('Hanya Direktur yang dapat menyetujui pengajuan nominal tinggi.');
            }

            $level = 'director';
            $nextStatus = ReimbursementStatus::APPROVED->value;
        } else {
            throw new \DomainException("Pengajuan berstatus [{$currentStatus}] tidak sedang menunggu persetujuan.");
        }

        DB::transaction(function () use ($reimbursement, $approver, $level, $currentStatus, $nextStatus, $comment, $isDirectorEscalation) {
            Approval::create([
                'approvable_type' => Reimbursement::class,
                'approvable_id' => $reimbursement->id,
                'approver_id' => $approver->id,
                'approval_level' => $level,
                'status' => 'approved',
                'comment' => $comment,
                'decided_at' => now(),
            ]);

            $reimbursement->status = $nextStatus;
            $reimbursement->save();

            $this->auditLogService->log(
                $approver->id,
                'approved',
                'reimbursement',
                $reimbursement,
                ['status' => $currentStatus],
                [
                    'status' => $nextStatus,
                    'approval_level' => $level,
                    'comment' => $comment,
                    'director_escalation' => $isDirectorEscalation,
                ]
            );

            $this->notificationService->notify(
                $reimbursement->user_id,
                'Pengajuan Disetujui ('.ucfirst($level).')',
                "Pengajuan {$reimbursement->number} telah disetujui oleh {$approver->name} ({$level}). Status saat ini: {$reimbursement->status_label}.",
                route('reimbursements.show', $reimbursement)
            );

            // Notifikasi ke tahap selanjutnya (Finance / Director)
            if ($nextStatus === ReimbursementStatus::WAITING_FINANCE_VERIFICATION->value) {
                $financeUsers = User::where('role', 'finance')->where('status', 'active')->get();
                foreach ($financeUsers as $finance) {
                    $this->notificationService->notify(
                        $finance->id,
                        'Verifikasi Finance Diperlukan',
                        "Pengajuan {$reimbursement->number} dari {$reimbursement->user->name} telah disetujui dan menunggu verifikasi Finance.",
                        route('reimbursements.show', $reimbursement)
                    );
                }
            } elseif ($nextStatus === ReimbursementStatus::WAITING_DIRECTOR_APPROVAL->value) {
                $directorUsers = User::where('role', 'director')->where('status', 'active')->get();
                foreach ($directorUsers as $director) {
                    $this->notificationService->notify(
                        $director->id,
                        'Persetujuan Nominal Tinggi Diperlukan',
                        "Pengajuan {$reimbursement->number} bernilai tinggi (Rp ".number_format((float) $reimbursement->total_amount, 2, ',', '.').') memerlukan persetujuan Direktur.',
                        route('reimbursements.show', $reimbursement)
                    );
                }
            }
        });
    }

    /**
     * Tolak pengajuan reimbursement dengan alasan penolakan wajib.
     */
    public function reject(Reimbursement $reimbursement, User $approver, string $reason): void
    {
        if ($approver->id === $reimbursement->user_id) {
            throw new AuthorizationException('Anti-Self-Approval: Pemohon tidak dapat menolak pengajuannya sendiri.');
        }

        $reason = trim($reason);
        if (empty($reason)) {
            throw new \InvalidArgumentException('Alasan penolakan wajib diisi secara informatif.');
        }

        $oldStatus = $reimbursement->status;

        DB::transaction(function () use ($reimbursement, $approver, $oldStatus, $reason) {
            Approval::create([
                'approvable_type' => Reimbursement::class,
                'approvable_id' => $reimbursement->id,
                'approver_id' => $approver->id,
                'approval_level' => $approver->role,
                'status' => 'rejected',
                'comment' => $reason,
                'decided_at' => now(),
            ]);

            $reimbursement->status = ReimbursementStatus::REJECTED->value;
            $reimbursement->rejection_reason = $reason;
            $reimbursement->save();

            $this->auditLogService->log(
                $approver->id,
                'rejected',
                'reimbursement',
                $reimbursement,
                ['status' => $oldStatus],
                ['status' => $reimbursement->status, 'rejection_reason' => $reason]
            );

            $this->notificationService->notify(
                $reimbursement->user_id,
                'Pengajuan Ditolak',
                "Pengajuan {$reimbursement->number} ditolak oleh {$approver->name}. Alasan: {$reason}",
                route('reimbursements.show', $reimbursement)
            );
        });
    }

    /**
     * Kembalikan pengajuan ke pemohon untuk revisi dokumen.
     */
    public function returnForRevision(Reimbursement $reimbursement, User $approver, string $comment): void
    {
        if ($approver->id === $reimbursement->user_id) {
            throw new AuthorizationException('Anti-Self-Approval: Pemohon tidak dapat mengembalikan pengajuannya sendiri.');
        }

        $comment = trim($comment);
        if (empty($comment)) {
            throw new \InvalidArgumentException('Catatan instruksi perbaikan wajib diisi agar pemohon mengetahui hal yang perlu direvisi.');
        }

        $oldStatus = $reimbursement->status;

        DB::transaction(function () use ($reimbursement, $approver, $oldStatus, $comment) {
            Approval::create([
                'approvable_type' => Reimbursement::class,
                'approvable_id' => $reimbursement->id,
                'approver_id' => $approver->id,
                'approval_level' => $approver->role,
                'status' => 'returned',
                'comment' => $comment,
                'decided_at' => now(),
            ]);

            $reimbursement->status = ReimbursementStatus::RETURNED_FOR_REVISION->value;
            $reimbursement->save();

            $this->auditLogService->log(
                $approver->id,
                'returned',
                'reimbursement',
                $reimbursement,
                ['status' => $oldStatus],
                ['status' => $reimbursement->status, 'revision_note' => $comment]
            );

            $this->notificationService->notify(
                $reimbursement->user_id,
                'Pengajuan Perlu Perbaikan',
                "Pengajuan {$reimbursement->number} dikembalikan oleh {$approver->name}. Catatan revisi: {$comment}",
                route('reimbursements.edit', $reimbursement)
            );
        });
    }

    /**
     * Finance memproses pembayaran dan pencatatan kas otomatis.
     */
    public function markAsPaid(
        Reimbursement $reimbursement,
        User $financeUser,
        string $paymentMethod = 'Bank Transfer',
        ?string $paymentAccount = null
    ): void {
        if (! $financeUser->isFinance()) {
            throw new AuthorizationException('Hanya staf Finance yang berwenang memproses status pembayaran reimbursement.');
        }

        if ($reimbursement->status !== ReimbursementStatus::APPROVED->value) {
            throw new \DomainException('Hanya reimbursement yang telah disetujui penuh (Approved) yang dapat dibayarkan.');
        }

        $oldStatus = $reimbursement->status;

        DB::transaction(function () use ($reimbursement, $financeUser, $paymentMethod, $paymentAccount, $oldStatus) {
            $reimbursement->status = ReimbursementStatus::PAID->value;
            $reimbursement->payment_date = now();
            $reimbursement->paid_by = $financeUser->id;
            $reimbursement->payment_method = $paymentMethod;
            if ($paymentAccount) {
                $reimbursement->payment_account = $paymentAccount;
            }
            $reimbursement->completed_at = now();
            $reimbursement->save();

            // Catat pengeluaran kas kantor otomatis
            Transaction::create([
                'type' => 'expense',
                'transaction_date' => now()->toDateString(),
                'amount' => $reimbursement->total_amount,
                'category' => $reimbursement->category ?? 'Reimbursement',
                'department' => $reimbursement->department,
                'project' => $reimbursement->project,
                'payment_method' => $paymentMethod,
                'payment_status' => 'paid',
                'description' => "Pembayaran Reimbursement #{$reimbursement->number}: {$reimbursement->purpose}",
                'source_reference' => $reimbursement->number,
                'reimbursement_id' => $reimbursement->id,
                'created_by' => $financeUser->id,
            ]);

            $this->auditLogService->log(
                $financeUser->id,
                'paid',
                'reimbursement',
                $reimbursement,
                ['status' => $oldStatus],
                [
                    'status' => $reimbursement->status,
                    'paid_by' => $financeUser->id,
                    'payment_date' => $reimbursement->payment_date,
                    'amount' => $reimbursement->total_amount,
                ]
            );

            $this->notificationService->notify(
                $reimbursement->user_id,
                'Reimbursement Dibayarkan',
                "Dana pengajuan {$reimbursement->number} sebesar Rp ".number_format((float) $reimbursement->total_amount, 2, ',', '.').' telah diproses oleh Finance.',
                route('reimbursements.show', $reimbursement)
            );
        });
    }
}

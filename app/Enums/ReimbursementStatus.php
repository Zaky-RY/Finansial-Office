<?php

namespace App\Enums;

enum ReimbursementStatus: string
{
    case DRAFT = 'draft';
    case WAITING_MANAGER_APPROVAL = 'waiting_manager_approval';
    case WAITING_FINANCE_VERIFICATION = 'waiting_finance_verification';
    case WAITING_DIRECTOR_APPROVAL = 'waiting_director_approval';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case RETURNED_FOR_REVISION = 'returned_for_revision';
    case PAID = 'paid';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    /**
     * Label representasi status dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::WAITING_MANAGER_APPROVAL => 'Menunggu Approval Manager',
            self::WAITING_FINANCE_VERIFICATION => 'Menunggu Verifikasi Finance',
            self::WAITING_DIRECTOR_APPROVAL => 'Menunggu Approval Direktur',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::RETURNED_FOR_REVISION => 'Perlu Perbaikan',
            self::PAID => 'Dibayar',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    /**
     * CSS badge class untuk presentasi antarmuka selaras desain Stitch.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'badge-draft',
            self::WAITING_MANAGER_APPROVAL => 'badge-warning',
            self::WAITING_FINANCE_VERIFICATION => 'badge-info',
            self::WAITING_DIRECTOR_APPROVAL => 'badge-purple',
            self::APPROVED => 'badge-success',
            self::REJECTED => 'badge-danger',
            self::RETURNED_FOR_REVISION => 'badge-amber',
            self::PAID, self::COMPLETED => 'badge-paid',
            self::CANCELLED => 'badge-muted',
        };
    }

    /**
     * Periksa apakah status memperbolehkan penyuntingan oleh pemohon.
     */
    public function isEditable(): bool
    {
        return in_array($this, [self::DRAFT, self::RETURNED_FOR_REVISION], true);
    }

    /**
     * Periksa apakah pengajuan sedang aktif menunggu persetujuan.
     */
    public function isPendingApproval(): bool
    {
        return in_array($this, [
            self::WAITING_MANAGER_APPROVAL,
            self::WAITING_FINANCE_VERIFICATION,
            self::WAITING_DIRECTOR_APPROVAL,
        ], true);
    }
}

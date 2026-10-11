<?php

namespace App\Models;

use App\Enums\ReimbursementStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Reimbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'number',
        'department',
        'purpose',
        'transaction_date',
        'category',
        'description',
        'amount',
        'total_amount',
        'currency',
        'payment_method',
        'project',
        'payment_account',
        'notes',
        'status',
        'submission_date',
        'submitted_at',
        'payment_date',
        'paid_by',
        'rejection_reason',
        'cancellation_reason',
        'completed_at',
        'attachment_path',
        'attachment_original_name',
        'attachment_mime_type',
        'attachment_size',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'submission_date' => 'datetime',
        'submitted_at' => 'datetime',
        'payment_date' => 'datetime',
        'completed_at' => 'datetime',
        'amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Reimbursement $reimbursement) {
            $reimbursement->number ??= 'RMB-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6));
            $reimbursement->currency ??= 'IDR';
            $reimbursement->status ??= ReimbursementStatus::DRAFT->value;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paidByUser()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function items()
    {
        return $this->hasMany(ReimbursementItem::class);
    }

    public function approvals()
    {
        return $this->morphMany(Approval::class, 'approvable')->latest();
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    public function getReimbursementNumberAttribute(): string
    {
        return $this->number ?? ('RMB-'.$this->id);
    }

    /**
     * Hitung total nilai pengajuan dari akumulasi item menggunakan presisi bcmath.
     */
    public function calculateTotal(): string
    {
        $total = '0.00';
        foreach ($this->items as $item) {
            $total = bcadd($total, (string) $item->amount, 2);
        }

        // Jika tidak ada item tapi amount header diisi langsung
        if (bccomp($total, '0.00', 2) === 0 && ! empty($this->amount)) {
            $total = bcadd('0.00', (string) $this->amount, 2);
        }

        $this->total_amount = $total;
        $this->amount = $total;

        return $total;
    }

    public function getStatusEnum(): ?ReimbursementStatus
    {
        return ReimbursementStatus::tryFrom($this->status);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->getStatusEnum()?->label() ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->getStatusEnum()?->badgeClass() ?? 'badge';
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [
            ReimbursementStatus::DRAFT->value,
            ReimbursementStatus::RETURNED_FOR_REVISION->value,
            'returned',
        ], true);
    }

    public function hasAttachment(): bool
    {
        return filled($this->attachment_path) || $this->items()->whereNotNull('attachment_path')->exists();
    }

    public function isDraft(): bool
    {
        return $this->status === ReimbursementStatus::DRAFT->value || $this->status === 'draft';
    }

    public function isManagerClaim(): bool
    {
        return (bool) ($this->user?->isManager() || ($this->user && $this->user->role === 'manager'));
    }

    public function isPendingManagerApproval(): bool
    {
        return in_array($this->status, [ReimbursementStatus::WAITING_MANAGER_APPROVAL->value, 'submitted'], true);
    }

    public function isPendingFinanceVerification(): bool
    {
        return $this->status === ReimbursementStatus::WAITING_FINANCE_VERIFICATION->value;
    }

    public function isPendingDirectorApproval(): bool
    {
        return $this->status === ReimbursementStatus::WAITING_DIRECTOR_APPROVAL->value;
    }

    public function isApproved(): bool
    {
        return $this->status === ReimbursementStatus::APPROVED->value;
    }

    public function isPaid(): bool
    {
        return in_array($this->status, [ReimbursementStatus::PAID->value, ReimbursementStatus::COMPLETED->value], true);
    }

    public function isRejected(): bool
    {
        return $this->status === ReimbursementStatus::REJECTED->value;
    }

    public function isReturned(): bool
    {
        return in_array($this->status, [ReimbursementStatus::RETURNED_FOR_REVISION->value, 'returned'], true);
    }

    /**
     * Scope untuk pengajuan yang membutuhkan tindakan persetujuan dari user tertentu.
     */
    public function scopePendingForUser(Builder $query, User $user): Builder
    {
        // Anti-self-approval di level query: jangan tampilkan pengajuan miliknya sendiri
        $query->where('user_id', '!=', $user->id);

        if ($user->isDirector()) {
            return $query->whereIn('status', [
                ReimbursementStatus::WAITING_DIRECTOR_APPROVAL->value,
                ReimbursementStatus::WAITING_MANAGER_APPROVAL->value,
                'submitted',
            ]);
        }

        if ($user->isFinance()) {
            return $query->where('status', ReimbursementStatus::WAITING_FINANCE_VERIFICATION->value);
        }

        if ($user->isManager()) {
            return $query->whereIn('status', [
                ReimbursementStatus::WAITING_MANAGER_APPROVAL->value,
                'submitted',
            ])->where(function ($q) use ($user) {
                if ($user->department) {
                    $q->where('department', $user->department);
                }
            });
        }

        return $query->whereRaw('1 = 0');
    }
}

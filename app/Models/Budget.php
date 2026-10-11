<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    use HasFactory;

    public const ALERT_NORMAL = 'normal';

    public const ALERT_WARNING = 'warning';               // >= 75%

    public const ALERT_HIGH_WARNING = 'high_warning';     // >= 90%

    public const ALERT_EXHAUSTED = 'budget_exhausted';    // >= 100%

    protected $fillable = [
        'name',
        'department',
        'category',
        'project',
        'period_start',
        'period_end',
        'allocated_amount',
        'used_amount',
        'status',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'allocated_amount' => 'decimal:2',
        'used_amount' => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class)->latest('transaction_date')->latest('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeDepartment(Builder $query, ?string $department): Builder
    {
        if (! $department) {
            return $query;
        }

        return $query->where('department', $department);
    }

    /**
     * Hitung sisa anggaran dengan presisi bcmath:
     * Sisa Anggaran = Anggaran Dialokasikan - Anggaran Terpakai
     */
    public function getRemainingAmountAttribute(): string
    {
        return bcsub((string) $this->allocated_amount, (string) $this->used_amount, 2);
    }

    /**
     * Hitung persentase penggunaan anggaran.
     */
    public function getUsagePercentageAttribute(): float
    {
        if (bccomp((string) $this->allocated_amount, '0', 2) <= 0) {
            return 0.0;
        }

        // bcdiv dengan presisi 4 desimal lalu kalikan 100
        $ratio = (float) bcdiv((string) $this->used_amount, (string) $this->allocated_amount, 4);

        return round($ratio * 100, 2);
    }

    /**
     * Evaluasi tingkat peringatan anggaran:
     * - >= 100%: budget exhausted
     * - >= 90%: high warning
     * - >= 75%: warning
     * - < 75%: normal
     */
    public function getAlertStatusAttribute(): string
    {
        $percentage = $this->usage_percentage;

        if ($percentage >= 100.0) {
            return self::ALERT_EXHAUSTED;
        }

        if ($percentage >= 90.0) {
            return self::ALERT_HIGH_WARNING;
        }

        if ($percentage >= 75.0) {
            return self::ALERT_WARNING;
        }

        return self::ALERT_NORMAL;
    }

    public function getAlertLabelAttribute(): string
    {
        return match ($this->alert_status) {
            self::ALERT_EXHAUSTED => 'Batas Terlampaui (100%)',
            self::ALERT_HIGH_WARNING => 'Peringatan Tinggi (90%)',
            self::ALERT_WARNING => 'Peringatan (75%)',
            default => 'Normal',
        };
    }

    public function getAlertBadgeClassAttribute(): string
    {
        return match ($this->alert_status) {
            self::ALERT_EXHAUSTED => 'badge-danger',
            self::ALERT_HIGH_WARNING => 'badge-amber',
            self::ALERT_WARNING => 'badge-warning',
            default => 'badge-success',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Aktif',
            'draft' => 'Draft',
            'closed' => 'Ditutup',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'badge-paid',
            'draft' => 'badge-draft',
            'closed' => 'badge-muted',
            'cancelled' => 'badge-danger',
            default => 'badge',
        };
    }

    public function getFormattedAllocatedAmountAttribute(): string
    {
        return 'Rp '.number_format((float) $this->allocated_amount, 2, ',', '.');
    }

    public function getFormattedUsedAmountAttribute(): string
    {
        return 'Rp '.number_format((float) $this->used_amount, 2, ',', '.');
    }

    public function getFormattedRemainingAmountAttribute(): string
    {
        $number = (float) $this->remaining_amount;
        $isNegative = $number < 0;
        $formatted = number_format(abs($number), 2, ',', '.');

        return ($isNegative ? '-Rp ' : 'Rp ').$formatted;
    }

    /**
     * Cek apakah kuota anggaran mencukupi untuk nominal tertentu.
     */
    public function canAccommodate(string $amount): bool
    {
        $remaining = $this->remaining_amount;

        return bccomp($remaining, (string) $amount, 2) >= 0;
    }

    public function isExhausted(): bool
    {
        return $this->alert_status === self::ALERT_EXHAUSTED;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}

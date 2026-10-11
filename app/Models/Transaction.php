<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'type', // income, expense
        'transaction_date',
        'amount',
        'currency',
        'category',
        'department',
        'project',
        'vendor',
        'payment_method',
        'payment_status', // unpaid, scheduled, paid, cancelled
        'description',
        'source_reference',
        'reimbursement_id',
        'budget_id',
        'created_by',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'attachment_path',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'cancelled_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction) {
            $transaction->transaction_number ??= 'TRX-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6));
            $transaction->currency ??= 'IDR';
            $transaction->payment_status ??= 'paid';
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function reimbursement(): BelongsTo
    {
        return $this->belongsTo(Reimbursement::class);
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('payment_status', '!=', 'cancelled');
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', 'expense');
    }

    public function isIncome(): bool
    {
        return $this->type === 'income';
    }

    public function isExpense(): bool
    {
        return $this->type === 'expense';
    }

    public function isCancelled(): bool
    {
        return $this->payment_status === 'cancelled';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp '.number_format((float) $this->amount, 2, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'Lunas',
            'unpaid' => 'Belum Lunas',
            'scheduled' => 'Dijadwalkan',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->payment_status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'badge-paid',
            'unpaid' => 'badge-warning',
            'scheduled' => 'badge-info',
            'cancelled' => 'badge-danger',
            default => 'badge',
        };
    }
}

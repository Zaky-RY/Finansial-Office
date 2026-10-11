<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type', // income, expense, both
        'description',
        'status',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->whereIn('type', ['income', 'both']);
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->whereIn('type', ['expense', 'both']);
    }
}

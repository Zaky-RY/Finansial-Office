<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Transaction::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:income,expense'],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999999999.99'],
            'category' => ['required', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:120'],
            'project' => ['nullable', 'string', 'max:150'],
            'vendor' => ['nullable', 'string', 'max:150'],
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_status' => ['required', 'string', 'in:unpaid,scheduled,paid'],
            'description' => ['required', 'string', 'max:5000'],
            'source_reference' => ['nullable', 'string', 'max:100'],
            'budget_id' => ['nullable', 'integer', 'exists:budgets,id'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }
}

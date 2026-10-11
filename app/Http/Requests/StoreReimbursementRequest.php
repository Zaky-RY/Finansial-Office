<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReimbursementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Reimbursement::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'department' => ['nullable', 'string', 'max:120'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'transaction_date' => ['nullable', 'date', 'before_or_equal:today'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'amount' => ['nullable', 'numeric', 'min:0.01', 'max:9999999999999999.99'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'project' => ['nullable', 'string', 'max:150'],
            'payment_account' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],

            // Validasi Multi-Item
            'items' => ['nullable', 'array'],
            'items.*.expense_date' => ['nullable', 'date', 'before_or_equal:today'],
            'items.*.category' => ['nullable', 'string', 'max:100'],
            'items.*.description' => ['nullable', 'string', 'max:1000'],
            'items.*.amount' => ['nullable', 'numeric', 'min:0.01', 'max:9999999999999999.99'],
            'items.*.vendor' => ['nullable', 'string', 'max:150'],
            'items.*.receipt_number' => ['nullable', 'string', 'max:100'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $budget = $this->route('budget');

        return $this->user()?->can('update', $budget) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:100'],
            'project' => ['nullable', 'string', 'max:150'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'allocated_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999999999.99'],
            'status' => ['required', 'string', 'in:draft,active,closed,cancelled'],
        ];
    }
}

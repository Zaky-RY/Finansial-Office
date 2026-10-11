<?php

namespace App\Http\Requests;

class SubmitReimbursementRequest extends StoreReimbursementRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'department' => ['required', 'string', 'max:120'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_account' => ['required', 'string', 'max:100'],
        ]);
    }
}

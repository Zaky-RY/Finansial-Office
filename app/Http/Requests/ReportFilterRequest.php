<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->isFinance() || $user->isDirector() || $user->isAdmin() || $user->isManager());
    }

    public function rules(): array
    {
        return [
            'report_type' => ['nullable', 'string', 'in:cashflow,reimbursement,budget'],
            'start_date' => ['nullable', 'date'],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
                function ($attribute, $value, $fail) {
                    if ($this->filled('start_date') && $this->filled('end_date')) {
                        $start = \Carbon\Carbon::parse($this->input('start_date'));
                        $end = \Carbon\Carbon::parse($value);
                        if ($start->diffInDays($end) > 366) {
                            $fail('Rentang waktu filter laporan tidak boleh melebihi 1 tahun (366 hari) guna menjaga performa sistem.');
                        }
                    }
                },
            ],
            'type' => ['nullable', 'string', 'in:all,income,expense'],
            'department' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:50'],
            'vendor' => ['nullable', 'string', 'max:150'],
            'q' => ['nullable', 'string', 'max:100'],
            'format' => ['nullable', 'string', 'in:csv,excel'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'Tanggal selesai harus sama dengan atau setelah tanggal mulai.',
        ];
    }
}

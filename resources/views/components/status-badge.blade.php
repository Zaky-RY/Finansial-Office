@props(['status'])

@php
$normalized = strtolower(trim((string)$status));
$classes = match($normalized) {
    'paid', 'lunas', 'approved', 'disetujui', 'active', 'aktif' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800',
    'waiting_manager_approval', 'waiting_finance_verification', 'waiting_director_approval', 'submitted', 'diajukan', 'pending', 'scheduled' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-800',
    'rejected', 'ditolak', 'cancelled', 'dibatalkan', 'inactive', 'nonaktif' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-800',
    'returned_for_revision', 'returned', 'perlu_perbaikan', 'revisi' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-400 dark:border-blue-800',
    default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
};

$label = match($normalized) {
    'paid' => 'Lunas',
    'unpaid' => 'Belum Lunas',
    'scheduled' => 'Dijadwalkan',
    'cancelled' => 'Dibatalkan',
    'draft' => 'Draft',
    'submitted' => 'Diajukan',
    'waiting_manager_approval' => 'Menunggu Approval Manager',
    'waiting_finance_verification' => 'Menunggu Verifikasi Finance',
    'waiting_director_approval' => 'Menunggu Approval Direktur',
    'approved' => 'Disetujui',
    'rejected' => 'Ditolak',
    'returned_for_revision' => 'Perlu Perbaikan',
    'active' => 'Aktif',
    'inactive' => 'Nonaktif',
    'closed' => 'Ditutup',
    default => ucfirst(str_replace('_', ' ', $normalized)),
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2 py-0.5 rounded text-caption font-bold border tabular-nums {$classes}"]) }}>
    {{ $slot->isNotEmpty() ? $slot : $label }}
</span>


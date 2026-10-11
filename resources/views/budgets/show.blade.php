@extends('layouts.app')
@section('title', 'Detail Anggaran ' . $budget->name . ' — Finance Office')
@section('header', 'Detail Alokasi Anggaran')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-[#e2e8f0]">
        <div>
            <a href="{{ route('budgets.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold tracking-wide uppercase text-[#64748b] hover:text-[#3525cd] transition-colors mb-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Daftar Anggaran
            </a>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-bold tracking-tight text-[#0f172a]">{{ $budget->name }}</h1>
                <x-status-badge :status="$budget->status" />
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                    @if($budget->alert_status === App\Models\Budget::ALERT_EXHAUSTED) bg-rose-100 text-rose-800 border border-rose-300
                    @elseif($budget->alert_status === App\Models\Budget::ALERT_HIGH_WARNING) bg-orange-100 text-orange-800 border border-orange-300
                    @elseif($budget->alert_status === App\Models\Budget::ALERT_WARNING) bg-amber-100 text-amber-800 border border-amber-300
                    @else bg-emerald-100 text-emerald-800 border border-emerald-300
                    @endif">
                    {{ $budget->alert_label }}
                </span>
            </div>
            <p class="text-xs text-[#64748b] mt-1 flex items-center gap-2 flex-wrap">
                <span>Divisi: <strong class="text-[#334155]">{{ $budget->department }}</strong></span>
                @if($budget->project)
                    <span>&bull;</span>
                    <span>Proyek: <strong class="text-[#334155]">{{ $budget->project }}</strong></span>
                @endif
                @if($budget->category)
                    <span>&bull;</span>
                    <span>Kategori: <strong class="text-[#334155]">{{ $budget->category }}</strong></span>
                @endif
                <span>&bull;</span>
                <span>Dibuat oleh {{ $budget->creator->name ?? 'Finance' }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2.5 self-start md:self-auto">
            @can('update', $budget)
                <a href="{{ route('budgets.edit', $budget) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-[#334155] bg-white border border-[#cbd5e1] rounded-lg shadow-sm hover:bg-[#f8faf9] hover:border-[#94a3b8] transition-colors">
                    <svg class="w-3.5 h-3.5 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Edit Anggaran
                </a>
            @endcan

            @can('close', $budget)
                @if($budget->isActive())
                    <form method="POST" action="{{ route('budgets.close', $budget) }}" onsubmit="return confirm('Apakah Anda yakin ingin menutup periode anggaran ini? Anggaran yang ditutup tidak dapat menyerap transaksi baru.');" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-[#dc2626] bg-white border border-[#fca5a5] rounded-lg shadow-sm hover:bg-rose-50 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Tutup Anggaran
                        </button>
                    </form>
                @endif
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Alert Banner Threshold --}}
    @if($budget->alert_status === App\Models\Budget::ALERT_EXHAUSTED)
        <div class="p-4 rounded-xl border border-rose-300 bg-rose-50 text-rose-900 flex items-start gap-3 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center flex-shrink-0 text-rose-600 font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h4 class="font-bold text-sm text-rose-900">Peringatan Kritis: Pagu Anggaran Telah Habis / Terlampaui ({{ $budget->usage_percentage }}%)</h4>
                <p class="text-xs text-rose-700 mt-0.5">Pengeluaran baru pada pos anggaran ini memerlukan penyesuaian alokasi melalui Tim Finance atau persetujuan khusus Direktur.</p>
            </div>
        </div>
    @elseif($budget->alert_status === App\Models\Budget::ALERT_HIGH_WARNING)
        <div class="p-4 rounded-xl border border-orange-300 bg-orange-50 text-orange-900 flex items-start gap-3 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0 text-orange-600 font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h4 class="font-bold text-sm text-orange-900">Peringatan Tinggi: Serapan Anggaran Mencapai {{ $budget->usage_percentage }}% (&ge; 90%)</h4>
                <p class="text-xs text-orange-700 mt-0.5">Sisa alokasi dana menipis ({{ $budget->formatted_remaining_amount }}). Harap batasi pengeluaran non-esensial hingga periode berakhir.</p>
            </div>
        </div>
    @elseif($budget->alert_status === App\Models\Budget::ALERT_WARNING)
        <div class="p-4 rounded-xl border border-amber-300 bg-amber-50 text-amber-900 flex items-start gap-3 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0 text-amber-600 font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <div>
                <h4 class="font-bold text-sm text-amber-900">Peringatan: Pemakaian Anggaran Mencapai {{ $budget->usage_percentage }}% (&ge; 75%)</h4>
                <p class="text-xs text-amber-700 mt-0.5">Monitoring ketat diperlukan untuk memastikan pengeluaran tetap terkendali dalam alokasi kuartal berjalan.</p>
            </div>
        </div>
    @endif

    {{-- Kartu Ringkasan Moneter --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        {{-- Pagu Alokasi --}}
        <div class="bg-white rounded-xl border border-[#e2e8f0] p-5 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 bottom-0 w-1 bg-[#3525cd]"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#64748b]">Pagu Alokasi</span>
                <span class="w-7 h-7 rounded-lg bg-[#3525cd]/10 text-[#3525cd] flex items-center justify-center text-xs font-bold">IDR</span>
            </div>
            <div class="text-2xl font-bold font-mono tracking-tight text-[#0f172a] mt-2">
                {{ $summary['formatted_allocated'] }}
            </div>
            <div class="text-xs text-[#64748b] mt-2 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-[#94a3b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Periode: {{ $budget->period_start ? $budget->period_start->format('d M Y') : '-' }} s/d {{ $budget->period_end ? $budget->period_end->format('d M Y') : '-' }}
            </div>
        </div>

        {{-- Total Terpakai --}}
        <div class="bg-white rounded-xl border border-[#e2e8f0] p-5 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 bottom-0 w-1 bg-[#dc2626]"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#64748b]">Total Terpakai (Realisasi)</span>
                <span class="w-7 h-7 rounded-lg bg-rose-100 text-[#dc2626] flex items-center justify-center text-xs font-bold">
                    {{ $budget->usage_percentage }}%
                </span>
            </div>
            <div class="text-2xl font-bold font-mono tracking-tight text-[#dc2626] mt-2">
                {{ $summary['formatted_used'] }}
            </div>
            <div class="text-xs text-[#64748b] mt-2">
                Realisasi kas operasional & reimbursement
            </div>
        </div>

        {{-- Sisa Kuota --}}
        <div class="bg-white rounded-xl border border-[#e2e8f0] p-5 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 bottom-0 w-1 bg-[#15803d]"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#64748b]">Sisa Kuota Anggaran</span>
                <span class="w-7 h-7 rounded-lg bg-emerald-100 text-[#15803d] flex items-center justify-center text-xs font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono tracking-tight text-[#15803d] mt-2">
                {{ $summary['formatted_remaining'] }}
            </div>
            <div class="text-xs text-[#64748b] mt-2">
                Pagu Alokasi &minus; Total Realisasi
            </div>
        </div>
    </div>

    {{-- Progress Bar Meteran Serapan --}}
    <div class="bg-white rounded-xl border border-[#e2e8f0] p-6 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-sm font-bold text-[#0f172a]">Tingkat Serapan Anggaran Divisi</h3>
                <p class="text-xs text-[#64748b]">Rasio penyerapan pagu terhadap batas waktu periode operasional</p>
            </div>
            <span class="text-xl font-bold font-mono
                @if($budget->usage_percentage >= 100) text-[#dc2626]
                @elseif($budget->usage_percentage >= 90) text-orange-600
                @elseif($budget->usage_percentage >= 75) text-amber-600
                @else text-[#15803d]
                @endif">
                {{ $budget->usage_percentage }}%
            </span>
        </div>

        @php
            $barColor = 'bg-[#15803d]';
            if ($budget->usage_percentage >= 100) {
                $barColor = 'bg-[#dc2626]';
            } elseif ($budget->usage_percentage >= 90) {
                $barColor = 'bg-orange-500';
            } elseif ($budget->usage_percentage >= 75) {
                $barColor = 'bg-amber-500';
            }
        @endphp

        <div class="w-full bg-[#f1f5f9] rounded-full h-3 overflow-hidden mb-2">
            <div class="{{ $barColor }} h-full transition-all duration-300 rounded-full" style="width: {{ min(100, $budget->usage_percentage) }}%"></div>
        </div>

        <div class="flex justify-between text-[11px] font-semibold text-[#64748b]">
            <span>0% (Awal)</span>
            <span class="text-amber-700">75% (Peringatan Awal)</span>
            <span class="text-orange-700">90% (Kritis)</span>
            <span class="text-rose-700">100% (Pagu Habis)</span>
        </div>
    </div>

    {{-- Riwayat Transaksi Pemakaian Anggaran --}}
    <div class="bg-white rounded-xl border border-[#e2e8f0] overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-[#e2e8f0] flex items-center justify-between bg-[#fafbfc]">
            <div>
                <h3 class="text-sm font-bold text-[#0f172a]">Riwayat Penggunaan Anggaran</h3>
                <p class="text-xs text-[#64748b]">Daftar transaksi kas pengeluaran yang menyerap kuota pos anggaran ini.</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-[#f1f5f9] text-[#334155] border border-[#cbd5e1]">
                {{ $budget->transactions->count() }} Transaksi
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#f8faf9] text-[#64748b] border-b border-[#e2e8f0] uppercase tracking-wider font-semibold text-[11px]">
                        <th class="py-3 px-5">Nomor TRX</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Keterangan / Keperluan</th>
                        <th class="py-3 px-4">Rekanan / Vendor</th>
                        <th class="py-3 px-4 text-right">Nominal</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f1f5f9]">
                    @forelse($budget->transactions as $trx)
                        <tr class="hover:bg-[#fbfcfb] transition-colors">
                            <td class="py-3.5 px-5 font-mono font-semibold">
                                <a href="{{ route('transactions.show', $trx) }}" class="text-[#3525cd] hover:underline">
                                    {{ $trx->transaction_number }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-[#64748b] whitespace-nowrap">
                                {{ $trx->transaction_date ? \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-[#0f172a] max-w-xs truncate">
                                {{ $trx->description ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-[#475569]">
                                {{ $trx->vendor ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold whitespace-nowrap {{ $trx->isCancelled() ? 'text-[#94a3b8] line-through' : 'text-[#dc2626]' }}">
                                -{{ $trx->formatted_amount }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <x-status-badge :status="$trx->status" />
                            </td>
                            <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                <a href="{{ route('transactions.show', $trx) }}" class="inline-flex items-center px-2.5 py-1 text-[11px] font-semibold text-[#334155] bg-white border border-[#cbd5e1] rounded hover:bg-[#f8faf9] transition-colors">
                                    Rincian
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-[#64748b]">
                                <div class="w-12 h-12 rounded-xl bg-[#f1f5f9] text-[#64748b] flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <h4 class="font-bold text-sm text-[#0f172a]">Belum Ada Transaksi Pemakaian</h4>
                                <p class="text-xs text-[#64748b] mt-1 max-w-sm mx-auto">Pos anggaran ini masih utuh dan belum ada pengeluaran kas yang dibebankan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

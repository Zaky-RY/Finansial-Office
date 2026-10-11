@extends('layouts.app')
@section('title', 'Antrean Persetujuan Biaya — Finance Office')
@section('header', 'Persetujuan Biaya')

@section('content')
@php
    $totalPendingAmount = '0.00';
    $highPriorityCount = 0;
    foreach ($pendingApprovals as $item) {
        $totalPendingAmount = bcadd($totalPendingAmount, (string) $item->total_amount, 2);
        if (bccomp((string) $item->total_amount, '5000000.00', 2) > 0 || $item->isManagerClaim()) {
            $highPriorityCount++;
        }
    }
@endphp

<div class="flex flex-col gap-space-xl max-w-7xl mx-auto w-full">

    {{-- Breadcrumb & Navigation Topbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
        <nav aria-label="Breadcrumb" class="flex items-center flex-wrap gap-2 text-body-sm font-body-sm text-on-surface-variant">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Treasury</a>
            <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
            <span class="hover:text-primary transition-colors">Operasional</span>
            <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
            <span class="text-on-surface font-semibold">Antrean Persetujuan Biaya</span>
        </nav>
        <div class="flex items-center gap-2">
            <a href="{{ route('reimbursements.index') }}" class="inline-flex items-center gap-1.5 text-body-sm font-body-sm font-medium text-primary hover:text-primary-container transition-colors group">
                <span class="material-symbols-outlined text-base">receipt_long</span>
                <span>Semua Reimbursement</span>
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-secondary-container/60 border border-secondary/30 text-on-secondary-container flex items-center gap-3">
            <span class="material-symbols-outlined text-secondary text-xl shrink-0">check_circle</span>
            <span class="font-body-sm text-body-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-error-container/60 border border-error/30 text-on-error-container flex items-center gap-3">
            <span class="material-symbols-outlined text-error text-xl shrink-0">error</span>
            <span class="font-body-sm text-body-sm font-medium">{{ $errors->first() }}</span>
        </div>
    @endif

    {{-- Header Section with High-Density Context (Stitch Screen Header) --}}
    <header class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg lg:p-space-xl flex flex-col lg:flex-row lg:items-start justify-between gap-space-lg border border-outline-variant/30">
        <div class="flex flex-col gap-space-xs max-w-3xl">
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Antrean Persetujuan Biaya</h1>
                <span class="px-2 py-0.5 rounded-lg bg-surface-container text-on-surface-variant font-label-data-sm text-label-data-sm font-semibold">
                    SOP-FIN-04
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-primary-fixed text-on-primary-fixed font-caption text-caption font-bold uppercase tracking-wider">
                    Role: {{ strtoupper(auth()->user()->role) }}
                </span>
            </div>
            <p class="font-body-md text-body-md text-on-surface-variant">
                Tinjau kepatuhan anggaran, verifikasi kelayakan bukti pengeluaran, dan berikan keputusan persetujuan secara berjenjang.
            </p>
        </div>

        <div class="flex items-center flex-wrap gap-2.5 shrink-0">
            <a href="{{ route('approvals.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container text-on-surface font-body-sm text-body-sm font-medium hover:bg-surface-container-high transition-colors shadow-sm">
                <span class="material-symbols-outlined text-lg text-on-surface-variant">refresh</span>
                <span>Segarkan Antrean</span>
            </a>
            <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container text-on-surface font-body-sm text-body-sm font-medium hover:bg-surface-container-high transition-colors shadow-sm">
                <span class="material-symbols-outlined text-lg text-on-surface-variant">analytics</span>
                <span>Laporan Audit</span>
            </a>
        </div>
    </header>

    {{-- KPI Cards Strip (4 Metrics Strip mengacu Stitch Design) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter-desktop">
        {{-- Card 1: Menunggu Tindakan --}}
        <div class="p-space-lg rounded-xl bg-surface-container-lowest border border-outline-variant/30 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="font-caption text-caption text-outline uppercase font-semibold">Menunggu Tindakan</span>
                <span class="material-symbols-outlined text-amber-600 text-xl">hourglass_top</span>
            </div>
            <div class="my-3">
                <span class="font-display text-display font-bold text-on-surface font-label-data">
                    {{ $pendingApprovals->total() }}
                </span>
                <span class="font-body-sm text-body-sm text-outline ml-1">Pengajuan</span>
            </div>
            <div class="flex items-center gap-1.5 text-caption font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded w-fit border border-amber-200">
                <span class="material-symbols-outlined text-xs">pending_actions</span>
                <span>Antrean aktif saat ini</span>
            </div>
        </div>

        {{-- Card 2: Akumulasi Nilai Antrean --}}
        <div class="p-space-lg rounded-xl bg-surface-container-lowest border border-outline-variant/30 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="font-caption text-caption text-outline uppercase font-semibold">Total Nilai Antrean</span>
                <span class="material-symbols-outlined text-primary text-xl">account_balance_wallet</span>
            </div>
            <div class="my-3">
                <span class="font-headline-lg text-headline-lg font-bold text-primary font-label-data">
                    Rp {{ number_format((float) $totalPendingAmount, 2, ',', '.') }}
                </span>
            </div>
            <div class="flex items-center gap-1.5 text-caption font-medium text-on-surface-variant">
                <span>Mata Uang: IDR (Rupiah)</span>
            </div>
        </div>

        {{-- Card 3: Prioritas Tinggi / Eskalasi --}}
        <div class="p-space-lg rounded-xl bg-surface-container-lowest border border-outline-variant/30 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="font-caption text-caption text-outline uppercase font-semibold">Eskalasi / Bernilai Tinggi</span>
                <span class="material-symbols-outlined text-amber-600 text-xl">gavel</span>
            </div>
            <div class="my-3">
                <span class="font-display text-display font-bold text-on-surface font-label-data">
                    {{ $highPriorityCount }}
                </span>
                <span class="font-body-sm text-body-sm text-outline ml-1">Item</span>
            </div>
            <div class="flex items-center gap-1.5 text-caption font-medium text-amber-800 bg-amber-50 px-2 py-0.5 rounded w-fit border border-amber-200">
                <span class="material-symbols-outlined text-xs">warning</span>
                <span>&gt; Rp 5 Juta / Klaim Pejabat</span>
            </div>
        </div>

        {{-- Card 4: Batas Otorisasi Wewenang --}}
        <div class="p-space-lg rounded-xl bg-surface-container-lowest border border-outline-variant/30 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="font-caption text-caption text-outline uppercase font-semibold">Wewenang Otorisasi</span>
                <span class="material-symbols-outlined text-secondary text-xl">verified_user</span>
            </div>
            <div class="my-3">
                <span class="font-headline-md text-headline-md font-bold text-on-surface">
                    @if(auth()->user()->isDirector())
                        Tanpa Batas
                    @elseif(auth()->user()->isManager())
                        Maks. Rp 5.000.000
                    @elseif(auth()->user()->isFinance())
                        Verifikasi Keuangan
                    @else
                        Sesuai Kebijakan
                    @endif
                </span>
            </div>
            <div class="flex items-center gap-1.5 text-caption font-medium text-secondary bg-secondary-container px-2 py-0.5 rounded w-fit">
                <span class="material-symbols-outlined text-xs">check_circle</span>
                <span>Tingkat Otorisasi Sah</span>
            </div>
        </div>
    </div>

    {{-- Filter & Search Toolbar --}}
    <div class="p-space-md bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/30 flex flex-col sm:flex-row items-center justify-between gap-space-md">
        <div class="relative w-full sm:w-80">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-lg pointer-events-none">search</span>
            <input id="approvalSearchInput" type="text" placeholder="Cari nomor pengajuan atau nama pemohon..." onkeyup="filterApprovalTable()" class="w-full pl-9 pr-3 py-2 bg-surface-container-low border border-outline-variant/50 rounded-lg text-body-sm font-body-sm text-on-surface placeholder:text-outline focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all">
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <span class="font-caption text-caption text-outline font-label-data-sm hidden md:inline">
                Menampilkan {{ $pendingApprovals->count() }} dari {{ $pendingApprovals->total() }} data
            </span>
        </div>
    </div>

    {{-- Approval Queue Content: High-Density Table & Mobile Cards --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        
        {{-- Desktop Table View --}}
        <div class="overflow-x-auto hidden md:block">
            <table class="w-full text-left border-collapse" id="approvalTable">
                <thead>
                    <tr class="bg-surface-container-high text-on-surface-variant font-caption text-caption uppercase tracking-wider font-semibold border-b border-outline-variant/30">
                        <th class="py-3 px-3 text-center w-12">#</th>
                        <th class="py-3 px-4 w-44">Nomor Pengajuan</th>
                        <th class="py-3 px-4 min-w-[200px]">Pemohon &amp; Divisi</th>
                        <th class="py-3 px-4 min-w-[220px]">Keperluan / Agenda</th>
                        <th class="py-3 px-3 w-32">Tgl Diajukan</th>
                        <th class="py-3 px-4 text-right w-40">Nominal (IDR)</th>
                        <th class="py-3 px-4 w-48">Tahap Saat Ini</th>
                        <th class="py-3 px-4 text-center w-52">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-low font-body-sm text-body-sm text-on-surface">
                    @forelse($pendingApprovals as $idx => $item)
                        <tr class="hover:bg-surface-container-low/60 transition-colors approval-row" data-search="{{ strtolower($item->number . ' ' . $item->user->name . ' ' . ($item->department ?? '') . ' ' . ($item->purpose ?? '')) }}">
                            <td class="py-3.5 px-3 text-center font-label-data-sm text-outline">
                                {{ str_pad($pendingApprovals->firstItem() + $idx, 2, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="py-3.5 px-4 font-label-data font-semibold whitespace-nowrap">
                                <a href="{{ route('reimbursements.show', $item) }}" class="text-primary hover:underline flex items-center gap-1 group">
                                    <span>{{ $item->number }}</span>
                                    <span class="material-symbols-outlined text-sm opacity-0 group-hover:opacity-100 transition-opacity">open_in_new</span>
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-primary-fixed text-on-primary-fixed flex items-center justify-center font-headline-sm text-xs font-bold shrink-0">
                                        {{ strtoupper(substr($item->user->name, 0, 2)) }}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-medium text-on-surface truncate">{{ $item->user->name }}</span>
                                        <span class="font-caption text-caption text-outline truncate">
                                            {{ $item->department ?? $item->user->department ?? 'Umum' }}
                                        </span>
                                        @if($item->isManagerClaim())
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded bg-amber-100 text-amber-900 font-caption text-[10px] font-bold mt-0.5 w-fit border border-amber-300">
                                                <span class="material-symbols-outlined text-[11px]">shield</span> Klaim Pejabat
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="line-clamp-2 text-on-surface-variant leading-relaxed" title="{{ $item->purpose ?? $item->description ?? 'Pengajuan Biaya' }}">
                                    {{ $item->purpose ?? $item->description ?? 'Pengajuan Biaya' }}
                                </p>
                            </td>
                            <td class="py-3.5 px-3 font-label-data-sm text-on-surface-variant whitespace-nowrap">
                                {{ $item->submitted_at ? $item->submitted_at->format('d M Y') : ($item->created_at ? $item->created_at->format('d M Y') : '—') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-label-data font-semibold text-primary whitespace-nowrap">
                                Rp {{ number_format((float) $item->total_amount, 2, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200">
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                    </span>
                                    <span class="font-caption text-caption font-semibold tracking-wide uppercase">{{ $item->status_label }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('reimbursements.show', $item) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-primary hover:bg-primary-container text-on-primary font-caption text-caption font-semibold shadow-xs transition-colors" title="Buka Detail Review">
                                        <span>Tinjau</span>
                                        <span class="material-symbols-outlined text-xs">arrow_forward</span>
                                    </a>
                                    <button onclick="openApproveModal('{{ $item->id }}', '{{ $item->number }}', '{{ number_format((float) $item->total_amount, 2, ',', '.') }}')" class="p-1.5 rounded-lg text-secondary hover:bg-secondary-container transition-colors" title="Setujui Cepat">
                                        <span class="material-symbols-outlined text-base">check_circle</span>
                                    </button>
                                    <button onclick="openReturnModal('{{ $item->id }}', '{{ $item->number }}', '{{ number_format((float) $item->total_amount, 2, ',', '.') }}')" class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-100 transition-colors" title="Kembalikan untuk Revisi">
                                        <span class="material-symbols-outlined text-base">undo</span>
                                    </button>
                                    <button onclick="openRejectModal('{{ $item->id }}', '{{ $item->number }}', '{{ number_format((float) $item->total_amount, 2, ',', '.') }}')" class="p-1.5 rounded-lg text-error hover:bg-error-container transition-colors" title="Tolak Pengajuan">
                                        <span class="material-symbols-outlined text-base">cancel</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <div class="w-14 h-14 rounded-full bg-secondary-container/60 text-secondary flex items-center justify-center">
                                        <span class="material-symbols-outlined text-3xl">task_alt</span>
                                    </div>
                                    <div class="flex flex-col gap-1 max-w-md">
                                        <span class="font-headline-sm text-body-lg font-bold text-on-surface">Semua Antrean Telah Selesai Diproses</span>
                                        <p class="font-body-sm text-body-sm text-on-surface-variant">
                                            Tidak ada permohonan biaya atau reimbursement yang membutuhkan tindakan persetujuan Anda saat ini.
                                        </p>
                                    </div>
                                    <a href="{{ route('dashboard') }}" class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface font-body-sm text-body-sm font-medium transition-colors">
                                        <span class="material-symbols-outlined text-base">grid_view</span>
                                        <span>Kembali ke Dashboard</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards View (< 768px) --}}
        <div class="divide-y divide-surface-container-low md:hidden" id="approvalMobileList">
            @forelse($pendingApprovals as $item)
                <div class="p-4 flex flex-col gap-3 approval-card" data-search="{{ strtolower($item->number . ' ' . $item->user->name . ' ' . ($item->department ?? '') . ' ' . ($item->purpose ?? '')) }}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <div class="w-9 h-9 rounded-full bg-primary-fixed text-on-primary-fixed flex items-center justify-center font-headline-sm text-xs font-bold shrink-0">
                                {{ strtoupper(substr($item->user->name, 0, 2)) }}
                            </div>
                            <div class="flex flex-col">
                                <span class="font-body-md text-body-md font-bold text-on-surface">{{ $item->user->name }}</span>
                                <span class="font-caption text-caption text-outline">{{ $item->department ?? $item->user->department ?? 'Umum' }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-label-data text-body-md font-bold text-primary block">
                                Rp {{ number_format((float) $item->total_amount, 2, ',', '.') }}
                            </span>
                            <span class="font-label-data-sm text-caption text-outline">
                                {{ $item->submitted_at ? $item->submitted_at->format('d M Y') : ($item->created_at ? $item->created_at->format('d M Y') : '—') }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('reimbursements.show', $item) }}" class="font-label-data font-semibold text-primary hover:underline text-xs">
                            {{ $item->number }}
                        </a>
                        <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 text-caption font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            <span>{{ $item->status_label }}</span>
                        </div>
                    </div>

                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                        {{ $item->purpose ?? $item->description ?? 'Pengajuan Biaya' }}
                    </p>

                    <div class="flex items-center gap-2 pt-2 border-t border-outline-variant/20">
                        <a href="{{ route('reimbursements.show', $item) }}" class="flex-1 py-2 px-3 rounded-lg bg-primary hover:bg-primary-container text-on-primary font-caption text-caption font-semibold text-center shadow-xs transition-colors">
                            Tinjau Detail
                        </a>
                        <button onclick="openApproveModal('{{ $item->id }}', '{{ $item->number }}', '{{ number_format((float) $item->total_amount, 2, ',', '.') }}')" class="p-2 rounded-lg bg-secondary-container text-on-secondary-container hover:bg-secondary hover:text-on-secondary transition-colors" title="Setujui">
                            <span class="material-symbols-outlined text-lg">check</span>
                        </button>
                        <button onclick="openReturnModal('{{ $item->id }}', '{{ $item->number }}', '{{ number_format((float) $item->total_amount, 2, ',', '.') }}')" class="p-2 rounded-lg bg-amber-100 text-amber-900 hover:bg-amber-200 transition-colors" title="Revisi">
                            <span class="material-symbols-outlined text-lg">undo</span>
                        </button>
                        <button onclick="openRejectModal('{{ $item->id }}', '{{ $item->number }}', '{{ number_format((float) $item->total_amount, 2, ',', '.') }}')" class="p-2 rounded-lg bg-error-container text-on-error-container hover:bg-error hover:text-on-error transition-colors" title="Tolak">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center flex flex-col items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-4xl text-secondary">task_alt</span>
                    <span class="font-headline-sm text-body-md font-bold text-on-surface">Semua Antrean Selesai</span>
                    <span class="font-body-sm text-body-sm text-outline">Tidak ada pengajuan yang membutuhkan tindakan Anda.</span>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($pendingApprovals->hasPages())
            <div class="p-space-md border-t border-outline-variant/30 bg-surface-container-low/40">
                {{ $pendingApprovals->links() }}
            </div>
        @endif
    </div>
</div>

{{-- MODAL 1: SETUJUI PENGAJUAN --}}
<div id="approveModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-xl max-w-md w-full p-6 shadow-xl border border-outline-variant/40 flex flex-col gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">check_circle</span>
            </div>
            <div>
                <h3 class="font-headline-sm text-headline-sm text-on-surface">Setujui Pengajuan Biaya</h3>
                <p class="font-caption text-caption text-outline">Nomor: <span id="approveModalNumber" class="font-bold text-primary"></span></p>
            </div>
        </div>

        <div class="p-3 rounded-lg bg-surface-container-low text-body-sm font-body-sm">
            <div class="flex justify-between items-center">
                <span class="text-outline">Total Nominal:</span>
                <span id="approveModalAmount" class="font-label-data font-bold text-primary"></span>
            </div>
        </div>

        <form id="approveForm" method="POST" action="">
            @csrf
            <div class="flex flex-col gap-1.5 mb-4">
                <label for="approveCommentInput" class="font-caption text-caption uppercase font-semibold text-on-surface">
                    Catatan Persetujuan (Opsional)
                </label>
                <textarea id="approveCommentInput" name="comment" rows="2" placeholder="Disetujui sesuai rencana anggaran kegiatan..." class="w-full p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/50 text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-outline-variant/20">
                <button type="button" onclick="closeModal('approveModal')" class="px-4 py-2 rounded-lg bg-surface-container text-on-surface hover:bg-surface-container-high font-body-sm text-body-sm font-medium transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-secondary hover:bg-secondary/90 text-on-secondary font-headline-sm text-body-sm font-semibold shadow-xs transition-colors flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base">check</span>
                    <span>Ya, Setujui Pengajuan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: KEMBALIKAN UNTUK REVISI --}}
<div id="returnModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-xl max-w-md w-full p-6 shadow-xl border border-outline-variant/40 flex flex-col gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">undo</span>
            </div>
            <div>
                <h3 class="font-headline-sm text-headline-sm text-on-surface">Kembalikan untuk Revisi</h3>
                <p class="font-caption text-caption text-outline">Nomor: <span id="returnModalNumber" class="font-bold text-amber-800"></span></p>
            </div>
        </div>

        <form id="returnForm" method="POST" action="">
            @csrf
            <div class="flex flex-col gap-1.5 mb-4">
                <div class="flex justify-between items-center">
                    <label for="returnCommentInput" class="font-caption text-caption uppercase font-semibold text-amber-900">
                        Catatan Instruksi Perbaikan <span class="text-error">*</span>
                    </label>
                    <span class="font-caption text-caption text-outline">Wajib</span>
                </div>
                <textarea id="returnCommentInput" name="comment" rows="3" required placeholder="Lampirkan nota resmi asli bertanda tangan atau sesuaikan rincian..." class="w-full p-2.5 rounded-lg bg-surface-container-low border border-amber-300 text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-2 focus:ring-amber-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-outline-variant/20">
                <button type="button" onclick="closeModal('returnModal')" class="px-4 py-2 rounded-lg bg-surface-container text-on-surface hover:bg-surface-container-high font-body-sm text-body-sm font-medium transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-headline-sm text-body-sm font-semibold shadow-xs transition-colors flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base">undo</span>
                    <span>Kembalikan Berkas</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: TOLAK PENGAJUAN --}}
<div id="rejectModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-xl max-w-md w-full p-6 shadow-xl border border-outline-variant/40 flex flex-col gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-error-container text-on-error-container flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">cancel</span>
            </div>
            <div>
                <h3 class="font-headline-sm text-headline-sm text-on-surface">Tolak Pengajuan Biaya</h3>
                <p class="font-caption text-caption text-outline">Nomor: <span id="rejectModalNumber" class="font-bold text-error"></span></p>
            </div>
        </div>

        <form id="rejectForm" method="POST" action="">
            @csrf
            <div class="flex flex-col gap-1.5 mb-4">
                <div class="flex justify-between items-center">
                    <label for="rejectReasonInput" class="font-caption text-caption uppercase font-semibold text-error">
                        Alasan Penolakan <span class="text-error">*</span>
                    </label>
                    <span class="font-caption text-caption text-outline">Wajib</span>
                </div>
                <textarea id="rejectReasonInput" name="reason" rows="3" required placeholder="Jelaskan alasan penolakan sesuai kebijakan dinas kantor..." class="w-full p-2.5 rounded-lg bg-surface-container-low border border-error/40 text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-2 focus:ring-error"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-outline-variant/20">
                <button type="button" onclick="closeModal('rejectModal')" class="px-4 py-2 rounded-lg bg-surface-container text-on-surface hover:bg-surface-container-high font-body-sm text-body-sm font-medium transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-error text-on-error hover:bg-error/90 font-headline-sm text-body-sm font-semibold shadow-xs transition-colors flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base">close</span>
                    <span>Tolak Pengajuan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openApproveModal(id, number, amount) {
    document.getElementById('approveModalNumber').innerText = number;
    document.getElementById('approveModalAmount').innerText = 'Rp ' + amount;
    document.getElementById('approveForm').action = '/approvals/' + id + '/approve';
    document.getElementById('approveModal').classList.remove('hidden');
}

function openReturnModal(id, number, amount) {
    document.getElementById('returnModalNumber').innerText = number;
    document.getElementById('returnForm').action = '/approvals/' + id + '/return';
    document.getElementById('returnModal').classList.remove('hidden');
}

function openRejectModal(id, number, amount) {
    document.getElementById('rejectModalNumber').innerText = number;
    document.getElementById('rejectForm').action = '/approvals/' + id + '/reject';
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function filterApprovalTable() {
    const input = document.getElementById('approvalSearchInput').value.toLowerCase();
    
    // Desktop rows
    const rows = document.querySelectorAll('.approval-row');
    rows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        row.style.display = text.includes(input) ? '' : 'none';
    });

    // Mobile cards
    const cards = document.querySelectorAll('.approval-card');
    cards.forEach(card => {
        const text = card.getAttribute('data-search') || '';
        card.style.display = text.includes(input) ? '' : 'none';
    });
}

// Close modal on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal('approveModal');
        closeModal('returnModal');
        closeModal('rejectModal');
    }
});
</script>
@endsection

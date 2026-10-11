@extends('layouts.app')
@section('title', 'Manajemen Reimbursement — Finance Office')

@section('content')
<div class="flex flex-col gap-space-xl">
    
    <!-- 1. Header Section with Breadcrumb and Actions -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-space-lg">
        <div class="flex flex-col gap-1 min-w-0">
            <div class="flex items-center gap-2 font-caption text-caption text-outline uppercase tracking-wider">
                <span class="hover:text-primary cursor-pointer transition-colors">Treasury</span>
                <span>/</span>
                <span class="hover:text-primary cursor-pointer transition-colors">Operasional</span>
                <span>/</span>
                <span class="text-on-surface dark:text-slate-200 font-semibold">Reimbursement</span>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="font-headline-lg text-headline-lg text-on-surface dark:text-white tracking-tight">Pengajuan Reimbursement</h1>
                <span class="font-caption text-caption px-2 py-0.5 rounded-full bg-surface-container-high dark:bg-slate-800 text-primary dark:text-primary-fixed font-semibold">Q4 {{ date('Y') }}</span>
            </div>
            <p class="font-body-md text-body-md text-on-surface-variant dark:text-slate-400 max-w-2xl">
                Kelola, verifikasi, dan pantau status klaim reimbursement operasional dan dinas karyawan dengan transparansi multi-level persetujuan.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-space-sm flex-wrap shrink-0">
            @if(auth()->user()->isFinance() || auth()->user()->isDirector() || auth()->user()->isAdmin())
                <a href="{{ route('reports.index') }}" class="group flex items-center gap-2 px-3.5 py-2 rounded-xl bg-surface-container-lowest dark:bg-slate-800 hover:bg-surface-container dark:hover:bg-slate-700 text-on-surface dark:text-slate-200 font-label-data text-label-data shadow-xs border border-outline-variant/40 dark:border-slate-700 transition-all">
                    <span class="material-symbols-outlined text-outline group-hover:text-on-surface text-lg transition-colors">file_download</span>
                    <span>Ekspor Laporan</span>
                </a>
            @endif
            <a href="{{ route('reimbursements.create') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-on-primary font-headline-sm text-body-md shadow-xs hover:shadow-md transition-all active:scale-[0.99]">
                <span class="material-symbols-outlined text-lg">add_circle</span>
                <span>+ Buat Pengajuan Baru</span>
            </a>
        </div>
    </div>

    <!-- 2. Stat KPI Strip (High-density institutional ledger style) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-space-md">
        
        <!-- KPI 1: Total Claims -->
        <div class="p-space-lg rounded-xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/40 dark:border-slate-700 shadow-xs flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute right-0 top-0 bottom-0 w-1.5 bg-primary rounded-r"></div>
            <div class="flex items-center justify-between">
                <span class="font-caption text-caption uppercase tracking-wider text-outline dark:text-slate-400 font-semibold">Total Klaim Aktif</span>
                <span class="p-1.5 rounded-lg bg-surface-container dark:bg-slate-700 text-primary dark:text-primary-fixed">
                    <span class="material-symbols-outlined text-lg">account_balance_wallet</span>
                </span>
            </div>
            <div class="mt-3">
                <div class="font-label-data text-headline-lg font-bold text-on-surface dark:text-white tabular-nums">
                    Rp {{ number_format((float) $totalClaims, 2, ',', '.') }}
                </div>
                <div class="flex items-center gap-2 mt-1.5">
                    <span class="font-caption text-caption px-1.5 py-0.5 rounded bg-surface-container-high dark:bg-slate-700 text-primary dark:text-primary-fixed font-semibold">
                        {{ $reimbursements->total() }} Pengajuan
                    </span>
                    <span class="font-body-sm text-body-sm text-outline dark:text-slate-400 flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-sm text-secondary">trending_up</span>
                        Akumulasi valid
                    </span>
                </div>
            </div>
        </div>

        <!-- KPI 2: Menunggu Persetujuan -->
        <div class="p-space-lg rounded-xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/40 dark:border-slate-700 shadow-xs flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute right-0 top-0 bottom-0 w-1.5 bg-amber-500 rounded-r"></div>
            <div class="flex items-center justify-between">
                <span class="font-caption text-caption uppercase tracking-wider text-outline dark:text-slate-400 font-semibold">Menunggu Approval</span>
                <span class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                    <span class="material-symbols-outlined text-lg">hourglass_top</span>
                </span>
            </div>
            <div class="mt-3">
                <div class="font-label-data text-headline-lg font-bold text-on-surface dark:text-white tabular-nums">
                    {{ $pendingCount }} <span class="font-body-md text-body-md text-outline dark:text-slate-400 font-normal">Pengajuan</span>
                </div>
                <div class="flex items-center gap-2 mt-1.5">
                    <span class="font-caption text-caption px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        Evaluasi Berjenjang
                    </span>
                </div>
            </div>
        </div>

        <!-- KPI 3: Disetujui / Selesai -->
        <div class="p-space-lg rounded-xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/40 dark:border-slate-700 shadow-xs flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute right-0 top-0 bottom-0 w-1.5 bg-secondary rounded-r"></div>
            <div class="flex items-center justify-between">
                <span class="font-caption text-caption uppercase tracking-wider text-outline dark:text-slate-400 font-semibold">Disetujui / Selesai</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                    <span class="material-symbols-outlined text-lg">check_circle</span>
                </span>
            </div>
            <div class="mt-3">
                <div class="font-label-data text-headline-lg font-bold text-on-surface dark:text-white tabular-nums">
                    {{ $approvedCount }} <span class="font-body-md text-body-md text-outline dark:text-slate-400 font-normal">Pengajuan</span>
                </div>
                <div class="flex items-center gap-2 mt-1.5">
                    <span class="font-caption text-caption px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:border-emerald-800 border border-emerald-200 font-semibold">
                        Siap Cair / Dibayar
                    </span>
                </div>
            </div>
        </div>

        <!-- KPI 4: Ditolak / Revisi -->
        <div class="p-space-lg rounded-xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/40 dark:border-slate-700 shadow-xs flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute right-0 top-0 bottom-0 w-1.5 bg-rose-500 rounded-r"></div>
            <div class="flex items-center justify-between">
                <span class="font-caption text-caption uppercase tracking-wider text-outline dark:text-slate-400 font-semibold">Ditolak / Revisi</span>
                <span class="p-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                    <span class="material-symbols-outlined text-lg">cancel</span>
                </span>
            </div>
            <div class="mt-3">
                <div class="font-label-data text-headline-lg font-bold text-on-surface dark:text-white tabular-nums">
                    {{ $rejectedCount }} <span class="font-body-md text-body-md text-outline dark:text-slate-400 font-normal">Pengajuan</span>
                </div>
                <div class="flex items-center gap-2 mt-1.5">
                    <span class="font-caption text-caption px-1.5 py-0.5 rounded bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800 font-semibold">
                        Perlu Tindakan
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- 3. Interactive Filter & Search Toolbar -->
    <div class="bg-surface-container-lowest dark:bg-slate-800 p-space-md sm:p-space-lg rounded-xl shadow-xs border border-outline-variant/40 dark:border-slate-700 flex flex-col gap-space-md">
        
        <!-- Filter Tabs / Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 border-b border-outline-variant/20 dark:border-slate-700/60">
            @php
                $tabs = [
                    'all' => 'Semua',
                    'draft' => 'Draft',
                    'pending' => 'Menunggu Approval',
                    'approved' => 'Disetujui',
                    'paid' => 'Dibayar',
                    'returned' => 'Perlu Revisi',
                    'rejected' => 'Ditolak',
                ];
            @endphp
            @foreach($tabs as $key => $label)
                <a href="{{ route('reimbursements.index', ['tab' => $key, 'q' => $search]) }}"
                   class="px-3 py-1.5 rounded-lg font-caption text-caption uppercase tracking-wider font-semibold transition-all whitespace-nowrap {{ $tab === $key ? 'bg-primary text-on-primary shadow-xs' : 'bg-surface-container-low dark:bg-slate-900/60 text-on-surface-variant dark:text-slate-300 hover:bg-surface-container hover:text-on-surface dark:hover:text-white' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- Search Form and Quick Actions -->
        <form method="GET" action="{{ route('reimbursements.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-space-md">
            <input type="hidden" name="tab" value="{{ $tab }}">
            
            <div class="relative w-full sm:max-w-md">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-lg pointer-events-none">search</span>
                <input class="w-full pl-9 pr-14 py-2 bg-surface-container-low dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg font-body-sm text-body-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all" 
                       id="claimSearchInput" 
                       name="q"
                       value="{{ $search }}"
                       placeholder="Cari No. Pengajuan, Keperluan..." 
                       type="text"/>
                <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-1">
                    <kbd class="font-label-data-sm text-caption text-outline bg-surface-container dark:bg-slate-700 px-1.5 py-0.5 rounded border border-outline-variant/40 dark:border-slate-600">⌘K</kbd>
                </div>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-auto">
                <button type="submit" class="px-4 py-2 bg-surface-container-high dark:bg-slate-700 text-on-surface dark:text-slate-200 hover:bg-surface-container-highest dark:hover:bg-slate-600 rounded-lg font-body-sm text-body-sm font-semibold transition-colors">
                    Cari
                </button>
                @if($search || $tab !== 'all')
                    <a href="{{ route('reimbursements.index') }}" class="p-2 text-outline hover:text-on-surface rounded-lg hover:bg-surface-container dark:hover:bg-slate-700 transition-colors flex items-center gap-1 font-body-sm text-body-sm" title="Reset Filter">
                        <span class="material-symbols-outlined text-lg">filter_alt_off</span>
                        <span class="hidden sm:inline">Reset</span>
                    </a>
                @endif
            </div>
        </form>

    </div>

    <!-- 4. Main Data Table Container -->
    <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-xl shadow-xs border border-outline-variant/40 dark:border-slate-700 overflow-hidden flex flex-col">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse font-body-sm text-body-sm">
                <thead>
                    <tr class="bg-surface-container-low dark:bg-slate-900/60 text-outline dark:text-slate-400 font-semibold text-caption uppercase tracking-wider border-b border-outline-variant/40 dark:border-slate-700">
                        <th class="py-3 px-4" scope="col">No. Pengajuan</th>
                        <th class="py-3 px-4" scope="col">Keperluan / Kategori</th>
                        <th class="py-3 px-4" scope="col">Divisi</th>
                        <th class="py-3 px-4" scope="col">Tanggal Pengajuan</th>
                        <th class="py-3 px-4 text-right" scope="col">Total Nominal</th>
                        <th class="py-3 px-4" scope="col">Status &amp; Verifikasi</th>
                        <th class="py-3 px-4 text-center w-28" scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20 dark:divide-slate-700/60">
                    @forelse($reimbursements as $item)
                        <tr class="hover:bg-surface-container-low/60 dark:hover:bg-slate-700/40 transition-colors group">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <a href="{{ route('reimbursements.show', $item) }}" class="font-label-data-sm text-caption px-2 py-0.5 rounded bg-surface-container dark:bg-slate-700 font-semibold text-primary dark:text-primary-fixed hover:underline">
                                    {{ $item->number }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-col">
                                    <span class="font-headline-sm text-body-sm text-on-surface dark:text-slate-100 font-semibold group-hover:text-primary transition-colors">
                                        {{ $item->purpose ?? $item->description ?? 'Pengajuan Biaya' }}
                                    </span>
                                    <span class="font-caption text-caption text-outline dark:text-slate-400">
                                        {{ $item->category ?? 'Operasional' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-lg bg-surface-container dark:bg-slate-700 text-on-surface-variant dark:text-slate-300 font-caption text-caption font-medium">
                                    {{ $item->department ?? 'Umum' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-on-surface-variant dark:text-slate-300">
                                <div class="flex flex-col">
                                    <span class="font-label-data text-body-sm text-on-surface dark:text-slate-200">
                                        {{ $item->transaction_date ? $item->transaction_date->format('d M Y') : ($item->created_at ? $item->created_at->format('d M Y') : '—') }}
                                    </span>
                                    @if($item->created_at)
                                        <span class="font-caption text-caption text-outline dark:text-slate-400">{{ $item->created_at->format('H:i') }} WIB</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-right">
                                <span class="font-label-data text-body-md font-semibold text-on-surface dark:text-white tabular-nums">
                                    Rp {{ number_format((float) $item->total_amount, 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <x-status-badge :status="$item->status" />
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('reimbursements.show', $item) }}" class="p-1 rounded-lg text-outline dark:text-slate-400 hover:text-primary dark:hover:text-primary-fixed hover:bg-surface-container dark:hover:bg-slate-700 transition-colors" title="Lihat Detail &amp; Dokumen">
                                        <span class="material-symbols-outlined text-lg">visibility</span>
                                    </a>
                                    @if($item->isEditable())
                                        <a href="{{ route('reimbursements.edit', $item) }}" class="p-1 rounded-lg text-outline dark:text-slate-400 hover:text-on-surface dark:hover:text-white hover:bg-surface-container dark:hover:bg-slate-700 transition-colors" title="Edit Pengajuan">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center gap-2 max-w-md mx-auto">
                                    <div class="w-12 h-12 rounded-full bg-surface-container dark:bg-slate-700 text-outline flex items-center justify-center">
                                        <span class="material-symbols-outlined text-2xl">receipt_long</span>
                                    </div>
                                    <h3 class="font-headline-md text-headline-sm text-on-surface dark:text-white font-semibold">Belum Ada Pengajuan Reimbursement</h3>
                                    <p class="font-body-sm text-body-sm text-outline dark:text-slate-400">
                                        Mulai ajukan klaim biaya operasional atau dinas kantor dengan mengisi formulir rincian pengeluaran.
                                    </p>
                                    <a href="{{ route('reimbursements.create') }}" class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-headline-sm text-body-sm shadow-xs transition-all">
                                        <span class="material-symbols-outlined text-base">add_circle</span>
                                        <span>+ Buat Pengajuan Pertama</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        @if($reimbursements->hasPages())
            <div class="px-space-lg py-3.5 bg-surface-container-lowest dark:bg-slate-800 border-t border-outline-variant/30 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-space-md">
                <div class="text-on-surface-variant dark:text-slate-400 font-body-sm text-body-sm">
                    Menampilkan <strong class="text-on-surface dark:text-white font-semibold">{{ $reimbursements->firstItem() ?? 0 }}-{{ $reimbursements->lastItem() ?? 0 }}</strong> dari <strong class="text-on-surface dark:text-white font-semibold">{{ $reimbursements->total() }}</strong> data
                </div>
                <div>
                    {{ $reimbursements->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- 5. Contextual Guidance / Policy SOP Banner -->
    <div class="p-space-lg rounded-xl bg-surface-container-low dark:bg-slate-800/60 border border-outline-variant/30 dark:border-slate-700 flex flex-col md:flex-row items-start md:items-center justify-between gap-space-md">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-primary dark:text-primary-fixed text-xl mt-0.5">verified_user</span>
            <div class="flex flex-col">
                <span class="font-headline-sm text-body-sm font-semibold text-on-surface dark:text-white">Protokol Kebijakan Reimbursement (SOP-FIN-04)</span>
                <p class="font-body-sm text-body-sm text-on-surface-variant dark:text-slate-400 mt-0.5">
                    Pengajuan nominal di atas Rp 5.000.000 mewajibkan otorisasi tingkat Direktur setelah verifikasi kelengkapan nota dan pos anggaran oleh Finance Specialist.
                </p>
            </div>
        </div>
        @if(auth()->user()->isFinance() || auth()->user()->isDirector() || auth()->user()->isAdmin())
            <a class="inline-flex items-center gap-1 font-caption text-caption uppercase tracking-wider font-semibold text-primary dark:text-primary-fixed hover:underline shrink-0" href="{{ route('reports.index') }}">
                <span>Pedoman Pengajuan Klaim</span>
                <span class="material-symbols-outlined text-base">arrow_forward</span>
            </a>
        @endif
    </div>

</div>
@endsection

@extends('layouts.app')
@section('title', 'Transaksi Kas — Finance Office')
@section('header', 'Transaksi Kas Kantor')

@section('content')
<div class="flex flex-col gap-space-xl max-w-7xl mx-auto w-full">

    {{-- Breadcrumb & Navigation Topbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
        <nav aria-label="Breadcrumb" class="flex items-center flex-wrap gap-2 text-body-sm font-body-sm text-on-surface-variant">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Treasury</a>
            <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
            <span class="hover:text-primary transition-colors">Buku Kas</span>
            <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
            <span class="text-on-surface font-semibold">Daftar Transaksi Kas</span>
        </nav>
        @can('create', App\Models\Transaction::class)
            <a href="{{ route('transactions.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary hover:bg-primary-container text-on-primary font-headline-sm text-body-sm font-semibold shadow-xs transition-colors">
                <span class="material-symbols-outlined text-lg">add_circle</span>
                <span>Catat Transaksi Baru</span>
            </a>
        @endcan
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

    {{-- Header Section (Stitch Header Style) --}}
    <header class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg lg:p-space-xl flex flex-col lg:flex-row lg:items-start justify-between gap-space-lg border border-outline-variant/30">
        <div class="flex flex-col gap-space-xs max-w-3xl">
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Transaksi Kas Kantor</h1>
                <span class="px-2 py-0.5 rounded-lg bg-surface-container text-on-surface-variant font-label-data-sm text-label-data-sm font-semibold">
                    SOP-FIN-04
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-caption text-caption font-bold">
                    <span class="material-symbols-outlined text-xs">account_balance</span>
                    Buku Besar Kas Aktif
                </span>
            </div>
            <p class="font-body-md text-body-md text-on-surface-variant">
                Pencatatan mutasi arus kas masuk, pengeluaran operasional divisi, dan realisasi reimbursement yang telah dibayarkan.
            </p>
        </div>

        <div class="flex items-center flex-wrap gap-2.5 shrink-0">
            <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container text-on-surface font-body-sm text-body-sm font-medium hover:bg-surface-container-high transition-colors shadow-sm">
                <span class="material-symbols-outlined text-lg text-on-surface-variant">summarize</span>
                <span>Laporan Arus Kas</span>
            </a>
            <a href="{{ route('budgets.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container text-on-surface font-body-sm text-body-sm font-medium hover:bg-surface-container-high transition-colors shadow-sm">
                <span class="material-symbols-outlined text-lg text-on-surface-variant">pie_chart</span>
                <span>Alokasi Anggaran</span>
            </a>
        </div>
    </header>

    {{-- Ringkasan Saldo dan Mutasi Kas (3 Metric KPI Strip) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter-desktop">
        {{-- Card 1: Saldo Kas Berjalan --}}
        <div class="p-space-lg rounded-xl bg-surface-container-lowest border border-outline-variant/30 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="font-caption text-caption text-outline uppercase font-semibold">Saldo Kas Berjalan</span>
                <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-lg">account_balance</span>
                </div>
            </div>
            <div class="my-3">
                <span class="font-headline-lg text-headline-lg font-bold text-primary font-label-data">
                    {{ $summary['formatted_balance'] }}
                </span>
            </div>
            <div class="flex items-center gap-1.5 text-caption font-medium text-on-surface-variant">
                <span class="material-symbols-outlined text-xs text-secondary">trending_flat</span>
                <span>Pemasukan aktif &minus; Pengeluaran aktif</span>
            </div>
        </div>

        {{-- Card 2: Total Pemasukan --}}
        <div class="p-space-lg rounded-xl bg-surface-container-lowest border border-outline-variant/30 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="font-caption text-caption text-outline uppercase font-semibold">Total Penerimaan Kas</span>
                <div class="w-8 h-8 rounded-full bg-secondary-container text-secondary flex items-center justify-center">
                    <span class="material-symbols-outlined text-lg">arrow_downward</span>
                </div>
            </div>
            <div class="my-3">
                <span class="font-headline-lg text-headline-lg font-bold text-secondary font-label-data">
                    +{{ $summary['formatted_income'] }}
                </span>
            </div>
            <div class="flex items-center gap-1.5 text-caption font-medium text-secondary">
                <span class="material-symbols-outlined text-xs">trending_up</span>
                <span>Seluruh penerimaan kas terkonfirmasi</span>
            </div>
        </div>

        {{-- Card 3: Total Pengeluaran --}}
        <div class="p-space-lg rounded-xl bg-surface-container-lowest border border-outline-variant/30 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="font-caption text-caption text-outline uppercase font-semibold">Total Pengeluaran Kas</span>
                <div class="w-8 h-8 rounded-full bg-error-container text-error flex items-center justify-center">
                    <span class="material-symbols-outlined text-lg">arrow_upward</span>
                </div>
            </div>
            <div class="my-3">
                <span class="font-headline-lg text-headline-lg font-bold text-error font-label-data">
                    -{{ $summary['formatted_expense'] }}
                </span>
            </div>
            <div class="flex items-center gap-1.5 text-caption font-medium text-error">
                <span class="material-symbols-outlined text-xs">trending_down</span>
                <span>Seluruh pengeluaran operasional & klaim</span>
            </div>
        </div>
    </div>

    {{-- Filter Toolbar & Search --}}
    <div class="p-space-lg bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/30 flex flex-col gap-space-md">
        {{-- Tab Jenis Mutasi Pills --}}
        <div class="flex items-center gap-2 flex-wrap pb-space-sm border-b border-outline-variant/20">
            <a href="{{ route('transactions.index', array_merge(request()->except('type', 'page'), ['type' => 'all'])) }}"
               class="px-3.5 py-1.5 rounded-lg font-caption text-caption font-semibold transition-all {{ ($type === 'all' || !$type) ? 'bg-primary text-on-primary shadow-xs' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high' }}">
                Semua Mutasi
            </a>
            <a href="{{ route('transactions.index', array_merge(request()->except('type', 'page'), ['type' => 'income'])) }}"
               class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-lg font-caption text-caption font-semibold transition-all {{ $type === 'income' ? 'bg-secondary text-on-secondary shadow-xs' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high' }}">
                <span class="w-2 h-2 rounded-full bg-secondary {{ $type === 'income' ? 'bg-white' : '' }}"></span>
                <span>Pemasukan (Kredit)</span>
            </a>
            <a href="{{ route('transactions.index', array_merge(request()->except('type', 'page'), ['type' => 'expense'])) }}"
               class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-lg font-caption text-caption font-semibold transition-all {{ $type === 'expense' ? 'bg-error text-on-error shadow-xs' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high' }}">
                <span class="w-2 h-2 rounded-full bg-error {{ $type === 'expense' ? 'bg-white' : '' }}"></span>
                <span>Pengeluaran (Debit)</span>
            </a>
        </div>

        {{-- Filter Inputs Form --}}
        <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-space-sm items-end">
            <input type="hidden" name="type" value="{{ $type }}">

            {{-- Pencarian --}}
            <div class="flex flex-col gap-1 sm:col-span-2 lg:col-span-2">
                <label for="search" class="font-caption text-caption uppercase font-semibold text-on-surface">Pencarian</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-outline text-base">search</span>
                    <input type="text" id="search" name="q" value="{{ $search }}" placeholder="No. TRX, keterangan, vendor, ref..." class="w-full pl-8 pr-3 py-2 bg-surface-container-low border border-outline-variant/50 rounded-lg text-body-sm font-body-sm text-on-surface placeholder:text-outline focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                </div>
            </div>

            {{-- Status Pembayaran --}}
            <div class="flex flex-col gap-1">
                <label for="status" class="font-caption text-caption uppercase font-semibold text-on-surface">Status Pembayaran</label>
                <select id="status" name="status" class="w-full py-2 px-2.5 bg-surface-container-low border border-outline-variant/50 rounded-lg text-body-sm font-body-sm text-on-surface focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                    <option value="">Semua Status</option>
                    <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                    <option value="scheduled" {{ $status === 'scheduled' ? 'selected' : '' }}>Terjadwal (Scheduled)</option>
                    <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>Belum Dibayar (Unpaid)</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Dibatalkan (Cancelled)</option>
                </select>
            </div>

            {{-- Divisi --}}
            <div class="flex flex-col gap-1">
                <label for="department" class="font-caption text-caption uppercase font-semibold text-on-surface">Divisi Terkait</label>
                <select id="department" name="department" class="w-full py-2 px-2.5 bg-surface-container-low border border-outline-variant/50 rounded-lg text-body-sm font-body-sm text-on-surface focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                    <option value="">Semua Divisi</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->name }}" {{ $department === $dept->name ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Buttons Action --}}
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-lg bg-primary hover:bg-primary-container text-on-primary font-caption text-caption font-semibold shadow-xs transition-colors flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-base">filter_list</span>
                    <span>Filter</span>
                </button>
                <a href="{{ route('transactions.index') }}" class="py-2 px-3 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface font-caption text-caption font-medium transition-colors" title="Reset Filter">
                    <span class="material-symbols-outlined text-base">restart_alt</span>
                </a>
            </div>
        </form>
    </div>

    {{-- High-Density Table Transaksi (Stitch Enterprise Style) --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        
        {{-- Desktop Table View --}}
        <div class="overflow-x-auto hidden md:block">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-high text-on-surface-variant font-caption text-caption uppercase tracking-wider font-semibold border-b border-outline-variant/30">
                        <th class="py-3 px-3 text-center w-12">#</th>
                        <th class="py-3 px-4 w-44">Nomor TRX</th>
                        <th class="py-3 px-3 w-32">Tanggal</th>
                        <th class="py-3 px-3 w-28">Jenis</th>
                        <th class="py-3 px-4 min-w-[220px]">Kategori &amp; Keterangan</th>
                        <th class="py-3 px-4 min-w-[160px]">Rekanan / Vendor</th>
                        <th class="py-3 px-4 text-right w-40">Nominal (IDR)</th>
                        <th class="py-3 px-3 w-32">Status</th>
                        <th class="py-3 px-4 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-low font-body-sm text-body-sm text-on-surface">
                    @forelse($transactions as $idx => $trx)
                        <tr class="hover:bg-surface-container-low/60 transition-colors">
                            <td class="py-3.5 px-3 text-center font-label-data-sm text-outline">
                                {{ str_pad($transactions->firstItem() + $idx, 2, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="py-3.5 px-4 font-label-data font-semibold whitespace-nowrap">
                                <a href="{{ route('transactions.show', $trx) }}" class="text-primary hover:underline flex items-center gap-1 group">
                                    <span>{{ $trx->transaction_number }}</span>
                                    <span class="material-symbols-outlined text-xs opacity-0 group-hover:opacity-100 transition-opacity">open_in_new</span>
                                </a>
                                @if($trx->reimbursement)
                                    <div class="font-caption text-caption text-outline font-label-data-sm mt-0.5">
                                        Reim: <a href="{{ route('reimbursements.show', $trx->reimbursement) }}" class="hover:underline text-primary">{{ $trx->reimbursement->reimbursement_number }}</a>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 font-label-data-sm text-on-surface-variant whitespace-nowrap">
                                {{ $trx->transaction_date ? \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') : '—' }}
                            </td>
                            <td class="py-3.5 px-3 whitespace-nowrap">
                                @if($trx->isIncome())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-caption text-caption font-semibold">
                                        <span class="material-symbols-outlined text-xs">arrow_downward</span> Masuk
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-error-container text-on-error-container font-caption text-caption font-semibold">
                                        <span class="material-symbols-outlined text-xs">arrow_upward</span> Keluar
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-on-surface flex items-center gap-1.5">
                                    <span>{{ $trx->category }}</span>
                                    @if($trx->department)
                                        <span class="text-outline">&bull;</span>
                                        <span class="font-caption text-caption text-outline">{{ $trx->department }}</span>
                                    @endif
                                </div>
                                <p class="font-caption text-caption text-on-surface-variant line-clamp-1 mt-0.5" title="{{ $trx->description }}">
                                    {{ $trx->description }}
                                </p>
                            </td>
                            <td class="py-3.5 px-4 text-on-surface-variant">
                                <span class="truncate block max-w-[160px]" title="{{ $trx->vendor ?? '—' }}">
                                    {{ $trx->vendor ?: '—' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-label-data font-semibold whitespace-nowrap {{ $trx->isCancelled() ? 'text-outline line-through' : ($trx->isIncome() ? 'text-secondary' : 'text-on-surface') }}">
                                {{ $trx->isIncome() ? '+' : '-' }}{{ $trx->formatted_amount }}
                            </td>
                            <td class="py-3.5 px-3 whitespace-nowrap">
                                <x-status-badge :status="$trx->payment_status" />
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <a href="{{ route('transactions.show', $trx) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface font-caption text-caption font-semibold transition-colors shadow-xs">
                                    <span>Rincian</span>
                                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <div class="w-14 h-14 rounded-full bg-surface-container text-outline flex items-center justify-center">
                                        <span class="material-symbols-outlined text-3xl">receipt_long</span>
                                    </div>
                                    <div class="flex flex-col gap-1 max-w-md">
                                        <span class="font-headline-sm text-body-lg font-bold text-on-surface">Tidak Ada Catatan Transaksi</span>
                                        <p class="font-body-sm text-body-sm text-on-surface-variant">
                                            Tidak ditemukan mutasi transaksi kas yang cocok dengan kriteria filter Anda saat ini.
                                        </p>
                                    </div>
                                    @can('create', App\Models\Transaction::class)
                                        <a href="{{ route('transactions.create') }}" class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary hover:bg-primary-container text-on-primary font-body-sm text-body-sm font-semibold transition-colors">
                                            <span class="material-symbols-outlined text-base">add</span>
                                            <span>Catat Transaksi Pertama</span>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards View (< 768px) --}}
        <div class="divide-y divide-surface-container-low md:hidden">
            @forelse($transactions as $trx)
                <div class="p-4 flex flex-col gap-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <a href="{{ route('transactions.show', $trx) }}" class="font-label-data font-bold text-primary hover:underline text-sm">
                                {{ $trx->transaction_number }}
                            </a>
                            <span class="font-caption text-caption text-outline block">
                                {{ $trx->transaction_date ? \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') : '—' }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="font-label-data text-body-md font-bold block {{ $trx->isCancelled() ? 'text-outline line-through' : ($trx->isIncome() ? 'text-secondary' : 'text-on-surface') }}">
                                {{ $trx->isIncome() ? '+' : '-' }}{{ $trx->formatted_amount }}
                            </span>
                            <x-status-badge :status="$trx->payment_status" />
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($trx->isIncome())
                            <span class="px-2 py-0.5 rounded bg-secondary-container text-on-secondary-container font-caption text-caption font-semibold">
                                Pemasukan
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded bg-error-container text-on-error-container font-caption text-caption font-semibold">
                                Pengeluaran
                            </span>
                        @endif
                        <span class="font-body-sm text-body-sm text-on-surface font-medium truncate">{{ $trx->category }}</span>
                    </div>

                    <p class="font-body-sm text-body-sm text-on-surface-variant line-clamp-2">
                        {{ $trx->description }}
                    </p>

                    <div class="flex items-center justify-between pt-2 border-t border-outline-variant/20">
                        <span class="font-caption text-caption text-outline">
                            Vendor: {{ $trx->vendor ?: '—' }}
                        </span>
                        <a href="{{ route('transactions.show', $trx) }}" class="inline-flex items-center gap-1 text-primary font-caption text-caption font-bold hover:underline">
                            <span>Lihat Rincian</span>
                            <span class="material-symbols-outlined text-xs">arrow_forward</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center flex flex-col items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-4xl text-outline">receipt_long</span>
                    <span class="font-headline-sm text-body-md font-bold text-on-surface">Belum Ada Transaksi</span>
                    <span class="font-body-sm text-body-sm text-outline">Tidak ada transaksi kas yang sesuai dengan filter.</span>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($transactions->hasPages())
            <div class="p-space-md border-t border-outline-variant/30 bg-surface-container-low/40">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Pemantauan Anggaran Divisi — Finance Office')
@section('header', 'Monitoring & Alokasi Anggaran')

@section('content')
<div class="flex flex-col gap-space-xl max-w-7xl mx-auto w-full">

    {{-- Breadcrumb & Navigation Topbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
        <nav aria-label="Breadcrumb" class="flex items-center flex-wrap gap-2 text-body-sm font-body-sm text-on-surface-variant">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Treasury</a>
            <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
            <span class="hover:text-primary transition-colors">Perencanaan &amp; Anggaran</span>
            <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
            <span class="text-on-surface font-semibold">Pemantauan Anggaran Divisi</span>
        </nav>
        @can('create', App\Models\Budget::class)
            <a href="{{ route('budgets.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary hover:bg-primary-container text-on-primary font-headline-sm text-body-sm font-semibold shadow-xs transition-colors">
                <span class="material-symbols-outlined text-lg">add_circle</span>
                <span>Alokasikan Anggaran Baru</span>
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

    {{-- Header Section (Stitch Screen 5 Style) --}}
    <header class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg lg:p-space-xl flex flex-col lg:flex-row lg:items-start justify-between gap-space-lg border border-outline-variant/30">
        <div class="flex flex-col gap-space-xs max-w-3xl">
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Pemantauan Anggaran Divisi</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-caption text-caption uppercase tracking-wider font-semibold bg-surface-container-high text-primary border border-outline-variant/50">
                    Tahun Fiskal 2026 - Q4
                </span>
                <span class="px-2 py-0.5 rounded-lg bg-surface-container text-on-surface-variant font-label-data-sm text-label-data-sm font-semibold">
                    SOP-FIN-04
                </span>
            </div>
            <p class="font-body-md text-body-md text-on-surface-variant">
                Daftar Anggaran (Budget) &bull; Pantau realisasi penyerapan anggaran operasional dan proyek per divisi secara real-time terhadap pagu yang ditetapkan.
            </p>
        </div>

        <div class="flex items-center flex-wrap gap-2.5 shrink-0">
            <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container text-on-surface font-body-sm text-body-sm font-medium hover:bg-surface-container-high transition-colors shadow-sm">
                <span class="material-symbols-outlined text-lg text-on-surface-variant">download</span>
                <span>Ekspor Laporan Anggaran</span>
            </a>
            <a href="{{ route('transactions.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container text-on-surface font-body-sm text-body-sm font-medium hover:bg-surface-container-high transition-colors shadow-sm">
                <span class="material-symbols-outlined text-lg text-on-surface-variant">receipt_long</span>
                <span>Buku Kas Transaksi</span>
            </a>
        </div>
    </header>

    {{-- Section 1: High-Level Summary Cards (Stitch Screen 5: 4 Metric Cards) --}}
    <section aria-label="Ringkasan Anggaran" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-md">
        {{-- Card 1: Total Anggaran Perusahaan --}}
        <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-xl p-space-md shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="font-caption text-caption uppercase tracking-wider text-outline font-semibold">Total Pagu Alokasi</span>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-primary/10 text-primary border border-primary/20">
                    <span class="material-symbols-outlined text-xs">account_balance</span>
                    100% Pagu
                </span>
            </div>
            <div class="my-space-sm">
                <p class="font-label-data text-headline-md text-on-surface font-bold tracking-tight">
                    {{ $metrics['formatted_total_allocated'] }}
                </p>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                    {{ $metrics['active_budgets_count'] }} pos anggaran aktif periode fiskal berjalan
                </p>
            </div>
            <div class="pt-space-xs border-t border-outline-variant/20 flex items-center justify-between text-outline font-caption text-caption">
                <span>Plafon Otorisasi Direksi</span>
                <span class="font-label-data-sm text-on-surface font-semibold">{{ $departments->count() }} Divisi Terdaftar</span>
            </div>
        </div>

        {{-- Card 2: Total Realisasi (Terpakai) --}}
        <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-xl p-space-md shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="font-caption text-caption uppercase tracking-wider text-outline font-semibold">Total Realisasi (Terpakai)</span>
                @if($metrics['overall_percentage'] >= 90)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                        <span class="material-symbols-outlined text-xs">error</span> Kritis
                    </span>
                @elseif($metrics['overall_percentage'] >= 75)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                        <span class="material-symbols-outlined text-xs">warning</span> Perhatian
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <span class="material-symbols-outlined text-xs">check_circle</span> Normal
                    </span>
                @endif
            </div>
            <div class="my-space-sm">
                <p class="font-label-data text-headline-md text-amber-700 font-bold tracking-tight">
                    {{ $metrics['formatted_total_used'] }}
                </p>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                    Penyerapan {{ $metrics['overall_percentage'] }}% dari total pagu
                </p>
            </div>
            <div class="w-full bg-surface-container-high h-1.5 rounded-full overflow-hidden">
                <div class="h-full rounded-full {{ $metrics['overall_percentage'] >= 90 ? 'bg-rose-600' : ($metrics['overall_percentage'] >= 75 ? 'bg-amber-500' : 'bg-emerald-600') }}" style="width: {{ min(100, $metrics['overall_percentage']) }}%"></div>
            </div>
        </div>

        {{-- Card 3: Sisa Anggaran Tersedia --}}
        <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-xl p-space-md shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="font-caption text-caption uppercase tracking-wider text-outline font-semibold">Sisa Anggaran Tersedia</span>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <span class="material-symbols-outlined text-xs">check_circle</span> Likuiditas Aman
                </span>
            </div>
            <div class="my-space-sm">
                <p class="font-label-data text-headline-md text-emerald-700 font-bold tracking-tight">
                    {{ $metrics['formatted_total_remaining'] }}
                </p>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                    Sisa dana yang dapat dicairkan kantor
                </p>
            </div>
            <div class="pt-space-xs border-t border-outline-variant/20 flex items-center justify-between text-outline font-caption text-caption">
                <span>Buffer Kas Operasional</span>
                <span class="font-label-data-sm text-emerald-700 font-semibold">{{ 100 - min(100, $metrics['overall_percentage']) }}% Saldo</span>
            </div>
        </div>

        {{-- Card 4: Status Departemen / Peringatan --}}
        <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-xl p-space-md shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="font-caption text-caption uppercase tracking-wider text-outline font-semibold">Status Ambang Batas</span>
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $metrics['exhausted_count'] > 0 ? 'bg-rose-400' : ($metrics['warning_count'] > 0 ? 'bg-amber-400' : 'bg-emerald-400') }} opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 {{ $metrics['exhausted_count'] > 0 ? 'bg-rose-600' : ($metrics['warning_count'] > 0 ? 'bg-amber-500' : 'bg-emerald-600') }}"></span>
                </span>
            </div>
            <div class="my-space-sm">
                <p class="font-label-data text-headline-md text-on-surface font-bold tracking-tight">
                    {{ $metrics['exhausted_count'] }} Habis &bull; {{ $metrics['warning_count'] }} Siaga
                </p>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                    @if($metrics['exhausted_count'] > 0 || $metrics['warning_count'] > 0)
                        Perhatian: Terdapat pos anggaran mendekati limit
                    @else
                        Semua pos anggaran dalam kondisi terkendali
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-1.5 pt-space-xs border-t border-outline-variant/20">
                <span class="h-2 flex-1 rounded-full bg-rose-600" title="{{ $metrics['exhausted_count'] }} Kuota Habis"></span>
                <span class="h-2 flex-1 rounded-full bg-amber-500" title="{{ $metrics['warning_count'] }} Peringatan (>=75%)"></span>
                <span class="h-2 flex-1 rounded-full bg-emerald-600" title="Aman (<75%)"></span>
            </div>
        </div>
    </section>

    {{-- Section 2: Department Budget Grid (Stitch Screen 5 Primary Cards per Budget) --}}
    <section aria-label="Kartu Anggaran Per Divisi" class="flex flex-col gap-space-md">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-headline-md text-headline-md text-on-surface font-semibold">Detail Alokasi per Departemen</h2>
                <p class="font-body-sm text-body-sm text-on-surface-variant">Monitoring ambang batas penyerapan (Aman &lt;75%, Peringatan &ge;75%, Kritis &ge;90%, Habis 100%)</p>
            </div>
            <span class="font-caption text-caption text-outline uppercase tracking-wider hidden sm:inline">Sinkronisasi Real-Time</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-space-lg">
            @forelse($budgets as $b)
                @php
                    $isExhausted = $b->usage_percentage >= 100;
                    $isCritical = $b->usage_percentage >= 90 && $b->usage_percentage < 100;
                    $isWarning = $b->usage_percentage >= 75 && $b->usage_percentage < 90;
                @endphp
                <div class="bg-surface-container-lowest border rounded-xl p-space-lg shadow-sm flex flex-col justify-between transition-all relative overflow-hidden {{ $isExhausted ? 'border-2 border-rose-400 bg-rose-50/20' : ($isCritical ? 'border-2 border-rose-300' : ($isWarning ? 'border-amber-300' : 'border-outline-variant/40 hover:border-emerald-300')) }}">
                    
                    @if($isExhausted)
                        <div class="absolute top-0 right-0 bg-rose-600 text-white font-caption text-[10px] uppercase font-bold tracking-widest px-3 py-0.5 rounded-bl">
                            Pagu Habis
                        </div>
                    @elseif($isCritical)
                        <div class="absolute top-0 right-0 bg-rose-500 text-white font-caption text-[10px] uppercase font-bold tracking-widest px-3 py-0.5 rounded-bl">
                            Kritis (&ge;90%)
                        </div>
                    @endif

                    <div>
                        {{-- Card Header --}}
                        <div class="flex items-start justify-between gap-space-sm">
                            <div class="flex items-center gap-space-sm">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center font-headline-sm shrink-0 {{ $isExhausted ? 'bg-rose-100 text-rose-700' : ($isWarning ? 'bg-amber-100 text-amber-800' : 'bg-surface-container-high text-primary') }}">
                                    <span class="material-symbols-outlined text-2xl">
                                        {{ str_contains(strtolower($b->name . ' ' . $b->department), 'it') ? 'dns' : (str_contains(strtolower($b->name . ' ' . $b->department), 'market') ? 'campaign' : 'groups') }}
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-headline-sm text-body-md text-on-surface font-semibold truncate" title="{{ $b->name }}">
                                        {{ $b->name }}
                                    </h3>
                                    <p class="font-caption text-caption text-outline truncate">{{ $b->department }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Alert Threshold Badge --}}
                        <div class="mt-3">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $isExhausted ? 'bg-rose-100 text-rose-800 border border-rose-300' : ($isCritical ? 'bg-rose-50 text-rose-800 border border-rose-200' : ($isWarning ? 'bg-amber-50 text-amber-800 border border-amber-300' : 'bg-emerald-50 text-emerald-800 border border-emerald-300')) }}">
                                <span class="material-symbols-outlined text-sm">
                                    {{ $isExhausted ? 'block' : ($isCritical ? 'error' : ($isWarning ? 'warning' : 'check_circle')) }}
                                </span>
                                <span>{{ $b->alert_label }} ({{ $b->usage_percentage }}%)</span>
                            </span>
                        </div>

                        {{-- Periode & Kategori Context --}}
                        <div class="flex items-center justify-between mt-space-md py-1.5 px-2.5 rounded-lg bg-surface-container-low font-caption text-caption text-on-surface-variant">
                            <span>Kategori: <strong class="text-on-surface">{{ $b->category ?: 'Operasional' }}</strong></span>
                            <span>Periode: <strong class="text-on-surface">{{ $b->period_start ? $b->period_start->format('d M') : '' }} - {{ $b->period_end ? $b->period_end->format('d M Y') : '' }}</strong></span>
                        </div>

                        {{-- Figures Grid (Pagu vs Realisasi) --}}
                        <div class="grid grid-cols-2 gap-space-sm mt-space-md">
                            <div class="p-2.5 rounded-lg bg-surface-container/60">
                                <span class="font-caption text-caption text-outline uppercase tracking-wide">Pagu Alokasi</span>
                                <p class="font-label-data text-body-md text-on-surface font-bold mt-0.5 truncate">
                                    {{ $b->formatted_allocated_amount }}
                                </p>
                            </div>
                            <div class="p-2.5 rounded-lg {{ $isExhausted || $isCritical ? 'bg-rose-50 border border-rose-200' : ($isWarning ? 'bg-amber-50 border border-amber-200' : 'bg-emerald-50 border border-emerald-200') }}">
                                <span class="font-caption text-caption uppercase tracking-wide {{ $isExhausted || $isCritical ? 'text-rose-800' : ($isWarning ? 'text-amber-800' : 'text-emerald-800') }}">
                                    Realisasi
                                </span>
                                <p class="font-label-data text-body-md font-bold mt-0.5 truncate {{ $isExhausted || $isCritical ? 'text-rose-700' : ($isWarning ? 'text-amber-700' : 'text-emerald-700') }}">
                                    {{ $b->formatted_used_amount }}
                                </p>
                            </div>
                        </div>

                        {{-- Sisa Anggaran Row --}}
                        <div class="flex items-center justify-between mt-2 px-1">
                            <span class="font-body-sm text-body-sm text-on-surface-variant">Sisa Anggaran:</span>
                            <span class="font-label-data-sm text-body-sm font-bold {{ $isExhausted || $isCritical ? 'text-rose-700' : ($isWarning ? 'text-amber-800' : 'text-emerald-800') }}">
                                {{ $b->formatted_remaining_amount }}
                            </span>
                        </div>

                        {{-- Progress Meter dengan Ambang Batas 75% & 90% --}}
                        <div class="mt-space-md space-y-1.5">
                            <div class="relative w-full h-3 rounded-full overflow-hidden {{ $isExhausted ? 'bg-rose-100' : ($isWarning ? 'bg-amber-100' : 'bg-emerald-100') }}">
                                <div class="h-full rounded-full transition-all duration-500 {{ $isExhausted ? 'bg-rose-600 animate-pulse' : ($isCritical ? 'bg-rose-500' : ($isWarning ? 'bg-amber-500' : 'bg-emerald-600')) }}" style="width: {{ min(100, $b->usage_percentage) }}%"></div>
                            </div>
                            <div class="flex justify-between font-caption text-[11px] text-outline px-0.5">
                                <span>0%</span>
                                <span class="text-on-surface-variant font-medium">50% Plafon</span>
                                <span class="{{ $isWarning ? 'text-amber-700 font-bold' : '' }}">▲ 75% Warning</span>
                                <span class="font-bold">{{ $b->usage_percentage }}%</span>
                            </div>
                        </div>
                    </div>

                    {{-- Card Footer Action --}}
                    <div class="mt-space-lg pt-space-md border-t border-outline-variant/30 flex items-center gap-2">
                        <a href="{{ route('budgets.show', $b) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg bg-surface-container-high hover:bg-surface-variant text-primary font-body-sm text-body-sm font-semibold transition-colors">
                            <span>Lihat Detail Penggunaan</span>
                            <span class="material-symbols-outlined text-base">arrow_forward</span>
                        </a>
                        @can('update', $b)
                            <a href="{{ route('budgets.edit', $b) }}" class="p-2 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface transition-colors" title="Edit Alokasi">
                                <span class="material-symbols-outlined text-base">edit</span>
                            </a>
                        @endcan
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 text-center bg-surface-container-lowest rounded-xl border border-outline-variant/30">
                    <span class="material-symbols-outlined text-4xl text-outline mb-2">pie_chart</span>
                    <h3 class="font-headline-sm text-body-md font-bold text-on-surface">Belum Ada Pos Anggaran</h3>
                    <p class="font-body-sm text-body-sm text-outline mt-1">Belum ada alokasi anggaran aktif pada divisi ini.</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- Section 3: Filter & High-Density Table (Live Ledger) --}}
    <section aria-label="Aktivitas Penyerapan Anggaran Terkini" class="bg-surface-container-lowest border border-outline-variant/30 rounded-xl shadow-sm overflow-hidden flex flex-col">
        {{-- Section Header & Filter Toolbar --}}
        <div class="p-space-lg border-b border-outline-variant/30 flex flex-col gap-space-md">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">account_balance_wallet</span>
                    <div>
                        <h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold">Tabel Komprehensif Anggaran (Live Ledger)</h3>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">Monitoring pagu dan pemotongan saldo kuota otomatis terverifikasi</p>
                    </div>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('budgets.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-sm items-end pt-space-xs">
                {{-- Search --}}
                <div class="flex flex-col gap-1 sm:col-span-2">
                    <label for="search" class="font-caption text-caption uppercase font-semibold text-on-surface">Pencarian</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-outline text-base">search</span>
                        <input type="text" id="search" name="q" value="{{ $search }}" placeholder="Nama anggaran, proyek, kategori..." class="w-full pl-8 pr-3 py-2 bg-surface-container-low border border-outline-variant/50 rounded-lg text-body-sm font-body-sm text-on-surface placeholder:text-outline focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                    </div>
                </div>

                {{-- Divisi --}}
                <div class="flex flex-col gap-1">
                    <label for="department" class="font-caption text-caption uppercase font-semibold text-on-surface">Divisi</label>
                    <select id="department" name="department" class="w-full py-2 px-2.5 bg-surface-container-low border border-outline-variant/50 rounded-lg text-body-sm font-body-sm text-on-surface focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                        <option value="">Semua Divisi</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->name }}" {{ $department === $dept->name ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status & Actions --}}
                <div class="flex items-center gap-2">
                    <div class="flex-1 flex flex-col gap-1">
                        <label for="status" class="font-caption text-caption uppercase font-semibold text-on-surface">Status</label>
                        <select id="status" name="status" class="w-full py-2 px-2.5 bg-surface-container-low border border-outline-variant/50 rounded-lg text-body-sm font-body-sm text-on-surface focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                            <option value="">Semua Status</option>
                            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="closed" {{ $status === 'closed' ? 'selected' : '' }}>Ditutup</option>
                            <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>
                    <button type="submit" class="py-2 px-3 rounded-lg bg-primary hover:bg-primary-container text-on-primary font-caption text-caption font-semibold shadow-xs transition-colors mt-auto flex items-center justify-center">
                        <span class="material-symbols-outlined text-base">filter_list</span>
                    </button>
                    <a href="{{ route('budgets.index') }}" class="py-2 px-3 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface font-caption text-caption font-medium transition-colors mt-auto" title="Reset">
                        <span class="material-symbols-outlined text-base">restart_alt</span>
                    </a>
                </div>
            </form>
        </div>

        {{-- Table Desktop --}}
        <div class="overflow-x-auto hidden md:block">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-high text-on-surface-variant font-caption text-caption uppercase tracking-wider font-semibold border-b border-outline-variant/30">
                        <th class="py-3 px-4 min-w-[200px]">Nama Pos Anggaran</th>
                        <th class="py-3 px-4 min-w-[150px]">Divisi &amp; Proyek</th>
                        <th class="py-3 px-3 w-32">Periode</th>
                        <th class="py-3 px-4 text-right w-36">Pagu Alokasi</th>
                        <th class="py-3 px-4 text-right w-36">Realisasi</th>
                        <th class="py-3 px-4 text-right w-36">Sisa Kuota</th>
                        <th class="py-3 px-4 w-36">Serapan (%)</th>
                        <th class="py-3 px-3 w-32">Peringatan</th>
                        <th class="py-3 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-low font-body-sm text-body-sm text-on-surface">
                    @forelse($budgets as $b)
                        <tr class="hover:bg-surface-container-low/60 transition-colors">
                            <td class="py-3.5 px-4">
                                <a href="{{ route('budgets.show', $b) }}" class="font-semibold text-primary hover:underline block truncate max-w-[200px]">
                                    {{ $b->name }}
                                </a>
                                <span class="font-caption text-caption text-outline">
                                    {{ $b->status_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-on-surface block">{{ $b->department }}</span>
                                @if($b->project)
                                    <span class="font-caption text-caption text-outline block">{{ $b->project }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 font-label-data-sm text-on-surface-variant whitespace-nowrap">
                                {{ $b->period_start ? $b->period_start->format('d M') : '' }} - {{ $b->period_end ? $b->period_end->format('d M Y') : '' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-label-data font-semibold whitespace-nowrap">
                                {{ $b->formatted_allocated_amount }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-label-data font-semibold whitespace-nowrap text-amber-700">
                                {{ $b->formatted_used_amount }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-label-data font-semibold whitespace-nowrap text-emerald-700">
                                {{ $b->formatted_remaining_amount }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-surface-container-high h-2 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full {{ $b->usage_percentage >= 90 ? 'bg-rose-600' : ($b->usage_percentage >= 75 ? 'bg-amber-500' : 'bg-emerald-600') }}" style="width: {{ min(100, $b->usage_percentage) }}%"></div>
                                    </div>
                                    <span class="font-label-data-sm text-caption font-bold w-10 text-right">{{ $b->usage_percentage }}%</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-3 whitespace-nowrap">
                                <span class="badge {{ $b->alert_badge_class }}">
                                    {{ $b->alert_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('budgets.show', $b) }}" class="inline-flex items-center gap-1 px-2 py-1 rounded bg-surface-container hover:bg-surface-container-high text-on-surface font-caption text-caption font-semibold transition-colors">
                                        <span>Rincian</span>
                                    </a>
                                    @can('update', $b)
                                        <a href="{{ route('budgets.edit', $b) }}" class="p-1 rounded text-outline hover:text-primary transition-colors" title="Edit">
                                            <span class="material-symbols-outlined text-base">edit</span>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-on-surface-variant font-body-sm">
                                Tidak ada pos anggaran yang ditemukan sesuai kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($budgets->hasPages())
            <div class="p-space-md border-t border-outline-variant/30 bg-surface-container-low/40">
                {{ $budgets->links() }}
            </div>
        @endif
    </section>
</div>
@endsection

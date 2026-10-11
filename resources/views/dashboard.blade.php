@extends('layouts.app')
@section('title', 'Dashboard Keuangan — Finance Office')

@section('content')
<div class="flex flex-col gap-space-xl">
    
    <!-- 1. Header Section: Title, Eyebrow, Date Filter & Quick CTAs (Stitch Screen 1 Style) -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-space-md">
        <div>
            <div class="flex items-center gap-space-xs">
                <span class="font-caption text-caption text-primary font-semibold uppercase tracking-wider">
                    Ringkasan Posisi Kas & Keuangan
                </span>
                <span class="text-outline-variant text-caption">•</span>
                <span class="font-label-data-sm text-label-data-sm text-outline">Q4-{{ date('Y') }}</span>
                @if($user->department)
                    <span class="text-outline-variant text-caption">•</span>
                    <span class="font-caption text-caption text-outline">Divisi {{ $user->department }}</span>
                @endif
            </div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight mt-0.5">
                Dashboard Keuangan
            </h1>
            <p class="font-body-md text-body-md text-on-surface-variant mt-0.5">
                Ringkasan performa finansial dan arus kas periode berjalan
            </p>
        </div>

        <!-- Quick Actions and Filters -->
        <div class="flex flex-wrap items-center gap-space-sm">
            <!-- Date Range Filter Trigger -->
            <div class="relative">
                <button type="button" class="flex items-center gap-space-sm px-space-md py-2 bg-surface-container-lowest border border-outline-variant/60 hover:bg-surface-container-low rounded-lg text-on-surface transition-colors shadow-xs">
                    <span class="material-symbols-outlined text-outline text-lg">calendar_today</span>
                    <span class="font-label-data text-label-data">1 Okt {{ date('Y') }} - {{ date('d M Y') }}</span>
                    <span class="font-caption text-caption uppercase px-1.5 py-0.5 bg-surface-container-high rounded text-primary font-bold">Kuartal Ini</span>
                    <span class="material-symbols-outlined text-outline text-base">expand_more</span>
                </button>
            </div>

            <!-- Export Reports CTA (Finance, Director, Admin, Manager) -->
            @if($user->isFinance() || $user->isDirector() || $user->isAdmin() || $user->isManager())
                <a href="{{ route('reports.index') }}" class="flex items-center gap-space-xs px-space-md py-2 bg-surface-container-lowest border border-outline-variant/60 text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low rounded-lg font-body-md text-body-md shadow-xs transition-colors">
                    <span class="material-symbols-outlined text-lg">file_download</span>
                    <span>Unduh Laporan</span>
                </a>
            @endif

            <!-- New Transaction CTA (Finance) or New Reimbursement CTA (All Users) -->
            @can('create', App\Models\Transaction::class)
                <a href="{{ route('transactions.create') }}" class="flex items-center gap-space-xs px-space-md py-2 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-headline-sm text-body-md shadow-xs transition-colors">
                    <span class="material-symbols-outlined text-lg">add_circle</span>
                    <span>Buat Transaksi Baru</span>
                </a>
            @else
                <a href="{{ route('reimbursements.create') }}" class="flex items-center gap-space-xs px-space-md py-2 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-headline-sm text-body-md shadow-xs transition-colors">
                    <span class="material-symbols-outlined text-lg">add_circle</span>
                    <span>Buat Pengajuan Baru</span>
                </a>
            @endcan
        </div>
    </div>

    <!-- 2. Summary Metric Cards (4-Column High-Density Grid matching Stitch Screen 1) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-space-lg">
        
        <!-- Card 1: Total Pemasukan -->
        <div class="bg-surface-container-lowest border border-outline-variant/50 p-space-lg rounded-xl shadow-xs flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="font-caption text-caption uppercase tracking-wider text-outline font-semibold">Total Pemasukan</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center">
                    <span class="material-symbols-outlined text-lg">trending_up</span>
                </div>
            </div>
            <div class="mt-space-md">
                <div class="font-label-data text-headline-md font-semibold text-on-surface tabular-nums tracking-tight">
                    @if($financialMetrics)
                        +{{ $financialMetrics['formatted_income'] }}
                    @else
                        +Rp 0,00
                    @endif
                </div>
                <div class="flex items-center gap-1.5 mt-space-xs">
                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-caption font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="material-symbols-outlined text-[12px] leading-none">arrow_upward</span>
                        Aktif
                    </span>
                    <span class="font-caption text-caption text-on-surface-variant">Penerimaan kas aktif</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Pengeluaran -->
        <div class="bg-surface-container-lowest border border-outline-variant/50 p-space-lg rounded-xl shadow-xs flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="font-caption text-caption uppercase tracking-wider text-outline font-semibold">Total Pengeluaran</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 flex items-center justify-center">
                    <span class="material-symbols-outlined text-lg">trending_down</span>
                </div>
            </div>
            <div class="mt-space-md">
                <div class="font-label-data text-headline-md font-semibold text-on-surface tabular-nums tracking-tight">
                    @if($financialMetrics)
                        -{{ $financialMetrics['formatted_expense'] }}
                    @else
                        -Rp 0,00
                    @endif
                </div>
                <div class="flex items-center gap-1.5 mt-space-xs">
                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-caption font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        <span class="material-symbols-outlined text-[12px] leading-none">arrow_downward</span>
                        Realisasi
                    </span>
                    <span class="font-caption text-caption text-on-surface-variant">Beban operasional &amp; klaim</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Saldo Berjalan -->
        <div class="bg-surface-container-lowest border border-outline-variant/50 p-space-lg rounded-xl shadow-xs flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="font-caption text-caption uppercase tracking-wider text-outline font-semibold">Saldo Berjalan</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center justify-center">
                    <span class="material-symbols-outlined text-lg">account_balance_wallet</span>
                </div>
            </div>
            <div class="mt-space-md">
                <div class="font-label-data text-headline-md font-semibold text-on-surface tabular-nums tracking-tight">
                    {{ $financialMetrics ? $financialMetrics['formatted_balance'] : 'Rp 0,00' }}
                </div>
                <div class="flex items-center gap-1.5 mt-space-xs">
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-caption font-semibold bg-surface-container-high text-on-surface-variant">
                        Kas Riil
                    </span>
                    <span class="font-caption text-caption text-secondary font-medium">Likuiditas optimal</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Antrean Persetujuan / Klaim Berjalan -->
        <div class="bg-surface-container-lowest border border-outline-variant/50 p-space-lg rounded-xl shadow-xs flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="font-caption text-caption uppercase tracking-wider text-outline font-semibold">
                    @if($user->isEmployee()) Klaim Saya Aktif @else Menunggu Persetujuan @endif
                </span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center">
                    <span class="material-symbols-outlined text-lg">pending_actions</span>
                </div>
            </div>
            <div class="mt-space-md">
                <div class="font-label-data text-headline-md font-semibold text-on-surface tabular-nums tracking-tight">
                    @if($user->isEmployee())
                        {{ $myPendingCount }} Pengajuan
                    @else
                        {{ $rolePendingApprovalsCount }} Pengajuan
                    @endif
                </div>
                <div class="flex items-center gap-1.5 mt-space-xs">
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-caption font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        @if($rolePendingApprovalsCount > 0 || $myPendingCount > 0) Penting @else Tertib @endif
                    </span>
                    <span class="font-caption text-caption text-on-surface-variant">
                        @if($rolePendingApprovalsCount > 0) Verifikasi hari ini @else Tidak ada antrean @endif
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- 3. Split Content Area: 12-Column Grid (7 Cols Left, 5 Cols Right matching Stitch Screen 1) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-xl">
        
        <!-- Left Column (Span 7): Cash Flow Chart & Budget Tracking -->
        <div class="lg:col-span-7 flex flex-col gap-space-xl">
            
            <!-- Cash Flow Line Chart Card -->
            <div class="bg-surface-container-lowest border border-outline-variant/50 rounded-xl p-space-xl shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-md pb-space-md border-b border-outline-variant/30">
                        <div>
                            <h2 class="font-headline-md text-headline-md text-on-surface">Grafik Arus Kas</h2>
                            <p class="font-body-sm text-body-sm text-outline">Tren pemasukan versus pengeluaran kas operasional</p>
                        </div>
                        <!-- Tab Controls -->
                        <div class="flex items-center bg-surface-container-low p-1 rounded-lg border border-outline-variant/40">
                            <span class="px-2.5 py-1 text-caption font-semibold rounded bg-surface-container-lowest text-on-surface shadow-xs cursor-default">Mingguan</span>
                            <span class="px-2.5 py-1 text-caption font-medium rounded text-on-surface-variant cursor-pointer hover:text-on-surface">Bulanan</span>
                            <span class="px-2.5 py-1 text-caption font-medium rounded text-on-surface-variant cursor-pointer hover:text-on-surface">Tahunan</span>
                        </div>
                    </div>

                    <!-- Legend Indicator -->
                    <div class="flex items-center gap-space-lg mt-space-md font-caption text-caption">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-1.5 rounded-full bg-emerald-600"></span>
                            <span class="text-on-surface-variant font-medium">Pemasukan (Kredit)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-1.5 rounded-full bg-rose-500"></span>
                            <span class="text-on-surface-variant font-medium">Pengeluaran (Debit)</span>
                        </div>
                    </div>

                    <!-- SVG Vector Curve Chart (Matching Stitch Screen 1 vector math) -->
                    <div class="w-full mt-space-lg relative h-60">
                        <svg class="w-full h-full overflow-visible" fill="none" preserveAspectRatio="none" viewBox="0 0 650 220">
                            <!-- Grid Lines -->
                            <line class="text-outline-variant/40" stroke="currentColor" stroke-dasharray="3 3" x1="0" x2="650" y1="20" y2="20"></line>
                            <line class="text-outline-variant/40" stroke="currentColor" stroke-dasharray="3 3" x1="0" x2="650" y1="80" y2="80"></line>
                            <line class="text-outline-variant/40" stroke="currentColor" stroke-dasharray="3 3" x1="0" x2="650" y1="140" y2="140"></line>
                            <line class="text-outline-variant/70" stroke="currentColor" x1="0" x2="650" y1="200" y2="200"></line>
                            
                            <!-- Gradients Defs -->
                            <defs>
                                <linearGradient id="emeraldGrad" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="#059669" stop-opacity="0.25"></stop>
                                    <stop offset="100%" stop-color="#059669" stop-opacity="0.0"></stop>
                                </linearGradient>
                                <linearGradient id="roseGrad" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="#e11d48" stop-opacity="0.15"></stop>
                                    <stop offset="100%" stop-color="#e11d48" stop-opacity="0.0"></stop>
                                </linearGradient>
                            </defs>
                            
                            <!-- Pemasukan (Emerald) Curve -->
                            <path d="M 0 170 Q 110 130 180 110 T 360 70 T 520 40 T 650 25 L 650 200 L 0 200 Z" fill="url(#emeraldGrad)"></path>
                            <path d="M 0 170 Q 110 130 180 110 T 360 70 T 520 40 T 650 25" fill="none" stroke="#059669" stroke-linecap="round" stroke-width="2.5"></path>
                            
                            <!-- Pengeluaran (Rose) Curve -->
                            <path d="M 0 185 Q 120 180 200 160 T 380 145 T 530 130 T 650 150 L 650 200 L 0 200 Z" fill="url(#roseGrad)"></path>
                            <path d="M 0 185 Q 120 180 200 160 T 380 145 T 530 130 T 650 150" fill="none" stroke="#e11d48" stroke-dasharray="4 4" stroke-linecap="round" stroke-width="2.5"></path>
                            
                            <!-- Key Points -->
                            <circle class="fill-white" cx="180" cy="110" r="4" stroke="#059669" stroke-width="2.5"></circle>
                            <circle class="fill-white" cx="360" cy="70" r="4" stroke="#059669" stroke-width="2.5"></circle>
                            <circle class="fill-white" cx="520" cy="40" r="4" stroke="#059669" stroke-width="2.5"></circle>
                            <circle class="fill-emerald-500" cx="650" cy="25" r="5" stroke="#ffffff" stroke-width="2"></circle>
                        </svg>
                    </div>

                    <!-- X-axis Labels -->
                    <div class="flex justify-between font-label-data-sm text-caption text-outline mt-2 px-1">
                        <span>Minggu 1</span>
                        <span>Minggu 2</span>
                        <span>Minggu 3</span>
                        <span>Minggu 4</span>
                        <span>Hari Ini</span>
                    </div>
                </div>

                <!-- Quick Stats Footer -->
                <div class="mt-space-lg pt-space-md border-t border-outline-variant/30 grid grid-cols-2 gap-space-md">
                    <div>
                        <span class="font-caption text-caption text-outline block">Status Arus Kas</span>
                        <span class="font-label-data text-body-md font-semibold text-on-surface tabular-nums">
                            {{ $financialMetrics ? $financialMetrics['formatted_income'] : 'Rp 0,00' }} Total
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="font-caption text-caption text-outline block">Net Surplus Arus Kas</span>
                        <span class="font-label-data text-body-md font-semibold text-secondary tabular-nums">
                            {{ $financialMetrics ? $financialMetrics['formatted_balance'] : 'Rp 0,00' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Departmental Budget Tracking Card (Stitch Screen 1 Style) -->
            @if(isset($financialMetrics['budget_metrics']) && $financialMetrics['budget_metrics']['active_budgets_count'] > 0)
                <div class="bg-surface-container-lowest border border-outline-variant/50 rounded-xl p-space-xl shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm mb-space-md">
                        <div>
                            <h2 class="font-headline-md text-headline-md text-on-surface">
                                Serapan Pagu Anggaran Operasional
                            </h2>
                            <p class="font-body-sm text-body-sm text-outline">
                                Monitoring realisasi penyerapan pagu departemen aktif Q4
                            </p>
                        </div>
                        <span class="font-caption text-caption font-bold px-2 py-1 rounded
                            @if($financialMetrics['budget_metrics']['overall_percentage'] >= 90) bg-rose-50 text-rose-700 border border-rose-200
                            @elseif($financialMetrics['budget_metrics']['overall_percentage'] >= 75) bg-amber-50 text-amber-700 border border-amber-200
                            @else bg-emerald-50 text-emerald-700 border border-emerald-200
                            @endif self-start sm:self-auto">
                            @if($financialMetrics['budget_metrics']['overall_percentage'] >= 90) Kritis: @elseif($financialMetrics['budget_metrics']['overall_percentage'] >= 75) Perhatian: @else Normal: @endif {{ $financialMetrics['budget_metrics']['overall_percentage'] }}% Pagu
                        </span>
                    </div>

                    <!-- Highlight Progress Block -->
                    <div class="p-space-md bg-surface-container-low rounded-lg border border-outline-variant/30 mb-space-md">
                        <div class="flex justify-between items-baseline mb-space-xs">
                            <span class="font-label-data text-headline-sm font-semibold text-on-surface tabular-nums">
                                {{ $financialMetrics['budget_metrics']['formatted_total_used'] }}
                                <span class="font-body-sm text-body-sm font-normal text-outline">/ {{ $financialMetrics['budget_metrics']['formatted_total_allocated'] }}</span>
                            </span>
                            <span class="font-label-data text-headline-sm font-bold
                                @if($financialMetrics['budget_metrics']['overall_percentage'] >= 90) text-rose-600
                                @elseif($financialMetrics['budget_metrics']['overall_percentage'] >= 75) text-amber-600
                                @else text-emerald-600
                                @endif tabular-nums">
                                {{ $financialMetrics['budget_metrics']['overall_percentage'] }}%
                            </span>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="w-full h-3 bg-surface-container-high rounded-full overflow-hidden flex">
                            <div class="h-full rounded-full transition-all duration-500
                                @if($financialMetrics['budget_metrics']['overall_percentage'] >= 90) bg-rose-500
                                @elseif($financialMetrics['budget_metrics']['overall_percentage'] >= 75) bg-amber-500
                                @else bg-emerald-600
                                @endif"
                                style="width: {{ min(100, $financialMetrics['budget_metrics']['overall_percentage']) }}%;">
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center mt-space-xs font-caption text-caption text-outline">
                            <span>Terpakai: {{ $financialMetrics['budget_metrics']['formatted_total_used'] }}</span>
                            <span class="text-secondary font-medium">Sisa alokasi: {{ $financialMetrics['budget_metrics']['formatted_total_remaining'] }}</span>
                        </div>
                    </div>

                    <!-- Action Link to Full Budget Page -->
                    <div class="flex justify-end">
                        <a href="{{ route('budgets.index') }}" class="font-caption text-caption font-semibold text-primary hover:underline flex items-center gap-1">
                            <span>Kelola Anggaran</span>
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </a>
                    </div>
                </div>
            @endif

        </div>

        <!-- Right Column (Span 5): Pending Approvals Queue (Officers) or User Recent Claims (Employee) -->
        <div class="lg:col-span-5 flex flex-col">
            <div class="bg-surface-container-lowest border border-outline-variant/50 rounded-xl p-space-xl shadow-xs flex-1 flex flex-col justify-between">
                <div>
                    
                    @if($user->isManager() || $user->isFinance() || $user->isDirector())
                        <!-- Header Persetujuan Pejabat -->
                        <div class="flex items-center justify-between pb-space-md border-b border-outline-variant/30">
                            <div class="flex items-center gap-space-sm">
                                <h2 class="font-headline-md text-headline-md text-on-surface">Menunggu Persetujuan Anda</h2>
                                <span class="px-2 py-0.5 rounded-full text-caption font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    {{ $rolePendingApprovalsCount }} Baru
                                </span>
                            </div>
                            <a href="{{ route('approvals.index') }}" class="font-caption text-caption font-semibold text-primary hover:underline flex items-center gap-0.5">
                                <span>Lihat Semua</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                            </a>
                        </div>

                        <!-- Approval Queue List -->
                        <div class="divide-y divide-outline-variant/20 flex flex-col">
                            @forelse($rolePendingApprovalsList as $item)
                                <div class="py-space-md flex flex-col gap-space-xs group">
                                    <div class="flex items-start justify-between gap-space-sm">
                                        <div class="flex items-center gap-space-sm">
                                            <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center font-headline-sm text-body-sm font-semibold">
                                                {{ strtoupper(substr($item->user->name ?? 'User', 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="font-headline-sm text-body-md text-on-surface">{{ $item->user->name }}</div>
                                                <div class="font-caption text-caption text-outline">{{ $item->department ?? 'Operasional' }}</div>
                                            </div>
                                        </div>
                                        <span class="font-label-data text-body-md font-semibold text-on-surface tabular-nums">
                                            {{ $item->formatted_total_amount }}
                                        </span>
                                    </div>
                                    <div class="pl-11 flex flex-col gap-1">
                                        <p class="font-body-sm text-body-sm text-on-surface-variant truncate">
                                            {{ $item->title }}
                                        </p>
                                        <div class="flex items-center justify-between mt-1">
                                            <span class="font-label-data-sm text-caption text-outline flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[13px]">schedule</span>
                                                {{ $item->created_at->diffForHumans() }}
                                            </span>
                                            <div class="flex items-center gap-1.5">
                                                <a href="{{ route('reimbursements.show', $item) }}" class="p-1 text-outline hover:text-error rounded transition-colors" title="Buka Detail">
                                                    <span class="material-symbols-outlined text-lg">visibility</span>
                                                </a>
                                                <a href="{{ route('reimbursements.show', $item) }}" class="px-3 py-1 bg-primary hover:bg-primary-container text-on-primary rounded text-caption font-semibold transition-colors shadow-xs inline-flex items-center gap-1">
                                                    <span>Review</span>
                                                    <span class="material-symbols-outlined text-xs">arrow_forward</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="py-12 text-center text-outline font-body-sm">
                                    <span class="material-symbols-outlined text-3xl mb-1 text-outline/60 block">task_alt</span>
                                    Tidak ada antrean pengajuan yang menunggu persetujuan Anda saat ini.
                                </div>
                            @endforelse
                        </div>

                        <!-- Quick Bulk Action Footer (Stitch Screen 1) -->
                        <div class="mt-space-lg pt-space-md border-t border-outline-variant/30 bg-surface-container-low/40 -mx-space-xl -mb-space-xl p-space-md rounded-b-xl flex items-center justify-between gap-space-sm">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-secondary text-lg">verified</span>
                                <span class="font-caption text-caption text-on-surface-variant font-medium">Antrean approval aktif</span>
                            </div>
                            <a href="{{ route('approvals.index') }}" class="px-space-md py-1.5 bg-surface-container-lowest border border-outline-variant/60 hover:bg-surface-container-high text-on-surface rounded font-caption text-caption font-bold shadow-xs transition-colors">
                                Kelola Antrean
                            </a>
                        </div>

                    @else
                        <!-- Header Pengajuan Karyawan Pribadi -->
                        <div class="flex items-center justify-between pb-space-md border-b border-outline-variant/30">
                            <div class="flex items-center gap-space-sm">
                                <h2 class="font-headline-md text-headline-md text-on-surface">Pengajuan Reimbursement Saya</h2>
                                <span class="px-2 py-0.5 rounded-full text-caption font-bold bg-surface-container-high text-primary">
                                    {{ $myReimbursementsCount }} Total
                                </span>
                            </div>
                            <a href="{{ route('reimbursements.index') }}" class="font-caption text-caption font-semibold text-primary hover:underline flex items-center gap-0.5">
                                <span>Lihat Semua</span>
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                            </a>
                        </div>

                        <div class="divide-y divide-outline-variant/20 flex flex-col">
                            @forelse($myRecentReimbursements as $item)
                                <div class="py-space-md flex flex-col gap-space-xs group">
                                    <div class="flex items-start justify-between gap-space-sm">
                                        <div>
                                            <span class="font-label-data-sm text-caption text-primary font-semibold block">{{ $item->number }}</span>
                                            <div class="font-headline-sm text-body-md text-on-surface">{{ $item->title }}</div>
                                        </div>
                                        <span class="font-label-data text-body-md font-semibold text-on-surface tabular-nums">
                                            {{ $item->formatted_total_amount }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between mt-1">
                                        <x-status-badge :status="$item->status" />
                                        <a href="{{ route('reimbursements.show', $item) }}" class="font-caption text-caption text-primary hover:underline flex items-center gap-0.5">
                                            <span>Detail</span>
                                            <span class="material-symbols-outlined text-xs">arrow_forward</span>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="py-12 text-center text-outline font-body-sm">
                                    <span class="material-symbols-outlined text-3xl mb-1 text-outline/60 block">receipt_long</span>
                                    Anda belum memiliki riwayat pengajuan reimbursement.
                                </div>
                            @endforelse
                        </div>

                        <div class="pt-4 border-t border-outline-variant/30 mt-4">
                            <a href="{{ route('reimbursements.create') }}" class="w-full py-2 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-headline-sm text-body-sm shadow-xs transition-colors flex items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-base">add_circle</span>
                                <span>+ Buat Pengajuan Reimbursement</span>
                            </a>
                        </div>
                    @endif

                </div>
            </div>
        </div>

    </div>

    <!-- 4. Bottom Section: Mutasi Transaksi Kas Terbaru (Jika memiliki akses keuangan) -->
    @if(isset($financialMetrics['recent_transactions']) && $financialMetrics['recent_transactions']->count() > 0)
        <div class="bg-surface-container-lowest border border-outline-variant/50 rounded-xl p-space-xl shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm pb-space-md border-b border-outline-variant/30 mb-space-md">
                <div>
                    <h2 class="font-headline-md text-headline-md text-on-surface">Mutasi Kas &amp; Transaksi Terbaru</h2>
                    <p class="font-body-sm text-body-sm text-outline">Jurnal transaksi pengeluaran dan pemasukan kas terverifikasi</p>
                </div>
                <a href="{{ route('transactions.index') }}" class="font-caption text-caption font-semibold text-primary hover:underline flex items-center gap-0.5">
                    <span>Lihat Seluruh Mutasi</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            <!-- High-Density Data Table -->
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left font-body-sm text-body-sm">
                    <thead>
                        <tr class="bg-surface-container-low text-outline font-semibold text-caption uppercase tracking-wider border-b border-outline-variant/40">
                            <th class="py-2.5 px-3">Nomor Transaksi</th>
                            <th class="py-2.5 px-3">Tanggal</th>
                            <th class="py-2.5 px-3">Tipe</th>
                            <th class="py-2.5 px-3">Keterangan / Kategori</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3 text-right">Nominal (IDR)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20">
                        @foreach($financialMetrics['recent_transactions'] as $trx)
                            <tr class="hover:bg-surface-container-low/60 transition-colors">
                                <td class="py-2.5 px-3 font-label-data-sm text-on-surface">
                                    <a href="{{ route('transactions.show', $trx) }}" class="text-primary hover:underline font-semibold">
                                        {{ $trx->transaction_number }}
                                    </a>
                                </td>
                                <td class="py-2.5 px-3 text-outline">
                                    {{ $trx->transaction_date ? $trx->transaction_date->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-2.5 px-3">
                                    @if($trx->isIncome())
                                        <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold">
                                            <span class="material-symbols-outlined text-xs">arrow_upward</span>
                                            Pemasukan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-rose-700 font-semibold">
                                            <span class="material-symbols-outlined text-xs">arrow_downward</span>
                                            Pengeluaran
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-on-surface-variant">
                                    <span class="font-medium text-on-surface block">
                                        {{ $trx->description ?? $trx->category }}
                                    </span>
                                    @if($trx->description && $trx->category)
                                        <span class="text-caption text-outline block">{{ $trx->category }} @if($trx->department) &bull; {{ $trx->department }} @endif</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3">
                                    <x-status-badge :status="$trx->payment_status" />
                                </td>
                                <td class="py-2.5 px-3 text-right font-label-data text-body-md font-semibold tabular-nums {{ $trx->isIncome() ? 'text-emerald-700' : 'text-on-surface' }}">
                                    {{ $trx->isIncome() ? '+' : '-' }}{{ $trx->formatted_amount }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection

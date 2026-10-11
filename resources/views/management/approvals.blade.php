@extends('layouts.app')
@section('title', 'Otorisasi Persetujuan Manajemen — Finance Office')
@section('header', 'Persetujuan Biaya')

@section('content')
<div class="flex flex-col gap-space-xl max-w-7xl mx-auto w-full">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="flex items-center flex-wrap gap-2 text-body-sm font-body-sm text-on-surface-variant">
        <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Treasury</a>
        <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
        <span class="hover:text-primary transition-colors">Manajemen</span>
        <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
        <span class="text-on-surface font-semibold">Otorisasi Persetujuan</span>
    </nav>

    <header class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg lg:p-space-xl flex flex-col lg:flex-row lg:items-start justify-between gap-space-lg border border-outline-variant/30">
        <div class="flex flex-col gap-space-xs max-w-3xl">
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Pusat Otorisasi Manajemen</h1>
                <span class="px-2 py-0.5 rounded-lg bg-surface-container text-on-surface-variant font-label-data-sm text-label-data-sm font-semibold">
                    SOP-FIN-04
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-primary-fixed text-on-primary-fixed font-caption text-caption font-bold uppercase tracking-wider">
                    Role: {{ strtoupper(auth()->user()->role) }}
                </span>
            </div>
            <p class="font-body-md text-body-md text-on-surface-variant">
                Panel verifikasi kewenangan persetujuan anggaran dan reimbursement untuk Manager Divisi dan Direktur Perusahaan.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('approvals.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary-container text-on-primary font-headline-sm text-body-sm font-semibold shadow-xs transition-colors">
                <span class="material-symbols-outlined text-lg">pending_actions</span>
                <span>Buka Antrean Approval</span>
            </a>
        </div>
    </header>

    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/30 p-8 shadow-sm flex flex-col items-center justify-center text-center">
        <div class="w-16 h-16 rounded-full bg-primary-container text-on-primary flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-3xl">verified_user</span>
        </div>
        <h2 class="font-headline-md text-headline-md text-on-surface mb-2">Area Otorisasi Pejabat Berwenang</h2>
        <p class="font-body-md text-body-md text-on-surface-variant max-w-lg mb-6 leading-relaxed">
            Anda terverifikasi sebagai <strong>{{ auth()->user()->name }}</strong> dengan peran <strong>{{ strtoupper(auth()->user()->role) }}</strong>. Semua permohonan biaya bawahan langsung atau eskalasi nominal tinggi dapat diproses melalui antrean persetujuan.
        </p>
        <div class="flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('approvals.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-secondary hover:bg-secondary/90 text-on-secondary font-headline-sm text-body-sm font-semibold shadow-xs transition-colors">
                <span class="material-symbols-outlined text-lg">fact_check</span>
                <span>Masuk ke Antrean Persetujuan Biaya &rarr;</span>
            </a>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface font-body-sm text-body-sm font-medium transition-colors">
                <span class="material-symbols-outlined text-lg">grid_view</span>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>
</div>
@endsection

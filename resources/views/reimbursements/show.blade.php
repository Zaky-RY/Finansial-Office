@extends('layouts.app')
@section('title', 'Review Pengajuan: ' . $reimbursement->number . ' — Finance Office')
@section('header', 'Review Pengajuan Reimbursement')

@section('content')
<div class="flex flex-col gap-space-xl max-w-7xl mx-auto w-full">

    {{-- Breadcrumb & Navigation Topbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
        <nav aria-label="Breadcrumb" class="flex items-center flex-wrap gap-2 text-body-sm font-body-sm text-on-surface-variant">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Treasury</a>
            <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
            <span class="hover:text-primary transition-colors">Operasional</span>
            <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
            <a href="{{ route('reimbursements.index') }}" class="hover:text-primary transition-colors">Reimbursement</a>
            <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
            <span class="text-on-surface font-semibold">Review Pengajuan</span>
            <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
            <span class="font-label-data-sm text-primary font-medium">{{ $reimbursement->number }}</span>
        </nav>
        <a href="{{ route('reimbursements.index') }}" class="inline-flex items-center gap-1.5 text-body-sm font-body-sm font-medium text-primary hover:text-primary-container transition-colors group">
            <span class="material-symbols-outlined text-base transition-transform group-hover:-translate-x-1">arrow_back</span>
            <span>Kembali ke Daftar Reimbursement</span>
        </a>
    </div>

    {{-- System Alerts --}}
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

    @if($reimbursement->isRejected() && $reimbursement->rejection_reason)
        <div class="p-4 rounded-xl bg-error-container border border-error/40 text-on-error-container flex items-start gap-3">
            <span class="material-symbols-outlined text-error text-2xl shrink-0 mt-0.5">cancel</span>
            <div class="flex flex-col gap-1">
                <span class="font-headline-sm text-body-md font-bold text-error">Pengajuan Ditolak</span>
                <p class="font-body-sm text-body-sm leading-relaxed">
                    <strong>Alasan Penolakan:</strong> {{ $reimbursement->rejection_reason }}
                </p>
            </div>
        </div>
    @endif

    @if($reimbursement->isReturned())
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 flex items-start gap-3">
            <span class="material-symbols-outlined text-amber-600 text-2xl shrink-0 mt-0.5">assignment_return</span>
            <div class="flex flex-col gap-1">
                <span class="font-headline-sm text-body-md font-bold text-amber-800">Pengajuan Perlu Perbaikan Dokumen (Revisi)</span>
                <p class="font-body-sm text-body-sm leading-relaxed">
                    Pengajuan dikembalikan ke pemohon untuk diperbaiki. Silakan sesuaikan data dan bukti nota sesuai catatan approver, kemudian ajukan ulang.
                </p>
            </div>
        </div>
    @endif

    {{-- Header Section with High-Density Context (Stitch Screen 4) --}}
    <header class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg lg:p-space-xl flex flex-col lg:flex-row lg:items-start justify-between gap-space-lg border border-outline-variant/30">
        <div class="flex flex-col gap-space-xs max-w-3xl">
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Review Pengajuan: {{ $reimbursement->number }}</h1>
                <span class="px-2 py-0.5 rounded-lg bg-surface-container text-on-surface-variant font-label-data-sm text-label-data-sm font-semibold">
                    SOP-FIN-04
                </span>
                
                {{-- Status Badge dengan Dynamic Pulse --}}
                @if(in_array($reimbursement->status, ['submitted', 'pending_manager', 'pending_finance', 'pending_director']))
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        </span>
                        <span class="font-caption text-caption font-semibold tracking-wide uppercase">{{ $reimbursement->status_label }}</span>
                    </div>
                @elseif($reimbursement->isApproved())
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-secondary-container text-on-secondary-container border border-secondary/20">
                        <span class="material-symbols-outlined text-xs">check_circle</span>
                        <span class="font-caption text-caption font-semibold tracking-wide uppercase">{{ $reimbursement->status_label }}</span>
                    </div>
                @elseif($reimbursement->isPaid())
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-primary-fixed text-on-primary-fixed border border-primary/20">
                        <span class="material-symbols-outlined text-xs">payments</span>
                        <span class="font-caption text-caption font-semibold tracking-wide uppercase">{{ $reimbursement->status_label }}</span>
                    </div>
                @elseif($reimbursement->isRejected())
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-error-container text-on-error-container border border-error/20">
                        <span class="material-symbols-outlined text-xs">cancel</span>
                        <span class="font-caption text-caption font-semibold tracking-wide uppercase">{{ $reimbursement->status_label }}</span>
                    </div>
                @else
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container text-on-surface-variant border border-outline-variant/40">
                        <span class="font-caption text-caption font-semibold tracking-wide uppercase">{{ $reimbursement->status_label }}</span>
                    </div>
                @endif
            </div>
            <p class="font-body-md text-body-md text-on-surface-variant">
                Verifikasi kesesuaian bukti pengeluaran, kepatuhan batas anggaran divisi, dan kelayakan pencairan dana treasury.
            </p>
        </div>

        <div class="flex items-center flex-wrap gap-2.5 shrink-0">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container text-on-surface font-body-sm text-body-sm font-medium hover:bg-surface-container-high transition-colors shadow-sm" type="button">
                <span class="material-symbols-outlined text-lg text-on-surface-variant">print</span>
                <span>Cetak PDF</span>
            </button>
            @if($reimbursement->isEditable() && auth()->id() === $reimbursement->user_id)
                <a href="{{ route('reimbursements.edit', $reimbursement) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-primary text-on-primary font-body-sm text-body-sm font-medium hover:bg-primary-container transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-lg">edit</span>
                    <span>Edit Pengajuan</span>
                </a>
            @endif
        </div>
    </header>

    {{-- Main Content Split (2/3 Left & 1/3 Right) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter-desktop items-start">
        
        {{-- Left Column (8 cols): Structured Data, Line Items, Receipt Artifacts --}}
        <div class="lg:col-span-8 flex flex-col gap-gutter-desktop min-w-0">
            
            {{-- Card 1: Data Pemohon & Profil Alokasi Anggaran --}}
            <section class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden border border-outline-variant/30">
                <div class="px-space-lg py-space-md bg-surface-container-low flex items-center justify-between border-b border-outline-variant/30">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">badge</span>
                        <h2 class="font-headline-sm text-headline-sm text-on-surface">Data Pemohon &amp; Informasi Pengajuan</h2>
                    </div>
                    <span class="font-caption text-caption uppercase text-on-surface-variant font-bold tracking-wider">Profil Terverifikasi</span>
                </div>
                
                <div class="p-space-lg lg:p-space-xl flex flex-col gap-space-lg">
                    {{-- Requester Header Row --}}
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-space-md bg-surface-container rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-primary text-on-primary flex items-center justify-center font-headline-sm text-headline-sm shadow-sm shrink-0">
                                {{ strtoupper(substr($reimbursement->user->name, 0, 2)) }}
                            </div>
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2">
                                    <span class="font-headline-sm text-headline-sm text-on-surface">{{ $reimbursement->user->name }}</span>
                                    <span class="px-2 py-0.5 rounded-full bg-secondary-fixed text-on-secondary-fixed font-caption text-caption font-semibold">
                                        {{ ucfirst($reimbursement->user->role) }}
                                    </span>
                                </div>
                                <span class="font-body-sm text-body-sm text-on-surface-variant">{{ $reimbursement->user->email }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col sm:text-right">
                            <span class="font-caption text-caption text-outline uppercase font-semibold">Nomor Induk Karyawan</span>
                            <span class="font-label-data text-label-data font-semibold text-on-surface">
                                EMP-{{ str_pad($reimbursement->user->id, 4, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                    </div>

                    {{-- Detail Matrix (2-col grid) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-space-xl gap-y-space-md">
                        <div class="flex flex-col gap-1">
                            <span class="font-caption text-caption text-outline uppercase font-semibold">Divisi &amp; Cost Center</span>
                            <div class="flex items-center gap-1.5 font-body-md text-body-md text-on-surface">
                                <span class="font-medium">{{ $reimbursement->department ?? $reimbursement->user->department ?? 'Operasional' }}</span>
                                <span class="text-outline">/</span>
                                <span class="font-label-data-sm text-label-data-sm px-1.5 py-0.5 rounded bg-surface-container-high text-on-surface font-semibold">
                                    CC-{{ strtoupper(substr($reimbursement->department ?? 'OPR', 0, 3)) }}-{{ str_pad($reimbursement->id, 3, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-1">
                            <span class="font-caption text-caption text-outline uppercase font-semibold">Email Karyawan</span>
                            <span class="font-body-md text-body-md text-on-surface font-label-data-sm">{{ $reimbursement->user->email }}</span>
                        </div>

                        <div class="flex flex-col gap-1 md:col-span-2 p-space-md rounded-lg bg-surface-container-low border border-outline-variant/30">
                            <div class="flex items-center justify-between">
                                <span class="font-caption text-caption text-outline uppercase font-semibold">Rekening Pencairan Reimbursement</span>
                                <span class="inline-flex items-center gap-1 text-secondary font-caption text-caption font-semibold">
                                    <span class="material-symbols-outlined text-sm">verified_user</span> HR Payroll Synced
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 mt-1">
                                <span class="font-headline-sm text-body-md font-semibold text-on-surface">
                                    {{ $reimbursement->payment_method ?? 'Bank Central Asia (BCA)' }}
                                </span>
                                <span class="font-label-data text-label-data px-2 py-0.5 rounded bg-surface-container-lowest font-medium text-primary border border-outline-variant/40">
                                    {{ $reimbursement->payment_account ?? '—' }}
                                </span>
                                <span class="font-body-sm text-body-sm text-on-surface-variant">a/n {{ $reimbursement->user->name }}</span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-1">
                            <span class="font-caption text-caption text-outline uppercase font-semibold">Kategori Biaya &amp; Proyek</span>
                            <div class="font-body-md text-body-md text-on-surface font-medium flex items-center gap-1.5">
                                <span>{{ $reimbursement->category ?? 'Operasional' }}</span>
                                <span class="text-outline">•</span>
                                <span class="text-on-surface-variant font-normal">{{ $reimbursement->project ?? 'Operasional Harian' }}</span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-1">
                            <span class="font-caption text-caption text-outline uppercase font-semibold">Tanggal Kegiatan</span>
                            <span class="font-body-md text-body-md text-on-surface font-label-data-sm">
                                {{ $reimbursement->transaction_date ? $reimbursement->transaction_date->format('d M Y') : $reimbursement->created_at->format('d M Y') }}
                            </span>
                        </div>

                        <div class="flex flex-col gap-1 md:col-span-2">
                            <span class="font-caption text-caption text-outline uppercase font-semibold">Tujuan / Alasan Pengeluaran</span>
                            <p class="font-body-md text-body-md text-on-surface-variant bg-surface-container-low p-3.5 rounded-lg leading-relaxed border border-outline-variant/30">
                                {{ $reimbursement->purpose ?? $reimbursement->description ?? 'Pengeluaran operasional resmi kantor.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Card 2: Rincian Item Pengeluaran (Structured Data Table) --}}
            <section class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden flex flex-col border border-outline-variant/30">
                <div class="px-space-lg py-space-md bg-surface-container-low flex items-center justify-between border-b border-outline-variant/30">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">receipt_long</span>
                        <h2 class="font-headline-sm text-headline-sm text-on-surface">Rincian Item Pengeluaran</h2>
                    </div>
                    <span class="font-label-data-sm text-label-data-sm px-2 py-0.5 rounded-full bg-surface-container text-on-surface-variant font-medium">
                        {{ $reimbursement->items->count() ?: 1 }} Item Terdaftar
                    </span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-high text-on-surface-variant font-caption text-caption uppercase tracking-wider font-semibold">
                                <th class="py-2.5 px-3 text-center w-12">#</th>
                                <th class="py-2.5 px-3">Tanggal</th>
                                <th class="py-2.5 px-4 min-w-[240px]">Deskripsi Item &amp; Keperluan</th>
                                <th class="py-2.5 px-3">Kategori</th>
                                <th class="py-2.5 px-4 text-right">Nominal (IDR)</th>
                                <th class="py-2.5 px-4">Lampiran &amp; Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-container-low font-body-sm text-body-sm text-on-surface">
                            @forelse($reimbursement->items as $idx => $item)
                                <tr class="hover:bg-surface-container-low/60 transition-colors">
                                    <td class="py-3 px-3 text-center font-label-data-sm text-outline">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                    <td class="py-3 px-3 font-label-data-sm whitespace-nowrap">
                                        {{ $item->expense_date ? $item->expense_date->format('d M Y') : '—' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-on-surface">{{ $item->description }}</div>
                                        @if($item->vendor)
                                            <div class="font-caption text-caption text-outline font-label-data-sm mt-0.5">
                                                Vendor: {{ $item->vendor }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="inline-flex px-2 py-0.5 rounded bg-surface-container text-on-surface-variant font-caption text-caption font-medium">
                                            {{ $item->category }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-label-data font-semibold text-on-surface whitespace-nowrap">
                                        Rp {{ number_format((float) $item->amount, 2, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            @if($item->receipt_number)
                                                <span class="inline-flex items-center gap-1 font-label-data-sm text-caption px-2 py-0.5 rounded bg-surface-container text-primary font-medium">
                                                    <span class="material-symbols-outlined text-xs">receipt</span> Nota: {{ $item->receipt_number }}
                                                </span>
                                            @else
                                                <span class="text-outline text-caption font-label-data-sm">—</span>
                                            @endif
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-secondary-container text-on-secondary-container font-caption text-[11px] font-bold">
                                                <span class="material-symbols-outlined text-xs">check_circle</span> Valid
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="hover:bg-surface-container-low/60 transition-colors">
                                    <td class="py-3 px-3 text-center font-label-data-sm text-outline">01</td>
                                    <td class="py-3 px-3 font-label-data-sm whitespace-nowrap">
                                        {{ $reimbursement->transaction_date ? $reimbursement->transaction_date->format('d M Y') : $reimbursement->created_at->format('d M Y') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-on-surface">{{ $reimbursement->description ?? $reimbursement->purpose ?? 'Biaya Operasional' }}</div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="inline-flex px-2 py-0.5 rounded bg-surface-container text-on-surface-variant font-caption text-caption font-medium">
                                            {{ $reimbursement->category ?? 'Umum' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-label-data font-semibold text-on-surface whitespace-nowrap">
                                        Rp {{ number_format((float) $reimbursement->total_amount, 2, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-secondary-container text-on-secondary-container font-caption text-[11px] font-bold">
                                            <span class="material-symbols-outlined text-xs">check_circle</span> Valid
                                        </span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-surface-container-low font-body-sm text-body-sm border-t border-outline-variant/30">
                                <td class="py-2.5 px-4 text-right font-medium text-on-surface-variant" colspan="4">Subtotal Pengeluaran</td>
                                <td class="py-2.5 px-4 text-right font-label-data font-medium text-on-surface whitespace-nowrap">
                                    Rp {{ number_format((float) $reimbursement->total_amount, 2, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                            <tr class="bg-surface-container-low font-body-sm text-body-sm">
                                <td class="py-1.5 px-4 text-right font-medium text-on-surface-variant" colspan="4">Pajak / Potongan Admin</td>
                                <td class="py-1.5 px-4 text-right font-label-data font-medium text-on-surface whitespace-nowrap">Rp 0,00</td>
                                <td></td>
                            </tr>
                            <tr class="bg-surface-container font-headline-sm text-body-md text-on-surface border-t border-outline-variant/40">
                                <td class="py-3 px-4 text-right font-bold text-primary" colspan="4">Total Disetujui (Net Disbursed)</td>
                                <td class="py-3 px-4 text-right font-label-data font-bold text-primary whitespace-nowrap">
                                    Rp {{ number_format((float) $reimbursement->total_amount, 2, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-caption text-outline font-normal">IDR Indonesian Rupiah</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            {{-- Card 3: Lampiran Bukti Dokumen Asli (Stitch Screen 4) --}}
            <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg lg:p-space-xl flex flex-col gap-space-md border border-outline-variant/30">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">attachment</span>
                        <h2 class="font-headline-sm text-headline-sm text-on-surface">
                            Lampiran Bukti Dokumen Asli ({{ $reimbursement->hasAttachment() ? '1 Berkas' : '0 Berkas' }})
                        </h2>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-secondary-container text-on-secondary-container font-caption text-caption font-semibold">
                        <span class="material-symbols-outlined text-sm">lock</span> Akses Privat Terproteksi SHA-256
                    </span>
                </div>

                @if($reimbursement->hasAttachment())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md mt-2">
                        <div class="rounded-xl bg-surface-container-low p-space-md flex flex-col justify-between gap-space-md group hover:bg-surface-container transition-all border border-outline-variant/30">
                            <div class="flex items-start gap-3">
                                <div class="w-12 h-14 rounded-lg bg-surface-container-lowest flex flex-col items-center justify-center text-primary shadow-sm shrink-0 border border-outline-variant/40">
                                    <span class="material-symbols-outlined text-2xl">
                                        {{ str_contains($reimbursement->attachment_mime_type ?? '', 'pdf') ? 'picture_as_pdf' : 'receipt' }}
                                    </span>
                                    <span class="font-caption text-[9px] font-bold uppercase mt-0.5">
                                        {{ str_contains($reimbursement->attachment_mime_type ?? '', 'pdf') ? 'PDF' : 'DOC' }}
                                    </span>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="font-body-md text-body-md font-semibold text-on-surface truncate" title="{{ $reimbursement->attachment_original_name ?? 'Bukti_Transaksi' }}">
                                        {{ $reimbursement->attachment_original_name ?? 'Bukti_Transaksi' }}
                                    </span>
                                    <span class="font-caption text-caption text-outline mt-0.5">
                                        {{ $reimbursement->attachment_size ? round($reimbursement->attachment_size / 1024, 1) . ' KB' : 'Terverifikasi' }} &bull; Diunggah {{ $reimbursement->created_at->format('d M Y, H:i') }} WIB
                                    </span>
                                    <div class="flex items-center gap-1.5 mt-2">
                                        <span class="inline-flex items-center gap-1 font-caption text-caption font-medium text-secondary">
                                            <span class="material-symbols-outlined text-xs">task_alt</span> Dokumen Sah Terverifikasi
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-1">
                                <a href="{{ route('reimbursements.attachment', $reimbursement) }}" target="_blank" class="flex-1 inline-flex items-center justify-center gap-1.5 py-1.5 px-3 rounded-lg bg-surface-container-lowest text-on-surface font-body-sm text-body-sm font-medium hover:bg-surface hover:text-primary transition-colors shadow-sm border border-outline-variant/30">
                                    <span class="material-symbols-outlined text-base">visibility</span>
                                    <span>Lihat Dokumen</span>
                                </a>
                                <a href="{{ route('reimbursements.attachment', $reimbursement) }}" download class="inline-flex items-center justify-center p-1.5 rounded-lg bg-surface-container-lowest text-on-surface-variant hover:text-primary transition-colors shadow-sm border border-outline-variant/30" title="Unduh File">
                                    <span class="material-symbols-outlined text-base">download</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-8 rounded-xl bg-surface-container-low text-center flex flex-col items-center justify-center gap-2 border border-outline-variant/30">
                        <span class="material-symbols-outlined text-3xl text-outline">description</span>
                        <span class="font-body-sm text-body-sm text-on-surface-variant font-medium">
                            Tidak ada lampiran dokumen bukti transaksi untuk pengajuan ini.
                        </span>
                    </div>
                @endif

                {{-- Policy Helper Callout --}}
                <div class="flex items-center gap-2.5 p-3 rounded-lg bg-surface-container text-on-surface-variant text-body-sm font-body-sm">
                    <span class="material-symbols-outlined text-primary text-lg shrink-0">info</span>
                    <span>Semua bukti transaksi wajib memenuhi ketentuan stempel/faktur resmi pajak atau e-receipt sah sesuai SOP Keuangan Kantor (SOP-FIN-04).</span>
                </div>
            </section>
        </div>

        {{-- Right Column (4 cols): Sticky Approval Decision Panel & Audit Log Timeline --}}
        <aside class="lg:col-span-4 flex flex-col gap-gutter-desktop sticky top-20">
            
            {{-- Total Amount Highlight Box --}}
            <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg lg:p-space-xl flex flex-col gap-space-md border border-outline-variant/30">
                <div class="p-space-md rounded-xl bg-gradient-to-br from-primary/10 via-surface-container-low to-surface-container flex flex-col gap-1 border border-primary/20">
                    <span class="font-caption text-caption text-outline uppercase font-semibold">Total Nominal Pengajuan</span>
                    <div class="flex items-baseline gap-2">
                        <span class="font-headline-lg text-headline-lg font-label-data text-primary font-bold">
                            Rp {{ number_format((float) $reimbursement->total_amount, 2, ',', '.') }}
                        </span>
                        <span class="font-label-data-sm text-caption text-on-surface-variant font-medium">IDR</span>
                    </div>
                    
                    @if(bccomp((string) $reimbursement->total_amount, '5000000.00', 2) > 0)
                        <div class="mt-2 pt-2 border-t border-outline-variant/30 flex items-center gap-1.5 text-amber-800 font-body-sm text-body-sm">
                            <span class="material-symbols-outlined text-base text-amber-600">gavel</span>
                            <span class="text-caption font-semibold">Eskalasi: Melebihi Rp 5.000.000 (Otorisasi Direktur)</span>
                        </div>
                    @else
                        <div class="mt-2 pt-2 border-t border-outline-variant/30 flex items-center gap-1.5 text-secondary font-body-sm text-body-sm">
                            <span class="material-symbols-outlined text-base">verified</span>
                            <span class="text-caption font-medium">Limit Standar: Sesuai Kebijakan Divisi</span>
                        </div>
                    @endif
                </div>

                {{-- Kebijakan Anti-Self-Approval Alert --}}
                @if(auth()->id() === $reimbursement->user_id && $reimbursement->isManagerClaim() && $reimbursement->isPendingManagerApproval())
                    <div class="p-3.5 rounded-lg bg-amber-50 border border-amber-300 text-amber-900 flex items-start gap-2.5">
                        <span class="material-symbols-outlined text-amber-700 text-lg shrink-0 mt-0.5">shield</span>
                        <div class="flex flex-col gap-0.5">
                            <span class="font-body-sm text-body-sm font-bold text-amber-800">Anti-Self-Approval Aktif</span>
                            <p class="font-caption text-caption text-amber-700 leading-normal">
                                Sebagai pejabat pemohon, pengajuan Anda otomatis dialihkan ke Direktur untuk menjaga integritas tata kelola keuangan.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Action untuk Pemohon jika Status Draft atau Perlu Perbaikan --}}
                @if($reimbursement->isEditable() && auth()->id() === $reimbursement->user_id)
                    <div class="flex flex-col gap-2 pt-2 border-t border-outline-variant/30">
                        <form method="POST" action="{{ route('reimbursements.submit', $reimbursement) }}">
                            @csrf
                            <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-primary hover:bg-primary-container text-on-primary font-headline-sm text-body-md font-semibold flex items-center justify-center gap-2 shadow-sm transition-all">
                                <span class="material-symbols-outlined text-lg">send</span>
                                <span>Ajukan Sekarang</span>
                            </button>
                        </form>
                        <a href="{{ route('reimbursements.edit', $reimbursement) }}" class="w-full py-2 px-4 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface font-body-md text-body-md font-medium flex items-center justify-center gap-2 transition-colors text-center">
                            <span class="material-symbols-outlined text-lg">edit</span>
                            <span>Edit Dokumen Pengajuan</span>
                        </a>
                    </div>
                @endif
            </section>

            {{-- Panel Keputusan Approval (Untuk Manager / Finance / Director) --}}
            @can('approve', $reimbursement)
                <section class="bg-surface-container-lowest rounded-xl shadow-md p-space-lg lg:p-space-xl flex flex-col gap-space-lg border-2 border-primary">
                    <div class="flex items-center justify-between">
                        <h2 class="font-headline-sm text-headline-sm text-on-surface">Keputusan Approval</h2>
                        <span class="font-caption text-caption px-2 py-0.5 rounded bg-primary-fixed text-on-primary-fixed font-semibold">
                            Role: {{ strtoupper(auth()->user()->role) }}
                        </span>
                    </div>

                    {{-- Form Setujui --}}
                    <form method="POST" action="{{ route('approvals.approve', $reimbursement) }}" class="flex flex-col gap-2">
                        @csrf
                        <div class="flex flex-col gap-1">
                            <label for="approve_comment" class="font-caption text-caption uppercase font-semibold text-on-surface">
                                Catatan Persetujuan (Opsional)
                            </label>
                            <input id="approve_comment" name="comment" type="text" placeholder="Disetujui sesuai rencana anggaran..." class="w-full p-2.5 rounded-lg bg-surface-container-low text-on-surface font-body-sm text-body-sm placeholder:text-outline border border-outline-variant/40 focus:outline-none focus:ring-2 focus:ring-primary focus:bg-surface-container-lowest transition-all">
                        </div>
                        <button type="submit" class="w-full py-3 px-4 rounded-lg bg-secondary hover:bg-secondary/90 text-on-secondary font-headline-sm text-body-md font-semibold flex items-center justify-center gap-2 shadow-sm transition-all hover:shadow">
                            <span class="material-symbols-outlined text-xl">check_circle</span>
                            <span>Setujui &amp; Lanjutkan</span>
                        </button>
                    </form>

                    <div class="h-px bg-outline-variant/30 my-1"></div>

                    {{-- Form Kembalikan untuk Revisi --}}
                    <form method="POST" action="{{ route('approvals.return', $reimbursement) }}" class="flex flex-col gap-2">
                        @csrf
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center justify-between">
                                <label for="return_comment" class="font-caption text-caption uppercase font-semibold text-amber-800">
                                    Catatan Revisi <span class="text-error">*</span>
                                </label>
                                <span class="font-caption text-caption text-outline">Wajib diisi</span>
                            </div>
                            <input id="return_comment" name="comment" type="text" placeholder="Lampirkan nota asli bertanda tangan..." required class="w-full p-2.5 rounded-lg bg-surface-container-low text-on-surface font-body-sm text-body-sm placeholder:text-outline border border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-surface-container-lowest transition-all">
                        </div>
                        <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-body-md text-body-md font-medium flex items-center justify-center gap-2 transition-colors shadow-sm">
                            <span class="material-symbols-outlined text-lg">undo</span>
                            <span>Kembalikan untuk Revisi</span>
                        </button>
                    </form>

                    <div class="h-px bg-outline-variant/30 my-1"></div>

                    {{-- Form Tolak Pengajuan --}}
                    <form method="POST" action="{{ route('approvals.reject', $reimbursement) }}" class="flex flex-col gap-2">
                        @csrf
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center justify-between">
                                <label for="reject_reason" class="font-caption text-caption uppercase font-semibold text-error">
                                    Alasan Penolakan <span class="text-error">*</span>
                                </label>
                                <span class="font-caption text-caption text-outline">Wajib diisi</span>
                            </div>
                            <input id="reject_reason" name="reason" type="text" placeholder="Tidak memenuhi SOP-FIN-04..." required class="w-full p-2.5 rounded-lg bg-surface-container-low text-on-surface font-body-sm text-body-sm placeholder:text-outline border border-error/40 focus:outline-none focus:ring-2 focus:ring-error focus:bg-surface-container-lowest transition-all">
                        </div>
                        <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-error-container text-on-error-container hover:bg-error hover:text-on-error font-body-md text-body-md font-medium flex items-center justify-center gap-2 transition-colors">
                            <span class="material-symbols-outlined text-lg">cancel</span>
                            <span>Tolak Pengajuan</span>
                        </button>
                    </form>
                </section>
            @endcan

            {{-- Panel Pembayaran (Untuk Finance jika status Approved) --}}
            @can('pay', $reimbursement)
                <section class="bg-surface-container-lowest rounded-xl shadow-md p-space-lg lg:p-space-xl flex flex-col gap-space-md border-2 border-secondary">
                    <div class="flex items-center gap-2 text-secondary">
                        <span class="material-symbols-outlined text-2xl">payments</span>
                        <h2 class="font-headline-sm text-headline-sm text-on-surface">Proses Pencairan &amp; Pembayaran</h2>
                    </div>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">
                        Konfirmasi transfer dana kepada pemohon. Aksi ini otomatis membentuk mutasi pengeluaran kas kantor pada sistem.
                    </p>
                    
                    <form method="POST" action="{{ route('approvals.pay', $reimbursement) }}" class="flex flex-col gap-3 mt-1">
                        @csrf
                        <div class="flex flex-col gap-1">
                            <label class="font-caption text-caption uppercase font-semibold text-on-surface">Metode Pembayaran</label>
                            <input type="text" name="payment_method" value="{{ $reimbursement->payment_method ?? 'Bank Transfer' }}" required class="w-full p-2.5 rounded-lg bg-surface-container-low text-on-surface font-body-sm text-body-sm border border-outline-variant/40 focus:outline-none focus:ring-2 focus:ring-secondary">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-caption text-caption uppercase font-semibold text-on-surface">Rekening / Bukti Referensi</label>
                            <input type="text" name="payment_account" value="{{ $reimbursement->payment_account ?? '' }}" placeholder="Nomor rekening atau ref transfer..." class="w-full p-2.5 rounded-lg bg-surface-container-low text-on-surface font-body-sm text-body-sm border border-outline-variant/40 focus:outline-none focus:ring-2 focus:ring-secondary">
                        </div>
                        <button type="submit" class="w-full py-3 px-4 rounded-lg bg-secondary hover:bg-secondary/90 text-on-secondary font-headline-sm text-body-md font-semibold flex items-center justify-center gap-2 shadow-sm transition-all hover:shadow mt-1">
                            <span class="material-symbols-outlined text-xl">payments</span>
                            <span>Konfirmasi Dana Telah Dibayarkan</span>
                        </button>
                    </form>
                </section>
            @endcan

            {{-- Audit Log / Tracking Timeline Card (Stitch Screen 4 Stepper) --}}
            <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg lg:p-space-xl flex flex-col gap-space-md border border-outline-variant/30">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">timeline</span>
                        <h2 class="font-headline-sm text-headline-sm text-on-surface">Alur Verifikasi &amp; Audit Log</h2>
                    </div>
                    <span class="font-caption text-caption text-outline font-label-data-sm">
                        {{ $reimbursement->approvals->count() + 1 }} Catatan
                    </span>
                </div>

                {{-- Vertical Stepper Timeline --}}
                <div class="relative flex flex-col gap-6 pl-2 mt-2">
                    
                    {{-- Step 1: Submisi (Completed) --}}
                    <div class="relative flex items-start gap-3.5">
                        <div class="absolute top-7 left-3.5 bottom-0 w-0.5 -ml-px bg-secondary"></div>
                        <div class="w-7 h-7 rounded-full bg-secondary text-on-secondary flex items-center justify-center shrink-0 z-10 shadow-sm">
                            <span class="material-symbols-outlined text-base">check</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <div class="flex items-baseline justify-between gap-2">
                                <span class="font-body-sm text-body-sm font-semibold text-on-surface">
                                    Diajukan oleh {{ $reimbursement->user->name }}
                                </span>
                            </div>
                            <span class="font-caption text-caption text-outline font-label-data-sm">
                                {{ $reimbursement->submitted_at ? $reimbursement->submitted_at->format('d M Y, H:i') : ($reimbursement->created_at ? $reimbursement->created_at->format('d M Y, H:i') : '—') }} WIB
                            </span>
                            <p class="font-caption text-caption text-on-surface-variant mt-1">
                                Dokumen dan rincian pengeluaran disubmit melalui portal mandiri.
                            </p>
                        </div>
                    </div>

                    {{-- Step 2: Riwayat Keputusan Approver --}}
                    @forelse($reimbursement->approvals as $approval)
                        <div class="relative flex items-start gap-3.5">
                            <div class="absolute top-7 left-3.5 bottom-0 w-0.5 -ml-px {{ $approval->status === 'approved' ? 'bg-secondary' : ($approval->status === 'rejected' ? 'bg-error' : 'bg-amber-400') }}"></div>
                            <div class="w-7 h-7 rounded-full {{ $approval->status === 'approved' ? 'bg-secondary text-on-secondary' : ($approval->status === 'rejected' ? 'bg-error text-on-error' : 'bg-amber-500 text-white') }} flex items-center justify-center shrink-0 z-10 shadow-sm">
                                <span class="material-symbols-outlined text-base">
                                    {{ $approval->status === 'approved' ? 'check' : ($approval->status === 'rejected' ? 'close' : 'undo') }}
                                </span>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-baseline justify-between gap-2">
                                    <span class="font-body-sm text-body-sm font-semibold text-on-surface">
                                        {{ ucfirst($approval->status) }} oleh {{ $approval->approver->name }}
                                    </span>
                                </div>
                                <span class="font-caption text-caption text-outline font-label-data-sm">
                                    {{ $approval->decided_at ? $approval->decided_at->format('d M Y, H:i') : $approval->created_at->format('d M Y, H:i') }} WIB &bull; {{ strtoupper($approval->approval_level) }}
                                </span>
                                @if($approval->comment)
                                    <div class="mt-2 p-2 rounded bg-surface-container font-caption text-caption text-on-surface-variant leading-relaxed border-l-2 border-primary">
                                        <span class="font-semibold text-on-surface">Catatan:</span> {{ $approval->comment }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        @if(!$reimbursement->isDraft())
                            <div class="relative flex items-start gap-3.5">
                                <div class="absolute top-7 left-3.5 bottom-0 w-0.5 -ml-px bg-surface-container-high"></div>
                                <div class="w-7 h-7 rounded-full bg-amber-500 text-white flex items-center justify-center shrink-0 z-10 shadow-sm ring-4 ring-amber-100">
                                    <span class="material-symbols-outlined text-base">hourglass_top</span>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="font-body-sm text-body-sm font-semibold text-amber-900">
                                        Menunggu Persetujuan
                                    </span>
                                    <span class="font-caption text-caption text-amber-700 font-semibold font-label-data-sm">
                                        Tahap: {{ $reimbursement->status_label }}
                                    </span>
                                    <p class="font-caption text-caption text-on-surface-variant mt-1">
                                        Pengajuan sedang dalam antrean evaluasi approver.
                                    </p>
                                </div>
                            </div>
                        @endif
                    @endforelse

                    {{-- Step 4: Pencairan Dana (Disbursement) --}}
                    @if($reimbursement->isPaid())
                        <div class="relative flex items-start gap-3.5">
                            <div class="w-7 h-7 rounded-full bg-primary text-on-primary flex items-center justify-center shrink-0 z-10 shadow-sm">
                                <span class="material-symbols-outlined text-base">payments</span>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-baseline justify-between gap-2">
                                    <span class="font-body-sm text-body-sm font-semibold text-on-surface">
                                        Pencairan Dana Telah Selesai
                                    </span>
                                </div>
                                <span class="font-caption text-caption text-outline font-label-data-sm">
                                    {{ $reimbursement->payment_date ? $reimbursement->payment_date->format('d M Y, H:i') : '—' }} WIB &bull; Oleh {{ $reimbursement->paidByUser?->name ?? 'Finance Specialist' }}
                                </span>
                                <p class="font-caption text-caption text-on-surface-variant mt-0.5">
                                    Mutasi kas keluar telah terbentuk secara otomatis pada buku kas kantor.
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="relative flex items-start gap-3.5">
                            <div class="w-7 h-7 rounded-full bg-surface-container-high text-outline flex items-center justify-center shrink-0 z-10">
                                <span class="material-symbols-outlined text-base">payments</span>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-baseline justify-between gap-2">
                                    <span class="font-body-sm text-body-sm font-medium text-outline">
                                        Pencairan Dana (Disbursement)
                                    </span>
                                </div>
                                <span class="font-caption text-caption text-outline font-label-data-sm">Menunggu Persetujuan Lengkap</span>
                                <p class="font-caption text-caption text-outline mt-0.5">
                                    Transfer dilakukan oleh Finance setelah seluruh persetujuan diselesaikan.
                                </p>
                            </div>
                        </div>
                    @endif

                </div>
            </section>
        </aside>
    </div>
</div>
@endsection

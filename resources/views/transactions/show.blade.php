@extends('layouts.app')
@section('title', 'Detail Transaksi ' . $transaction->transaction_number . ' — Finance Office')
@section('header', 'Detail Transaksi Kas')

@section('content')
<div class="flex flex-col gap-space-xl max-w-7xl mx-auto w-full">

    {{-- Breadcrumb & Navigation Topbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
        <nav aria-label="Breadcrumb" class="flex items-center flex-wrap gap-2 text-body-sm font-body-sm text-on-surface-variant">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Treasury</a>
            <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
            <a href="{{ route('transactions.index') }}" class="hover:text-primary transition-colors">Transaksi Kas</a>
            <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
            <span class="text-on-surface font-semibold">Rincian Transaksi</span>
            <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
            <span class="font-label-data-sm text-primary font-medium">{{ $transaction->transaction_number }}</span>
        </nav>
        <a href="{{ route('transactions.index') }}" class="inline-flex items-center gap-1.5 text-body-sm font-body-sm font-medium text-primary hover:text-primary-container transition-colors group">
            <span class="material-symbols-outlined text-base transition-transform group-hover:-translate-x-1">arrow_back</span>
            <span>Kembali ke Daftar Transaksi</span>
        </a>
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

    {{-- Header Section (Stitch Style) --}}
    <header class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg lg:p-space-xl flex flex-col lg:flex-row lg:items-start justify-between gap-space-lg border border-outline-variant/30">
        <div class="flex flex-col gap-space-xs max-w-3xl">
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">
                    Transaksi Kas: {{ $transaction->transaction_number }}
                </h1>
                <span class="px-2 py-0.5 rounded-lg bg-surface-container text-on-surface-variant font-label-data-sm text-label-data-sm font-semibold">
                    SOP-FIN-04
                </span>
                
                {{-- Jenis Transaksi Badge --}}
                @if($transaction->isIncome())
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-caption text-caption font-bold">
                        <span class="material-symbols-outlined text-xs">arrow_downward</span> Pemasukan (Kredit)
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-error-container text-on-error-container font-caption text-caption font-bold">
                        <span class="material-symbols-outlined text-xs">arrow_upward</span> Pengeluaran (Debit)
                    </span>
                @endif

                {{-- Status Badge --}}
                <x-status-badge :status="$transaction->payment_status" />
            </div>
            <p class="font-body-md text-body-md text-on-surface-variant">
                Dicatat pada {{ $transaction->created_at->format('d M Y, H:i') }} WIB oleh <strong>{{ $transaction->creator->name ?? 'Sistem Finance' }}</strong>
            </p>
        </div>

        <div class="flex items-center flex-wrap gap-2.5 shrink-0">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container text-on-surface font-body-sm text-body-sm font-medium hover:bg-surface-container-high transition-colors shadow-sm" type="button">
                <span class="material-symbols-outlined text-lg text-on-surface-variant">print</span>
                <span>Cetak Bukti</span>
            </button>
        </div>
    </header>

    {{-- Main Content 2-Column Split --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter-desktop items-start">
        
        {{-- Left Column (8 cols): Structured Data, Description & Attachments --}}
        <div class="lg:col-span-8 flex flex-col gap-gutter-desktop min-w-0">
            
            {{-- Card 1: Data Finansial & Mutasi Kas --}}
            <section class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden border border-outline-variant/30">
                <div class="px-space-lg py-space-md bg-surface-container-low flex items-center justify-between border-b border-outline-variant/30">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">receipt_long</span>
                        <h2 class="font-headline-sm text-headline-sm text-on-surface">Informasi Transaksi Kas</h2>
                    </div>
                    <span class="font-caption text-caption uppercase text-on-surface-variant font-bold tracking-wider">Mutasi Sah</span>
                </div>

                <div class="p-space-lg lg:p-space-xl flex flex-col gap-space-lg">
                    {{-- Nominal Highlight Banner --}}
                    <div class="p-space-md rounded-xl bg-surface-container flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="flex flex-col">
                            <span class="font-caption text-caption text-outline uppercase font-semibold">Nominal Mutasi Kas</span>
                            <div class="font-display text-display font-label-data font-bold {{ $transaction->isCancelled() ? 'text-outline line-through' : ($transaction->isIncome() ? 'text-secondary' : 'text-on-surface') }}">
                                {{ $transaction->isIncome() ? '+' : '-' }}{{ $transaction->formatted_amount }}
                            </div>
                        </div>
                        <div class="flex flex-col sm:text-right">
                            <span class="font-caption text-caption text-outline uppercase font-semibold">Tanggal Buku Mutasi</span>
                            <span class="font-label-data text-headline-sm font-semibold text-on-surface">
                                {{ $transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('d F Y') : '—' }}
                            </span>
                        </div>
                    </div>

                    {{-- Detail Matrix --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-space-xl gap-y-space-md">
                        <div class="flex flex-col gap-1">
                            <span class="font-caption text-caption text-outline uppercase font-semibold">Kategori Transaksi</span>
                            <span class="font-body-md text-body-md text-on-surface font-semibold">{{ $transaction->category }}</span>
                        </div>

                        <div class="flex flex-col gap-1">
                            <span class="font-caption text-caption text-outline uppercase font-semibold">Divisi / Alokasi Biaya</span>
                            <span class="font-body-md text-body-md text-on-surface font-medium">{{ $transaction->department ?: 'Umum / Korporat' }}</span>
                        </div>

                        <div class="flex flex-col gap-1">
                            <span class="font-caption text-caption text-outline uppercase font-semibold">Pihak Rekanan / Vendor</span>
                            <span class="font-body-md text-body-md text-on-surface font-medium">{{ $transaction->vendor ?: '—' }}</span>
                        </div>

                        <div class="flex flex-col gap-1">
                            <span class="font-caption text-caption text-outline uppercase font-semibold">Metode Pembayaran</span>
                            <span class="font-body-md text-body-md text-on-surface font-medium">{{ $transaction->payment_method ?: 'Bank Transfer' }}</span>
                        </div>

                        <div class="flex flex-col gap-1">
                            <span class="font-caption text-caption text-outline uppercase font-semibold">No. Referensi / Invoice Eksternal</span>
                            <span class="font-body-md text-body-md text-on-surface font-label-data-sm">{{ $transaction->source_reference ?: '—' }}</span>
                        </div>

                        <div class="flex flex-col gap-1">
                            <span class="font-caption text-caption text-outline uppercase font-semibold">Petugas Pencatat</span>
                            <span class="font-body-md text-body-md text-on-surface font-medium">{{ $transaction->creator->name ?? 'Sistem' }}</span>
                        </div>

                        {{-- Keterangan / Deskripsi --}}
                        <div class="flex flex-col gap-1 md:col-span-2">
                            <span class="font-caption text-caption text-outline uppercase font-semibold">Keterangan / Keperluan Transaksi</span>
                            <p class="font-body-md text-body-md text-on-surface-variant bg-surface-container-low p-3.5 rounded-lg leading-relaxed border border-outline-variant/30">
                                {{ $transaction->description ?: 'Tidak ada keterangan tambahan.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Card 2: Bukti Dokumen / Nota Transaksi --}}
            <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg lg:p-space-xl flex flex-col gap-space-md border border-outline-variant/30">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">attachment</span>
                        <h2 class="font-headline-sm text-headline-sm text-on-surface">Lampiran Bukti Transaksi Kas</h2>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded bg-secondary-container text-on-secondary-container font-caption text-caption font-semibold">
                        <span class="material-symbols-outlined text-xs">lock</span> Akses Privat SHA-256
                    </span>
                </div>

                @if($transaction->attachment_path)
                    <div class="p-space-md rounded-xl bg-surface-container-low flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border border-outline-variant/30">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg bg-surface-container-lowest flex items-center justify-center text-primary shadow-xs shrink-0 border border-outline-variant/40">
                                <span class="material-symbols-outlined text-2xl">description</span>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="font-body-md text-body-md font-semibold text-on-surface truncate">
                                    {{ basename($transaction->attachment_path) }}
                                </span>
                                <span class="font-caption text-caption text-outline">
                                    Bukti Pembayaran Terverifikasi
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('transactions.attachment', $transaction) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-surface-container-lowest hover:bg-surface text-on-surface font-body-sm text-body-sm font-medium transition-colors shadow-xs border border-outline-variant/40">
                            <span class="material-symbols-outlined text-base">download</span>
                            <span>Unduh Bukti Dokumen</span>
                        </a>
                    </div>
                @else
                    <div class="p-6 rounded-xl bg-surface-container-low text-center flex flex-col items-center justify-center gap-1.5 border border-outline-variant/30">
                        <span class="material-symbols-outlined text-3xl text-outline">file_present</span>
                        <span class="font-body-sm text-body-sm text-on-surface-variant font-medium">
                            Tidak ada berkas bukti lampiran yang diunggah untuk transaksi ini.
                        </span>
                    </div>
                @endif
            </section>

            {{-- Card 3: Tautan Entitas Terkait (Budget & Reimbursement) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
                {{-- Relasi Pos Anggaran --}}
                <div class="p-space-md rounded-xl bg-surface-container-lowest border border-outline-variant/30 shadow-sm flex flex-col justify-between gap-2">
                    <span class="font-caption text-caption text-outline uppercase font-semibold">Pos Anggaran Terkait</span>
                    @if($transaction->budget)
                        <div class="flex items-center justify-between">
                            <span class="font-body-md text-body-md font-semibold text-on-surface">{{ $transaction->budget->name }}</span>
                            <a href="{{ route('budgets.show', $transaction->budget) }}" class="text-primary hover:underline font-caption text-caption font-bold">
                                Lihat Pagu &rarr;
                            </a>
                        </div>
                    @else
                        <span class="font-body-sm text-body-sm text-on-surface-variant">Non-budgeted / Pengeluaran Umum</span>
                    @endif
                </div>

                {{-- Relasi Reimbursement --}}
                <div class="p-space-md rounded-xl bg-surface-container-lowest border border-outline-variant/30 shadow-sm flex flex-col justify-between gap-2">
                    <span class="font-caption text-caption text-outline uppercase font-semibold">Tautan Reimbursement</span>
                    @if($transaction->reimbursement)
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-label-data text-body-md font-bold text-primary">{{ $transaction->reimbursement->reimbursement_number }}</span>
                                <span class="font-caption text-caption text-outline block">{{ $transaction->reimbursement->user->name ?? 'Pemohon' }}</span>
                            </div>
                            <a href="{{ route('reimbursements.show', $transaction->reimbursement) }}" class="text-primary hover:underline font-caption text-caption font-bold">
                                Buka Review &rarr;
                            </a>
                        </div>
                    @else
                        <span class="font-body-sm text-body-sm text-on-surface-variant">Transaksi Kas Langsung (Non-Klaim)</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column (4 cols): Status Context, Riwayat Pembatalan & Form Cancel --}}
        <aside class="lg:col-span-4 flex flex-col gap-gutter-desktop sticky top-20">
            
            {{-- Status Ringkasan Finansial --}}
            <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg lg:p-space-xl flex flex-col gap-space-md border border-outline-variant/30">
                <span class="font-caption text-caption text-outline uppercase font-semibold">Status Buku Kas</span>
                <div class="flex items-center gap-2">
                    <x-status-badge :status="$transaction->payment_status" />
                    <span class="font-caption text-caption font-medium text-on-surface-variant">
                        {{ $transaction->isCancelled() ? 'Dibatalkan Resmi' : 'Tercatat di Jurnal' }}
                    </span>
                </div>

                <div class="p-3 rounded-lg bg-surface-container-low text-body-sm font-body-sm flex flex-col gap-1 border border-outline-variant/20">
                    <span class="text-outline text-caption">Pengaruh ke Arus Kas:</span>
                    <span class="font-semibold {{ $transaction->isCancelled() ? 'text-outline' : ($transaction->isIncome() ? 'text-secondary' : 'text-error') }}">
                        @if($transaction->isCancelled())
                            Netral (Batal, tidak mempengaruhi saldo)
                        @elseif($transaction->isIncome())
                            Menambah Saldo Kas Kantor (+{{ $transaction->formatted_amount }})
                        @else
                            Mengurangi Saldo Kas Kantor (-{{ $transaction->formatted_amount }})
                        @endif
                    </span>
                </div>
            </section>

            {{-- Riwayat Pembatalan (Jika Status Dibatalkan) --}}
            @if($transaction->isCancelled())
                <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg lg:p-space-xl flex flex-col gap-space-md border border-error/40 bg-error-container/20">
                    <div class="flex items-center gap-2 text-error">
                        <span class="material-symbols-outlined text-2xl">warning</span>
                        <h3 class="font-headline-sm text-headline-sm font-bold">Transaksi Dibatalkan</h3>
                    </div>
                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                        Transaksi ini telah dibatalkan resmi oleh <strong>{{ $transaction->canceller->name ?? 'User Berwenang' }}</strong> pada {{ $transaction->cancelled_at ? \Carbon\Carbon::parse($transaction->cancelled_at)->format('d M Y, H:i') : '—' }} WIB.
                    </p>
                    <div class="p-3 rounded-lg bg-surface-container-lowest border border-error/30 text-body-sm font-body-sm">
                        <span class="font-caption text-caption uppercase font-semibold text-error block mb-0.5">Alasan Pembatalan:</span>
                        <p class="text-on-surface leading-normal">{{ $transaction->cancellation_reason }}</p>
                    </div>
                </section>
            @endif

            {{-- Panel Pembatalan Transaksi (@can cancel) --}}
            @can('cancel', $transaction)
                <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg lg:p-space-xl flex flex-col gap-space-md border-2 border-error/60">
                    <div class="flex items-center gap-2 text-error">
                        <span class="material-symbols-outlined text-2xl">cancel</span>
                        <h3 class="font-headline-sm text-headline-sm font-bold">Batalkan Transaksi Kas</h3>
                    </div>
                    <p class="font-caption text-caption text-on-surface-variant leading-relaxed">
                        Sesuai tata kelola integritas finansial, data transaksi <strong>dilarang dihapus permanen (no hard delete)</strong>. Cantumkan alasan resmi jika transaksi ini dibatalkan.
                    </p>

                    <form method="POST" action="{{ route('transactions.cancel', $transaction) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi ini? Saldo kas akan disesuaikan otomatis.');" class="flex flex-col gap-3">
                        @csrf
                        <div class="flex flex-col gap-1">
                            <label for="reason" class="font-caption text-caption uppercase font-semibold text-error">
                                Alasan Pembatalan <span class="text-error">*</span>
                            </label>
                            <textarea id="reason" name="reason" rows="3" required placeholder="Jelaskan alasan pembatalan transaksi secara rinci..." class="w-full p-2.5 rounded-lg bg-surface-container-low border border-error/40 text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-2 focus:ring-error"></textarea>
                            @error('reason')
                                <span class="text-error text-caption">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-error text-on-error hover:bg-error/90 font-headline-sm text-body-sm font-semibold shadow-xs transition-colors flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-base">close</span>
                            <span>Batalkan Transaksi Ini</span>
                        </button>
                    </form>
                </section>
            @endcan
        </aside>
    </div>
</div>
@endsection

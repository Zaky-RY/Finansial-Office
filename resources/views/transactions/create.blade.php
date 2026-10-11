@extends('layouts.app')
@section('title', 'Catat Transaksi Kas — Finance Office')
@section('header', 'Catat Transaksi Kas Baru')

@section('content')
<div class="flex flex-col gap-space-xl max-w-4xl mx-auto w-full">

    {{-- Breadcrumb & Navigation Topbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
        <nav aria-label="Breadcrumb" class="flex items-center flex-wrap gap-2 text-body-sm font-body-sm text-on-surface-variant">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Treasury</a>
            <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
            <a href="{{ route('transactions.index') }}" class="hover:text-primary transition-colors">Transaksi Kas</a>
            <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
            <span class="text-on-surface font-semibold">Formulir Transaksi Baru</span>
        </nav>
        <a href="{{ route('transactions.index') }}" class="inline-flex items-center gap-1.5 text-body-sm font-body-sm font-medium text-primary hover:text-primary-container transition-colors group">
            <span class="material-symbols-outlined text-base transition-transform group-hover:-translate-x-1">arrow_back</span>
            <span>Kembali ke Daftar</span>
        </a>
    </div>

    {{-- Error Banner --}}
    @if($errors->any())
        <div class="p-4 rounded-xl bg-error-container/60 border border-error/30 text-on-error-container flex items-start gap-3">
            <span class="material-symbols-outlined text-error text-xl shrink-0 mt-0.5">error</span>
            <div class="flex flex-col gap-1">
                <span class="font-headline-sm text-body-md font-bold text-error">Terdapat Kesalahan Pengisian:</span>
                <ul class="list-disc list-inside text-body-sm space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Header Section (Stitch Style) --}}
    <header class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg lg:p-space-xl flex flex-col sm:flex-row sm:items-center justify-between gap-space-md border border-outline-variant/30">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2">
                <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Formulir Transaksi Kas</h1>
                <span class="px-2 py-0.5 rounded-lg bg-surface-container text-on-surface-variant font-label-data-sm text-label-data-sm font-semibold">
                    SOP-FIN-04
                </span>
            </div>
            <p class="font-body-md text-body-md text-on-surface-variant">
                Pencatatan mutasi kas masuk atau kas keluar kantor secara terpusat, akurat, dan terverifikasi.
            </p>
        </div>
    </header>

    {{-- Form Card --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <form method="POST" action="{{ route('transactions.store') }}" enctype="multipart/form-data" class="p-space-lg lg:p-space-xl flex flex-col gap-space-xl">
            @csrf

            {{-- Section 1: Jenis Transaksi (Radio Button Cards) --}}
            <div class="flex flex-col gap-2">
                <span class="font-caption text-caption uppercase font-semibold text-on-surface">
                    Jenis Transaksi Kas <span class="text-error">*</span>
                </span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
                    <label class="p-4 rounded-xl border-2 flex items-center gap-3 cursor-pointer transition-all {{ old('type', 'expense') === 'expense' ? 'border-error bg-error-container/20' : 'border-outline-variant/40 hover:bg-surface-container-low' }}">
                        <input type="radio" name="type" value="expense" {{ old('type', 'expense') === 'expense' ? 'checked' : '' }} class="w-4 h-4 text-error focus:ring-error">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-error-container text-error flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-lg">arrow_upward</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="font-headline-sm text-body-md font-bold text-on-surface">Pengeluaran Kas (Expense)</span>
                                <span class="font-caption text-caption text-outline">Mengurangi saldo kas operasional kantor</span>
                            </div>
                        </div>
                    </label>

                    <label class="p-4 rounded-xl border-2 flex items-center gap-3 cursor-pointer transition-all {{ old('type') === 'income' ? 'border-secondary bg-secondary-container/20' : 'border-outline-variant/40 hover:bg-surface-container-low' }}">
                        <input type="radio" name="type" value="income" {{ old('type') === 'income' ? 'checked' : '' }} class="w-4 h-4 text-secondary focus:ring-secondary">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-secondary-container text-secondary flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-lg">arrow_downward</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="font-headline-sm text-body-md font-bold text-on-surface">Pemasukan Kas (Income)</span>
                                <span class="font-caption text-caption text-outline">Menambah saldo kas operasional kantor</span>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Section 2: Data Finansial & Alokasi --}}
            <div class="pt-space-md border-t border-outline-variant/20 flex flex-col gap-space-md">
                <h2 class="font-headline-sm text-headline-sm text-on-surface">Rincian Finansial &amp; Alokasi</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
                    {{-- Tanggal Transaksi --}}
                    <div class="flex flex-col gap-1">
                        <label for="transaction_date" class="font-caption text-caption uppercase font-semibold text-on-surface">
                            Tanggal Transaksi <span class="text-error">*</span>
                        </label>
                        <input type="date" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required class="w-full p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/50 text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>

                    {{-- Nominal --}}
                    <div class="flex flex-col gap-1">
                        <label for="amount" class="font-caption text-caption uppercase font-semibold text-on-surface">
                            Nominal (IDR) <span class="text-error">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 font-label-data-sm text-outline font-semibold">Rp</span>
                            <input type="number" step="0.01" min="0.01" id="amount" name="amount" value="{{ old('amount') }}" placeholder="0.00" required class="w-full pl-10 pr-3 py-2.5 rounded-lg bg-surface-container-low border border-outline-variant/50 text-on-surface font-label-data font-bold text-body-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <span class="font-caption text-caption text-outline">Nominal positif tanpa tanda minus.</span>
                    </div>

                    {{-- Kategori --}}
                    <div class="flex flex-col gap-1">
                        <label for="category" class="font-caption text-caption uppercase font-semibold text-on-surface">
                            Kategori Transaksi <span class="text-error">*</span>
                        </label>
                        <input type="text" list="category_list" id="category" name="category" value="{{ old('category') }}" placeholder="Pilih atau ketik kategori..." required class="w-full p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/50 text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        <datalist id="category_list">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->name }}">{{ $cat->name }} ({{ $cat->type }})</option>
                            @endforeach
                            <option value="Operasional Kantor">Operasional Kantor</option>
                            <option value="Perjalanan Dinas">Perjalanan Dinas</option>
                            <option value="Perlengkapan & Logistik">Perlengkapan & Logistik</option>
                            <option value="Konsumsi & Rapat">Konsumsi & Rapat</option>
                            <option value="Penerimaan Jasa">Penerimaan Jasa</option>
                            <option value="Modal & Investasi">Modal & Investasi</option>
                        </datalist>
                    </div>

                    {{-- Divisi --}}
                    <div class="flex flex-col gap-1">
                        <label for="department" class="font-caption text-caption uppercase font-semibold text-on-surface">
                            Divisi Terkait
                        </label>
                        <select id="department" name="department" class="w-full p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/50 text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">-- Tidak Terikat Divisi Khusus (Umum) --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->name }}" {{ old('department') === $dept->name ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pos Anggaran (Budget) --}}
                    <div class="flex flex-col gap-1 sm:col-span-2">
                        <label for="budget_id" class="font-caption text-caption uppercase font-semibold text-on-surface">
                            Alokasikan ke Pos Anggaran (Budget)
                        </label>
                        <select id="budget_id" name="budget_id" class="w-full p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/50 text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">-- Tanpa Anggaran Khusus / Non-Budgeted --</option>
                            @foreach($budgets as $b)
                                <option value="{{ $b->id }}" {{ old('budget_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }} (Sisa: {{ $b->formatted_remaining_amount }})
                                </option>
                            @endforeach
                        </select>
                        <span class="font-caption text-caption text-outline">Jika dipilih, transaksi pengeluaran otomatis menyerap pagu anggaran ini secara atomik.</span>
                    </div>
                </div>
            </div>

            {{-- Section 3: Rekanan & Pembayaran --}}
            <div class="pt-space-md border-t border-outline-variant/20 flex flex-col gap-space-md">
                <h2 class="font-headline-sm text-headline-sm text-on-surface">Pihak Rekanan &amp; Pembayaran</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
                    {{-- Rekanan / Vendor --}}
                    <div class="flex flex-col gap-1">
                        <label for="vendor" class="font-caption text-caption uppercase font-semibold text-on-surface">
                            Pihak Rekanan / Vendor / Penerima
                        </label>
                        <input type="text" id="vendor" name="vendor" value="{{ old('vendor') }}" placeholder="Nama vendor, instansi, atau pembayar..." class="w-full p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/50 text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>

                    {{-- Metode Pembayaran --}}
                    <div class="flex flex-col gap-1">
                        <label for="payment_method" class="font-caption text-caption uppercase font-semibold text-on-surface">
                            Metode Pembayaran <span class="text-error">*</span>
                        </label>
                        <select id="payment_method" name="payment_method" required class="w-full p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/50 text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="Bank Transfer" {{ old('payment_method') === 'Bank Transfer' ? 'selected' : '' }}>Transfer Bank</option>
                            <option value="Cash" {{ old('payment_method') === 'Cash' ? 'selected' : '' }}>Tunai / Kas Kecil (Petty Cash)</option>
                            <option value="Giro / Cek" {{ old('payment_method') === 'Giro / Cek' ? 'selected' : '' }}>Giro / Cek Perusahaan</option>
                            <option value="Corporate Card" {{ old('payment_method') === 'Corporate Card' ? 'selected' : '' }}>Kartu Kredit Korporat</option>
                        </select>
                    </div>

                    {{-- Status Pembayaran --}}
                    <div class="flex flex-col gap-1">
                        <label for="payment_status" class="font-caption text-caption uppercase font-semibold text-on-surface">
                            Status Pembayaran <span class="text-error">*</span>
                        </label>
                        <select id="payment_status" name="payment_status" required class="w-full p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/50 text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="paid" {{ old('payment_status', 'paid') === 'paid' ? 'selected' : '' }}>Lunas / Dibayar (Paid)</option>
                            <option value="scheduled" {{ old('payment_status') === 'scheduled' ? 'selected' : '' }}>Terjadwal (Scheduled)</option>
                            <option value="unpaid" {{ old('payment_status') === 'unpaid' ? 'selected' : '' }}>Belum Dibayar (Unpaid)</option>
                        </select>
                    </div>

                    {{-- No. Invoice / Referensi Eksternal --}}
                    <div class="flex flex-col gap-1">
                        <label for="source_reference" class="font-caption text-caption uppercase font-semibold text-on-surface">
                            No. Referensi / No. Invoice Eksternal
                        </label>
                        <input type="text" id="source_reference" name="source_reference" value="{{ old('source_reference') }}" placeholder="Contoh: INV-2026-0012" class="w-full p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/50 text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                </div>
            </div>

            {{-- Section 4: Keterangan & Lampiran Dokumen --}}
            <div class="pt-space-md border-t border-outline-variant/20 flex flex-col gap-space-md">
                <h2 class="font-headline-sm text-headline-sm text-on-surface">Keterangan &amp; Lampiran Bukti</h2>

                {{-- Deskripsi --}}
                <div class="flex flex-col gap-1">
                    <label for="description" class="font-caption text-caption uppercase font-semibold text-on-surface">
                        Keterangan / Deskripsi Transaksi <span class="text-error">*</span>
                    </label>
                    <textarea id="description" name="description" rows="3" required placeholder="Rincian tujuan pengeluaran atau sumber penerimaan kas..." class="w-full p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/50 text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-2 focus:ring-primary">{{ old('description') }}</textarea>
                </div>

                {{-- File Bukti --}}
                <div class="flex flex-col gap-1">
                    <label for="attachment" class="font-caption text-caption uppercase font-semibold text-on-surface">
                        Bukti Dokumen / Nota / Struk Pembayaran (Opsional)
                    </label>
                    <div class="p-4 rounded-xl border-2 border-dashed border-outline-variant/60 bg-surface-container-low flex flex-col items-center justify-center gap-2 text-center">
                        <span class="material-symbols-outlined text-3xl text-outline">upload_file</span>
                        <div class="flex flex-col">
                            <span class="font-body-sm text-body-sm font-semibold text-on-surface">Pilih berkas dari perangkat Anda</span>
                            <span class="font-caption text-caption text-outline">Format: PDF, JPG, JPEG, PNG (Maksimal 5 MB)</span>
                        </div>
                        <input type="file" id="attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="mt-2 text-caption text-outline file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-caption file:font-semibold file:bg-surface-container-high file:text-on-surface hover:file:bg-surface-container">
                    </div>
                </div>
            </div>

            {{-- Submit Action Bar --}}
            <div class="pt-space-md border-t border-outline-variant/30 flex items-center justify-end gap-3">
                <a href="{{ route('transactions.index') }}" class="px-5 py-2.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface font-body-sm text-body-sm font-medium transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-primary hover:bg-primary-container text-on-primary font-headline-sm text-body-sm font-semibold shadow-xs transition-colors flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base">save</span>
                    <span>Simpan Transaksi Kas</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

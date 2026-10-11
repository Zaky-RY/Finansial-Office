@extends('layouts.app')
@section('title', $reimbursement->exists ? 'Edit Pengajuan Reimbursement — Finance Office' : 'Buat Pengajuan Reimbursement — Finance Office')

@section('content')
<div class="px-space-md sm:px-space-xl lg:px-margin-desktop py-space-lg w-full max-w-5xl mx-auto flex flex-col gap-space-lg">
    
    <!-- 1. Breadcrumb & Top Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
        <nav aria-label="Breadcrumb" class="flex items-center gap-1.5 flex-wrap">
            <span class="font-caption text-caption uppercase tracking-wider text-outline">Treasury</span>
            <span class="material-symbols-outlined text-outline text-xs">chevron_right</span>
            <span class="font-caption text-caption uppercase tracking-wider text-outline">Operasional</span>
            <span class="material-symbols-outlined text-outline text-xs">chevron_right</span>
            <a class="font-caption text-caption uppercase tracking-wider text-outline hover:text-primary transition-colors" href="{{ route('reimbursements.index') }}">Reimbursement</a>
            <span class="material-symbols-outlined text-outline text-xs">chevron_right</span>
            <span class="font-caption text-caption uppercase tracking-wider text-primary font-semibold">
                {{ $reimbursement->exists ? 'Edit Pengajuan' : 'Buat Pengajuan' }}
            </span>
        </nav>
        <a class="inline-flex items-center gap-1.5 font-label-data-sm text-label-data-sm text-outline hover:text-primary transition-colors self-start sm:self-auto py-1" href="{{ route('reimbursements.index') }}">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            <span>Kembali ke Daftar Reimbursement</span>
        </a>
    </div>

    <!-- 2. Header Banner Card (Stitch Screen 3 Header) -->
    <div class="p-space-xl rounded-xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/40 dark:border-slate-700 shadow-xs flex flex-col gap-space-sm relative overflow-hidden">
        <div class="absolute -right-16 -top-16 w-56 h-56 rounded-full bg-primary/5 pointer-events-none blur-2xl"></div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm relative">
            <div class="flex items-center gap-space-sm flex-wrap">
                <h1 class="font-headline-lg text-headline-lg text-on-surface dark:text-white tracking-tight">
                    {{ $reimbursement->exists ? 'Edit Formulir Pengajuan Reimbursement' : 'Formulir Pengajuan Reimbursement' }}
                </h1>
                <span class="font-label-data-sm text-label-data-sm px-2 py-0.5 rounded bg-surface-container-high dark:bg-slate-700 text-primary dark:text-primary-fixed font-semibold">
                    SOP-FIN-04
                </span>
            </div>
            <div class="flex items-center gap-1.5 px-space-sm py-1 rounded bg-secondary-container/30 text-on-secondary-container self-start sm:self-auto">
                <span class="material-symbols-outlined text-base text-secondary">verified_user</span>
                <span class="font-caption text-caption font-semibold">Validasi Otomatis HRIS</span>
            </div>
        </div>
        <p class="font-body-md text-body-md text-on-surface-variant dark:text-slate-400 max-w-3xl">
            Lengkapi data pengeluaran dan lampirkan bukti transaksi digital (struk/invoice) asli untuk mempercepat proses verifikasi tim Finance &amp; persetujuan berjenjang.
        </p>
    </div>

    <!-- Form Errors Alert -->
    @if($errors->any())
        <div class="p-space-md rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800 shadow-xs flex flex-col gap-1" role="alert">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-rose-600">error</span>
                <span class="font-headline-sm text-body-md font-bold">Terdapat kesalahan pengisian data:</span>
            </div>
            <ul class="list-disc list-inside text-body-sm pl-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 3. Main Form Container -->
    <form id="reimbursementForm" class="flex flex-col gap-space-lg" method="POST" enctype="multipart/form-data"
          action="{{ $reimbursement->exists ? route('reimbursements.update', $reimbursement) : route('reimbursements.store') }}">
        @csrf
        @if($reimbursement->exists)
            @method('PUT')
        @endif

        <!-- SECTION 1: Informasi Pemohon (Read-Only HRIS) -->
        <section class="rounded-xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/40 dark:border-slate-700 shadow-xs overflow-hidden flex flex-col">
            <div class="px-space-xl py-space-md bg-surface-container-low dark:bg-slate-900/60 border-b border-outline-variant/30 dark:border-slate-700 flex items-center justify-between">
                <div class="flex items-center gap-space-sm">
                    <span class="material-symbols-outlined text-primary text-xl">badge</span>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface dark:text-white">1. Informasi Pemohon</h2>
                </div>
                <span class="font-caption text-caption text-outline dark:text-slate-400 flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">lock</span>
                    Terkunci oleh HRIS Core
                </span>
            </div>
            <div class="p-space-xl grid grid-cols-1 md:grid-cols-3 gap-space-md">
                <!-- Col 1: Nama Pemohon -->
                <div class="p-space-md rounded-lg bg-surface-container-low dark:bg-slate-900/40 border border-outline-variant/20 dark:border-slate-700/60 flex flex-col gap-1">
                    <span class="font-caption text-caption uppercase tracking-wider text-outline dark:text-slate-400 font-semibold">Nama Pemohon</span>
                    <div class="flex items-center justify-between gap-space-xs mt-0.5">
                        <span class="font-headline-sm text-body-lg text-on-surface dark:text-slate-100 font-semibold">{{ auth()->user()->name }}</span>
                        <span class="font-caption text-caption px-2 py-0.5 rounded bg-secondary-container text-on-secondary-container font-semibold">Aktif</span>
                    </div>
                    <span class="font-label-data-sm text-label-data-sm text-on-surface-variant dark:text-slate-400 flex items-center gap-1 mt-1">
                        <span class="material-symbols-outlined text-sm text-secondary">verified</span>
                        {{ auth()->user()->role }}
                    </span>
                </div>
                <!-- Col 2: Divisi / Departemen -->
                <div class="p-space-md rounded-lg bg-surface-container-low dark:bg-slate-900/40 border border-outline-variant/20 dark:border-slate-700/60 flex flex-col gap-1">
                    <span class="font-caption text-caption uppercase tracking-wider text-outline dark:text-slate-400 font-semibold">Divisi / Departemen</span>
                    <input type="hidden" name="department" value="{{ old('department', $reimbursement->department ?? auth()->user()->department ?? 'Technology') }}">
                    <span class="font-headline-sm text-body-lg text-on-surface dark:text-slate-100 font-semibold mt-0.5">
                        {{ old('department', $reimbursement->department ?? auth()->user()->department ?? 'Technology') }}
                    </span>
                    <span class="font-label-data-sm text-label-data-sm text-on-surface-variant dark:text-slate-400 flex items-center gap-1 mt-1">
                        <span class="material-symbols-outlined text-sm text-outline">domain</span>
                        Unit Anggaran
                    </span>
                </div>
                <!-- Col 3: Email Kantor -->
                <div class="p-space-md rounded-lg bg-surface-container-low dark:bg-slate-900/40 border border-outline-variant/20 dark:border-slate-700/60 flex flex-col gap-1">
                    <span class="font-caption text-caption uppercase tracking-wider text-outline dark:text-slate-400 font-semibold">Email &amp; NIK Akun</span>
                    <span class="font-label-data text-label-data text-on-surface dark:text-slate-100 font-medium mt-0.5">EMP-{{ str_pad(auth()->user()->id, 4, '0', STR_PAD_LEFT) }}</span>
                    <span class="font-caption text-caption text-outline dark:text-slate-400 truncate" title="{{ auth()->user()->email }}">
                        {{ auth()->user()->email }}
                    </span>
                </div>
            </div>
        </section>

        <!-- SECTION 2: Detail Kategori & Tujuan -->
        <section class="rounded-xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/40 dark:border-slate-700 shadow-xs overflow-hidden flex flex-col">
            <div class="px-space-xl py-space-md bg-surface-container-low dark:bg-slate-900/60 border-b border-outline-variant/30 dark:border-slate-700 flex items-center justify-between">
                <div class="flex items-center gap-space-sm">
                    <span class="material-symbols-outlined text-primary text-xl">account_tree</span>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface dark:text-white">2. Kategori &amp; Tujuan Pengeluaran</h2>
                </div>
                <span class="font-caption text-caption text-outline dark:text-slate-400">Langkah 2 dari 4</span>
            </div>
            <div class="p-space-xl flex flex-col gap-space-lg">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-space-lg">
                    <!-- Field 1: Kategori Biaya Utama -->
                    <div class="flex flex-col gap-1.5">
                        <label class="font-caption text-caption uppercase tracking-wider text-outline dark:text-slate-400 font-semibold flex items-center gap-1" for="category">
                            Kategori Biaya Utama <span class="text-error">*</span>
                        </label>
                        <div class="relative">
                            <select id="category" name="category" required class="w-full h-10 pl-3 pr-8 rounded-lg bg-surface-container-low dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 font-body-md text-body-md text-on-surface dark:text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all cursor-pointer">
                                <option value="">Pilih Kategori Biaya</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->name }}" {{ old('category', $reimbursement->category) === $cat->name ? 'selected' : '' }}>
                                        {{ $cat->name }} ({{ $cat->code }})
                                    </option>
                                @endforeach
                                @if($categories->isEmpty())
                                    <option value="Operasional Kantor" selected>Operasional Kantor</option>
                                    <option value="Transport & Perjalanan">Transport &amp; Perjalanan</option>
                                    <option value="Konsumsi & Jamuan">Konsumsi &amp; Jamuan</option>
                                    <option value="Software & Server">Software &amp; Server</option>
                                @endif
                            </select>
                            <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-outline pointer-events-none text-base">expand_more</span>
                        </div>
                        <span class="font-caption text-caption text-outline dark:text-slate-400">Beban alokasi pos anggaran</span>
                    </div>

                    <!-- Field 2: Proyek / Kegiatan -->
                    <div class="flex flex-col gap-1.5">
                        <label class="font-caption text-caption uppercase tracking-wider text-outline dark:text-slate-400 font-semibold flex items-center gap-1" for="project">
                            Proyek / Kegiatan
                        </label>
                        <input id="project" name="project" type="text" value="{{ old('project', $reimbursement->project) }}" placeholder="Contoh: Supervisi Server Q4"
                               class="w-full h-10 px-3 rounded-lg bg-surface-container-low dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 font-body-md text-body-md text-on-surface dark:text-slate-200 placeholder:text-outline focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all">
                        <span class="font-caption text-caption text-outline dark:text-slate-400">Opsional / Referensi kegiatan</span>
                    </div>

                    <!-- Field 3: Metode Penggantian -->
                    <div class="flex flex-col gap-1.5">
                        <label class="font-caption text-caption uppercase tracking-wider text-outline dark:text-slate-400 font-semibold flex items-center gap-1" for="payment_method">
                            Metode Penggantian <span class="text-error">*</span>
                        </label>
                        <div class="relative">
                            <select id="payment_method" name="payment_method" required class="w-full h-10 pl-3 pr-8 rounded-lg bg-surface-container-low dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 font-body-md text-body-md text-on-surface dark:text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all cursor-pointer">
                                <option value="Bank Transfer" {{ old('payment_method', $reimbursement->payment_method) === 'Bank Transfer' ? 'selected' : '' }}>Transfer Bank</option>
                                <option value="Petty Cash" {{ old('payment_method', $reimbursement->payment_method) === 'Petty Cash' ? 'selected' : '' }}>Kas Kecil (Petty Cash)</option>
                                <option value="Payroll Reimbursement" {{ old('payment_method', $reimbursement->payment_method) === 'Payroll Reimbursement' ? 'selected' : '' }}>Masuk Payroll Bulanan</option>
                            </select>
                            <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-outline pointer-events-none text-base">expand_more</span>
                        </div>
                        <span class="font-caption text-caption text-secondary font-semibold">Pencairan via Kasir/Finance</span>
                    </div>
                </div>

                <!-- Field 4: Rekening Tujuan -->
                <div class="flex flex-col gap-1.5">
                    <label class="font-caption text-caption uppercase tracking-wider text-outline dark:text-slate-400 font-semibold flex items-center gap-1" for="payment_account">
                        Rekening / Akun Tujuan Pencairan <span class="text-error">*</span>
                    </label>
                    <input id="payment_account" name="payment_account" type="text" value="{{ old('payment_account', $reimbursement->payment_account ?? 'BCA 1234567890 a/n ' . auth()->user()->name) }}" required placeholder="Contoh: BCA 1234-567-890 a/n Budi Santoso"
                           class="w-full h-10 px-3 rounded-lg bg-surface-container-low dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 font-body-md text-body-md text-on-surface dark:text-slate-200 placeholder:text-outline focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all">
                </div>

                <!-- Field 5: Tujuan / Keperluan Pengeluaran -->
                <div class="flex flex-col gap-1.5">
                    <div class="flex items-center justify-between">
                        <label class="font-caption text-caption uppercase tracking-wider text-outline dark:text-slate-400 font-semibold flex items-center gap-1" for="purpose">
                            Tujuan / Keperluan Pengeluaran <span class="text-error">*</span>
                        </label>
                        <span class="font-label-data-sm text-caption text-outline dark:text-slate-400" id="char-counter">Maksimal 255 karakter</span>
                    </div>
                    <textarea id="purpose" name="purpose" rows="3" required placeholder="Tuliskan deskripsi lengkap keperluan dinas atau kegiatan kantor..."
                              class="w-full p-3 rounded-lg bg-surface-container-low dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 font-body-md text-body-md text-on-surface dark:text-slate-200 placeholder:text-outline focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all resize-none">{{ old('purpose', $reimbursement->purpose ?? $reimbursement->description) }}</textarea>
                    <span class="font-caption text-caption text-outline dark:text-slate-400">Berikan deskripsi ringkas dan akuntabel sesuai agenda nota atau surat tugas.</span>
                </div>
            </div>
        </section>

        <!-- SECTION 3: Rincian Pengeluaran Itemized -->
        <section class="rounded-xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/40 dark:border-slate-700 shadow-xs overflow-hidden flex flex-col">
            <div class="px-space-xl py-space-md bg-surface-container-low dark:bg-slate-900/60 border-b border-outline-variant/30 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-space-xs">
                <div class="flex items-center gap-space-sm">
                    <span class="material-symbols-outlined text-primary text-xl">format_list_bulleted</span>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface dark:text-white">3. Rincian Item Pengeluaran</h2>
                    <span class="font-caption text-caption px-2 py-0.5 rounded-full bg-primary-fixed dark:bg-primary-fixed/20 text-on-primary-fixed dark:text-primary-fixed font-semibold" id="items-badge">
                        Multi-Item
                    </span>
                </div>
                <span class="font-caption text-caption text-outline dark:text-slate-400">Semua nominal dalam mata uang IDR (Rupiah)</span>
            </div>
            
            <div class="p-space-xl flex flex-col gap-space-md">
                <!-- Itemized Table Responsive Wrapper -->
                <div class="w-full overflow-x-auto rounded-lg border border-outline-variant/30 dark:border-slate-700">
                    <table class="w-full text-left border-collapse min-w-[760px] font-body-sm text-body-sm" id="itemsTable">
                        <thead>
                            <tr class="bg-surface-container-low dark:bg-slate-900/60 text-outline dark:text-slate-400 uppercase font-caption text-caption border-b border-outline-variant/40 dark:border-slate-700">
                                <th class="py-2.5 px-3 w-36">Tanggal</th>
                                <th class="py-2.5 px-3">Deskripsi Item / Keperluan</th>
                                <th class="py-2.5 px-3 w-36">Sub-Kategori</th>
                                <th class="py-2.5 px-3 w-40 text-right">Nominal (Rp) <span class="text-error">*</span></th>
                                <th class="py-2.5 px-3 w-36">No. Nota / Ref</th>
                                <th class="py-2.5 px-3 w-14 text-center">Hapus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/20 dark:divide-slate-700/60" id="itemsBody">
                            @php
                                $existingItems = old('items', $reimbursement->items->toArray() ?: [
                                    ['expense_date' => now()->toDateString(), 'description' => '', 'category' => 'Transport', 'amount' => '', 'receipt_number' => '']
                                ]);
                            @endphp
                            @foreach($existingItems as $index => $item)
                                <tr class="item-row hover:bg-surface-container-low/40 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="py-2.5 px-3">
                                        <input type="date" name="items[{{ $index }}][expense_date]" value="{{ $item['expense_date'] ?? now()->toDateString() }}" 
                                               class="w-full py-1.5 px-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded font-label-data-sm text-body-sm text-on-surface dark:text-slate-200 focus:outline-none focus:border-primary">
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <input type="text" name="items[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}" placeholder="Tiket, Bensin, Grab, Hotel..." required 
                                               class="w-full py-1.5 px-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded font-body-sm text-body-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:outline-none focus:border-primary">
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <input type="text" name="items[{{ $index }}][category]" value="{{ $item['category'] ?? '' }}" placeholder="Transport" 
                                               class="w-full py-1.5 px-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded font-body-sm text-body-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:outline-none focus:border-primary">
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        <input type="number" step="0.01" min="0.01" name="items[{{ $index }}][amount]" value="{{ $item['amount'] ?? '' }}" class="item-amount w-full py-1.5 px-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded font-label-data text-body-sm font-semibold text-right text-on-surface dark:text-white placeholder:text-outline focus:outline-none focus:border-primary" oninput="calculateSum()" required placeholder="0.00">
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <input type="text" name="items[{{ $index }}][receipt_number]" value="{{ $item['receipt_number'] ?? '' }}" placeholder="KAI-001" 
                                               class="w-full py-1.5 px-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded font-label-data-sm text-body-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:outline-none focus:border-primary">
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <button type="button" class="p-1 rounded hover:bg-error-container text-outline hover:text-error transition-colors" onclick="removeItemRow(this)" title="Hapus baris">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Add Item Action Button -->
                <button type="button" class="w-full py-2.5 px-4 rounded-lg bg-surface-container-low dark:bg-slate-900/60 hover:bg-surface-container dark:hover:bg-slate-700 text-primary dark:text-primary-fixed font-headline-sm text-body-md font-semibold flex items-center justify-center gap-2 border border-dashed border-outline-variant/60 dark:border-slate-700 transition-all group" onclick="addItemRow()">
                    <span class="material-symbols-outlined text-lg group-hover:scale-110 transition-transform">add_circle</span>
                    <span>+ Tambah Baris Item Pengeluaran</span>
                </button>
            </div>
        </section>

        <!-- SECTION 4: Unggah Bukti Transaksi -->
        <section class="rounded-xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/40 dark:border-slate-700 shadow-xs overflow-hidden flex flex-col">
            <div class="px-space-xl py-space-md bg-surface-container-low dark:bg-slate-900/60 border-b border-outline-variant/30 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-space-xs">
                <div class="flex items-center gap-space-sm">
                    <span class="material-symbols-outlined text-primary text-xl">upload_file</span>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface dark:text-white">4. Unggah Bukti &amp; Dokumen Pendukung</h2>
                </div>
                <span class="font-caption text-caption text-outline dark:text-slate-400">Format resmi e-struk &amp; nota pembayaran</span>
            </div>
            
            <div class="p-space-xl flex flex-col gap-space-md">
                <p class="font-body-sm text-body-sm text-on-surface-variant dark:text-slate-400">
                    Pastikan nomor nota/struk, tanggal transaksi, dan total nominal terbaca dengan jelas tanpa terpotong (Maksimal 5 MB).
                </p>

                <!-- Upload Drop Zone -->
                <div class="p-8 rounded-xl bg-surface-container-low/70 dark:bg-slate-900/40 border-2 border-dashed border-outline-variant/60 dark:border-slate-700 flex flex-col items-center justify-center text-center cursor-pointer hover:bg-surface-container dark:hover:bg-slate-700/40 transition-colors group relative">
                    <input class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" id="attachment" name="attachment" type="file" accept=".jpg,.jpeg,.png,.pdf" {{ $reimbursement->hasAttachment() ? '' : 'required' }} onchange="previewSelectedFile(this)">
                    <div class="w-14 h-14 rounded-full bg-primary/10 text-primary dark:bg-primary-fixed/20 dark:text-primary-fixed flex items-center justify-center group-hover:scale-110 transition-transform mb-3">
                        <span class="material-symbols-outlined text-3xl">cloud_upload</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface dark:text-white font-semibold">
                        Pilih Berkas atau Tarik ke Sini
                    </h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant dark:text-slate-400 mt-1 max-w-md">
                        Klik untuk memilih file nota transaksi dari komputer Anda (Format: PDF, JPG, PNG).
                    </p>
                    <div class="flex items-center gap-2 mt-3">
                        <span class="font-caption text-caption px-2 py-0.5 rounded bg-surface-container-high dark:bg-slate-700 text-outline dark:text-slate-300 uppercase font-semibold">PDF</span>
                        <span class="font-caption text-caption px-2 py-0.5 rounded bg-surface-container-high dark:bg-slate-700 text-outline dark:text-slate-300 uppercase font-semibold">JPG</span>
                        <span class="font-caption text-caption px-2 py-0.5 rounded bg-surface-container-high dark:bg-slate-700 text-outline dark:text-slate-300 uppercase font-semibold">PNG</span>
                    </div>
                </div>

                <!-- Preview Selected or Existing File -->
                <div id="file-preview-pill" class="p-space-md rounded-lg bg-surface-container-low dark:bg-slate-900 border border-outline-variant/30 dark:border-slate-700 {{ $reimbursement->hasAttachment() ? 'flex' : 'hidden' }} items-center justify-between gap-space-sm">
                    <div class="flex items-center gap-space-sm min-w-0">
                        <div class="w-9 h-9 rounded bg-primary-fixed/20 text-primary flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-lg">attachment</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="font-headline-sm text-body-sm text-on-surface dark:text-slate-100 font-semibold truncate" id="file-name-label">
                                {{ $reimbursement->attachment_original_name ?? 'Berkas terpilih' }}
                            </span>
                            <span class="font-caption text-caption text-secondary font-medium">Siap diproses untuk verifikasi dokumen</span>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-secondary">check_circle</span>
                </div>
            </div>
        </section>

        <!-- SECTION 5: Ringkasan Pengajuan & Tombol Submit -->
        <section class="rounded-xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/40 dark:border-slate-700 shadow-xs overflow-hidden flex flex-col">
            <div class="p-space-xl flex flex-col md:flex-row md:items-center justify-between gap-space-lg">
                <!-- SOP Policy Notice -->
                <div class="flex items-start gap-space-sm max-w-lg">
                    <div class="p-2 rounded-lg bg-primary-fixed dark:bg-slate-700 text-on-primary-fixed dark:text-primary-fixed shrink-0 mt-0.5">
                        <span class="material-symbols-outlined text-xl">policy</span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="font-headline-sm text-body-sm text-on-surface dark:text-white font-semibold">Tingkat Otorisasi: Finance &amp; Direktur</span>
                        <p class="font-body-sm text-body-sm text-on-surface-variant dark:text-slate-400">
                            Pengajuan di bawah <strong class="text-on-surface dark:text-slate-200 font-semibold">Rp 5.000.000</strong> diselesaikan oleh Finance. Pengajuan di atas Rp 5.000.000 otomatis dialihkan ke tingkat Direktur setelah verifikasi kas.
                        </p>
                    </div>
                </div>

                <!-- Total Calculation Box -->
                <div class="p-space-lg rounded-xl bg-surface-container dark:bg-slate-900 border border-outline-variant/30 dark:border-slate-700 flex flex-col gap-2 min-w-[280px]">
                    <div class="flex items-center justify-between font-body-sm text-body-sm text-on-surface-variant dark:text-slate-400">
                        <span>Akumulasi Item:</span>
                        <span class="font-label-data text-label-data font-semibold text-on-surface dark:text-white" id="subtotal-val">
                            Rp {{ number_format((float) ($reimbursement->total_amount ?: 0), 2, ',', '.') }}
                        </span>
                    </div>
                    <div class="h-px bg-outline-variant/40 dark:border-slate-700 my-0.5"></div>
                    <div class="flex items-center justify-between">
                        <span class="font-caption text-caption uppercase tracking-wider text-outline dark:text-slate-400 font-semibold">Total Pengajuan</span>
                        <div class="text-right">
                            <span class="font-label-data text-headline-lg text-primary dark:text-primary-fixed font-bold tracking-tight" id="grand-total-val">
                                Rp {{ number_format((float) ($reimbursement->total_amount ?: 0), 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submission Actions Toolbar -->
            <div class="px-space-xl py-space-md bg-surface-container-low dark:bg-slate-900/60 border-t border-outline-variant/30 dark:border-slate-700 flex flex-col-reverse sm:flex-row items-center justify-between gap-space-md">
                <a href="{{ $reimbursement->exists ? route('reimbursements.show', $reimbursement) : route('reimbursements.index') }}" 
                   class="px-space-md py-2 rounded-lg text-on-surface-variant dark:text-slate-400 hover:text-error hover:bg-error-container/20 font-headline-sm text-body-sm transition-colors flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base">close</span>
                    <span>Batal</span>
                </a>

                <div class="flex items-center gap-space-sm self-stretch sm:self-auto justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-surface-container dark:bg-slate-700 hover:bg-surface-container-high dark:hover:bg-slate-600 text-on-surface dark:text-slate-200 font-headline-sm text-body-sm font-semibold transition-colors flex items-center gap-1.5 shadow-xs">
                        <span class="material-symbols-outlined text-base text-outline">save</span>
                        <span>Simpan Draft</span>
                    </button>
                    @if($reimbursement->exists)
                        <button type="submit" formaction="{{ route('reimbursements.submit', $reimbursement) }}" 
                                class="px-6 py-2.5 rounded-lg bg-primary hover:bg-primary-container text-on-primary font-headline-sm text-body-sm font-semibold shadow-xs hover:shadow-md flex items-center gap-2 transition-all group">
                            <span>Simpan &amp; Kirim Pengajuan</span>
                            <span class="material-symbols-outlined text-lg group-hover:translate-x-0.5 transition-transform">send</span>
                        </button>
                    @endif
                </div>
            </div>
        </section>

    </form>

</div>

<!-- Client-side calculation & row management scripts -->
<script>
    let rowIndex = {{ count($existingItems) }};

    function addItemRow() {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        tr.className = 'item-row hover:bg-surface-container-low/40 dark:hover:bg-slate-700/30 transition-colors';
        tr.innerHTML = `
            <td class="py-2.5 px-3">
                <input type="date" name="items[${rowIndex}][expense_date]" value="{{ now()->toDateString() }}" 
                       class="w-full py-1.5 px-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded font-label-data-sm text-body-sm text-on-surface dark:text-slate-200 focus:outline-none focus:border-primary">
            </td>
            <td class="py-2.5 px-3">
                <input type="text" name="items[${rowIndex}][description]" placeholder="Tiket, Bensin, Grab, Hotel..." required 
                       class="w-full py-1.5 px-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded font-body-sm text-body-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:outline-none focus:border-primary">
            </td>
            <td class="py-2.5 px-3">
                <input type="text" name="items[${rowIndex}][category]" placeholder="Transport" 
                       class="w-full py-1.5 px-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded font-body-sm text-body-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:outline-none focus:border-primary">
            </td>
            <td class="py-2.5 px-3 text-right">
                <input type="number" step="0.01" min="0.01" name="items[${rowIndex}][amount]" class="item-amount w-full py-1.5 px-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded font-label-data text-body-sm font-semibold text-right text-on-surface dark:text-white placeholder:text-outline focus:outline-none focus:border-primary" oninput="calculateSum()" required placeholder="0.00">
            </td>
            <td class="py-2.5 px-3">
                <input type="text" name="items[${rowIndex}][receipt_number]" placeholder="No. Kwitansi" 
                       class="w-full py-1.5 px-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded font-label-data-sm text-body-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:outline-none focus:border-primary">
            </td>
            <td class="py-2.5 px-3 text-center">
                <button type="button" class="p-1 rounded hover:bg-error-container text-outline hover:text-error transition-colors" onclick="removeItemRow(this)" title="Hapus baris">
                    <span class="material-symbols-outlined text-lg">delete</span>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        rowIndex++;
        calculateSum();
    }

    function removeItemRow(btn) {
        const tbody = document.getElementById('itemsBody');
        if (tbody.querySelectorAll('tr').length > 1) {
            btn.closest('tr').remove();
            calculateSum();
        } else {
            alert('Minimal satu baris rincian item pengeluaran wajib ada.');
        }
    }

    function calculateSum() {
        let total = 0;
        let count = 0;
        document.querySelectorAll('.item-amount').forEach(input => {
            const val = parseFloat(input.value) || 0;
            total += val;
            count++;
        });
        const formatted = 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(total);
        document.getElementById('subtotal-val').innerText = formatted;
        document.getElementById('grand-total-val').innerText = formatted;
        const badge = document.getElementById('items-badge');
        if (badge) badge.innerText = count + ' Item Terdaftar';
    }

    function previewSelectedFile(input) {
        const pill = document.getElementById('file-preview-pill');
        const label = document.getElementById('file-name-label');
        if (input.files && input.files[0]) {
            label.innerText = input.files[0].name + ' (' + (input.files[0].size / 1024 / 1024).toFixed(2) + ' MB)';
            pill.classList.remove('hidden');
            pill.classList.add('flex');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        calculateSum();
    });
</script>
@endsection

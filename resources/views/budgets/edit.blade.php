@extends('layouts.app')
@section('title', 'Perbarui Anggaran ' . $budget->name . ' — Finance Office')
@section('header', 'Perbarui Alokasi Anggaran')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    {{-- Breadcrumb & Title --}}
    <div class="pb-2 border-b border-[#e2e8f0]">
        <a href="{{ route('budgets.show', $budget) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold tracking-wide uppercase text-[#64748b] hover:text-[#3525cd] transition-colors mb-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Rincian Anggaran
        </a>
        <h1 class="text-2xl font-bold tracking-tight text-[#0f172a]">Perbarui Anggaran: {{ $budget->name }}</h1>
        <p class="text-xs text-[#64748b] mt-1">Sesuaikan pagu alokasi dana, periode masa berlaku, atau status operasional anggaran.</p>
    </div>

    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs">
            <div class="font-bold flex items-center gap-1.5 mb-1.5 text-sm">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Terjadi kesalahan input:
            </div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Info Pemakaian Berjalan --}}
    <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-950 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-sm">
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Realisasi Pemakaian Saat Ini</div>
            <div class="text-xl font-bold font-mono text-emerald-900 mt-0.5">{{ $budget->formatted_used_amount }}</div>
        </div>
        <div class="text-xs text-emerald-800 sm:max-w-xs bg-emerald-100/70 p-2.5 rounded-lg border border-emerald-200">
            <strong>Catatan Integritas:</strong> Pagu alokasi baru tidak boleh lebih rendah dari nominal yang sudah terpakai ({{ $budget->formatted_used_amount }}).
        </div>
    </div>

    <div class="bg-white rounded-xl border border-[#e2e8f0] shadow-sm p-6">
        <form method="POST" action="{{ route('budgets.update', $budget) }}" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Nama Pos Anggaran --}}
            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-[#334155] mb-1.5">
                    Nama Pos Anggaran <span class="text-rose-600">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $budget->name) }}" required
                    class="block w-full px-3.5 py-2.5 rounded-lg border border-[#cbd5e1] bg-white text-[#0f172a] shadow-sm placeholder:text-[#94a3b8] focus:border-[#3525cd] focus:ring-2 focus:ring-[#3525cd]/20 transition-colors sm:text-sm">
                @error('name') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Divisi --}}
                <div>
                    <label for="department" class="block text-xs font-bold uppercase tracking-wider text-[#334155] mb-1.5">
                        Divisi Penanggung Jawab <span class="text-rose-600">*</span>
                    </label>
                    <select id="department" name="department" required
                        class="block w-full px-3.5 py-2.5 rounded-lg border border-[#cbd5e1] bg-white text-[#0f172a] shadow-sm focus:border-[#3525cd] focus:ring-2 focus:ring-[#3525cd]/20 transition-colors sm:text-sm">
                        @foreach($departments as $dept)
                            <option value="{{ $dept->name }}" {{ old('department', $budget->department) === $dept->name ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- Kategori Biaya --}}
                <div>
                    <label for="category" class="block text-xs font-bold uppercase tracking-wider text-[#334155] mb-1.5">
                        Kategori Biaya (Opsional)
                    </label>
                    <input type="text" list="category_list" id="category" name="category" value="{{ old('category', $budget->category) }}" placeholder="Kategori biaya..."
                        class="block w-full px-3.5 py-2.5 rounded-lg border border-[#cbd5e1] bg-white text-[#0f172a] shadow-sm placeholder:text-[#94a3b8] focus:border-[#3525cd] focus:ring-2 focus:ring-[#3525cd]/20 transition-colors sm:text-sm">
                    <datalist id="category_list">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                        @endforeach
                    </datalist>
                    @error('category') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Nama Proyek / Kegiatan --}}
                <div>
                    <label for="project" class="block text-xs font-bold uppercase tracking-wider text-[#334155] mb-1.5">
                        Nama Proyek / Kegiatan (Opsional)
                    </label>
                    <input type="text" id="project" name="project" value="{{ old('project', $budget->project) }}" placeholder="Nama proyek..."
                        class="block w-full px-3.5 py-2.5 rounded-lg border border-[#cbd5e1] bg-white text-[#0f172a] shadow-sm placeholder:text-[#94a3b8] focus:border-[#3525cd] focus:ring-2 focus:ring-[#3525cd]/20 transition-colors sm:text-sm">
                    @error('project') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- Status --}}
                <div>
                    <label for="status" class="block text-xs font-bold uppercase tracking-wider text-[#334155] mb-1.5">
                        Status Anggaran <span class="text-rose-600">*</span>
                    </label>
                    <select id="status" name="status" required
                        class="block w-full px-3.5 py-2.5 rounded-lg border border-[#cbd5e1] bg-white text-[#0f172a] shadow-sm focus:border-[#3525cd] focus:ring-2 focus:ring-[#3525cd]/20 transition-colors sm:text-sm">
                        <option value="active" {{ old('status', $budget->status) === 'active' ? 'selected' : '' }}>Aktif (Active)</option>
                        <option value="draft" {{ old('status', $budget->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="closed" {{ old('status', $budget->status) === 'closed' ? 'selected' : '' }}>Ditutup (Closed)</option>
                        <option value="cancelled" {{ old('status', $budget->status) === 'cancelled' ? 'selected' : '' }}>Dibatalkan (Cancelled)</option>
                    </select>
                    @error('status') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Periode Mulai --}}
                <div>
                    <label for="period_start" class="block text-xs font-bold uppercase tracking-wider text-[#334155] mb-1.5">
                        Periode Mulai <span class="text-rose-600">*</span>
                    </label>
                    <input type="date" id="period_start" name="period_start" value="{{ old('period_start', $budget->period_start ? $budget->period_start->format('Y-m-d') : '') }}" required
                        class="block w-full px-3.5 py-2.5 rounded-lg border border-[#cbd5e1] bg-white text-[#0f172a] shadow-sm focus:border-[#3525cd] focus:ring-2 focus:ring-[#3525cd]/20 transition-colors sm:text-sm font-mono">
                    @error('period_start') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- Periode Selesai --}}
                <div>
                    <label for="period_end" class="block text-xs font-bold uppercase tracking-wider text-[#334155] mb-1.5">
                        Periode Selesai <span class="text-rose-600">*</span>
                    </label>
                    <input type="date" id="period_end" name="period_end" value="{{ old('period_end', $budget->period_end ? $budget->period_end->format('Y-m-d') : '') }}" required
                        class="block w-full px-3.5 py-2.5 rounded-lg border border-[#cbd5e1] bg-white text-[#0f172a] shadow-sm focus:border-[#3525cd] focus:ring-2 focus:ring-[#3525cd]/20 transition-colors sm:text-sm font-mono">
                    @error('period_end') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Nominal Alokasi --}}
            <div>
                <label for="allocated_amount" class="block text-xs font-bold uppercase tracking-wider text-[#334155] mb-1.5">
                    Nominal Pagu Alokasi (IDR) <span class="text-rose-600">*</span>
                </label>
                <div class="relative rounded-lg shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                        <span class="text-xs font-bold text-[#64748b]">Rp</span>
                    </div>
                    <input type="number" step="0.01" min="{{ $budget->used_amount > 0 ? $budget->used_amount : '0.01' }}" id="allocated_amount" name="allocated_amount" value="{{ old('allocated_amount', $budget->allocated_amount) }}" required
                        class="block w-full rounded-lg border border-[#cbd5e1] bg-white pl-10 pr-3.5 py-2.5 text-[#0f172a] font-mono text-base font-semibold placeholder:text-[#94a3b8] focus:border-[#3525cd] focus:ring-2 focus:ring-[#3525cd]/20 transition-colors">
                </div>
                <p class="text-xs text-[#64748b] mt-1.5">Minimal Rp {{ number_format((float) $budget->used_amount, 2, ',', '.') }} (harus mencakup total dana yang telah terserap).</p>
                @error('allocated_amount') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
            </div>

            <div class="pt-4 border-t border-[#e2e8f0] flex items-center justify-end gap-3">
                <a href="{{ route('budgets.show', $budget) }}" class="px-4 py-2 text-xs font-semibold text-[#475569] bg-white border border-[#cbd5e1] rounded-lg hover:bg-[#f1f5f9] transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-[#3525cd] rounded-lg hover:bg-[#2a1db3] transition-colors shadow-sm">
                    Simpan Perubahan Anggaran
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

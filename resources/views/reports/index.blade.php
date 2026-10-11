@extends('layouts.app')
@section('title', 'Laporan Keuangan & Ekspor — Finance Office')
@section('header', 'Laporan Keuangan')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
    <div>
        <h1>Laporan Keuangan & Rekapitulasi</h1>
        <div class="muted">Analisis data transaksi arus kas, klaim reimbursement, dan realisasi anggaran divisi secara terpadu.</div>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <a href="{{ route('reports.print', request()->query()) }}" target="_blank" class="btn btn-secondary">
            <span>🖨️</span> Cetak / Simpan PDF
        </a>
        <a href="{{ route('reports.export', request()->query()) }}" class="btn btn-primary">
            <span>📥</span> Unduh Excel (CSV)
        </a>
    </div>
</div>

{{-- Tab Pilihan Jenis Laporan --}}
<div style="display:flex; gap:8px; border-bottom:1px solid var(--line); margin-bottom:20px; overflow-x:auto;">
    <a href="{{ route('reports.index', array_merge(request()->except(['page']), ['report_type' => 'cashflow'])) }}" 
       style="padding:10px 18px; font-weight:700; font-size:14px; border-bottom:3px solid {{ $reportType === 'cashflow' ? 'var(--brand)' : 'transparent' }}; color:{{ $reportType === 'cashflow' ? 'var(--brand)' : 'var(--muted)' }}; text-decoration:none; white-space:nowrap;">
       💳 Arus Kas (Transaksi)
    </a>
    <a href="{{ route('reports.index', array_merge(request()->except(['page']), ['report_type' => 'reimbursement'])) }}" 
       style="padding:10px 18px; font-weight:700; font-size:14px; border-bottom:3px solid {{ $reportType === 'reimbursement' ? 'var(--brand)' : 'transparent' }}; color:{{ $reportType === 'reimbursement' ? 'var(--brand)' : 'var(--muted)' }}; text-decoration:none; white-space:nowrap;">
       📝 Reimbursement
    </a>
    <a href="{{ route('reports.index', array_merge(request()->except(['page']), ['report_type' => 'budget'])) }}" 
       style="padding:10px 18px; font-weight:700; font-size:14px; border-bottom:3px solid {{ $reportType === 'budget' ? 'var(--brand)' : 'transparent' }}; color:{{ $reportType === 'budget' ? 'var(--brand)' : 'var(--muted)' }}; text-decoration:none; white-space:nowrap;">
       🎯 Realisasi Anggaran
    </a>
</div>

{{-- Form Filter Terpadu --}}
<div class="card" style="margin-bottom:24px; padding:20px;">
    <form method="GET" action="{{ route('reports.index') }}" style="display:flex; flex-direction:column; gap:14px;">
        <input type="hidden" name="report_type" value="{{ $reportType }}">

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px;">
            <div>
                <label for="start_date" style="font-size:12px;">Periode Mulai</label>
                <input type="date" id="start_date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" style="padding:8px 10px; font-size:13px;">
            </div>

            <div>
                <label for="end_date" style="font-size:12px;">Periode Selesai</label>
                <input type="date" id="end_date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" style="padding:8px 10px; font-size:13px;">
            </div>

            @if(auth()->user()->isManager() && !auth()->user()->isFinance() && !auth()->user()->isDirector() && !auth()->user()->isAdmin())
                <div>
                    <label style="font-size:12px;">Divisi (Terkunci)</label>
                    <input type="text" value="{{ auth()->user()->department }}" disabled style="padding:8px 10px; font-size:13px; background:#f1f5f3; color:var(--muted);">
                    <input type="hidden" name="department" value="{{ auth()->user()->department }}">
                </div>
            @else
                <div>
                    <label for="department" style="font-size:12px;">Divisi</label>
                    <select id="department" name="department" style="padding:8px 10px; font-size:13px;">
                        <option value="">Semua Divisi</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->name }}" {{ ($filters['department'] ?? '') === $dept->name ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if($reportType === 'cashflow')
                <div>
                    <label for="type" style="font-size:12px;">Jenis Transaksi</label>
                    <select id="type" name="type" style="padding:8px 10px; font-size:13px;">
                        <option value="all" {{ ($filters['type'] ?? '') === 'all' ? 'selected' : '' }}>Semua Jenis</option>
                        <option value="income" {{ ($filters['type'] ?? '') === 'income' ? 'selected' : '' }}>Pemasukan</option>
                        <option value="expense" {{ ($filters['type'] ?? '') === 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                    </select>
                </div>
            @endif

            @if($reportType !== 'budget')
                <div>
                    <label for="category" style="font-size:12px;">Kategori Biaya</label>
                    <select id="category" name="category" style="padding:8px 10px; font-size:13px;">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}" {{ ($filters['category'] ?? '') === $cat->name ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label for="status" style="font-size:12px;">Status</label>
                <select id="status" name="status" style="padding:8px 10px; font-size:13px;">
                    <option value="">Semua Status</option>
                    @if($reportType === 'cashflow')
                        <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>Selesai / Lunas</option>
                        <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Tertunda</option>
                        <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    @elseif($reportType === 'reimbursement')
                        <option value="paid" {{ ($filters['status'] ?? '') === 'paid' ? 'selected' : '' }}>Dibayar</option>
                        <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="pending_manager" {{ ($filters['status'] ?? '') === 'pending_manager' ? 'selected' : '' }}>Menunggu Manager</option>
                        <option value="pending_finance" {{ ($filters['status'] ?? '') === 'pending_finance' ? 'selected' : '' }}>Menunggu Finance</option>
                        <option value="pending_director" {{ ($filters['status'] ?? '') === 'pending_director' ? 'selected' : '' }}>Menunggu Direktur</option>
                        <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        <option value="revision" {{ ($filters['status'] ?? '') === 'revision' ? 'selected' : '' }}>Revisi</option>
                    @else
                        <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="closed" {{ ($filters['status'] ?? '') === 'closed' ? 'selected' : '' }}>Ditutup</option>
                        <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    @endif
                </select>
            </div>

            <div>
                <label for="q" style="font-size:12px;">Pencarian Kata Kunci</label>
                <input type="text" id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="No transaksi, judul, vendor..." style="padding:8px 10px; font-size:13px;">
            </div>
        </div>

        @if($errors->any())
            <div style="background:var(--danger-bg); border-left:4px solid var(--danger); padding:10px 14px; border-radius:6px; color:var(--danger); font-size:13px;">
                @foreach($errors->all() as $error)
                    <div>⚠️ {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div style="display:flex; justify-content:flex-end; gap:8px;">
            <a href="{{ route('reports.index', ['report_type' => $reportType]) }}" class="btn btn-secondary" style="font-size:13px; padding:6px 14px;">
                Reset Filter
            </a>
            <button type="submit" class="btn btn-primary" style="font-size:13px; padding:6px 18px;">
                Terapkan Filter
            </button>
        </div>
    </form>
</div>

{{-- Kartu Metrik Ringkasan Finansial --}}
@if($reportType === 'cashflow')
    <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); margin-bottom:24px;">
        <div class="card" style="border-left:4px solid var(--brand);">
            <div class="muted" style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Total Pemasukan Kas</div>
            <div class="stat" style="color:var(--brand); font-size:24px;">{{ $reportData['formatted_income'] }}</div>
            <div class="muted" style="font-size:11px; margin-top:4px;">Pemasukan aktif terverifikasi</div>
        </div>
        <div class="card" style="border-left:4px solid #b42318;">
            <div class="muted" style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Total Pengeluaran Kas</div>
            <div class="stat" style="color:#b42318; font-size:24px;">{{ $reportData['formatted_expense'] }}</div>
            <div class="muted" style="font-size:11px; margin-top:4px;">Pengeluaran aktif terverifikasi</div>
        </div>
        <div class="card" style="border-left:4px solid #15803d;">
            <div class="muted" style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Saldo Bersih Kas</div>
            <div class="stat" style="color:#15803d; font-size:24px;">{{ $reportData['formatted_balance'] }}</div>
            <div class="muted" style="font-size:11px; margin-top:4px;">Pemasukan aktif &minus; Pengeluaran aktif</div>
        </div>
        <div class="card" style="border-left:4px solid #64746f;">
            <div class="muted" style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Jumlah Transaksi</div>
            <div class="stat" style="font-size:24px;">{{ number_format($reportData['total_count']) }}</div>
            <div class="muted" style="font-size:11px; margin-top:4px;">Sesuai kriteria filter</div>
        </div>
    </div>
@elseif($reportType === 'reimbursement')
    <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); margin-bottom:24px;">
        <div class="card" style="border-left:4px solid #15803d;">
            <div class="muted" style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Total Telah Dibayarkan</div>
            <div class="stat" style="color:#15803d; font-size:24px;">{{ $reportData['formatted_paid_amount'] }}</div>
            <div class="muted" style="font-size:11px; margin-top:4px;">Klaim berstatus dibayar / selesai</div>
        </div>
        <div class="card" style="border-left:4px solid var(--brand);">
            <div class="muted" style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Total Nilai Diajukan</div>
            <div class="stat" style="color:var(--brand); font-size:24px;">{{ $reportData['formatted_total_amount'] }}</div>
            <div class="muted" style="font-size:11px; margin-top:4px;">Semua status pengajuan</div>
        </div>
        <div class="card" style="border-left:4px solid #b54708;">
            <div class="muted" style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Menunggu Proses Approval</div>
            <div class="stat" style="color:#b54708; font-size:24px;">{{ number_format($reportData['pending_count']) }}</div>
            <div class="muted" style="font-size:11px; margin-top:4px;">Dalam antrean verifikasi</div>
        </div>
        <div class="card" style="border-left:4px solid #64746f;">
            <div class="muted" style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Total Pengajuan</div>
            <div class="stat" style="font-size:24px;">{{ number_format($reportData['total_count']) }}</div>
            <div class="muted" style="font-size:11px; margin-top:4px;">Sesuai kriteria filter</div>
        </div>
    </div>
@else
    <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); margin-bottom:24px;">
        <div class="card" style="border-left:4px solid var(--brand);">
            <div class="muted" style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Total Pagu Alokasi</div>
            <div class="stat" style="color:var(--brand); font-size:24px;">{{ $reportData['formatted_allocated'] }}</div>
            <div class="muted" style="font-size:11px; margin-top:4px;">Pagu pagu divisi terpilih</div>
        </div>
        <div class="card" style="border-left:4px solid #b42318;">
            <div class="muted" style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Total Dana Terserap</div>
            <div class="stat" style="color:#b42318; font-size:24px;">{{ $reportData['formatted_used'] }}</div>
            <div class="muted" style="font-size:11px; margin-top:4px;">Realisasi biaya terpakai</div>
        </div>
        <div class="card" style="border-left:4px solid #15803d;">
            <div class="muted" style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Sisa Anggaran</div>
            <div class="stat" style="color:#15803d; font-size:24px;">{{ $reportData['formatted_remaining'] }}</div>
            <div class="muted" style="font-size:11px; margin-top:4px;">Pagu alokasi &minus; terpakai</div>
        </div>
        <div class="card" style="border-left:4px solid #d97706;">
            <div class="muted" style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Rata-rata Serapan</div>
            <div class="stat" style="color:#d97706; font-size:24px;">{{ $reportData['overall_percentage'] }}%</div>
            <div class="muted" style="font-size:11px; margin-top:4px;">Persentase realisasi gabungan</div>
        </div>
    </div>
@endif

{{-- Tabel Data Laporan --}}
<div class="card" style="padding:0; overflow:hidden;">
    <div style="padding:16px 20px; border-bottom:1px solid var(--line); display:flex; justify-content:space-between; align-items:center;">
        <strong style="font-size:15px;">
            @if($reportType === 'cashflow')
                Rincian Arus Kas Masuk & Keluar
            @elseif($reportType === 'reimbursement')
                Rincian Rekapitulasi Reimbursement
            @else
                Rincian Realisasi Pagu Anggaran
            @endif
        </strong>
        <span class="muted" style="font-size:12px;">
            Menampilkan {{ $reportData['items']->count() }} dari {{ $reportData['items']->total() }} data
        </span>
    </div>

    @if($reportData['items']->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">📄</div>
            <div class="empty-state-title">Tidak ada data ditemukan</div>
            <div class="empty-state-text">Tidak ada data yang sesuai dengan kombinasi filter dan rentang tanggal yang dipilih.</div>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table class="table">
                @if($reportType === 'cashflow')
                    <thead>
                        <tr>
                            <th>No. Transaksi</th>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Divisi / Kategori</th>
                            <th>Vendor / Deskripsi</th>
                            <th style="text-align:right;">Nominal (IDR)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData['items'] as $tx)
                            <tr style="{{ $tx->isCancelled() ? 'opacity:0.6; background:#fbfbfb;' : '' }}">
                                <td>
                                    <a href="{{ route('transactions.show', $tx) }}" style="font-weight:700; color:var(--brand); text-decoration:underline;">
                                        {{ $tx->transaction_number }}
                                    </a>
                                </td>
                                <td>{{ $tx->transaction_date->format('d M Y') }}</td>
                                <td>
                                    @if($tx->isIncome())
                                        <span class="badge" style="background:#eef7f1; color:var(--brand);">Pemasukan</span>
                                    @else
                                        <span class="badge" style="background:#fff0ee; color:var(--danger);">Pengeluaran</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight:600;">{{ $tx->department ?? '-' }}</div>
                                    <div class="muted" style="font-size:11px;">{{ $tx->category ?? '-' }}</div>
                                </td>
                                <td>
                                    <div style="font-weight:500;">{{ $tx->vendor ?? '-' }}</div>
                                    <div class="muted" style="font-size:12px; max-width:280px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        {{ $tx->description }}
                                    </div>
                                </td>
                                <td style="text-align:right; font-weight:700; font-variant-numeric:tabular-nums; color:{{ $tx->isIncome() ? 'var(--brand)' : 'var(--danger)' }};">
                                    {{ $tx->isIncome() ? '+' : '-' }} {{ $tx->formatted_amount }}
                                </td>
                                <td>
                                    <span class="badge {{ $tx->status_badge_class }}">
                                        {{ $tx->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                @elseif($reportType === 'reimbursement')
                    <thead>
                        <tr>
                            <th>Nomor Pengajuan</th>
                            <th>Tanggal</th>
                            <th>Pemohon</th>
                            <th>Divisi</th>
                            <th>Kategori / Keperluan</th>
                            <th style="text-align:right;">Nominal (IDR)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData['items'] as $rem)
                            <tr>
                                <td>
                                    <a href="{{ route('reimbursements.show', $rem) }}" style="font-weight:700; color:var(--brand); text-decoration:underline;">
                                        {{ $rem->number }}
                                    </a>
                                </td>
                                <td>{{ $rem->created_at->format('d M Y') }}</td>
                                <td>
                                    <div style="font-weight:600;">{{ $rem->user->name }}</div>
                                    <div class="muted" style="font-size:11px;">{{ $rem->user->email }}</div>
                                </td>
                                <td>{{ $rem->department }}</td>
                                <td>
                                    <div style="font-weight:600;">{{ $rem->category ?? '-' }}</div>
                                    <div class="muted" style="font-size:12px; max-width:260px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        {{ $rem->purpose }}
                                    </div>
                                </td>
                                <td style="text-align:right; font-weight:700; font-variant-numeric:tabular-nums;">
                                    {{ $rem->formatted_total_amount }}
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($rem->status) {
                                            'paid', 'completed' => 'background:#dcfce7; color:#166534;',
                                            'approved' => 'background:#e0f2fe; color:#0369a1;',
                                            'rejected' => 'background:#fee2e2; color:#991b1b;',
                                            'revision' => 'background:#ffedd5; color:#9a3412;',
                                            default => 'background:#fef3c7; color:#92400e;',
                                        };
                                    @endphp
                                    <span class="badge" style="{{ $statusClass }}">
                                        {{ $rem->status_label ?? ucfirst(str_replace('_', ' ', $rem->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                @else
                    <thead>
                        <tr>
                            <th>Pos Anggaran</th>
                            <th>Divisi</th>
                            <th>Periode</th>
                            <th style="text-align:right;">Pagu Alokasi</th>
                            <th style="text-align:right;">Realisasi Terpakai</th>
                            <th style="text-align:right;">Sisa Pagu</th>
                            <th style="text-align:center;">Serapan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData['items'] as $b)
                            <tr>
                                <td>
                                    <a href="{{ route('budgets.show', $b) }}" style="font-weight:700; color:var(--brand); text-decoration:underline;">
                                        {{ $b->name }}
                                    </a>
                                    @if($b->project)
                                        <div class="muted" style="font-size:11px;">Proyek: {{ $b->project }}</div>
                                    @endif
                                </td>
                                <td>{{ $b->department }}</td>
                                <td>
                                    <div style="font-size:12px;">{{ $b->period_start->format('d M Y') }}</div>
                                    <div class="muted" style="font-size:11px;">s/d {{ $b->period_end->format('d M Y') }}</div>
                                </td>
                                <td style="text-align:right; font-weight:600; font-variant-numeric:tabular-nums;">
                                    {{ $b->formatted_allocated_amount }}
                                </td>
                                <td style="text-align:right; font-weight:600; font-variant-numeric:tabular-nums; color:#b42318;">
                                    {{ $b->formatted_used_amount }}
                                </td>
                                <td style="text-align:right; font-weight:700; font-variant-numeric:tabular-nums; color:#15803d;">
                                    {{ $b->formatted_remaining_amount }}
                                </td>
                                <td style="text-align:center;">
                                    @php
                                        $badgeBg = match($b->usage_level) {
                                            'exhausted' => 'background:#fee2e2; color:#991b1b;',
                                            'high_warning' => 'background:#ffedd5; color:#c2410c;',
                                            'warning' => 'background:#fef3c7; color:#92400e;',
                                            default => 'background:#eef7f1; color:var(--brand);',
                                        };
                                    @endphp
                                    <span class="badge" style="{{ $badgeBg }}">
                                        {{ $b->usage_percentage }}%
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" style="text-transform:capitalize;">{{ $b->status }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                @endif
            </table>
        </div>

        <div style="padding:16px 20px; border-top:1px solid var(--line);">
            {{ $reportData['items']->links() }}
        </div>
    @endif
</div>
@endsection

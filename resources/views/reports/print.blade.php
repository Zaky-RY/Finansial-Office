<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan — {{ ucfirst($reportType) }} — Finance Office</title>
    <style>
        :root {
            --ink: #111827;
            --muted: #4b5563;
            --line: #d1d5db;
            --brand: #0f4e3a;
        }
        * { box-sizing: border-box; }
        body {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: var(--ink);
            background: #f3f4f6;
            margin: 0;
            padding: 24px;
            font-size: 13px;
            line-height: 1.5;
        }
        .page {
            max-width: 960px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--brand);
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .company-name {
            font-size: 20px;
            font-weight: 800;
            color: var(--brand);
            letter-spacing: -0.02em;
        }
        .report-title {
            font-size: 16px;
            font-weight: 700;
            margin-top: 4px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            background: #f9fafb;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 24px;
            font-size: 12px;
        }
        .meta-item { display: flex; }
        .meta-label { width: 130px; font-weight: 600; color: var(--muted); }
        .meta-val { font-weight: 600; color: var(--ink); }
        
        .summary-boxes {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
        }
        .summary-box {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid var(--line);
            border-radius: 6px;
            background: #fcfdfd;
        }
        .summary-box-title { font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--muted); }
        .summary-box-val { font-size: 18px; font-weight: 800; margin-top: 4px; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid var(--line);
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background: #f3f4f6;
            font-weight: 700;
            color: #1f2937;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .num { font-variant-numeric: tabular-nums; }
        
        .signatures {
            margin-top: 48px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        .signature-col {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            margin-top: 64px;
            border-top: 1px solid #000;
            padding-top: 6px;
            font-weight: 700;
        }
        
        .actions-bar {
            max-width: 960px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            background: #0f4e3a;
            color: #fff;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            border: none;
            font-size: 13px;
        }
        .btn-outline {
            background: transparent;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .page { padding: 0; box-shadow: none; border-radius: 0; max-width: 100%; }
            .no-print { display: none !important; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body>

<div class="actions-bar no-print">
    <button onclick="window.close()" class="btn btn-outline">← Kembali</button>
    <button onclick="window.print()" class="btn">🖨️ Cetak Dokumen / Simpan PDF</button>
</div>

<div class="page">
    <div class="header">
        <div>
            <div class="company-name">FINANCE OFFICE</div>
            <div class="report-title">
                @if($reportType === 'cashflow')
                    LAPORAN ARUS KAS & TRANSAKSI KAS INTERNAL
                @elseif($reportType === 'reimbursement')
                    LAPORAN REKAPITULASI KLAIM REIMBURSEMENT
                @else
                    LAPORAN MONITORING & REALISASI ANGGARAN DIVISI
                @endif
            </div>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">Sistem Tata Kelola Keuangan Perusahaan</div>
        </div>
        <div style="text-align:right; font-size:11px; color:var(--muted);">
            <div><strong>Nomor Dokumen:</strong> REP-{{ strtoupper($reportType) }}-{{ date('YmdHi') }}</div>
            <div><strong>Waktu Cetak:</strong> {{ now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</div>
            <div><strong>Dicetak Oleh:</strong> {{ $user->name }} ({{ strtoupper($user->role) }})</div>
        </div>
    </div>

    <div class="meta-grid">
        <div class="meta-item">
            <span class="meta-label">Periode Mulai:</span>
            <span class="meta-val">{{ !empty($filters['start_date']) ? \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') : 'Semua Periode' }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Divisi:</span>
            <span class="meta-val">{{ $filters['department'] ?? 'Semua Divisi' }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Periode Selesai:</span>
            <span class="meta-val">{{ !empty($filters['end_date']) ? \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') : 'Sampai Hari Ini' }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Kategori / Status:</span>
            <span class="meta-val">{{ $filters['category'] ?? ($filters['status'] ?? 'Semua Kategori & Status') }}</span>
        </div>
    </div>

    {{-- Ringkasan Agregasi Finansial --}}
    @if($reportType === 'cashflow')
        <div class="summary-boxes">
            <div class="summary-box">
                <div class="summary-box-title">Total Pemasukan Kas</div>
                <div class="summary-box-val" style="color:#0f4e3a;">{{ $reportData['formatted_income'] }}</div>
            </div>
            <div class="summary-box">
                <div class="summary-box-title">Total Pengeluaran Kas</div>
                <div class="summary-box-val" style="color:#b42318;">{{ $reportData['formatted_expense'] }}</div>
            </div>
            <div class="summary-box">
                <div class="summary-box-title">Saldo Bersih Arus Kas</div>
                <div class="summary-box-val" style="color:#15803d;">{{ $reportData['formatted_balance'] }}</div>
            </div>
        </div>
    @elseif($reportType === 'reimbursement')
        <div class="summary-boxes">
            <div class="summary-box">
                <div class="summary-box-title">Total Telah Dibayar</div>
                <div class="summary-box-val" style="color:#15803d;">{{ $reportData['formatted_paid_amount'] }}</div>
            </div>
            <div class="summary-box">
                <div class="summary-box-title">Total Nilai Diajukan</div>
                <div class="summary-box-val" style="color:#0f4e3a;">{{ $reportData['formatted_total_amount'] }}</div>
            </div>
            <div class="summary-box">
                <div class="summary-box-title">Dalam Antrean Persetujuan</div>
                <div class="summary-box-val" style="color:#b54708;">{{ number_format($reportData['pending_count']) }} berkas</div>
            </div>
        </div>
    @else
        <div class="summary-boxes">
            <div class="summary-box">
                <div class="summary-box-title">Total Pagu Alokasi</div>
                <div class="summary-box-val" style="color:#0f4e3a;">{{ $reportData['formatted_allocated'] }}</div>
            </div>
            <div class="summary-box">
                <div class="summary-box-title">Total Dana Terserap</div>
                <div class="summary-box-val" style="color:#b42318;">{{ $reportData['formatted_used'] }}</div>
            </div>
            <div class="summary-box">
                <div class="summary-box-title">Sisa Anggaran Operasional</div>
                <div class="summary-box-val" style="color:#15803d;">{{ $reportData['formatted_remaining'] }}</div>
            </div>
            <div class="summary-box">
                <div class="summary-box-title">Rata-rata Serapan</div>
                <div class="summary-box-val" style="color:#b54708;">{{ $reportData['overall_percentage'] }}%</div>
            </div>
        </div>
    @endif

    {{-- Tabel Data --}}
    @if($reportType === 'cashflow')
        <table>
            <thead>
                <tr>
                    <th style="width:30px;" class="text-center">No</th>
                    <th>No. Transaksi</th>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Divisi / Kategori</th>
                    <th>Keterangan / Vendor</th>
                    <th class="text-right">Nominal (IDR)</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['items'] as $idx => $tx)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td><strong>{{ $tx->transaction_number }}</strong></td>
                        <td>{{ $tx->transaction_date->format('d/m/Y') }}</td>
                        <td>{{ $tx->isIncome() ? 'Pemasukan' : 'Pengeluaran' }}</td>
                        <td>{{ $tx->department ?? '-' }} / {{ $tx->category ?? '-' }}</td>
                        <td>{{ $tx->vendor ? $tx->vendor . ' — ' : '' }}{{ $tx->description }}</td>
                        <td class="text-right num" style="font-weight:700;">
                            {{ $tx->isIncome() ? '+' : '-' }} {{ $tx->formatted_amount }}
                        </td>
                        <td class="text-center">{{ ucfirst($tx->payment_status) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding:20px; color:var(--muted);">Tidak ada transaksi yang tercatat pada kriteria ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($reportType === 'reimbursement')
        <table>
            <thead>
                <tr>
                    <th style="width:30px;" class="text-center">No</th>
                    <th>Nomor Pengajuan</th>
                    <th>Tanggal</th>
                    <th>Pemohon</th>
                    <th>Divisi</th>
                    <th>Keperluan</th>
                    <th class="text-right">Total (IDR)</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['items'] as $idx => $rem)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td><strong>{{ $rem->number }}</strong></td>
                        <td>{{ $rem->created_at->format('d/m/Y') }}</td>
                        <td>{{ $rem->user->name }}</td>
                        <td>{{ $rem->department }}</td>
                        <td>{{ $rem->purpose }}</td>
                        <td class="text-right num" style="font-weight:700;">{{ $rem->formatted_total_amount }}</td>
                        <td class="text-center">{{ $rem->status_label ?? $rem->status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding:20px; color:var(--muted);">Tidak ada pengajuan reimbursement pada kriteria ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width:30px;" class="text-center">No</th>
                    <th>Nama Pos Anggaran</th>
                    <th>Divisi</th>
                    <th>Periode</th>
                    <th class="text-right">Pagu Alokasi</th>
                    <th class="text-right">Dana Terpakai</th>
                    <th class="text-right">Sisa Pagu</th>
                    <th class="text-center">Serapan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['items'] as $idx => $b)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td><strong>{{ $b->name }}</strong>{{ $b->project ? ' (' . $b->project . ')' : '' }}</td>
                        <td>{{ $b->department }}</td>
                        <td>{{ $b->period_start->format('d/m/Y') }} s/d {{ $b->period_end->format('d/m/Y') }}</td>
                        <td class="text-right num">{{ $b->formatted_allocated_amount }}</td>
                        <td class="text-right num">{{ $b->formatted_used_amount }}</td>
                        <td class="text-right num" style="font-weight:700;">{{ $b->formatted_remaining_amount }}</td>
                        <td class="text-center">{{ $b->usage_percentage }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding:20px; color:var(--muted);">Tidak ada pos anggaran pada kriteria ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="signatures">
        <div class="signature-col">
            <div>Dibuat Oleh:</div>
            <div class="signature-line">{{ $user->name }}</div>
            <div style="font-size:11px; color:var(--muted);">{{ ucfirst($user->role) }}</div>
        </div>
        <div class="signature-col">
            <div>Ditinjau Oleh:</div>
            <div class="signature-line">( Head of Finance )</div>
            <div style="font-size:11px; color:var(--muted);">Tim Keuangan Kantor</div>
        </div>
        <div class="signature-col">
            <div>Disetujui Oleh:</div>
            <div class="signature-line">( Direktur Utama )</div>
            <div style="font-size:11px; color:var(--muted);">Manajemen Eksekutif</div>
        </div>
    </div>
</div>

</body>
</html>


@extends('layouts.app')
@section('title', 'Ruang Kerja Keuangan — Finance Office')
@section('header', 'Ruang Kerja Keuangan')
@section('content')
<div style="margin-bottom:24px">
    <h1>Workspace Finance</h1>
    <div class="muted">Panel kelola transaksi kas, review anggaran, dan verifikasi permohonan.</div>
</div>

<div class="card">
    <div class="empty-state" style="text-align:center; padding:40px 20px;">
        <div style="font-size:36px; margin-bottom:12px;">📊</div>
        <h2 style="margin:0 0 8px;">Area Otorisasi Tim Finance</h2>
        <p class="muted" style="max-width:480px; margin:0 auto 20px;">
            Halaman ini terproteksi oleh backend role middleware dan hanya dapat diakses oleh staf atau penanggung jawab tim Finance.
        </p>
        <span class="badge" style="background:var(--soft); color:var(--brand); font-size:12px; padding:6px 14px;">
            Otorisasi Terverifikasi: {{ strtoupper(auth()->user()->role) }}
        </span>
    </div>
</div>
@endsection


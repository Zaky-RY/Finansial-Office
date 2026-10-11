@extends('layouts.app')
@section('title', 'Ringkasan Eksekutif — Finance Office')
@section('header', 'Ringkasan Eksekutif')
@section('content')
<div style="margin-bottom:24px">
    <h1>Ringkasan Eksekutif</h1>
    <div class="muted">Pemantauan performa keuangan global dan persetujuan nominal tinggi untuk Direksi.</div>
</div>

<div class="card">
    <div class="empty-state" style="text-align:center; padding:40px 20px;">
        <div style="font-size:36px; margin-bottom:12px;">🏛️</div>
        <h2 style="margin:0 0 8px;">Area Khusus Direksi</h2>
        <p class="muted" style="max-width:480px; margin:0 auto 20px;">
            Halaman ini terproteksi oleh backend role middleware dan hanya dapat diakses oleh Direktur.
        </p>
        <span class="badge" style="background:var(--soft); color:var(--brand); font-size:12px; padding:6px 14px;">
            Otorisasi Terverifikasi: {{ strtoupper(auth()->user()->role) }}
        </span>
    </div>
</div>
@endsection


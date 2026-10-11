# Finance Office — Sistem Manajemen Keuangan & Persetujuan Internal

Aplikasi tata kelola keuangan internal kantor terpusat, transparan, teruji, dan terdokumentasi dengan alur persetujuan bertingkat (*multi-level approval*), pelacakan pagu anggaran (*budget control*), pencatatan transaksi kas masuk/keluar, dan laporan keuangan komprehensif.

---

## 🌟 Fitur Utama & Modul (MVP)

1. **Autentikasi & Otorisasi Berbasis Peran (RBAC):**
   - 5 Role Pengguna: `employee`, `manager`, `finance`, `director`, dan `admin`.
   - Proteksi sesi, proteksi user nonaktif, rate limiting login (`throttle:login`), dan pencegahan IDOR (Insecure Direct Object Reference) di seluruh controller dan policy.
2. **Pengajuan Reimbursement Multi-Item:**
   - Pembuatan draf klaim biaya, rincian multi-item nota/kuitansi, kalkulasi presisi tinggi di backend, dan lampiran berkas bukti transaksi privat (`storage/app/local`).
3. **Approval Bertingkat & Anti-Self-Approval:**
   - Persetujuan berjenjang: *Manager Divisi* &rarr; *Verifikasi Finance* &rarr; *Direktur Eksekutif* (otomatis untuk pengajuan &gt; Rp5.000.000,00).
   - Penegakan mutlak **Anti-Self-Approval**: approver dilarang menyetujui pengajuannya sendiri di tingkat backend policy maupun service.
   - Penolakan (*reject*) wajib disertai alasan, pengembalian untuk revisi (*return*) wajib disertai instruksi perbaikan.
4. **Pencatatan Transaksi Kas & Saldo Berjalan:**
   - Pencatatan pemasukan dan pengeluaran kas kantor dengan nomor transaksi unik otomatis.
   - Formula saldo mutlak: `Saldo = Total Pemasukan Aktif - Total Pengeluaran Aktif`.
   - Larangan keras *Hard Delete* pada data transaksi historis; pembatalan (*cancellation*) memerlukan alasan wajib dan tercatat di audit log.
5. **Pengendalian & Realisasi Anggaran (Budget Management):**
   - Alokasi pagu dana per divisi dan proyek dengan periode waktu mulai dan selesai.
   - Formula serapan: `Sisa Anggaran = Anggaran Dialokasikan - Anggaran Terpakai`.
   - Ambang batas peringatan kuota: **75% (Warning)**, **90% (High Warning)**, dan **100% (Budget Exhausted)**.
   - Pemotongan dan pengembalian pagu anggaran berlangsung secara atomik (`DB::transaction`) saat transaksi kas dicatat atau dibatalkan.
6. **Laporan Keuangan & Ekspor Streaming:**
   - Tiga instrumen laporan: Arus Kas (*Cash Flow*), Rekapitulasi Reimbursement, dan Realisasi Anggaran Divisi.
   - Filter tanggal terpadu, isolasi divisi untuk peran Manager, dan ekspor CSV/Excel berstandar RFC 4180 dengan Byte Order Mark UTF-8 (`\xEF\xBB\xBF`).
   - Tampilan cetak resmi (`@media print`) yang bersih untuk cetak dokumen fisik atau simpan sebagai PDF.
7. **Auditability & Logging Imutabel:**
   - Seluruh mutasi sensitif dicatat ke tabel `audit_logs` (aktor, aksi, modul, nilai sebelum & sesudah, alamat IP, dan user-agent).

---

## 🛠️ Tech Stack & Standar Rekayasa

- **Framework:** Laravel 10
- **Bahasa:** PHP 8.1+ / 8.2
- **Database:** MySQL / PostgreSQL / SQLite
- **Presisi Moneter:** Ekstensi PHP `bcmath` (`bcadd`, `bcsub`, `bcmul`, `bcdiv`, `bccomp`) tanpa floating-point error.
- **Tipe Kolom Finansial:** `DECIMAL(15, 2)` di seluruh skema migration.
- **Zona Waktu:** `Asia/Jakarta` (UTC+7 / WIB).
- **Format Tampilan:** Rupiah Indonesia (`Rp 1.500.000,00`).
- **File Storage:** Private storage disk (`storage/app/local`, non-public).

---

## 🚀 Panduan Menjalankan Aplikasi

### 1. Prasyarat Sistem
- PHP >= 8.1 dengan ekstensi `bcmath`, `pdo`, `sqlite3`/`mysql`, `fileinfo`.
- Composer.
- Server database (MySQL atau SQLite).

### 2. Instalasi Dependensi
```bash
composer install
```

### 3. Konfigurasi Environment
Salin file konfigurasi environment dan sesuaikan kredensial database:
```bash
cp .env.example .env
php artisan key:generate
```

Pastikan konfigurasi zona waktu di `.env`:
```env
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
```

### 4. Migrasi & Seeder Database
Jalankan migrasi dan seeder untuk menyiapkan struktur database dan akun pengguna bawaan:
```bash
php artisan migrate --seed
```

### 5. Menjalankan Server Lokal
```bash
php artisan serve
```
Akses aplikasi melalui peramban di `http://127.0.0.1:8000`.

---

## 👥 Akun Uji Coba Bawaan (Default Seeders)

Seluruh akun default menggunakan password: `password123`

| Email | Peran (Role) | Divisi | Hak Akses Utama |
|---|---|---|---|
| `admin@office.test` | `admin` | IT | Konfigurasi sistem, melihat audit log |
| `director@office.test` | `director` | Management | Ringkasan eksekutif, persetujuan nominal tinggi (&gt; Rp5 jt), seluruh laporan |
| `finance@office.test` | `finance` | Finance & Accounting | Verifikasi reimbursement, pembayaran/pencairan kas, catat transaksi kas, kelola anggaran, seluruh laporan |
| `manager@office.test` | `manager` | Technology | Persetujuan tahap divisi Technology, melihat realisasi anggaran divisi |
| `employee@office.test` | `employee` | Technology | Mengajukan reimbursement pribadi, unggah bukti transaksi |
| `inactive@office.test` | `employee` | Technology | Akun dinonaktifkan (akses login diblokir) |

---

## 🧪 Pengujian Otomatis (Test Suite)

Jalankan seluruh rangkaian pengujian fitur, otorisasi, integritas finansial, dan audit log:
```bash
php artisan test
```

Jalankan code linter / formatter (Laravel Pint):
```bash
vendor/bin/pint --test
```

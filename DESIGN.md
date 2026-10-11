# DESIGN.md

# Finance Office — Technical Design Document

> **Metadata Dokumen:**  
> - **Nama Aplikasi:** Finance Office  
> - **Tipe Aplikasi:** Sistem Pengelolaan Keuangan Internal Kantor  
> - **Versi Desain:** 1.0 (MVP)  
> - **Mata Uang Utama:** IDR (Rupiah)  
> - **Zona Waktu:** `Asia/Jakarta` (UTC+7)  
> - **Bahasa Antarmuka:** Bahasa Indonesia  

---

## 1. Tujuan dan Batasan

### 1.1 Tujuan Desain
Dokumen ini merupakan acuan teknis arsitektur, domain data, alur persetujuan, dan keamanan untuk pengembangan aplikasi Finance Office.
Prioritas teknis utama:
1. **Keamanan & Otorisasi Finansial:** Perlindungan data sensitif, penegakan Zero-Trust pada backend, dan proteksi IDOR.
2. **Akurasi Perhitungan Moneter:** Nol toleransi terhadap kesalahan floating-point dengan standardisasi presisi `DECIMAL(18,2)`.
3. **Kejelasan Alur Approval:** State machine yang deterministik dengan penegakan aturan Anti-Self-Approval.
4. **Auditability Penuh:** Seluruh mutasi finansial dapat dilacak secara kronologis dan bersifat *append-only*.
5. **Kemudahan Pemeliharaan:** Arsitektur modular monolith yang clean, mengikuti kaidah SOLID dan DRY.

### 1.2 Batasan Ruang Lingkup (Scope MVP)
- **Fitur Termasuk (In-Scope):**
  - Autentikasi dan Role-Based Access Control (5 role pengguna).
  - Manajemen master data: Pengguna, Divisi, Kategori Transaksi, Vendor.
  - Pencatatan Pemasukan dan Pengeluaran kas kantor.
  - Pengajuan Reimbursement (multi-item) dan Permintaan Dana (Fund Request).
  - State machine approval berjenjang berdasarkan threshold nominal.
  - Alokasi dan pemantauan Anggaran (Budget) dengan alert sistem (75%, 90%, 100%).
  - Upload dan proteksi bukti transaksi pada private storage.
  - Dashboard keuangan eksekutif & operasional.
  - Laporan keuangan internal dan ekspor data (Excel & PDF).
  - In-app notification untuk perubahan status pengajuan.
  - Audit log aktivitas dan mutasi data sensitif.
- **Fitur di Luar Scope MVP (Out-of-Scope):**
  - Payroll / penggajian karyawan otomatis.
  - Integrasi payment gateway atau transfer bank otomatis via direct API.
  - Perhitungan pajak otomatis (PPh 21/23/PPN).
  - Sistem akuntansi double-entry penuh (jurnal umum, buku besar penutup).
  - OCR nota/invoice otomatis.
  - Prediksi arus kas berbasis AI.
  - Integrasi WhatsApp Gateway / SMS Gateway.
  - Aplikasi mobile native (iOS / Android).
  - Penghapusan permanen (*hard delete*) pada data transaksi keuangan.

---

## 2. Arsitektur

### 2.1 Pola Arsitektur: Modular Monolith
Aplikasi dibangun menggunakan pola **Modular Monolith** berbasis framework Laravel. Seluruh modul berada dalam satu codebase dan satu deployment unit, tetapi memiliki batas tanggung jawab (*boundary*) yang jelas antar modul domain.

```text
Browser / Client (Web UI)
        │
        ▼
Presentation Layer (Blade / Livewire / Tailwind CSS)
        │
        ▼
HTTP Layer (Routes, Controllers, Form Requests, Policies, Middlewares)
        │
        ▼
Application / Service Layer (Services, Actions, Event Listeners, Jobs)
        │
        ▼
Domain Layer (Eloquent Models, Business Rules, State Transitions, Value Objects)
        │
        ▼
Infrastructure Layer (Database MySQL/PostgreSQL, Private Disk Storage, Queues, Cache)
```

### 2.2 Prinsip Request & Transaction Boundary (ACID)
1. **Single Source of Truth:** Seluruh kalkulasi saldo, sisa anggaran, dan validasi approval wajib dilakukan di backend. Frontend tidak diperbolehkan menjadi sumber kebenaran kalkulasi moneter.
2. **Database Transaction Boundary:** Setiap operasi mutasi majemuk wajib dibungkus dalam `DB::transaction()`:
   - Approval yang memicu perubahan status pengajuan dan alokasi dana.
   - Pencatatan transaksi pengeluaran yang memotong sisa anggaran dan mencatat audit log.
   - Pembatalan transaksi yang mengembalikan kuota anggaran.
   Jika terjadi kegagalan di salah satu langkah, seluruh perubahan wajib di-rollback secara otomatis.

---

## 3. Modul

Struktur modul aplikasi dikelompokkan ke dalam domain-domain terisolasi:

```text
app/
├── Actions/
│   ├── Approval/
│   ├── Budget/
│   ├── Reimbursement/
│   └── Transaction/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Policies/
├── Services/
│   ├── ApprovalService.php
│   ├── AuditLogService.php
│   ├── BudgetService.php
│   ├── ReimbursementService.php
│   ├── ReportService.php
│   └── TransactionService.php
└── Support/
    ├── Enums/
    ├── Money/
    └── DateTime/
```

### Rincian Tanggung Jawab Modul:
1. **Auth & Identity Module:** Autentikasi sesi, reset password, dan manajemen user/divisi.
2. **Transaction Module:** Pencatatan pemasukan (`income`) dan pengeluaran (`expense`), validasi anti-duplikasi, serta status pembayaran (`unpaid`, `scheduled`, `paid`, `cancelled`).
3. **Reimbursement Module:** Pengajuan reimbursement multi-item, kalkulasi total biaya dari detail item, dan relasi lampiran nota.
4. **Fund Request Module:** Permintaan dana di muka sebelum transaksi, validasi kuota anggaran divisi terkait.
5. **Approval Engine Module:** Evaluasi alur persetujuan bertingkat, penegakan anti-self-approval, pencatatan alasan tolak/revisi.
6. **Budget Module:** Alokasi kuota anggaran per periode, pelacakan pemakaian (`used_amount`), dan kalkulasi sisa dana.
7. **Vendor Module:** Master vendor rekanan, proteksi nomor rekening, dan relasi transaksi pengeluaran.
8. **Attachment Module:** Penyimpanan aman berkas bukti transaksi pada private disk, pembuatan UUID file, dan proteksi otorisasi unduhan.
9. **Reporting & Export Module:** Agregasi data transaksi, perangkuman neraca sederhana, serta job export asynchronous untuk file besar.
10. **Audit Log Module:** Pencatatan mutasi data sensitif secara *append-only* (immutable).

---

## 4. Model Data

### 4.1 Entitas & Atribut Utama
Seluruh nominal keuangan wajib menggunakan tipe data `DECIMAL(18,2)`.

- **`users`:** `id`, `name`, `email`, `phone`, `password`, `role` (enum: employee, manager, finance, director, admin), `department_id`, `manager_id`, `status` (active, inactive), `timestamps`.
- **`departments`:** `id`, `name`, `code`, `manager_id`, `status`, `timestamps`.
- **`categories`:** `id`, `name`, `code`, `type` (enum: income, expense, both), `status`, `timestamps`.
- **`vendors`:** `id`, `name`, `contact_name`, `email`, `phone`, `address`, `bank_name`, `bank_account_number`, `bank_account_name`, `tax_number`, `status`, `timestamps`.
- **`transactions`:** `id`, `transaction_number` (unique), `type` (income, expense), `transaction_date`, `amount` (`DECIMAL(18,2)`), `currency` (default IDR), `category_id`, `department_id`, `project_id` (nullable), `vendor_id` (nullable), `payment_method`, `payment_status` (unpaid, scheduled, paid, cancelled), `description`, `source_reference` (nullable), `created_by`, `cancelled_by`, `cancelled_at`, `cancellation_reason`, `timestamps`.
- **`reimbursements`:** `id`, `reimbursement_number` (unique), `requester_id`, `department_id`, `submission_date`, `total_amount` (`DECIMAL(18,2)`), `currency`, `purpose`, `status`, `payment_date`, `paid_by`, `rejection_reason`, `completed_at`, `timestamps`.
- **`reimbursement_items`:** `id`, `reimbursement_id`, `expense_date`, `category_id`, `description`, `amount` (`DECIMAL(18,2)`), `vendor_id` (nullable), `receipt_number`, `timestamps`.
- **`fund_requests`:** `id`, `request_number` (unique), `requester_id`, `department_id`, `budget_id`, `purpose`, `requested_amount` (`DECIMAL(18,2)`), `usage_date`, `status`, `approved_amount` (`DECIMAL(18,2)`), `disbursed_at`, `timestamps`.
- **`budgets`:** `id`, `name`, `period_start`, `period_end`, `department_id`, `category_id`, `project_id` (nullable), `allocated_amount` (`DECIMAL(18,2)`), `used_amount` (`DECIMAL(18,2)`), `status` (draft, active, closed, cancelled), `created_by`, `timestamps`.
- **`approvals`:** `id`, `approvable_type`, `approvable_id`, `approver_id`, `approval_level`, `status` (pending, approved, rejected, returned), `comment`, `decided_at`, `timestamps`.
- **`attachments`:** `id`, `attachable_type`, `attachable_id`, `original_name`, `stored_name` (UUID), `path`, `mime_type`, `file_size`, `uploaded_by`, `timestamps`.
- **`audit_logs`:** `id`, `user_id`, `action`, `module`, `auditable_type`, `auditable_id`, `old_values` (JSON), `new_values` (JSON), `ip_address`, `user_agent`, `created_at`.

### 4.2 Database Indexing
Index performa wajib diterapkan pada:
- Kolom kode unik: `transaction_number`, `reimbursement_number`, `request_number`.
- Foreign keys: `department_id`, `category_id`, `requester_id`, `vendor_id`, `created_by`.
- Status & filtering: `status`, `payment_status`, `transaction_date`, `created_at`.

---

## 5. Alur Domain

### 5.1 Siklus Reimbursement
1. **Penyusunan Draft:** Karyawan mengisi deskripsi pengajuan dan detail item pengeluaran (`reimbursement_items`).
2. **Kalkulasi Total:** Backend menghitung total nominal $\sum (\text{item.amount})$.
3. **Upload Bukti:** Pemohon mengunggah bukti nota/struk ke storage privat.
4. **Submit Pengajuan:** Pengajuan dikunci dari penyuntingan pemohon; sistem mengevaluasi hierarki approval.
5. **Approval Berjenjang:**
   - Manager memeriksa kelayakan biaya operasional tim.
   - Finance memverifikasi keabsahan nota dan ketersediaan mata anggaran.
   - Direktur menyetujui jika nominal melebihi ambang batas (default > Rp5.000.000).
6. **Pembayaran & Realisasi:** Finance melakukan transfer dana, mengubah status menjadi `paid`, otomatis membentuk record pengeluaran kas (`Transaction`), memotong saldo anggaran, dan menyelesaikan pengajuan.

### 5.2 Siklus Permintaan Dana (Fund Request)
1. Pengajuan permohonan dana diajukan sebelum transaksi dilakukan.
2. Sistem memvalidasi ketersediaan sisa anggaran divisi.
3. Permintaan disetujui melalui alur approval (Manager $\rightarrow$ Finance $\rightarrow$ Direktur jika diperlukan).
4. Finance mencairkan dana (`disbursed`). Pemohon menyerahkan bukti transaksi real pasca-kegiatan untuk verifikasi akhir.

### 5.3 Validasi Duplikasi Transaksi (Idempotency)
Sistem mencegah double-claim/double-payment dengan memeriksa kombinasi unik:
`pemohon_id` + `tanggal_transaksi` + `nominal` + `nomor_nota / invoice`.

---

## 6. State Transition

### 6.1 State Machine Reimbursement
```text
           [ Draft ]
               │
               ▼ (submit)
  [ Waiting Manager Approval ]
     │               │               │
 (approve)        (return)        (reject)
     │               │               │
     │               ▼               ▼
     │     [ Returned for Revision ] [ Rejected ]
     │               │
     │         (resubmit)
     │               │
     ▼               ▼
  [ Waiting Finance Verification ]
     │               │
 (approve > thres) (approve <= thres)
     │               │
     ▼               │
[ Waiting Director ] │
     │               │
 (approve)           │
     │               │
     └───────┬───────┘
             ▼
        [ Approved ]
             │
             ▼ (pay)
          [ Paid ]
             │
             ▼ (confirm)
        [ Completed ]
```
- Transisi `cancelled` dapat dilakukan pada status aktif sebelum pembayaran, wajib menyertakan alasan pembatalan dan tercatat di audit log.

### 6.2 State Machine Transaction
`draft` $\rightarrow$ `posted` $\rightarrow$ `scheduled` $\rightarrow$ `paid` / `cancelled`.
Transaksi yang telah berstatus `paid` dilarang dihapus (hard delete).

---

## 7. Authorization

### 7.1 Matriks Hak Akses Peran Pengguna
| Fitur / Aksi | `employee` | `manager` | `finance` | `director` | `admin` |
|---|:---:|:---:|:---:|:---:|:---:|
| Membuat Reimbursement / Permintaan Dana | Ya | Ya | Ya | Ya | Ya |
| Melihat Pengajuan Pribadi | Ya | Ya | Ya | Ya | Ya |
| Melihat Pengajuan Divisi | Tidak | Ya | Ya | Ya | Ya |
| Approval Tingkat Manager | Tidak | Ya (Divisi) | Opsional | Opsional | Tidak |
| Verifikasi Finance & Pembayaran | Tidak | Tidak | Ya | Tidak | Tidak |
| Approval Nominal Tinggi | Tidak | Tidak | Tidak | Ya | Tidak |
| Input Pemasukan / Pengeluaran Manual | Tidak | Tidak | Ya | Tidak | Tidak |
| Pengelolaan Anggaran & Vendor | Tidak | View (Divisi)| Ya | View | Terbatas |
| Akses Laporan Penuh & Export | Tidak | Divisi | Ya | Ya | Terbatas |
| Manajemen User, Role, & Konfigurasi | Tidak | Tidak | Tidak | Tidak | Ya |
| Melihat Audit Log | Tidak | Tidak | Terbatas | Ya | Ya |

### 7.2 Aturan Kunci Otorisasi
1. **Anti-Self-Approval:** Pengguna tidak dapat menyetujui pengajuannya sendiri (`requester_id !== auth()->id()`).
2. **Server-Side Enforcement:** Proteksi mutlak di backend via Laravel Policy (`ReimbursementPolicy`, `TransactionPolicy`) dan Form Request `authorize()`.
3. **Data Rekening Terproteksi:** Nomor rekening vendor dan karyawan hanya boleh diakses oleh pemiliknya, role `finance`, dan `admin`.

---

## 8. Integrasi Internal

Seluruh komunikasi antar domain dalam modular monolith diatur melalui event-driven architecture internal:
1. **Domain Events & Listeners:**
   - `ReimbursementSubmitted` $\rightarrow$ Memicu notifikasi in-app ke approver pertama.
   - `ReimbursementApproved` $\rightarrow$ Memicu evaluasi step approval berikutnya atau notifikasi siap bayar ke Finance.
   - `ReimbursementPaid` $\rightarrow$ Memanggil `TransactionService` untuk mencatat transaksi pengeluaran dan `BudgetService` untuk memperbarui realisasi anggaran.
   - `BudgetThresholdExceeded` $\rightarrow$ Memicu warning alert ke Manager divisi dan Finance.
2. **Asynchronous Background Queue:**
   - Pengiriman notifikasi sistem.
   - Pencatatan log aktivitas berat.
   - Pemrosesan export laporan Excel/PDF berukuran besar.

---

## 9. File Storage

### 9.1 Keamanan Dokumen Privat
Semua file bukti transaksi (nota, struk, faktur pajak) berstatus dokumen finansial rahasia:
- **Lokasi Penyimpanan:** Private storage disk (`storage/app/private/`).
- **Larangan Keras:** Dilarang menempatkan file di direktori `public/` atau membuat symlink publik ke folder lampiran.
- **Konvensi Penamaan:** Menggunakan UUID acak (`{uuid}.{extension}`) untuk mencegah enumerasi nama berkas asli.
- **Validasi Berkas:** Ekstensi diizinkan: `PDF`, `JPG`, `JPEG`, `PNG`. Ukuran berkas maksimal 5 MB.

### 9.2 Jalur Akses File (Protected Streaming)
Akses ke file lampiran wajib melewati route controller terproteksi:
```text
GET /attachments/{attachment}/download
```
Controller memeriksa otorisasi `Gate::authorize('view', $attachment)` sebelum menyajikan berkas via temporary signed URL atau file stream response.

---

## 10. Reporting

### 10.1 Katalog Laporan MVP
1. **Laporan Arus Kas:** Rekapitulasi pemasukan, pengeluaran, dan saldo kas bersih.
2. **Laporan Reimbursement:** Rekap status pengajuan, waktu penyelesaian, dan nominal per karyawan/divisi.
3. **Laporan Realisasi Anggaran:** Perbandingan alokasi vs pemakaian anggaran per divisi dan kategori.
4. **Laporan Transaksi per Vendor / Kategori:** Rincian pengeluaran per rekanan bisnis.

### 10.2 Mekanisme Query & Export
- **Pencegahan N+1 Query:** Menggunakan eager loading pada seluruh relasi (`with(['department', 'category', 'user'])`).
- **Agregasi Database:** Seluruh kalkulasi saldo dan total laporan diproses langsung di level database (`SUM`, `COUNT`) tanpa memuat seluruh baris ke memory PHP.
- **Export Asynchronous:** Ekspor data lebih dari 1.000 baris dialihkan ke queue worker. Hasil file disimpan sementara di private storage dan disajikan via tautan unduhan setelah selesai.

---

## 11. Security Design

1. **Proteksi Injeksi:** Menggunakan PDO parameter binding bawaan Eloquent ORM untuk mencegah SQL Injection; escaping output di view untuk mencegah XSS.
2. **Kredensial & Autentikasi:** Password di-hash menggunakan algoritma `bcrypt` atau `argon2id`. Proteksi CSRF aktif di seluruh form mutasi.
3. **Rate Limiting:** Throttle middleware diterapkan pada endpoint sensitif (login, reset password, submit transaksi, approval).
4. **Audit Log Append-Only:** Pencatatan immutable mencakup: actor (`user_id`), nama aksi, model target, nilai lama (`old_values`), nilai baru (`new_values`), alamat IP, dan user agent. Tabel audit log tidak memiliki fitur edit/delete.

---

## 12. Testing Strategy

Pengujian wajib memastikan integritas alur bisnis finansial:
1. **Unit Testing:**
   - Ketepatan penjumlahan total item reimbursement ($\text{Total} = \sum \text{items}$).
   - Ketepatan sisa anggaran ($\text{Sisa} = \text{Alokasi} - \text{Terpakai}$).
   - State transition rules (validasi transisi yang diizinkan dan ditolak).
   - Penegakan format nominal Rupiah.
2. **Feature Testing:**
   - Alur pengajuan reimbursement end-to-end (Draft $\rightarrow$ Submit $\rightarrow$ Manager $\rightarrow$ Finance $\rightarrow$ Paid).
   - Siklus pencatatan transaksi kas manual oleh Finance.
   - Pemicu peringatan anggaran saat pemakaian mencapai threshold (75%, 90%, 100%).
3. **Authorization & Security Testing:**
   - IDOR prevention: Karyawan dilarang mengakses ID pengajuan karyawan lain (ekspektasi: 403 Forbidden).
   - Anti-Self-Approval: Manager/Finance dilarang menyetujui pengajuannya sendiri (ekspektasi: 403 Forbidden).
   - Private Attachment: Akses URL file langsung tanpa autentikasi/izin wajib gagal (ekspektasi: 401/403).
4. **Database Transaction Rollback Testing:**
   - Memastikan rollback penuh terjadi saat terjadi kegagalan di tengah-tengah proses majemuk (misal saat pembuatan transaksi gagal setelah status reimbursement berubah).

---

## 13. Architecture Decisions

- **ADR 001 — Modular Monolith Pattern:** Dipilih karena kesederhanaan deployment, kemudahan pengelolaan ACID transaction, dan skalabilitas yang ideal untuk operasional internal perusahaan.
- **ADR 002 — Private Disk Storage untuk Seluruh Bukti:** Bukti pembayaran dan nota mengandung data finansial sensitif; wajib diisolasi dari akses publik.
- **ADR 003 — Larangan Hard Delete pada Data Finansial:** Rekord transaksi yang diproses tidak boleh dihapus secara permanen guna menjamin konsistensi laporan historis dan kelayakan audit.
- **ADR 004 — DECIMAL(18,2) & Presisi BCMath:** Menghindari floating-point binary representation error pada pembulatan saldo dan total uang.
- **ADR 005 — Configurable Approval Threshold Limits:** Ambang batas nominal persetujuan disimpan dalam konfigurasi terpusat agar fleksibel tanpa mengubah kode sumber.
- **ADR 006 — Mata Uang Tunggal IDR:** Mengurangi kompleksitas konversi mata uang pada fase MVP.

---

## 14. Open Decisions

Bagian ini mencatat keputusan arsitektural dan bisnis yang masih memerlukan validasi lebih lanjut bersama stakeholder:
1. **Nilai Baku Ambang Approval Direktur:**
   - *Status:* Usulan awal batas nominal adalah Rp5.000.000.
   - *Kebutuhan:* Konfirmasi batas nominal definitif dari manajemen eksekutif.
2. **Kebijakan Pengajuan di Luar Kuota Anggaran (Over-Budget):**
   - *Status:* Apakah sistem menerapkan penolakan keras (*hard stop*) secara otomatis, atau mengizinkan submit dengan kewajiban approval langsung dari Direktur Utama.
3. **Aturan Cut-off Penutupan Periode Buku Bulanan:**
   - *Status:* Batas tanggal setiap bulan di mana transaksi bulan sebelumnya dikunci permanen dari pengeditan atau pembatalan oleh tim Finance.
4. **Retensi Penyimpanan Berkas Lampiran:**
   - *Status:* Masa simpan dokumen privat di disk server sebelum pengarsipan jangka panjang (misal 5 tahun sesuai regulasi perpajakan).

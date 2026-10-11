# PROJECT RULES — FINANCE OFFICE

## 1. Peran AI Agent

Kamu adalah senior software engineer yang bertugas mengembangkan aplikasi keuangan internal kantor secara aman, modular, teruji, dan mudah dipelihara.

Prioritas utama:
1. **Keamanan data keuangan:** Perlindungan data sensitif, validasi otorisasi berlapis, dan proteksi dari kebocoran file/informasi finansial.
2. **Ketepatan perhitungan nominal:** Akurasi mutlak pada kalkulasi moneter tanpa toleransi pembulatan mengambang (floating-point error).
3. **Kejelasan alur approval:** State machine persetujuan bertingkat yang transparan, konsisten, dan mencegah konflik kepentingan (anti-self-approval).
4. **Auditability:** Kemampuan melacak seluruh riwayat mutasi, perubahan status, dan interaksi pengguna secara kronologis dan tidak dapat dimanipulasi.
5. **Kualitas kode:** Kode modular, clean architecture, menerapkan SOLID principles, terdokumentasi rapi, dan mudah di-maintain.
6. **Kemudahan penggunaan:** Tampilan responsif, alur intuitif, pesan validasi/error informatif dalam Bahasa Indonesia.
7. **Perubahan kecil dan terkontrol:** Perubahan bertahap, atomic, tidak merusak fitur eksisting, dan mudah diverifikasi.

> **PENTING:** Jangan membuat atau mengasumsikan fitur di luar scope tanpa persetujuan eksplisit dari pengguna.

---

## 2. Konteks Produk

Aplikasi ini digunakan untuk mengelola keuangan internal kantor secara terpusat, transparan, dan terdokumentasi:
- Pengajuan reimbursement.
- Permintaan dana.
- Approval biaya bertingkat.
- Pencatatan pemasukan.
- Pencatatan pengeluaran.
- Pengelolaan anggaran (budget monitoring & alert).
- Pengelolaan master vendor.
- Upload dan verifikasi bukti transaksi.
- Dashboard ringkasan keuangan dan visualisasi.
- Laporan keuangan internal dan export dokumen.
- Audit log aktivitas pengguna dan transaksi.

**Karakteristik & Standar Regional:**
- **Sifat Aplikasi:** Sistem internal kantor dengan data finansial rahasia dan sensitif.
- **Mata Uang Utama:** Rupiah (IDR) — contoh format tampilan: `Rp 1.500.000,00`.
- **Zona Waktu:** `Asia/Jakarta` (UTC+7). Seluruh timestamp disimpan dalam UTC atau zona waktu lokal terstandar.
- **Bahasa Antarmuka:** Bahasa Indonesia untuk seluruh label, placeholder, notifikasi, dan pesan validasi/error.

---

## 3. Teknologi Utama

Jika repository belum menentukan teknologi, gunakan tech stack standar berikut:
- **Backend:** Laravel (versi LTS/terbaru yang stabil)
- **Bahasa:** PHP 8.2+
- **Database:** PostgreSQL atau MySQL
- **Frontend:** Livewire atau React (sesuai struktur awal repository)
- **CSS Framework:** Tailwind CSS
- **Authentication:** Sistem authentication bawaan Laravel (Laravel Breeze / Fortify)
- **File Storage:** Private disk storage (`storage/app/private`, non-public symbolic link)
- **Testing:** PHPUnit atau Pest
- **API:** RESTful API jika integrasi frontend client-side diperlukan

**Aturan Arsitektur & Dependensi:**
- Dilarang mengganti framework, database engine, atau arsitektur dasar tanpa persetujuan.
- Sebelum menulis kode baru:
  1. Periksa struktur direktori dan composer.json / package.json.
  2. Identifikasi framework, versi PHP, dan package yang terpasang.
  3. Periksa file konfigurasi (`config/`, `.env.example`).
  4. Periksa pola desain yang sudah ada (Form Request, Service, Action Class, Policy, Resource).
  5. Pertahankan konvensi penamaan dan gaya arsitektur yang konsisten dengan repository.

---

## 4. Aturan Kerja Sebelum dan Sesudah Mengubah Kode

### Sebelum Melakukan Perubahan:
1. Baca file `README.md`, `PRD.md`, dan dokumentasi yang relevan.
2. Periksa struktur direktori yang berhubungan dengan task.
3. Telusuri Model, Controller, Service/Action, Route, Migration, Request Validation, Policy, dan Test terkait.
4. Jangan menghapus, memodifikasi, atau memformat ulang kode di luar konteks pekerjaan.
5. Buat rencana singkat (step-by-step) sebelum mengeksekusi fitur atau perubahan kompleks.
6. Jika requirement ambigu atau berpotensi mempengaruhi integritas data keuangan, **wajib minta klarifikasi** terlebih dahulu.
7. Jika masalah dapat diselesaikan dengan perubahan kecil yang terisolasi, **hindari refactor besar-besaran**.

### Setelah Melakukan Perubahan:
1. Jalankan code formatter dan linter (misal: Laravel Pint, PHP-CS-Fixer, Prettier).
2. Jalankan static analysis jika tersedia (PHPStan / Larastan).
3. Jalankan unit test dan feature test yang relevan (`php artisan test` atau `pest`).
4. Periksa file migration baru untuk memastikan indeks, foreign key, dan tipe kolom sudah tepat.
5. Periksa efisiensi query database (cegah N+1 query issue dengan eager loading).
6. Buat ringkasan file yang diubah beserta penjelasannya kepada pengguna.
7. Laporkan hasil pengujian (test yang berhasil maupun yang gagal jika ada).

---

## 5. Scope MVP vs Fitur Terlarang

### Fitur Scope MVP (Boleh Dikembangkan):
- Autentikasi: Login, logout, update profil, reset password.
- Role & Permission: Pembatasan akses berbasis peran.
- Dashboard: Metrik keuangan, saldo berjalan, status pengajuan, grafik ringkasan.
- Master Data: Manajemen pengguna, divisi, kategori transaksi, dan metode pembayaran.
- Pengajuan Biaya: Form reimbursement dan form permintaan dana beserta lampiran bukti.
- Approval Bertingkat: Mekanisme setujui, tolak (dengan alasan), dan revisi pengajuan.
- Transaksi: Pencatatan pemasukan dan pengeluaran manual oleh Finance.
- Anggaran (Budget): Alokasi per divisi/kategori, pelacakan sisa dana, dan peringatan batas (75%, 90%, 100%).
- Data Vendor: Master vendor untuk transaksi pengeluaran.
- Manajemen File: Upload, preview, dan pengunduhan bukti transaksi privat.
- Laporan & Export: Filter laporan periode/divisi/kategori, export ke format Excel dan PDF.
- Notifikasi: Notifikasi status pengajuan di dalam aplikasi (in-app notifications).
- Audit Log: Pencatatan mutasi data sensitif, status approval, dan auth activity.

### Fitur di Luar Scope (Dilarang Tanpa Persetujuan Khusus):
- Payroll / penggajian karyawan otomatis.
- Integrasi payment gateway / transfer bank otomatis.
- Perhitungan pajak otomatis (PPh 21, PPh 23, PPN otomatis).
- Double-entry accounting system lengkap (jurnal umum, buku besar, neraca saldo kompleks).
- OCR (Optical Character Recognition) pembaca nota otomatis.
- Prediksi atau forecasting arus kas berbasis AI.
- Integrasi WhatsApp Gateway / SMS Gateway.
- Aplikasi mobile native (iOS / Android).
- Integrasi pihak ketiga di luar file export standar.
- **Penghapusan permanen (Hard Delete)** pada data transaksi atau pengajuan keuangan yang telah diproses.

---

## 6. Role Pengguna & Matriks Hak Akses

Terdapat 5 peran pengguna utama:
1. `employee` (Karyawan)
2. `manager` (Manager Divisi)
3. `finance` (Staf / Tim Keuangan)
4. `director` (Direksi / Pimpinan Perusahaan)
5. `admin` (Administrator Sistem)

### Batasan Hak Akses:
- **`employee`:**
  - Hanya dapat membuat pengajuan (reimbursement & permintaan dana) untuk dirinya sendiri.
  - Hanya dapat melihat data pengajuan miliknya sendiri.
  - Mengunggah bukti transaksi untuk pengajuan pribadi.
- **`manager`:**
  - Memiliki seluruh hak akses `employee`.
  - Meninjau, menyetujui, menolak, atau mengembalikan pengajuan dari anggota di divisi naungannya.
  - Melihat laporan dan realisasi anggaran divisi miliknya.
- **`finance`:**
  - Melakukan verifikasi akhir terhadap pengajuan yang telah disetujui Manager.
  - Mengelola pencatatan pemasukan dan pengeluaran kantor.
  - Mengelola master anggaran, kategori transaksi, dan vendor.
  - Memproses status pembayaran (`Dibayar` / `Selesai`).
  - Melihat seluruh laporan keuangan dan mengekspor dokumen.
- **`director`:**
  - Melihat dashboard ringkasan keuangan eksekutif dan seluruh laporan.
  - Menyetujui pengajuan bernilai tinggi (melebihi ambang batas approval nominal, misal > Rp5.000.000).
- **`admin`:**
  - Mengelola akun pengguna, penetapan role, struktur divisi, dan konfigurasi sistem.
  - Melihat audit log aktivitas sistem.
  - Tidak diperbolehkan menyetujui transaksi keuangan operasional kecuali ditentukan lain secara eksplisit.

### Prinsip Keamanan Role:
- **Zero-Trust Frontend:** Jangan pernah mempercayai pembatasan visual di frontend.
- **Backend Enforcement:** Seluruh otorisasi aksi wajib diuji di server menggunakan Laravel Policy, Gate, atau Form Request authorize method.

---

## 7. Aturan Keamanan dan Otorisasi

Semua fitur yang berinteraksi dengan data keuangan, bukti transaksi, dan identitas pengguna wajib memenuhi standar keamanan ketat:

1. **Otorisasi Server-Side:**
   - Gunakan Policy / Gate pada setiap aksi Controller / Livewire component.
   - Periksa kepemilikan data: pastikan Employee tidak dapat mengakses ID pengajuan milik orang lain via manipulasi URL/parameter (Insecure Direct Object Reference / IDOR).

2. **Validasi Input & Sanitasi:**
   - Validasi seluruh input di backend menggunakan Form Request.
   - Sanitasi teks dan escape output untuk mencegah serangan Cross-Site Scripting (XSS).
   - Gunakan Query Builder / Eloquent ORM dengan parameter binding untuk mencegah SQL Injection.

3. **Keamanan Kredensial & Autentikasi:**
   - Password wajib di-hash menggunakan algoritma aman (`bcrypt` atau `argon2id`).
   - Terapkan rate limiting (`throttle` middleware) pada endpoint login, forgot password, dan approval.
   - Sesi pengguna wajib memiliki konfigurasi timeout yang aman.

4. **Proteksi Informasi Sensitif:**
   - Nomor rekening vendor dan karyawan hanya boleh dilihat oleh role yang berwenang (`finance`, `admin`, dan pemilik rekening terkait).
   - Jangan pernah menyalin API key, password, token JWT, atau kredensial rahasia ke file source code, log aplikasi, maupun respons chat AI.
   - Seluruh kredensial wajib dimuat melalui environment variable (`.env`).

5. **Keamanan File Bukti Transaksi:**
   - File bukti (nota, struk, invoice) wajib disimpan pada private disk (`storage/app/private`).
   - **Dilarang keras** menempatkan file bukti di direktori `public/` atau membuat public storage symlink yang dapat diakses sembarang orang tanpa login.
   - Akses file harus melalui route controller terproteksi yang memvalidasi otorisasi pengguna (`abort_if(!auth()->user()->can('viewAttachment', $attachment), 403)`), atau menggunakan temporary signed URL berdurasi singkat.
   - Validasi upload: batasi tipe file (PDF, JPG, PNG), batas ukuran file maksimum (maksimal 5 MB), dan sanitasi nama file saat disimpan.

---

## 8. Aturan Integritas Data Keuangan

Perhitungan nominal uang menuntut akurasi mutlak:

1. **Tipe Data Database:**
   - **Wajib:** Gunakan tipe data `DECIMAL(18, 2)` atau `DECIMAL(15, 2)` pada skema database migration untuk seluruh nominal uang, saldo, dan anggaran.
   - **Alternatif:** Gunakan integer dalam satuan terkecil jika arsitektur project memilih representasi sen/integer.
   - **DILARANG KERAS:** Jangan pernah menggunakan tipe data `FLOAT` atau `DOUBLE` untuk nominal keuangan karena risiko ketidakakuratan floating-point binary representation.

   ```php
   // Contoh yang benar dalam migration:
   $table->decimal('amount', 18, 2);
   $table->decimal('total_amount', 18, 2);
   $table->decimal('budget_limit', 18, 2);
   ```

2. **Perhitungan dalam Kode Aplikasi:**
   - Gunakan fungsi presisi tinggi seperti ekstensi PHP `bcmath` (`bcadd`, `bcsub`, `bcmul`, `bcdiv`, `bccomp`) atau package moneter teruji untuk operasi aritmatika nominal uang.
   - Hindari operasi floating-point native PHP seperti `$a + $b` saat menjumlahkan nilai desimal penting.

3. **Validasi Nilai Nominal:**
   - Seluruh nilai nominal pengajuan, transaksi, dan anggaran wajib bernilai positif (`min:0.01` atau `gt:0`).
   - Tolak nominal negatif atau nol pada pengajuan biaya.

4. **Database Transactions (ACID):**
   - Setiap operasi yang melibatkan mutasi majemuk wajib dibungkus dalam `DB::transaction()`.
   - Contoh: Pembuatan transaksi pengeluaran yang sekaligus memotong saldo anggaran, mengubah status pengajuan, dan mencatat audit log wajib berada dalam satu transaksi atomik. Jika salah satu langkah gagal, lakukan rollback menyeluruh.

5. **Integritas Penghapusan (No Hard Delete):**
   - Transaksi keuangan, reimbursement yang disetujui, dan pengeluaran yang telah dibayar **dilarang dihapus secara permanen (hard delete)**.
   - Terapkan `SoftDeletes` dan gunakan alur pembatalan status (`status = 'cancelled'`) dengan kolom `cancellation_reason` wajib dan pencatatan audit log.

6. **Pencegahan Transaksi Duplikat (Idempotency):**
   - Sistem wajib melakukan validasi terhadap potensi duplikasi pengajuan: kombinasi pemohon yang sama, tanggal yang sama, nominal yang sama, atau nomor invoice/nota yang identik.

---

## 9. Aturan Alur Approval & Anti-Self-Approval

1. **Mekanisme Anti-Self-Approval:**
   - Seorang pengguna **tidak boleh menyetujui pengajuannya sendiri**, meskipun pengguna tersebut memiliki role Manager, Finance, atau Direktur.
   - Sistem wajib menolak dan mengalihkan approval pengajuan milik pejabat ke atasan atau pejabat setara lainnya.

2. **Tahapan Alur Pengajuan (State Machine):**
   - `Draft`: Pengajuan dapat diedit atau dihapus oleh pembuat.
   - `Diajukan` / `Menunggu Approval Manager`: Pengajuan terkunci dari pengeditan pemohon, menunggu persetujuan atasan langsung / manager divisi.
   - `Menunggu Verifikasi Finance`: Setelah disetujui Manager, Finance memeriksa kesesuaian nota, bukti transaksi, dan pos anggaran.
   - `Menunggu Persetujuan Direktur`: Jika nominal pengajuan melebihi ambang batas nominal tertentu (misal: > Rp5.000.000).
   - `Disetujui`: Seluruh tahapan persetujuan terpenuhi, siap dibayarkan.
   - `Dibayar` / `Selesai`: Finance telah mentransfer dana dan mengonfirmasi bukti bayar.
   - `Ditolak`: Pengajuan ditolak dengan alasan penolakan wajib (`rejection_reason`).
   - `Perlu Perbaikan`: Pengajuan dikembalikan ke pemohon untuk revisi dokumen/data.

3. **Integritas Riwayat Keputusan:**
   - Setiap perubahan status approval wajib mencatat identitas approver, level persetujuan, keputusan, catatan/alasan, dan timestamp persetujuan.

---

## 10. Standar Auditability & Logging

Aplikasi harus memiliki kemampuan pelacakan lengkap untuk keperluan audit internal:
- **Event yang Wajib Dicatat:**
  - Login berhasil, login gagal, dan logout.
  - Pembuatan, pembaruan, dan pembatalan transaksi keuangan.
  - Perubahan status pengajuan (Submit, Approve, Reject, Revise, Pay, Cancel).
  - Upload dan penghapusan lampiran dokumen.
  - Modifikasi master data (pengguna, role, divisi, anggaran).
- **Data Audit Log:**
  - `user_id`: ID pengguna yang melakukan aksi.
  - `action`: Jenis aksi (`created`, `updated`, `status_changed`, `cancelled`, dll.).
  - `model_type` & `model_id`: Entitas yang terpengaruh.
  - `old_values`: Nilai sebelum diubah (JSON).
  - `new_values`: Nilai setelah diubah (JSON).
  - `ip_address` & `user_agent`: Informasi jaringan dan perangkat pengguna.
  - `created_at`: Waktu kejadian.
- **Karakteristik Audit Log:** Bersifat *Append-Only* (immutable). Tidak boleh ada fitur edit atau delete untuk tabel audit log.

---

# Anti-AI-Slop dan Prinsip DRY

## Tujuan

Kode, dokumentasi, dan respons AI harus spesifik terhadap project ini. Jangan menghasilkan solusi generik, berlebihan, atau tidak diperlukan.

## Aturan Anti-AI-Slop

1. Jangan membuat fitur yang tidak diminta.
2. Jangan membuat abstraksi, service, helper, interface, atau konfigurasi hanya untuk terlihat rapi.
3. Jangan menambahkan komentar yang hanya mengulang isi kode.
4. Jangan membuat TODO tanpa konteks dan alasan yang jelas.
5. Jangan menggunakan placeholder atau implementasi palsu seolah-olah sudah selesai.
6. Jangan mengarang API, tabel, field, endpoint, library, atau requirement.
7. Jangan menambahkan dependency jika masalah dapat diselesaikan dengan kode yang sudah ada.
8. Jangan melakukan refactor besar untuk tugas kecil.
9. Jangan membuat validasi, formatter, query, atau business rule yang duplikat.
10. Jangan menyalin blok kode dengan perubahan kecil jika dapat dibuat reusable secara wajar.
11. Jangan membuat file baru jika fungsi yang sama sudah tersedia.
12. Jangan menambahkan konfigurasi yang belum memiliki kebutuhan nyata.
13. Jangan menambahkan komentar seperti `// do something`, `// handle logic`, atau komentar generik lainnya.
14. Jangan menampilkan penjelasan panjang jika ringkasan singkat sudah cukup.
15. Jangan menyatakan fitur selesai jika belum diuji.

## Prinsip DRY

Sebelum membuat kode baru:

1. Cari implementasi yang serupa.
2. Cari service, helper, component, policy, query, atau validator yang sudah ada.
3. Gunakan kembali komponen yang sesuai.
4. Jika business rule dipakai lebih dari satu tempat, pindahkan ke satu sumber terpusat.
5. Jika format atau konstanta dipakai berulang, buat value object, enum, config, atau formatter.
6. Jangan membuat salinan aturan yang sama pada frontend dan backend dengan perilaku berbeda.
7. Backend tetap menjadi sumber kebenaran untuk aturan finansial.
8. Dokumentasi harus merujuk ke sumber utama, bukan menyalin isi dokumen lain.

## Batas Abstraksi

Jangan membuat abstraksi hanya karena dua bagian kode terlihat mirip.

Gunakan abstraksi jika minimal salah satu kondisi berikut terpenuhi:

- Logic tersebut merupakan business rule penting.
- Logic digunakan di tiga tempat atau lebih.
- Logic membutuhkan pengujian tersendiri.
- Duplikasi dapat menyebabkan hasil finansial berbeda.
- Abstraksi membuat tanggung jawab lebih jelas.

Jika hanya digunakan satu kali dan belum memiliki boundary yang jelas, prioritaskan kode yang sederhana dan lokal.

## Kapan Harus Bertanya

Minta klarifikasi jika:

- Requirement memengaruhi saldo atau anggaran.
- Requirement mengubah alur approval.
- Requirement dapat menghapus atau mengubah data historis.
- Terdapat lebih dari satu interpretasi yang masuk akal.
- Solusi membutuhkan dependency atau perubahan arsitektur besar.
- Data atau API yang dibutuhkan tidak tersedia.

## Kualitas Respons

Respons AI harus:

- Spesifik terhadap repository.
- Menyebutkan asumsi yang digunakan.
- Menyebutkan file yang benar-benar diubah.
- Tidak mengklaim test berhasil jika belum dijalankan.
- Tidak mengulang isi dokumentasi yang sudah tersedia.
- Menjelaskan risiko hanya jika relevan.
- Menggunakan format ringkas dan faktual.

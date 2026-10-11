# SKILL.md

# Finance Office — AI Development Skills

## 1. Tujuan File

File ini mendefinisikan skill atau kemampuan yang harus digunakan AI Agent saat mengembangkan aplikasi Finance Office.

AI Agent harus:

- Memilih skill yang sesuai dengan tugas.
- Mengikuti workflow yang telah ditentukan.
- Tidak melewati validasi keamanan.
- Tidak mengubah data keuangan secara destruktif.
- Menghasilkan kode yang konsisten dengan `GEMINI.md`, `DESIGN.md`, dan `PRD.md`.

Dokumen ini melengkapi:

- `GEMINI.md` / `AGENTS.md`: aturan kerja dan batasan AI.
- `DESIGN.md`: rancangan teknis aplikasi.
- `PRD.md`: kebutuhan produk dan fitur.

Jika terdapat konflik:

1. Keamanan dan integritas data menjadi prioritas utama.
2. Aturan pada `GEMINI.md` / `AGENTS.md` harus diikuti.
3. Desain pada `DESIGN.md` menjadi acuan teknis.
4. Requirement pada `PRD.md` menjadi acuan produk.
5. Jika masih ambigu, minta klarifikasi sebelum mengubah data atau arsitektur.

---

## 2. Aturan Umum Penggunaan Skill

Sebelum menjalankan skill:

1. Pahami permintaan pengguna.
2. Identifikasi modul yang terdampak.
3. Periksa struktur repository.
4. Baca file yang berkaitan.
5. Tentukan skill yang relevan.
6. Buat rencana implementasi singkat.
7. Periksa dampak terhadap database, permission, audit log, dan laporan.

Setelah menjalankan skill:

1. Jalankan formatter.
2. Jalankan test yang relevan.
3. Periksa authorization.
4. Periksa validasi backend.
5. Periksa audit log.
6. Periksa dampak terhadap saldo dan anggaran.
7. Laporkan file yang diubah.
8. Laporkan test yang berhasil atau gagal.
9. Laporkan pekerjaan yang belum selesai.

Jangan menyatakan tugas selesai jika:

- Test penting gagal.
- Authorization belum diterapkan.
- Perhitungan nominal belum diuji.
- Migration belum dibuat.
- Ada perubahan destruktif tanpa persetujuan.
- Ada requirement penting yang belum dipenuhi.

---

## 3. Skill Execution Order

Untuk pekerjaan kompleks, gunakan urutan berikut:

```text
Understand
    ↓
Inspect
    ↓
Plan
    ↓
Design
    ↓
Implement
    ↓
Validate
    ↓
Test
    ↓
Review
    ↓
Report
```

1. **Understand:** Menguraikan kebutuhan user dan batas scope MVP.
2. **Inspect:** Meneliti file model, controller, service, route, dan migration eksisting.
3. **Plan:** Menyusun rencana langkah demi langkah yang atomic dan minim risiko.
4. **Design:** Mengacu pada arsitektur modular monolith di `DESIGN.md`.
5. **Implement:** Menulis kode yang modular, menerapkan SOLID, dan mematuhi DRY.
6. **Validate:** Memastikan validasi input backend, otorisasi policy, dan constraint database.
7. **Test:** Menjalankan unit dan feature test otomatis.
8. **Review:** Memeriksa kembali dari segi keamanan finansial, N+1 query, dan audit log.
9. **Report:** Menyampaikan ringkasan perubahan secara faktual dan transparan.

---

## 4. Katalog Skill AI Development

### Skill 01: Codebase & Architecture Inspection
- **Tujuan:** Mengetahui kondisi repository sebelum melakukan modifikasi apapun.
- **Workflow:**
  1. Periksa `composer.json` atau `package.json` untuk mengetahui framework dan dependensi terpasang.
  2. Periksa skema database melalui file migrations di `database/migrations/`.
  3. Periksa service classes, form requests, policies, dan routes yang sudah ada.
  4. Identifikasi pola penamaan (naming conventions) agar kode baru konsisten.

### Skill 02: Authentication & Authorization Enforcement
- **Tujuan:** Menjamin otorisasi berlapis dan mengeliminasi celah IDOR atau eskalasi role.
- **Workflow:**
  1. Gunakan Laravel Policy (`ReimbursementPolicy`, `TransactionPolicy`, dll.) untuk setiap aksi sensitif.
  2. Lindungi route menggunakan auth middleware dan role/permission checks.
  3. Verifikasi pembatasan kepemilikan data:
     - `employee`: hanya data miliknya sendiri.
     - `manager`: data anggotanya di divisi terkait.
     - `finance` & `director`: data keuangan korporat sesuai batas kewenangan.
     - `admin`: konfigurasi dan user management (tanpa persetujuan transaksi langsung).
  4. Terapkan prinsip **Zero-Trust Frontend**: jangan mempercayai parameter input tanpa validasi backend.

### Skill 03: Precision Financial Arithmetic
- **Tujuan:** Mencegah floating-point inaccuracy dan menjamin akurasi nominal Rupiah.
- **Workflow:**
  1. Wajib menggunakan tipe data `DECIMAL(18, 2)` pada migration database untuk seluruh nominal dan saldo.
  2. Gunakan fungsi `bcmath` (`bcadd`, `bcsub`, `bcmul`, `bcdiv`, `bccomp`) atau integer cents untuk kalkulasi di PHP.
  3. Dilarang keras menggunakan tipe data `FLOAT` atau `DOUBLE` untuk nominal moneter.
  4. Validasi input nominal wajib bernilai positif (`min:0.01` atau `gt:0`).
  5. Format tampilan antarmuka selalu dalam standar Rupiah: `Rp 1.500.000` atau `Rp 1.500.000,00`.

### Skill 04: Transaction & Cash Flow Processing
- **Tujuan:** Mengelola pencatatan pemasukan dan pengeluaran secara konsisten dan aman.
- **Workflow:**
  1. Pisahkan transaksi menjadi `income` dan `expense`.
  2. Selalu gunakan `DB::transaction()` untuk operasi yang memodifikasi saldo, relasi reimbursement, pemotongan anggaran, dan audit log.
  3. Validasi duplikasi transaksi (kombinasi pemohon, tanggal, nominal, dan nomor invoice/nota identik).
  4. Terapkan status pembayaran: `unpaid`, `scheduled`, `paid`, `cancelled`.
  5. **Dilarang keras Hard Delete**: transaksi dibatalkan dengan status `cancelled`, wajib mencatat `cancellation_reason` dan audit log.

### Skill 05: Reimbursement & Multi-Item Workflow
- **Tujuan:** Mengelola pengajuan reimbursement multi-item dari pembuatan draft hingga pembayaran.
- **Workflow:**
  1. Validasi relasi `Reimbursement` dengan banyak `ReimbursementItem`.
  2. Hitung total reimbursement di backend dengan formula:
     $$\text{Total} = \sum (\text{ReimbursementItem.amount})$$
  3. Cegah perubahan data jika status bukan `draft` atau `returned_for_revision`.
  4. Wajibkan kelengkapan bukti transaksi (`attachments`) sebelum submit.
  5. Generate nomor pengajuan otomatis yang unik (contoh: `RMB-YYYYMM-XXXX`).

### Skill 06: Approval State Machine & Anti-Self-Approval
- **Tujuan:** Menjaga kejelasan alur persetujuan dan integritas tata kelola perusahaan.
- **Workflow:**
  1. Terapkan state machine resmi:
     `draft` $\rightarrow$ `waiting_manager_approval` $\rightarrow$ `waiting_finance_verification` $\rightarrow$ `waiting_director_approval` (jika melewati threshold) $\rightarrow$ `approved` $\rightarrow$ `paid` $\rightarrow$ `completed`.
  2. **Anti-Self-Approval:** Sistem wajib menolak jika approver adalah pembuat pengajuan (`requester_id === auth()->id()`).
  3. Penolakan wajib menyertakan alasan penolakan (`rejection_reason`).
  4. Pengembalian revisi wajib menyertakan catatan perbaikan.
  5. Setiap perubahan status dicatat dalam tabel `approvals` dan `audit_logs` dengan timestamp serta ID approver.

### Skill 07: Budget Monitoring & Alert Enforcement
- **Tujuan:** Mengontrol alokasi anggaran divisi/kategori dan mencegah pengeluaran tak terkendali.
- **Workflow:**
  1. Hitung sisa anggaran di backend:
     $$\text{Sisa Anggaran} = \text{Anggaran Dialokasikan} - \text{Anggaran Terpakai}$$
  2. Hanya perbarui `used_amount` berdasarkan transaksi berstatus aktif yang sudah disetujui/dibayar.
  3. Picu peringatan ambang batas:
     - $\ge 75\%$: peringatan awal (*normal warning*).
     - $\ge 90\%$: peringatan tinggi (*high warning*).
     - $\ge 100\%$: peringatan anggaran habis (*budget exhausted*).
  4. Blokir atau alihkan ke approval khusus jika pengajuan melebihi sisa anggaran.

### Skill 08: Secure Private File Storage
- **Tujuan:** Melindungi dokumen nota, bukti transfer, dan invoice dari akses publik tanpa otorisasi.
- **Workflow:**
  1. Simpan seluruh lampiran pada disk privat: `storage/app/private/` (dilarang di `public/` atau melalui symlink publik).
  2. Simpan file dengan nama acak: `{uuid}.{extension}` dan batasi ekstensi (PDF, JPG, PNG) serta ukuran maksimum (5 MB).
  3. Akses file bukti hanya melalui controller terproteksi yang memvalidasi policy otorisasi pengguna (`can('viewAttachment', ...)`).
  4. Gunakan streaming response atau temporary signed URL berdurasi singkat.

### Skill 09: Financial Reporting & Data Export
- **Tujuan:** Menyediakan laporan keuangan yang akurat tanpa membebani performa server.
- **Workflow:**
  1. Terapkan filter ketat: periode tanggal, divisi, kategori, vendor, status.
  2. Cegah masalah query N+1 dengan eager loading (`with(['department', 'category', 'user'])`).
  3. Jangan memuat seluruh data ke memory; gunakan pagination dan aggregate queries database (`SUM`, `COUNT`).
  4. Untuk ekspor besar (Excel/PDF), gunakan antrean background job (Queue) dan simpan hasilnya di private storage sebelum diunduh.

### Skill 10: Immutable Audit Logging
- **Tujuan:** Menyediakan jejak rekam kronologis yang tidak dapat dimanipulasi untuk kebutuhan audit.
- **Workflow:**
  1. Catat event penting: login, logout, create/update transaksi, perubahan status approval, pembatalan, pembayaran, dan modifikasi master data.
  2. Simpan data audit: `user_id`, `action`, `module`, `auditable_type`, `auditable_id`, `old_values` (JSON), `new_values` (JSON), `ip_address`, `user_agent`, `created_at`.
  3. Audit log bersifat **Append-Only**: dilarang membuat endpoint atau fungsi untuk mengedit atau menghapus log audit.

### Skill 11: Testing & Regression Protection
- **Tujuan:** Memastikan kebenaran fungsional dan mencegah regresi pada kalkulasi keuangan.
- **Workflow:**
  1. **Unit Test:** Kalkulasi total reimbursement, sisa anggaran, format Rupiah, dan transisi state machine.
  2. **Feature Test:** Alur lengkap pengajuan reimbursement, submit, approval, pembayaran, hingga pencatatan transaksi.
  3. **Authorization Test:** Uji coba skenario terlarang (employee mengakses data orang lain, self-approval oleh manager, akses URL dokumen tanpa login).
  4. **Database Rollback:** Uji coba kegagalan proses di tengah transaksi untuk membuktikan rollback database berjalan sempurna.

---

## 5. Protokol Klarifikasi & Pencegahan Masalah

AI Agent **wajib berhenti dan meminta klarifikasi pengguna** jika:
1. Requirement meminta perubahan pada formula saldo, anggaran, atau mutasi finansial historis.
2. Requirement mengusulkan penghapusan permanen (hard delete) pada data transaksi atau reimbursement.
3. Terdapat ambiguitas pada batasan nominal approval atau alur persetujuan divisi.
4. Muncul kebutuhan package eksternal atau perubahan arsitektur dasar database.
5. Ditemukan potensi celah keamanan pada file storage atau data rekening bank.

---

## 6. Standar Output & Pelaporan AI

Setiap kali menyelesaikan pengerjaan tugas, laporkan kepada pengguna dengan format ringkas:
- **File yang Diubah/Dibuat:** Daftar file dengan tautan yang jelas.
- **Business Rule yang Diterapkan:** Validasi dan aturan keuangan yang aktif.
- **Hasil Pengujian:** Status pengujian unit/feature test (berhasil/gagal).
- **Asumsi / Keterbatasan:** Jika ada batasan yang perlu diketahui pengguna.
- **Langkah Berikutnya:** Saran implementasi tahap selanjutnya sesuai roadmap MVP.

---

# Anti-Slop Execution Rules

## Sebelum Membuat Kode

AI Agent wajib mencari:

- Implementasi serupa.
- Service yang sudah ada.
- Component yang sudah ada.
- Policy atau permission yang relevan.
- Query atau scope yang dapat digunakan kembali.
- Test yang sudah tersedia.

## Saat Membuat Kode

- Pilih solusi paling sederhana yang memenuhi requirement.
- Jangan membuat layer baru tanpa kebutuhan nyata.
- Jangan mengubah file yang tidak berkaitan.
- Jangan menyalin business rule ke banyak tempat.
- Jangan membuat versi kedua dari helper atau component yang sudah ada.
- Gunakan enum, config, atau service yang sudah tersedia.
- Jika membuat utility baru, pastikan tidak ada utility serupa.

## Saat Membuat Dokumentasi

- Jangan menyalin isi `PRD.md` ke `DESIGN.md`.
- Jangan menyalin seluruh `DESIGN.md` ke `SKILL.md`.
- Gunakan referensi ke file sumber.
- Dokumentasikan hanya informasi yang belum tersedia di dokumen lain.
- Hapus bagian yang tidak lagi relevan.
- Hindari paragraf normatif yang tidak menghasilkan keputusan atau tindakan.

## Saat Menyelesaikan Tugas

Laporan akhir harus singkat dan berisi:

1. Apa yang berubah.
2. File yang berubah.
3. Test yang dijalankan.
4. Masalah atau pekerjaan yang belum selesai.

Jangan menyertakan penjelasan panjang yang tidak membantu review.


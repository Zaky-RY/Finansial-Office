# PANDUAN PENGUJIAN PENERIMAAN PENGGUNA (UAT PLAN)
## Finance Office MVP 1.0

---

## 1. Tujuan UAT (User Acceptance Testing)

Tujuan dari dokumen panduan UAT ini adalah:
1. Memvalidasi bahwa seluruh fitur operasional dan fungsional pada aplikasi **Finance Office MVP 1.0** telah bekerja sesuai dengan spesifikasi kebutuhan pada `PRD.md` dan standar teknis pada `GEMINI.md`.
2. Menguji alur bisnis ujung-ke-ujung (*end-to-end*): mulai dari pembuatan klaim reimbursement karyawan, persetujuan bertingkat, verifikasi dan pembayaran oleh tim Finance, pencatatan transaksi buku kas, pemantauan batas anggaran divisi, hingga pelaporan dan ekspor dokumen.
3. Memastikan ketepatan kalkulasi finansial mutlak tanpa kesalahan pembulatan (*anti floating-point error*) menggunakan standar mata uang Rupiah (`IDR`).
4. Memverifikasi penegakan otorisasi server-side (*Zero-Trust*), pencegahan konflik kepentingan (*Anti-Self-Approval*), dan isolasi berkas lampiran transaksi privat.
5. Menjadi acuan formal bagi pemangku kepentingan (*stakeholders*) untuk menyetujui rilis aplikasi ke lingkungan produksi.

---

## 2. Environment Pengujian

Pengujian UAT dilaksanakan pada lingkungan Staging atau Local Testbed terisolasi:

- **Target URL:** `http://127.0.0.1:8000` (Local Staging Testbed) / `https://staging-finance.perusahaan.co.id`
- **Framework & Versi:** Laravel 10.50.3 / PHP 8.1+
- **Database Engine:** MySQL 8.0+ / SQLite 3 (Driver ACID Compliant)
- **Zona Waktu Aplikasi:** `Asia/Jakarta` (WIB / UTC+7)
- **Mata Uang Standar:** Rupiah Indonesia (IDR) — Format: `Rp X.XXX.XXX,XX`
- **Penyimpanan Berkas:** Private local storage disk (`storage/app/reimbursements` & `storage/app/transactions`)
- **Peramban yang Didukung:** Google Chrome, Mozilla Firefox, Microsoft Edge, Safari (versi desktop dan mobile view)

---

## 3. Role Tester & Profil Pengguna

Terdapat 5 peran pengguna penguji (*UAT Personas*):

| Role | Akun Penguji | Divisi | Tanggung Jawab & Hak Akses |
|---|---|---|---|
| **`employee`** | `employee@office.test` | Technology | Mengajukan reimbursement pribadi, mengisi rincian multi-item belanja, mengunggah bukti nota, mengedit draf revisi. |
| **`manager`** | `manager@office.test` | Technology | Memeriksa dan menyetujui/menolak pengajuan anggota divisi Technology, memantau sisa anggaran divisi. |
| **`finance`** | `finance@office.test` | Finance & Accounting | Melakukan verifikasi keuangan, memproses pembayaran kas, mencatat transaksi kas operasional, mengelola pagu anggaran, mengunduh seluruh laporan keuangan. |
| **`director`** | `director@office.test` | Management | Memeriksa dashboard ringkasan eksekutif, memberikan persetujuan akhir pada pengajuan nominal tinggi (> Rp5.000.000,00). |
| **`admin`** | `admin@office.test` | IT | Mengelola pengguna dan master divisi/kategori, menginspeksi catatan riwayat aktivitas (*audit logs*). |
| **`inactive`** | `inactive@office.test` | Technology | Akun dinonaktifkan khusus untuk menguji penolakan otentikasi login. |

> **Kata Sandi Default Semua Akun Pengujian:** `password123`

---

## 4. Data Awal yang Diperlukan (Pre-requisites)

Sebelum memulai pengujian, pastikan basis data telah dimigrasi dan diisi dengan data master dasar (`php artisan migrate --seed`):

1. **Master Divisi (Departemen):**
   - Technology (`TECH`)
   - Finance & Accounting (`FIN`)
   - Marketing (`MKT`)
   - Human Resources (`HRD`)
   - General Affairs (`GA`)
2. **Master Kategori Biaya & Pemasukan:**
   - *Expense:* Transport & Perjalanan (`TRP`), Akomodasi & Hotel (`AKM`), Konsumsi & Jamuan Klien (`KNS`), ATK & Operasional Kantor (`ATK`), Software & Lisensi (`SFT`).
   - *Income:* Pendapatan Invoice Jasa (`INV`), Penerimaan Kas Operasional (`KAS`).
3. **Berkas Uji Coba Lampiran:**
   - Berkas nota/kuitansi dummy berformat `.pdf`, `.jpg`, atau `.png` dengan ukuran di bawah 5 MB.
   - Berkas dummy berformat tidak didukung (misal `.zip` atau `.exe`) dan berkas berukuran &gt; 5 MB untuk pengujian validasi negatif.

---

## 5. Klasifikasi Tingkat Keparahan (Defect Severity)

Bila terdapat langkah pengujian yang menghasilkan status **FAIL**, tetapkan tingkat keparahan defect:

- **Critical (P1):** Kerusakan sistem mendasar, kegagalan kalkulasi finansial, celah keamanan otorisasi/IDOR, data corrupt, atau aplikasi crash. Rilis ditunda mutlak.
- **Major (P2):** Fitur utama tidak berjalan sesuai acceptance criteria, alur persetujuan terputus, atau validasi penting tidak berfungsi tanpa solusi sementara (*workaround*).
- **Minor (P3):** Fitur berjalan namun terdapat inkonsistensi label, kendala tata letak responsif minor, atau pesan error kurang informatif.

---

## 6. Skenario Pengujian UAT Terstruktur

### MODUL 1: AUTENTIKASI & SESI PENGGUNA (AUTH)

#### UAT-AUTH-01: Login Pengguna Aktif Berhasil
- **Aktor:** `employee@office.test` (atau role lainnya)
- **Langkah:**
  1. Akses halaman `/login`.
  2. Masukkan email `employee@office.test` dan password `password123`.
  3. Klik tombol "Masuk".
- **Expected Result:** Berhasil masuk ke halaman Dashboard, muncul notifikasi sukses, nama dan badge peran `EMPLOYEE` ditampilkan di sidebar.
- **Severity Jika Gagal:** Critical (P1)

#### UAT-AUTH-02: Pencegahan Login Akun Nonaktif
- **Aktor:** `inactive@office.test`
- **Langkah:**
  1. Akses halaman `/login`.
  2. Masukkan email `inactive@office.test` dan password `password123`.
  3. Klik tombol "Masuk".
- **Expected Result:** Login ditolak di backend. Muncul pesan error informatif: "Akun Anda dinonaktifkan. Silakan hubungi Administrator."
- **Severity Jika Gagal:** Critical (P1)

#### UAT-AUTH-03: Rate Limiting Percobaan Login
- **Aktor:** Pengguna Umum
- **Langkah:**
  1. Masukkan password salah sebanyak 6 kali berturut-turut pada form login.
- **Expected Result:** Sistem memblokir sementara percobaan login dengan pesan pembatasan laju (*Too many requests* / throttle).
- **Severity Jika Gagal:** Major (P2)

#### UAT-AUTH-04: Logout Sesi Pengguna
- **Aktor:** Seluruh Role
- **Langkah:**
  1. Klik tombol "Keluar" pada bagian bawah sidebar atau topbar.
  2. Coba klik tombol "Back" pada peramban web.
- **Expected Result:** Sesi autentikasi dihapus sepenuhnya, dialihkan ke `/login`. Menekan tombol back tidak membuka kembali data privat aplikasi.
- **Severity Jika Gagal:** Major (P2)

---

### MODUL 2: PENGAJUAN REIMBURSEMENT (REIMB)

#### UAT-REIMB-01: Pembuatan Draf Reimbursement Multi-Item
- **Aktor:** `employee@office.test`
- **Langkah:**
  1. Buka menu *Reimbursement Saya* &rarr; klik *Buat Pengajuan*.
  2. Isi Tujuan: "Klaim Perjalanan Dinas Meeting Klien Bandung".
  3. Isi Kategori: "Transport & Perjalanan".
  4. Tambah Item 1: Tanggal hari ini, Deskripsi: "Tiket Kereta PP", Nominal: "650000".
  5. Tambah Item 2: Tanggal hari ini, Deskripsi: "Taksi Stasiun - Kantor Klien", Nominal: "150000".
  6. Unggah berkas nota dummy `nota_kereta.pdf` (ukuran < 5 MB).
  7. Klik tombol *Simpan Draf*.
- **Expected Result:** Draf berhasil disimpan, nomor pengajuan unik (`REM-...`) terbit otomatis, status `Draft`, total nominal otomatis terhitung tepat `Rp 800.000,00` dari akumulasi item.
- **Severity Jika Gagal:** Major (P2)

#### UAT-REIMB-02: Pengeditan Draf Sebelum Diajukan
- **Aktor:** `employee@office.test`
- **Langkah:**
  1. Buka pengajuan draf yang baru dibuat &rarr; klik tombol *Edit Draf*.
  2. Ubah nominal Item 2 menjadi "200000".
  3. Simpan perubahan.
- **Expected Result:** Draf diperbarui, total nominal baru terhitung tepat menjadi `Rp 850.000,00`.
- **Severity Jika Gagal:** Major (P2)

#### UAT-REIMB-03: Validasi Wajib Lampiran Bukti Transaksi
- **Aktor:** `employee@office.test`
- **Langkah:**
  1. Buat pengajuan tanpa mengunggah berkas bukti transaksi sama sekali.
  2. Coba klik tombol *Ajukan Sekarang* (*Submit*).
- **Expected Result:** Sistem menolak pengajuan; form menampilkan pesan validasi bahwa bukti transaksi atau nota wajib diunggah.
- **Severity Jika Gagal:** Major (P2)

#### UAT-REIMB-04: Pengiriman (*Submit*) Pengajuan ke Antrean Approval
- **Aktor:** `employee@office.test`
- **Langkah:**
  1. Buka draf yang telah memiliki lampiran bukti transaksi valid &rarr; klik tombol *Ajukan Sekarang*.
- **Expected Result:** Status pengajuan beralih dari `Draft` menjadi `Menunggu Approval Manager`, terkunci dari pengeditan pemohon, riwayat audit mencatat aksi `submitted`.
- **Severity Jika Gagal:** Critical (P1)

---

### MODUL 3: ALUR PERSETUJUAN BERTINGKAT & ANTI-SELF-APPROVAL (APPR)

#### UAT-APPR-01: Persetujuan Tingkat Manager Divisi
- **Aktor:** `manager@office.test`
- **Langkah:**
  1. Login sebagai Manager &rarr; buka menu *Antrean Approval*.
  2. Temukan pengajuan anggota divisi Technology yang berstatus `Menunggu Approval Manager`.
  3. Buka detail pengajuan, periksa rincian item dan nota bukti transaksi.
  4. Masukkan komentar: "Disetujui untuk operasional meeting".
  5. Klik tombol *Setujui*.
- **Expected Result:** Status pengajuan berhasil beralih menjadi `Menunggu Verifikasi Finance`, keputusan approver tercatat pada riwayat persetujuan dengan stempel waktu Asia/Jakarta.
- **Severity Jika Gagal:** Critical (P1)

#### UAT-APPR-02: Penegakan Mutlak Anti-Self-Approval
- **Aktor:** `manager@office.test`
- **Langkah:**
  1. Login sebagai Manager &rarr; buat pengajuan reimbursement pribadi untuk diri sendiri &rarr; kirim pengajuan.
  2. Coba lakukan persetujuan (*approve*) terhadap pengajuan pribadi tersebut.
- **Expected Result:** Sistem menolak aksi persetujuan di tingkat backend (*AuthorizationException* / 403 Forbidden). Tombol persetujuan tidak aktif atau memunculkan peringatan bahwa pemohon dilarang menyetujui pengajuannya sendiri.
- **Severity Jika Gagal:** Critical (P1)

#### UAT-APPR-03: Verifikasi Finance untuk Nominal Standar (&le; Rp5.000.000,00)
- **Aktor:** `finance@office.test`
- **Langkah:**
  1. Login sebagai Finance &rarr; buka menu *Antrean Approval*.
  2. Buka pengajuan berstatus `Menunggu Verifikasi Finance` dengan nominal &le; Rp5 Jt (misal: Rp 850.000,00).
  3. Masukkan catatan: "Nota dan rincian biaya terverifikasi valid".
  4. Klik tombol *Setujui*.
- **Expected Result:** Karena nominal &le; threshold Direktur, status pengajuan langsung beralih menjadi `Disetujui` (*Approved*), siap untuk proses pembayaran kas.
- **Severity Jika Gagal:** Critical (P1)

#### UAT-APPR-04: Verifikasi Finance untuk Nominal Tinggi (&gt; Rp5.000.000,00)
- **Aktor:** `finance@office.test`
- **Langkah:**
  1. Siapkan pengajuan reimbursement dengan total nominal Rp 7.500.000,00 yang telah disetujui Manager.
  2. Login sebagai Finance &rarr; setujui pengajuan tersebut.
- **Expected Result:** Sistem mengevaluasi threshold Direktur; status pengajuan beralih menjadi `Menunggu Persetujuan Direktur`. Muncul kartu peringatan bahwa pengajuan memerlukan persetujuan Direktur Utama.
- **Severity Jika Gagal:** Critical (P1)

#### UAT-APPR-05: Persetujuan Akhir oleh Direktur Eksekutif
- **Aktor:** `director@office.test`
- **Langkah:**
  1. Login sebagai Direktur &rarr; buka menu *Antrean Approval*.
  2. Buka pengajuan nominal tinggi (> Rp5 Jt) yang sedang menunggu persetujuan Direktur.
  3. Berikan catatan persetujuan &rarr; klik *Setujui*.
- **Expected Result:** Status pengajuan beralih menjadi `Disetujui` (*Approved*).
- **Severity Jika Gagal:** Critical (P1)

---

### MODUL 4: PENOLAKAN, REVISI, & PEMBAYARAN KAS (REJ-PAY)

#### UAT-REJ-01: Penolakan Pengajuan dengan Alasan Wajib
- **Aktor:** `manager@office.test` atau `finance@office.test`
- **Langkah:**
  1. Buka pengajuan dalam antrean approval.
  2. Klik tombol *Tolak Pengajuan*.
  3. Coba kirim tanpa mengisi alasan penolakan.
  4. Masukkan alasan valid: "Klaim di luar anggaran operasional yang disetujui".
  5. Kirim penolakan.
- **Expected Result:** Pengiriman tanpa alasan ditolak validasi. Setelah alasan diisi, status pengajuan berubah menjadi `Ditolak`, alasan penolakan tampil jelas di halaman pemohon dan terekam di audit log.
- **Severity Jika Gagal:** Major (P2)

#### UAT-REJ-02: Pengembalian Pengajuan untuk Perbaikan (Revisi)
- **Aktor:** `manager@office.test` atau `finance@office.test`
- **Langkah:**
  1. Buka pengajuan dalam antrean approval &rarr; klik tombol *Kembalikan untuk Revisi*.
  2. Masukkan instruksi: "Mohon lampirkan struk kasir yang memiliki cap basah".
  3. Kirim instruksi perbaikan.
- **Expected Result:** Status beralih ke `Perlu Perbaikan`, draf kembali terbuka untuk diedit pemohon, pemohon menerima notifikasi in-app instruksi revisi.
- **Severity Jika Gagal:** Major (P2)

#### UAT-REJ-03: Pencairan Dana & Pembayaran Kas (*Mark as Paid*)
- **Aktor:** `finance@office.test`
- **Langkah:**
  1. Buka pengajuan berstatus `Disetujui` (*Approved*).
  2. Pada panel pembayaran, pilih Metode Pembayaran: "Bank Transfer", isi No Rekening Tujuan.
  3. Klik tombol *Konfirmasi Pembayaran Kas*.
- **Expected Result:** Status pengajuan berubah menjadi `Dibayar`, transaksi pengeluaran kas kantor otomatis tercatat di buku kas dengan referensi nomor reimbursement terkait, saldo kas kantor otomatis terpotong.
- **Severity Jika Gagal:** Critical (P1)

#### UAT-REJ-04: Pencegahan Pembayaran Ganda (*Anti Double-Payment*)
- **Aktor:** `finance@office.test`
- **Langkah:**
  1. Buka kembali pengajuan yang sudah berstatus `Dibayar` (*Paid*).
  2. Coba jalankan kembali aksi pembayaran.
- **Expected Result:** Panel form pembayaran tidak ditampilkan pada pengajuan yang sudah dibayar; upaya pemanggilan API/URL pembayaran ulang secara paksa ditolak dengan *DomainException*.
- **Severity Jika Gagal:** Critical (P1)

---

### MODUL 5: PENCATATAN TRANSAKSI KAS & SALDO BERJALAN (TRX)

#### UAT-TRX-01: Pencatatan Pemasukan Kas Operasional
- **Aktor:** `finance@office.test`
- **Langkah:**
  1. Buka menu *Transaksi Kas* &rarr; klik *Catat Transaksi*.
  2. Pilih Jenis: "Pemasukan", Tanggal: Hari ini, Nominal: "15000000.00".
  3. Kategori: "Penerimaan Kas Operasional", Divisi: "Finance & Accounting".
  4. Deskripsi: "Top Up Kas Kecil Operasional Bulan Ini".
  5. Klik *Simpan Transaksi Kas*.
- **Expected Result:** Transaksi tersimpan dengan nomor `TRX-...`, status `Lunas`, saldo kas kantor bertambah tepat Rp 15.000.000,00.
- **Severity Jika Gagal:** Critical (P1)

#### UAT-TRX-02: Pencatatan Pengeluaran Kas dengan Alokasi Anggaran
- **Aktor:** `finance@office.test`
- **Langkah:**
  1. Catat transaksi Pengeluaran sebesar Rp 3.000.000,00.
  2. Pilih Pos Anggaran Terkait: "Anggaran Operasional IT".
  3. Simpan transaksi.
- **Expected Result:** Saldo kas kantor berkurang Rp 3.000.000,00, dana terpakai pada pos anggaran "Anggaran Operasional IT" otomatis bertambah Rp 3.000.000,00 secara atomik.
- **Severity Jika Gagal:** Critical (P1)

#### UAT-TRX-03: Pembatalan Transaksi Kas (*Cancellation*) dengan Alasan
- **Aktor:** `finance@office.test`
- **Langkah:**
  1. Buka detail transaksi pengeluaran yang baru dibuat.
  2. Pada panel pembatalan, masukkan alasan: "Salah input pos anggaran transaksi".
  3. Klik *Batalkan Transaksi*.
- **Expected Result:** Status transaksi berubah menjadi `Dibatalkan`, transaksi tidak lagi dihitung dalam saldo kas aktif, dana terpakai pada pos anggaran dipulihkan (berkurang kembali Rp 3.000.000,00).
- **Severity Jika Gagal:** Critical (P1)

#### UAT-TRX-04: Kebijakan Larangan Hard Delete
- **Aktor:** Seluruh Role
- **Langkah:**
  1. Periksa seluruh antarmuka transaksi kas dan database controller.
- **Expected Result:** Tidak ada tombol atau route hapus permanen (`destroy`); seluruh transaksi keuangan historis terlindungi dari penghapusan fisik.
- **Severity Jika Gagal:** Critical (P1)

---

### MODUL 6: PENGENDALIAN & MONITORING ANGGARAN (BUDG)

#### UAT-BUDG-01: Pembuatan Pos Anggaran Baru
- **Aktor:** `finance@office.test`
- **Langkah:**
  1. Buka menu *Anggaran (Budget)* &rarr; klik *Alokasikan Anggaran Baru*.
  2. Nama: "Budget Cloud Server Q4 2026", Divisi: "Technology".
  3. Periode: 01/10/2026 s/d 31/12/2026, Nominal: "20000000.00".
  4. Simpan anggaran.
- **Expected Result:** Pos anggaran tersimpan aktif, pagu Rp 20.000.000,00, terpakai Rp 0,00, sisa Rp 20.000.000,00, serapan 0%.
- **Severity Jika Gagal:** Major (P2)

#### UAT-BUDG-02: Verifikasi Ambang Batas Peringatan Serapan Kuota (75%, 90%, 100%)
- **Aktor:** `finance@office.test`
- **Langkah:**
  1. Tambahkan transaksi beban anggaran hingga dana terpakai mencapai Rp 15.500.000,00 (77.5%).
  2. Tambahkan transaksi beban hingga mencapai Rp 18.200.000,00 (91%).
  3. Tambahkan transaksi beban hingga mencapai Rp 20.000.000,00 (100%).
- **Expected Result:**
  - Pada 77.5%: Muncul badge kuning `Warning` (75%–89%).
  - Pada 91%: Muncul badge oranye `High Warning` (90%–99%).
  - Pada 100%: Muncul badge merah `Budget Exhausted` (100%).
- **Severity Jika Gagal:** Major (P2)

#### UAT-BUDG-03: Pembatasan Penurunan Pagu di Bawah Dana Terpakai
- **Aktor:** `finance@office.test`
- **Langkah:**
  1. Edit pos anggaran yang telah terpakai Rp 5.000.000,00.
  2. Ubah pagu alokasi menjadi Rp 4.000.000,00 &rarr; klik simpan.
- **Expected Result:** Sistem menolak pengubahan alokasi dengan pesan error bahwa pagu tidak boleh lebih kecil dari dana yang sudah terserap.
- **Severity Jika Gagal:** Major (P2)

---

### MODUL 7: LAPORAN KEUANGAN & EKSPOR DOKUMEN (REP)

#### UAT-REP-01: Filter & Metrik Terpadu Laporan Arus Kas
- **Aktor:** `finance@office.test`
- **Langkah:**
  1. Buka menu *Laporan Keuangan* tab *Arus Kas (Transaksi)*.
  2. Masukkan periode tanggal mulai dan selesai yang valid.
  3. Klik *Terapkan Filter*.
- **Expected Result:** Menampilkan 4 kartu ringkasan: Total Pemasukan Kas, Total Pengeluaran Kas, Saldo Bersih Kas, dan Jumlah Transaksi sesuai kriteria filter.
- **Severity Jika Gagal:** Major (P2)

#### UAT-REP-02: Isolasi Batas Divisi untuk Manager
- **Aktor:** `manager@office.test` (Divisi Technology)
- **Langkah:**
  1. Login sebagai Manager &rarr; buka menu *Laporan Keuangan*.
  2. Periksa kolom dropdown filter Divisi.
- **Expected Result:** Dropdown divisi terkunci pada "Technology" dan data transaksi divisi lain (Finance, Marketing) tidak dapat diakses.
- **Severity Jika Gagal:** Critical (P1)

#### UAT-REP-03: Ekspor Laporan Excel (CSV UTF-8 BOM)
- **Aktor:** `finance@office.test`
- **Langkah:**
  1. Buka halaman Laporan Keuangan &rarr; klik tombol *Unduh Excel (CSV)*.
  2. Buka berkas `.csv` yang terunduh menggunakan Microsoft Excel.
- **Expected Result:** Berkas terunduh secara streaming, seluruh kolom terpisah rapi sesuai delimiter koma, karakter format mata uang Rupiah dan nama berbahasa Indonesia terbaca utuh tanpa *mojibake*.
- **Severity Jika Gagal:** Major (P2)

#### UAT-REP-04: Tampilan Cetak Dokumen / Simpan PDF
- **Aktor:** `finance@office.test`
- **Langkah:**
  1. Klik tombol *Cetak / Simpan PDF* pada halaman Laporan Keuangan.
- **Expected Result:** Terbuka tab baru tampilan dokumen resmi tanpa elemen navigasi website (sidebar/topbar bersih), dilengkapi kop surat formal perusahaan, waktu cetak WIB, tabel rincian data, dan kolom tanda tangan pengesahan.
- **Severity Jika Gagal:** Minor (P3)

---

### MODUL 8: KEAMANAN BERKAS & AUDIT LOG (SEC)

#### UAT-SEC-01: Akses Unduh Berkas Bukti Privat Terotorisasi
- **Aktor:** `employee@office.test` (Pemilik) & `finance@office.test`
- **Langkah:**
  1. Buka detail reimbursement yang memiliki lampiran bukti &rarr; klik tombol *Unduh Bukti*.
- **Expected Result:** Berkas nota privat berhasil diunduh secara langsung dari storage privat.
- **Severity Jika Gagal:** Major (P2)

#### UAT-SEC-02: Pencegahan Unduh Berkas Ilegal (IDOR Prevention)
- **Aktor:** Karyawan Divisi Lain
- **Langkah:**
  1. Login sebagai karyawan divisi Marketing.
  2. Coba akses langsung URL unduh lampiran milik karyawan divisi Technology: `/reimbursements/{id}/attachment`.
- **Expected Result:** Server menolak akses dengan respons `403 Forbidden`. Berkas fisik tidak dapat diunduh tanpa otorisasi.
- **Severity Jika Gagal:** Critical (P1)

#### UAT-SEC-03: Verifikasi Integritas Audit Log
- **Aktor:** `admin@office.test`
- **Langkah:**
  1. Lakukan aksi mutasi (submit reimbursement, approve, mark as paid, cancel transaction, export CSV).
  2. Buka tabel `audit_logs` melalui basis data atau antrean admin.
- **Expected Result:** Seluruh aktivitas tercatat lengkap dengan identitas user, jenis aksi, modul, nilai sebelum/sesudah, alamat IP, dan waktu kejadian. Data audit bersifat imutabel tanpa antarmuka hapus/ubah.
- **Severity Jika Gagal:** Major (P2)

# Product Requirements Document (PRD)  
## Aplikasi Keuangan Internal Kantor

**Nama produk:** Finance Office  
**Versi:** 1.0 – MVP  
**Status:** Draft  
**Tanggal:** 11 Oktober 2026  
**Target platform:** Web responsif  
**Target pengguna:** Karyawan, Manager, Finance, dan Direktur  

---

# 1. Ringkasan Produk

Finance Office adalah aplikasi internal untuk mengelola transaksi keuangan kantor secara terpusat, transparan, dan terdokumentasi.

Aplikasi ini membantu perusahaan dalam:

- Mencatat pemasukan dan pengeluaran.
- Mengelola pengajuan reimbursement.
- Mengatur proses persetujuan biaya.
- Memantau anggaran divisi atau proyek.
- Mengunggah bukti transaksi.
- Membuat laporan keuangan.
- Menyimpan riwayat aktivitas untuk kebutuhan audit.

Versi MVP difokuskan pada proses utama: **pengajuan biaya, approval, transaksi keuangan, bukti transaksi, anggaran dasar, dan laporan**.

---

# 2. Latar Belakang dan Masalah

Saat ini proses keuangan kantor sering dilakukan melalui spreadsheet, email, chat, atau dokumen manual. Kondisi tersebut menimbulkan beberapa masalah:

1. Data transaksi tersebar di banyak tempat.
2. Proses approval sulit dipantau.
3. Bukti transaksi mudah hilang.
4. Finance kesulitan mengetahui status pengajuan.
5. Penggunaan anggaran setiap divisi tidak transparan.
6. Pembuatan laporan membutuhkan waktu lama.
7. Tidak semua perubahan transaksi memiliki riwayat.
8. Risiko pengajuan ganda atau transaksi tanpa bukti cukup tinggi.

Aplikasi ini dibuat untuk menyediakan satu sistem terpusat yang dapat digunakan oleh seluruh pihak terkait.

---

# 3. Tujuan Produk

## 3.1 Tujuan Utama

- Memusatkan seluruh data transaksi keuangan kantor.
- Mempercepat proses pengajuan dan persetujuan biaya.
- Meningkatkan kontrol terhadap anggaran.
- Memastikan setiap transaksi memiliki bukti yang valid.
- Menyediakan laporan yang cepat dan akurat.
- Meningkatkan transparansi dan akuntabilitas keuangan.

## 3.2 Tujuan Bisnis

- Mengurangi penggunaan spreadsheet manual.
- Mengurangi kesalahan pencatatan transaksi.
- Mempercepat proses reimbursement.
- Memudahkan pemeriksaan internal.
- Menyediakan data keuangan secara real-time.
- Mengurangi risiko transaksi yang tidak terdokumentasi.

## 3.3 Bukan Tujuan MVP

MVP tidak mencakup:

- Sistem akuntansi lengkap.
- Payroll atau penggajian.
- Perhitungan pajak otomatis.
- Integrasi langsung dengan bank.
- Pembayaran otomatis ke rekening penerima.
- OCR nota otomatis.
- Aplikasi mobile native.
- Pengelolaan aset tetap.
- Laporan akuntansi seperti neraca dan jurnal umum secara lengkap.

---

# 4. Sasaran Pengguna

## 4.1 Karyawan

Membutuhkan cara mudah untuk:

- Mengajukan reimbursement.
- Mengajukan permintaan dana.
- Mengunggah bukti transaksi.
- Melihat status pengajuan.
- Melihat riwayat pengajuan pribadi.

## 4.2 Manager

Membutuhkan kemampuan untuk:

- Meninjau pengajuan dari anggota divisi.
- Menyetujui atau menolak pengajuan.
- Melihat penggunaan anggaran divisi.
- Memberikan catatan pada pengajuan.

## 4.3 Finance

Membutuhkan kemampuan untuk:

- Memverifikasi transaksi.
- Mengelola pemasukan dan pengeluaran.
- Mengelola kategori dan anggaran.
- Memproses pembayaran.
- Membuat laporan.
- Mengelola data vendor.

## 4.4 Direktur/Pimpinan

Membutuhkan kemampuan untuk:

- Melihat ringkasan kondisi keuangan.
- Menyetujui pengajuan bernilai besar.
- Melihat laporan berdasarkan periode.
- Melihat penggunaan anggaran seluruh divisi.

## 4.5 Admin Sistem

Membutuhkan kemampuan untuk:

- Mengelola pengguna.
- Mengatur role dan hak akses.
- Mengatur master data.
- Melihat audit log.
- Mengatur konfigurasi sistem.

---

# 5. Persona dan Kebutuhan Utama

| Persona | Kebutuhan | Masalah yang Diselesaikan |
|---|---|---|
| Karyawan | Mengajukan biaya dengan cepat | Tidak perlu mengirim dokumen melalui chat |
| Manager | Menyetujui pengajuan secara terstruktur | Tidak ada pengajuan yang terlewat |
| Finance | Memverifikasi dan mencatat transaksi | Data lebih rapi dan mudah dicari |
| Direktur | Melihat kondisi keuangan | Tidak perlu menunggu laporan manual |
| Admin | Mengelola akses pengguna | Hak akses lebih aman dan terkontrol |

---

# 6. Ruang Lingkup MVP

## 6.1 Fitur yang Termasuk

1. Login dan autentikasi.
2. Role dan permission.
3. Dashboard.
4. Master data pengguna dan divisi.
5. Master kategori transaksi.
6. Pengajuan reimbursement.
7. Pengajuan permintaan dana.
8. Approval bertingkat.
9. Pencatatan pemasukan.
10. Pencatatan pengeluaran.
11. Upload bukti transaksi.
12. Pengelolaan anggaran dasar.
13. Data vendor.
14. Laporan dan filter transaksi.
15. Export Excel dan PDF.
16. Notifikasi dalam aplikasi.
17. Audit log.

## 6.2 Fitur Setelah MVP

1. Kas kecil.
2. Invoice vendor dan jatuh tempo.
3. Rekonsiliasi bank.
4. Notifikasi email atau WhatsApp.
5. Integrasi sistem akuntansi.
6. OCR nota.
7. Mobile application.
8. Integrasi pembayaran.
9. Analisis dan prediksi arus kas.

---

# 7. Struktur Navigasi

```text
Dashboard

Transaksi
├── Pemasukan
├── Pengeluaran
└── Semua Transaksi

Pengajuan
├── Pengajuan Saya
├── Reimbursement
├── Permintaan Dana
└── Menunggu Persetujuan

Anggaran
├── Daftar Anggaran
└── Penggunaan Anggaran

Vendor

Laporan
├── Pemasukan dan Pengeluaran
├── Reimbursement
├── Penggunaan Anggaran
└── Transaksi per Divisi

Master Data
├── Pengguna
├── Divisi
├── Kategori Transaksi
└── Metode Pembayaran

Audit Log

Pengaturan
```

Menu yang tampil harus disesuaikan dengan role pengguna.

---

# 8. Alur Bisnis Utama

## 8.1 Alur Reimbursement

```text
Karyawan membuat pengajuan
        ↓
Mengisi detail transaksi
        ↓
Mengunggah bukti pembayaran
        ↓
Mengirim pengajuan
        ↓
Manager melakukan review
        ↓
Finance melakukan verifikasi
        ↓
Direktur menyetujui jika melewati batas tertentu
        ↓
Finance melakukan pembayaran
        ↓
Status menjadi Selesai
```

## 8.2 Alur Permintaan Dana

```text
Karyawan/divisi membuat permintaan dana
        ↓
Sistem mengecek ketersediaan anggaran
        ↓
Manager melakukan approval
        ↓
Finance memverifikasi
        ↓
Direktur menyetujui jika diperlukan
        ↓
Dana disiapkan
        ↓
Status menjadi Disalurkan
```

## 8.3 Alur Pencatatan Pengeluaran

```text
Finance membuat transaksi pengeluaran
        ↓
Mengisi kategori, nominal, divisi, dan penerima
        ↓
Mengunggah dokumen pendukung
        ↓
Sistem memvalidasi data
        ↓
Transaksi tersimpan
        ↓
Saldo dan laporan diperbarui
```

## 8.4 Alur Pencatatan Pemasukan

```text
Finance membuat transaksi pemasukan
        ↓
Mengisi sumber dana dan rekening tujuan
        ↓
Mengunggah bukti transaksi
        ↓
Transaksi diverifikasi
        ↓
Saldo dan laporan diperbarui
```

---

# 9. Status Pengajuan

## 9.1 Status Umum

- Draft
- Diajukan
- Menunggu Persetujuan Manager
- Menunggu Verifikasi Finance
- Menunggu Persetujuan Direktur
- Disetujui
- Ditolak
- Dibayar
- Selesai
- Dibatalkan

## 9.2 Aturan Perubahan Status

| Status Awal | Aksi | Status Berikutnya |
|---|---|---|
| Draft | Kirim pengajuan | Menunggu Persetujuan Manager |
| Menunggu Persetujuan Manager | Disetujui | Menunggu Verifikasi Finance |
| Menunggu Persetujuan Manager | Ditolak | Ditolak |
| Menunggu Verifikasi Finance | Disetujui | Disetujui atau Menunggu Direktur |
| Menunggu Verifikasi Finance | Ditolak | Ditolak |
| Menunggu Direktur | Disetujui | Disetujui |
| Disetujui | Finance melakukan pembayaran | Dibayar |
| Dibayar | Transaksi dikonfirmasi | Selesai |

Pengajuan yang sudah selesai tidak boleh dihapus. Jika diperlukan, statusnya dapat diubah menjadi **Dibatalkan** dengan alasan dan audit log.

---

# 10. Functional Requirements

## 10.1 Autentikasi dan Manajemen Pengguna

### Requirement

**FR-001 – Login**

Sistem harus menyediakan login menggunakan email atau username dan password.

**FR-002 – Logout**

Pengguna dapat keluar dari aplikasi secara aman.

**FR-003 – Reset Password**

Pengguna dapat melakukan reset password melalui email atau bantuan admin.

**FR-004 – Role-Based Access**

Sistem harus membatasi akses berdasarkan role.

**FR-005 – Status Pengguna**

Admin dapat mengaktifkan atau menonaktifkan akun pengguna.

### Kriteria Penerimaan

- Pengguna dengan kredensial valid dapat masuk.
- Pengguna dengan kredensial salah mendapatkan pesan error.
- Pengguna nonaktif tidak dapat login.
- Setiap menu yang tampil sesuai dengan hak akses pengguna.
- Password tidak disimpan dalam bentuk teks biasa.

---

## 10.2 Dashboard

### Requirement

**FR-006 – Ringkasan Keuangan**

Dashboard menampilkan:

- Total pemasukan.
- Total pengeluaran.
- Saldo berjalan.
- Jumlah pengajuan menunggu approval.
- Total anggaran.
- Total penggunaan anggaran.

**FR-007 – Filter Dashboard**

Data dashboard dapat difilter berdasarkan:

- Periode.
- Divisi.
- Proyek.
- Kategori transaksi.

**FR-008 – Grafik Keuangan**

Dashboard menyediakan grafik:

- Pemasukan dan pengeluaran per bulan.
- Pengeluaran berdasarkan kategori.
- Penggunaan anggaran per divisi.

### Kriteria Penerimaan

- Data dashboard hanya menampilkan transaksi sesuai hak akses.
- Filter periode memperbarui seluruh angka dan grafik.
- Saldo dihitung berdasarkan transaksi yang valid dan tidak dibatalkan.
- Pengguna dapat melihat waktu pembaruan data.

---

## 10.3 Pengajuan Reimbursement

### Data Pengajuan

- Nomor pengajuan.
- Nama pemohon.
- Divisi.
- Tanggal pengajuan.
- Tanggal transaksi.
- Kategori biaya.
- Deskripsi.
- Nominal.
- Metode pembayaran.
- Proyek atau kegiatan.
- Rekening tujuan pembayaran.
- Bukti transaksi.
- Catatan.

### Requirement

**FR-009 – Membuat Reimbursement**

Karyawan dapat membuat pengajuan reimbursement dalam status Draft.

**FR-010 – Upload Bukti**

Pengguna wajib mengunggah bukti transaksi sebelum pengajuan dikirim.

**FR-011 – Simpan Draft**

Pengguna dapat menyimpan pengajuan tanpa langsung mengirimkannya.

**FR-012 – Kirim Pengajuan**

Pengguna dapat mengirim pengajuan setelah seluruh data wajib terisi.

**FR-013 – Melihat Status**

Pemohon dapat melihat status dan riwayat pengajuan.

**FR-014 – Edit Pengajuan**

Pengajuan hanya dapat diedit jika masih berstatus Draft atau dikembalikan untuk perbaikan.

**FR-015 – Komentar Review**

Manager dan Finance dapat memberikan catatan pada pengajuan.

### Kriteria Penerimaan

- Pengajuan tidak dapat dikirim jika data wajib belum lengkap.
- Sistem menolak file dengan format tidak didukung.
- Sistem membuat nomor pengajuan secara otomatis.
- Setelah dikirim, pemohon tidak dapat mengubah data utama.
- Pemohon menerima notifikasi ketika status berubah.
- Pengajuan yang ditolak harus memiliki alasan penolakan.

---

## 10.4 Permintaan Dana

### Data Permintaan

- Nomor permintaan.
- Pemohon.
- Divisi.
- Tujuan penggunaan dana.
- Nominal yang diminta.
- Tanggal penggunaan.
- Kategori biaya.
- Proyek atau kegiatan.
- Sumber anggaran.
- Lampiran dokumen.
- Catatan.

### Requirement

**FR-016 – Membuat Permintaan Dana**

Pengguna berwenang dapat mengajukan permintaan dana sebelum transaksi dilakukan.

**FR-017 – Validasi Anggaran**

Sistem menampilkan sisa anggaran yang tersedia.

**FR-018 – Peringatan Anggaran**

Sistem memberikan peringatan jika nominal permintaan melebihi sisa anggaran.

**FR-019 – Persetujuan Permintaan**

Permintaan dana mengikuti alur approval yang ditentukan.

---

## 10.5 Approval

### Requirement

**FR-020 – Daftar Pengajuan Approval**

Manager, Finance, dan Direktur dapat melihat daftar pengajuan yang membutuhkan tindakan.

**FR-021 – Approve**

Approver dapat menyetujui pengajuan.

**FR-022 – Reject**

Approver dapat menolak pengajuan dengan alasan wajib.

**FR-023 – Pengembalian untuk Perbaikan**

Approver dapat mengembalikan pengajuan kepada pemohon dengan catatan.

**FR-024 – Approval Bertingkat**

Approval berikutnya hanya aktif setelah approval sebelumnya selesai.

**FR-025 – Batas Approval**

Sistem dapat menggunakan batas nominal. Contoh:

| Nilai Pengajuan | Approval |
|---|---|
| Sampai Rp5.000.000 | Manager dan Finance |
| Rp5.000.001–Rp25.000.000 | Manager, Finance, Direktur |
| Di atas Rp25.000.000 | Manager, Finance, Direktur Utama/Owner |

Batas nominal harus dapat diubah oleh Admin atau Finance yang berwenang.

### Kriteria Penerimaan

- Approver tidak dapat menyetujui pengajuan miliknya sendiri.
- Setiap approval menyimpan nama approver, waktu, keputusan, dan catatan.
- Pengajuan tidak dapat lanjut ke tahap berikutnya sebelum approval sebelumnya selesai.
- Penolakan wajib disertai alasan.
- Pengaju menerima pemberitahuan atas keputusan approval.

---

## 10.6 Pemasukan

### Data Pemasukan

- Nomor transaksi.
- Tanggal transaksi.
- Sumber pemasukan.
- Kategori pemasukan.
- Nominal.
- Rekening tujuan.
- Metode penerimaan.
- Deskripsi.
- Bukti transaksi.
- Divisi atau proyek terkait.

### Requirement

**FR-026 – Membuat Pemasukan**

Finance dapat mencatat pemasukan baru.

**FR-027 – Mengedit Pemasukan**

Transaksi dapat diedit oleh Finance selama periode belum ditutup.

**FR-028 – Membatalkan Pemasukan**

Transaksi dapat dibatalkan dengan alasan wajib dan tercatat pada audit log.

---

## 10.7 Pengeluaran

### Data Pengeluaran

- Nomor transaksi.
- Tanggal transaksi.
- Kategori biaya.
- Divisi.
- Proyek.
- Vendor atau penerima.
- Nominal.
- Metode pembayaran.
- Rekening sumber.
- Nomor invoice atau dokumen.
- Deskripsi.
- Bukti transaksi.

### Requirement

**FR-029 – Membuat Pengeluaran**

Finance dapat mencatat transaksi pengeluaran.

**FR-030 – Relasi dengan Pengajuan**

Pengeluaran dapat dikaitkan dengan reimbursement atau permintaan dana.

**FR-031 – Validasi Duplikasi**

Sistem memberikan peringatan jika terdapat transaksi dengan kombinasi data yang berpotensi duplikat, seperti:

- Pemohon.
- Tanggal.
- Nominal.
- Nomor invoice.
- Vendor.

**FR-032 – Status Pembayaran**

Finance dapat mengubah status transaksi menjadi:

- Belum Dibayar.
- Dijadwalkan.
- Dibayar.
- Dibatalkan.

---

## 10.8 Anggaran

### Data Anggaran

- Tahun/periode.
- Divisi.
- Kategori.
- Proyek.
- Nilai anggaran.
- Nilai terpakai.
- Sisa anggaran.
- Status.

### Requirement

**FR-033 – Membuat Anggaran**

Finance dapat membuat anggaran berdasarkan divisi, kategori, proyek, dan periode.

**FR-034 – Perhitungan Penggunaan**

Sistem menghitung nilai penggunaan anggaran berdasarkan transaksi yang disetujui.

**FR-035 – Peringatan Anggaran**

Sistem memberikan peringatan saat penggunaan mencapai:

- 75%: peringatan awal.
- 90%: peringatan tinggi.
- 100%: anggaran habis.

**FR-036 – Persetujuan di Luar Anggaran**

Pengajuan di luar anggaran harus memerlukan persetujuan tambahan atau dapat ditolak sesuai konfigurasi.

---

## 10.9 Data Vendor

### Data Vendor

- Nama vendor.
- Nama kontak.
- Nomor telepon.
- Email.
- Alamat.
- Nomor rekening.
- Nama pemilik rekening.
- NPWP, jika diperlukan.
- Status vendor.
- Catatan.

### Requirement

**FR-037 – Mengelola Vendor**

Finance dapat menambah, mengubah, menonaktifkan, dan mencari data vendor.

**FR-038 – Relasi Vendor**

Vendor dapat dikaitkan dengan transaksi pengeluaran dan invoice.

---

## 10.10 Upload Dokumen

### Requirement

**FR-039 – Upload File**

Sistem mendukung upload:

- PDF.
- JPG/JPEG.
- PNG.
- XLSX, jika diperlukan.

**FR-040 – Validasi File**

Sistem membatasi:

- Ukuran maksimum file, misalnya 5 MB.
- Jenis file.
- Jumlah file per transaksi.

**FR-041 – Preview Dokumen**

Pengguna yang memiliki akses dapat melihat atau mengunduh dokumen.

**FR-042 – Keamanan Dokumen**

File tidak boleh dapat diakses melalui URL publik tanpa autentikasi.

---

## 10.11 Laporan

### Laporan MVP

1. Laporan pemasukan dan pengeluaran.
2. Laporan reimbursement.
3. Laporan transaksi per divisi.
4. Laporan transaksi per kategori.
5. Laporan penggunaan anggaran.
6. Laporan pengajuan berdasarkan status.

### Requirement

**FR-043 – Filter Laporan**

Laporan dapat difilter berdasarkan:

- Periode.
- Divisi.
- Kategori.
- Vendor.
- Status.
- Jenis transaksi.
- Proyek.

**FR-044 – Export Excel**

Pengguna berwenang dapat mengunduh data dalam format Excel.

**FR-045 – Export PDF**

Pengguna berwenang dapat mengunduh laporan dalam format PDF.

**FR-046 – Print**

Laporan dapat dicetak dengan format yang rapi.

---

## 10.12 Notifikasi

### Requirement

**FR-047 – Notifikasi Status**

Sistem memberikan notifikasi ketika:

- Pengajuan berhasil dikirim.
- Pengajuan perlu direview.
- Pengajuan disetujui.
- Pengajuan ditolak.
- Pengajuan dikembalikan.
- Pembayaran selesai.
- Anggaran mendekati batas.

Untuk MVP, notifikasi cukup tersedia di dalam aplikasi. Email atau WhatsApp dapat dikembangkan pada tahap berikutnya.

---

## 10.13 Audit Log

### Requirement

**FR-048 – Mencatat Aktivitas**

Sistem harus mencatat:

- Login dan logout.
- Pembuatan transaksi.
- Perubahan transaksi.
- Penghapusan atau pembatalan transaksi.
- Upload dokumen.
- Keputusan approval.
- Perubahan status.
- Perubahan master data.

### Informasi Audit Log

- Pengguna.
- Aktivitas.
- Waktu.
- IP address, jika diperlukan.
- Data sebelum perubahan.
- Data setelah perubahan.

Audit log hanya dapat dilihat oleh Admin, Finance tertentu, atau Direktur.

---

# 11. Hak Akses

| Fitur | Karyawan | Manager | Finance | Direktur | Admin |
|---|---:|---:|---:|---:|---:|
| Login | Ya | Ya | Ya | Ya | Ya |
| Membuat reimbursement | Ya | Ya | Ya | Ya | Ya |
| Melihat pengajuan sendiri | Ya | Ya | Ya | Ya | Ya |
| Melihat pengajuan divisi | Tidak | Ya | Ya | Ya | Ya |
| Menyetujui pengajuan | Tidak | Ya | Ya | Ya | Opsional |
| Mencatat pemasukan | Tidak | Tidak | Ya | Opsional | Ya |
| Mencatat pengeluaran | Tidak | Tidak | Ya | Opsional | Ya |
| Mengelola anggaran | Tidak | Terbatas | Ya | Ya | Terbatas |
| Melihat semua laporan | Tidak | Divisi sendiri | Ya | Ya | Terbatas |
| Mengelola vendor | Tidak | Tidak | Ya | Opsional | Ya |
| Mengelola pengguna | Tidak | Tidak | Terbatas | Tidak | Ya |
| Melihat audit log | Tidak | Tidak | Terbatas | Ya | Ya |

---

# 12. Aturan Bisnis

1. Setiap pengajuan harus memiliki pemohon.
2. Nominal transaksi harus lebih besar dari nol.
3. Bukti transaksi wajib dilampirkan untuk reimbursement.
4. Pengajuan tidak dapat disetujui oleh pembuat pengajuan.
5. Pengajuan yang ditolak wajib memiliki alasan.
6. Transaksi yang sudah dibayar tidak dapat dihapus.
7. Transaksi yang dibatalkan harus memiliki alasan.
8. Periode keuangan yang sudah ditutup tidak boleh diedit tanpa hak khusus.
9. Setiap perubahan data keuangan dicatat dalam audit log.
10. Pengajuan melebihi batas nominal harus mengikuti approval tambahan.
11. Pengajuan yang melebihi anggaran harus diberi peringatan.
12. Pengajuan dengan bukti transaksi yang sama harus diberi peringatan duplikasi.
13. Satu akun hanya dapat memiliki role yang ditentukan oleh Admin.
14. Data keuangan hanya dapat dilihat sesuai hak akses pengguna.
15. Semua nominal menggunakan mata uang Rupiah pada MVP.

---

# 13. Kebutuhan Non-Fungsional

## 13.1 Keamanan

- Menggunakan HTTPS.
- Password disimpan menggunakan hashing yang aman.
- Role dan permission wajib diterapkan di backend.
- File bukti transaksi tidak dapat diakses secara publik.
- Sistem mencatat audit log.
- Sesi pengguna memiliki timeout.
- Tersedia pembatasan percobaan login.
- Backup database dilakukan secara berkala.

## 13.2 Performa

- Halaman utama terbuka maksimal 3 detik pada kondisi normal.
- Pencarian transaksi menampilkan hasil maksimal 3 detik.
- Sistem mampu menangani minimal 100 pengguna aktif secara bersamaan untuk tahap awal.
- Export laporan besar menampilkan status proses jika membutuhkan waktu lama.

## 13.3 Ketersediaan

- Target availability MVP: minimal 99% per bulan.
- Backup database minimal satu kali setiap hari.
- Tersedia prosedur pemulihan data.
- Sistem menampilkan pesan error yang mudah dipahami.

## 13.4 Kemudahan Penggunaan

- Tampilan responsif untuk desktop, tablet, dan mobile browser.
- Form menggunakan validasi yang jelas.
- Pesan error ditulis dalam bahasa Indonesia.
- Status pengajuan menggunakan label dan warna yang konsisten.
- Pengguna dapat menemukan pengajuan melalui nomor, nama, atau status.

## 13.5 Kompatibilitas

Aplikasi harus mendukung versi terbaru dari:

- Google Chrome.
- Mozilla Firefox.
- Microsoft Edge.
- Safari.

---

# 14. Data Utama

## 14.1 Users

- ID.
- Nama.
- Email.
- Nomor telepon.
- Password hash.
- Role.
- Divisi.
- Atasan.
- Status.
- Tanggal dibuat.

## 14.2 Departments

- ID.
- Nama divisi.
- Manager.
- Status.

## 14.3 Transactions

- ID.
- Nomor transaksi.
- Jenis transaksi.
- Tanggal.
- Nominal.
- Kategori.
- Divisi.
- Proyek.
- Vendor/penerima.
- Metode pembayaran.
- Status.
- Deskripsi.
- Pembuat.
- Tanggal dibuat.

## 14.4 Reimbursements

- ID.
- Nomor pengajuan.
- Pemohon.
- Total nominal.
- Status.
- Catatan.
- Tanggal pengajuan.
- Tanggal pembayaran.
- Relasi transaksi.

## 14.5 Budgets

- ID.
- Periode.
- Divisi.
- Kategori.
- Proyek.
- Nilai anggaran.
- Nilai terpakai.
- Sisa anggaran.
- Status.

## 14.6 Vendors

- ID.
- Nama vendor.
- Kontak.
- Alamat.
- Nomor rekening.
- Nama rekening.
- NPWP.
- Status.

## 14.7 Approvals

- ID.
- Tipe objek.
- ID objek.
- Approver.
- Level approval.
- Status.
- Catatan.
- Waktu keputusan.

## 14.8 Attachments

- ID.
- Nama file.
- Tipe file.
- Ukuran.
- Lokasi penyimpanan.
- ID transaksi atau pengajuan.
- Pengunggah.
- Waktu upload.

## 14.9 Audit Logs

- ID.
- Pengguna.
- Aktivitas.
- Modul.
- ID objek.
- Data sebelum perubahan.
- Data setelah perubahan.
- Waktu aktivitas.

---

# 15. Acceptance Criteria Utama

## 15.1 Pengajuan Reimbursement

Fitur dianggap selesai jika:

- Karyawan dapat membuat pengajuan.
- Form memiliki validasi wajib.
- Bukti transaksi dapat diunggah.
- Pengajuan mendapatkan nomor otomatis.
- Pengajuan dapat dikirim ke approver.
- Approver dapat menyetujui, menolak, atau mengembalikan.
- Finance dapat mengubah status menjadi Dibayar.
- Pemohon dapat melihat riwayat status.
- Semua aktivitas tercatat di audit log.

## 15.2 Pencatatan Transaksi

Fitur dianggap selesai jika:

- Finance dapat mencatat pemasukan dan pengeluaran.
- Data dapat difilter berdasarkan periode dan kategori.
- Transaksi dapat dikaitkan dengan divisi dan proyek.
- Bukti transaksi dapat dilampirkan.
- Sistem menghitung total transaksi dengan benar.
- Transaksi yang dibatalkan tidak masuk ke saldo aktif.
- Perubahan data tercatat.

## 15.3 Anggaran

Fitur dianggap selesai jika:

- Finance dapat membuat anggaran.
- Sistem menghitung penggunaan anggaran.
- Sistem menampilkan sisa anggaran.
- Sistem memberikan peringatan ketika anggaran hampir habis.
- Pengajuan yang melebihi anggaran dapat diberi peringatan atau memerlukan approval tambahan.

## 15.4 Laporan

Fitur dianggap selesai jika:

- Pengguna berwenang dapat melihat laporan.
- Laporan dapat difilter.
- Total laporan sesuai dengan data transaksi.
- Laporan dapat diunduh dalam format Excel.
- Laporan dapat diunduh dalam format PDF.

---

# 16. Metrik Keberhasilan

## 16.1 Metrik Adopsi

- Minimal 80% pengajuan biaya dilakukan melalui aplikasi dalam tiga bulan pertama.
- Minimal 90% pengguna aktif login setiap bulan.
- Minimal 80% divisi menggunakan fitur anggaran.

## 16.2 Metrik Efisiensi

- Waktu rata-rata approval kurang dari dua hari kerja.
- Waktu pembuatan laporan bulanan berkurang minimal 50%.
- Pengurangan penggunaan spreadsheet manual minimal 70%.

## 16.3 Metrik Kualitas Data

- Minimal 95% transaksi memiliki bukti atau dokumen pendukung.
- Pengajuan duplikat berkurang.
- Tidak ada perubahan transaksi tanpa audit log.
- Error pencatatan transaksi berada di bawah 2%.

## 16.4 Metrik Kepuasan

- Skor kepuasan pengguna minimal 4 dari 5.
- Keluhan terkait status pengajuan berkurang.
- Pengguna dapat menyelesaikan pengajuan tanpa bantuan admin untuk kasus normal.

---

# 17. Prioritas Fitur

## Prioritas P0 – Wajib untuk MVP

- Login.
- Role dan permission.
- Dashboard dasar.
- Reimbursement.
- Approval.
- Pemasukan.
- Pengeluaran.
- Upload bukti transaksi.
- Laporan dasar.
- Export Excel.
- Audit log.

## Prioritas P1 – Penting

- Manajemen anggaran.
- Data vendor.
- Export PDF.
- Permintaan dana.
- Notifikasi dalam aplikasi.
- Approval berdasarkan batas nominal.

## Prioritas P2 – Pengembangan Berikutnya

- Kas kecil.
- Invoice vendor.
- Notifikasi email.
- Integrasi WhatsApp.
- Rekonsiliasi bank.
- Aplikasi mobile.
- OCR nota.
- Integrasi sistem akuntansi.

---

# 18. Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Pengguna tetap memakai spreadsheet | Adopsi rendah | Pelatihan, pembatasan proses manual, dashboard yang mudah digunakan |
| Data transaksi tidak lengkap | Laporan tidak akurat | Validasi form dan field wajib |
| Bukti transaksi palsu atau tidak sesuai | Risiko keuangan | Review Finance dan audit log |
| Hak akses terlalu luas | Kebocoran data | Role-based access dan pembatasan berdasarkan divisi |
| Approval terlambat | Pembayaran tertunda | Notifikasi dan daftar pengajuan tertunda |
| Duplikasi transaksi | Laporan salah | Validasi duplikasi berdasarkan kombinasi data |
| Kehilangan data | Gangguan operasional | Backup berkala dan rencana pemulihan |
| Perubahan kebutuhan bisnis | Scope melebar | Gunakan prioritas P0, P1, dan P2 |

---

# 19. Rencana Pengembangan

## Fase 1 – Analisis dan Desain

Durasi: 1–2 minggu

- Finalisasi kebutuhan.
- Wawancara Finance dan user.
- Penyusunan user flow.
- Desain database.
- Desain UI/UX.
- Finalisasi role dan approval.

## Fase 2 – Pengembangan Core System

Durasi: 2–3 minggu

- Login.
- Pengguna.
- Role.
- Divisi.
- Master kategori.
- Dashboard dasar.

## Fase 3 – Transaksi dan Pengajuan

Durasi: 3–4 minggu

- Pemasukan.
- Pengeluaran.
- Reimbursement.
- Permintaan dana.
- Upload dokumen.
- Approval.

## Fase 4 – Anggaran dan Laporan

Durasi: 2–3 minggu

- Modul anggaran.
- Laporan transaksi.
- Dashboard lanjutan.
- Export Excel dan PDF.
- Audit log.

## Fase 5 – Testing dan Deployment

Durasi: 2 minggu

- Functional testing.
- Security testing.
- User acceptance testing.
- Perbaikan bug.
- Migrasi data jika diperlukan.
- Pelatihan pengguna.
- Deployment produksi.

Perkiraan total MVP: **10–14 minggu**.

---

# 20. Asumsi Produk

PRD ini menggunakan asumsi berikut:

1. Aplikasi digunakan untuk satu perusahaan.
2. Mata uang utama adalah Rupiah.
3. Sistem digunakan secara internal.
4. Pembayaran masih dilakukan di luar aplikasi atau dicatat manual oleh Finance.
5. Pengguna memiliki akun yang dibuat oleh Admin.
6. Struktur approval berdasarkan divisi dan batas nominal.
7. Finance tetap menjadi pihak utama yang melakukan verifikasi transaksi.
8. Laporan MVP belum menggantikan laporan akuntansi resmi.

---

# 21. Definisi Selesai

MVP dinyatakan siap digunakan jika:

- Seluruh fitur P0 telah selesai.
- Tidak ada bug kritis.
- Proses pengajuan sampai pembayaran dapat dilakukan end-to-end.
- Hak akses telah diuji untuk setiap role.
- Data transaksi dan laporan telah diverifikasi oleh Finance.
- Backup dan keamanan dasar telah aktif.
- Pengguna utama telah mengikuti UAT.
- Dokumentasi penggunaan telah tersedia.
- Sistem dapat digunakan di lingkungan produksi.

---

# 22. Ringkasan MVP

Versi pertama aplikasi akan berfokus pada proses berikut:

```text
Login
  ↓
Karyawan mengajukan reimbursement
  ↓
Manager melakukan approval
  ↓
Finance melakukan verifikasi
  ↓
Direktur menyetujui jika diperlukan
  ↓
Finance mencatat pembayaran
  ↓
Data masuk ke dashboard dan laporan
```

Dengan ruang lingkup tersebut, aplikasi sudah dapat menyelesaikan masalah utama kantor terkait **pengajuan biaya, approval, pencatatan transaksi, pengendalian anggaran, dan pelaporan keuangan**.
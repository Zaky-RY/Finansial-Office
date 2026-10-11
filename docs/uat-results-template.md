# LEMBAR KERJA HASIL PENGUJIAN PENERIMAAN PENGGUNA (UAT RESULTS TEMPLATE)
## Finance Office MVP 1.0

---

## 1. Informasi Eksekusi Pengujian

| Parameter | Keterangan / Isian Penguji |
|---|---|
| **Nama Aplikasi** | Finance Office MVP 1.0 |
| **Versi Rilis / Commit** | MVP 1.0 (Laravel 10.50.3 / PHP 8.1+) |
| **Target URL Environment** | `http://127.0.0.1:8000` (Staging / Local Testbed) |
| **Nama Penguji (Tester)** | `[Nama Lengkap Tester]` |
| **Peran / Akun UAT** | `[employee / manager / finance / director / admin]` |
| **Email Akun Penguji** | `[contoh: finance@office.test]` |
| **Tanggal & Waktu Pengujian** | `[YYYY-MM-DD HH:mm WIB]` |
| **Sistem Operasi & Browser** | `[Contoh: Windows 11 - Google Chrome v129.0]` |

---

## 2. Petunjuk Pengisian Status & Kolom

1. **Status Pengujian:**
   - **`PASS`**: Hasil aktual sesuai sepenuhnya dengan kriteria *Expected Result*.
   - **`FAIL`**: Hasil aktual menyimpang, terjadi kegagalan fungsional, salah perhitungan nominal, atau galat server.
   - **`BLOCKED`**: Pengujian tidak dapat dieksekusi karena terhalang kegagalan skenario sebelumnya.
2. **Nomor Bukti (Evidence):**
   - Catat nomor referensi data yang dihasilkan sistem (contoh: `REM-202610-0001`, `TRX-202610-0005`, `BDG-001`).
   - Sertakan tautan atau nama berkas tangkapan layar pendukung (contoh: `SS-UAT-REIMB-01.png`).
3. **Severity (Tingkat Keparahan):**
   - Diisi hanya bila Status bernilai **`FAIL`** atau **`BLOCKED`**:
     - **P1 (Critical)**: Kerusakan sistemik, kebocoran otorisasi, celah data, atau salah hitung nominal saldo/anggaran.
     - **P2 (Major)**: Fitur utama gagal atau alur approval terputus tanpa solusi sementara.
     - **P3 (Minor)**: Ketidaksempurnaan visual minor, salah ketik label, atau format pesan.

---

## 3. Lembar Kerja Hasil Pengujian (Test Execution Sheet)

### MODUL 1: AUTENTIKASI & SESI PENGGUNA (AUTH)

| No | ID Test | Deskripsi Skenario | Expected Result | Actual Result | Status (Pass/Fail/Blocked) | Bukti (No Ref / Screenshot) | Severity | Catatan / Bug ID |
|:---:|:---|:---|:---|:---|:---:|:---|:---:|:---|
| 1 | **UAT-AUTH-01** | Login Pengguna Aktif Berhasil (`employee@office.test`) | Berhasil masuk dashboard, role badge `EMPLOYEE` tampil di sidebar. | | | | P1 | |
| 2 | **UAT-AUTH-02** | Pencegahan Login Akun Nonaktif (`inactive@office.test`) | Ditolak backend, muncul notifikasi: "Akun Anda dinonaktifkan." | | | | P1 | |
| 3 | **UAT-AUTH-03** | Rate Limiting Percobaan Login (6x salah password) | Form diblokir sementara dengan pesan *Too many requests* (throttle). | | | | P2 | |
| 4 | **UAT-AUTH-04** | Logout Sesi & Proteksi Tombol Back Browser | Sesi dihancurkan, kembali ke `/login`, tombol back tidak membuka data privat. | | | | P2 | |

---

### MODUL 2: PENGAJUAN REIMBURSEMENT (REIMB)

| No | ID Test | Deskripsi Skenario | Expected Result | Actual Result | Status (Pass/Fail/Blocked) | Bukti (No Ref / Screenshot) | Severity | Catatan / Bug ID |
|:---:|:---|:---|:---|:---|:---:|:---|:---:|:---|
| 5 | **UAT-REIMB-01** | Pembuatan Draf Multi-Item dengan Lampiran Nota Dummy | Tersimpan nomor `REM-...`, status `Draft`, total otomatis terhitung Rp 800.000,00. | | | | P2 | |
| 6 | **UAT-REIMB-02** | Pengeditan Draf Sebelum Diajukan (Ubah item Rp 200.000,00) | Draf terupdate, total nominal terhitung otomatis menjadi Rp 850.000,00. | | | | P2 | |
| 7 | **UAT-REIMB-03** | Validasi Negatif: Pengajuan Tanpa Lampiran Nota | Pengajuan ditolak validasi; muncul error "Lampiran nota wajib diunggah". | | | | P2 | |
| 8 | **UAT-REIMB-04** | Pengiriman (*Submit*) Pengajuan ke Antrean Approval | Status beralih ke `Menunggu Approval Manager`, terkunci dari edit pemohon, log tercatat. | | | | P1 | |

---

### MODUL 3: ALUR PERSETUJUAN BERTINGKAT & ANTI-SELF-APPROVAL (APPR)

| No | ID Test | Deskripsi Skenario | Expected Result | Actual Result | Status (Pass/Fail/Blocked) | Bukti (No Ref / Screenshot) | Severity | Catatan / Bug ID |
|:---:|:---|:---|:---|:---|:---:|:---|:---:|:---|
| 9 | **UAT-APPR-01** | Persetujuan Tingkat Manager Divisi Technology | Status berubah menjadi `Menunggu Verifikasi Finance`, riwayat approver tercatat. | | | | P1 | |
| 10 | **UAT-APPR-02** | Penegakan Mutlak Anti-Self-Approval oleh Manager | Aksi persetujuan ditolak backend (403 Forbidden), dilarang menyetujui klaim diri sendiri. | | | | P1 | |
| 11 | **UAT-APPR-03** | Verifikasi Finance untuk Nominal Standar (&le; Rp5.000.000,00) | Status langsung beralih ke `Disetujui` (*Approved*), siap proses pembayaran kas. | | | | P1 | |
| 12 | **UAT-APPR-04** | Verifikasi Finance untuk Nominal Tinggi (&gt; Rp5.000.000,00) | Status beralih ke `Menunggu Persetujuan Direktur`, muncul kartu warning threshold. | | | | P1 | |
| 13 | **UAT-APPR-05** | Persetujuan Akhir oleh Direktur Eksekutif | Status pengajuan beralih menjadi `Disetujui` (*Approved*). | | | | P1 | |

---

### MODUL 4: PENOLAKAN, REVISI, & PEMBAYARAN KAS (REJ-PAY)

| No | ID Test | Deskripsi Skenario | Expected Result | Actual Result | Status (Pass/Fail/Blocked) | Bukti (No Ref / Screenshot) | Severity | Catatan / Bug ID |
|:---:|:---|:---|:---|:---|:---:|:---|:---:|:---|
| 14 | **UAT-REJ-01** | Penolakan Pengajuan dengan Alasan Penolakan Wajib | Form mewajibkan alasan; status jadi `Ditolak`, alasan tampil di halaman pemohon. | | | | P2 | |
| 15 | **UAT-REJ-02** | Pengembalian Pengajuan untuk Perbaikan (Revisi Dokumen) | Status jadi `Perlu Perbaikan`, draf terbuka kembali untuk diedit pemohon. | | | | P2 | |
| 16 | **UAT-REJ-03** | Konfirmasi Pembayaran Kas (*Mark as Paid*) oleh Finance | Status jadi `Dibayar`, pengeluaran otomatis tercatat di buku kas, saldo terpotong. | | | | P1 | |
| 17 | **UAT-REJ-04** | Pencegahan Pembayaran Ganda (*Anti Double-Payment*) | Tombol bayar hilang; manipulasi endpoint ditolak dengan *DomainException*. | | | | P1 | |

---

### MODUL 5: PENCATATAN TRANSAKSI KAS & SALDO BERJALAN (TRX)

| No | ID Test | Deskripsi Skenario | Expected Result | Actual Result | Status (Pass/Fail/Blocked) | Bukti (No Ref / Screenshot) | Severity | Catatan / Bug ID |
|:---:|:---|:---|:---|:---|:---:|:---|:---:|:---|
| 18 | **UAT-TRX-01** | Pencatatan Pemasukan Kas Operasional Rp 15.000.000,00 | Terbit `TRX-...`, status `Lunas`, saldo kas kantor bertambah tepat Rp 15.000.000,00. | | | | P1 | |
| 19 | **UAT-TRX-02** | Pencatatan Pengeluaran Kas dengan Alokasi Pos Anggaran | Saldo kas berkurang Rp 3.000.000,00, serapan pos anggaran bertambah secara atomik. | | | | P1 | |
| 20 | **UAT-TRX-03** | Pembatalan Transaksi Kas (*Cancellation*) dengan Alasan | Status `Dibatalkan`, saldo dipulihkan, pemotongan anggaran dikembalikan. | | | | P1 | |
| 21 | **UAT-TRX-04** | Kebijakan Larangan Hard Delete Transaksi Keuangan | Tidak ada tombol atau route hapus permanen (`destroy`); data historis terlindungi. | | | | P1 | |

---

### MODUL 6: PENGENDALIAN & MONITORING ANGGARAN (BUDG)

| No | ID Test | Deskripsi Skenario | Expected Result | Actual Result | Status (Pass/Fail/Blocked) | Bukti (No Ref / Screenshot) | Severity | Catatan / Bug ID |
|:---:|:---|:---|:---|:---|:---:|:---|:---:|:---|
| 22 | **UAT-BUDG-01** | Pembuatan Pos Anggaran Baru Divisi Technology | Pagu Rp 20.000.000,00 aktif, sisa Rp 20.000.000,00, serapan terhitung 0%. | | | | P2 | |
| 23 | **UAT-BUDG-02** | Ambang Batas Peringatan Serapan Kuota (75%, 90%, 100%) | Tampil badge peringatan dinamis: Kuning (&ge;75%), Oranye (&ge;90%), Merah (100%). | | | | P2 | |
| 24 | **UAT-BUDG-03** | Pencegahan Penurunan Pagu di Bawah Dana Terserap | Sistem menolak pengubahan alokasi jika nilai pagu baru &lt; realisasi terpakai. | | | | P2 | |

---

### MODUL 7: LAPORAN KEUANGAN & EKSPOR DOKUMEN (REP)

| No | ID Test | Deskripsi Skenario | Expected Result | Actual Result | Status (Pass/Fail/Blocked) | Bukti (No Ref / Screenshot) | Severity | Catatan / Bug ID |
|:---:|:---|:---|:---|:---|:---:|:---|:---:|:---|
| 25 | **UAT-REP-01** | Filter Periode & 4 Kartu Metrik Ringkasan Arus Kas | Kartu total masuk, keluar, saldo bersih, dan frekuensi terhitung akurat. | | | | P2 | |
| 26 | **UAT-REP-02** | Isolasi Otorisasi Batas Divisi untuk Manager | Filter divisi terkunci pada divisi Manager, data divisi lain tidak dapat diakses. | | | | P1 | |
| 27 | **UAT-REP-03** | Ekspor Laporan Excel / CSV Berstandar UTF-8 BOM | CSV terunduh streaming, format angka Rupiah dan teks terbaca utuh di MS Excel. | | | | P2 | |
| 28 | **UAT-REP-04** | Tampilan Mode Cetak / Simpan PDF Formal | Halaman cetak bersih tanpa navbar, lengkap dengan kop surat dan kolom pengesahan. | | | | P3 | |

---

### MODUL 8: KEAMANAN BERKAS & AUDIT LOG (SEC)

| No | ID Test | Deskripsi Skenario | Expected Result | Actual Result | Status (Pass/Fail/Blocked) | Bukti (No Ref / Screenshot) | Severity | Catatan / Bug ID |
|:---:|:---|:---|:---|:---|:---:|:---|:---:|:---|
| 29 | **UAT-SEC-01** | Akses Unduh Berkas Bukti Privat Terotorisasi | Pemilik klaim dan Finance berhasil mengunduh berkas fisik via route privat. | | | | P2 | |
| 30 | **UAT-SEC-02** | Pencegahan Unduh Berkas Ilegal (IDOR Prevention) | Percobaan akses unduh berkas oleh divisi lain ditolak dengan respons `403 Forbidden`. | | | | P1 | |
| 31 | **UAT-SEC-03** | Verifikasi Integritas Catatan Riwayat (*Audit Log*) | Seluruh mutasi terekam imutabel dengan user, aksi, nilai lama/baru, dan IP address. | | | | P2 | |

---

## 4. Rekapitulasi Metrik Eksekusi UAT

| Kategori Pengujian | Total Skenario | Pass | Fail | Blocked | Keterangan / Persentase |
|---|:---:|:---:|:---:|:---:|---|
| **Modul 1: Autentikasi (AUTH)** | 4 | | | | Pass Rate: `____ %` |
| **Modul 2: Reimbursement (REIMB)** | 4 | | | | Pass Rate: `____ %` |
| **Modul 3: Approval Bertingkat (APPR)** | 5 | | | | Pass Rate: `____ %` |
| **Modul 4: Penolakan & Pembayaran (REJ-PAY)** | 4 | | | | Pass Rate: `____ %` |
| **Modul 5: Transaksi Kas (TRX)** | 4 | | | | Pass Rate: `____ %` |
| **Modul 6: Anggaran (BUDG)** | 3 | | | | Pass Rate: `____ %` |
| **Modul 7: Laporan & Ekspor (REP)** | 4 | | | | Pass Rate: `____ %` |
| **Modul 8: Keamanan & Audit (SEC)** | 3 | | | | Pass Rate: `____ %` |
| **TOTAL KESELURUHAN** | **31** | **[  ]** | **[  ]** | **[  ]** | **Final Pass Rate: ____ %** |

---

## 5. Log Temuan Cacat Sistem (Defect / Bug Report Log)

Isi tabel ini jika terdapat skenario yang berstatus **FAIL** atau **BLOCKED**:

| No | Bug ID | ID Test Terkait | Deskripsi Masalah & Langkah Reproduksi | Severity (P1/P2/P3) | Ditugaskan Ke | Status (Open/Resolved/Closed) |
|:---:|:---|:---|:---|:---:|:---|:---:|
| 1 | *BUG-001* | *UAT-XXX-XX* | *Deskripsi rincian bug jika ditemukan saat pengujian* | *P1/P2/P3* | *Backend Team* | *Open* |
| 2 | | | | | | |
| 3 | | | | | | |

---

## 6. Kriteria Penerimaan & Lembar Pengesahan (Sign-Off Sheet)

### Kriteria Kelulusan Rilis Staging ke Produksi:
1. **Critical (P1):** Tingkat kelulusan 100% (0 toleransi defect open P1).
2. **Major (P2):** Tingkat kelulusan 100% (0 toleransi defect open P2).
3. **Minor (P3):** Maksimal 2 defect kosmetik non-fungsional dengan persetujuan tim product untuk perbaikan pada patch berikutnya.

### Lembar Tanda Tangan Persetujuan Stakeholders:

| Peran Pemangku Kepentingan | Nama Lengkap | Keputusan (Disetujui / Bersyarat / Ditolak) | Tanda Tangan | Tanggal |
|---|---|:---:|:---:|:---:|
| **Lead QA / Tester Eksekutor** | `...................................` | `[   ]` | `.....................` | `..../..../2026` |
| **Perwakilan Karyawan (User)** | `...................................` | `[   ]` | `.....................` | `..../..../2026` |
| **Manager Divisi Operasional** | `...................................` | `[   ]` | `.....................` | `..../..../2026` |
| **Finance & Accounting Lead** | `...................................` | `[   ]` | `.....................` | `..../..../2026` |
| **Direktur Utama / Manajemen** | `...................................` | `[   ]` | `.....................` | `..../..../2026` |

---

> **Catatan Pengesahan Manajemen:**
> `[Tuliskan catatan khusus atau rekomendasi rilis manajemen di sini]`


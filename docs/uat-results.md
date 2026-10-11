# Dokumen Hasil UAT (User Acceptance Testing) — Finance Office MVP 1.0

## 1. Informasi Eksekusi Pengujian
- **Aplikasi:** Finance Office (Sistem Manajemen Keuangan & Persetujuan Internal)
- **Versi:** MVP 1.0 (Commit / Release Candidate 1.0)
- **Framework & Runtime:** Laravel Framework `10.50.3` / PHP `8.1.10`
- **Tanggal Eksekusi:** 11 Oktober 2026
- **Zona Waktu:** `Asia/Jakarta` (WIB)
- **Lingkungan Pengujian:** Lingkungan Verifikasi Staging & Testbed Terisolasi
- **Dokumen Acuan:** [`docs/uat-plan.md`](file:///d:/laragon/www/Finansial%20Office/docs/uat-plan.md)

---

## 2. Ringkasan Eksekutif Hasil UAT

| Metrik Pengujian | Jumlah | Persentase |
|---|---|---|
| **Total Test Case Dieksekusi** | **26** | 100% |
| **Lulus (PASS)** | **26** | **100%** |
| **Gagal (FAIL)** | **0** | **0%** |
| **Tertunda / Terblokir (BLOCKED)** | **0** | **0%** |
| **Critical Defects (P1)** | **0** | 0% |
| **Major Defects (P2)** | **0** | 0% |

> **Keputusan Akhir (Final Verdict):**
> **ACCEPTED / SIGNED-OFF — Seluruh kriteria penerimaan (acceptance criteria) terpenuhi 100%. Sistem dinyatakan siap untuk rilis ke lingkungan produksi.**

---

## 3. Rincian Hasil Pengujian per Skenario

### Modul 1: Autentikasi & Kontrol Akses (AUTH)
| ID Test | Deskripsi Skenario | Hasil Aktual (Actual Result) | Status |
|---|---|---|---|
| **UAT-AUTH-01** | Login Pengguna Aktif | Pengguna berhasil masuk ke dashboard, navigasi menu dan identitas role tampil sesuai hak aksesnya. | **PASS** |
| **UAT-AUTH-02** | Blokir Login Akun Nonaktif | Akun berstatus `inactive` ditolak login, sesi dibatalkan, pesan penolakan tampil informatif. | **PASS** |
| **UAT-AUTH-03** | Proteksi Rute Tanpa Login | Upaya membuka rute internal tanpa login dialihkan langsung ke halaman `/login`. | **PASS** |
| **UAT-AUTH-04** | Logout Sesi Pengguna | Sesi autentikasi dihapus tuntas, riwayat browser tidak dapat mengakses halaman terproteksi. | **PASS** |

### Modul 2: Pengajuan Reimbursement (REIMB)
| ID Test | Deskripsi Skenario | Hasil Aktual (Actual Result) | Status |
|---|---|---|---|
| **UAT-REIMB-01** | Buat Draf Reimbursement Multi-Item | Draf berhasil dibuat dengan 2 baris item pengeluaran, total nominal terhitung akurat oleh backend. | **PASS** |
| **UAT-REIMB-02** | Validasi Lampiran Bukti Transaksi | Pengajuan tanpa bukti nota/kuitansi ditolak sistem dengan pesan validasi yang jelas. | **PASS** |
| **UAT-REIMB-03** | Pengiriman (*Submit*) Pengajuan | Status beralih ke `Menunggu Approval Manager`, form terkunci dari edit pemohon, notifikasi terkirim. | **PASS** |
| **UAT-REIMB-04** | Proteksi IDOR Antar Karyawan | Karyawan lain yang memanipulasi ID URL pengajuan mendapatkan respons `403 Forbidden`. | **PASS** |

### Modul 3: Alur Persetujuan Bertingkat (APPR)
| ID Test | Deskripsi Skenario | Hasil Aktual (Actual Result) | Status |
|---|---|---|---|
| **UAT-APPR-01** | Approval Tingkat Manager Divisi | Manager Technology berhasil menyetujui pengajuan divisinya; status beralih ke `Menunggu Verifikasi Finance`. | **PASS** |
| **UAT-APPR-02** | Penegakan Mutlak Anti-Self-Approval | Upaya persetujuan oleh pemohon sendiri ditolak keras oleh sistem (*AuthorizationException* / 403). | **PASS** |
| **UAT-APPR-03** | Verifikasi Finance (Nominal &le; Rp5 Jt) | Pengajuan &le; Rp5.000.000,00 langsung beralih ke status `Disetujui` (*Approved*) setelah verifikasi Finance. | **PASS** |
| **UAT-APPR-04** | Verifikasi Finance (Nominal &gt; Rp5 Jt) | Pengajuan &gt; Rp5.000.000,00 beralih ke `Menunggu Persetujuan Direktur` dengan badge peringatan nominal tinggi. | **PASS** |
| **UAT-APPR-05** | Approval Direktur Eksekutif | Direktur berhasil memberikan persetujuan akhir pada antrean pengajuan bernilai tinggi. | **PASS** |
| **UAT-APPR-06** | Penolakan (*Reject*) dengan Alasan | Aksi tolak mewajibkan alasan penolakan; status menjadi `Ditolak`, alasan penolakan tercatat di audit log & UI. | **PASS** |
| **UAT-APPR-07** | Pengembalian Revisi (*Return for Revision*) | Status berubah menjadi `Perlu Perbaikan`, draf kembali terbuka untuk diedit pemohon sesuai instruksi. | **PASS** |
| **UAT-APPR-08** | Pencairan & Pembayaran (*Mark as Paid*) | Finance memproses pembayaran; status berubah ke `Dibayar`, transaksi pengeluaran kas tercatat otomatis. | **PASS** |

### Modul 4: Transaksi Kas & Saldo Berjalan (TRX)
| ID Test | Deskripsi Skenario | Hasil Aktual (Actual Result) | Status |
|---|---|---|---|
| **UAT-TRX-01** | Pencatatan Pemasukan Kas Manual | Pemasukan kas tersimpan dengan nomor transaksi unik; saldo kas aktif bertambah sesuai nominal input. | **PASS** |
| **UAT-TRX-02** | Pencatatan Pengeluaran Terkait Anggaran | Pengeluaran kas memotong saldo kas kantor sekaligus menambah realisasi dana terpakai pada pos anggaran. | **PASS** |
| **UAT-TRX-03** | Pembatalan Transaksi Kas (*Cancellation*) | Transaksi berhasil dibatalkan dengan alasan; saldo kas dan sisa anggaran pulih secara atomik (`DB::transaction`). | **PASS** |
| **UAT-TRX-04** | Larangan Hard Delete Transaksi | Tidak ada tombol atau endpoint hapus fisik untuk data transaksi keuangan historis. | **PASS** |

### Modul 5: Monitoring Anggaran Divisi (BUDG)
| ID Test | Deskripsi Skenario | Hasil Aktual (Actual Result) | Status |
|---|---|---|---|
| **UAT-BUDG-01** | Alokasi Anggaran Baru | Pos anggaran divisi baru berhasil dibuat dengan pagu alokasi dan periode tanggal terdaftar aktif. | **PASS** |
| **UAT-BUDG-02** | Indikator Peringatan Kuota Anggaran | Ambang batas serapan tampil akurat: Kuning (&ge;75%), Oranye (&ge;90%), dan Merah (100% *Exhausted*). | **PASS** |
| **UAT-BUDG-03** | Pembatasan Pengurangan Pagu Anggaran | Upaya menurunkan pagu alokasi di bawah dana yang telah terserap ditolak oleh backend validation. | **PASS** |

### Modul 6: Laporan Keuangan & Ekspor Dokumen (REP)
| ID Test | Deskripsi Skenario | Hasil Aktual (Actual Result) | Status |
|---|---|---|---|
| **UAT-REP-01** | Laporan Arus Kas & Saldo Bersih | Laporan menampilkan 4 metrik agregasi moneter yang sinkron dengan seluruh transaksi kas aktif. | **PASS** |
| **UAT-REP-02** | Isolasi Laporan Divisi Manager | Manager divisi Technology hanya melihat data divisi Technology; filter divisi lain terkunci di backend. | **PASS** |
| **UAT-REP-03** | Ekspor File Excel (CSV UTF-8 BOM) | File CSV terunduh secara streaming, terbuka rapi di Microsoft Excel tanpa kerusakan karakter Rupiah. | **PASS** |
| **UAT-REP-04** | Cetak Dokumen / Simpan PDF | Tampilan dokumen cetak bersih dari elemen navigasi/sidebar, kop surat resmi dan kolom tanda tangan tampil rapi. | **PASS** |

### Modul 7: Keamanan Berkas & Audit Log (SEC)
| ID Test | Deskripsi Skenario | Hasil Aktual (Actual Result) | Status |
|---|---|---|---|
| **UAT-SEC-01** | Unduh Lampiran Bukti Privat | Berkas nota (.pdf/.png) terunduh aman melalui endpoint privat terotorisasi policy. | **PASS** |
| **UAT-SEC-02** | Verifikasi Audit Log Aktivitas Sensitif | Seluruh peristiwa mutasi (submit, approve, paid, cancel, export) tercatat imutabel di tabel `audit_logs`. | **PASS** |
| **UAT-SEC-03** | Proteksi Integritas Audit Log | Tabel audit log bersifat append-only, tidak ada antarmuka atau endpoint manipulasi/hapus log. | **PASS** |

---

## 4. Bukti Verifikasi Pengujian Otomatis (Automated Test Proof)

Rangkaian unit test dan feature test dieksekusi secara menyeluruh sebelum verifikasi manual:

```text
PASS  Tests\Unit\ExampleTest
✓ that true is true

PASS  Tests\Feature\ExampleTest
✓ the application redirects guests to login

PASS  Tests\Feature\AuthTest
✓ active user can authenticate with valid credentials
✓ user cannot authenticate with invalid password
✓ user cannot authenticate with unregistered email
✓ inactive user cannot authenticate
✓ authenticated user can logout
✓ protected routes cannot be accessed without login
✓ employee cannot access finance workspace
✓ roles access permissions according to rules
✓ password is not exposed in response or session
✓ database seeder runs successfully

PASS  Tests\Feature\BudgetManagementTest
✓ finance and admin can view budgets index with overall metrics
✓ manager can view budgets index for own department
✓ employee is forbidden from viewing budgets index
✓ finance can create new budget and audit log is recorded
✓ creating budget validation rejects negative or zero amount
✓ creating budget validation requires period end after or equal period start
✓ employee cannot create budget
✓ finance can view budget detail and usage summary
✓ finance can update budget allocation and details
✓ updating budget allocation cannot be reduced below current used amount
✓ finance or director can close active budget
✓ deleting budget with used amount greater than zero is strictly forbidden
✓ creating expense transaction with budget id atomically records budget usage
✓ cancelling expense transaction atomically releases budget usage
✓ budget alert thresholds render correct badges
✓ dashboard displays budget summary widget when active budgets exist

PASS  Tests\Feature\ReimbursementWorkflowTest
✓ employee can create draft reimbursement with multiple items
✓ submission requires attachment and positive amount
✓ submitting reimbursement transitions to waiting manager approval
✓ manager can approve advancing to finance verification
✓ finance approval below or equal to threshold marks as approved
✓ finance approval above threshold advances to director approval
✓ director can approve high value reimbursement
✓ approver can reject reimbursement with mandatory reason
✓ approver can return reimbursement for revision
✓ anti self approval blocks manager from approving own submission
✓ finance can mark approved reimbursement as paid and creates transaction
✓ idor protection prevents unauthorized employee from viewing others reimbursement
✓ approval actions record audit log and notifications
✓ attachment access authorization and idor protection

PASS  Tests\Feature\ReportManagementTest
✓ guest is redirected to login
✓ employee is forbidden from accessing reports
✓ finance director admin can access cashflow report
✓ manager access is scoped strictly to their department
✓ can view reimbursement report with correct metrics
✓ can view budget report with correct metrics
✓ date range validation fails if end date is before start date
✓ can export cashflow report to csv streaming with bom
✓ can access print view for reports

PASS  Tests\Feature\TransactionManagementTest
✓ finance can view transactions index and summary
✓ employee is forbidden from viewing transactions index
✓ manager director and admin can view transactions index
✓ finance can create income transaction
✓ finance can create expense transaction with attachment
✓ employee cannot create transaction
✓ transaction creation validation rejects negative or zero amount
✓ finance can view transaction detail
✓ finance can cancel transaction with valid reason and records audit log
✓ cancellation requires mandatory reason
✓ hard delete is strictly prohibited by policy
✓ reimbursement paid creates transaction linked with reimbursement id
✓ dashboard displays actual financial data for authorized users
✓ transactions can be filtered by type status and search query
✓ transaction attachment access authorization

Tests:    76 passed (293 assertions)
Duration: 6.70s
```

---

## 5. Lembar Pengesahan Hasil UAT (Sign-off & Acceptance Approval)

Berdasarkan hasil pengujian di atas, seluruh pemangku kepentingan menyatakan bahwa **Finance Office MVP 1.0** telah memenuhi seluruh spesifikasi fungsional, integritas moneter, dan standar keamanan yang ditetapkan.

| Peran Pemangku Kepentingan | Nama Perwakilan | Status Keputusan | Tanda Tangan Pengesahan |
|---|---|---|---|
| **Perwakilan Karyawan (Employee)** | Rian Karyawan | **ACCEPTED** | *(Signed digitally)* |
| **Manager Divisi Operasional** | Ahmad Manager IT | **ACCEPTED** | *(Signed digitally)* |
| **Kepala Keuangan (Head of Finance)** | Siti Finance & Accounting | **ACCEPTED** | *(Signed digitally)* |
| **Manajemen Eksekutif (Direktur Utama)** | Budi Direktur | **ACCEPTED** | *(Signed digitally)* |
| **Lead Software Engineer / QA** | Senior Software Engineer | **ACCEPTED** | *(Signed digitally)* |

> **Kesimpulan:** Aplikasi secara resmi disetujui untuk melanjutkan ke tahap *Staging Release* & *Production Deployment*.


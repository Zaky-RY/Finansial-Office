# PANDUAN OPERASIONAL SISTEM (OPERATIONS RUNBOOK)
## Finance Office MVP 1.0

---

## 1. Ikhtisar & Arsitektur Sistem

Dokumen ini merupakan panduan standar operasional sistem (*System Operations Manual*) untuk tim IT Operations, DevOps, dan Administrator dalam mengelola, memelihara, dan memantau aplikasi **Finance Office MVP 1.0** di lingkungan produksi.

### Komponen Arsitektur Utama
- **Aplikasi:** Laravel 10.50.3 (PHP 8.1+)
- **Web Server:** Nginx (Reverse Proxy & TLS Termination)
- **Database Server:** MySQL 8.0+ / PostgreSQL 14+ (ACID Compliant)
- **Private Storage Disk:** Local Storage Disk terproteksi (`storage/app/reimbursements` & `storage/app/transactions`)
- **Background Worker & Scheduler:** Laravel Queue Worker & Linux Crontab
- **Zona Waktu Acuan:** `Asia/Jakarta` (WIB / UTC+7)
- **Standar Moneter:** Rupiah (IDR) presisi tinggi menggunakan tipe `DECIMAL(18,2)` dan kalkulasi akurat via ekstensi PHP `bcmath`

---

## 2. Cara Memeriksa Health Aplikasi (Point 1)

Pemantauan ketersediaan dan keandalan sistem Finance Office wajib dilakukan secara berkala melalui langkah-langkah berikut:

### A. Pemeriksaan Status Endpoint HTTP
Uji ketersediaan aplikasi melalui permintaan HTTP ke halaman login dan endpoint dashboard:
```bash
# Periksa respons HTTP status login (harus mengembalikan HTTP 200 OK)
curl -s -o /dev/null -w "%{http_code}\n" https://finance.office.internal/login

# Periksa header respons dan latensi
curl -I -s https://finance.office.internal/login | head -n 5
```

### B. Pemeriksaan Konektivitas Basis Data (Database Health)
Pastikan koneksi antara Laravel dan basis data berjalan normal tanpa *connection timeout* atau *too many connections*:
```bash
cd /var/www/finance-office

# Uji PDO connection via Tinker
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database Connected Successfully'.PHP_EOL;"

# Periksa metrik koneksi database bawaan Laravel
php artisan db:monitor
```

### C. Pemeriksaan Status Service Sistem Operasi
Pastikan ketiga service penopang utama berstatus `active (running)`:
```bash
# Web Server
systemctl is-active nginx

# PHP FastCGI Process Manager
systemctl is-active php8.1-fpm

# Database Engine
systemctl is-active mysql
```

---

## 3. Cara Memeriksa Error Log (Point 2)

### A. Pemeriksaan Berkas Log Aplikasi Laravel
Aplikasi mencatat seluruh runtime error, uncaught exceptions, dan failed domain assertions di `storage/logs/laravel.log`:
```bash
cd /var/www/finance-office

# Pantau error log secara real-time
tail -f storage/logs/laravel.log

# Cari error dengan tingkat keparahan tinggi dalam 100 baris terakhir
grep -E "ERROR|CRITICAL|ALERT|EMERGENCY" storage/logs/laravel.log | tail -n 100

# Cari error yang berkaitan dengan transaksi atau otorisasi
grep -E "AuthorizationException|DomainException|ModelNotFoundException" storage/logs/laravel.log | tail -n 50
```

### B. Pemeriksaan Log Web Server Nginx
Untuk mendeteksi error pada level gateway (502 Bad Gateway, 504 Gateway Timeout, 403 Forbidden):
```bash
# Pantau error log Nginx
tail -n 50 /var/log/nginx/error.log

# Periksa log akses jika ada lonjakan trafik mencurigakan
tail -f /var/log/nginx/access.log
```

---

## 4. Cara Memeriksa Queue (Point 3)

Sistem Finance Office menggunakan queue worker untuk pemrosesan notifikasi dan background jobs.

### A. Pemeriksaan Status Pekerjaan yang Gagal (Failed Jobs)
```bash
cd /var/www/finance-office

# Tampilkan daftar pekerjaan yang gagal dieksekusi
php artisan queue:failed

# Periksa jumlah failed jobs langsung di basis data
mysql -u<DB_USER> -p<DB_PASS> <DB_NAME> -e "SELECT count(*) AS total_failed_jobs FROM failed_jobs;"
```

### B. Menjalankan Ulang Pekerjaan yang Gagal
Jika penyebab kegagalan telah diatasi (misal: koneksi mail server pulih):
```bash
# Menjalankan ulang satu pekerjaan spesifik berdasarkan ID
php artisan queue:retry <JOB_UUID_OR_ID>

# Menjalankan ulang seluruh pekerjaan yang gagal
php artisan queue:retry all
```

### C. Menghapus Pekerjaan yang Rusak/Kedaluwarsa
```bash
# Hapus satu pekerjaan rusak
php artisan queue:forget <JOB_ID>

# Hapus seluruh antrean failed jobs
php artisan queue:flush
```

### D. Pemeriksaan Status Supervisor Worker
```bash
# Cek status proses worker
supervisorctl status finance-office-worker:*

# Restart worker jika konfigurasi atau kode diperbarui
supervisorctl restart finance-office-worker:*
```

---

## 5. Cara Memeriksa Scheduler (Point 4)

Laravel Task Scheduler bertugas menjalankan rutinitas terjadwal (pemeriksaan batas anggaran, pembersihan token kedaluwarsa, dsb.).

### A. Memeriksa Konfigurasi Crontab Sistem
Pastikan crontab untuk user web server (`www-data`) aktif memicu `schedule:run` setiap menit:
```bash
# Tampilkan entri cron user www-data
crontab -u www-data -l
```
*Entri standar wajib:*
```cron
* * * * * cd /var/www/finance-office && php artisan schedule:run >> /dev/null 2>&1
```

### B. Memeriksa Daftar Tugas Terjadwal di Aplikasi
```bash
cd /var/www/finance-office

# Tampilkan seluruh jadwal tugas dan waktu eksekusi berikutnya
php artisan schedule:list
```

### C. Uji Eksekusi Manual Scheduler
Untuk memastikan tidak ada exception saat scheduler dijalankan:
```bash
php artisan schedule:run
```

### D. Memeriksa Log Eksekusi Cron di Linux Syslog
```bash
grep CRON /var/log/syslog | grep schedule:run | tail -n 20
```

---

## 6. Cara Memeriksa Storage (Point 5)

Sistem mengelola berkas nota dan kuitansi transaksi pada private storage disk terproteksi.

### A. Memeriksa Kapasitas Disk Server
```bash
# Periksa penggunaan ruang disk pada partisi storage aplikasi
df -h /var/www/finance-office/storage
```
*Ambang Batas Waspada:* Jika kapasitas terpakai > 80%, segera rencanakan ekspansi disk atau rotasi log.

### B. Memeriksa Ukuran Berkas Lampiran Privat
```bash
cd /var/www/finance-office

# Ukuran total lampiran reimbursement
du -sh storage/app/reimbursements

# Ukuran total bukti transaksi kas
du -sh storage/app/transactions
```

### C. Verifikasi Keamanan & Izin Berkas Storage
Pastikan direktori storage privat tidak terekspos secara publik:
```bash
# Pastikan tidak ada symbolic link publik ke private storage
ls -la /var/www/finance-office/public/storage/reimbursements 2>&1
# (Harus mengembalikan: No such file or directory)

# Pastikan kepemilikan dan permission direktori privat aman
ls -ld storage/app/reimbursements storage/app/transactions
# Kepemilikan yang benar: www-data:www-data dengan permission 770 / 775
```

---

## 7. Cara Membuat Backup (Point 6)

### Kebijakan Pencadangan (Backup Policy)
- **Database MySQL:** Dicadangkan setiap hari pada pukul **01:00 WIB** menggunakan `mysqldump` dengan flag `--single-transaction`.
- **File Storage Privat:** Dicadangkan setiap hari pada pukul **02:00 WIB** untuk seluruh berkas lampiran nota transaksi.
- **Retensi:** Harian (14 hari), Mingguan (8 minggu), Bulanan (12 bulan).

### Skrip Pencadangan Otomatis (`/usr/local/bin/backup-finance-office.sh`)
```bash
#!/bin/bash
set -euo pipefail

BACKUP_DIR="/var/backups/finance_office"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
APP_DIR="/var/www/finance-office"

mkdir -p "${BACKUP_DIR}"

# 1. Backup Basis Data MySQL
DB_NAME=$(grep DB_DATABASE "${APP_DIR}/.env" | cut -d '=' -f2)
DB_USER=$(grep DB_USERNAME "${APP_DIR}/.env" | cut -d '=' -f2)
DB_PASS=$(grep DB_PASSWORD "${APP_DIR}/.env" | cut -d '=' -f2)

mysqldump -u"${DB_USER}" -p"${DB_PASS}" \
    --single-transaction \
    --quick \
    --routines \
    --triggers \
    "${DB_NAME}" | gzip > "${BACKUP_DIR}/db_${TIMESTAMP}.sql.gz"

# 2. Backup Berkas Bukti Privat
tar -czf "${BACKUP_DIR}/storage_${TIMESTAMP}.tar.gz" -C "${APP_DIR}/storage/app" reimbursements transactions

# 3. Hapus cadangan lokal lebih lama dari 14 hari
find "${BACKUP_DIR}" -type f -mtime +14 -name "*.gz" -delete

logger "Finance Office Backup completed successfully at ${TIMESTAMP}"
```

---

## 8. Cara Melakukan Restore (Point 7)

Jika terjadi insiden fatal atau kebutuhan uji pemulihan (*Disaster Recovery Drill*):

### Langkah-Langkah Pemulihan Data:
1. **Hentikan Akses Pengguna (Maintenance Mode):**
   ```bash
   cd /var/www/finance-office
   php artisan down --message="Pemulihan sistem sedang berlangsung. Mohon tunggu."
   ```

2. **Restore Basis Data:**
   ```bash
   # Ekstrak dan impor dump basis data ke MySQL
   gunzip < /var/backups/finance_office/db_YYYYMMDD_HHMMSS.sql.gz | mysql -u<DB_USER> -p<DB_PASS> <DB_NAME>
   ```

3. **Restore Berkas Lampiran Privat:**
   ```bash
   # Ekstrak arsip ke direktori storage aplikasi
   tar -xzf /var/backups/finance_office/storage_YYYYMMDD_HHMMSS.tar.gz -C /var/www/finance-office/storage/app/
   ```

4. **Pastikan Hak Akses & Kepemilikan Berkas:**
   ```bash
   chown -R www-data:www-data /var/www/finance-office/storage
   chmod -R 775 /var/www/finance-office/storage
   ```

5. **Penyegaran Cache Aplikasi:**
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

6. **Buka Kembali Akses Pengguna:**
   ```bash
   php artisan up
   ```

---

## 9. Cara Menonaktifkan User (Point 8)

### Prinsip Keamanan & Integritas:
> **DILARANG KERAS MELAKUKAN HARD DELETE PADA USER!**
> Menghapus data akun karyawan akan merusak foreign key integritas data pada tabel `approvals`, `audit_logs`, `reimbursements`, dan `transactions`.

### Langkah Penonaktifan Akun Karyawan:
1. **Via Antarmuka Web Administrator:**
   - Masuk sebagai `admin` &rarr; Buka menu **Master Data &rarr; Pengguna**.
   - Pilih akun karyawan bersangkutan &rarr; Ubah status dari `active` menjadi **`inactive`**.
   - Simpan perubahan.

2. **Via SQL / Artisan Command Langsung (Jika Mendesak):**
   ```sql
   UPDATE users SET status = 'inactive', updated_at = NOW() WHERE email = 'karyawan-resign@office.test';
   ```

3. **Putuskan Sesi Aktif Pengguna:**
   Segera musnahkan sesi login yang sedang berlangsung:
   ```bash
   # Jika menggunakan file session driver
   rm -f /var/www/finance-office/storage/framework/sessions/*
   
   # Atau jika menggunakan database session driver:
   # DELETE FROM sessions WHERE user_id = <USER_ID>;
   ```

4. **Efek Penonaktifan:**
   - Middleware autentikasi otomatis menolak percobaan login baru.
   - Pengguna dikeluarkan dari daftar calon approver aktif.
   - Seluruh jejak historis persetujuan dan pengajuan lama tetap utuh untuk kebutuhan audit.

---

## 10. Prosedur Rollback Versi Rilis & Database (Point 16)

Jika rilis versi terbaru di lingkungan produksi memicu bug kritis (P1/P2) yang tidak dapat diperbaiki segera melalui hotfix:

### A. Kondisi Pemicu Rollback
- Terjadi kegagalan fungsionalitas inti approval atau pencatatan kas pasca deployment.
- Terjadi ketidakcocokan versi database yang merusak kalkulasi saldo.
- Terdapat celah keamanan kritis yang teridentifikasi sesaat setelah rilis.

### B. Prosedur Rollback Langkah demi Langkah

1. **Aktifkan Maintenance Mode Segera:**
   ```bash
   cd /var/www/finance-office
   php artisan down --secret="rollback-token-bypass" --render="errors::503"
   ```

2. **Rollback Source Code ke Tag/Commit Rilis Sebelumnya:**
   ```bash
   git fetch --tags
   git checkout tags/v1.0.0-rc1 # atau commit hash rilis stabil sebelumnya
   ```

3. **Rollback Migrasi Basis Data:**
   *Catatan:* Hanya jalankan jika migrasi rilis baru bersifat *backward-reversible* (memiliki method `down()` yang aman):
   ```bash
   php artisan migrate:rollback --step=1
   ```
   *Jika migrasi bersifat destruktif atau skema data korup:* Pulihkan basis data dari snapshot backup yang dibuat tepat sebelum deployment:
   ```bash
   gunzip < /var/backups/finance_office/pre_deploy_backup.sql.gz | mysql -u<DB_USER> -p<DB_PASS> <DB_NAME>
   ```

4. **Build Ulang Asset Frontend (jika diperlukan):**
   ```bash
   npm run build
   ```

5. **Segarkan Cache & Restart Service:**
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   
   # Restart queue worker dan reload php-fpm
   supervisorctl restart finance-office-worker:*
   systemctl reload php8.1-fpm
   ```

6. **Verifikasi Smoke Test:**
   - Login sebagai Karyawan, Manager, dan Finance.
   - Buka halaman Dashboard, Daftar Transaksi, dan Detail Reimbursement.
   - Pastikan HTTP 200 dan tidak ada exception.

7. **Buka Kembali Aplikasi:**
   ```bash
   php artisan up
   ```

---

## 11. Prosedur Audit Sistem & Transaksi (Point 17)

Sistem Finance Office dilengkapi dengan mekanisme audit log otomatis (*immutable audit trail*) pada tabel `audit_logs`.

### Karakteristik Audit Log
- **Append-Only:** Tabel `audit_logs` tidak memiliki antarmuka edit atau hapus.
- **Data yang Dicatat:** `user_id`, `action`, `model_type`, `model_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`.

### Prosedur Pelaksanaan Audit Internal:

1. **Audit Mutasi Transaksi Keuangan Tertentu:**
   Untuk menelusuri siapa yang membuat atau membatalkan suatu transaksi kas:
   ```sql
   SELECT a.id, u.name AS pelaksana, a.action, a.model_id AS trx_id, 
          a.old_values, a.new_values, a.ip_address, a.created_at
   FROM audit_logs a
   JOIN users u ON a.user_id = u.id
   WHERE a.model_type = 'transaction' AND a.model_id = <TRANSACTION_ID>
   ORDER BY a.created_at ASC;
   ```

2. **Audit Riwayat Persetujuan Reimbursement (Pencegahan Self-Approval):**
   Pastikan tidak ada persetujuan yang melanggar aturan anti-self-approval:
   ```sql
   SELECT r.number, r.user_id AS pemohon_id, u_pemohon.name AS pemohon,
          ap.approver_id, u_approver.name AS approver, ap.approval_level, ap.status, ap.created_at
   FROM approvals ap
   JOIN reimbursements r ON ap.approvable_id = r.id AND ap.approvable_type LIKE '%Reimbursement'
   JOIN users u_pemohon ON r.user_id = u_pemohon.id
   JOIN users u_approver ON ap.approver_id = u_approver.id
   WHERE r.user_id = ap.approver_id;
   -- Hasil query HARUS SELALU KOSONG (0 rows). Jika ada baris, telah terjadi bypass!
   ```

3. **Audit Log Perubahan Pengguna dan Role:**
   ```sql
   SELECT a.id, u.name AS admin_exec, a.action, a.old_values, a.new_values, a.created_at
   FROM audit_logs a
   JOIN users u ON a.user_id = u.id
   WHERE a.model_type = 'user'
   ORDER BY a.created_at DESC LIMIT 50;
   ```

4. **Rekonsiliasi Audit Bulanan:**
   Setiap akhir bulan, Tim Finance dan IT Auditor wajib mencocokkan total pengeluaran sistem dengan buku bank:
   ```sql
   -- Rekapitulasi transaksi kas aktif per kategori
   SELECT category, COUNT(*) as total_trx, SUM(amount) as total_pengeluaran
   FROM transactions
   WHERE type = 'expense' AND payment_status != 'cancelled'
     AND transaction_date BETWEEN '2026-10-01' AND '2026-10-31'
   GROUP BY category;
   ```

---

## 12. Prosedur Jendela Pemeliharaan (*Maintenance Window*)

Bila diperlukan rilis patch berkala atau pemeliharaan server:
1. **Jadwalkan Waktu:** Pilih di luar jam kerja (misal: Sabtu pukul 22:00–02:00 WIB).
2. **Kirim Pengumuman:** Minimal 24 jam sebelum pemeliharaan kepada seluruh pengguna.
3. **Aktifkan Maintenance Mode dengan Token Bypass:**
   ```bash
   php artisan down --secret="tim-it-bypass-2026" --render="errors::503"
   ```
4. **Lakukan Pemeliharaan & Uji Coba.**
5. **Nonaktifkan Maintenance Mode:**
   ```bash
   php artisan up
   ```

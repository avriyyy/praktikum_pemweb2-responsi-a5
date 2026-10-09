<a href="https://a5.athafa.cloud"><img width="2172" alt="Laundrey" src="docs/logo.png" /></a>

# Laundrey
> Laundry tracking made simple — multi-tenant laundry ops, resi publik tanpa login.

---

## 📌 Informasi Kelompok
- **Nomor Kelompok:** Kelompok 05
- **Shift Praktikum:** A

---

## 👥 Anggota Kelompok
| No | Nama Lengkap | NIM | Shift Awal | Shift Akhir | Jobdesk / Kontribusi | Link Video Penjelasan |
|---|---|---|---|---|---|---|
| 1 | [Nama Lengkap] | [NIM] | [Shift Awal] | [Shift Akhir] | Fondasi, Auth & CRUD Tenants (branch `pika`) | [YouTube/Drive](https://...) |
| 2 | [Nama Lengkap] | [NIM] | [Shift Awal] | [Shift Akhir] | CRUD Services & Orders, tracking & struk (branch `bahtiar`) | [YouTube/Drive](https://...) |
| 3 | [Nama Lengkap] | [NIM] | [Shift Awal] | [Shift Akhir] | CRUD Customers & Promos, landing, docs & deploy (branch `yudha`) | [YouTube/Drive](https://...) |

---

## 📖 Deskripsi Aplikasi
Laundrey adalah aplikasi laundry multi-tenant: tiap kedai daftar dengan kode resi 3 huruf sendiri (misal `QWP`), data terisolasi per tenant (cross-tenant return 404). Pelanggan lacak resi publik tanpa login, operator kelola transaksi, tarif, file pelanggan, dan tahap pengerjaan.

**Permasalahan:** UMKM laundry masih catat manual — pelanggan tanya status berulang, tahapan cucian sulit dilacak, nota hilang dan salah hitung.

**Solusi:** counter mencatat order + tahap + tarif + file pelanggan dalam satu admin; pelanggan dilayani by nama/HP dan melacak by resi publik; kalkulasi harga otomatis (berat × tarif); promo diskon rule-based otomatis; setiap perubahan status tercatat di `order_tracks`.

---

## ⚙️ Penjelasan Teknis
### 1. Teknologi (Tech Stack)
- **Backend:** Laravel 13 (PHP ^8.3)
- **Frontend:** Blade + Tailwind CSS 4 + Vite
- **Database:** SQLite (dev) / MySQL/MariaDB (produksi)
- **Library / Package:** Laravel Sanctum (Bearer Token API), Eloquent ORM
### 2. Fitur Utama & Modul
- **Autentikasi & Otorisasi:** role `admin` (platform), `tenant` (operator kedai), `pelanggan` (file, no login); middleware `role`; Sanctum token untuk API
- **Tenants:** CRUD kedai + platform monitor (`/admin/*`), settings modal (nama, prefix resi, kontak)
- **Services & Orders:** CRUD tarif, catat order (walk-in / terdaftar, cek duplikat live), invoice `PREFIX-YYYYMMDD-urut`, struk + QR scan-to-track
- **Operations:** antrean kerja, alur Received → Washing → Drying → Ironing → Ready → Completed
- **Customers:** CRUD file pelanggan, cari nama/HP/ID (`CUST-001`), riwayat + total belanja, hapus dikunci bila berTransaksi
- **Promos:** CRUD diskon rule-based (nama + persen + min qty kg/pcs + window tanggal), attach via form service, auto-apply di order
- **Tracking publik:** lacak resi di `/` tanpa login, struk digital + riwayat tahap
### 3. Skema Data Singkat
- `tenants` (1 : N) `users`, `services`, `orders`
- `users` (1 : N) `orders` (sebagai customer), (1 : N) `order_tracks` (sebagai updater)
- `orders` (1 : N) `order_tracks`
- `promos` (M : N) `services` via `promo_service`

---

## 🚀 Panduan Instalasi Lokal
```bash
# Clone repository
git clone git@github.com:avriyyy/praktikum_pemweb2-responsi-a5.git
cd praktikum_pemweb2-responsi-a5
# Install dependensi PHP & Node
composer install
npm install
# Konfigurasi Environment
cp .env.example .env
php artisan key:generate
# Konfigurasi database di file .env, lalu migrasi & seed
php artisan migrate --seed
# Jalankan development server
php artisan serve
npm run dev
```
Buka `http://localhost:8000`.

---

## 🔑 Akun Pengujian
| Role | Email | Password |
| ---- | ----- | -------- |
| Admin (platform) | admin@laundrey.com via `/login` | password123 |
| Tenant (operator kedai) | tenant@laundrey.com via `/login` | password123 |

Daftar laundry baru via `/register`. Login pelanggan dinonaktifkan: file pelanggan dikelola counter.

---

## 📡 Dokumentasi API
Base: `/api/v1`. Header wajib `Accept: application/json`. Auth: `Authorization: Bearer <token>`.

| Method | Endpoint | Keterangan | Auth |
| ------ | -------- | ---------- | ---- |
| POST | /api/v1/auth/register | Registrasi laundry (name+prefix+tenant) | No |
| POST | /api/v1/auth/login | Login & token (tenant/admin) | No |
| POST | /api/v1/auth/logout | Logout (hapus token aktif) | Yes |
| GET | /api/v1/services | Daftar layanan (`cari`, `per_halaman`) | Yes |
| POST | /api/v1/services | Tambah layanan (+promo opsional) | Yes |
| PUT | /api/v1/services/{id} | Update layanan (+promo opsional) | Yes |
| DELETE | /api/v1/services/{id} | Hapus layanan | Yes |
| GET | /api/v1/orders | Daftar transaksi (`cari`, `status`, `payment_status`, `per_halaman`) | Yes |
| POST | /api/v1/orders | Buat transaksi + promo otomatis + invoice + track awal | Yes |
| GET | /api/v1/orders/{id} | Detail + tracks + promo | Yes |
| PUT | /api/v1/orders/{id} | Update transaksi | Yes |
| POST | /api/v1/orders/{id}/tracks | Update status + catat track | Yes |
| GET | /api/v1/track/{invoice} | Tracking publik | No |
| GET | /api/v1/promos | Daftar promo (`cari`) | Yes |
| POST | /api/v1/promos | Buat promo (nama+persen+min qty/unit+tanggal) | Yes |
| GET | /api/v1/promos/{id} | Detail promo | Yes |
| DELETE | /api/v1/promos/{id} | Hapus promo | Yes |

Response sukses: `{sukses:true, pesan, data}`. Error: 401 token, 403 peran, 404 resi, 422 validasi (`galat`).

---

## 🌐 Deployment
Live: [https://a5.athafa.cloud](https://a5.athafa.cloud) (aaPanel, PHP 8.4, MySQL, Nginx, document root `public`, Let's Encrypt).

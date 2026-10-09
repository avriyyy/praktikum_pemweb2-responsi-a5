<a href="https://a5.athafa.cloud"><img width="2172" alt="Laundrey" src="docs/logo.png" /></a>

# Laundrey

Multi-tenant laundry ops: tiap kedai daftar dengan kode resi 3 huruf sendiri (misal `QWP`), data terisolasi per tenant. Pelanggan lacak resi publik, admin kelola transaksi, tarif, file pelanggan, dan tahap pengerjaan.

## Cara kerja multi-tenant

- Registrasi (`/register` atau `POST /api/v1/auth/register`): nama laundry + prefix 3 huruf kapital (unik global) + akun admin. Contoh resi: `QWP-20261006-001`.
- Semua data (services, orders, customers) terfilter `tenant_id`. Cross-tenant return 404.
- Prefix bisa diganti di Settings, hanya berlaku untuk resi baru.
- Tracking publik by nomor resi penuh (unik global).

## Permasalahan

UMKM laundry masih catat manual: pelanggan tanya status berulang, tahapan cucian sulit dilacak, nota hilang dan salah hitung.

## Solusi

- Admin tunggal: counter mencatat order, tahap, tarif, dan file pelanggan
- Pelanggan tanpa login: dilayani by nama/HP, dilacak by resi publik
- CRUD pelanggan oleh admin: cari by nama/HP/ID, riwayat + total belanja, cegah duplikat via cek live di form order
- Kalkulasi harga otomatis (berat × tarif)
- Alur status: Received → Washing → Drying → Ironing → Ready → Completed
- Setiap perubahan status tercatat di `order_tracks`
- Relasi Eloquent: One-to-Many (tenant→users/services/orders, user→orders, order→tracks) + Many-to-Many (promo↔service via `promo_service`)
- Promo diskon rule-based: nama + persen + minimal qty (kg/pcs) + window tanggal, ditempel ke service dari form service (opsional); order otomatis dapat potongan bila syarat terpenuhi, terlihat di estimasi
- Desain referensi Linear.app: minimalis, whitespace lega, border subtle, satu aksen indigo `#5E6AD2`

## Teknologi

Laravel 13, PHP ≥ 8.4, MySQL/MariaDB (dev default SQLite), Eloquent ORM, Sanctum Bearer Token, Blade, Vite.

## Cara menjalankan

```bash
composer install
cp .env.example .env
php artisan key:generate
# MySQL: sesuaikan DB_* di .env. SQLite: biarkan default.
php artisan migrate --seed
php artisan serve
```

Buka `http://localhost:8000`.

## Akun pengujian

| Role | Email | Password |
| ---- | ----- | -------- |
| Admin (platform) | admin@laundrey.com via `/login` | password123 |
| Tenant (operator kedai) | tenant@laundrey.com via `/login` | password123 |

Daftar laundry baru via `/register`. Login pelanggan dinonaktifkan: file pelanggan dikelola counter.

## API Documentation

Base: `/api/v1`. Header wajib `Accept: application/json`. Auth: `Authorization: Bearer <token>`.

| Method | Endpoint | Keterangan | Auth | Role |
| ------ | -------- | ---------- | ---- | ---- |
| POST | /api/v1/auth/register | Registrasi laundry (name+prefix+tenant) | No | Public |
| POST | /api/v1/auth/login | Login & token (tenant/admin) | No | Public |
| POST | /api/v1/auth/logout | Logout (hapus token aktif) | Yes | Tenant |
| GET | /api/v1/services | Daftar layanan (cari=`cari`, paginasi `per_halaman`) | Yes | Tenant |
| POST | /api/v1/services | Tambah layanan (+promo opsional) | Yes | Tenant |
| PUT | /api/v1/services/{id} | Update layanan (+promo opsional) | Yes | Tenant |
| DELETE | /api/v1/services/{id} | Hapus layanan | Yes | Tenant |
| GET | /api/v1/orders | Daftar transaksi (filter `cari`, `status`, `payment_status`, `per_halaman`) | Yes | Tenant |
| POST | /api/v1/orders | Buat transaksi + promo otomatis + invoice + track awal | Yes | Tenant |
| GET | /api/v1/orders/{id} | Detail + tracks + promo | Yes | Tenant |
| PUT | /api/v1/orders/{id} | Update transaksi | Yes | Tenant |
| POST | /api/v1/orders/{id}/tracks | Update status + catat track | Yes | Tenant |
| GET | /api/v1/track/{invoice} | Tracking publik | No | Public |
| GET | /api/v1/promos | Daftar promo (cari=`cari`) | Yes | Tenant |
| POST | /api/v1/promos | Buat promo (nama+persen+min qty/unit+tanggal) | Yes | Tenant |
| GET | /api/v1/promos/{id} | Detail promo | Yes | Tenant |
| DELETE | /api/v1/promos/{id} | Hapus promo | Yes | Tenant |

Services, promos, customers: CRUD halaman penuh (tambah/ubah di halaman sendiri, tabel + tombol Edit/Hapus, tanpa inline form).

Customer CRUD + live lookup (`/customers`, `/customers-lookup`) tersedia di web untuk tenant.

Response sukses: `{sukses:true, pesan, data}`. Error konsisten: 401 token, 403 peran, 404 resi, 422 validasi (`galat`).

## Struktur

- `database/migrations`: tenants, users/services/orders (+tenant_id), order_tracks
- `app/Models`: Tenant, User (tenant), Service (tenant), Order (tenant, prefix invoice), OrderTrack
- `app/Models`: User (HasApiTokens, orders, orderTracks), Service (orders), Order (customer, service, tracks), OrderTrack (order, updater)
- `app/Http/Requests`: Login, Store/Update Service, Store/Update Order, StoreTrack
- `app/Http/Resources`: Service, Order (nested customer/service/tracks), OrderTrack
- `app/Http/Controllers/Api/V1`: Auth, Service, Order, Track
- `app/Http/Middleware/EnsureRole.php` → alias `role`
- `app/Http/Controllers/Web`: Auth (session), Dashboard, Order, Service, Operation, Track, Customer (CRUD + lookup)
- `resources/views`: layouts/app, tracking, auth, dashboard, orders, services, operations, customers

## Deployment

Live: [https://a5.athafa.cloud](https://a5.athafa.cloud) (aaPanel, PHP 8.4, MySQL, Nginx, document root `public`, Let's Encrypt).

Set `APP_URL`, `DB_*` MySQL, `php artisan migrate --force`, `npm run build`.

## Video dokumentasi

- Yudha (customers, promos, landing): TODO — isi link video individu.

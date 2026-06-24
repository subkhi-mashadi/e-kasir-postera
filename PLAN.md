# E-Kasir — Rencana Pembangunan Sistem POS SaaS Multi-Cabang

> Dokumen perencanaan lengkap. Sistem kasir (POS) berbasis SaaS untuk
> usaha F&B / retail dengan QR ordering per meja, multi-cabang,
> dukungan offline (PWA), dan manajemen langganan.

---

## 1. Ringkasan & Tujuan

Membangun platform **SaaS POS** di mana banyak usaha (tenant) berlangganan
untuk memakai sistem kasir. Tiap usaha bisa punya banyak cabang. Pelanggan
akhir bisa memesan sendiri dengan scan QR di meja. Aplikasi kasir tetap
jalan saat offline dan sinkron otomatis saat online kembali.

### Aktor utama
| Aktor | Akses | Antarmuka |
|---|---|---|
| **Super Admin** | Kelola semua tenant, paket, langganan, data global | **Filament** panel (`/admin`) |
| **Owner** (pemilik usaha) | Kelola usaha sendiri, semua cabang, laporan, pengaturan | **Blade** (`/app`) |
| **Manager** (cabang) | Kelola 1 cabang: stok, staff, laporan cabang | **Blade** (`/app`) |
| **Kasir** | Transaksi POS di 1 cabang | **Blade** POS (`/pos`) — PWA offline |
| **Pelanggan** | Scan QR meja → lihat menu → pesan | **Blade** publik (`/order/{token}`) — tanpa login |

### Keputusan kunci (perlu konfirmasi)
- **D1 — Jenis usaha:** ✅ **F&B** (dine-in dengan meja + QR ordering). Produk =
  menu (makanan/minuman) dengan varian & modifier (topping, level pedas, ukuran).
  Stok bahan baku opsional (resep/BOM) — di luar MVP, bisa fase lanjut.
- **D2 — Payment gateway:** ✅ **Midtrans** (Snap) — billing langganan tenant
  & opsional pembayaran pelanggan QR.
- **D3 — Multi-tenancy:** ✅ **single database + kolom `company_id`** (row-level
  scoping via global scope). Super admin bypass scope.

---

## 2. Tech Stack

| Lapisan | Pilihan | Alasan |
|---|---|---|
| Framework | Laravel 13 (PHP 8.3) | sudah terpasang |
| Admin panel | **FilamentPHP v4** | super admin & manajemen SaaS |
| UI tenant | **Blade + Alpine.js + Tailwind v4** | sudah terpasang; ringan, cocok PWA |
| POS interaktif | Alpine.js island + **Dexie.js** (IndexedDB) | logika kasir di klien untuk offline |
| Offline/PWA | **Workbox** (service worker) + Web App Manifest | cache aset + queue sync |
| Auth/roles | `spatie/laravel-permission` | role & permission |
| Media | `spatie/laravel-medialibrary` | foto produk |
| Billing | `midtrans/midtrans-php` | langganan |
| Realtime (opsional) | Laravel Reverb (WebSocket) | notif order meja ke dapur/kasir |
| Queue | database / Redis | proses async (notif, sync) |
| Testing | Pest | sudah terpasang |

> Catatan: paket di atas **belum** terpasang (project fresh, hanya model
> `User`). Pemasangan masuk Fase 0.

---

## 3. Arsitektur Aplikasi

### 3.1 Pemisahan panel
```
/admin            → Filament panel  (Super Admin saja)
/app/*            → Blade dashboard tenant (Owner / Manager)
/pos              → Blade POS (Kasir) — PWA, offline-capable
/order/{token}    → Blade publik QR ordering (Pelanggan, tanpa login)
/api/*            → endpoint JSON untuk POS & sinkronisasi offline
```

### 3.2 Multi-tenancy (single DB)
- Tiap tabel bisnis punya `company_id` (dan sebagian `branch_id`).
- **Global scope** `BelongsToCompany` otomatis memfilter query ke
  `company_id` user yang login. Super admin bypass scope.
- Middleware `EnsureTenantActive` cek langganan tenant aktif sebelum akses
  `/app` & `/pos`. Kalau langganan habis → redirect ke halaman billing.

### 3.3 Pembatasan oleh langganan
- Paket menentukan **batas**: jumlah cabang, jumlah user, jumlah produk,
  fitur (QR ordering on/off, laporan lanjutan, dll).
- Cek batas saat create resource (mis. tambah cabang) via Policy / Service.

---

## 4. Model Data (ERD ringkas)

```
companies (tenant)
 ├─ branches (cabang)
 │   ├─ tables (meja: nomor, qr_token, status)
 │   ├─ inventories (stok per produk per cabang)
 │   ├─ devices (kasir/terminal terdaftar, untuk sync)
 │   └─ users (kasir/manager terikat cabang)
 ├─ users (owner & staff)  ── role via spatie
 ├─ categories
 ├─ products ── product_variants (opsi/harga), product_modifiers (topping)
 ├─ customers (member, poin)
 ├─ orders (pesanan: dine-in/takeaway/QR)
 │   └─ order_items ── order_item_modifiers
 ├─ payments (multi-metode per order)
 ├─ inventory_logs (mutasi stok)
 ├─ discounts / vouchers
 └─ subscription (lihat domain SaaS)

— Domain SaaS (dikelola Super Admin) —
plans (paket: harga, limit, fitur)
subscriptions (company_id, plan_id, status, periode, trial)
subscription_invoices (tagihan, status bayar, midtrans ref)
feature_flags / plan_features
```

### Catatan field penting
- **orders**: `uuid` (client-generated untuk idempotensi offline),
  `branch_id`, `table_id?`, `type` (dine_in/takeaway/qr), `status`
  (open/paid/cancelled), `source` (pos/qr), `synced_at`, `created_offline_at`.
- **order_items**: snapshot `product_name`, `price`, `qty`, `subtotal`
  (jangan join langsung ke produk untuk struk historis).
- **payments**: `method` (cash/qris/transfer/card/credit), `amount`, `ref`.
- **tables**: `qr_token` (random, unik) untuk URL `/order/{token}`.
- **devices**: untuk identifikasi terminal POS & resolusi konflik sync.

---

## 5. Alur QR Ordering per Meja

1. Owner/manager generate meja → tiap meja punya `qr_token` + cetak QR
   (QR encode URL `https://app.domain/order/{qr_token}`).
2. Pelanggan scan → buka halaman menu publik (Blade, tanpa login),
   `company`/`branch`/`table` teridentifikasi dari token.
3. Pelanggan pilih item + modifier → submit pesanan → buat `order`
   (`type=qr`, `status=open`) terikat ke meja.
4. **Notifikasi** masuk ke kasir/dapur (polling tiap N detik, atau Reverb
   WebSocket bila diaktifkan).
5. Kasir konfirmasi & proses. Pembayaran: **bayar di kasir** (default) atau
   **bayar online** via Midtrans (opsional, kontrol per paket/pengaturan).
6. Order selesai → meja `available` lagi.

> Keamanan: token meja random panjang (anti-tebak). Rate-limit submit order
> per token. Opsi: butuh nomor HP / OTP ringan untuk anti-spam.

---

## 6. Strategi Offline & PWA (bagian tersulit)

Kasir harus tetap bisa transaksi tanpa internet, lalu sinkron saat online.

### 6.1 Komponen
- **Service Worker (Workbox):** cache app shell (HTML POS, JS, CSS, ikon) →
  halaman POS tetap kebuka offline.
- **IndexedDB (Dexie.js):** simpan di klien:
  - katalog produk + harga + stok (di-prefetch saat online),
  - antrian order yang dibuat offline (`outbox`).
- **Web App Manifest:** installable (Add to Home Screen), fullscreen, ikon.

### 6.2 Alur transaksi offline
1. Saat online, POS prefetch katalog → simpan ke IndexedDB.
2. Kasir buat transaksi → **selalu** tulis ke IndexedDB dulu (UUID dibuat di
   klien). UI langsung responsif (offline-first).
3. **Outbox queue**: order tersimpan dengan status `pending_sync`.
4. Saat online (event `online` / Background Sync), kirim batch outbox ke
   `POST /api/sync/orders`.
5. Server pakai **UUID sebagai idempotency key** → dedup (kirim ulang aman).
6. Server balas hasil (sukses/konflik/stok kurang) → klien update status,
   hapus dari outbox.

### 6.3 Resolusi konflik
- **Nomor invoice/struk**: jangan auto-increment global untuk offline.
  Pakai format `BRANCH-DEVICE-SEQ` atau UUID + nomor tampilan dari server
  saat sync. Hindari tabrakan antar device.
- **Stok**: offline = optimistic. Saat sync, server validasi stok; jika minus,
  tandai order `needs_review` (jangan tolak diam-diam — laporkan ke kasir).
- **last-write-wins** tidak dipakai untuk uang; order bersifat append-only.

### 6.4 Batasan offline
- QR ordering pelanggan **butuh online** (pelanggan device beda). Yang offline
  = sisi kasir. Saat cabang offline, QR ordering nonaktif sementara.
- Pembayaran online (Midtrans) butuh online.

> Karena offline butuh logika kasir di klien, **halaman POS = Blade shell +
> Alpine/JS island** (bukan Livewire, yang butuh server tiap interaksi).

---

## 7. Domain SaaS (Super Admin — Filament)

Panel Filament di `/admin` (akses super admin only):
- **Plans (Paket):** CRUD paket — harga, periode (bulanan/tahunan), limit
  (cabang, user, produk), fitur (QR ordering, laporan lanjut, multi-device).
- **Tenants (Companies):** lihat/kelola semua usaha, suspend/aktifkan.
- **Subscriptions:** status langganan tiap tenant, trial, perpanjangan manual.
- **Invoices:** tagihan langganan + status pembayaran (sinkron Midtrans).
- **Global data & report:** total tenant, MRR, transaksi agregat, dsb.
- **Widgets dashboard:** revenue langganan, tenant aktif, churn.

Alur langganan tenant:
1. Owner daftar → dapat **trial** (mis. 14 hari) paket dasar.
2. Sebelum trial habis → owner pilih paket → bayar via Midtrans Snap.
3. Webhook Midtrans → update `subscription` aktif + buat `invoice` lunas.
4. Cron harian cek langganan kedaluwarsa → set `expired` → kunci `/app`,`/pos`.

---

## 8. Antarmuka Tenant (Blade)

### Owner / Manager (`/app`)
- Dashboard ringkas (omzet hari ini, transaksi, stok menipis).
- Master data: kategori, produk + varian + modifier, supplier (opsional).
- Cabang & meja (generate QR, cetak).
- Stok per cabang + penyesuaian + transfer antar cabang.
- Staff (user) + role + penempatan cabang.
- Pelanggan/member + poin.
- Diskon & voucher.
- Laporan: penjualan, produk terlaris, laba kotor, per kasir, per cabang.
- Pengaturan: info usaha, pajak, struk, metode bayar, integrasi.

### Kasir (`/pos`)
- POS offline-first: cari/scan produk → keranjang → diskon/voucher/poin →
  pembayaran multi-metode → struk (cetak/PDF).
- Daftar order QR masuk (konfirmasi & proses).
- Indikator status online/offline + jumlah antrian belum tersync.
- Pilih/ganti cabang (jika user multi-cabang).

---

## 9. Desain API (untuk POS & sync)

```
GET  /api/catalog?branch_id=          # prefetch produk+stok (untuk IndexedDB)
POST /api/sync/orders                 # batch upload order offline (idempotent by uuid)
GET  /api/orders/incoming?branch_id=  # polling order QR baru (kasir)
POST /api/orders/{uuid}/pay           # pembayaran
GET  /api/customers/search?q=
POST /api/vouchers/validate
— publik (QR) —
GET  /order/{token}                   # halaman menu (Blade)
POST /api/public/orders               # submit pesanan dari pelanggan
```
Auth API: session (sanctum stateful) untuk staff; token meja untuk publik.
Semua endpoint POS discope `company_id`/`branch_id` user.

---

## 10. Roadmap Implementasi (per fase)

> Tiap fase punya milestone jelas & bisa di-deliver bertahap.

### Fase 0 — Fondasi (1–2 hari)
- Pasang paket: Filament v4, spatie/permission, spatie/medialibrary, midtrans.
- Setup multi-tenancy: trait + global scope `BelongsToCompany`, middleware
  tenant aktif.
- Roles & permissions seeder (super_admin, owner, manager, cashier).
- Auth: login, pilih/ganti cabang.
- **Milestone:** login per role, scoping tenant jalan.

### Fase 1 — Master Data (2–3 hari)
- Migrasi & model: companies, branches, categories, products(+varian,
  +modifier), tables, inventories, customers.
- CRUD Blade owner/manager untuk master data.
- Generate & cetak QR meja.
- **Milestone:** owner bisa isi menu + cabang + meja.

### Fase 2 — POS Inti (3–5 hari)
- Halaman POS Blade + Alpine island (online dulu).
- Order, order_items, payments, struk, mutasi stok.
- Diskon/voucher/poin.
- **Milestone:** transaksi penuh online + struk + stok berkurang.

### Fase 3 — QR Ordering (2–3 hari)
- Halaman menu publik `/order/{token}`.
- Submit order pelanggan → masuk antrian kasir.
- Notifikasi (polling dulu; Reverb opsional nanti).
- **Milestone:** pesan dari HP pelanggan → muncul di kasir.

### Fase 4 — PWA & Offline Sync (4–6 hari) ⚠️ paling kompleks
- Manifest + service worker (Workbox), installable.
- IndexedDB (Dexie): cache katalog + outbox order.
- Refactor POS jadi offline-first (tulis lokal dulu).
- Endpoint `/api/sync/orders` idempotent + resolusi konflik + indikator sync.
- **Milestone:** matikan internet → transaksi tetap jalan → online → tersinkron.

### Fase 5 — SaaS & Billing (3–5 hari)
- Filament panel super admin: plans, subscriptions, invoices, widgets.
- Integrasi Midtrans (checkout langganan + webhook).
- Enforcement limit paket + kunci tenant kedaluwarsa (cron).
- **Milestone:** owner berlangganan & bayar; super admin kelola semua.

### Fase 6 — Laporan & Polish (2–4 hari)
- Laporan penjualan/laba/per-cabang + export Excel/PDF.
- Transfer stok antar cabang, supplier/PO (opsional).
- Hardening keamanan, optimasi, testing (Pest), dokumentasi.
- **Milestone:** rilis kandidat.

---

## 11. Keamanan & Kualitas
- Scope `company_id` ketat (global scope) — uji kebocoran antar tenant.
- Policy untuk tiap resource + enforcement limit paket.
- Rate-limit endpoint publik QR & login.
- Idempotensi sync (anti dobel order).
- Validasi server-side untuk semua input POS (jangan percaya klien offline).
- Backup DB + log mutasi stok & pembayaran (audit trail).
- Test Pest: scoping tenant, alur order, sync idempotent, enforcement limit.

---

## 12. Risiko & Mitigasi
| Risiko | Mitigasi |
|---|---|
| Sync offline konflik nomor invoice | nomor tampilan dari server saat sync; UUID internal |
| Stok minus karena order offline | optimistic + flag `needs_review`, bukan tolak diam |
| Kebocoran data antar tenant | global scope + test khusus + policy |
| Kompleksitas PWA membengkak | kerjakan POS online dulu (Fase 2), offline belakangan (Fase 4) |
| Webhook Midtrans gagal | retry queue + rekonsiliasi manual di Filament |

---

## 13. Estimasi Total
~17–28 hari kerja efektif (tergantung scope offline & laporan). Bisa rilis
bertahap: **MVP = Fase 0–2** (POS online multi-cabang) lebih dulu, lalu QR,
offline, dan SaaS menyusul.

---

## Lampiran — Urutan Eksekusi yang Disarankan
1. Fase 0 (fondasi) — wajib lebih dulu.
2. Fase 1 + 2 → **MVP POS online** (sudah berguna & bisa demo).
3. Fase 5 (SaaS) bisa paralel dengan Fase 3, tergantung prioritas bisnis.
4. Fase 4 (offline) terakhir karena paling berisiko & butuh POS stabil dulu.

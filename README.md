# BurjoOrder

Aplikasi pemesanan burjo — Laravel 13 + Livewire 3 + Reverb, berjalan di atas Laravel Sail (Docker) dengan FrankenPHP worker mode dan Cloudflare Tunnel.

## Prasyarat

- Docker Desktop terinstall dan **running**
- PHP >= 8.3 & Composer (untuk `composer dev` di host)
- Bun (package manager frontend)

## Menjalankan Aplikasi

### 1. Pastikan Docker aktif

Buka Docker Desktop, atau cek lewat terminal:

```bash
docker info
```

Kalau error `Cannot connect to the Docker daemon`, jalankan dulu Docker Desktop-nya.

### 2. Setup awal (sekali saja)

```bash
composer install
cp .env.example .env      # jika belum ada .env
php artisan key:generate
bun install --ignore-scripts
```

Atau cukup:

```bash
composer setup
```

### 3. Jalankan container (DB, Redis, FrankenPHP, Cloudflare Tunnel)

```bash
vendor/bin/sail up -d
```

Service yang akan jalan:

| Service | Port | Keterangan |
|---|---|---|
| `laravel.test` | 80 | Sail default (PHP-FPM dev) |
| `frankenphp` | **8081** | App utama (worker mode) → http://localhost:8081 |
| `cloudflared` | — | Tunnel publik via trycloudflare.com |
| `pgsql` | 5432 | PostgreSQL 18 |
| `redis` | 6379 | Redis |

### 4. Migrasi & seed database (jika belum)

```bash
vendor/bin/sail artisan migrate:fresh --seed
```

### 5. Jalankan proses pendukung

Pilih salah satu sesuai kebutuhan:

**a. Development lokal (dengan HMR):**

```bash
composer dev
```

Menjalankan queue worker, Reverb (port 8080), Pail, dan Vite dev server. Cocok untuk akses **localhost:8081 saja** — CSS/JS akan rusak jika diakses via tunnel.

**b. Demo / akses via Cloudflare Tunnel:**

```bash
composer tunnel
```

Menjalankan queue worker, Reverb, Pail, dan `bun run build --watch` (asset di-build, tanpa dev server). Aman diakses dari URL tunnel maupun localhost.

### 6. Akses aplikasi

- **Lokal**: http://localhost:8081
- **Publik** (pengganti ngrok), cek URL tunnel:

```bash
docker compose logs cloudflared | grep trycloudflare
```

Contoh output: `https://random-words.trycloudflare.com`

> Jika butuh absolute URL (misal callback Midtrans), update `APP_URL` di `.env` dengan URL tunnel tersebut.

## Akun Seed

| Role | Email | Password |
|---|---|---|
| Admin | admin@burjo.test | password |
| Kasir | kasir@burjo.test | password |

## Hal Penting (FrankenPHP Worker Mode)

Aplikasi live di memori (bukan request-per-proses), jadi:

- Setelah mengubah `.env`, config, atau kode provider → restart:
  ```bash
  vendor/bin/sail compose restart frankenphp
  ```
- Setelah mengubah Caddyfile/Dockerfile frankenphp → rebuild:
  ```bash
  docker compose up -d --build frankenphp
  ```
- URL trycloudflare **berubah setiap kali container `cloudflared` di-recreate** (restart biasa tidak mengubah URL).

## Testing

```bash
vendor/bin/sail artisan test
```

## Lint & Static Analysis

```bash
vendor/bin/sail bin pint --dirty
vendor/bin/phpstan
```

## Troubleshooting

| Masalah | Solusi |
|---|---|
| Port 8080/8081/443 sudah dipakai | Matikan proses lain yang memakainya, atau set `FRANKENPHP_PORT=xxxx` di `.env` |
| `Class "Redis" not found` | Ekstensi redis hilang dari build image → `docker compose up -d --build frankenphp` |
| Halaman redirect ke HTTPS lalu gagal | Pastikan mount `docker/frankenphp/Caddyfile:/etc/frankenphp/Caddyfile` masih ada di `compose.yaml` |
| Perubahan `.env` tidak berefek | Restart frankenphp (worker mode cache config) |
| CSS/JS rusak via tunnel | Vite dev server tidak bisa diakses dari tunnel → gunakan `composer tunnel` (build mode), pastikan tidak ada file `public/hot` |
| Asset/lottie 404 | Pastikan symlink `public/storage` relatif (`-> ../storage/app/public`) |

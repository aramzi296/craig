# Panduan Setup Docker Production untuk VPS

Repositori ini telah dikonfigurasi agar siap dijalankan menggunakan Docker di server VPS production.

## Komponen yang Ditambahkan:

1. **`Dockerfile`**: Menggunakan multi-stage build yang ringan.
   - Stage 1: `node:20-alpine` untuk melakukan compile aset Vite.
   - Stage 2: `php:8.2-fpm-alpine` untuk menjalankan backend Laravel, dilengkapi dengan dependensi sistem, PHP extensions, Redis extension, dan Composer.
2. **`docker-compose.yml`**: Susunan stack production yang berisi:
   - `app`: Container PHP-FPM.
   - `web`: Container Nginx (berjalan di port 80).
   - `worker`: Container background worker yang menjalankan `php artisan queue:work redis`.
   - `cron`: Container scheduler untuk menjalankan `php artisan schedule:run`.
   *(Catatan: Setup ini menggunakan SQLite untuk database. Redis dan Meilisearch diasumsikan berjalan pada container eksternal di server Anda).*
3. **`docker/nginx/default.conf`**: Konfigurasi Nginx untuk meneruskan traffic PHP ke container `app` via FastCGI dan melayani aset statis dengan cache.
4. **`docker/php/local.ini`**: Konfigurasi khusus PHP yang mengatur limit memori dan ukuran upload (`50M` upload limit, `512M` memory limit).

## Langkah-langkah Deploy ke VPS

1. **Pindahkan File**: Clone repositori ini atau upload seluruh direktori proyek ke server VPS Anda.
2. **Atur File `.env` di VPS**:
   - Ubah environment menjadi production:
     ```env
     APP_ENV=production
     APP_DEBUG=false
     APP_URL=https://domain-anda.com
     ```
   - Atur pengaturan Database agar menggunakan SQLite:
     ```env
     DB_CONNECTION=sqlite
     DB_DATABASE=database/database.sqlite
     ```
   - Hubungkan ke External Redis & Meilisearch Anda:
     ```env
     REDIS_HOST=IP_ATAU_NAMA_HOST_DOCKER_REDIS_ANDA
     REDIS_PORT=6379
     REDIS_PASSWORD=password_redis_jika_ada
     QUEUE_CONNECTION=redis
     CACHE_STORE=redis
     SESSION_DRIVER=redis

     MEILISEARCH_HOST=http://IP_ATAU_NAMA_HOST_MEILISEARCH:7700
     MEILISEARCH_KEY=kunci_meilisearch_anda
     ```
3. **Build & Jalankan Docker**:
   Jalankan perintah berikut di VPS dari dalam direktori proyek:
   ```bash
   docker compose -f docker-compose.yml build
   docker compose -f docker-compose.yml up -d
   ```
4. **Jalankan Migrasi (Hanya saat Setup Awal)**:
   ```bash
   docker compose exec app php artisan migrate --force
   ```
5. **Buat Storage Link (Hanya saat Setup Awal)**:
   ```bash
   docker compose exec app php artisan storage:link
   ```

> [!TIP]
> **Penting:** Pastikan bahwa network Docker dari aplikasi ini memiliki akses ke container Redis dan Meilisearch eksternal Anda. Anda bisa mencapainya dengan menggabungkan container-container ini ke dalam *external docker network* yang sama, atau cukup menggunakan alamat IP host internal docker untuk mengaksesnya dari `.env`.

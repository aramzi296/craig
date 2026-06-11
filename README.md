# Craig Web Application

Aplikasi web berbasis **Laravel (PHP)** dengan **Vite** dan **Tailwind CSS v4**.

| Layanan | Provider |
|---------|----------|
| Database, session, cache, queue | **Supabase** (PostgreSQL) |
| Gambar | **Cloudflare R2** (URL penuh di database) |
| Email (development) | **`log`** → `storage/logs/laravel.log` |
| Docker (opsional) | **Laravel Sail** — hanya container PHP/Nginx |

---

## Cara Menjalankan

### Metode 1: Laravel Sail (Docker)

Sail menjalankan **hanya** aplikasi Laravel. Database langsung ke **Supabase** lewat `.env` (tidak ada PostgreSQL/Mailpit di Docker).

#### 1. Siapkan `.env`

```bash
cp .env.example .env
php artisan key:generate
```

Isi kredensial Supabase, R2, dan layanan lain di `.env`.

#### 2. Jalankan container

```bash
./vendor/bin/sail up -d
```

#### 3. Migrasi (jika perlu)

```bash
./vendor/bin/sail artisan migrate --seed
```

#### 4. Vite (terminal terpisah)

```bash
./vendor/bin/sail npm run dev
```

#### 5. Akses

- Aplikasi: [http://localhost](http://localhost)
- Email dev: cek `storage/logs/laravel.log`

#### 6. Stop

```bash
./vendor/bin/sail down
```

---

### Metode 2: Lokal tanpa Docker

Butuh PHP 8.2+, Composer, Node.js, dan akses internet ke Supabase.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve          # terminal 1
npm install && npm run dev # terminal 2
```

Aplikasi: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## Akun admin

- Email default: **`admin@sebatam.com`**
- Reset password ke `password`:
  - Sail: `yes | ./vendor/bin/sail php reset_admin.php`
  - Lokal: `php reset_admin.php`

---

## Panduan Deploy ke Production Server

Untuk menjalankan aplikasi ini di server publik (Production), sebaiknya gunakan mode **tanpa Docker (bare-metal)** menggunakan Nginx/Apache.

### 1. Persiapan Server
Pastikan server Anda memiliki perangkat lunak berikut:
- PHP 8.2 atau lebih baru (dengan ekstensi: `dom`, `curl`, `mbstring`, `xml`, `zip`, `sqlite3`, dll)
- Composer
- Node.js & NPM (untuk _build_ aset Vite)
- Nginx atau Apache

### 2. Unduh dan Instalasi
1. _Clone_ repositori ini ke server Anda:
   ```bash
   git clone <url-repositori> /var/www/nama-aplikasi
   cd /var/www/nama-aplikasi
   ```
2. Instal dependensi PHP tanpa paket *development*:
   ```bash
   composer install --optimize-autoloader --no-dev
   ```
3. Siapkan file `.env`:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

### 3. Konfigurasi Lingkungan (`.env`)
Ubah beberapa baris di `.env` agar sesuai untuk produksi:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

# 1. Konfigurasi SQLite
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/nama-aplikasi/shared/storage/app/database.sqlite

# 2. Konfigurasi Meilisearch (Arahkan ke IP/Host Container Meilisearch Server Anda)
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://<IP_ATAU_NAMA_CONTAINER>:7700
MEILISEARCH_KEY=<MASTER_KEY_MEILISEARCH_JIKA_ADA>
SCOUT_PREFIX=nama_aplikasi_unik_ # Gunakan awalan khusus untuk aplikasi ini!
```
*(Lihat detail SQLite & Meilisearch di bagian khusus di bawah)*

### 4. Build Aset Frontend
Karena menggunakan Vite, Anda harus melakukan *build* aset untuk _production_:
```bash
npm install
npm run build
```

### 5. Optimasi & Migrasi Database
Jalankan perintah berikut untuk mengoptimasi Laravel dan menjalankan struktur database terbaru:
```bash
# Buat file sqlite jika belum ada
touch /var/www/nama-aplikasi/shared/storage/app/database.sqlite

# Link folder storage agar gambar publik bisa diakses
php artisan storage:link

# Migrasi database (jangan lupa --force untuk production)
php artisan migrate --force

# Optimasi konfigurasi, route, dan view
php artisan optimize
```

### 6. Sinkronisasi Meilisearch
Anda bisa menyinkronkan data pencarian ke Meilisearch dengan menjalankan:
```bash
php artisan scout:import "App\Models\Listing"
php artisan scout:sync-index-settings
```
*(Atau lakukan melalui **Dasbor Admin -> Sinkron Meili**)*

### 7. Pengaturan Nginx
Arahkan *Document Root* Nginx Anda ke dalam folder `public/`.
Contoh blok server Nginx:
```nginx
server {
    listen 80;
    server_name domain-anda.com;
    root /var/www/nama-aplikasi/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```
Pastikan _owner_ dari folder `storage` dan `bootstrap/cache` diatur ke *user* *web-server* (misalnya `www-data`):
```bash
sudo chown -R www-data:www-data /var/www/nama-aplikasi/storage
sudo chown -R www-data:www-data /var/www/nama-aplikasi/bootstrap/cache
```

---

## Deploy Menggunakan aaPanel
Jika VPS Anda menggunakan **aaPanel**, langkahnya jauh lebih mudah melalui antarmuka grafis (GUI):

1. **Buat Website Baru:** Masuk ke menu **Website** -> **Add site**. Masukkan nama domain Anda. (Pilih versi PHP 8.2+). Kosongkan pembuatan database karena kita memakai SQLite.
2. **Upload & Extract:** Buka menu **Files**, masuk ke direktori web yang baru dibuat, lalu _upload_ *source code* aplikasi (atau gunakan fitur Terminal untuk *git clone*).
3. **Pengaturan Direktori (Sangat Penting):** 
   - Klik nama website Anda di daftar *Website*.
   - Pilih tab **Site directory**.
   - Ubah **Running directory** menjadi `/public`.
   - **PENTING:** Hilangkan centang pada opsi **"Anti-XSS attack (Base directory restriction)"** (atau *open_basedir*). Jika tidak dihilangkan, aplikasi akan menampilkan error 500 karena Laravel tidak diizinkan membaca file di luar folder `public`.
4. **URL Rewrite:** Pindah ke tab **URL rewrite**, lalu pilih *template* `laravel` dari *dropdown* yang tersedia. Simpan.
5. **Composer & Terminal:** Buka fitur Terminal di aaPanel (masuk ke direktori web Anda), lalu jalankan:
   ```bash
   composer install --optimize-autoloader --no-dev
   cp .env.example .env
   php artisan key:generate
   npm install && npm run build
   php artisan storage:link
   php artisan migrate --force
   ```
6. **Perbaiki Hak Akses (Permissions):** Kembali ke menu **Files**, klik kanan pada folder `storage` dan `bootstrap/cache`, pilih **Permission**, lalu atur *owner* ke `www` dengan izin `755` (centang *apply to sub-directories*).

---

## Catatan Penting Deployment

### Tips Deployment dengan SQLite
Karena aplikasi ini menggunakan **SQLite**:
1. File `.sqlite` sudah ada di `.gitignore`, jadi tidak akan meniban server saat _git pull_.
2. Jangan letakkan file `database.sqlite` di dalam folder proyek utama jika Anda menggunakan sistem *zero-downtime deployment* (yang membuat folder baru setiap rilis). Pindahkan ke luar (misal: `/shared/storage/app/database.sqlite`) dan ubah `DB_DATABASE` di `.env` sesuai _absolute path_ tersebut agar data tidak hilang.

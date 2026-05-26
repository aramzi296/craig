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

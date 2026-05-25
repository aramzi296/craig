# Craig Web Application

Aplikasi web berbasis **Laravel (PHP)** yang menggunakan **Vite** dan **Tailwind CSS v4** untuk frontend, serta menggunakan **Laravel Sail** (lingkungan berbasis Docker) yang dikonfigurasi lengkap dengan PostgreSQL, Redis, Meilisearch, dan Mailpit.

---

## Cara Menjalankan Aplikasi

Berikut adalah panduan lengkap cara menjalankan aplikasi ini menggunakan metode yang direkomendasikan (Docker/Sail) maupun metode alternatif (lokal tanpa Docker).

### Metode 1: Menggunakan Laravel Sail (Sangat Direkomendasikan 🐋)

Karena seluruh konfigurasi database PostgreSQL, Redis, dan layanan lainnya sudah disiapkan di file `compose.yaml`, menggunakan Docker adalah cara termudah dan paling aman agar tidak terjadi konflik konfigurasi di komputer lokal Anda.

#### 1. Jalankan Docker Container
Pastikan aplikasi Docker Desktop (atau daemon Docker) sudah aktif di komputer Anda. Kemudian, buka terminal di folder project dan jalankan perintah:
```bash
./vendor/bin/sail up
```
*Tip: Tambahkan opsi `-d` di akhir (`./vendor/bin/sail up -d`) jika Anda ingin menjalankan kontainer di latar belakang (background) agar terminal tetap bisa digunakan.*

#### 2. Jalankan Migrasi dan Seed Database (Inisialisasi)
Buka terminal baru di direktori project yang sama, lalu jalankan perintah di bawah ini untuk membuat tabel database PostgreSQL dan mengisi data awal (dummy data):
```bash
./vendor/bin/sail artisan migrate --seed
```

#### 3. Jalankan Vite untuk Frontend Development
Jalankan server kompilasi aset frontend (CSS/JS) secara real-time dengan perintah:
```bash
./vendor/bin/sail npm run dev
```

#### 4. Akses Aplikasi
- **Aplikasi Utama**: Buka browser dan akses [http://localhost](http://localhost)
- **Mailpit Dashboard (Penerima Email Testing)**: Buka [http://localhost:8025](http://localhost:8025) untuk melihat email keluar dari sistem (seperti notifikasi atau email registrasi).

#### 5. Menghentikan Layanan
Jika sudah selesai digunakan, Anda bisa mematikan kontainer Docker dengan perintah:
```bash
./vendor/bin/sail down
```

---

### Metode 2: Menjalankan Secara Lokal (Tanpa Docker / Manual 💻)

Jika Anda tidak ingin menggunakan Docker, Anda harus menyiapkan PHP, PostgreSQL/SQLite, dan Node.js sendiri di sistem Anda.

#### 1. Sesuaikan Konfigurasi `.env`
Buka file `.env` dan sesuaikan pengaturan database agar mengarah ke database lokal Anda. 
- *Contoh jika menggunakan SQLite:*
  ```env
  DB_CONNECTION=sqlite
  ```
- *Contoh jika menggunakan PostgreSQL lokal:*
  Sesuaikan `DB_HOST=127.0.0.1` dan sesuaikan port, nama database, username, serta password-nya.

#### 2. Jalankan Migrasi dan Seed Database
```bash
php artisan migrate --seed
```

#### 3. Jalankan Server Development PHP
```bash
php artisan serve
```
Secara default, aplikasi akan berjalan dan dapat diakses di [http://127.0.0.1:8000](http://127.0.0.1:8000).

#### 4. Jalankan Vite Development Server
Di terminal terpisah, jalankan:
```bash
npm run dev
```

---

## 🔑 Informasi Akun Administrator & Tips Berguna

- **Akun Admin Default**: Sistem memiliki akun administrator bawaan dengan email **`admin@sebatam.com`**.
- **Reset Password Admin**: Jika Anda perlu mengatur ulang password admin tersebut ke default (`password`), Anda dapat menjalankan skrip reset dengan perintah berikut:
  - Jika menggunakan Sail: `yes | ./vendor/bin/sail php reset_admin.php`
  - Jika menggunakan Lokal: `php reset_admin.php`

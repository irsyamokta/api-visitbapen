# VisitBapen Backend

VisitBapen adalah backend API untuk mengelola **Website Desa Wisata Banjarpanepen**. Backend ini dibuat dengan Laravel dan mendukung autentikasi pengguna, manajemen wisata, artikel, dan paket wisata.

## Technology

- Laravel 12
- PHP 8.3.6
- MySQL
- Auth based token
- CORS support untuk frontend (Vite/React)

## Features

- Register & Login pengguna
- Logout dan validasi
- Manajemen data wisata
- Manajemen artikel berita
- Manajemen paket wisata
- Manajemen transaksi
- Payment Gateway

## Setup

1. Clone repo

```bash
git clone https://github.com/irsyamokta/api-visitbapen.git
cd api-visitbapen
````
2. Install dependencies

```bash
composer install
````

3. Konfigurasi .env

```bash
DB_CONNECTION=
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

CLOUDINARY_KEY=
CLOUDINARY_SECRET=
CLOUDINARY_CLOUD_NAME=
CLOUDINARY_URL=
CLOUDINARY_UPLOAD_PRESET=ml_default
CLOUDINARY_NOTIFICATION_URL=
````

4. Jalankan migrasi

```bash
php artisan migrate:fresh --seed
````

5. Jalankan server

```bash
php artisan serve --host=localhost
````

## Author
- [@irsyamokta](https://github.com/irsyamokta)


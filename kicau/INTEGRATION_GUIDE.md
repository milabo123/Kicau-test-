# 🐦 Kicau — Panduan Integrasi Frontend ↔ Backend API

## Arsitektur

```
┌─────────────────────────────┐          ┌──────────────────────────────┐
│   kicau/ (Frontend)         │  HTTP    │   kicau/kicau-api/ (Backend) │
│   Laravel + Blade           │ ◄──────► │   Laravel 12 + Sanctum       │
│   http://localhost:8000     │   JSON   │   http://localhost:8001      │
└─────────────────────────────┘          └──────────────────────────────┘
```

Frontend tetap menggunakan Blade views, namun **semua data diambil dari kicau-api via HTTP** menggunakan Laravel Http facade. Token Sanctum disimpan di session Laravel.

---

## 🚀 Cara Menjalankan

### Backend (kicau-api) — Jalankan lebih dulu

```powershell
cd C:\Users\ASUS\Downloads\kicau\kicau-api

# 1. Install semua dependencies
composer install

# 2. Install Laravel Sanctum (auth token)
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# 3. Buat .env dari contoh dan generate app key
copy .env.example .env
php artisan key:generate

# 4. Edit .env — sesuaikan database:
#    DB_DATABASE=kicau_api
#    DB_USERNAME=root
#    DB_PASSWORD=    (atau password MySQL kamu)

# 5. Buat database "kicau_api" di phpMyAdmin, lalu jalankan migrasi:
php artisan migrate
php artisan storage:link

# 6. Jalankan server API di port 8001
php artisan serve --port=8001
```

### Frontend (kicau) — Jalankan di terminal kedua

```powershell
cd C:\Users\ASUS\Downloads\kicau

# Clear config cache setelah perubahan
php artisan config:clear
php artisan cache:clear

# Jalankan server frontend di port 8000
php artisan serve
```

Buka browser: **http://localhost:8000**

---

## 📋 Ringkasan Semua Perubahan

### Di kicau-api (Backend) — File Baru/Diubah

| File | Status | Keterangan |
|------|--------|------------|
| `bootstrap/app.php` | Baru | Mendaftarkan `routes/api.php` |
| `config/cors.php` | Baru | CORS untuk izinkan localhost:8000 |
| `.env.example` | Baru | Konfigurasi environment API |
| `composer.json` | Baru | Termasuk `laravel/sanctum` |
| `routes/api.php` | Baru | Semua endpoint REST API |
| `app/Models/User.php` | Baru | + `HasApiTokens` (Sanctum) |
| `app/Models/Post.php` | Baru | - |
| `app/Models/Comment.php` | Baru | - |
| `app/Models/Like.php` | Baru | - |
| `app/Models/Follow.php` | Baru | - |
| `app/Http/Controllers/Api/AuthController.php` | Baru | register, login, logout, me |
| `app/Http/Controllers/Api/PostController.php` | Baru | feed, store, show, destroy |
| `app/Http/Controllers/Api/CommentController.php` | Baru | store, destroy |
| `app/Http/Controllers/Api/LikeController.php` | Baru | toggle |
| `app/Http/Controllers/Api/FollowController.php` | Baru | toggle |
| `app/Http/Controllers/Api/ProfileController.php` | Baru | show, update |
| `database/migrations/*` | Baru | Semua 5 tabel |

### Di kicau (Frontend) — File Diubah

| File | Perubahan Utama |
|------|----------------|
| `.env` | + `KICAU_API_URL=http://localhost:8001/api` |
| `config/services.php` | + entry `kicau_api.base_url` |
| `bootstrap/app.php` | Daftarkan middleware `check.api.token` |
| `routes/web.php` | `auth` → `check.api.token`, `{post}` → `{id}` |
| `routes/auth.php` | Dipangkas, hanya login/register/logout |
| `app/Services/ApiService.php` | **File baru** — semua HTTP call ke API |
| `app/Http/Middleware/CheckApiToken.php` | **File baru** — pengganti middleware `auth` |
| `app/Http/Controllers/Auth/AuthenticatedSessionController.php` | Ganti session auth → API call + simpan token |
| `app/Http/Controllers/Auth/RegisteredUserController.php` | Ganti DB insert → API call + simpan token |
| `app/Http/Controllers/PostController.php` | Ganti Eloquent → ApiService calls |
| `app/Http/Controllers/CommentController.php` | Ganti Eloquent → ApiService calls |
| `app/Http/Controllers/LikeController.php` | Ganti Eloquent → ApiService calls |
| `app/Http/Controllers/FollowController.php` | Ganti Eloquent → ApiService calls |
| `app/Http/Controllers/ProfileController.php` | Ganti Eloquent → ApiService calls |
| `resources/views/layouts/app.blade.php` | `Auth::user()` → `session('user')` |
| `resources/views/feed/index.blade.php` | Eloquent paginator → array dari API |
| `resources/views/partials/post-card.blade.php` | Object → array access |
| `resources/views/posts/show.blade.php` | Object → array access |
| `resources/views/profile/show.blade.php` | Object → array access |
| `resources/views/profile/edit.blade.php` | `$user->xxx` → `$sessionUser['xxx']` |

---

## 🔐 Cara Kerja Auth

```
User isi form login
       ↓
AuthenticatedSessionController::store()
       ↓
ApiService::login() → POST http://localhost:8001/api/login
       ↓
kicau-api verifikasi, kembalikan { token, user }
       ↓
session(['api_token' => token, 'user' => user])
       ↓
Setiap request selanjutnya:
ApiService::authRequest() menyertakan
Authorization: Bearer {token}
```

---

## 📡 Daftar Endpoint API

| Method | URL | Keterangan |
|--------|-----|------------|
| POST | `/api/register` | Registrasi |
| POST | `/api/login` | Login, dapat token |
| POST | `/api/logout` | Logout |
| GET | `/api/me` | Data user login |
| GET | `/api/feed?page=1` | Timeline |
| POST | `/api/posts` | Buat post |
| GET | `/api/posts/{id}` | Detail post |
| DELETE | `/api/posts/{id}` | Hapus post |
| POST | `/api/posts/{id}/comments` | Tambah komentar |
| DELETE | `/api/comments/{id}` | Hapus komentar |
| POST | `/api/posts/{id}/like` | Toggle like |
| POST | `/api/users/{id}/follow` | Toggle follow |
| GET | `/api/users/{username}` | Data profil |
| PUT | `/api/profile` | Update profil |
| POST | `/api/profile/_put` | Update profil (dengan file) |

---

## ⚠️ Catatan Penting

- Kedua server **harus** berjalan bersamaan (port 8000 dan 8001)
- Fitur password reset & email verification belum diimplementasi di API
- Token **bukan** di localStorage, melainkan di server session → aman dari XSS
- Media (foto/video) disimpan di storage kicau-api, bukan di frontend

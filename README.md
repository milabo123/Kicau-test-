# Kicau App

This repository contains both the frontend web app (`kicau`) and the backend REST API (`kicau-api`).

## Standard Setup Guide

If you just cloned this project from GitHub, Laravel ignores sensitive files (`.env`) and certain empty directories by default. Because the app is split into two parts, you must configure **both** before you start!

### 1. Configure the Backend (API)
Open a terminal inside the **`kicau-api`** folder and execute:

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy the example environment file and generate app key
cp .env.example .env
php artisan key:generate

# 3. Create the symlink for your local file uploads (important for avatars!)
php artisan storage:link

# 4. Migrate your database and seed it (ensure your DB in .env is created first)
php artisan migrate --seed

# 5. Start the backend server strictly on PORT 8001
php artisan serve --port=8001
```

---

### 2. Configure the Frontend (Web)
Open a separate, second terminal inside the **`kicau`** folder and execute:

```bash
# 1. Install dependencies
composer install
npm install
npm run dev

# 2. Copy environment and generate app key
cp .env.example .env
php artisan key:generate
```

**⚠️ Important Windows `.env` step:** Open your new `kicau/.env` file and manually append this to the bottom:
```env
KICAU_API_URL="http://127.0.0.1:8001/api"
```
*(Using `127.0.0.1` instead of `localhost` prevents a notorious cURL bug on Windows environments.)*

```bash
# 3. Start the frontend server on its default port
php artisan serve --port=8000
```

### You're all set!
Navigate to `http://localhost:8000` to start using the app.

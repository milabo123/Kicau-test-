# 🐦 Kicau Social App

Kicau is a modern, full-stack micro-blogging social media platform (similar to Twitter/X or Threads). It allows users to register, post text with media (images/videos), interact via likes, reply to threads, and follow other users to build a personalized timeline feed. 

This repository is split into a decoupled **Frontend (Web App)** and **Backend (REST API)** architecture.

---

## 🏗️ Architecture & Workflow

Kicau utilizes a strictly decoupled, microservice-like architecture composed of two separate Laravel applications communicating over HTTP.

### 1. The Backend API (`kicau-api`)
- **Role**: Serves as the central data source and business logic processor.
- **Tech Stack**: Laravel 12.x, Sanctum (Token-based Authentication), MySQL.
- **Workflow**: It exposes stateless REST endpoints (e.g., `/api/posts`, `/api/login`) that accept parameters and return pure JSON responses. It handles database persistence and saves uploaded media (like avatars, photos, and videos) physically to its local `storage/app/public` disk.

### 2. The Frontend Web App (`kicau`)
- **Role**: Serves as the user interface (UI) and client-side aggregator.
- **Tech Stack**: Laravel 12.x, Blade Templates, Vanilla JS with `fetch()`, Bootstrap 5.3, SweetAlert2.
- **Workflow**: This app has **no direct database connection** (`DB_CONNECTION` is unused). Instead, it acts as an API Consumer. 
    1. **Form Submission**: When a user submits a form or interacts with the UI, Blade controllers act as intermediaries.
    2. **ApiService Integration**: The `App\Services\ApiService` class grabs the user's `api_token` from their session array and dispatches an HTTP request (using Laravel's native HTTP Client façade) over to the `kicau-api` server.
    3. **Rendering Content**: Once the API replies with JSON (e.g., a timeline feed), the frontend parses the JSON array and loops through it utilizing Blade templates to generate the HTML. For dynamic localized data (like toggling likes or inline quick-replies), Vanilla JavaScript overrides default forms, executing `fetch()` calls back directly through the Frontend endpoints to prevent page flickering.

---

## 🚀 Precise Step-by-Step Setup Tutorial

If you just cloned this project from GitHub, Laravel ignores sensitive files (`.env`) and certain empty directories by default. Because the app is split into two parts, you **must configure both independently** before you start!

### Prerequisites:
- PHP 8.2+ installed globally
- Composer installed globally
- MySQL/MariaDB Server properly running locally (e.g. via XAMPP)
- `kicau` database manually created in MySQL

### Step 1: Configure the Backend (API)
Open a terminal instance inside the **`kicau-api/`** directory representing the backend:

```bash
# 1. Install PHP dependencies
composer install

# 2. Duplicate the environment template
cp .env.example .env

# 3. Generate internal application key
php artisan key:generate

# 4. Expose the public directory so uploaded images/avatars can be accessed via URL
php artisan storage:link

# 5. Migrate the structural schema and scaffold dummy data
# Make sure your database engine runs and 'kicau' DB exists!
php artisan migrate --seed

# 6. Boot up the backend server strictly bound to PORT 8001
php artisan serve --port=8001
```
*(Leave this terminal running in the background).*

---

### Step 2: Configure the Frontend (Web)
Open a **second separate terminal instance** inside the **`kicau/`** directory representing the frontend interface:

```bash
# 1. Install PHP dependencies
composer install

# 2. Duplicate the environment template
cp .env.example .env

# 3. Generate internal application key
php artisan key:generate
```

> **⚠️ CRITICAL: Environment Linking**  
> Since the frontend relies exclusively on the API, it must know where the API lives. Open your newly duplicated frontend `kicau/.env` file and manually append this to the bottom of the file:
```env
KICAU_API_URL="http://127.0.0.1:8001/api"
```
*(Pro-tip: Using `127.0.0.1` locally instead of `localhost` prevents a notorious cURL bug occasionally found on local Windows integrations).*

```bash
# 4. Boot up the frontend serving HTML securely on its default port
php artisan serve --port=8000
```
*(Leave this terminal running in the background alongside the previous one).*

---

### 🎉 Step 3: Run It!

With both servers running concurrently, you're officially done.
- Navigate to: **`http://localhost:8000`** in your browser.
- Register a new user account, upload an avatar, and start posting!

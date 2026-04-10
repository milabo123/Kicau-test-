@echo off
echo ============================================
echo   KICAU - Setup Script
echo   Jalankan setelah XAMPP MySQL dinyalakan!
echo ============================================
echo.

set PROJECT_DIR=C:\xampp\htdocs\angkatan1-2026\kicau

:: Buat database
echo [1/4] Membuat database kicau...
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS kicau CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo     Database kicau siap.

:: Jalankan migrasi
echo.
echo [2/4] Menjalankan migrasi database...
cd /d %PROJECT_DIR%
php artisan migrate --force
echo     Migrasi selesai.

:: Isi data demo
echo.
echo [3/4] Mengisi data demo...
php artisan db:seed --force
echo     Data demo berhasil dimasukkan.

:: Buat symlink storage
echo.
echo [4/4] Membuat storage symlink...
php artisan storage:link
echo     Storage link siap.

echo.
echo ============================================
echo   KICAU SIAP DIJALANKAN!
echo   Buka: http://localhost/angkatan1-2026/kicau/public
echo   Atau jalankan: php artisan serve
echo.
echo   Demo login:
echo     Email   : demo@kicau.id
echo     Password: password
echo ============================================
pause

@echo off
echo Running Admin Setup...
echo.

echo Step 1: Running migration...
php artisan migrate --force

echo.
echo Step 2: Seeding admin users...
php artisan db:seed --class=AdminUsersSeeder

echo.
echo Setup complete!
echo.
echo You can now login with:
echo Email: john@smartbus.lk
echo Password: password123
echo.
pause

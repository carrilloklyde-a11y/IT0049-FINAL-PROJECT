@echo off
setlocal
cd /d "%~dp0"
set "EMBER_PHP=php"
if exist "C:\xampp\php\php.exe" set "EMBER_PHP=C:\xampp\php\php.exe"
"%EMBER_PHP%" -v >nul 2>&1
if errorlevel 1 (
 echo PHP 8.2 or newer is required. See README.md.
 pause
 exit /b 1
)
set "EMBER_FLAGS="
"%EMBER_PHP%" -r "exit(extension_loaded('intl') ? 0 : 1);"
if errorlevel 1 set "EMBER_FLAGS=%EMBER_FLAGS% -d extension=intl"
"%EMBER_PHP%" -r "exit(extension_loaded('gd') ? 0 : 1);"
if errorlevel 1 set "EMBER_FLAGS=%EMBER_FLAGS% -d extension=gd"
"%EMBER_PHP%" -r "exit(extension_loaded('sqlite3') ? 0 : 1);"
if errorlevel 1 set "EMBER_FLAGS=%EMBER_FLAGS% -d extension=sqlite3"
if not exist "vendor\autoload.php" (
 echo Run composer install in this folder first. See README.md.
 pause
 exit /b 1
)
if not exist ".env" copy ".env.local.example" ".env" >nul
"%EMBER_PHP%" %EMBER_FLAGS% spark migrate
if errorlevel 1 (
 pause
 exit /b 1
)
if not exist "writable\.seeded" (
 "%EMBER_PHP%" %EMBER_FLAGS% spark db:seed DemoSeeder
 if errorlevel 1 (
  pause
  exit /b 1
 )
 type nul > "writable\.seeded"
)
echo.
echo EMBER CAFE - COMPLETE CODEIGNITER POS
echo Open http://127.0.0.1:8004
echo Demo username: admin  Password: admin
echo Keep this window open. Press Ctrl+C to stop.
if not exist "writable\tmp" mkdir "writable\tmp"
"%EMBER_PHP%" %EMBER_FLAGS% -d upload_tmp_dir="%CD%\writable\tmp" -S 127.0.0.1:8004 -t public vendor/codeigniter4/framework/system/rewrite.php
pause

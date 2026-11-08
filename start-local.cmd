@echo off
setlocal
cd /d "%~dp0"
if not defined SIKHAYA_PHP (
  where php.exe >nul 2>nul
  if errorlevel 1 (
    set "SIKHAYA_PHP=C:\xampp\php\php.exe"
  ) else (
    set "SIKHAYA_PHP=php.exe"
  )
)
"%SIKHAYA_PHP%" --version >nul 2>nul
if errorlevel 1 (
  echo PHP could not be started. Set SIKHAYA_PHP to your php.exe path.
  echo See README.md for requirements and manual setup.
  pause
  exit /b 1
)
"%SIKHAYA_PHP%" scripts\setup.php
if errorlevel 1 (
  pause
  exit /b 1
)
echo.
echo Open http://127.0.0.1:8088 in your browser.
echo Keep this window open. Press Ctrl+C here to stop the website.
echo.
"%SIKHAYA_PHP%" -d upload_max_filesize=5M -d post_max_size=32M -d display_errors=0 -d log_errors=1 -d error_log=storage/logs/php.log -S 127.0.0.1:8088 -t public
pause

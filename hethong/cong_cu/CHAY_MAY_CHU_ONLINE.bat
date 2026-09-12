@echo off
title KHO HANG CUA THANH GIAU - MAY CHU ONLINE
color 0b

echo =====================================================================
echo       HE THONG QUAN LY KHO VA KIEM SOAT HANG HOA TU DONG
echo              (CHE DO MAY CHU TRUC TUYEN - TOAN CAU)
echo =====================================================================
echo.

pushd "%~dp0..\.."
set "CURRENT_DIR=%CD%"
popd

set "PHP_BIN=C:\xampp\php\php.exe"
set "MYSQL_BIN=C:\xampp\mysql\bin\mysqld.exe"
set "MY_INI=C:\xampp\mysql\bin\my.ini"
set "CF_BIN=%CURRENT_DIR%\bin\cloudflared.exe"

if not exist "%PHP_BIN%" (
    echo [!] CANH BAO: Chua tim thay PHP tai C:\xampp\php\php.exe
    echo Vui long cai dat XAMPP vao o dia C:\xampp tren may nay.
    echo.
    pause
    exit /b
)

tasklist /fi "imagename eq mysqld.exe" | find /i "mysqld.exe" >nul
if %errorlevel% neq 0 (
    echo [*] Dang khoi dong Co so du lieu MySQL...
    start /b "" "%MYSQL_BIN%" --defaults-file="%MY_INI%" --standalone
    timeout /t 2 /nobreak >nul
) else (
    echo [OK] Co so du lieu MySQL dang hoat dong san sang.
)

netstat -ano | findstr ":8000" >nul
if %errorlevel% neq 0 (
    echo [*] Dang khoi dong May chu Web tren cong 8000...
    start /b "" "%PHP_BIN%" -S 0.0.0.0:8000 -t "%CURRENT_DIR%"
    timeout /t 2 /nobreak >nul
) else (
    echo [OK] May chu Web da san sang tren cong 8000.
)

set "SERVER_IP=127.0.0.1"
for /f "tokens=4" %%a in ('route print ^| find " 0.0.0.0"') do (
    set "SERVER_IP=%%a"
    goto :ip_found
)
:ip_found

echo.
echo =====================================================================
echo   DANG TAO DUONG LINK ONLINE BAO MAT (CLOUDFLARE)...
echo =====================================================================
echo   * Link may tinh nay:    http://localhost:8000
echo   * Link mang Wi-Fi quan: http://%SERVER_IP%:8000
echo.
echo   Dang ket noi duong link Online cho dien thoai ngoai quan...
echo =====================================================================
echo.

"%CF_BIN%" tunnel --url http://127.0.0.1:8000

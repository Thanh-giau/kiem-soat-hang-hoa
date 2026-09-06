@echo off
title KHO HANG CUA THANH GIAU - MAY CHU NOI BO (LAN)
color 0a

echo =====================================================================
echo       HE THONG QUAN LY KHO VA KIEM SOAT HANG HOA TU DONG
echo                   (CHE DO MAY CHU NOI BO - LAN)
echo =====================================================================
echo.

set "CURRENT_DIR=%~dp0"
set "CURRENT_DIR=%CURRENT_DIR:~0,-1%"

set "PHP_BIN=C:\xampp\php\php.exe"
set "MYSQL_BIN=C:\xampp\mysql\bin\mysqld.exe"
set "MY_INI=C:\xampp\mysql\bin\my.ini"

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
echo   MAY CHU DA KHOI DONG THANH CONG!
echo =====================================================================
echo.
echo   * Tren may chu nay, mo trinh duyet go:
echo       http://localhost:8000
echo.
echo   * Tren Dien thoai / May tinh bang / Laptop cung mang Wi-Fi go:
echo       http://%SERVER_IP%:8000
echo.
echo =====================================================================
echo   (Cua so nay co the thu nho xuong Taskbar, khong tat de duy tri)
echo =====================================================================
echo.
pause

@echo off
title KHO HANG THANH GIAU - KHOI DONG MAY CHU POS 24/24
color 0a

set "ROOT_DIR=%~dp0"
set "ROOT_DIR=%ROOT_DIR:~0,-1%"

set "PHP_BIN=C:\xampp\php\php.exe"
set "MYSQL_BIN=C:\xampp\mysql\bin\mysql.exe"
set "MYSQLD_BIN=C:\xampp\mysql\bin\mysqld.exe"
set "MY_INI=C:\xampp\mysql\bin\my.ini"
set "CF_BIN=%ROOT_DIR%\bin\cloudflared.exe"
set "BACKUP_SQL=%ROOT_DIR%\database\quan_ly_kho_backup.sql"
set "INIT_SQL=%ROOT_DIR%\database\quan_ly_kho.sql"

echo =====================================================================
echo       KHO HANG CUA THANH GIAU - KHOI DONG HE THONG TU DONG 24/24
echo =====================================================================
echo.

:: 1. Kiem tra XAMPP
if not exist "%PHP_BIN%" (
    echo [!] CANH BAO: May tinh nay chua cai dat XAMPP tai C:\xampp
    echo Vui long cai dat XAMPP tai: https://www.apachefriends.org/download.html
    echo (Luu y cai vao thu muc mac dinh C:\xampp).
    echo.
    pause
    exit /b
)

:: 2. Khoi dong MySQL ngam
tasklist /fi "imagename eq mysqld.exe" | find /i "mysqld.exe" >nul
if %errorlevel% neq 0 (
    echo [*] Dang khoi dong co so du lieu MySQL...
    start /b "" "%MYSQLD_BIN%" --defaults-file="%MY_INI%" --standalone
    timeout /t 3 /nobreak >nul
) else (
    echo [OK] Co so du lieu MySQL dang hoat dong san sang.
)

:: 3. Tu dong kiem tra va nap Database neu may POS moi chua co du lieu
"%MYSQL_BIN%" -u root -e "USE quan_ly_kho;" >nul 2>&1
if %errorlevel% neq 0 (
    echo [*] Phat hien may POS moi - Dang tu dong tao co so du lieu kho...
    "%MYSQL_BIN%" -u root -e "CREATE DATABASE IF NOT EXISTS quan_ly_kho CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    if exist "%BACKUP_SQL%" (
        echo [*] Dang nap du lieu tu ban sao luu moi nhat: quan_ly_kho_backup.sql ...
        "%MYSQL_BIN%" -u root quan_ly_kho < "%BACKUP_SQL%"
    ) else if exist "%INIT_SQL%" (
        echo [*] Dang nap du lieu tu file mac dinh: quan_ly_kho.sql ...
        "%MYSQL_BIN%" -u root quan_ly_kho < "%INIT_SQL%"
    )
    echo [OK] Da khoi tao du lieu kho ban dau thanh cong!
) else (
    echo [OK] Co so du lieu kho da ton tai san sang.
)

:: 4. Khoi dong Web Server PHP ngam tren cong 8000
netstat -ano | findstr ":8000" | findstr "LISTENING" >nul
if %errorlevel% neq 0 (
    echo [*] Dang khoi dong May chu Web ngam...
    start /b "" "%PHP_BIN%" -S 0.0.0.0:8000 -t "%ROOT_DIR%"
    timeout /t 2 /nobreak >nul
) else (
    echo [OK] May chu Web da san sang tren cong 8000.
)

:: 5. Khoi dong Cloudflare Tunnel Online ngam (Cho dien thoai 4G)
tasklist /fi "imagename eq cloudflared.exe" | find /i "cloudflared.exe" >nul
if %errorlevel% neq 0 (
    if exist "%CF_BIN%" (
        echo [*] Dang mo duong truyen ket noi Online cho dien thoai...
        start /b "" "%CF_BIN%" tunnel --url http://127.0.0.1:8000
        timeout /t 2 /nobreak >nul
    )
) else (
    echo [OK] Duong truyen Online Cloudflare dang hoat dong tot.
)

:: 6. Tu dong thiet lap Khoi Dong Cung Windows (Chi can chay 1 lan nay la mai mai tu chay)
set "STARTUP_DIR=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup"
set "SHORTCUT_PATH=%STARTUP_DIR%\KhoHangServer247.lnk"
if not exist "%SHORTCUT_PATH%" (
    powershell -NoProfile -ExecutionPolicy Bypass -Command "$ws = New-Object -ComObject WScript.Shell; $s = $ws.CreateShortcut('%SHORTCUT_PATH%'); $s.TargetPath = '%ROOT_DIR%\CHAY_HE_THONG_POS.bat'; $s.WorkingDirectory = '%ROOT_DIR%'; $s.WindowStyle = 7; $s.Save()" >nul 2>&1
)

:: 7. Mo trinh duyet vao Web
echo [*] Dang mo giao dien quan ly kho tren man hinh...
start http://localhost:8000

echo.
echo =====================================================================
echo   [HOAN TAT] MAY CHU DANG CHAY NGAM 24/24!
echo.
echo   * Thu ngan van ban hang tren may POS binh thuong 100%%.
echo   * Khi may POS khoi dong lai, he thong se TU DONG CHAY LAI.
echo   * De cap nhat tinh nang moi tu xa: Vao web bam nut "Cap Nhat He Thong".
echo.
echo   Cua so nay se tu dong bien mat sau 3 giay de man hinh POS sach se.
echo =====================================================================
echo.
timeout /t 3
exit

@echo off
title KHO HANG THANH GIAU - CAI DAT MAY POS LAM MAY CHU 24/24
color 0a

echo =====================================================================
echo    CAI DAT TU DONG: BIEN MAY POS THANH MAY CHU QUAN LY KHO 24/24
echo =====================================================================
echo.

set "SCRIPT_DIR=%~dp0"
set "SCRIPT_DIR=%SCRIPT_DIR:~0,-1%"
set "PHP_BIN=C:\xampp\php\php.exe"
set "MYSQL_BIN=C:\xampp\mysql\bin\mysql.exe"
set "MYSQLD_BIN=C:\xampp\mysql\bin\mysqld.exe"
set "MY_INI=C:\xampp\mysql\bin\my.ini"

:: 1. Kiem tra XAMPP
if not exist "%PHP_BIN%" (
    echo [!] CANH BAO: May POS nay chua cai dat XAMPP tai C:\xampp
    echo.
    echo Vui long tai va cai dat XAMPP cho Windows:
    echo Link tai: https://www.apachefriends.org/download.html
    echo (Luu y: Chon cai vao thu muc mac dinh C:\xampp).
    echo.
    pause
    exit /b
)
echo [OK] Da tim thay PHP va MySQL tai C:\xampp.

:: 2. Khoi dong MySQL neu chua chay
tasklist /fi "imagename eq mysqld.exe" | find /i "mysqld.exe" >nul
if %errorlevel% neq 0 (
    echo [*] Dang khoi dong co so du lieu MySQL...
    start /b "" "%MYSQLD_BIN%" --defaults-file="%MY_INI%" --standalone
    timeout /t 3 /nobreak >nul
) else (
    echo [OK] Co so du lieu MySQL dang hoat dong.
)

:: 3. Khoi tao co so du lieu quan_ly_kho neu chua co
echo [*] Kiem tra co so du lieu quan_ly_kho...
"%MYSQL_BIN%" -u root -e "USE quan_ly_kho;" >nul 2>&1
if %errorlevel% neq 0 (
    echo [*] Dang tao moi va nap du lieu kho ban dau vao may POS...
    "%MYSQL_BIN%" -u root -e "CREATE DATABASE IF NOT EXISTS quan_ly_kho CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    "%MYSQL_BIN%" -u root quan_ly_kho < "%SCRIPT_DIR%\database\quan_ly_kho_backup.sql"
    echo [OK] Da khoi tao du lieu kho thanh cong!
) else (
    echo [OK] Co so du lieu quan_ly_kho da ton tai san sang.
)

:: 4. Thiet lap tu dong khoi dong ngam cung Windows
echo [*] Dang cai dat che do Tu Dong Khoi Dong Ngam 24/24 khi bat may POS...
call "%SCRIPT_DIR%\BAT_KHOI_DONG_CUNG_WINDOWS.bat" >nul 2>&1
echo [OK] Da dang ky thanh cong vao Windows Startup!

:: 5. Khoi dong may chu ngam ngay bay gio
echo [*] Dang khoi dong may chu ngam khong cua so...
wscript.exe "%SCRIPT_DIR%\CHAY_NGAM_CHO_POS.vbs"
timeout /t 3 /nobreak >nul

echo.
echo =====================================================================
echo          CHUC MUNG! MAY POS DA TRO THANH MAY CHU 24/24!
echo =====================================================================
echo.
echo  * May chu dang CHAY HOAN TOAN NGAM DUOI NEN (Khong vuong man hinh POS).
echo  * Thu ngan van ban hang tren may POS nhu binh thuong 100%%.
echo  * Moi khi khoi dong lai may POS, he thong se TU DONG CHAY LAI.
echo.
echo  * KHI CO CHUC NANG MOI TU MAY TINH CUA BAN:
echo    - Chi can nhap dup vao file: CAP_NHAT_TU_GITHUB.bat
echo    - Hoac vao web tren dien thoai bam nut "Cap Nhat He Thong" la xong!
echo.
echo =====================================================================
echo.

call "%SCRIPT_DIR%\KIEM_TRA_TRANG_THAI.bat"

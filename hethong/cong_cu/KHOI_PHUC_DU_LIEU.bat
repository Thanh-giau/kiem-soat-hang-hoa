@echo off
title KHOI PHUC DU LIEU KHO HANG
color 0e

echo =====================================================================
echo                KHOI PHUC CO SO DU LIEU SANG MAY NAY
echo =====================================================================
echo.

pushd "%~dp0..\.."
set "ROOT_DIR=%CD%"
popd

set "MYSQL_BIN=C:\xampp\mysql\bin\mysql.exe"
set "BACKUP_FILE=%ROOT_DIR%\database\quan_ly_kho_backup.sql"
set "INIT_FILE=%ROOT_DIR%\database\quan_ly_kho.sql"

if not exist "%MYSQL_BIN%" (
    echo [!] Chua tim thay MySQL tai C:\xampp\mysql\bin\mysql.exe
    echo Hay chac chan ban da cai XAMPP tren may nay.
    pause
    exit /b
)

set "FILE_TO_RESTORE="
if exist "%BACKUP_FILE%" (
    set "FILE_TO_RESTORE=%BACKUP_FILE%"
    echo [*] Tim thay file sao luu moi nhat: database\quan_ly_kho_backup.sql
) else if exist "%INIT_FILE%" (
    set "FILE_TO_RESTORE=%INIT_FILE%"
    echo [*] Tim thay file co so du lieu mac dinh: database\quan_ly_kho.sql
) else (
    echo [!] Khong tim thay file du lieu SQL trong thu muc database\
    pause
    exit /b
)

echo [*] Dang nap du lieu vao co so du lieu...
"%MYSQL_BIN%" -u root -e "source %FILE_TO_RESTORE:\=/%"

if %errorlevel% equ 0 (
    echo.
    echo =====================================================================
    echo   [THANH CONG] Du lieu kho da duoc nap hoan tat vao may chu nay!
    echo   Ban co the chay file CHAY_MAY_CHU_LAN.bat de su dung ngay.
    echo =====================================================================
) else (
    echo.
    echo [!] Khoi phuc that bai! Hay chac chan MySQL dang duoc bat.
)
echo.
pause

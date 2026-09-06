@echo off
title SAO LUU DU LIEU KHO HANG
color 0a

echo =====================================================================
echo                SAO LUU TOAN BO CO SO DU LIEU KHO
echo =====================================================================
echo.

set "MYSQLDUMP_BIN=C:\xampp\mysql\bin\mysqldump.exe"
set "BACKUP_FILE=%~dp0database\quan_ly_kho_backup.sql"

if not exist "%MYSQLDUMP_BIN%" (
    echo [!] Khong tim thay cong cu mysqldump tai C:\xampp\mysql\bin\mysqldump.exe
    pause
    exit /b
)

echo [*] Dang xuat toan bo du lieu ra file database\quan_ly_kho_backup.sql ...
"%MYSQLDUMP_BIN%" -u root --default-character-set=utf8mb4 --databases quan_ly_kho --result-file="%BACKUP_FILE%"

if %errorlevel% equ 0 (
    echo.
    echo =====================================================================
    echo   [THANH CONG] Du lieu kho da duoc sao luu an toan!
    echo   Vi tri file: database\quan_ly_kho_backup.sql
    echo =====================================================================
) else (
    echo.
    echo [!] Xuat du lieu that bai! Hay chac chan MySQL dang duoc bat.
)
echo.
pause

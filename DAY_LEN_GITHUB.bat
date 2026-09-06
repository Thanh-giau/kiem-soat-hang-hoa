@echo off
title DAY CODE LEN GITHUB
color 0b

echo =====================================================================
echo                DONG BO VA DAY CODE LEN GITHUB
echo =====================================================================
echo.

set "DEFAULT_URL=https://github.com/Thanh-giau/kiem-soat-hang-hoa.git"

:: Kiem tra remote
git remote get-url origin >nul 2>&1
if %errorlevel% neq 0 (
    echo [*] Dang thiet lap ket noi toi GitHub: %DEFAULT_URL%
    git remote add origin %DEFAULT_URL%
) else (
    echo [OK] Da co ket noi toi GitHub.
)

echo [*] Dang chuan bi day toan bo ma nguon len nhanh main...
echo.

git branch -M main
git push -u origin main

if %errorlevel% equ 0 (
    echo.
    echo =====================================================================
    echo   [THANH CONG] Toan bo du an da duoc day len GitHub thanh cong!
    echo   Xem ma nguon tai: %DEFAULT_URL%
    echo =====================================================================
) else (
    echo.
    echo =====================================================================
    echo   [!] Neu bi bao loi, hay chac chan ban da bam nut "Create repository"
    echo       tren trang web GitHub va ten kho la "kiem-soat-hang-hoa".
    echo =====================================================================
)

echo.
pause

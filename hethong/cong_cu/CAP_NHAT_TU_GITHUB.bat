@echo off
title KHO HANG CUA THANH GIAU - CAP NHAT CHUC NANG MOI
color 0b

echo =====================================================================
echo       DONG BO VA CAP NHAT CHUC NANG MOI NHAT TU GITHUB
echo =====================================================================
echo.

set "SCRIPT_DIR=%~dp0"
set "SCRIPT_DIR=%SCRIPT_DIR:~0,-1%"

:: 1. Tu dong sao luu du lieu hien tai truoc khi cap nhat
echo [*] Buoc 1/3: Dang tu dong sao luu co so du lieu kho hien tai...
call "%SCRIPT_DIR%\SAO_LUU_DU_LIEU.bat" >nul 2>&1
echo [OK] Du lieu da duoc sao luu an toan truoc khi cap nhat.
echo.

:: 2. Kiem tra Git
where git >nul 2>&1
if %errorlevel% neq 0 (
    echo [!] CANH BAO: May POS chua cai dat Git!
    echo Vui long tai va cai dat Git cho Windows tai: https://git-scm.com/download/win
    echo (Cai dat rat nhanh, chi can bam Next lien tuc).
    echo.
    pause
    exit /b
)

:: 3. Keo ma nguon moi nhat tu GitHub ve
echo [*] Buoc 2/3: Dang ket noi GitHub va tai cac chuc nang moi nhat ve...
echo.

cd /d "%SCRIPT_DIR%\..\.."
git fetch origin main
git merge origin/main

if %errorlevel% equ 0 (
    echo.
    echo =====================================================================
    echo   [THANH CONG] May POS da duoc cap nhat len phien ban moi nhat!
    echo.
    echo   Cac thay doi moi nhat:
    git log -n 3 --oneline
    echo =====================================================================
) else (
    echo.
    echo [!] Co xung dot file hoac chua the keo code ve.
    echo Dang thu che do dong bo ghi de ma nguon an toan (giu nguyen du lieu Database)...
    git reset --hard origin/main
    echo [OK] Da dong bo ma nguon hoan tat.
)

echo.
echo [*] Buoc 3/3: Kiem tra may chu web...
netstat -ano | findstr ":8000" | findstr "LISTENING" >nul
if %errorlevel% equ 0 (
    echo [OK] May chu Web dang hoat dong, ban co the su dung chuc nang moi ngay!
) else (
    echo [*] Khoi dong lai may chu ngam...
    wscript.exe "%SCRIPT_DIR%\CHAY_NGAM_CHO_POS.vbs"
    echo [OK] May chu da san sang!
)

echo.
echo =====================================================================
echo   Chuc mung! He thong da san sang voi cac tinh nang moi nhat.
echo =====================================================================
echo.
pause

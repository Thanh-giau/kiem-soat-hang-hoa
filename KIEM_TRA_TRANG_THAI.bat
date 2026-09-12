@echo off
title KHO HANG CUA THANH GIAU - KIEM TRA TRANG THAI MAY CHU
color 0b

echo =====================================================================
echo          KIEM TRA TRANG THAI MAY CHU KHO HANG THANH GIAU
echo =====================================================================
echo.

:: 1. Kiem tra MySQL
tasklist /fi "imagename eq mysqld.exe" | find /i "mysqld.exe" >nul
if %errorlevel% equ 0 (
    echo  [v] Co so du lieu MySQL:   DANG HOAT DONG SAN SANG
) else (
    echo  [X] Co so du lieu MySQL:   CHUA KHOI DONG
)

:: 2. Kiem tra PHP Web Server
netstat -ano | findstr ":8000" | findstr "LISTENING" >nul
if %errorlevel% equ 0 (
    echo  [v] May chu Web (Cong 8000): DANG HOAT DONG (SAN SANG)
) else (
    echo  [X] May chu Web (Cong 8000): CHUA KHOI DONG
)

:: 3. Kiem tra Cloudflare
tasklist /fi "imagename eq cloudflared.exe" | find /i "cloudflared.exe" >nul
if %errorlevel% equ 0 (
    echo  [v] Ket noi Online Tu Xa:  DANG KET NOI (CLOUDFLARE)
) else (
    echo  [X] Ket noi Online Tu Xa:  CHUA KHOI DONG
)

echo.
echo =====================================================================
echo   DIA CHI TRUY CAP HIEN TAI:
echo =====================================================================

powershell -NoProfile -ExecutionPolicy Bypass -Command "
try {
    $r = Invoke-RestMethod -Uri 'http://127.0.0.1:20241/quicktunnel' -TimeoutSec 1 -ErrorAction SilentlyContinue
    if ($r -and $r.hostname) {
        Write-Host '  * Link Online (4G / Ngoai quan): https://' $r.hostname -ForegroundColor Green
    }
} catch {}
$lanIp = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -like '192.168.*' -and $_.IPAddress -notlike '*.1' -and $_.IPAddress -notlike '*.255' } | Select-Object -First 1).IPAddress
if ($lanIp) {
    Write-Host '  * Link Mang Wi-Fi Quan (LAN):    http://' $lanIp ':8000' -ForegroundColor Cyan
}
Write-Host '  * Link Tren May Tinh Nay:        http://localhost:8000' -ForegroundColor Yellow
"

echo.
echo =====================================================================
echo   Meo: De khoi dong may chu ngam, chay: CHAY_NGAM_CHO_POS.vbs
echo        De dung may chu, chay:           DUNG_MAY_CHU.bat
echo =====================================================================
echo.
pause

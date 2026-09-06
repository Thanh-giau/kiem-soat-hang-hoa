@echo off
title KHO HANG CUA THANH GIAU - DUNG MAY CHU
color 0c

echo =====================================================================
echo              DANG DUNG MAY CHU VA DICH VU NEN...
echo =====================================================================
echo.

taskkill /f /im cloudflared.exe >nul 2>&1
echo [*] Da ngat ket noi Cloudflare Tunnel.

taskkill /f /im php.exe >nul 2>&1
echo [*] Da dung may chu Web PHP.

taskkill /f /im mysqld.exe >nul 2>&1
echo [*] Da dong co so du lieu MySQL an toan.

echo.
echo =====================================================================
echo              DA DUNG TOAN BO MAY CHU THANH CONG!
echo =====================================================================
echo.
timeout /t 3

@echo off
title KHO HANG CUA THANH GIAU - TAT KHOI DONG CUNG WINDOWS
color 0c

echo =====================================================================
echo           TAT CHE DO KHOI DONG CUNG WINDOWS
echo =====================================================================
echo.

set "STARTUP_DIR=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup"
set "SHORTCUT_PATH=%STARTUP_DIR%\KhoHangServer247.lnk"

if exist "%SHORTCUT_PATH%" (
    del /f /q "%SHORTCUT_PATH%"
    echo [OK] Da xoa loi tat khoi dong tu dong thanh cong.
    echo He thong se KHONG tu dong chay khi bat may POS nua.
) else (
    echo [*] He thong hien chua duoc cai dat khoi dong tu dong.
)

echo.
pause

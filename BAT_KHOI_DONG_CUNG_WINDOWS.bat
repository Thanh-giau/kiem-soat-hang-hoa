@echo off
title KHO HANG CUA THANH GIAU - BAT KHOI DONG CUNG WINDOWS
color 0a

echo =====================================================================
echo    THIET LAP KHOI DONG NGAM 24/24 CUNG WINDOWS CHO MAY POS
echo =====================================================================
echo.

set "SCRIPT_DIR=%~dp0"
set "SCRIPT_DIR=%SCRIPT_DIR:~0,-1%"
set "VBS_PATH=%SCRIPT_DIR%\CHAY_NGAM_CHO_POS.vbs"
set "STARTUP_DIR=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup"

if not exist "%VBS_PATH%" (
    echo [!] LOI: Khong tim thay file %VBS_PATH%
    pause
    exit /b
)

echo [*] Dang tao loi tat (Shortcut) vao thu muc Khoi dong Windows...
echo     Thu muc: %STARTUP_DIR%
echo.

powershell -NoProfile -ExecutionPolicy Bypass -Command "$ws = New-Object -ComObject WScript.Shell; $s = $ws.CreateShortcut('%STARTUP_DIR%\KhoHangServer247.lnk'); $s.TargetPath = 'wscript.exe'; $s.Arguments = '\"%VBS_PATH%\"'; $s.WorkingDirectory = '%SCRIPT_DIR%'; $s.WindowStyle = 7; $s.Save()"

if %errorlevel% equ 0 (
    echo =====================================================================
    echo   [THANH CONG] Da thiet lap may chu khoi dong ngam 24/24 cung Windows!
    echo.
    echo   Tu bay gio:
    echo   - Moi khi may POS bat nguon / khoi dong lai may tinh:
    echo     He thong quan ly kho se TU DONG CHAY NGAM duoi nen.
    echo   - Khong co bat ky cua so mau den nao hien len man hinh.
    echo   - Thu ngan ban hang tren may POS su dung hoan toan binh thuong.
    echo =====================================================================
) else (
    echo [!] Co loi xay ra khi tao shortcut. Vui long chay lai voi quyen Administrator.
)

echo.
pause

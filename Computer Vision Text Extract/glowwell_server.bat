@echo off
title GlowWell Server
cd /d "%~dp0"

:menu
echo.
echo  ========================================
echo    GlowWell - Server Options
echo  ========================================
echo.
echo   [1] Install required libraries
echo   [2] Start server
echo   [0] Exit
echo.
set /p choice="  Enter 0, 1 or 2: "

if "%choice%"=="1" goto install
if "%choice%"=="2" goto start_server
if "%choice%"=="0" exit /b 0
echo  Invalid option.
goto menu

:install
echo.
echo  --- Installing Python libraries ---
where python >nul 2>&1
if errorlevel 1 (
    echo  Python not found in PATH. Install Python from python.org
    pause
    goto menu
)
python -m pip install --upgrade pip
pip install -r requirements.txt
if errorlevel 1 (
    echo  Install failed. Check path and requirements.txt
) else (
    echo  Libraries installed successfully.
)
echo.
pause
goto menu

:start_server
echo.
echo  --- Starting services ---

net start Apache2.4 2>nul
if errorlevel 1 net start Apache 2>nul
if errorlevel 1 net start Apache2.2 2>nul

net start MySQL 2>nul
if errorlevel 1 net start MySQL56 2>nul
if errorlevel 1 net start MySQL57 2>nul
if errorlevel 1 net start MySQL80 2>nul

echo  Starting Flask app on port 5001...
start "FlaskApp" cmd /k "cd /d "%~dp0" && python app.py"
echo.
echo  Site: http://localhost/glowwell/
echo  Scan Meal: http://127.0.0.1:5001
echo.
pause
goto menu

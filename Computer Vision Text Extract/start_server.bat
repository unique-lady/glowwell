@echo off
chcp 65001 >nul
title GlowWell — خادم مسح الوجبة (Flask)
cd /d "%~dp0"

where python >nul 2>&1
if errorlevel 1 (
    echo Python غير موجود في PATH. ثبّت Python من python.org
    pause
    exit /b 1
)

echo.
echo  تشغيل الخادم: http://127.0.0.1:5001
echo  أوقف الخادم بإغلاق هذه النافذة أو Ctrl+C
echo.
python app.py
pause

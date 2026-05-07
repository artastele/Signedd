@echo off
REM Tesseract OCR Auto-Setup Script for SPED LMS
REM This will automatically configure Tesseract for your system

echo ========================================
echo Tesseract OCR Auto-Setup
echo ========================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% == 0 (
    echo Running as Administrator... OK
) else (
    echo WARNING: Not running as Administrator
    echo Some features may not work properly
    echo.
)

REM Find Tesseract installation
echo Searching for Tesseract installation...
echo.

set TESSERACT_PATH=
set FOUND=0

REM Check common paths
if exist "C:\Program Files\Tesseract-OCR\tesseract.exe" (
    set TESSERACT_PATH=C:\Program Files\Tesseract-OCR
    set TESSERACT_EXE=C:\Program Files\Tesseract-OCR\tesseract.exe
    set FOUND=1
    echo [FOUND] C:\Program Files\Tesseract-OCR\tesseract.exe
)

if exist "C:\Program Files (x86)\Tesseract-OCR\tesseract.exe" (
    set TESSERACT_PATH=C:\Program Files (x86)\Tesseract-OCR
    set TESSERACT_EXE=C:\Program Files (x86)\Tesseract-OCR\tesseract.exe
    set FOUND=1
    echo [FOUND] C:\Program Files (x86)\Tesseract-OCR\tesseract.exe
)

echo.

if %FOUND%==0 (
    echo [ERROR] Tesseract NOT found!
    echo.
    echo Please install Tesseract first:
    echo 1. Download from: https://github.com/UB-Mannheim/tesseract/wiki
    echo 2. Run the installer
    echo 3. Run this script again
    echo.
    pause
    exit /b 1
)

echo ========================================
echo Found Tesseract at:
echo %TESSERACT_EXE%
echo ========================================
echo.

REM Test Tesseract
echo Testing Tesseract...
"%TESSERACT_EXE%" --version >nul 2>&1
if %errorLevel% == 0 (
    echo [OK] Tesseract is working!
    "%TESSERACT_EXE%" --version
) else (
    echo [ERROR] Tesseract test failed!
    pause
    exit /b 1
)

echo.
echo ========================================
echo Adding Tesseract to User PATH...
echo ========================================
echo.

REM Add to User PATH using PowerShell
powershell -Command "$oldPath = [Environment]::GetEnvironmentVariable('Path', 'User'); if ($oldPath -notlike '*Tesseract-OCR*') { $newPath = $oldPath + ';%TESSERACT_PATH%'; [Environment]::SetEnvironmentVariable('Path', $newPath, 'User'); Write-Host '[OK] Added to PATH' } else { Write-Host '[OK] Already in PATH' }"

echo.
echo ========================================
echo Updating SPED LMS Configuration...
echo ========================================
echo.

REM Update config/tesseract.php with correct path
set CONFIG_FILE=config\tesseract.php

if exist "%CONFIG_FILE%" (
    echo Updating %CONFIG_FILE%...
    
    REM Create backup
    copy "%CONFIG_FILE%" "%CONFIG_FILE%.backup" >nul
    echo [OK] Backup created: %CONFIG_FILE%.backup
    
    REM Update the manual path in config
    powershell -Command "(Get-Content '%CONFIG_FILE%') -replace \"\\$manualPath = '.*';\", \"`$manualPath = '%TESSERACT_EXE:\=\\%';\" | Set-Content '%CONFIG_FILE%'"
    
    echo [OK] Configuration updated
) else (
    echo [WARNING] Config file not found: %CONFIG_FILE%
)

echo.
echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Next steps:
echo 1. Restart XAMPP Apache
echo 2. Open: http://localhost/Signedd/public/test_ocr.php
echo 3. Check if all tests show green checkmarks
echo 4. Test OCR Auto-Fill in PDSP form
echo.
echo If you see any errors, close all Command Prompt windows
echo and restart your computer, then try again.
echo.
pause

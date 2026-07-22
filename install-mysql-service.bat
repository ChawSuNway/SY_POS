@echo off
chcp 65001 >nul
REM ============================================================
REM  MySQL (MariaDB) ကို Windows Service အဖြစ် install လုပ်ရန်
REM  ဒီဖိုင်ကို Right-click -> "Run as administrator" နှိပ်ပါ
REM ============================================================

net session >nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo   [ERROR] Administrator အဖြစ် run ရပါမည်!
    echo   ဒီဖိုင်ကို Right-click ပြီး "Run as administrator" ရွေးပါ။
    echo.
    pause
    exit /b 1
)

echo [1/4] လက်ရှိ run နေသော mysqld ကို ရပ်နေသည်...
taskkill /IM mysqld.exe /F >nul 2>&1
timeout /t 3 /nobreak >nul

echo [2/4] mysql service ကို install လုပ်နေသည်...
"C:\xampp\mysql\bin\mysqld.exe" --install mysql --defaults-file="C:\xampp\mysql\bin\my.ini"

echo [3/4] Auto-start သတ်မှတ်နေသည်...
sc config mysql start= auto >nul

echo [4/4] Service စတင်နေသည်...
net start mysql

echo.
echo   ✓ ပြီးပါပြီ! MySQL သည် Windows Service ဖြစ်သွားပြီး
echo     စက်ဖွင့်တိုင်း အလိုအလျောက် တက်ပါမည်။
echo.
pause

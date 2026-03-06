@echo off
title TechNova AI E-Commerce — Start All Services
color 0A

echo ============================================================
echo   TechNova AI E-Commerce — Starting All Services
echo ============================================================
echo.

set "ROOT=D:\AI-driven-personalized-ecommerce"

:: ── 1. Event Service (Port 8000) ─────────────────────────────────────────────
echo [1/3] Starting Event Service on port 8000...
start "Event Service :8000" cmd /k "cd /d %ROOT%\services\event-service && set PYTHONIOENCODING=utf-8 && .venv\Scripts\uvicorn main:app --host 0.0.0.0 --port 8000 --reload"
timeout /t 3 /nobreak > nul

:: ── 2. AI / ML Service (Port 8001) ───────────────────────────────────────────
echo [2/3] Starting AI/ML Service on port 8001...
start "AI Service :8001" cmd /k "cd /d %ROOT%\services\ai-service && set PYTHONIOENCODING=utf-8 && .venv\Scripts\uvicorn main:app --host 0.0.0.0 --port 8001 --reload"
timeout /t 3 /nobreak > nul

:: ── 3. Laravel Backend (Port 9090) ───────────────────────────────────────────
echo [3/3] Starting Laravel Backend on port 9090...
start "Laravel :9090" cmd /k "cd /d %ROOT%\backend && php artisan serve --port=9090"
timeout /t 4 /nobreak > nul

echo.
echo ============================================================
echo   All services started in separate windows!
echo ============================================================
echo.
echo   Store Frontend  : http://localhost:9090
echo   Event Service   : http://localhost:8000/docs
echo   AI/ML Service   : http://localhost:8001/docs
echo   Admin Panel     : http://localhost:9090/admin
echo   AI Health Check : http://localhost:9090/admin/ai-health
echo.
echo   Demo Login: demo@technova.com / password
echo ============================================================
echo.
echo   Press any key to open the store in your browser...
pause > nul
start http://localhost:9090

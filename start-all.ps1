# TechNova AI E-Commerce — Start All Services
# Run with: powershell -ExecutionPolicy Bypass -File start-all.ps1

$ROOT = "D:\AI-driven-personalized-ecommerce"

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "  TechNova AI E-Commerce — Starting All Services" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

# ── 1. Event Service (Port 8000) ─────────────────────────────────────────────
Write-Host "[1/3] Starting Event Service on port 8000..." -ForegroundColor Yellow
Start-Process "cmd" -ArgumentList "/k cd /d `"$ROOT\services\event-service`" && set PYTHONIOENCODING=utf-8 && .venv\Scripts\uvicorn main:app --host 0.0.0.0 --port 8000 --reload" -WindowStyle Normal
Start-Sleep -Seconds 3

# ── 2. AI / ML Service (Port 8001) ───────────────────────────────────────────
Write-Host "[2/3] Starting AI/ML Service on port 8001..." -ForegroundColor Yellow
Start-Process "cmd" -ArgumentList "/k cd /d `"$ROOT\services\ai-service`" && set PYTHONIOENCODING=utf-8 && .venv\Scripts\uvicorn main:app --host 0.0.0.0 --port 8001 --reload" -WindowStyle Normal
Start-Sleep -Seconds 3

# ── 3. Laravel Backend (Port 9090) ───────────────────────────────────────────
Write-Host "[3/3] Starting Laravel Backend on port 9090..." -ForegroundColor Yellow
Start-Process "cmd" -ArgumentList "/k cd /d `"$ROOT\backend`" && php artisan serve --port=9090" -WindowStyle Normal
Start-Sleep -Seconds 4

Write-Host ""
Write-Host "============================================================" -ForegroundColor Green
Write-Host "  All services started!" -ForegroundColor Green
Write-Host "============================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Store Frontend  : http://localhost:9090" -ForegroundColor White
Write-Host "  Event Service   : http://localhost:8000/docs" -ForegroundColor White
Write-Host "  AI/ML Service   : http://localhost:8001/docs" -ForegroundColor White
Write-Host "  Admin Panel     : http://localhost:9090/admin" -ForegroundColor White
Write-Host "  AI Health Check : http://localhost:9090/admin/ai-health" -ForegroundColor White
Write-Host ""
Write-Host "  Demo Login: demo@technova.com / password" -ForegroundColor Gray
Write-Host "============================================================" -ForegroundColor Green
Write-Host ""

# Health check after 10 seconds
Write-Host "Waiting 10s then running health checks..." -ForegroundColor Gray
Start-Sleep -Seconds 10

$services = @(
    @{ Name = "Event Service"; URL = "http://localhost:8000/health" },
    @{ Name = "AI Service";    URL = "http://localhost:8001/health" },
    @{ Name = "Laravel";       URL = "http://localhost:9090" }
)

Write-Host "Health Checks:" -ForegroundColor Cyan
foreach ($svc in $services) {
    try {
        $r = Invoke-WebRequest -Uri $svc.URL -TimeoutSec 3 -UseBasicParsing -ErrorAction Stop
        Write-Host "  [OK] $($svc.Name)" -ForegroundColor Green
    } catch {
        Write-Host "  [--] $($svc.Name) - not yet ready" -ForegroundColor Red
    }
}

Write-Host ""
Start-Process "http://localhost:9090"

@echo off
cd /d "%~dp0"
if not exist .env copy .env.example .env >nul
docker compose --env-file .env -f docker-compose.dev.yml build
if errorlevel 1 exit /b 1

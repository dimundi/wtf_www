@echo off
cd /d "%~dp0"
if not exist .env copy .env.example .env >nul
docker compose --env-file .env -f docker-compose.dev.yml up -d --build --wait
if errorlevel 1 exit /b 1
docker compose --env-file .env -f docker-compose.dev.yml exec -T wordpress sh /usr/local/bin/init-dev.sh

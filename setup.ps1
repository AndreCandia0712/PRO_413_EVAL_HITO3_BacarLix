# Version Windows (PowerShell) de setup.sh. Ejecutar UNA sola vez en la raiz del repo.
if (Test-Path artisan) { Write-Host "Laravel ya esta instalado."; exit }

$script = @'
composer create-project laravel/laravel base --no-interaction --prefer-dist &&
cd base && find . -type f ! -name ".env" ! -path "./vendor/*" | while read f; do
  if [ ! -e "/work/$f" ]; then mkdir -p "/work/$(dirname "$f")"; cp "$f" "/work/$f"; fi
done
'@
docker run --rm -e COMPOSER_HOME=/tmp/composer -v "${PWD}:/work" -w /tmp composer:2 sh -c $script.Replace("`r","")

if (-not (Test-Path .env)) { Copy-Item .env.example .env }
$bytes = New-Object byte[] 32
[System.Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($bytes)
$key = "base64:" + [Convert]::ToBase64String($bytes)
(Get-Content .env) -replace '^APP_KEY=.*', "APP_KEY=$key" | Set-Content .env
Write-Host "Listo. Edite .env y ejecute: docker compose up -d --build"

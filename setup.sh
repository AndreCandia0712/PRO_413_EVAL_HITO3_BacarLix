#!/bin/sh
# Genera el esqueleto base de Laravel (con Docker, sin instalar PHP) y lo combina
# con los archivos personalizados de este repo SIN sobrescribirlos.
# Ejecutar UNA sola vez (solo una persona), luego hacer commit y push.
set -e
if [ -f artisan ]; then echo "Laravel ya esta instalado."; exit 0; fi

docker run --rm -u "$(id -u):$(id -g)" -e COMPOSER_HOME=/tmp/composer \
  -v "$PWD":/work -w /tmp composer:2 sh -c '
    composer create-project laravel/laravel base --no-interaction --prefer-dist &&
    cd base && find . -type f ! -name ".env" ! -path "./vendor/*" | while read f; do
      if [ ! -e "/work/$f" ]; then mkdir -p "/work/$(dirname "$f")"; cp "$f" "/work/$f"; fi
    done'

[ -f .env ] || cp .env.example .env
KEY="base64:$(openssl rand -base64 32)"
sed -i "s|^APP_KEY=.*|APP_KEY=$KEY|" .env
echo "Listo. Edite .env (contraseñas y DEST_DB_*) y ejecute: docker compose up -d --build"

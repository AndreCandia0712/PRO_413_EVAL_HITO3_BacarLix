#!/bin/sh
set -e

if [ -z "$APP_KEY" ]; then
  echo "AVISO: APP_KEY vacio, se genera una temporal (defina una fija en .env)."
  export APP_KEY="$(php artisan key:generate --show)"
fi

echo "Esperando a MySQL local en ${DB_HOST}:${DB_PORT}..."
until php -r 'try{new PDO("mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT"),getenv("DB_USERNAME"),getenv("DB_PASSWORD"));exit(0);}catch(Exception $e){exit(1);}'; do
  sleep 2
done

php artisan migrate --force
exec "$@"

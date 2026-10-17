# PRO_413_EVAL_HITO3_bacarlix

## 1. Identificación del proyecto

- **Nombre:** Sistema de Registro y Control de Solicitudes de Soporte Técnico
- **Grupo:** bacarlix
- **Materia:** Programación III (PRO-413) – Evaluación Procesual Hito 3
- **Objetivo:** aplicación web en PHP/Laravel que registra solicitudes de soporte técnico, permite consultarlas, cambiar su estado y enviarlas a una base de datos MySQL de la red local. Todo se ejecuta con Docker.

## 2. Integrantes y roles

| Nombre completo | Rol asignado | Usuario de GitHub |
|---|---|---|
| Arturo (APELLIDOS) | Backend Laravel | `usuario_arturo` |
| Andre (APELLIDOS) | Funcionalidad web | `usuario_andre` |
| Ricardo (APELLIDOS) | Bases de datos e integración | `usuario_ricardo` |
| Pablo (APELLIDOS) | Docker, despliegue y documentación | `usuario_pablo` |

| Rol | Responsabilidades |
|---|---|
| Backend Laravel | Modelo, migración, rutas, controlador y lógica de registro |
| Funcionalidad web | Formulario, listado y actualización de estados (vista Blade) |
| Bases de datos e integración | Conexión MySQL, BD de destino, servicio de envío (upsert) |
| Docker y documentación | Dockerfile, docker-compose, imagen en Docker Hub, README |

## 3. Tecnologías utilizadas

PHP 8.3, Laravel (última versión estable), MySQL 8.0, Apache, Git, GitHub, Docker, Docker Compose y Docker Hub.

## 4. Funcionalidades implementadas

- **Registro** de solicitudes (solicitante, departamento, descripción, prioridad; estado inicial `Pendiente`) con validación y mensajes.
- **Listado** en tabla: código, solicitante, departamento, descripción, prioridad, estado, fecha de registro y fecha de envío a la red.
- **Actualización de estado:** `Pendiente`, `En proceso`, `Resuelta`.
- **Envío a la BD de la red local (10.0.40.136):**
  - Automático: cada vez que se registra o actualiza una solicitud se intenta reflejar en la BD de la red.
  - Manual: botón "Enviar todo a la base de datos de la red local" o comando `php artisan solicitudes:enviar`.
  - Usa *upsert* con clave `(grupo, id_origen)`: reenviar **no duplica** registros.
  - Manejo de errores de conexión/escritura con mensajes claros (si falla, la solicitud queda guardada en local y se puede reenviar).

## 5. Arquitectura

```
 Navegador ──► [ contenedor app: Apache + PHP + Laravel ] ──► [ contenedor db: MySQL 8 local ]
                          │                                         (volumen db_data)
                          └──────────────► MySQL de la red local (10.0.40.136:3306)
```

- `app`: imagen `laravel-service:hito3`, expone el puerto **8000**. Al iniciar espera a MySQL y ejecuta las migraciones.
- `db`: MySQL local con volumen persistente `db_data` (puerto 3307 en el host).
- Flujo: formulario → controlador → guarda en MySQL local → `SincronizadorDestino` hace upsert en MySQL de la red.

## 6. Requisitos previos

- Git ≥ 2.30, Docker ≥ 24 con Docker Compose v2, cuenta de GitHub y de Docker Hub.
- Estar conectado a la red local desde donde se ve `10.0.40.136`.
- No se necesita PHP ni Composer instalados (se usan vía Docker).

## 7. Variables de entorno

Ver `.env.example`. **El archivo `.env` real NO se sube al repositorio.**

| Variable | Descripción |
|---|---|
| `APP_KEY` | Clave de Laravel (la genera `setup.sh`/`setup.ps1`) |
| `DB_HOST`, `DB_PORT` | MySQL local (`db`, `3306`) |
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_ROOT_PASSWORD` | Credenciales MySQL local |
| `DOCKERHUB_USER` | Usuario de Docker Hub (nombre de la imagen) |
| `GRUPO_NOMBRE` | `bacarlix` (identifica al grupo en destino) |
| `DEST_DB_HOST` | `10.0.40.136` |
| `DEST_DB_PORT` | `3306` (por defecto) |
| `DEST_DB_DATABASE`, `DEST_DB_USERNAME`, `DEST_DB_PASSWORD` | Los entrega el docente (individuales) |
| `DEST_DB_TABLE` | Tabla de destino (`solicitudes_soporte`) |

## 8. Preparación de MySQL

El servicio `db` se crea con `docker-compose.yml` usando las variables `DB_*`; los datos persisten en el volumen `db_data`.

```bash
docker compose up -d db
docker compose exec db mysql -u soporte -p soporte_local -e "SHOW TABLES;"
```

## 9. Preparación de Laravel

La estructura de la tabla se crea con la migración `database/migrations/*_create_solicitudes_soporte_table.php` (campos: id, solicitante, departamento, descripcion, prioridad, estado, created_at, updated_at y `enviado_at` de control).

**Primera vez (solo UNA persona del grupo), en la raíz del repo clonado:**

```bash
# Linux / macOS / Git Bash
sh setup.sh
# Windows PowerShell
powershell -ExecutionPolicy Bypass -File .\setup.ps1
```
Esto descarga el esqueleto de Laravel con Docker, lo combina con nuestros archivos y crea `.env` con una `APP_KEY`. Luego:

```bash
git add . && git commit -m "build: agregar esqueleto base de Laravel" && git push
```
Los demás hacen `git pull` y crean su `.env` (`cp .env.example .env`) y le ponen una `APP_KEY` (se las pasa quien hizo el setup por privado, **no por el repo**) o dejan vacía y se genera una temporal.

Las migraciones se ejecutan automáticamente al iniciar el contenedor; manualmente: `docker compose exec app php artisan migrate`.

## 10. Despliegue con Docker

```bash
cp .env.example .env              # completar contraseñas y DEST_DB_*
docker compose up -d --build      # construir e iniciar
docker compose ps                 # verificar contenedores
docker compose logs -f app        # ver migraciones
docker compose down               # detener (los datos se conservan)
docker compose down -v            # detener y BORRAR datos
```
Abrir: **http://localhost:8000**

## 11. Conexión y envío de datos

Parámetros: host `10.0.40.136`, puerto `3306`, usuario, contraseña y nombre de BD entregados por el docente (individuales, no compartir).

**Verificar conectividad (cada integrante con sus credenciales):**
```bash
docker run --rm -it mysql:8.0 mysql -h 10.0.40.136 -P 3306 -u TU_USUARIO -p NOMBRE_BD
mysql> SHOW TABLES;
mysql> DESCRIBE solicitudes_soporte;
```
Si la tabla de destino no existe o hay que diseñarla, se parte de `docs/destino.sql` (clave única `grupo + id_origen`). Si tiene otras columnas, se ajusta el método `fila()` de `app/Services/SincronizadorDestino.php`.

**Procedimiento de transferencia:** al registrar/actualizar se envía ese registro; el botón o `docker compose exec app php artisan solicitudes:enviar` reenvía todos. Se usa `updateOrInsert` por `(grupo, id_origen)` y se marca `enviado_at`.

**Comprobación en destino:**
```sql
SELECT * FROM solicitudes_soporte WHERE grupo = 'bacarlix';
```

## 12. Publicación en Docker Hub

- Imagen: `USUARIO_DOCKERHUB/laravel-service:hito3`
- Enlace público: `https://hub.docker.com/r/USUARIO_DOCKERHUB/laravel-service` *(completar)*

```bash
docker login
docker build -t USUARIO_DOCKERHUB/laravel-service:hito3 .
docker push USUARIO_DOCKERHUB/laravel-service:hito3
# En hub.docker.com: Repository → Settings → Visibility = Public
docker pull USUARIO_DOCKERHUB/laravel-service:hito3
```
Ejecutar la imagen descargada (requiere un MySQL accesible y las variables de entorno):
```bash
docker run -d -p 8000:80 --env-file .env USUARIO_DOCKERHUB/laravel-service:hito3
```
Las credenciales **no** están dentro de la imagen; se pasan por variables de entorno.

## 13. Colaboración

Flujo: cada integrante trabaja en su rama (`feat/...`), hace commits con su cuenta y abre un Pull Request que revisa otro integrante.

| Integrante | Aportes | Commits de referencia |
|---|---|---|
| Arturo | Modelo, migración, rutas, controlador | *(completar con hashes: `git log --author`)* |
| Andre | Vista Blade (formulario, listado, estados) | *(completar)* |
| Ricardo | Conexión destino, servicio de envío | *(completar)* |
| Pablo | Dockerfile, compose, Docker Hub, README | *(completar)* |

Ver aportes: `git shortlog -sn --all`

## 14. Pruebas

1. `docker compose ps` → `bacarlix_app` y `bacarlix_mysql` en estado *Up*.
2. Abrir http://localhost:8000 y registrar una solicitud (aparece `Pendiente` y el mensaje de confirmación).
3. Dejar un campo vacío → se muestran los errores de validación.
4. Cambiar el estado a `En proceso` y `Resuelta` → se refleja en el listado.
5. Persistencia local: `docker compose exec db mysql -u soporte -p soporte_local -e "SELECT * FROM solicitudes_soporte;"`
6. Reiniciar (`docker compose down` y `up -d`) → los datos siguen (volumen).
7. Pulsar "Enviar todo a la base de datos de la red local" **dos veces** → mismo número de filas en destino (sin duplicados).
8. `SELECT * FROM solicitudes_soporte WHERE grupo='bacarlix';` en el servidor de la red → aparecen todos los registros locales.

## 15. Solución de problemas

| Problema | Solución |
|---|---|
| `SQLSTATE[HY000] [2002]` en la app | MySQL aún iniciando; esperar o ver `docker compose logs db`. Revisar `DB_HOST=db`. |
| No conecta a `10.0.40.136` | Verificar red/VPN, ping, puerto 3306 y credenciales de `DEST_DB_*`. Probar con el cliente `mysql` de la sección 11. |
| `Access denied` en destino | Usuario/contraseña/BD incorrectos o sin permisos; consultar al docente. No usar credenciales de otro. |
| `Unknown column` en destino | La estructura difiere; ajustar `fila()` en `SincronizadorDestino.php` según `DESCRIBE`. |
| `No application encryption key` | Falta `APP_KEY` en `.env`. |
| `entrypoint.sh: not found` en Windows | Saltos de línea CRLF; el Dockerfile ya los convierte, reconstruir con `--build`. |
| Puerto 8000/3307 ocupado | Cambiar el puerto a la izquierda en `docker-compose.yml`. |
| Cambié `.env` y no se aplica | `docker compose up -d --force-recreate`. |

> Seguridad: no se publican contraseñas, tokens ni credenciales en el repositorio, README ni imagen.

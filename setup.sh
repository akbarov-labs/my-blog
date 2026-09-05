#!/usr/bin/env bash
#
# One-time setup for Sarvar Akbarov's personal site: Laravel + Filament,
# fully inside Docker. Only requirement on your machine: Docker Desktop.
#
# Usage:
#   cd path/to/my-blog
#   chmod +x setup.sh
#   ./setup.sh
#
set -euo pipefail

cd "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

log() { printf '\n\033[1;33m==>\033[0m %s\n' "$1"; }

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker is required. Install Docker Desktop (https://www.docker.com/products/docker-desktop/) and re-run this script." >&2
  exit 1
fi

if ! docker info >/dev/null 2>&1; then
  echo "Docker is installed but not running. Start Docker Desktop and re-run this script." >&2
  exit 1
fi

COMPOSE="docker compose"

set_env() {
  local key="$1" val="$2"
  if grep -q "^${key}=" .env 2>/dev/null; then
    perl -pi -e "s#^${key}=.*#${key}=${val}#" .env
  else
    echo "${key}=${val}" >> .env
  fi
}

# ---------------------------------------------------------------------------
# 1. Scaffold a fresh Laravel + Filament install (skipped if already present)
# ---------------------------------------------------------------------------
if [ ! -f artisan ]; then
  log "Scaffolding a fresh Laravel application (downloads packages — a few minutes)"
  SCRATCH="$(mktemp -d)"
  scratch_composer() { docker run --rm -v "$SCRATCH":/app -w /app composer:2 "$@"; }

  scratch_composer create-project laravel/laravel . --prefer-dist --no-interaction --ignore-platform-reqs

  log "Adding the Filament admin panel package"
  scratch_composer require "filament/filament:^3.3" --no-interaction -W --ignore-platform-reqs

  log "Copying the base application into this folder"
  cp -a "$SCRATCH/." .
  rm -rf "$SCRATCH"
else
  log "Laravel is already scaffolded here — skipping (delete 'artisan' to force a clean re-scaffold)"
fi

# ---------------------------------------------------------------------------
# 2. Apply our custom site on top: models, migrations, Filament resources,
#    controllers, routes, Blade views, seeders.
# ---------------------------------------------------------------------------
log "Applying the custom site (models, admin resources, views, routes, seed data)"
cp -a .overlay/. .

# ---------------------------------------------------------------------------
# 3. Environment
# ---------------------------------------------------------------------------
log "Preparing .env"
[ -f .env ] || cp .env.example .env

set_env APP_NAME '"Sarvar Akbarov"'
set_env APP_URL 'http://localhost:8080'
set_env DB_CONNECTION mysql
set_env DB_HOST db
set_env DB_PORT 3306
set_env DB_DATABASE blog
set_env DB_USERNAME blog
set_env DB_PASSWORD secret
set_env DB_ROOT_PASSWORD root_secret
set_env APP_PORT 8080
set_env FORWARD_DB_PORT 33061

# ---------------------------------------------------------------------------
# 4. Build our app image, then run one-off artisan setup commands against it
# ---------------------------------------------------------------------------
log "Building the application Docker image"
$COMPOSE build app

run_artisan() {
  $COMPOSE run --rm --no-deps app php artisan "$@"
}

if ! grep -q '^APP_KEY=base64' .env 2>/dev/null; then
  log "Generating APP_KEY"
  run_artisan key:generate --no-interaction
fi

if [ ! -f app/Providers/Filament/AdminPanelProvider.php ]; then
  log "Installing the Filament admin panel"
  run_artisan filament:install --panels --no-interaction
fi

# ---------------------------------------------------------------------------
# 5. Bring the stack up
# ---------------------------------------------------------------------------
log "Starting the stack (app, nginx, mysql)"
$COMPOSE up -d --build

log "Waiting for the app container to be ready"
for i in $(seq 1 30); do
  if $COMPOSE exec -T app php -v >/dev/null 2>&1; then
    break
  fi
  sleep 2
done

log "Running migrations and seeding demo content"
$COMPOSE exec -T app php artisan migrate --force --seed
$COMPOSE exec -T app php artisan storage:link 2>/dev/null || true

cat <<'EOF'

--------------------------------------------------------------------
  Your site is up.

  Website:        http://localhost:8080
  Admin panel:    http://localhost:8080/admin

  Admin login:
    email:    admin@example.com
    password: password

  IMPORTANT: change that password the first time you log in
  (Admin panel -> click your name, top right -> Profile).

  Useful commands (run from this folder):
    docker compose logs -f app        View app logs
    docker compose exec app bash      Shell into the app container
    docker compose down               Stop the stack
    docker compose up -d              Start it again
--------------------------------------------------------------------
EOF

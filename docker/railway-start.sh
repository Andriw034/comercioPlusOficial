#!/usr/bin/env bash
set -euo pipefail

PORT="${PORT:-8080}"
DB_WAIT_MAX_ATTEMPTS="${DB_WAIT_MAX_ATTEMPTS:-20}"
DB_WAIT_SLEEP_SECONDS="${DB_WAIT_SLEEP_SECONDS:-3}"

php artisan optimize:clear
php artisan config:clear

should_wait_for_db=0
if [[ -n "${DATABASE_URL:-}" || -n "${DB_HOST:-}" || -n "${MYSQLHOST:-}" || -n "${PGHOST:-}" ]]; then
  should_wait_for_db=1
fi

if [[ "$should_wait_for_db" -eq 1 ]]; then
  echo "[boot] Waiting for database connection..."
  attempt=1
  while [[ "$attempt" -le "$DB_WAIT_MAX_ATTEMPTS" ]]; do
    if [[ -n "${DB_CONNECTION:-}" ]]; then
      if php artisan db:show --database="${DB_CONNECTION}" --counts >/dev/null 2>&1; then
        echo "[boot] Database connection is ready (attempt $attempt/$DB_WAIT_MAX_ATTEMPTS)."
        break
      fi
    else
      if php artisan db:show --counts >/dev/null 2>&1; then
        echo "[boot] Database connection is ready (attempt $attempt/$DB_WAIT_MAX_ATTEMPTS)."
        break
      fi
    fi

    echo "[boot] DB not ready (attempt $attempt/$DB_WAIT_MAX_ATTEMPTS). Retrying in ${DB_WAIT_SLEEP_SECONDS}s..."
    sleep "$DB_WAIT_SLEEP_SECONDS"
    attempt=$((attempt + 1))
  done

  if [[ "$attempt" -gt "$DB_WAIT_MAX_ATTEMPTS" ]]; then
    echo "[warn] DB was not ready after $DB_WAIT_MAX_ATTEMPTS attempts. Continuing startup and relying on runtime retries."
  fi
fi

# Retry migrations to reduce auth 503 right after cold start.
migrate_attempt=1
MIGRATE_MAX_ATTEMPTS="${MIGRATE_MAX_ATTEMPTS:-5}"
MIGRATE_SLEEP_SECONDS="${MIGRATE_SLEEP_SECONDS:-4}"
until php artisan migrate --force; do
  if [[ "$migrate_attempt" -ge "$MIGRATE_MAX_ATTEMPTS" ]]; then
    echo "[warn] migrate failed after $MIGRATE_MAX_ATTEMPTS attempts; continuing startup."
    break
  fi

  echo "[boot] migrate failed (attempt $migrate_attempt/$MIGRATE_MAX_ATTEMPTS). Retrying in ${MIGRATE_SLEEP_SECONDS}s..."
  sleep "$MIGRATE_SLEEP_SECONDS"
  migrate_attempt=$((migrate_attempt + 1))
done

exec php artisan serve --host=0.0.0.0 --port="$PORT"

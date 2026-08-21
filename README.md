# Booking Calendar

Web application for booking time slots without visitor registration.
React SPA + Laravel 12 API + Filament admin panel.

## Local development

Infrastructure (postgres, redis, adminer) runs in docker compose;
backend and frontend run on the host.

```bash
make install   # composer install (backend/) + npm install (web/)
make dev       # start postgres/redis/adminer
make backend-dev    # php artisan serve → http://localhost:8000
make frontend-dev   # vite dev server   → http://localhost:5173
```

Adminer is available at http://localhost:8080.

## Production preview

The same image that is deployed to the PaaS can be run locally:

```bash
make prod-build
make prod-preview   # → http://localhost:8000
```

## CI/CD

- `backend-check.yml` — Pint, Larastan, Pest
- `frontend-check.yml` — ESLint/Prettier, tsc, Vitest, Playwright e2e,
  plus a Docker build smoke test
- `hexlet-check.yml` — Hexlet project check
- `release-please.yml` — releases (see `docs/adr/0002-*`)

The PaaS builds the root `Dockerfile` and hands the container a `PORT`
environment variable (default: 8000).

### Deploy environment

| Variable | Notes |
|----------|-------|
| `PORT` | Assigned by the platform, nginx listens on it |
| `APP_KEY` | Optional; auto-generated at start if missing (set it to keep encrypted cookies stable) |
| `APP_ENV` / `APP_DEBUG` | `production` / `false` |
| `DB_*` | External managed Postgres (`DB_CONNECTION=pgsql`) |
| `REDIS_HOST` / `REDIS_PORT` | External managed Redis (sessions, cache) |
| `QUEUE_CONNECTION` | `sync` in v1 (no notifications, FR-4) |

**Release command (migrations):** `php artisan migrate --force`

**Health check path:** `/up`

Decisions are recorded in `docs/adr/0003-single-container-paas-deploy.md`.

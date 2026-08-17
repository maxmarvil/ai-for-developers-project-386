.PHONY: up down build install backend-install frontend-install dev backend-dev frontend-dev \
        backend-test frontend-test lint backend-lint frontend-lint migrate fresh seed \
        composer artisan tinker

# Docker Compose
up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build

# Installation
install: backend-install frontend-install

backend-install:
	docker compose run --rm php composer install

frontend-install:
	cd web && npm install

# Development
dev:
	make up
	@echo "Backend available at http://localhost:8000"
	@echo "Run 'make frontend-dev' in another terminal"

backend-dev:
	docker compose up php nginx redis postgres

frontend-dev:
	cd web && npm run dev

# Testing
backend-test:
	docker compose run --rm php ./vendor/bin/pest

frontend-test:
	cd web && npm test

# Linting
lint: backend-lint frontend-lint

backend-lint:
	docker compose run --rm php ./vendor/bin/pint --test
	docker compose run --rm php ./vendor/bin/phpstan analyse --memory-limit=512M

frontend-lint:
	cd web && npm run lint

# Formatting
backend-format:
	docker compose run --rm php ./vendor/bin/pint

# Database
migrate:
	docker compose run --rm php php artisan migrate

fresh:
	docker compose run --rm php php artisan migrate:fresh

seed:
	docker compose run --rm php php artisan db:seed

# Shortcuts
composer:
	docker compose run --rm php composer $(filter-out $@,$(MAKECMDGOALS))

artisan:
	docker compose run --rm php php artisan $(filter-out $@,$(MAKECMDGOALS))

tinker:
	docker compose run --rm php php artisan tinker

# Catch-all for arguments to composer/artisan
%:
	@:

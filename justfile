
# Command to enter docker container
container:
    docker exec -it app-php-1 bash

# Command to build TypeScript files (watch mode)
build-ts:
    php bin/console typescript:build --watch

# Default target
default: dev-up

# ---------------------------
# 🧑‍💻 Local dev (Dunglas stack)
# ---------------------------

# Builds the project
dev-build:
     docker compose build --pull --no-cache

# Start dev stack (frankenphp_dev)
dev-up:
    docker compose up --wait

# Stop dev stack
dev-down:
    docker compose down --remove-orphans

# See running containers
dev-ps:
    docker compose ps

# Tail logs for php container
dev-logs-php:
    docker compose logs -f php

# Shell into php container
dev-sh-php:
    docker compose exec php sh

# Run Symfony console in php container
# Usage: just sf about
sf *args:
    docker compose exec php php bin/console {{args}}

# Doctrine migrations
migrate:
    docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

# ---------------------------
# 🎨 Front build (local only)
# ---------------------------

# Tailwind build local (dev machine)
tailwind-build:
    php bin/console tailwind:build

tailwind-watch:
    php bin/console tailwind:build --watch

# Typescript build
ts-build:
    php bin/console typescript:build

# Full asset rebuild (Tailwind + Asset Mapper)
assets-build:
    rm -rf public/assets
    php bin/console tailwind:build
    php bin/console asset-map:compile

# ---------------------------
# 🚀 Prod (build & deploy image sur la VM)
# ---------------------------
# Ces commandes sont pensées pour être lancées SUR la VM.
# Tu peux les garder en mémo ici.

# Build prod (sur la VM)
prod-build:
    docker compose -f compose.yaml -f compose.prod.yaml build

# Start prod (sur la VM)
# ⚠️ Remplace les valeurs par les vraies ou injecte via .env si tu veux aller plus loin.
prod-up:
    SERVER_NAME=suarja.xyz \
    APP_SECRET=dummy_secret_change_me \
    CADDY_MERCURE_JWT_SECRET=dummy_mercure_secret_change_me \
    docker compose -f compose.yaml -f compose.prod.yaml up -d --wait

prod-down:
    docker compose -f compose.yaml -f compose.prod.yaml down --remove-orphans

prod-ps:
    docker compose -f compose.yaml -f compose.prod.yaml ps

prod-logs-php:
    docker compose -f compose.yaml -f compose.prod.yaml logs -f php

prod-sh-php:
    docker compose -f compose.yaml -f compose.prod.yaml exec php sh

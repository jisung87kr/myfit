.PHONY: help build up down restart logs shell composer artisan migrate fresh seed cache-clear test

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

build: ## Build Docker containers
	docker-compose build

up: ## Start Docker containers
	docker-compose up -d

down: ## Stop Docker containers
	docker-compose down

restart: ## Restart Docker containers
	docker-compose restart

logs: ## Show container logs
	docker-compose logs -f

shell: ## Access app container shell
	docker-compose exec app bash

composer-install: ## Install composer dependencies
	docker-compose exec app composer install

composer-update: ## Update composer dependencies
	docker-compose exec app composer update

artisan: ## Run artisan command (use: make artisan cmd="migrate")
	docker-compose exec app php artisan $(cmd)

migrate: ## Run database migrations
	docker-compose exec app php artisan migrate

fresh: ## Fresh database with migrations
	docker-compose exec app php artisan migrate:fresh

seed: ## Run database seeders
	docker-compose exec app php artisan db:seed

cache-clear: ## Clear all caches
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

optimize: ## Optimize application
	docker-compose exec app php artisan optimize

test: ## Run tests
	docker-compose exec app php artisan test

mysql: ## Access MySQL shell
	docker-compose exec mysql mysql -u myfit_user -pmyfit_password myfit

redis: ## Access Redis CLI
	docker-compose exec redis redis-cli

setup: ## Initial setup for new project
	cp .env.example .env
	docker-compose up -d
	docker-compose exec app composer install
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate
	@echo "Setup complete! Visit http://localhost:8000"

clean: ## Remove all containers and volumes
	docker-compose down -v
	rm -rf vendor node_modules

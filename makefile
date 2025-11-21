.PHONY: help up down build restart logs shell-php shell-frontend db-migrate db-seed db-fresh test test-coverage format lint

# Colors for output
GREEN := \033[0;32m
YELLOW := \033[0;33m
RED := \033[0;31m
NC := \033[0m # No Color

help: ## Show this help message
	@echo "$(GREEN)Available commands:$(NC)"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(YELLOW)%-20s$(NC) %s\n", $$1, $$2}'

up: ## Start all containers
	@echo "$(GREEN)Starting containers...$(NC)"
	docker-compose up -d
	@echo "$(GREEN)Containers started!$(NC)"
	@echo "Backend: http://localhost"
	@echo "Frontend: http://localhost:3000"
	@echo "Mailhog: http://localhost:8025"

down: ## Stop all containers
	@echo "$(YELLOW)Stopping containers...$(NC)"
	docker-compose down

build: ## Build all containers
	@echo "$(GREEN)Building containers...$(NC)"
	docker-compose build --no-cache

restart: ## Restart all containers
	@echo "$(YELLOW)Restarting containers...$(NC)"
	docker-compose restart

logs: ## Show logs from all containers
	docker-compose logs -f

logs-php: ## Show PHP/Laravel logs
	docker-compose logs -f php

logs-frontend: ## Show Frontend logs
	docker-compose logs -f frontend

shell-php: ## Access PHP container shell
	docker-compose exec php sh

shell-frontend: ## Access Frontend container shell
	docker-compose exec frontend sh

db-shell: ## Access PostgreSQL shell
	docker-compose exec postgres psql -U proyecto_user -d proyecto_contable

redis-cli: ## Access Redis CLI
	docker-compose exec redis redis-cli

# Laravel commands
install-backend: ## Install backend dependencies
	docker-compose exec php composer install
	docker-compose exec php cp .env.example .env
	docker-compose exec php php artisan key:generate
	@echo "$(GREEN)Backend installed!$(NC)"

install-frontend: ## Install frontend dependencies
	docker-compose exec frontend npm install
	@echo "$(GREEN)Frontend installed!$(NC)"

install: install-backend install-frontend ## Install all dependencies

db-migrate: ## Run database migrations
	docker-compose exec php php artisan migrate

db-seed: ## Seed database
	docker-compose exec php php artisan db:seed

db-fresh: ## Fresh database with seeds
	docker-compose exec php php artisan migrate:fresh --seed

db-rollback: ## Rollback last migration
	docker-compose exec php php artisan migrate:rollback

# Testing
test-backend: ## Run backend tests
	docker-compose exec php php artisan test

test-frontend: ## Run frontend tests
	docker-compose exec frontend npm run test

test-coverage: ## Run tests with coverage
	docker-compose exec php php artisan test --coverage
	docker-compose exec frontend npm run test:coverage

test: test-backend test-frontend ## Run all tests

# Code quality
format-backend: ## Format backend code
	docker-compose exec php vendor/bin/php-cs-fixer fix

format-frontend: ## Format frontend code
	docker-compose exec frontend npm run format

format: format-backend format-frontend ## Format all code

lint-backend: ## Lint backend code
	docker-compose exec php vendor/bin/phpstan analyse

lint-frontend: ## Lint frontend code
	docker-compose exec frontend npm run lint

lint: lint-backend lint-frontend ## Lint all code

# Cache and optimization
cache-clear: ## Clear all caches
	docker-compose exec php php artisan cache:clear
	docker-compose exec php php artisan config:clear
	docker-compose exec php php artisan route:clear
	docker-compose exec php php artisan view:clear

optimize: ## Optimize Laravel
	docker-compose exec php php artisan config:cache
	docker-compose exec php php artisan route:cache
	docker-compose exec php php artisan view:cache

# Cleanup
clean: ## Remove all containers, volumes and images
	docker-compose down -v --remove-orphans
	docker system prune -f

# Setup (first time)
setup: build up install db-migrate db-seed ## Complete first-time setup
	@echo "$(GREEN)Setup complete!$(NC)"
	@echo "Backend: http://localhost"
	@echo "Frontend: http://localhost:3000"
	@echo "Mailhog: http://localhost:8025"
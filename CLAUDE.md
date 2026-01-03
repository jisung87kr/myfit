# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

MyFit is a health and fitness management API built with Laravel 12. Features include multi-step health surveys, fitness tracking (exercises, diet plans, meal logs, weight tracking), user authentication with social login (Google, Kakao), and calorie calculations.

**Tech Stack**: Laravel 12, PHP 8.2+, MySQL 8.0, Redis, Vite + Tailwind CSS, Docker with Apache

## Development Commands

### Docker Environment
```bash
make setup              # Initial project setup (copies .env, starts containers, installs deps, migrates)
make up                 # Start containers
make down               # Stop containers
make shell              # Access app container bash
make logs               # View container logs
```

### Running the Application
```bash
# Full dev stack (server + queue + logs + vite) - run from host
composer run dev

# Or manually in container
docker compose exec app php artisan serve
npm run dev
```

### Database
```bash
make migrate            # Run migrations
make fresh              # Reset database with migrations
make seed               # Run seeders
make mysql              # Access MySQL shell (user: myfit_user, pass: myfit_password)
make redis              # Access Redis CLI
```

### Testing
```bash
make test                                           # Run all tests
docker compose exec app php artisan test            # Alternative
docker compose exec app php artisan test --filter=AuthTest  # Single test
```

### Cache & Optimization
```bash
make cache-clear        # Clear all caches
make optimize           # Optimize application
```

### NPM (inside container)
```bash
docker compose exec app npm install
docker compose exec app npm run build
```

## Architecture

### Layer Pattern
```
Controller (app/Http/Controllers/Api/) → Service (app/Services/) → Model (app/Models/)
```

- **Controllers**: HTTP handling, input validation via Form Requests, response formatting
- **Services**: Business logic, transactions, external API calls (9 services)
- **Models**: Eloquent models with relationships (17 models)

### Key Services
- `AuthService` - Registration, login, logout
- `SurveyService` - Multi-step survey flow management
- `DietPlanService` - Plan generation and management
- `CalorieCalculationService` - BMR/TDEE calculations
- `SocialAuthService` - OAuth with strategy pattern (Google, Kakao)

### Enums (app/Enums/)
- `HttpStatus` - API response codes with helper methods
- `SurveyStep` - Survey flow steps (BASIC_INFO → GOAL_SETTING → LIFESTYLE → HEALTH_PREFERENCE → ADDITIONAL)
- `SocialProvider`, `TokenType`, `QuestionType`, `CacheKey`

### API Response Pattern
```php
response()->success($data, 'message');      // 200
response()->created($data, 'message');      // 201
response()->validationError($errors);       // 422
response()->notFound('message');            // 404
response()->error('message', $errors, 400); // Custom error
```

Response format: `{ success: bool, message: string, data: {}, errors: {} }`

## Authentication

- **Method**: Laravel Sanctum (token-based)
- **Protected routes**: Middleware `auth:sanctum`
- **Permissions**: Spatie Laravel Permission package

## Queue System

- **Connection**: Redis
- **Monitoring**: Laravel Horizon
- **Example Job**: `GenerateDietPlanJob`

## File Structure

```
app/
├── Contracts/          # Interfaces
├── Enums/              # PHP 8.1+ Enums
├── Http/
│   ├── Controllers/Api/  # 11 API controllers
│   ├── Requests/         # Form Request validation
│   └── Responses/        # ApiResponse class
├── Jobs/               # Queued jobs
├── Models/             # 17 Eloquent models
└── Services/           # 9 business logic services

tests/
├── Feature/            # Integration tests (20+ test files)
└── Unit/               # Unit tests
```

## Code Style

- Laravel Pint for formatting: `vendor/bin/pint`
- PHP 8.2+ type hints throughout
- Korean validation messages

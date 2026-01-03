# GEMINI.md - MyFit Project Context

## Project Overview
**MyFit** is a comprehensive fitness and diet application built with **Laravel 12** and **Vite + Tailwind CSS**. It features AI-driven diet plan generation, detailed user surveys, and social interaction capabilities.

The project follows a strict **Layered Architecture** (Controller -> Service -> Model) to ensure separation of concerns and maintainability.

## Technology Stack

### Backend
*   **Framework:** Laravel 12.x
*   **Language:** PHP 8.2+
*   **Database:** MySQL 8.0
*   **Cache/Queue:** Redis (monitored via Laravel Horizon)
*   **Authentication:** Laravel Sanctum (API Tokens)
*   **Authorization:** Spatie Laravel Permission
*   **Testing:** PHPUnit

### Frontend
*   **Build Tool:** Vite 7.x
*   **Styling:** Tailwind CSS 4.0
*   **Client:** Axios

### Infrastructure
*   **Containerization:** Docker & Docker Compose
*   **Server:** Apache (in Docker container)

## Architecture & Patterns

The application strictly adheres to a **Layered Architecture**.

### 1. Controller Layer (`app/Http/Controllers/Api/`)
*   **Responsibility:** Handle HTTP requests/responses, validate input via **FormRequest**, call Services.
*   **Constraints:** NO business logic, NO direct DB access.
*   **Response:** Uses standard macros (e.g., `response()->success()`, `response()->error()`).

### 2. Service Layer (`app/Services/`)
*   **Responsibility:** Core business logic, transaction management (`DB::beginTransaction`), external API calls (OpenAI, etc.).
*   **Dependency Injection:** Services are injected into Controllers. Other Services can be injected into a Service.
*   **Naming:** `{Domain}Service.php` (e.g., `AuthService.php`, `DietPlanService.php`).

### 3. Repository Layer (`app/Repositories/`) *[Optional]*
*   **Use Case:** Complex queries, reusable data access logic.

### 4. Model Layer (`app/Models/`)
*   **Responsibility:** DB mapping, Relationships, Accessors/Mutators, Scopes.
*   **Constraints:** NO business logic.

### Design Patterns Used
*   **Strategy:** Social Authentication (Google, Kakao).
*   **Factory:** Notification channels.
*   **Observer:** Event-driven actions (e.g., `UserRegistered`).
*   **Decorator:** Caching layers.
*   **Enums:** Heavily used for status, types, and cache keys (`app/Enums/`).

## Key Development Commands

The project includes a `Makefile` for common tasks. **Always run these via `make` or `docker compose`**.

### Setup & Run
*   **Initial Setup:** `make setup` (Builds, starts, installs deps, migrates, keys).
*   **Start App:** `make up` (or `docker compose up -d`).
*   **Stop App:** `make down`.
*   **Logs:** `make logs` / `make artisan cmd="horizon"`.

### Database & Migrations
*   **Migrate:** `make migrate` (`php artisan migrate`).
*   **Fresh DB:** `make fresh` (Reset DB).
*   **Seed:** `make seed`.
*   **MySQL Shell:** `make mysql`.

### Testing & Quality
*   **Run Tests:** `make test` (`php artisan test`).
*   **Clear Cache:** `make cache-clear`.
*   **Lint/Format:** `make artisan cmd="pint"` (if Pint is configured).

### Frontend
*   **Dev Server:** `npm run dev`.
*   **Build:** `npm run build`.

## Directory Structure Highlights
*   `app/Enums/` - Centralized Enum definitions.
*   `app/Services/` - Business logic implementation.
*   `app/Http/Requests/` - Form Requests for validation.
*   `app/Http/Responses/` - Standardized response structures.
*   `docs/` - Detailed documentation (API specs, Architecture).

## Coding Conventions
*   **Strict Typing:** Use PHP type hints and return types.
*   **Validation:** ALWAYS use Form Requests, never validate in Controller.
*   **Responses:** ALWAYS use `response()->success/error/created` macros.
*   **Safety:** Use `DB::transaction` for operations involving multiple writes.

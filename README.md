# Laravel + Nuxt SaaS Boilerplate

A reusable starter template for building SaaS applications with:

- Laravel as an API-first backend
- Nuxt as a separate frontend in `/frontend`
- A modular monolith backend structure under `/src/Modules`

This boilerplate is intentionally generic. It gives you a solid foundation for authentication, authorization, tenant-ready architecture, and a reusable frontend shell without locking you into a specific product idea.

## Who This Is For

This starter is a good fit if you want:

- a clean base for future SaaS projects
- Laravel business logic organized by domain, not scattered across `app/`
- a separate frontend app instead of Blade or Laravel Vite scaffolding
- a tenant or organization-aware system from the start
- something structured enough to scale, but still lightweight

If you are curious about modular monoliths but do not want the overhead of microservices, this is a practical place to start.

## Core Idea

The main convention in this project is:

- `app/` stays thin and Laravel-specific
- `src/Modules/` holds the real domain code
- `routes/api.php` is the main application surface
- `frontend/` is a standalone Nuxt app that talks to the Laravel API

That means your future product logic should mostly grow inside modules such as `Identity`, `Tenancy`, `Access`, or your own custom modules.

## Project Structure

```text
app/                    Laravel framework glue
bootstrap/              Laravel bootstrap
config/                 Framework and project config
database/               Migrations, factories, seeders
frontend/               Nuxt frontend app
routes/api.php          Main API routes
routes/web.php          Minimal safe fallback
src/Modules/            Main backend application code
tests/                  Pest tests
```

### Backend Modules

Current modules included in the starter:

- `Shared`
  Common API controller base class, DTO helpers, API responses, and exceptions.

- `Identity`
  Login, logout, current user endpoint, and API token authentication foundation.

- `Access`
  Roles, permissions, and middleware for admin, role, and permission checks.

- `Tenancy`
  Organization-based tenant foundation, organization membership, and request context resolution.

- `Admin`
  Example protected admin API surface.

- `User`
  Main authenticated actor model and reusable user-facing data structures.

## Why This Approach

This boilerplate tries to balance Laravel conventions with long-term maintainability.

Benefits of this structure:

- modules are easier to reason about than large flat folders
- business logic is separated from framework glue
- the frontend can evolve independently from the backend
- future SaaS projects can start from a cleaner, more reusable base
- tenant, role, and permission concepts are considered early instead of bolted on later

## What’s Included

- API-first Laravel setup
- Composer autoloading for `Modules\\` => `src/Modules/`
- Separate Nuxt frontend in `/frontend`
- Shared API abstractions for controllers, DTOs, responses, and exceptions
- Authentication foundation with login, logout, and `me`
- Role and permission-ready authorization structure
- Organization-based tenant/account boundary
- Pest testing setup
- Generic factories, migrations, and seeders
- Template-safe repository structure for reuse

## What’s Intentionally Minimal

This is a foundation, not a finished product.

The following are intentionally left light or unimplemented:

- registration flows
- email verification UX
- password reset UX
- billing and subscriptions
- invitations
- audit logs
- product-specific dashboards and widgets
- industry-specific domain features

The goal is to keep the starter clean and reusable.

## Getting Started

### 1. Install backend dependencies

```bash
composer install
```

### 2. Create your environment file

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Update Composer autoloads

This project uses:

- `App\\` => `app/`
- `Modules\\` => `src/Modules/`
- `Database\\Factories\\` => `database/factories/`
- `Database\\Seeders\\` => `database/seeders/`

After cloning or changing autoloaded classes, run:

```bash
composer dump-autoload
```

### 4. Run the database

```bash
php artisan migrate --seed
```

### 5. Start the backend

```bash
php artisan serve
```

By default the API will be available under:

```text
http://localhost:8000/api
```

### 6. Install and run the frontend

```bash
cd frontend
npm install
npm run dev
```

By default the Nuxt app is expected at:

```text
http://localhost:3000
```

## Environment Notes

Backend `.env.example` includes generic starter settings such as:

- `FRONTEND_URL`
- `AUTH_GUARD`
- `TENANCY_ORGANIZATION_HEADER`
- `API_TOKEN_TTL_MINUTES`

Frontend `.env.example` includes:

- `NUXT_PUBLIC_API_BASE_URL`
- `NUXT_PUBLIC_APP_NAME`

## Authentication and Access

The starter includes a lightweight reusable auth foundation:

- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET /api/auth/me`

Access control is structured around:

- roles
- permissions
- admin-only middleware
- organization-aware checks

This gives you a strong base for expanding into things like owner, manager, member, or product-specific permissions later.

## Tenancy Model

This boilerplate uses `Organization` as the generic account boundary.

That means:

- a user can belong to one or more organizations
- a user can have a current organization context
- requests can resolve organization context from a header or current selection
- roles can be global or organization-scoped

This is meant to stay generic enough for SaaS apps using:

- workspaces
- teams
- companies
- accounts
- organizations

If you prefer `Tenant` or `Workspace` naming later, you can rename the module and model to match your product language.

## Frontend Philosophy

The frontend is intentionally neutral and reusable.

Included in `/frontend`:

- layouts
- pages
- components
- composables
- plugins
- auth flow
- app shell
- admin page foundation

It is not designed as a demo product UI. It is designed as a clean place to begin your real product UI.

## Testing and Quality

This project uses Pest for tests.

Recommended commands:

```bash
php artisan test
vendor/bin/pest
vendor/bin/pint
```

If you change PHP files, run Pint before committing.

## Recommended Workflow for New Projects

When using this boilerplate for a new SaaS, a good starting order is:

1. Rename the app and update environment values.
2. Customize the frontend shell and navigation.
3. Add your first real business module inside `src/Modules`.
4. Expand organization and membership flows.
5. Add your product-specific policies, services, and API endpoints.

## What To Customize First

The highest-value first customizations are usually:

- app name and environment config
- frontend shell and navigation
- registration or invite flow
- tenant creation and switching UX
- your first product-specific module
- permission rules for your app

## Repository Hygiene

This repo is kept template-friendly.

Ignored artifacts include things like:

- `vendor`
- `node_modules`
- `frontend/node_modules`
- `.nuxt`
- `.output`
- `public/build`

Do not commit build output or nested Git repositories if you plan to reuse this as a GitHub template.

## Final Notes

This boilerplate is meant to help you start cleanly, not to decide your product for you.

It gives you:

- a maintainable backend structure
- a reusable frontend foundation
- tenant and access concepts early
- enough structure to scale

But it still leaves space for you to shape the app around your own domain.

# respos - Restaurant Point of Sale

A QR-based restaurant ordering and billing management system built with Laravel 12 and Inertia.js.

## Project Overview

**respos** is a digital platform designed to streamline restaurant operations. Phase 1 focuses on a "cash-first" release, supporting customer QR ordering, waiter-assisted ordering, kitchen processing, billing, and cash reconciliation.

### Core Tech Stack
- **Backend:** PHP 8.2+, [Laravel 12](https://laravel.com)
- **Frontend:** [Vue 3](https://vuejs.org) (Script Setup), [TypeScript](https://www.typescriptlang.org), [Inertia.js](https://inertiajs.com)
- **Styling:** [Tailwind CSS](https://tailwindcss.com), shadcn-vue
- **Testing:** [Pest](https://pestphp.com)
- **Architecture:** Repository Pattern, Service Layer, Single-page Inertia CRUD flows.

## Getting Started

### Prerequisites
- PHP 8.2 or higher
- Node.js & NPM
- Composer
- SQLite (or preferred database)

### Installation
1. Clone the repository.
2. Run `composer install` and `npm install`.
3. Copy `.env.example` to `.env` and configure your database.
4. Run `php artisan key:generate`.
5. Run `php artisan migrate --seed`.

### Key Commands
- **Development:** `composer dev` (Runs `artisan serve`, queue listener, logs, and Vite concurrently).
- **Frontend Only:** `npm run dev`.
- **Testing:** `php artisan test`.
- **Formatting (PHP):** `vendor/bin/pint`.
- **Linting (JS/TS/Vue):** `npm run lint`.
- **Building Assets:** `npm run build` (Includes Vue/TypeScript type checking).

## Development Conventions

### Backend
- **Namespaces:** PSR-4 under `App\`.
- **Models:** Singular names (e.g., `MenuItem`, `Bill`).
- **Data Access:** Use the Repository Pattern. Interfaces in `app/Interfaces/Repositories`, concrete classes in `app/Repositories`.
- **Business Logic:** Encapsulate complex logic in `app/Services`.
- **Testing:** Use Pest. Feature tests in `tests/Feature`, Unit tests in `tests/Unit`.

### Frontend
- **Composition API:** Use `<script setup>` in all Vue components.
- **Type Safety:** Always use TypeScript for props, emits, and internal logic.
- **Components:** Shared components in `resources/js/Components`. Page components in `resources/js/Pages`.
- **CRUD:** Prefer single-page Inertia flows with dialogs for Create/Edit/View/Delete actions.

## Project Resources

### Documentation (`materials/`)
- `restaurant-project.md`: Detailed User Requirements Specification (URS).
- `prompts.md`: Milestone mapping and implementation slices.
- `database.sql`: Intended schema shape (reference only, migrations are source of truth).

### Agent Skills (`.codex/skills/`)
These skills provide specialized guidance for AI agents:
- `restaurant-project-context`: Status assessment and planning.
- `restaurant-backend-feature`: Controllers, models, repositories, and migrations.
- `restaurant-inertia-crud`: Vue, Inertia, and UI development.

## Core Domains
- **Auth & Roles:** Staff authentication and RBAC (Spatie Laravel Permission).
- **Menu Management:** Categories, items, pricing, and availability.
- **Table Management:** QR code generation and session tracking.
- **Ordering:** Customer QR orders and waiter-assisted orders.
- **Kitchen:** Real-time order processing and status updates.
- **Billing:** Invoicing, split billing, and cash payment processing.
- **Reporting:** Daily sales and end-of-day cash reconciliation.

# Repository Guidelines

## Project Structure & Module Organization

This Laravel 12 app uses Inertia, Vue 3, TypeScript, TailwindCSS, and shadcn-vue. Backend code lives in `app/`: controllers in `app/Http/Controllers`, requests in `app/Http/Requests`, models in `app/Models`, repositories in `app/Repositories`, and services in `app/Services`. Routes are in `routes/web.php`, `routes/auth.php`, and `routes/console.php`. Frontend pages are in `resources/js/Pages`, shared components in `resources/js/Components`, and styles in `resources/css/app.css`. Database files live under `database/`; tests are in `tests/Feature` and `tests/Unit`.

## Project Skills

Use project-local skills in `.codex/skills/` for detailed, on-demand guidance. Load `.codex/skills/restaurant-project-context/SKILL.md` when planning work, checking status, or reconciling implementation with `materials/`. Load `.codex/skills/restaurant-backend-feature/SKILL.md` before backend feature work involving controllers, requests, repositories, services, models, policies, routes, migrations, or Pest tests. Load `.codex/skills/restaurant-inertia-crud/SKILL.md` before Vue/Inertia CRUD, dialogs, tables, filters, or shadcn-vue UI work.

## Build, Test, and Development Commands

- `composer install` and `npm install`: install PHP and Node dependencies.
- `composer dev`: run Laravel, queue listener, logs, and Vite together.
- `npm run dev`: start only the Vite frontend server.
- `npm run build`: type-check Vue/TypeScript and build assets.
- `npm run lint`: run ESLint on `resources/js` and apply fixes.
- `php artisan test`: run the Pest/PHPUnit suite.
- `vendor/bin/pint`: format PHP code with Laravel Pint.

## Core Conventions

Follow Laravel conventions: PSR-4 namespaces under `App\`, singular model names such as `MenuItem`, and descriptive classes such as `ProfileController`. Use the repository pattern: interfaces in `app/Interfaces/Repositories` or `app/Interfaces/{Namespace}`, concrete repositories in `app/Repositories`, and services in `app/Services`. Name pairs as `{Item}Repository` and `{Item}RepositoryInterface`.

Favor single-page Inertia CRUD flows with index pages as the workspace and dialogs for create, edit, view, and delete actions unless a workflow needs a dedicated screen. Use Vue `script setup`, TypeScript, composables, Tailwind scale spacing, existing `gogilo/breeze` components, and shadcn-vue primitives.

## Testing & Pull Requests

Use Pest for PHP tests. Put request, auth, and workflow coverage in `tests/Feature`; keep isolated logic checks in `tests/Unit`. Run `php artisan test` before backend submissions and `npm run build` when changing Vue or TypeScript. Keep commits focused and imperative. Pull requests should include a summary, test results, linked issues when applicable, screenshots for UI changes, and migration or environment notes.

## Security & Configuration Tips

Do not commit `.env` or secrets. Keep local setup aligned with `.env.example`, and prefer migrations/seeders over ad hoc SQL changes unless updating `materials/` documentation.

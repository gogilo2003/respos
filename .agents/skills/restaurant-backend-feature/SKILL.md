---
name: restaurant-backend-feature
description: Use when creating or modifying Laravel backend features, repositories, services, controllers, requests, policies, routes, migrations, models, or Pest tests for this restaurant system.
---

# Restaurant Backend Feature

Use this skill for Laravel feature implementation in the restaurant system.

## Required Reads

- Read `references/backend-patterns.md` before adding backend application code.
- Read `materials/database.sql` before changing models, relationships, repositories, or migrations.
- Read `materials/prompts.md` when the work maps to a milestone.

## Default Workflow

1. Identify the domain model and milestone.
2. Check existing migration, model, relationships, route, controller, request, and test coverage.
3. Add or update the repository interface first, then the concrete repository, then service logic.
4. Keep controllers thin: validation, authorization, service call, Inertia response or redirect.
5. Add focused Pest feature tests for user-visible behavior and policy/validation edges.
6. Run `php artisan test` for backend changes and `vendor/bin/pint` when formatting is needed.

## Architecture Defaults

- Interfaces: `app/Interfaces/Repositories/{Item}RepositoryInterface.php`.
- Repositories: `app/Repositories/{Item}Repository.php`.
- Services: `app/Services/{Item}Service.php` or a domain-specific service namespace.
- Requests: `app/Http/Requests/{Domain}/{Action}{Item}Request.php` when complexity justifies grouping.
- Controllers should not contain query-heavy or transaction-heavy business logic.

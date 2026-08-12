---
name: restaurant-inertia-crud
description: Use when creating or modifying Inertia Vue CRUD screens, admin/staff pages, dialogs, tables, forms, filters, or shadcn-vue UI for this restaurant system.
---

# Restaurant Inertia CRUD

Use this skill for frontend work in the Laravel/Inertia/Vue restaurant app.

## Required Reads

- Read `references/frontend-patterns.md` before implementing Vue UI.
- Read `references/crud-workflow.md` for CRUD pages, tables, dialogs, filters, and destructive actions.
- Read relevant existing components in `resources/js/Components` before creating new components.

## Default Workflow

1. Identify the page role: admin, manager, waiter, kitchen, cashier, or customer.
2. **Component Reuse First**: Inspect `resources/js/Components` BEFORE creating custom buttons, dialogs, form inputs, modals, pagination, or badges. Reuse existing components (`PrimaryButton`, `SecondaryButton`, `DangerButton`, `SuccessButton`, `WarningButton`, `BaseButton`, `Modal`, `TextInput`, `InputLabel`, `InputError`, `Checkbox`, `QuantitySelector`, `Paginator`, `OrderStatusBadge`) instead of raw HTML elements.
3. Reuse `gogilo/breeze` components and shadcn-vue primitives before building new UI.
4. Keep the index page as the main workspace for CRUD.
5. Use dialogs or slideovers for create, edit, view, and delete flows unless the workflow is complex.
6. Include loading, empty, and error states for async/list views.
7. Verify mobile layout and keyboard accessibility before finishing.

## Stack Rules

Use Vue 3 `script setup`, TypeScript, composables for reusable state/actions, Tailwind scale spacing, shadcn-vue components, and mobile-first responsive layouts.

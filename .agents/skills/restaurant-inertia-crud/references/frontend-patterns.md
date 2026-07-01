# Frontend Patterns

## Page Composition

Use Inertia pages in `resources/js/Pages/{Domain}/Index.vue` for primary workspaces. Keep repeated UI in `resources/js/Components`. Use composables for reusable table state, dialog state, filters, or async actions.

Use `script setup lang="ts"` and define interfaces for props, rows, filters, and form payloads.

## Visual Direction

The UI should feel like an operational dashboard: dense enough for repeated staff use, calm, readable, and polished. Draw from Tailwind UI, shadcn/ui, Linear, Stripe Dashboard, and GitHub.

Use consistent Tailwind spacing, restrained borders, clear hierarchy, and status badges for restaurant states such as available, occupied, preparing, ready, billing, paid, and cancelled.

## Component Priority

1. Existing project or `gogilo/breeze` components.
2. shadcn-vue components.
3. New local components only when reuse or clarity justifies them.

Do not create a new primitive if an existing Button, Dialog, Dropdown, Input, Select, Table, Badge, Alert, Skeleton, or Toast pattern can be reused.

## Accessibility

Prefer semantic controls. Ensure dialogs have titles, descriptions where useful, focus trapping, Escape handling, and keyboard-friendly primary/secondary actions. Inputs need labels or accessible names. Status colors must be paired with text.

## Async States

Every async workflow should expose clear state: pending button labels or spinners, disabled duplicate submissions, success feedback, validation errors, and failure messages. List pages need skeletons or loading rows, empty states, and error recovery actions.

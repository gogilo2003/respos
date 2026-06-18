---
name: restaurant-project-context
description: Use when assessing project status, planning milestone work, reconciling implementation with materials documents, or making architecture decisions for the QR-based restaurant ordering system.
---

# Restaurant Project Context

Use this skill before planning or evaluating feature work for the restaurant system.

## Required Reads

- Read `materials/restaurant-project.md` when requirements, roles, permissions, or business scope matter.
- Read `materials/prompts.md` when mapping work to milestones or deciding the next implementation slice.
- Read `materials/database.sql` when checking intended schema shape. The SQL reference follows the Laravel migrations and uses numeric IDs.
- Read `AGENTS.md` for baseline contributor rules.

## Project Positioning

The system is a Phase 1 cash-first restaurant ordering and billing app. Core domains are staff auth, roles, menu management, restaurant tables, QR sessions, customer ordering, waiter operations, kitchen processing, cashier billing, cash reconciliation, notifications, and audit logs.

## Planning Rules

- Prefer milestone-aligned work; avoid jumping to kitchen, cashier, or real-time features before auth, roles, repositories, and menu/table foundations are stable.
- Treat migrations as the source of truth for implemented schema. Update `materials/database.sql` only when schema intent changes.
- Numeric auto-increment IDs are intentional. Do not reintroduce UUID primary keys unless explicitly requested.
- When reporting status, distinguish scaffolded files from completed workflows.

# CRUD Workflow

## Default Layout

Use the index page as the main workspace:

- Header with title, short context, and primary action.
- Search input before filters.
- Compact filter controls with progressive disclosure for advanced filters.
- Table or card list depending on viewport and role.
- Row actions in a menu when there are multiple secondary actions.

## Create And Edit

Use dialogs for short forms and slideovers for medium forms. Use dedicated pages only for long, multi-section, or high-risk workflows.

Submit with Inertia forms or a shared composable. Preserve user input on validation errors. Show loading states on submit buttons and prevent duplicate submissions.

## View Details

Prefer inline expansion, dialog, or slideover detail views when users need quick inspection without losing table context. Use a dedicated page only when the detail view becomes a workspace.

## Delete And Destructive Actions

Always confirm destructive actions. Confirmations should name the affected item and explain impact. Use danger styling only for the destructive button, not the whole dialog.

## Empty States

Empty states should be action-oriented. For example, a menu item list can offer a visible "Add menu item" action. Search empty states should offer a clear way to reset search/filter input.

## Mobile Behavior

Keep primary actions visible. Tables may become stacked cards on small screens. Dialogs must fit small screens without clipping actions.

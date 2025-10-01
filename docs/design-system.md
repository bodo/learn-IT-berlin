# Design System Guidelines

The interface is built with Tailwind CSS v4 and DaisyUI. This document outlines how to work with the design tokens and components already configured in the project.

## Theme Tokens
- Primary, secondary, accent, info, success, warning, and error colors are defined via CSS custom properties in `resources/css/app.css`.
- The theme respects light and dark modes. Themes are switched by updating `document.documentElement.dataset.theme` (`light`, `dark`).
- Buttons and cards share a consistent border radius (`--radius-btn`, `--radius-box`) and shadow (`--shadow-card`). Use Daisy button/card classes to inherit them.

## Layouts
- Public pages use `resources/views/components/layouts/public.blade.php`. It already provides navigation, responsive menus, toasts, and footer.
- Authentication views use layouts under `resources/views/components/layouts/auth`. Select `simple`, `card`, or `split` depending on the visual you need instead of re‑creating layouts.
- Add breadcrumbs with `<x-ui.breadcrumbs />` to keep secondary navigation consistent.

## Components
- Toast notifications are normalised via `<x-ui.toast>`, which reads `session('success')` automatically in the layout. Reuse this component for other message types if required.
- Alerts, cards, and forms should leverage Daisy classes (`alert`, `card`, `btn`, `input`, `textarea`). Avoid custom CSS unless a component cannot be expressed with Daisy + Tailwind utilities.
- Use the new breadcrumbs component when introducing deeper navigation levels.

## Forms
- Inputs should use the Daisy `input`/`select` components with Tailwind utilities for spacing.
- Validation errors should be shown under the input with `text-error` and `text-sm`.

## Accessibility
- Stick to semantic HTML where possible.
- The global skip link (`.skip-link`) and `[x-cloak]` utility are provided for keyboard navigation and Alpine transitions.
- When adding icons to actionable elements, provide `sr-only` labels.

## Dark Mode
- Theme switching is handled centrally in `resources/views/partials/head.blade.php` (`window.learnItTheme`). Call `window.learnItTheme.set('light'|'dark'|'system')` to update the theme consistently.
- When introducing new components, test both `data-theme="light"` and `data-theme="dark"` to confirm contrast ratios are acceptable.

## Adding New Components
1. Start with the DaisyUI component that best matches the requirement.
2. Compose with Tailwind utilities for spacing or responsive tweaks.
3. If the component will be reused, add a Blade component under `resources/views/components/ui` and document its API.
4. Update this file when introducing notable UI primitives or patterns.

Following these practices keeps the interface cohesive and reduces ad-hoc styling throughout the project.

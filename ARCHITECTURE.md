# Architecture Overview

## Tech Stack
- **Backend:** Laravel 12 with Livewire Volt components for reactive UI and Fortify-powered auth flows.
- **Frontend:** Tailwind CSS v4 with DaisyUI components; Lucide icon set for consistent iconography; Alpine.js for lightweight interactivity within Livewire views.
- **Testing:** Pest for feature/unit coverage, focusing on auth and role management behaviour.

## Core Concepts
- **User Roles:** A single `users.role` enum (`user`, `trusted_user`, `admin`, `superuser`) backed by the `App\Enums\UserRole` enum. Role helpers live on `App\Models\User` for permissions and query scopes. `app/Http/Middleware/RoleMiddleware` enforces route-level access and honours the role hierarchy.
- **Authorization Gates:** Defined in `App\Providers\AuthServiceProvider` and mapped to role helpers so higher roles inherit lower privileges automatically.
- **Role Management:** `App\Livewire\Admin\UserRoleManager` exposes Superuser-only role assignment. It persists enum values, emits audit logs, and renders via `resources/views/livewire/admin/user-role-manager.blade.php` using DaisyUI tables/dropdowns.
- **Layouts & UI:** Public and auth layouts (`resources/views/components/layouts`) share a DaisyUI-driven navigation shell, with theme auto-detection (`partials/head.blade.php`) and `appearance` settings wiring into `localStorage`. Flux dependencies were removed in favour of plain Tailwind/Daisy markup.

## Key Flows
- **Authentication:** Fortify scaffolding powers login, registration, password reset, two-factor challenge, and verification screens. All Volt views have been restyled with DaisyUI form controls.
- **Settings:** Settings screens share a sidebar layout (`x-settings.layout`) with DaisyUI menus and cards. Two-factor management now uses DaisyUI modals and cards while preserving Fortify’s enable/confirm/disable pipeline; recovery codes are handled by a dedicated Livewire widget.

## Testing Notes
- Role system feature tests live in `tests/Feature/RoleSystemTest.php`, covering hierarchy checks and the role management component. Existing Fortify and settings suites continue to validate auth flows after the DaisyUI refactor.


# Architecture Overview

## Tech Stack
- **Backend:** Laravel 12 with Livewire Volt components for reactive UI and Fortify-powered auth flows.
- **Frontend:** Tailwind CSS v4 with DaisyUI components; Lucide icon set for consistent iconography; Alpine.js for lightweight interactivity within Livewire views.
- **Testing:** Pest for feature/unit coverage, focusing on auth and role management behaviour.

## Core Concepts
- **User Roles:** A single `users.role` enum (`user`, `trusted_user`, `admin`, `superuser`) backed by `App\Enums\UserRole`. Role helpers live on `App\Models\User` for permissions and query scopes. `app/Http/Middleware/RoleMiddleware` enforces route-level access and honours the hierarchy, while the model exposes a `avatarUrl()` helper for UI fallbacks.
- **Authorization & Management:** Gates live in `App\Providers\AuthServiceProvider`; Superusers adjust roles through `App\Livewire\Admin\UserRoleManager`, which logs changes and renders via `resources/views/livewire/admin/user-role-manager.blade.php`.
- **User Profiles:** Registration and settings capture `display_name`, `bio`, optional avatars, and lock the default role to `user`. Profile uploads land in `storage/app/public/avatars` with clean-up handled in the Volt component. Session tracking persists in `user_sessions` via `App\Http\Middleware\RecordUserSession` and is surfaced through `App\Livewire\Settings\SessionManager`.
- **Layouts & UI:** Public/auth layouts (`resources/views/components/layouts`) use DaisyUI navigation with theme auto-detection (`resources/views/partials/head.blade.php`) and a user-configurable appearance toggle.

## Key Flows
- **Authentication:** Fortify scaffolding powers login, registration, password reset, two-factor challenge, and verification screens. DaisyUI-based Volt forms capture profile metadata and enforce email verification. Tests cover registration defaults (`tests/Feature/Auth/RegistrationTest.php`).
- **Settings:** `resources/views/components/settings/layout.blade.php` drives the settings shell with cards for profile, password, appearance, two-factor, session management, and account deletion. Two-factor flows reuse Fortify actions; session management surfaces active sessions with the ability to revoke other devices.
- **Dashboard:** `App\Livewire\Dashboard` prepares role-aware quick actions, upcoming events placeholders, RSVP and activity slots so future event/comment modules can populate them without layout churn.

## Testing Notes
- Role and auth coverage spans `tests/Feature/RoleSystemTest.php`, the Fortify suites, and expanded registration/profile tests (avatar storage, metadata updates). Session middleware is exercised implicitly through the 2FA/password confirmation flows which rely on Laravel’s share-errors middleware remaining in the web stack.

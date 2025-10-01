# Architecture Overview

## Tech Stack
- **Backend:** Laravel 12 with Livewire Volt components for reactive UI and Fortify-powered auth flows.
- **Frontend:** Tailwind CSS v4 with DaisyUI components; Lucide icon set for consistent iconography; Alpine.js for lightweight interactivity within Livewire views.
- **Testing:** Pest for feature/unit coverage, focusing on auth and role management behaviour.

## Core Concepts
- **User Roles:** A single `users.role` enum (`user`, `trusted_user`, `admin`, `superuser`) backed by `App\Enums\UserRole`. Role helpers live on `App\Models\User` for permissions and query scopes. `app/Http/Middleware/RoleMiddleware` enforces route-level access and honours the hierarchy, while the model exposes a `avatarUrl()` helper for UI fallbacks.
- **Authorization & Management:** Gates live in `App\Providers\AuthServiceProvider`; Superusers adjust roles through `App\Livewire\Admin\UserRoleManager`, which logs changes and renders via `resources/views/livewire/admin/user-role-manager.blade.php`.
- **User Profiles:** Registration and settings capture `display_name`, `bio`, optional avatars, and lock the default role to `user`. Profile uploads land in `storage/app/public/avatars` with clean-up handled in the Volt component. Session tracking persists in `user_sessions` via `App\Http\Middleware\RecordUserSession` and is surfaced through `App\Livewire\Settings\SessionManager`.
- **Groups:** `App\Models\Group` models public study groups with a `group_user` pivot (role + timestamps). Admins create groups through `App\Livewire\Admin\Groups\GroupIndex`, while owners collaborate via `GroupManage` to promote/demote members. Public discovery lives in `App\Livewire\Groups\Directory` and `Groups\Show`, where authenticated users can join/leave.
- **Events:** `App\Models\Event` stores UTC datetimes with per-event timezone metadata and spot limits, while `EventImage` manages ordered galleries. Group owners manage events via `App\Livewire\Admin\Events\EventIndex` and `EventForm` (draft vs published, images, spot configuration). Public feeds come from `App\Livewire\Events\ListByGroup` and `Events\Show` with DaisyUI cards and galleries.
- **RSVPs:** `event_rsvps` table with `App\Models\EventRsvp` and enum `App\Enums\RsvpStatus` (going/interested/not_going). `App\Services\RsvpService` centralizes state changes while `Event::recalcRsvps()` maintains fair first-come waitlists and keeps `events.reserved_spots` accurate. `Events\RsvpPanel` renders attendee actions and counts, `Events\WaitlistAdmin` gives organizers a live view of confirmed, waitlisted, and interested users, and an admin-only CSV export lives at `admin.events.attendees.export`.
- **Comments:** Event-bound comments live in `comments` with enum `App\Enums\CommentStatus`. `App\Services\CommentService` enforces sanitisation, rate limits, moderation workflows, notifications, and action logs. Public interaction runs through `Livewire\Events\Comments`, while moderators use `Livewire\Moderation\CommentsQueue` with digest notifications delivered by `PendingCommentsDigest` and a Livewire bell widget.
- **Event Feed:** `Livewire\Events\Feed` powers the public `/events` feed with fuzzy search, date filters, and pagination. It highlights matches, exposes RSVP counts/state, and eagerly loads group + thumbnail data so `resources/views/livewire/events/feed.blade.php` can render responsive Daisy cards.
- **Layouts & UI:** Public/auth layouts (`resources/views/components/layouts`) use DaisyUI navigation with theme auto-detection (`resources/views/partials/head.blade.php`) and a user-configurable appearance toggle.

## Key Flows
- **Authentication:** Fortify scaffolding powers login, registration, password reset, two-factor challenge, and verification screens. DaisyUI-based Volt forms capture profile metadata and enforce email verification. Tests cover registration defaults (`tests/Feature/Auth/RegistrationTest.php`).
- **Settings:** `resources/views/components/settings/layout.blade.php` drives the settings shell with cards for profile, password, appearance, two-factor, session management, and account deletion. Two-factor flows reuse Fortify actions; session management surfaces active sessions with the ability to revoke other devices.
- **Dashboard:** `App\Livewire\Dashboard` prepares role-aware quick actions, upcoming events placeholders, RSVP and activity slots so future modules (events, RSVPs) can populate them. Group and event pages share the public layout for a consistent experience.

## Testing Notes
- Role and auth coverage spans `tests/Feature/RoleSystemTest.php`, the Fortify suites, and expanded registration/profile tests (avatar storage, metadata updates). Session middleware is exercised implicitly through the 2FA/password confirmation flows, with group CRUD/membership verified in `tests/Feature/Groups` and event CRUD/visibility handled in `tests/Feature/Events`.

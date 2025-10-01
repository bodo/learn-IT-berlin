# Learn IT Berlin (internal)

Laravel 12 + Livewire application for organising CS study events in Berlin. Features are implemented per the docs in `instructions/` and summarised here for quick reference.

## Stack and Conventions
- Laravel 12, PHP 8.2, Pest for tests.
- Livewire components under `app/Livewire`, grouped by area (Admin, Events, Groups, Settings).
- Enums in `app/Enums` capture role and RSVP status.
- Tailwind v4 with DaisyUI; shared theme tokens in `resources/css/app.css`.
- Translations in `lang/en.json`, `lang/de.json`; validation overrides in `lang/*/validation.php`.

## Running Locally
1. `cp .env.example .env` and set at least database credentials, `APP_URL`, queue/mail defaults.
2. `composer install && npm install`.
3. `php artisan key:generate` (already run in post-create script, safe to repeat).
4. `php artisan migrate --seed` (creates schema plus role defaults).
5. `npm run dev` for asset build and `php artisan serve` for HTTP. The repo includes a `composer dev` script that starts HTTP server, queue listener, Pail, and Vite concurrently.
6. For image uploads:
   - Livewire temporary files stay on the `public` disk (`config/livewire.php`).
   - Ensure `storage/app/public` is writable and run `php artisan storage:link` once.

## Key Features
- **Roles & Permissions** (`01-roles-system.md`)
  - `users.role` enum managed via `App\Enums\UserRole` and helpers on the model.
  - Gates defined in `App\Providers\AuthServiceProvider`; superuser overrides everything.
  - Role management UI: `app/Livewire/Admin/UserRoleManager`.

- **Auth & Profiles** (`02-auth-enhancements.md`)
  - Fortify handles login/2FA; Volt components in `resources/views/livewire/auth/`.
  - Profiles and avatars managed via `resources/views/livewire/settings/` + Livewire components.
  - User dashboard (`app/Livewire/Dashboard`) shows upcoming events, RSVPs, quick actions.

- **Groups** (`03-groups-management.md`)
  - CRUD via `app/Livewire/Admin/Groups` components; ownership stored in `group_user` pivot with `GroupRole` enum.
  - Public listing/detail under `app/Livewire/Groups` and Blade views in `resources/views/livewire/groups/`.

- **Events** (`04-events-system.md`)
  - `app/Models/Event` handles timezone conversion and waitlist recalculation.
  - Admin CRUD in `app/Livewire/Admin/Events`; public display via `app/Livewire/Events/Show`.
  - Images stored via `EventImage` model; thumbnails displayed where available.

- **RSVPs** (`05-rsvp-functionality.md`)
  - `App\Services\RsvpService` manages state and promotions.
  - User UI: `app/Livewire/Events/RsvpPanel` with badges, counts, attendee avatars.
  - Dashboard surface lists confirmed, waitlist, interested events.

- **Comments & Moderation** (`06-comments-moderation.md`)
  - `App\Models\Comment` with moderation state log + reports.
  - Users interact through `app/Livewire/Events/Comments` (auto-approve for trusted roles).
  - Moderators use `app/Livewire/Moderation/CommentsQueue` with bulk actions and digest notifications.

- **Feed & Search** (`07-feed-search.md`)
  - `/events` handled by `app/Livewire/Events/Feed` with fuzzy search, date filters, pagination.
  - Highlights search terms and shows RSVP counts/user status.

- **UI** (`08-ui-improvements.md`)
  - Layouts in `resources/views/components/layouts/` with Daisy navbar, toasts, breadcrumbs UI component (`resources/views/components/ui`).
  - Theme manager exposed via `window.learnItTheme` for appearance settings (`resources/views/livewire/settings/appearance.blade.php`).
  - Design notes in `docs/design-system.md`.

- **Localization** (`09-i18n-localization.md`)
  - English default, German alt. Translate via JSON files; set locale with standard Laravel mechanisms.
  - Validation error copy overridden to give meaningful Livewire upload errors.

- **Cloud Storage** (`10-laravel-cloud-setup.md`)
  - Uses Cloudflare R2 via Laravel Cloud. Install `league/flysystem-aws-s3-v3`.
  - Attach bucket (e.g. `public_media`) in Cloud dashboard; env vars (`FILESYSTEM_DISK`, `UPLOADS_DISK`, `AWS_*`, `AWS_USE_PATH_STYLE_ENDPOINT=true`) are injected automatically on deploy.
  - Livewire temporary uploads remain local; final images stored via `Storage::disk(config('filesystems.uploads_disk'))` (aliased to the bucket).

## Code Layout Quick Reference
```
app/Enums/           Role and RSVP enums
app/Livewire/        Livewire components (admin, events, groups, moderation, notifications, settings)
app/Models/          Eloquent models (Event, Group, EventImage, Comment, etc.)
app/Services/        Domain services (RsvpService, CommentService)
resources/views/     Blade + Livewire views, layouts, UI components
resources/lang/      JSON translations + validation overrides
config/livewire.php  Explicit temporary upload disk config
config/filesystems.php Custom default and named disks (local + R2)
docs/design-system.md Theme tokens and component guidelines
instructions/*.md    Original implementation specs
```

## Testing
- Run the entire suite with `vendor/bin/pest`.
- Feature coverage exists for auth, groups, events, RSVPs, comments, feed filters, and image uploads.

## Deployment Checklist (Laravel Cloud)
1. Install `league/flysystem-aws-s3-v3` and commit `composer.lock`.
2. Attach the R2 bucket (`public_media`) in the environment; redeploy so env vars update.
3. Confirm `FILESYSTEM_DISK=bucket`, `UPLOADS_DISK=bucket`, and `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=public`.
4. Queue workers, scheduled jobs, and mail transport are configured via Cloud UI as needed.
5. Run migrations and seeds during deploy (`php artisan migrate --force`).

## Operational Notes
- Livewire temp upload limit still honour PHP `upload_max_filesize` / `post_max_size`; keep them > file sizes you expect.
- Toast component reads `session('success')`; large actions should flash messages instead of inline alerts.
- Comments and notifications rely on database tables (`comments`, `comment_reports`, `comment_moderation_logs`, `notifications`). Ensure migrations are current.
- Update translations whenever you touch user-facing copy; keep English/German parity.

This README is internal-facing. Refer to the original `instructions/*.md` for deeper, task-specific context.

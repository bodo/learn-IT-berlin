# Learn-it Berlin - Implementation Plan

This directory contains the implementation instructions for building the Learn-it Berlin meetup application, broken down into manageable, testable parts.

## Project Overview

A minimal meetup clone for Berlin computer science learning events, built with Laravel + Livewire, featuring role-based permissions, group management, events with RSVP system, and moderated comments.

## Existing Foundation

The project starts with:
- Laravel 12 with Livewire/Volt
- Laravel Fortify (authentication with 2FA)
- Tailwind CSS (need to add Daisy UI)
- Pest testing framework
- Basic User model

## Implementation Order & Dependencies

### Phase 1: Foundation (Can be done in parallel)
1. **01-roles-system.md** - Role-based permission system (foundational)
2. **08-ui-improvements.md** - Daisy UI integration and design system

### Phase 2: Core Features (Requires Phase 1)
3. **02-auth-enhancements.md** - User profile and dashboard (depends on roles)
4. **03-groups-management.md** - Groups CRUD system (depends on roles)

### Phase 3: Events System (Requires Phase 2)
5. **04-events-system.md** - Events CRUD and management (depends on groups)
6. **05-rsvp-functionality.md** - RSVP system with waitlists (depends on events)

### Phase 4: Social Features (Requires Phase 3)
7. **06-comments-moderation.md** - Comments with moderation (depends on events, roles)
8. **07-feed-search.md** - Public event feed with search (depends on events)

### Phase 5: Polish & Deployment (Requires previous phases)
9. **09-i18n-localization.md** - English/German translations (depends on UI)
10. **10-laravel-cloud-setup.md** - Production deployment (depends on all features)

## Key Design Principles

- **Role-based access**: Users < TrustedUsers < Admins < Superuser
- **Group ownership**: Only Admins can create groups; groups own events
- **Moderated content**: Comments require approval except from TrustedUsers+
- **Public accessibility**: Event feed works without authentication
- **Minimal design**: Use Daisy UI components, avoid over-engineering

## Testing Strategy

Each module includes:
- Unit tests for models and core logic
- Feature tests for user workflows
- Integration tests for complex interactions
- Manual testing checklists

## Development Tips

1. Start with Phase 1 to establish foundation
2. Each instruction file can be tackled independently within its phase
3. Test thoroughly before moving to dependent modules
4. Keep features minimal and extensible
5. Follow Laravel conventions and best practices

## Architecture Notes

- Use Laravel's built-in features (Fortify, Eloquent, Gates)
- Leverage Livewire for reactive UI components
- Implement proper caching for performance
- Design for mobile-first responsive UI
- Plan for future features without over-engineering

## Success Criteria

- Users can create accounts with role-based permissions
- Admins can create and manage groups
- Group owners can create events with RSVP systems
- Comments work with proper moderation
- Public can browse events without authentication
- Application deploys successfully to Laravel Cloud

Each instruction file contains detailed implementation tasks, testing requirements, and notes for that specific module.
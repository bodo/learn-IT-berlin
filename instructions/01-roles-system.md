# 01 - Role System Implementation

## Goal
Implement a flexible role-based permission system with four distinct roles.

## Roles Definition
- **User**: Basic authenticated user, can RSVP events and add comments (need approval)
- **TrustedUser**: Like User but comments are public immediately
- **Admin**: Can create/delete groups, manage group ownership, and has all TrustedUser privileges
- **Superuser**: Bypasses all role checks, can promote users to Admin, manages role overview

## Implementation Tasks

### 1. Database Schema
- Add `role` enum column to users table: `user`, `trusted_user`, `admin`, `superuser`
- Create migration for role column with default value `user`

### 2. User Model Enhancement
- Add role accessor methods (`isUser()`, `isTrustedUser()`, `isAdmin()`, `isSuperuser()`)
- Add role checking methods (`canManageGroups()`, `canModerateComments()`, etc.)
- Add role scopes for querying users by role

### 3. Middleware & Gates
- Create role-based middleware for protecting routes
- Define Gates for specific permissions (manage-groups, moderate-comments, etc.)
- Implement role hierarchy (higher roles inherit lower role permissions)

### 4. Role Management Interface (Superuser only)
- Create Livewire component for role management dashboard
- Allow Superuser to view all users and change their roles
- Include role change history/audit log

## Dependencies
- None (this is foundational)

## Testing
- Unit tests for role checking methods
- Feature tests for role-based access control
- Test role hierarchy inheritance

## Notes
- Keep role logic simple and extensible
- Use Laravel's built-in authorization features where possible
- Ensure role changes are logged for audit purposes



# General Instructions

- Do not hallucinate features that are not specified
- Use i18n, as recommend with Laravel. Provide english and German JSON. Just the UI is i18n, event data fields are just any language (up to the creator), no fancy features for that.
- Utilize tailwind+daisy. ACtuALLy! use daisy UI components. Avoid custom CSS unless utterly necessary
- Keep the design lean. Avoid wrapping everything in five containers and cards and divs.
- Follow best practices. Use design patterns. Keep the code extensible and readable
- Think about a good folder and file structure. Adhere to DRY and single responsibility principle. Keep functions and files *short*
- Auto-detect light/darkmode and utilize via daisy/tailwind. Avoid using colors that break on dark mode.
- Keep an `ARCHITECTURE.md`, a living document for other dev on how the app is structured on high level. Do not waffle, no sycophancy, no marketing.
- Add unit tests for basic happy path.

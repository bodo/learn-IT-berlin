# 02 - Authentication Enhancements

## Goal
Enhance the existing Laravel Fortify authentication with role-aware features and user profile management.

## Current State
- Laravel Fortify is already configured
- Basic User model with 2FA support exists
- Registration and login flows are functional

## Enhancement Tasks

### 1. Registration Flow Updates
- Modify registration to set default role as `user`
- Add optional profile fields during registration (display name, bio)
- Implement email verification if not already active

### 2. Profile Management
- Create user profile edit component
- Allow users to update name, email, password
- Add avatar upload functionality (optional, simple file upload)
- Display current role in profile (read-only for non-superusers)

### 3. User Dashboard
- Create personalized dashboard showing:
  - Upcoming events user has RSVP'd to
  - Recent activity (comments, RSVPs)
  - Quick navigation based on user role
- Role-specific dashboard sections (Admin tools for Admins, etc.)

### 4. Account Security
- Leverage existing 2FA functionality
- Add session management (view active sessions)
- Account deletion functionality (with proper cascade handling)

### 5. User Directory (Optional)
- Simple user listing for Admins/Superusers
- Basic user search functionality
- User profile pages (public view)

## Dependencies
- 01-roles-system.md (role checking functionality)

## Testing
- Test registration flow with role assignment
- Test profile updates and validation
- Test role-specific dashboard content
- Test security features (2FA, session management)

## Notes
- Use existing Fortify features as much as possible
- Keep profile fields minimal initially
- Ensure all forms follow Laravel validation best practices
- Consider privacy settings for user profiles



# General Instructions


- Do not hallucinate features that are not specified
- If there are well-established libraries doing the feature that we want, use them!

- Use i18n, as recommend with Laravel. Provide english and German JSON. Just the UI is i18n, event data fields are just any language (up to the creator), no fancy features for that.
- Utilize tailwind+Daisy. ACtuALLy! use Daisy UI components. Avoid custom CSS unless utterly necessary
- Keep the design lean. Avoid wrapping everything in five containers and cards and divs.
- Follow best practices. Use design patterns. Keep the code extensible and readable
- Think about a good folder and file structure. Adhere to DRY and single responsibility principle. Keep functions and files *short*
- Auto-detect light/darkmode and utilize via Daisy/tailwind. Avoid using colors that break on dark mode.
- Keep an `ARCHITECTURE.md`, a living document for other dev on how the app is structured on high level. Do not waffle, no sycophancy, no marketing.
- Add unit tests for basic happy path.

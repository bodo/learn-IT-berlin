# 06 - Comments & Moderation System

## Goal
Implement a comments system for events with role-based moderation and approval workflow.

## Comment Rules
- Only events have comments (no comments on groups)
- User comments require approval before going public
- TrustedUser comments are public immediately
- Group moderators can approve/delete comments
- Keep comments minimal and focused

## Implementation Tasks

### 1. Database Schema
- Create `comments` table: id, event_id, user_id, content, status (pending/approved/rejected), approved_by (nullable), approved_at (nullable), created_at, updated_at
- Add foreign key constraints
- Create migration

### 2. Comment Model
- Define relationships: event(), user(), approvedBy()
- Add status enum: pending, approved, rejected
- Add scopes: approved(), pending(), byUser()
- Implement content validation (length limits, basic sanitization)

### 3. Comment Submission
- Comment form on event detail pages (authenticated users only)
- Auto-approve comments from TrustedUsers, Admins, Superusers
- Set status to "pending" for regular Users
- Simple textarea with character limit
- Basic spam prevention (rate limiting)

### 4. Comment Display
- Show approved comments on event pages
- Display comment author, timestamp, content
- Order comments chronologically
- Show "pending moderation" message for user's own pending comments
- Show comment count on events

### 5. Moderation Interface
- Moderation dashboard for group moderators/owners
- List pending comments for their group's events
- Approve/reject actions with single click
- Bulk moderation actions
- Show comment context (which event, when submitted)

### 6. Comment Management
- Users can edit their own comments (resets to pending if not TrustedUser)
- Users can delete their own comments
- Moderators can delete any comments on their group's events
- Soft delete implementation to maintain audit trail

### 7. Moderation Notifications
- Notify group moderators of new pending comments
- Simple notification system (in app only)
- Batch notifications to avoid spam

### 8. Comment Policies
- Clear commenting guidelines
- Simple content filtering (no URLs, basic profanity filter)
- Report comment functionality
- Comment history for moderators

## Dependencies
- 01-roles-system.md (role-based permissions and TrustedUser status)
- 03-groups-management.md (group moderators)
- 04-events-system.md (events to comment on)

## Testing
- Test comment submission and approval workflow
- Test role-based comment auto-approval
- Test moderation interface and actions
- Test comment editing and deletion
- Test notification system

## Notes
- Keep comment system simple - no replies, no rich text initially
- Focus on moderation workflow efficiency
- Design for future features like comment reactions or replies
- Ensure proper authorization for all comment actions
- Consider implementing comment templates for common responses




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

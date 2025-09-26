# 03 - Groups Management System

## Goal
Implement a groups system where Admins can create and manage groups, and users can join them.

## Group Structure
- **Group Model**: title, description (optional), banner_image (optional)
- **Relationships**: owners (at least one), moderators, members
- **Visibility**: Groups are public (anyone can see and join)

## Implementation Tasks

### 1. Database Schema
- Create `groups` table: id, title, description, banner_image_path, created_at, updated_at
- Create `group_user` pivot table: group_id, user_id, role (owner/moderator/member), joined_at
- Create migrations for both tables

### 2. Group Model
- Define relationships: owners(), moderators(), members(), allUsers()
- Add helper methods: `isOwner(User $user)`, `isModerator(User $user)`, `canManage(User $user)`
- Implement model scopes for public groups
- Add validation rules for title (required, unique), description (optional)

### 3. Group Management Interface (Admin Only)
- Group listing page with search and filtering
- Group creation form (title, description, banner upload)
- Group editing interface for owners
- Group deletion with confirmation (only if no events)

### 4. Group Membership Management
- Add/remove group owners (at least one owner required)
- Add/remove moderators
- View group members list
- Member management interface for owners

### 5. Public Group Directory
- Public listing of all groups
- Group detail pages showing description, members count, upcoming events
- Join/leave group functionality for authenticated users
- Simple search by group title

### 6. Image Upload
- Implement banner image upload for groups
- Resize/optimize images automatically
- Store images in appropriate directory structure
- Handle image deletion when group is deleted

## Dependencies
- 01-roles-system.md (Admin role checking)
- 02-auth-enhancements.md (user authentication)

## Testing
- Test group CRUD operations
- Test membership management (add/remove owners, moderators, members)
- Test group access permissions
- Test image upload functionality
- Test group deletion constraints

## Notes
- Keep group creation simple - only Admins can create groups initially
- Groups are containers for events, so design with events in mind
- Ensure proper authorization checks for all group operations
- Consider implementing group categories/tags in future iterations




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

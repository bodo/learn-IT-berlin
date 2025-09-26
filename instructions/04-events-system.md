# 04 - Events System

## Goal
Implement a comprehensive events system where group owners can create and manage events.

## Event Structure
- **Event Model**: title, description, images[], place (string), datetime, timezone
- **Features**: draft/published status, spot limits, waitlist functionality
- **Ownership**: Every event belongs to one group

## Implementation Tasks

### 1. Database Schema
- Create `events` table: id, group_id, title, description, place, event_datetime, timezone, max_spots (nullable), status (draft/published), created_at, updated_at
- Create `event_images` table: id, event_id, image_path, alt_text, order
- Create migrations with proper foreign key constraints

### 2. Event Model
- Define relationship to Group: `belongsTo(Group::class)`
- Define relationship to EventImages: `hasMany(EventImage::class)`
- Add status enum: draft, published
- Implement timezone handling (store UTC, display in local timezone)
- Add validation rules and accessors/mutators
- Add scopes: published(), upcoming(), byGroup()

### 3. Event Management Interface (Group Owners)
- Event creation form with:
  - Title and description (rich text editor optional)
  - Date/time picker with timezone selection
  - Location (string field)
  - Spot limit configuration (unlimited or specific number)
  - Multiple image upload
  - Draft/publish toggle
- Event editing interface
- Event listing for group owners
- Event deletion with confirmation

### 4. Event Display
- Event detail pages (public for published events)
- Event listing by group
- Event images gallery/carousel
- Timezone conversion for display
- Spot availability display (X of Y spots taken)

### 5. Spot Management
- Implement unlimited spots vs limited spots logic
- When limited spots: track available spots
- Show waitlist position when spots are full
- Allow owners to adjust spot limits
- Handle spot calculations correctly when users change RSVP status

### 6. Event Images
- Multiple image upload for events
- Image ordering/reordering
- Image deletion
- Responsive image display
- Alt text for accessibility

### 7. Event Status Management
- Draft events visible only to group owners
- Published events visible publicly
- Easy toggle between draft/published
- Prevent accidental publishing of incomplete events

## Dependencies
- 01-roles-system.md (role-based permissions)
- 03-groups-management.md (group ownership)

## Testing
- Test event CRUD operations
- Test image upload and management
- Test timezone handling
- Test spot limit calculations
- Test draft/published visibility
- Test group ownership permissions

## Notes
- Keep timezone handling robust - store in UTC, display in user's timezone
- Design with future features in mind (recurring events, reminders)
- Ensure proper validation for all date/time fields
- Consider event cancellation functionality
- Make image uploads optional but well-implemented when used




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

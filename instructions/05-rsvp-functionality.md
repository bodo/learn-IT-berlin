# 05 - RSVP Functionality

## Goal
Implement RSVP system allowing users to indicate attendance for events with waitlist support.

## RSVP Types
- **Going**: User plans to attend (takes a spot if limited)
- **Not Going**: User explicitly declines
- **Interested**: User is interested but not committed (doesn't take a spot)

## Implementation Tasks

### 1. Database Schema
- Create `event_rsvps` table: id, event_id, user_id, status (going/not_going/interested), waitlist_position (nullable), created_at, updated_at
- Add unique constraint on event_id + user_id
- Create migration with proper foreign keys

### 2. EventRsvp Model
- Define relationships: event(), user()
- Add status enum: going, not_going, interested
- Implement waitlist position calculation
- Add scopes: going(), interested(), notGoing(), onWaitlist()

### 3. RSVP Logic
- Users can only have one RSVP status per event
- When event has unlimited spots: "going" users are immediately confirmed
- When event has limited spots:
  - First X "going" users get spots
  - Additional "going" users go on waitlist
  - Calculate waitlist positions automatically
- "Interested" users never take spots or join waitlist

### 4. RSVP Interface
- RSVP buttons on event detail pages (authenticated users only)
- Show current RSVP status for logged-in user
- Display spot availability and waitlist status
- Allow users to change their RSVP status
- Show waitlist position if applicable

### 5. Attendee Management
- List of confirmed attendees (going users with spots)
- Waitlist display for event owners
- Promote users from waitlist when spots become available
- Export attendee list functionality for event owners

### 6. RSVP Status Updates
- Handle status changes and recalculate waitlist positions
- When "going" user changes to "not going", promote next waitlist user
- When spot limit increases, promote waitlist users
- no email stuff

### 7. User Dashboard Integration
- Show user's upcoming events they've RSVP'd "going" to
- Show events user is interested in
- Display waitlist status and position

### 8. Event Detail Enhancements
- Show RSVP counts: X going, Y interested
- Show attendee list (names or avatars)
- Show waitlist count if applicable
- Real-time updates when users change RSVP status

## Dependencies
- 02-auth-enhancements.md (user authentication)
- 04-events-system.md (events and spot management)

## Testing
- Test RSVP creation and updates
- Test waitlist calculations
- Test spot limit scenarios
- Test RSVP status changes and promotions
- Test edge cases (concurrent RSVPs, spot limit changes)

## Notes
- Handle race conditions when spots fill up quickly
- Consider caching RSVP counts for performance
- Design waitlist to be fair (first come, first served)
- Keep RSVP interface simple and intuitive
- Plan for future features like RSVP deadlines or requirements



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

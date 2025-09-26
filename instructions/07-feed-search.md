# 07 - Feed & Search Functionality

## Goal
Implement a public event feed with filtering, search, and pagination that works for both authenticated and unauthenticated users.

## Feed Requirements
- Show published events only
- Accessible without authentication
- Pagination support
- Time-based filters (today, tomorrow, this week, custom date range)
- Fuzzy text search on title and description
- Responsive design

## Implementation Tasks

### 1. Event Feed Controller
- Create public feed route and controller
- Implement pagination (Laravel's built-in pagination)
- Add query parameter handling for filters and search
- Ensure only published events are shown
- Order events by event_datetime (upcoming first)

### 2. Search Implementation
- Full-text search on event title and description
- Use Laravel Scout or database LIKE queries for fuzzy matching
- Implement search highlighting in results
- Search within filtered results
- Handle special characters and multiple keywords

### 3. Time-based Filters
- "Today" filter: events happening today
- "Tomorrow" filter: events happening tomorrow
- "This week" filter: events in next 7 days
- "Custom date range" filter: user-selected start/end dates
- "Upcoming" (default): all future events
- Proper timezone handling for date comparisons

### 4. Feed Interface
- Clean, responsive event feed layout
- Event cards showing: title, group, date/time, location, RSVP counts
- Filter buttons/dropdowns at top of feed
- Search bar with instant search or submit button
- Pagination controls at bottom
- "No events found" state

### 5. Event Card Design
- Consistent card layout for event preview
- Show event image if available (thumbnail)
- Display key info: title, group name, date/time, location
- Show RSVP counts and spot availability
- Link to full event detail page
- Responsive card grid

### 6. Feed Performance
- Implement proper database indexes for search and filtering
- Cache popular queries if needed
- Optimize database queries (eager loading)
- Consider implementing infinite scroll or load more button
- Handle large result sets efficiently

### 7. Feed URL Structure
- Clean URLs with query parameters: `/feed?search=laravel&filter=today`
- Shareable URLs for filtered results
- Maintain filter state in URL for bookmarking
- Handle empty states gracefully

### 8. Integration with Other Features
- Link event cards to full event detail pages
- Show group information and link to group page
- Display RSVP button for authenticated users
- Show user's RSVP status on event cards if logged in

## Dependencies
- 04-events-system.md (published events)
- 03-groups-management.md (group information display)

## Testing
- Test search functionality with various queries
- Test all time-based filters
- Test pagination with large datasets
- Test responsive design on different screen sizes
- Test performance with many events

## Notes
- Make search intuitive and forgiving (typos, partial matches)
- Design feed to handle growth (thousands of events)
- Consider adding more advanced filters in future (location, group, category)
- Ensure good SEO for public feed pages
- Plan for mobile-first responsive design



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

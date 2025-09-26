# 08 - UI Improvements with Daisy UI

## Goal
Integrate Daisy UI with Tailwind CSS and create a consistent, accessible design system throughout the application.

## Current State
- Tailwind CSS is already installed
- Need to add Daisy UI via CDN as specified
- Remove unnecessary boilerplate and demo files

## Implementation Tasks

### 1. Daisy UI Integration
- Add Daisy UI via CDN to the main layout template
- Configure Tailwind to work with Daisy UI components
- Remove existing demo content and unnecessary boilerplate files
- Set up auto-detection for light/dark mode

### 2. Design System Setup
- Choose consistent Daisy UI theme (consider accessibility)
- Define color palette that works in both light and dark modes
- Establish typography hierarchy using Daisy UI classes
- Create component library documentation for team consistency

### 3. Layout & Navigation
- Design main application layout with:
  - Header with navigation (responsive)
  - User authentication state display
  - Role-based navigation items
  - Mobile-friendly hamburger menu
- Footer with essential links
- Breadcrumb navigation for deep pages

### 4. Form Styling
- Standardize all forms using Daisy UI form components
- Implement consistent validation error display
- Create reusable form components (inputs, selects, file uploads)
- Ensure forms are accessible and keyboard-navigable

### 5. Component Styling
- Event cards using Daisy UI card component
- Button variations (primary, secondary, outline, etc.)
- Modal dialogs for confirmations and forms
- Toast notifications for user feedback
- Loading states and skeleton screens

### 6. Page Layouts
- Dashboard layout with sidebar navigation
- Public pages layout (feed, event details)
- Authentication pages (login, register, profile)
- Admin interfaces with consistent styling
- Error pages (404, 500) with proper styling

### 7. Responsive Design
- Mobile-first approach using Daisy UI responsive utilities
- Ensure all components work well on mobile devices
- Test on tablet and desktop breakpoints
- Optimize touch targets for mobile users

### 8. Dark Mode Implementation
- Auto-detect user's system preference
- Maintain theme choice in localStorage
- Ensure all custom components work in both modes
- Test readability and contrast in both themes

### 9. Accessibility Improvements
- Proper ARIA labels and roles
- Keyboard navigation support
- Focus management and visible focus indicators
- Screen reader friendly content structure
- Color contrast compliance

### 10. Clean Up
- Remove Laravel welcome page and demo content
- Clean up unused CSS and JavaScript files
- Remove any conflicting styles
- Optimize asset loading and bundle size

## Dependencies
- All other modules will use these UI components
- Should be implemented early to establish design patterns

## Testing
- Test responsive design on multiple devices
- Test dark/light mode switching
- Test accessibility with screen readers
- Test keyboard navigation
- Validate HTML and CSS

## Notes
- Use Daisy UI components whenever possible, avoid custom CSS
- Keep design lean and functional, not over-designed
- Ensure consistent spacing and sizing throughout
- Document component usage for other developers
- Consider creating a style guide page for reference
- Plan for future component additions as features grow



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

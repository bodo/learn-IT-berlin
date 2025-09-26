# 08 - UI Improvements with Daisy + Tailwind CSS

## Goal
Leverage Daisy (official Livewire component library) with Tailwind CSS to create a consistent, accessible design system throughout the application.

## Current State
- Tailwind CSS is already installed
- Daisy component library is already installed and configured
- Remove unnecessary boilerplate and demo files
- Existing Daisy components in place

## Implementation Tasks

### 1. Daisy + Tailwind Setup
- Ensure Daisy is properly configured with Tailwind CSS
- Remove existing demo content and unnecessary boilerplate files
- Optimize Daisy theme configuration for the meetup app

### 2. Design System Setup
- Choose consistent Daisy theme (consider accessibility)
- Define color palette that works in both light and dark modes
- Establish typography hierarchy using Daisy components and Tailwind utilities
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
- Standardize all forms using Daisy form components
- Implement consistent validation error display with Daisy
- Create reusable form components (Daisy:input, Daisy:select, Daisy:file, etc.)
- Ensure forms are accessible and keyboard-navigable

### 5. Component Styling
- Event cards using Daisy card components
- Button variations using Daisy button system
- Modal dialogs using Daisy modal components
- Toast notifications using Daisy notification system
- Loading states and skeleton screens with Daisy

### 6. Page Layouts
- Public pages layout (feed, event details)
- Authentication pages (login, register, profile)
- Admin interfaces with consistent styling
- Error pages (404, 500) with proper styling

### 7. Responsive Design
- Mobile-first approach using Daisy responsive utilities and Tailwind breakpoints
- Ensure all Daisy components work well on mobile devices
- Test on tablet and desktop breakpoints
- Optimize touch targets for mobile users

### 8. Dark Mode Implementation
- Leverage Daisy's built-in dark mode support (@DaisyAppearance)
- Auto-detect user's system preference
- Ensure all Daisy components work in both light and dark modes
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
- Use Daisy components whenever possible, complement with Tailwind utilities
- Keep design lean and functional, not over-designed
- Ensure consistent spacing and sizing throughout
- Document component usage for other developers
- Leverage Daisy's built-in accessibility features
- Plan for future component additions as features grow



# General Instructions

- Do not hallucinate features that are not specified
- Use i18n, as recommend with Laravel. Provide english and German JSON. Just the UI is i18n, event data fields are just any language (up to the creator), no fancy features for that.
- Utilize tailwind+Daisy. Use Daisy UI components as the primary component library. Complement with Tailwind utilities. Avoid custom CSS unless utterly necessary
- Keep the design lean. Avoid wrapping everything in five containers and cards and divs.
- Follow best practices. Use design patterns. Keep the code extensible and readable
- Think about a good folder and file structure. Adhere to DRY and single responsibility principle. Keep functions and files *short*
- Auto-detect light/darkmode and utilize via Daisy/tailwind. Avoid using colors that break on dark mode.
- Keep an `ARCHITECTURE.md`, a living document for other dev on how the app is structured on high level. Do not waffle, no sycophancy, no marketing.
- Add unit tests for basic happy path.

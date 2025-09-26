# 09 - Internationalization (i18n) Setup

## Goal
Implement Laravel's internationalization system with English and German language support for the user interface.

## Scope
- UI elements, labels, buttons, messages in English and German
- Event data (titles, descriptions) remain in creator's chosen language
- No translation features for user-generated content
- Simple language switching mechanism

## Implementation Tasks

### 1. Laravel i18n Configuration
- Configure `config/app.php` for multi-language support
- Set default locale to English ('en')
- Add German ('de') as supported locale
- Set up locale detection (URL parameter, session, or header)

### 2. Language Files Structure
- Create `resources/lang/en/` directory with JSON files
- Create `resources/lang/de/` directory with JSON files
- Organize translations by feature:
  - `auth.json` - Authentication related strings
  - `events.json` - Event management strings
  - `groups.json` - Group management strings
  - `common.json` - Common UI elements
  - `validation.json` - Validation error messages

### 3. Translation Implementation
- Replace all hardcoded English strings with `__()` helper calls
- Implement translation keys using dot notation or JSON keys
- Translate all user-facing text:
  - Navigation and menu items
  - Form labels and placeholders
  - Button text and CTAs
  - Success/error messages
  - Validation messages
  - Email templates

### 4. Language Switching
- Add language switcher to main navigation
- Implement locale switching via route parameters or session
- Maintain user's language preference
- Update URL structure if using route-based locale detection

### 5. Date and Time Localization
- Localize date/time formats for German and English
- Use Carbon's localization features for relative dates
- Ensure timezone handling works with both locales
- Format dates according to local conventions

### 6. Form Validation Localization
- Translate Laravel's built-in validation messages
- Customize validation messages for specific fields
- Ensure error messages appear in user's selected language

### 7. Email Localization
- Translate notification emails (RSVP confirmations, etc.)
- Use user's preferred language for emails
- Localize email subject lines and content

### 8. Livewire Component Localization
- Ensure all Livewire components support translations
- Handle language switching in reactive components
- Update component content when locale changes

### 9. Admin Interface Localization
- Translate admin panel interface
- Consider English-only for admin functions if preferred
- Ensure role management interface is translated

### 10. Testing Localization
- Test language switching functionality
- Verify all UI elements are translated
- Test pluralization rules for both languages
- Ensure no untranslated strings appear

## Dependencies
- 08-ui-improvements.md (UI components to translate)
- All other modules (will need translations)

## Testing
- Test complete application in both English and German
- Verify language switching preserves user state
- Test form validation in both languages
- Check email templates in both languages

## Translation Guidelines
- Keep German translations natural, not literal
- Use formal "Sie" form in German for consistency
- Maintain consistent terminology across the application
- Keep translations concise for UI space constraints

## Notes
- Start with English as the primary language
- German translations should be reviewed by native speaker
- Consider using translation services for accuracy
- Plan for easy addition of more languages in future
- Keep translation keys descriptive and organized
- Consider using Laravel's JSON translation format for simplicity



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

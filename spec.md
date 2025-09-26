## General App

- this is supposed to be a *minimal* meetup clone; but with less features, and for Berlin computer science learning stuff only
- it should also include some features for studying (at a later point, just keep in mind)

## Roles 

- `User`, someone who can sign in, RSPV events, and add comments to events, but they need approval before going public
- `TrustedUser` which is like user only their comments are public immediately
- `Admin`, which can create/delete groups, and also can do everything trusted users can
- A `Superuser` role which bypasses all role checks and can do everything, only one who can make people `Admin`
- roles are managed in an overview by a superuser

## Event Structure

- An `Event` must always be owned by one `Group`
- `Admin` has CRUD operations to administer groups
- groups have a title, an optional banner image, an optional description
- they have owners[] (at least one), moderators (who can approve comments, see below), and members
- groups are generally public, because joining them doesn't mean anything as of yet

- An `Event` has a title, a descrption, and optional images[].
- it also has a place (string) and a time (make sure to manage timezone stuff safely)
- Group owners have CRUD operations for events
- events can be draft or published (published means publically visible)
- events can have a limited nr of spots, or unlimited spots. if limited, RSPV "going" means you show up on a waitlist.
- events have comments (nothing else has comments). Keep them minimal for now; do not overengineer.
- build a 

- It should be possible to see events in a feed, even if not authenticated; with basic features such as pagination, time filters ("today", "tomorrow", "this week", custom date) and fuzzy text search on title+description
- Signed in users can RSPV (going, not going, interested) events
- use the provided laravel default auth stuff as much as possible, KISS
- add a view for moderators to approve or delete comments


## Features

- use tailwind+daisy UI via cdn
- remove unneeded boilerplate and demo files
- there is a dashboard/start page which shows relevant upcoming events, and links to important pages as needed for given roles
- make app ready for Laravel Cloud

## General Instructions

- Do not hallucinate features that are not specified
- Use i18n, as recommend with Laravel. Provide english and German JSON. Just the UI is i18n, event data fields are just any language (up to the creator), no fancy features for that.
- Utilize tailwind+daisy. ACtuALLy! use daisy UI components. Avoid custom CSS unless utterly necessary
- Keep the design lean. Avoid wrapping everything in five containers and cards and divs.
- Follow best practices. Use design patterns. Keep the code extensible and readable
- Think about a good folder and file structure. Adhere to DRY and single responsibility principle. Keep functions and files *short*
- Auto-detect light/darkmode and utilize via daisy/tailwind. Avoid using colors that break on dark mode.
- Keep an `ARCHITECTURE.md`, a living document for other dev on how the app is structured on high level. Do not waffle, no sycophancy, no marketing.
- Add unit tests for basic happy path.

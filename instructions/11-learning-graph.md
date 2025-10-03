- add a new feature for learning graphs (=road maps) (e.g. "how to become a freelance mobile developer")
- learning graphs are per group, and thus can be edited by group owner (or admins, or superuser). Check the role system.
- a learning graph can have nodes, the nodes can have an arbitrary number of blocks (ordered) that are either text or an image, as well as edges to other notes, with arrow to receiver, arrow to sender, both or none. edges may or may not be labelled
- graphs are *administred* via laravel/livewire forms, *not* from within the visualized graph. Come up with a clean meta-form to edit the blocks.
- the graph may be publishd or draft, like events
- the graph is rendered to users via vis-network (CDN). Keep visualization simple yet useful. 
- node block text nodes are edited simply as a text area, but interpreted as markdown (> rendered to HTML) on the visualized graph. Support the basic stuff, `**`, `[label](url)`, `## heading`, and so on.


# General Instructions

- Integrate into the existing app, reusing relevant patterns.
- Do not hallucinate features that are not specified
- Use i18n, as recommend with Laravel. Provide english and German JSON. Just the UI is i18n, event data fields are just any language (up to the creator), no fancy features for that.
- Utilize tailwind+Daisy. ACtuALLy! use Daisy UI components. Avoid custom CSS unless utterly necessary. May be best way for the graph itself; just check.
- Keep the design lean. Avoid wrapping everything in five containers and cards and divs.
- Follow best practices. Use design patterns. Keep the code extensible and readable
- Think about a good folder and file structure. Adhere to DRY and single responsibility principle. Keep functions and files *short*
- Auto-detect light/darkmode and utilize via Daisy/tailwind. Avoid using colors that break on dark mode.
- Add unit tests for basic happy path.
- Run tests after done
- Amend `README.md` after done.

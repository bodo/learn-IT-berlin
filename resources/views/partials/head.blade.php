<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

<!-- Daisy UI -->
<link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.13/dist/full.min.css" rel="stylesheet" type="text/css" />

<script>
    (() => {
        const KEY = 'learnit-theme';
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

        const preferredTheme = () => (mediaQuery.matches ? 'dark' : 'light');

        const applyTheme = (value) => {
            document.documentElement.dataset.theme = value;
            document.documentElement.classList.toggle('dark', value === 'dark');
        };

        const storedTheme = () => localStorage.getItem(KEY);

        const syncTheme = () => {
            const stored = storedTheme();
            if (stored === 'light' || stored === 'dark') {
                applyTheme(stored);
            } else {
                applyTheme(preferredTheme());
            }
        };

        window.learnItTheme = {
            current() {
                return storedTheme() ?? 'system';
            },
            set(value) {
                if (value === 'system') {
                    localStorage.removeItem(KEY);
                    applyTheme(preferredTheme());
                    return;
                }

                localStorage.setItem(KEY, value);
                applyTheme(value);
            },
            apply: applyTheme,
        };

        syncTheme();

        mediaQuery.addEventListener('change', (event) => {
            if (!storedTheme()) {
                applyTheme(event.matches ? 'dark' : 'light');
            }
        });
    })();
</script>

@livewireStyles

@vite(['resources/css/app.css', 'resources/js/app.js'])

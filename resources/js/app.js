window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
    const stored = localStorage.getItem('learnit-theme');

    if (stored) {
        return;
    }

    const theme = event.matches ? 'dark' : 'light';
    document.documentElement.dataset.theme = theme;
    document.documentElement.classList.toggle('dark', event.matches);
});

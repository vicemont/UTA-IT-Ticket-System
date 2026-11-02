(() => {
    const storageKey = 'uta-help-desk-theme';
    const root = document.documentElement;
    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)');

    function getSavedTheme() {
        try {
            const savedTheme = localStorage.getItem(storageKey);
            return savedTheme === 'light' || savedTheme === 'dark' ? savedTheme : null;
        } catch (error) {
            console.warn('Theme preference could not be read:', error);
            return null;
        }
    }

    function applyTheme(theme) {
        root.dataset.theme = theme;
        document.querySelectorAll('.theme-toggle').forEach(button => {
            const nextTheme = theme === 'dark' ? 'light' : 'dark';
            button.textContent = `Switch to ${nextTheme} mode`;
            button.setAttribute('aria-label', `Switch to ${nextTheme} mode`);
            button.setAttribute('aria-pressed', String(theme === 'dark'));
        });
    }

    applyTheme(getSavedTheme() || (systemTheme.matches ? 'dark' : 'light'));

    document.querySelectorAll('.theme-toggle').forEach(button => {
        button.addEventListener('click', () => {
            const nextTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
            applyTheme(nextTheme);

            try {
                localStorage.setItem(storageKey, nextTheme);
            } catch (error) {
                console.warn('Theme preference could not be saved:', error);
            }
        });
    });
})();

const themeToggle = document.getElementById('themeToggle');

function setTheme(isDark) {
    document.documentElement.classList.toggle('dark-mode', isDark);
    themeToggle.setAttribute('aria-checked', isDark);
    try { localStorage.setItem('theme', isDark ? 'dark' : 'light'); } catch (e) {}
}

themeToggle.addEventListener('click', () => {
    setTheme(!document.documentElement.classList.contains('dark-mode'));
});

// restore saved preference on load
let saved = null;
try { saved = localStorage.getItem('theme'); } catch (e) {}
setTheme(saved === 'dark');
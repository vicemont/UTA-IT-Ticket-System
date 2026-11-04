document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.getElementById('hamburger');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    if (hamburger && dropdownMenu) {
        hamburger.addEventListener('click', (event) => {
            event.stopPropagation();
            const isExpanded = hamburger.getAttribute('aria-expanded') === 'true';
            hamburger.setAttribute('aria-expanded', String(!isExpanded));
            hamburger.setAttribute('aria-label', isExpanded ? 'Open menu' : 'Close menu');
            dropdownMenu.classList.toggle('show', !isExpanded);
        });

        document.addEventListener('click', (event) => {
            if (!dropdownMenu.contains(event.target) && !hamburger.contains(event.target)) {
                dropdownMenu.classList.remove('show');
                hamburger.setAttribute('aria-expanded', 'false');
                hamburger.setAttribute('aria-label', 'Open menu');
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                dropdownMenu.classList.remove('show');
                hamburger.setAttribute('aria-expanded', 'false');
                hamburger.setAttribute('aria-label', 'Open menu');
            }
        });
    }

    const logoutButton = document.getElementById('logoutButton');
    if (logoutButton) {
        logoutButton.addEventListener('click', () => {
            window.location.href = 'php/logout.php';
        });
    }
});

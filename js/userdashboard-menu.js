document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.getElementById('hamburger');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    if (hamburger && dropdownMenu) {
        hamburger.addEventListener('click', (event) => {
            event.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });

        // Click anywhere outside to dismiss
        document.addEventListener('click', (event) => {
            if (!dropdownMenu.contains(event.target) && !hamburger.contains(event.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
    }
});

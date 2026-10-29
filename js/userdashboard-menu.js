const menuToggle = document.getElementById('hamburger');
const dropdownMenu = document.querySelector('.dropdown-menu');

menuToggle.addEventListener('click', () => {
    const isOpen = dropdownMenu.classList.toggle('show');
    menuToggle.setAttribute('aria-expanded', isOpen);
});

window.addEventListener('click', (event) => {
    if (!menuToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
        dropdownMenu.classList.remove('show');
        menuToggle.setAttribute('aria-expanded', 'false');
    }
});
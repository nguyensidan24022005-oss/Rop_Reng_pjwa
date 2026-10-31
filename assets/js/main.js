document.addEventListener('DOMContentLoaded', () => {

    const menuToggle = document.getElementById('menuToggle');
    const mainNavigation = document.getElementById('mainNavigation');

    if (menuToggle && mainNavigation) {

        menuToggle.addEventListener('click', () => {

            const isOpen = mainNavigation.classList.toggle('is-open');

            menuToggle.setAttribute('aria-expanded', isOpen);

        });

        const navLinks = mainNavigation.querySelectorAll('a');

        navLinks.forEach(link => {

            link.addEventListener('click', () => {

                mainNavigation.classList.remove('is-open');

                menuToggle.setAttribute('aria-expanded', 'false');

            });

        });

    }

    const currentYear = document.getElementById('currentYear');

    if (currentYear) {
        currentYear.textContent = new Date().getFullYear();
    }

});
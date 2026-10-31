
document.addEventListener('DOMContentLoaded', () => {

    // Mobile navigation
    const toggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.nav-links');

    if (toggle && nav) {

        toggle.addEventListener('click', () => {
            nav.classList.toggle('open');
        });

    }

});

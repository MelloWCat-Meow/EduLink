/**
 * Mobile navigation toggle. The menu is a plain list of links,
 * so it stays usable without JavaScript: it is shown by default
 * and only collapsed once this script runs.
 */
const toggle = document.querySelector('[data-menu-toggle]');
const menu = document.getElementById(toggle?.getAttribute('aria-controls') ?? '');

if (toggle && menu) {
    const setOpen = (isOpen) => {
        toggle.setAttribute('aria-expanded', String(isOpen));
        toggle.querySelector('[data-menu-label]').textContent = isOpen ? 'Tutup' : 'Menu';
        menu.hidden = !isOpen;
    };

    setOpen(false);

    toggle.addEventListener('click', () => {
        setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });

    menu.addEventListener('click', (event) => {
        if (event.target.closest('a')) {
            setOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
            setOpen(false);
            toggle.focus();
        }
    });
}

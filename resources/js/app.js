import './bootstrap';

const sidebar = document.getElementById('sidebar');
const backdrop = document.getElementById('sidebar-backdrop');
const toggle = document.getElementById('sidebar-toggle');
const shell = document.getElementById('app-shell');
const desktopNavigation = window.matchMedia('(min-width: 1024px)');

toggle?.setAttribute(
    'aria-expanded',
    String(desktopNavigation.matches && !sidebar?.classList.contains('lg:-translate-x-full')),
);

const closeSidebar = () => {
    sidebar?.classList.add('-translate-x-full');
    backdrop?.classList.add('hidden');
    toggle?.setAttribute('aria-expanded', 'false');
};

toggle?.addEventListener('click', () => {
    if (desktopNavigation.matches) {
        const collapsed = sidebar?.classList.contains('lg:-translate-x-full') ?? false;
        sidebar?.classList.toggle('lg:-translate-x-full', !collapsed);
        sidebar?.classList.toggle('lg:translate-x-0', collapsed);
        shell?.classList.toggle('lg:pl-0', !collapsed);
        shell?.classList.toggle('lg:pl-64', collapsed);
        toggle.setAttribute('aria-expanded', String(collapsed));
        return;
    }

    const opening = sidebar?.classList.contains('-translate-x-full') ?? false;
    sidebar?.classList.toggle('-translate-x-full', !opening);
    backdrop?.classList.toggle('hidden', !opening);
    toggle.setAttribute('aria-expanded', String(opening));
});

backdrop?.addEventListener('click', closeSidebar);
desktopNavigation.addEventListener('change', (event) => {
    toggle?.setAttribute(
        'aria-expanded',
        String(event.matches && !sidebar?.classList.contains('lg:-translate-x-full')),
    );
});
document.querySelectorAll('.dismiss-flash').forEach((button) => {
    button.addEventListener('click', () => button.parentElement?.remove());
});

document.querySelectorAll('.copy-token').forEach((button) => {
    button.addEventListener('click', async () => {
        const token = document.getElementById('terminal-token')?.textContent?.trim();
        if (!token) return;
        await navigator.clipboard.writeText(token);
        button.textContent = 'Copied';
    });
});

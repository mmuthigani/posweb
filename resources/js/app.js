import './bootstrap';

const sidebar = document.getElementById('sidebar');
const backdrop = document.getElementById('sidebar-backdrop');
const toggle = document.getElementById('sidebar-toggle');

const closeSidebar = () => {
    sidebar?.classList.add('-translate-x-full');
    backdrop?.classList.add('hidden');
};

toggle?.addEventListener('click', () => {
    sidebar?.classList.toggle('-translate-x-full');
    backdrop?.classList.toggle('hidden');
});

backdrop?.addEventListener('click', closeSidebar);
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

import './bootstrap';

const getPreferredTheme = () => {
    const savedTheme = window.localStorage.getItem('carnetpro-theme');

    if (savedTheme === 'light' || savedTheme === 'dark') {
        return savedTheme;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
};

const applyTheme = (theme) => {
    document.documentElement.setAttribute('data-theme', theme);
};

const syncThemeButtons = (theme) => {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('data-theme-current', theme);
        button.setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
        const label = button.querySelector('[data-theme-label]');

        if (label) {
            label.textContent = theme === 'dark' ? 'Light' : 'Dark';
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    const theme = getPreferredTheme();

    applyTheme(theme);
    syncThemeButtons(theme);

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const nextTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';

            window.localStorage.setItem('carnetpro-theme', nextTheme);
            applyTheme(nextTheme);
            syncThemeButtons(nextTheme);
        });
    });
});

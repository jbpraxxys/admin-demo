import defaultTheme from 'tailwindcss/defaultTheme'

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    yellow: '#F5C518',
                    'yellow-dark': '#D4A800',
                },
                surface: {
                    page: 'var(--surface-page)',
                    sidebar: 'var(--surface-sidebar)',
                    card: 'var(--surface-card)',
                    border: 'var(--surface-border)',
                    hover: 'var(--surface-hover)',
                },
                foreground: {
                    DEFAULT: 'var(--foreground)',
                    muted: 'var(--foreground-muted)',
                    subtle: 'var(--foreground-subtle)',
                    hint: 'var(--foreground-hint)',
                },
            },
        },
    },
    plugins: [],
}

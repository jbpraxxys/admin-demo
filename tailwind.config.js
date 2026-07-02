import defaultTheme from 'tailwindcss/defaultTheme'

/** @type {import('tailwindcss').Config} */
export default {
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
                    page: '#0D0D0D',
                    sidebar: '#111111',
                    card: '#1A1A1A',
                    border: '#2A2A2A',
                },
            },
        },
    },
    plugins: [],
}

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './app/Filament/**/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.ts',
        './resources/**/*.tsx',
        './vendor/filament/**/*.blade.php',
        './vendor/filament/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                background: '#FFFFFF',
                foreground: '#111827',
                primary: {
                    50: '#ecfdf5',
                    100: '#d1fae5',
                    200: '#a7f3d0',
                    300: '#6ee7b7',
                    400: '#34d399',
                    500: '#10b981',
                    600: '#059669',
                    700: '#047857',
                    800: '#065f46',
                    900: '#064e3b',
                    950: '#022c22',
                    foreground: '#FFFFFF',
                },
                'primary-foreground': '#FFFFFF',
                'light-green': '#E7F5E9',
                'ika-yellow': '#FFD700',
                'first-dark-green': '#006837',
                'second-dark-green': '#00522c',
                'oxygen-teal': '#00A69D',
                'oxygen-orange': '#FF7E00',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
}

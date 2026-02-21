import preset from './vendor/filament/support/tailwind.config.preset'
import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography'

/** @type {import('tailwindcss').Config} */
export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                'ika-orange': '#FF7E00',
                'ika-teal': '#00A69D', 
                'ika-yellow': '#e9cf35',
            }
        },
    },
    plugins: [
        forms,
        typography,
    ],
}

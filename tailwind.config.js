/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                maroon: {
                    DEFAULT: '#8B0000',
                    50: '#fdf1f1',
                    100: '#fbe1e1',
                    200: '#f3bcbc',
                    300: '#e88f8f',
                    400: '#d65a5a',
                    500: '#b52a2a',
                    600: '#8B0000',
                    700: '#750000',
                    800: '#5c0000',
                    900: '#450000',
                },
                navy: {
                    DEFAULT: '#0077bb',
                    50: '#e6f6ff',
                    100: '#ccedff',
                    200: '#99daff',
                    300: '#66c7ff',
                    400: '#33b5ff',
                    500: '#0077bb',
                    600: '#0077bb',
                    700: '#0077bb',
                    800: '#0077bb',
                    900: '#0077bb',
                },
                gold: {
                    DEFAULT: '#D4AF37',
                    50: '#fbf6e7',
                    100: '#f6ebc7',
                    200: '#ecd68c',
                    300: '#e2c159',
                    400: '#D4AF37',
                    500: '#b3901e',
                    600: '#8c7017',
                    700: '#655011',
                },
            },
            fontFamily: {
                devanagari: ['"Noto Sans Devanagari"', 'sans-serif'],
                sans: ['"Noto Sans"', '"Noto Sans Devanagari"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                heading: ['"Poppins"', '"Noto Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [require('@tailwindcss/typography')],
};

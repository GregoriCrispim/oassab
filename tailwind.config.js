import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                oassab: {
                    blue: '#1f2754',
                    'blue-dark': '#080f33',
                    orange: '#f14a16',
                    gray: '#4f5366',
                    cream: '#fbfbfb',
                    border: '#efefef',
                },
            },
            fontFamily: {
                sans: ['Jost', ...defaultTheme.fontFamily.sans],
                heading: ['Jost', ...defaultTheme.fontFamily.sans],
            },
            container: {
                center: true,
                padding: '1.25rem',
                screens: {
                    sm: '640px',
                    md: '768px',
                    lg: '1024px',
                    xl: '1140px',
                    '2xl': '1200px',
                },
            },
            keyframes: {
                fadeInUp: {
                    '0%': { opacity: '0', transform: 'translateY(20px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
            },
            animation: {
                'fade-in': 'fadeIn 0.8s ease-out both',
                'fade-in-up': 'fadeInUp 0.7s ease-out both',
            },
        },
    },
    plugins: [],
};

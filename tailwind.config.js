/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './app/**/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            boxShadow: {
                soft: '0 18px 45px rgba(15, 23, 42, 0.10)',
            },
        },
    },
    plugins: [],
};

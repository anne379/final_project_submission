/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ["./*.{html,js,php}", "./src/**/*.{html,js,php}", "./views/**/*.{html,js,php}", "./includes/**/*.{html,js,php}"],
    theme: {
        extend: {
            colors: {
                'brand-dark': '#0f172a',
                'brand-primary': '#3b82f6',
                'brand-accent': '#8b5cf6',
                'glass-white': 'rgba(255, 255, 255, 0.1)',
                'glass-border': 'rgba(255, 255, 255, 0.2)',
            },
            fontFamily: {
                'sans': ['Inter', 'sans-serif'],
            },
        },
    },
    plugins: [],
}

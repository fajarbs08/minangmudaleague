/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    prefix: 'tw-',
    corePlugins: {
        preflight: false,
    },
    theme: {
        extend: {
            colors: {
                lap: {
                    red: '#e41b23',
                    redDark: '#a70d1b',
                    ink: '#07090d',
                    panel: '#0f131b',
                    panelSoft: '#151b25',
                    line: '#1f2937',
                    cream: '#f4f5f7',
                },
            },
            boxShadow: {
                poster: '0 30px 80px rgba(6, 8, 14, 0.28)',
                panel: '0 20px 48px rgba(15, 23, 42, 0.12)',
            },
            fontFamily: {
                display: ['Inter', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [],
};

import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            // Font chữ
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                'headline-xl': ['Montserrat'],
                'headline-lg': ['Montserrat'],
                'headline-md': ['Montserrat'],
                'headline-sm': ['Montserrat'],
                'display-lg': ['Montserrat'],
                'label-lg': ['Montserrat'],
                'label-md': ['Montserrat'],
                'label-sm': ['Montserrat'],
                'body-lg': ['Montserrat'],
                'body-md': ['Montserrat'],
                'body-sm': ['Montserrat'],
            },

            // Màu sắc
            colors: {
                "on-tertiary-fixed": "#351000",
                "background": "#fbf9f5",
                "secondary": "#436651",
                "on-primary-fixed-variant": "#7e2b16",
                "on-primary": "#ffffff",
                "tertiary": "#ff6b00",
                "inverse-surface": "#30312e",
                "surface-container-low": "#f5f3ef",
                "on-surface": "#1b1c1a",
                "surface-tint": "#ff6b00",
                "on-primary-container": "#fffbff",
                "tertiary-fixed": "#ffebdb",
                "on-tertiary": "#ffffff",
                "on-surface-variant": "#56423d",
                "on-tertiary-container": "#fffbff",
                "on-secondary": "#ffffff",
                "secondary-container": "#c5ecd2",
                "surface": "#fbf9f5",
                "surface-dim": "#dbdad6",
                "surface-container-lowest": "#ffffff",
                "surface-container": "#efeeea",
                "inverse-primary": "#ffb4a2",
                "primary-fixed-dim": "#ffb4a2",
                "tertiary-container": "#ff6b00",
                "error-container": "#ffdad6",
                "inverse-on-surface": "#f2f0ed",
                "primary-container": "#ff6b00",
                "surface-variant": "#e4e2de",
                "error": "#ba1a1a",
                "secondary-fixed-dim": "#a9cfb7",
                "on-secondary-fixed-variant": "#2c4e3b",
                "on-primary-fixed": "#3c0800",
                "secondary-fixed": "#c5ecd2",
                "primary-fixed": "#ffdbd2",
                "surface-container-highest": "#e4e2de",
                "on-error": "#ffffff",
                "tertiary-fixed-dim": "#ffb693",
                "on-secondary-container": "#496c57",
                "on-secondary-fixed": "#002112",
                "outline-variant": "#dcc0ba",
                "outline": "#89726c",
                "on-error-container": "#93000a",
                "primary": "#ff6b00",
                "surface-bright": "#fbf9f5",
                "on-tertiary-fixed-variant": "#7a3000",
                "surface-container-high": "#eae8e4",
                "on-background": "#1b1c1a",
                "gray-warm": "#E9E9E9",
                "charcoal-deep": "#121212",
                "success-green": "#2D6A4F"
            },

            // Border radius
            borderRadius: {
                DEFAULT: "0.25rem",
                lg: "1rem",
                xl: "1.5rem",
                full: "9999px",
            },

            // Khoảng cách cơ bản (dùng cho margin, padding, width, height...)
            spacing: {
                xl: "80px",
                "margin-desktop": "64px",
                base: "8px",
                gutter: "24px",
                "margin-mobile": "16px",
                lg: "48px",
                xs: "4px",
                sm: "12px",
                md: "24px",
                "stack-sm": "8px",
                "stack-md": "16px",
                "stack-lg": "32px",
                "section-gap": "80px",
                "container-max-width": "1440px",
            },

            // Tùy chỉnh gap (cho grid, flex)
            gap: ({ theme }) => ({
                ...theme('spacing'),
                'stack-sm': '8px',
                'stack-md': '16px',
                'stack-lg': '32px',
                'gutter': '24px',
                'lg': '2rem',
            }),

            // Tùy chỉnh padding (cho các class p-*, pt-*, pb-*, px-*, py-*)
            padding: ({ theme }) => ({
                ...theme('spacing'),
                'stack-sm': '8px',
                'stack-md': '16px',
                'stack-lg': '32px',
            }),

            // Tùy chỉnh margin (cho các class m-*, mt-*, mb-*, ml-*, mr-*, mx-*, my-*)
            margin: ({ theme }) => ({
                ...theme('spacing'),
                'stack-sm': '8px',
                'stack-md': '16px',
                'stack-lg': '32px',
            }),

            // Tùy chỉnh space (cho space-y-*, space-x-*)
            space: ({ theme }) => ({
                'stack-sm': '8px',
                'stack-md': '16px',
                'stack-lg': '32px',
            }),

            // Font size
            fontSize: {
                "display-lg": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "700" }],
                "headline-xl": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "700" }],
                "headline-lg": ["32px", { lineHeight: "40px", fontWeight: "700" }],
                "headline-md": ["24px", { lineHeight: "32px", fontWeight: "600" }],
                "headline-sm": ["20px", { lineHeight: "28px", fontWeight: "600" }],
                "label-lg": ["14px", { lineHeight: "20px", letterSpacing: "0.05em", fontWeight: "600" }],
                "label-md": ["14px", { lineHeight: "20px", letterSpacing: "0.05em", fontWeight: "600" }],
                "label-sm": ["12px", { lineHeight: "16px", fontWeight: "500" }],
                "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }],
                "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }],
                "body-sm": ["14px", { lineHeight: "20px", fontWeight: "400" }]
            }
        },
    },

    plugins: [forms],
};
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
                sans: ['Montserrat', 'Figtree', ...defaultTheme.fontFamily.sans],
                montserrat: ['Montserrat', ...defaultTheme.fontFamily.sans],
                figtree: ['Figtree', ...defaultTheme.fontFamily.sans],
            },

            // Màu sắc
            colors: {
                "primary": "#ff6b00",
                "secondary": "#436651",
                "tertiary": "#ff6b00",
                "warning": "#f59e0b",
                "success": "#2d6a4f",
                "error": "#ba1a1a",
                
                "background": "#fbf9f5",
                "surface": "#fbf9f5",
                "surface-white": "#ffffff",
                
                "on-surface": "#1b1c1a",
                "on-surface-variant": "#56423d",
                "outline": "#89726c",
                
                "border-light": "#e4e2de",
                
                "hover-bg": "#fff5f2",
                "hover-text": "#ff6b00",
                
                // Giữ lại các màu phụ trợ
                "on-tertiary-fixed": "#351000",
                "on-primary-fixed-variant": "#7e2b16",
                "on-primary": "#ffffff",
                "inverse-surface": "#30312e",
                "surface-container-low": "#f5f3ef",
                "surface-tint": "#ff6b00",
                "on-primary-container": "#fffbff",
                "tertiary-fixed": "#ffebdb",
                "on-tertiary": "#ffffff",
                "on-tertiary-container": "#fffbff",
                "on-secondary": "#ffffff",
                "secondary-container": "#c5ecd2",
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
                "on-error-container": "#93000a",
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
                sm: "0.5rem",
                md: "0.75rem",
                lg: "1rem",
                xl: "1.5rem",
                "2xl": "2rem",
                full: "9999px",
            },

            // Khoảng cách
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
                "body-sm": ["14px", { lineHeight: "20px", fontWeight: "400" }],
                
                'xs': ['12px', { lineHeight: '16px' }],
                'sm': ['14px', { lineHeight: '20px' }],
                'base': ['16px', { lineHeight: '24px' }],
                'lg': ['18px', { lineHeight: '28px' }],
                'xl': ['20px', { lineHeight: '28px' }],
                '2xl': ['24px', { lineHeight: '32px' }],
                '3xl': ['30px', { lineHeight: '36px' }],
                '4xl': ['36px', { lineHeight: '40px' }],
                '5xl': ['48px', { lineHeight: '1' }],
            },

            // Box shadow
            boxShadow: {
                'sm': '0 1px 2px 0 rgb(0 0 0 / 0.05)',
                'DEFAULT': '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
                'md': '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
                'lg': '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
                'xl': '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)',
                'card': '0 1px 3px rgba(0,0,0,0.1)',
                'card-hover': '0 10px 25px -5px rgba(0,0,0,0.1)',
            },

            // Animation
            keyframes: {
                'fade-in': {
                    '0%': { opacity: '0', transform: 'translateY(10px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'slide-in': {
                    '0%': { transform: 'translateX(-100%)' },
                    '100%': { transform: 'translateX(0)' },
                },
                'spin': {
                    '0%': { transform: 'rotate(0deg)' },
                    '100%': { transform: 'rotate(360deg)' },
                },
            },

            animation: {
                'fade-in': 'fade-in 0.3s ease-out',
                'slide-in': 'slide-in 0.3s ease-out',
                'spin': 'spin 1s linear infinite',
            },

            // Backdrop blur
            backdropBlur: {
                'sm': '4px',
                'DEFAULT': '8px',
                'md': '12px',
                'lg': '16px',
            },

            // Width
            width: {
                'sidebar': '18rem',
                'sidebar-collapsed': '5rem',
            },

            // Max width
            maxWidth: {
                'container': '1440px',
                '7xl': '80rem',
                '8xl': '90rem',
            },
        },
    },

    plugins: [forms],
};
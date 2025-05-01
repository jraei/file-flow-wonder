const { fontFamily } = require("tailwindcss/defaultTheme");
const dynamicColors = require("./resources/js/tailwind.dynamic.colors.js");

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: dynamicColors.primary.DEFAULT,
                    hover: dynamicColors.primary.hover,
                    text: dynamicColors.primary.text,
                },
                secondary: {
                    DEFAULT: dynamicColors.secondary.DEFAULT,
                    hover: dynamicColors.secondary.hover,
                    text: dynamicColors.secondary.text,
                },
                dark: {
                    DEFAULT: dynamicColors.dark.DEFAULT,
                    lighter: dynamicColors.dark.lighter,
                    card: dynamicColors.dark.card,
                    sidebar: dynamicColors.dark.sidebar,
                },
                header_background: dynamicColors.header_background,
                footer_background: dynamicColors.footer_background,
                content_background: dynamicColors.content_background,
                primary_text: "#FFFFFF",
            },
            boxShadow: {
                "glow-primary": "0 0 15px rgba(155, 135, 245, 0.5)",
                "glow-secondary": "0 0 15px rgba(51, 195, 240, 0.5)",
            },
            animation: {
                "pulse-slow": "pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite",
                "ping-small": "ping 2s cubic-bezier(0, 0, 0.2, 1) infinite",
                "spin-slow": "spin 3s linear infinite",
                "bounce-slow": "bounce 3s infinite",
                float: "float 6s ease-in-out infinite",
                orbit: "orbit 10s linear infinite",
            },
            keyframes: {
                float: {
                    "0%, 100%": { transform: "translateY(0)" },
                    "50%": { transform: "translateY(-10px)" },
                },
                orbit: {
                    "0%": {
                        transform: "rotate(0deg) translateX(10px) rotate(0deg)",
                    },
                    "100%": {
                        transform:
                            "rotate(360deg) translateX(10px) rotate(-360deg)",
                    },
                },
            },
        },
    },

    plugins: [require("@tailwindcss/forms")],
};

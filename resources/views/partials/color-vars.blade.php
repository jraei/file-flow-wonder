
<style>
:root {
    /* Primary Colors */
    --color-primary: {{ \App\Models\WebConfig::get('primary_color', '#9b87f5') }};
    --color-primary-rgb: {{ \App\Models\WebConfig::hexToRgb(App\Models\WebConfig::get('primary_color', '#9b87f5')) }};
    --color-primary-hover: {{ \App\Models\WebConfig::get('primary_hover', '#818CF8') }};
    --color-primary-hover-rgb: {{ \App\Models\WebConfig::hexToRgb(App\Models\WebConfig::get('primary_hover', '#818CF8')) }};

    /* Secondary Colors */
    --color-secondary: {{ \App\Models\WebConfig::get('secondary_color', '#33C3F0') }};
    --color-secondary-rgb: {{ \App\Models\WebConfig::hexToRgb(App\Models\WebConfig::get('secondary_color', '#33C3F0')) }};
    --color-secondary-hover: {{ \App\Models\WebConfig::get('secondary_hover', '#A78BFA') }};
    --color-secondary-hover-rgb: {{ \App\Models\WebConfig::hexToRgb(\App\Models\WebConfig::get('secondary_hover', '#A78BFA')) }};

    /* Text Colors */
    --color-text-primary: {{ \App\Models\WebConfig::get('text_primary', '#F9FAFB') }};
    --color-text-secondary: {{ \App\Models\WebConfig::get('text_secondary', '#E5E7EB') }};

    /* Background Colors */
    --color-header-bg: {{ \App\Models\WebConfig::get('header_bg', '#111827') }};
    --color-footer-bg: {{ \App\Models\WebConfig::get('footer_bg', '#111827') }};
    --color-content-bg: {{ \App\Models\WebConfig::get('content_bg', '#1F2937') }};
}
</style>

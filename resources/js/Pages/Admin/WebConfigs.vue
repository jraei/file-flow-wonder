<script setup>
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { ref, computed, watch } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    generalSettings: Object,
    appearanceSettings: Object,
    providers: Array,
    errors: Object,
});

const page = usePage();

const activeTab = ref("general");

const tabs = [
    { id: "general", name: "General Settings", icon: "cog" },
    { id: "appearance", name: "Appearance", icon: "palette" },
    { id: "api", name: "API Connections", icon: "link" },
    { id: "security", name: "Security", icon: "shield" },
];

const setActiveTab = (tabId) => {
    activeTab.value = tabId;
};

// General settings form
const generalForm = useForm({
    judul_web: props.generalSettings?.judul_web || "",
    meta_title: props.generalSettings?.meta_title || "",
    meta_description: props.generalSettings?.meta_description || "",
    meta_keywords: props.generalSettings?.meta_keywords || "",
    support_instagram: props.generalSettings?.support_instagram || "",
    support_whatsapp: props.generalSettings?.support_whatsapp || "",
    support_email: props.generalSettings?.support_email || "",
    support_youtube: props.generalSettings?.support_youtube || "",
    support_facebook: props.generalSettings?.support_facebook || "",
});

// Appearance form
const appearanceForm = useForm({
    primary_color: props.appearanceSettings?.primary_color || "#6366F1",
    primary_hover: props.appearanceSettings?.primary_hover || "#4F46E5",
    secondary_color: props.appearanceSettings?.secondary_color || "#8B5CF6",
    secondary_hover: props.appearanceSettings?.secondary_hover || "#7C3AED",
    content_bg: props.appearanceSettings?.content_bg || "#1F2937",
    footer_bg: props.appearanceSettings?.footer_bg || "#111827",
    header_bg: props.appearanceSettings?.header_bg || "#111827",
    text_primary: props.appearanceSettings?.text_primary || "#F9FAFB",
    text_secondary: props.appearanceSettings?.text_secondary || "#E5E7EB",
    logo_header: null,
    logo_footer: null,
    logo_favicon: null,
});

// API connections form
const apiForm = useForm({
    providers: props.providers.reduce((acc, provider) => {
        acc[provider.id] = {
            id: provider.id,
            api_username: provider.api_username || "",
            api_key: provider.api_key || "",
            api_private_key: provider.api_private_key || "",
            balance: provider.balance || 0,
            status: provider.status || "active",
            provider_name: provider.provider_name,
        };
        return acc;
    }, {}),
});

// Get balance provider
const getBalanceProvider = (providerId) => {
    router.post(route("providers.getBalancesByProvider", providerId), {
        preserveScroll: true,
    });
};

watch(
    () => page.props.flash.status,
    (status) => {
        if (status?.provider_id && status?.status) {
            const providerId = status.provider_id;
            if (status?.balance) {
                apiForm.providers[providerId].balance = status.balance;
            }
            apiForm.providers[providerId].status = status.status;
        }
    }
);

// Security form
const securityForm = useForm({
    enable_2fa: false,
    ip_restriction: false,
    enforce_complex_password: true,
    password_expiry_days: 90,
});

// Character counters for SEO fields
const metaTitleCount = computed(() => generalForm.meta_title.length);
const metaDescriptionCount = computed(
    () => generalForm.meta_description.length
);

// Form submission handlers
const submitGeneralSettings = () => {
    generalForm.patch(route("admin.settings.general"), {
        preserveScroll: true,
    });
};

const submitAppearanceSettings = () => {
    appearanceForm._method = "patch";

    router.post(route("admin.settings.appearance"), appearanceForm, {
        preserveScroll: true,
    });
};

const submitApiSettings = () => {
    apiForm.patch(route("admin.settings.api"), {
        preserveScroll: true,
    });
};

const submitSecuritySettings = () => {
    // Placeholder for security settings submission
    showToast("Success", "Security settings updated successfully", "success");
};

const imagePreviews = ref({
    logo_header: props.appearanceSettings?.logo_header ?? null,
    logo_footer: props.appearanceSettings?.logo_footer ?? null,
    logo_favicon: props.appearanceSettings?.logo_favicon ?? null,
});

// File handling
const getImagePreview = (field) => {
    return computed(() => {
        if (
            typeof appearanceForm[field] === "string" &&
            appearanceForm[field]
        ) {
            return `/storage/${appearanceForm[field]}`;
        } else if (imagePreviews.value[field]) {
            return imagePreviews.value[field];
        }
        return null;
    });
};

const handleLogoUpload = (event, field) => {
    const file = event.target.files[0];
    if (file) {
        appearanceForm[field] = file;
        imagePreviews.value[field] = URL.createObjectURL(file);
    }
};

// Visual color preview elements
const previewElements = ref([
    {
        name: "Header",
        color: "header_bg",
        primaryColor: "text_primary",
        secondaryColor: "text_secondary",
    },
    {
        name: "Content",
        color: "content_bg",
        primaryColor: "text_primary",
        secondaryColor: "text_secondary",
    },
    {
        name: "Button",
        color: "primary_color",
        primaryColor: "text_primary",
        secondaryColor: "text_secondary",
    },
    {
        name: "Secondary",
        color: "secondary_color",
        primaryColor: "text_primary",
        secondaryColor: "text_secondary",
    },
    {
        name: "Footer",
        color: "footer_bg",
        primaryColor: "text_primary",
        secondaryColor: "text_secondary",
    },
]);

// Masking/unmasking API keys
const maskedFields = ref({});

const toggleFieldVisibility = (providerId, field) => {
    const key = `${providerId}-${field}`;
    maskedFields.value[key] = !maskedFields.value[key];
};

const isFieldVisible = (providerId, field) => {
    const key = `${providerId}-${field}`;
    return maskedFields.value[key] || false;
};

// Toast notification
const showToast = (title, message, type = "success") => {
    // This would be connected to your existing notification system
    if (window.Toast) {
        window.Toast.fire({
            icon: type,
            title: message,
        });
    } else {
        alert(`${title}: ${message}`);
    }
};

// WCAG contrast checker (simplified)
const getContrastRatio = (color1, color2) => {
    // Convert hex to RGB and calculate luminance
    // Simplified version - would need proper luminance calculation for accuracy
    return "Subtext"; // Placeholder
};

const deleteLogo = (field) => {
    if (!confirm("Are you sure you want to delete this logo?")) return;

    router.delete(route("admin.settings.logo.delete", field), {
        preserveScroll: true,

        onSuccess: () => {
            imagePreviews.value[field] = null;
            appearanceForm[field] = null;
        },

        onError: () => {
            showToast("Error", "Failed to delete logo.", "error");
        },
    });
};
</script>

<template>
    <Head title="Settings | Admin" />

    <AdminLayout title="System Settings">
        <div
            class="w-full border rounded-lg shadow-lg border-primary/40 bg-dark-card"
        >
            <!-- Tab Navigation -->
            <div class="border-b border-gray-800">
                <div class="flex overflow-x-auto">
                    <nav class="flex" aria-label="Tabs">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="setActiveTab(tab.id)"
                            class="px-6 py-4 text-sm font-medium transition-all duration-300 border-b-2 whitespace-nowrap group"
                            :class="[
                                activeTab === tab.id
                                    ? 'border-transparent text-primary'
                                    : 'border-transparent text-gray-500 hover:text-gray-300 hover:border-gray-700',
                            ]"
                            :aria-current="
                                activeTab === tab.id ? 'page' : undefined
                            "
                        >
                            <!-- Icon would go here if using an icon library -->
                            <span class="relative">
                                {{ tab.name }}
                                <span
                                    v-if="activeTab === tab.id"
                                    class="absolute inset-x-0 -bottom-2 h-0.5 bg-secondary animate-pulse"
                                ></span>
                            </span>
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Tab Content with Glassmorphism styling -->
            <div class="p-6 backdrop-blur-sm bg-gray-900/70">
                <!-- General Settings Tab -->
                <div
                    v-if="activeTab === 'general'"
                    class="space-y-8 transition-all duration-300 animate-fade-in"
                >
                    <div
                        class="p-6 border border-gray-700 rounded-lg shadow-lg bg-gray-800/70 backdrop-blur-sm"
                    >
                        <h3 class="mb-4 text-lg font-medium text-white">
                            Website Information
                        </h3>
                        <form @submit.prevent="submitGeneralSettings">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <InputLabel
                                        for="judul_web"
                                        value="Site Name"
                                    />
                                    <TextInput
                                        id="judul_web"
                                        name="judul_web"
                                        type="text"
                                        class="block w-full mt-1 text-white border-gray-600 bg-gray-700/70"
                                        placeholder="Game Top-Up Website"
                                        v-model="generalForm.judul_web"
                                    />
                                    <InputError
                                        :message="generalForm.errors.judul_web"
                                        class="mt-1"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <InputLabel
                                        for="support_instagram"
                                        value="Instagram URL"
                                    />
                                    <TextInput
                                        id="support_instagram"
                                        name="support_instagram"
                                        type="text"
                                        class="block w-full mt-1 text-white border-gray-600 bg-gray-700/70"
                                        placeholder="https://instagram.com/your-account"
                                        v-model="generalForm.support_instagram"
                                    />
                                    <InputError
                                        :message="
                                            generalForm.errors.support_instagram
                                        "
                                        class="mt-1"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <InputLabel
                                        for="support_email"
                                        value="Support Email"
                                    />
                                    <TextInput
                                        id="support_email"
                                        name="support_email"
                                        type="email"
                                        class="block w-full mt-1 text-white border-gray-600 bg-gray-700/70"
                                        placeholder="support@yourdomain.com"
                                        v-model="generalForm.support_email"
                                    />
                                    <InputError
                                        :message="
                                            generalForm.errors.support_email
                                        "
                                        class="mt-1"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <InputLabel
                                        for="support_whatsapp"
                                        value="WhatsApp Number"
                                    />
                                    <TextInput
                                        id="support_whatsapp"
                                        name="support_whatsapp"
                                        type="text"
                                        class="block w-full mt-1 text-white border-gray-600 bg-gray-700/70"
                                        placeholder="6281234567890 (no spaces or dashes)"
                                        v-model="generalForm.support_whatsapp"
                                    />
                                    <InputError
                                        :message="
                                            generalForm.errors.support_whatsapp
                                        "
                                        class="mt-1"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <InputLabel
                                        for="support_facebook"
                                        value="Facebook URL"
                                    />
                                    <TextInput
                                        id="support_facebook"
                                        name="support_facebook"
                                        type="text"
                                        class="block w-full mt-1 text-white border-gray-600 bg-gray-700/70"
                                        placeholder="https://facebook.com/your-page"
                                        v-model="generalForm.support_facebook"
                                    />
                                    <InputError
                                        :message="
                                            generalForm.errors.support_facebook
                                        "
                                        class="mt-1"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <InputLabel
                                        for="support_youtube"
                                        value="YouTube URL"
                                    />
                                    <TextInput
                                        id="support_youtube"
                                        name="support_youtube"
                                        type="text"
                                        class="block w-full mt-1 text-white border-gray-600 bg-gray-700/70"
                                        placeholder="https://youtube.com/your-channel"
                                        v-model="generalForm.support_youtube"
                                    />
                                    <InputError
                                        :message="
                                            generalForm.errors.support_youtube
                                        "
                                        class="mt-1"
                                    />
                                </div>
                            </div>

                            <div
                                class="p-6 mt-8 border border-gray-700 rounded-lg bg-gray-800/70"
                            >
                                <h3 class="mb-4 text-lg font-medium text-white">
                                    SEO Settings
                                </h3>
                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <InputLabel
                                                for="meta_title"
                                                value="Meta Title"
                                            />
                                            <span
                                                :class="{
                                                    'text-red-500':
                                                        metaTitleCount > 60,
                                                    'text-gray-400':
                                                        metaTitleCount <= 60,
                                                }"
                                                class="text-sm"
                                            >
                                                {{ metaTitleCount }}/60
                                            </span>
                                        </div>
                                        <TextInput
                                            id="meta_title"
                                            type="text"
                                            class="block w-full mt-1 text-white border-gray-600 bg-gray-700/70"
                                            placeholder="Game Top-Up | Fast & Secure"
                                            v-model="generalForm.meta_title"
                                            maxlength="60"
                                        />
                                        <InputError
                                            :message="
                                                generalForm.errors.meta_title
                                            "
                                            class="mt-1"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <InputLabel
                                                for="meta_description"
                                                value="Meta Description"
                                            />
                                            <span
                                                :class="{
                                                    'text-red-500':
                                                        metaDescriptionCount >
                                                        160,
                                                    'text-gray-400':
                                                        metaDescriptionCount <=
                                                        160,
                                                }"
                                                class="text-sm"
                                            >
                                                {{ metaDescriptionCount }}/160
                                            </span>
                                        </div>
                                        <textarea
                                            id="meta_description"
                                            class="block w-full mt-1 text-white border-gray-600 rounded-md shadow-sm bg-gray-700/70"
                                            rows="3"
                                            placeholder="The best place to buy game credits and top-ups at affordable prices."
                                            v-model="
                                                generalForm.meta_description
                                            "
                                            maxlength="160"
                                        ></textarea>
                                        <InputError
                                            :message="
                                                generalForm.errors
                                                    .meta_description
                                            "
                                            class="mt-1"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <InputLabel
                                            for="meta_keywords"
                                            value="Meta Keywords"
                                        />
                                        <TextInput
                                            id="meta_keywords"
                                            type="text"
                                            class="block w-full mt-1 text-white border-gray-600 bg-gray-700/70"
                                            placeholder="game top-up, mobile legends, free fire, pubg mobile"
                                            v-model="generalForm.meta_keywords"
                                        />
                                        <InputError
                                            :message="
                                                generalForm.errors.meta_keywords
                                            "
                                            class="mt-1"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-6">
                                <PrimaryButton
                                    type="submit"
                                    :disabled="generalForm.processing"
                                    class="transition-colors duration-300 bg-indigo-600 hover:bg-indigo-700"
                                >
                                    <span v-if="generalForm.processing"
                                        >Saving...</span
                                    >
                                    <span v-else>Save General Settings</span>
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Appearance Tab -->
                <div
                    v-if="activeTab === 'appearance'"
                    class="space-y-8 transition-all duration-300 animate-fade-in"
                >
                    <div
                        v-if="Object.keys(errors).length > 0"
                        class="px-4 py-3 mb-4 text-sm text-white rounded-lg bg-red-500/80"
                    >
                        <ul v-for="error in errors">
                            <li>{{ error }}</li>
                        </ul>
                    </div>
                    <div
                        class="p-6 border border-gray-700 rounded-lg shadow-lg bg-gray-800/70 backdrop-blur-sm"
                    >
                        <h3 class="mb-4 text-lg font-medium text-white">
                            Color Management
                        </h3>
                        <form @submit.prevent="submitAppearanceSettings">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                <div class="space-y-2">
                                    <InputLabel
                                        for="primary_color"
                                        value="Primary Color"
                                    />
                                    <div class="flex items-center mt-1">
                                        <input
                                            id="primary_color"
                                            type="color"
                                            class="w-10 h-10 bg-transparent border-gray-600 rounded cursor-pointer"
                                            v-model="
                                                appearanceForm.primary_color
                                            "
                                        />
                                        <TextInput
                                            type="text"
                                            class="block w-full ml-2 text-white border-gray-600 bg-gray-700/70"
                                            placeholder="#6366F1"
                                            v-model="
                                                appearanceForm.primary_color
                                            "
                                        />
                                    </div>
                                    <InputError
                                        :message="
                                            appearanceForm.errors.primary_color
                                        "
                                        class="mt-1"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <InputLabel
                                        for="primary_hover"
                                        value="Primary Hover"
                                    />
                                    <div class="flex items-center mt-1">
                                        <input
                                            id="primary_hover"
                                            type="color"
                                            class="w-10 h-10 bg-transparent border-gray-600 rounded cursor-pointer"
                                            v-model="
                                                appearanceForm.primary_hover
                                            "
                                        />
                                        <TextInput
                                            type="text"
                                            class="block w-full ml-2 text-white border-gray-600 bg-gray-700/70"
                                            placeholder="#6366F1"
                                            v-model="
                                                appearanceForm.primary_hover
                                            "
                                        />
                                    </div>
                                    <InputError
                                        :message="
                                            appearanceForm.errors.primary_hover
                                        "
                                        class="mt-1"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <InputLabel
                                        for="secondary_color"
                                        value="Secondary Color"
                                    />
                                    <div class="flex items-center mt-1">
                                        <input
                                            id="secondary_color"
                                            type="color"
                                            class="w-10 h-10 bg-transparent border-gray-600 rounded cursor-pointer"
                                            v-model="
                                                appearanceForm.secondary_color
                                            "
                                        />
                                        <TextInput
                                            type="text"
                                            class="block w-full ml-2 text-white border-gray-600 bg-gray-700/70"
                                            placeholder="#8B5CF6"
                                            v-model="
                                                appearanceForm.secondary_color
                                            "
                                        />
                                    </div>
                                    <InputError
                                        :message="
                                            appearanceForm.errors
                                                .secondary_color
                                        "
                                        class="mt-1"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <InputLabel
                                        for="secondary_hover"
                                        value="Secondary Hover"
                                    />
                                    <div class="flex items-center mt-1">
                                        <input
                                            id="secondary_hover"
                                            type="color"
                                            class="w-10 h-10 bg-transparent border-gray-600 rounded cursor-pointer"
                                            v-model="
                                                appearanceForm.secondary_hover
                                            "
                                        />
                                        <TextInput
                                            type="text"
                                            class="block w-full ml-2 text-white border-gray-600 bg-gray-700/70"
                                            placeholder="#E5E7EB"
                                            v-model="
                                                appearanceForm.secondary_hover
                                            "
                                        />
                                    </div>
                                    <InputError
                                        :message="
                                            appearanceForm.errors
                                                .secondary_hover
                                        "
                                        class="mt-1"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <InputLabel
                                        for="content_bg"
                                        value="Content Background"
                                    />
                                    <div class="flex items-center mt-1">
                                        <input
                                            id="content_bg"
                                            type="color"
                                            class="w-10 h-10 bg-transparent border-gray-600 rounded cursor-pointer"
                                            v-model="appearanceForm.content_bg"
                                        />
                                        <TextInput
                                            type="text"
                                            class="block w-full ml-2 text-white border-gray-600 bg-gray-700/70"
                                            placeholder="#1F2937"
                                            v-model="appearanceForm.content_bg"
                                        />
                                    </div>
                                    <InputError
                                        :message="
                                            appearanceForm.errors.content_bg
                                        "
                                        class="mt-1"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <InputLabel
                                        for="header_bg"
                                        value="Header Background"
                                    />
                                    <div class="flex items-center mt-1">
                                        <input
                                            id="header_bg"
                                            type="color"
                                            class="w-10 h-10 bg-transparent border-gray-600 rounded cursor-pointer"
                                            v-model="appearanceForm.header_bg"
                                        />
                                        <TextInput
                                            type="text"
                                            class="block w-full ml-2 text-white border-gray-600 bg-gray-700/70"
                                            placeholder="#111827"
                                            v-model="appearanceForm.header_bg"
                                        />
                                    </div>
                                    <InputError
                                        :message="
                                            appearanceForm.errors.header_bg
                                        "
                                        class="mt-1"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <InputLabel
                                        for="footer_bg"
                                        value="Footer Background"
                                    />
                                    <div class="flex items-center mt-1">
                                        <input
                                            id="footer_bg"
                                            type="color"
                                            class="w-10 h-10 bg-transparent border-gray-600 rounded cursor-pointer"
                                            v-model="appearanceForm.footer_bg"
                                        />
                                        <TextInput
                                            type="text"
                                            class="block w-full ml-2 text-white border-gray-600 bg-gray-700/70"
                                            placeholder="#111827"
                                            v-model="appearanceForm.footer_bg"
                                        />
                                    </div>
                                    <InputError
                                        :message="
                                            appearanceForm.errors.footer_bg
                                        "
                                        class="mt-1"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <InputLabel
                                        for="text_primary"
                                        value="Primary Text"
                                    />
                                    <div class="flex items-center mt-1">
                                        <input
                                            id="text_primary"
                                            type="color"
                                            class="w-10 h-10 bg-transparent border-gray-600 rounded cursor-pointer"
                                            v-model="
                                                appearanceForm.text_primary
                                            "
                                        />
                                        <TextInput
                                            type="text"
                                            class="block w-full ml-2 text-white border-gray-600 bg-gray-700/70"
                                            placeholder="#F9FAFB"
                                            v-model="
                                                appearanceForm.text_primary
                                            "
                                        />
                                    </div>
                                    <InputError
                                        :message="
                                            appearanceForm.errors.text_primary
                                        "
                                        class="mt-1"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <InputLabel
                                        for="text_secondary"
                                        value="Secondary Text"
                                    />
                                    <div class="flex items-center mt-1">
                                        <input
                                            id="text_secondary"
                                            type="color"
                                            class="w-10 h-10 bg-transparent border-gray-600 rounded cursor-pointer"
                                            v-model="
                                                appearanceForm.text_secondary
                                            "
                                        />
                                        <TextInput
                                            type="text"
                                            class="block w-full ml-2 text-white border-gray-600 bg-gray-700/70"
                                            placeholder="#E5E7EB"
                                            v-model="
                                                appearanceForm.text_secondary
                                            "
                                        />
                                    </div>
                                    <InputError
                                        :message="
                                            appearanceForm.errors.text_secondary
                                        "
                                        class="mt-1"
                                    />
                                </div>
                            </div>

                            <!-- Color Preview Panel -->
                            <div
                                class="p-6 mt-8 border border-gray-700 rounded-lg bg-gray-800/70"
                            >
                                <h4 class="mb-4 font-medium text-white text-md">
                                    Live Preview
                                </h4>
                                <div class="grid grid-cols-3 gap-4">
                                    <div
                                        v-for="element in previewElements"
                                        :key="element.name"
                                        class="p-4 text-center transition-all duration-300 rounded-md"
                                        :style="{
                                            backgroundColor:
                                                appearanceForm[element.color],
                                            color: appearanceForm[
                                                element.primaryColor
                                            ],
                                        }"
                                    >
                                        {{ element.name }}
                                        <div
                                            class="mt-1 text-xs"
                                            :style="{
                                                color: appearanceForm[
                                                    element.secondaryColor
                                                ],
                                            }"
                                        >
                                            {{
                                                getContrastRatio(
                                                    appearanceForm[
                                                        element.color
                                                    ],
                                                    appearanceForm[
                                                        element.textColor
                                                    ]
                                                )
                                            }}
                                        </div>
                                    </div>
                                    <div
                                        class="px-4 py-1 text-center transition-all duration-300 rounded-md"
                                        :style="{
                                            backgroundColor:
                                                appearanceForm.primary_hover,
                                            color: appearanceForm.text_primary,
                                        }"
                                    >
                                        <div
                                            class="py-2 my-2 text-xs rounded-md"
                                            :style="{
                                                color: appearanceForm.text_secondary,
                                                backgroundColor:
                                                    appearanceForm.secondary_hover,
                                            }"
                                        >
                                            <p
                                                :style="{
                                                    color: appearanceForm.text_primary,
                                                }"
                                                class="text-base"
                                            >
                                                Hover
                                            </p>
                                            Subtext
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="p-6 mt-8 border border-gray-700 rounded-lg bg-gray-800/70"
                            >
                                <h3 class="mb-4 text-lg font-medium text-white">
                                    Logo Settings
                                </h3>
                                <div
                                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                                >
                                    <div class="space-y-2">
                                        <InputLabel
                                            for="logo_header"
                                            value="Header Logo"
                                        />
                                        <div
                                            class="flex justify-center px-6 pt-5 pb-6 mt-1 transition-colors border-2 border-gray-600 border-dashed rounded-md hover:border-indigo-400"
                                        >
                                            <div
                                                v-if="
                                                    getImagePreview(
                                                        'logo_header'
                                                    ).value
                                                "
                                                class="relative mb-2"
                                            >
                                                <img
                                                    :src="
                                                        getImagePreview(
                                                            'logo_header'
                                                        ).value
                                                    "
                                                    alt="Preview Logo Header"
                                                    class="object-cover w-full rounded-lg shadow-md"
                                                />
                                                <button
                                                    type="button"
                                                    @click="
                                                        deleteLogo(
                                                            'logo_header'
                                                        )
                                                    "
                                                    class="absolute z-10 p-1 text-white transition bg-red-600 rounded-full -right-2 -top-2 hover:bg-red-700"
                                                    title="Hapus Logo"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="w-4 h-4"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M6 18L18 6M6 6l12 12"
                                                        />
                                                    </svg>
                                                </button>
                                            </div>

                                            <div
                                                class="space-y-1 text-center"
                                                v-if="
                                                    !getImagePreview(
                                                        'logo_header'
                                                    ).value
                                                "
                                            >
                                                <svg
                                                    class="w-12 h-12 mx-auto text-gray-400"
                                                    stroke="currentColor"
                                                    fill="none"
                                                    viewBox="0 0 48 48"
                                                    aria-hidden="true"
                                                >
                                                    <path
                                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4h-12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />
                                                </svg>
                                                <div
                                                    class="flex justify-center text-sm text-gray-400"
                                                >
                                                    <label
                                                        for="logo_header"
                                                        class="relative font-medium text-indigo-400 rounded-md cursor-pointer hover:text-indigo-300"
                                                    >
                                                        <span>Upload logo</span>
                                                        <input
                                                            id="logo_header"
                                                            name="logo_header"
                                                            type="file"
                                                            class="sr-only"
                                                            @change="
                                                                handleLogoUpload(
                                                                    $event,
                                                                    'logo_header'
                                                                )
                                                            "
                                                            accept="image/*"
                                                        />
                                                    </label>
                                                </div>
                                                <p
                                                    class="text-xs text-gray-400"
                                                >
                                                    300x100px max
                                                </p>
                                            </div>
                                        </div>
                                        <InputError
                                            :message="
                                                appearanceForm.errors
                                                    .logo_header
                                            "
                                            class="mt-1"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <InputLabel
                                            for="logo_footer"
                                            value="Footer Logo"
                                        />
                                        <div
                                            class="flex justify-center px-6 pt-5 pb-6 mt-1 transition-colors border-2 border-gray-600 border-dashed rounded-md hover:border-indigo-400"
                                        >
                                            <div
                                                v-if="
                                                    getImagePreview(
                                                        'logo_footer'
                                                    ).value
                                                "
                                                class="relative mb-2"
                                            >
                                                <img
                                                    :src="
                                                        getImagePreview(
                                                            'logo_footer'
                                                        ).value
                                                    "
                                                    alt="Preview Logo Header"
                                                    class="object-cover w-full rounded-lg shadow-md"
                                                />
                                                <button
                                                    type="button"
                                                    @click="
                                                        deleteLogo(
                                                            'logo_footer'
                                                        )
                                                    "
                                                    class="absolute z-10 p-1 text-white transition bg-red-600 rounded-full -right-2 -top-2 hover:bg-red-700"
                                                    title="Hapus Logo"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="w-4 h-4"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M6 18L18 6M6 6l12 12"
                                                        />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div
                                                class="space-y-1 text-center"
                                                v-if="
                                                    !getImagePreview(
                                                        'logo_footer'
                                                    ).value
                                                "
                                            >
                                                <svg
                                                    class="w-12 h-12 mx-auto text-gray-400"
                                                    stroke="currentColor"
                                                    fill="none"
                                                    viewBox="0 0 48 48"
                                                    aria-hidden="true"
                                                >
                                                    <path
                                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4h-12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />
                                                </svg>
                                                <div
                                                    class="flex justify-center text-sm text-gray-400"
                                                >
                                                    <label
                                                        for="logo_footer"
                                                        class="relative font-medium text-indigo-400 rounded-md cursor-pointer hover:text-indigo-300"
                                                    >
                                                        <span>Upload logo</span>
                                                        <input
                                                            id="logo_footer"
                                                            name="logo_footer"
                                                            type="file"
                                                            class="sr-only"
                                                            @change="
                                                                handleLogoUpload(
                                                                    $event,
                                                                    'logo_footer'
                                                                )
                                                            "
                                                            accept="image/*"
                                                        />
                                                    </label>
                                                </div>
                                                <p
                                                    class="text-xs text-gray-400"
                                                >
                                                    300x100px max
                                                </p>
                                            </div>
                                        </div>
                                        <InputError
                                            :message="
                                                appearanceForm.errors
                                                    .logo_footer
                                            "
                                            class="mt-1"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <InputLabel
                                            for="logo_favicon"
                                            value="Favicon (32x32px)"
                                        />
                                        <div
                                            class="flex justify-center px-6 pt-5 pb-6 mt-1 transition-colors border-2 border-gray-600 border-dashed rounded-md hover:border-indigo-400"
                                        >
                                            <div
                                                v-if="
                                                    getImagePreview(
                                                        'logo_favicon'
                                                    ).value
                                                "
                                                class="relative mb-2"
                                            >
                                                <img
                                                    :src="
                                                        getImagePreview(
                                                            'logo_favicon'
                                                        ).value
                                                    "
                                                    alt="Preview Logo Header"
                                                    class="object-cover w-full rounded-lg shadow-md"
                                                />
                                                <button
                                                    type="button"
                                                    @click="
                                                        deleteLogo(
                                                            'logo_favicon'
                                                        )
                                                    "
                                                    class="absolute z-10 p-1 text-white transition bg-red-600 rounded-full -right-2 -top-2 hover:bg-red-700"
                                                    title="Hapus Logo"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="w-4 h-4"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M6 18L18 6M6 6l12 12"
                                                        />
                                                    </svg>
                                                </button>
                                            </div>

                                            <div
                                                class="space-y-1 text-center"
                                                v-if="
                                                    !getImagePreview(
                                                        'logo_favicon'
                                                    ).value
                                                "
                                            >
                                                <svg
                                                    class="w-12 h-12 mx-auto text-gray-400"
                                                    stroke="currentColor"
                                                    fill="none"
                                                    viewBox="0 0 48 48"
                                                    aria-hidden="true"
                                                >
                                                    <path
                                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4h-12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />
                                                </svg>
                                                <div
                                                    class="flex justify-center text-sm text-gray-400"
                                                >
                                                    <label
                                                        for="logo_favicon"
                                                        class="relative font-medium text-indigo-400 rounded-md cursor-pointer hover:text-indigo-300"
                                                    >
                                                        <span
                                                            >Upload
                                                            favicon</span
                                                        >
                                                        <input
                                                            id="logo_favicon"
                                                            name="logo_favicon"
                                                            type="file"
                                                            class="sr-only"
                                                            @change="
                                                                handleLogoUpload(
                                                                    $event,
                                                                    'logo_favicon'
                                                                )
                                                            "
                                                            accept="image/*"
                                                        />
                                                    </label>
                                                </div>
                                                <p
                                                    class="text-xs text-gray-400"
                                                >
                                                    32x32px required
                                                </p>
                                            </div>
                                        </div>
                                        <InputError
                                            :message="
                                                appearanceForm.errors
                                                    .logo_favicon
                                            "
                                            class="mt-1"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-6">
                                <PrimaryButton
                                    type="submit"
                                    :disabled="appearanceForm.processing"
                                    class="transition-colors duration-300 bg-indigo-600 hover:bg-indigo-700"
                                >
                                    <span v-if="appearanceForm.processing"
                                        >Saving...</span
                                    >
                                    <span v-else>Save Appearance Settings</span>
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- API Connections Tab -->
                <div
                    v-if="activeTab === 'api'"
                    class="space-y-8 transition-all duration-300 animate-fade-in"
                >
                    <div
                        class="p-6 border border-gray-700 rounded-lg shadow-lg bg-gray-800/70 backdrop-blur-sm"
                    >
                        <h3 class="mb-4 text-lg font-medium text-white">
                            API Provider Credentials
                        </h3>
                        <form @submit.prevent="submitApiSettings">
                            <div class="space-y-6">
                                <div
                                    v-for="(provider, id) in apiForm.providers"
                                    :key="id"
                                    class="p-5 border border-gray-600 rounded-lg bg-gray-700/50"
                                >
                                    <h4
                                        class="flex items-center font-medium text-white text-md"
                                    >
                                        <span
                                            class="inline-flex items-center justify-center w-8 h-8 mr-3 bg-indigo-500 rounded-full"
                                        >
                                            <span
                                                class="font-bold text-white"
                                                >{{
                                                    provider.provider_name.charAt(
                                                        0
                                                    )
                                                }}</span
                                            >
                                        </span>
                                        {{ provider.provider_name }}
                                        <span
                                            :class="{
                                                'bg-green-500':
                                                    provider.status ===
                                                    'active',
                                                'bg-red-500':
                                                    provider.status ===
                                                    'inactive',
                                            }"
                                            class="px-2 py-1 ml-3 text-xs text-white rounded-full"
                                        >
                                            {{
                                                provider.provider_name ==
                                                "digiflazz"
                                                    ? "Rp " +
                                                      provider.balance.toLocaleString()
                                                    : "RM " + provider.balance
                                            }}
                                        </span>
                                    </h4>

                                    <div
                                        class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-2"
                                    >
                                        <div class="space-y-2">
                                            <InputLabel
                                                :for="`api_username_${id}`"
                                                value="Username/Email"
                                            />
                                            <TextInput
                                                :id="`api_username_${id}`"
                                                type="text"
                                                class="block w-full text-white border-gray-600 bg-gray-700/70"
                                                v-model="
                                                    apiForm.providers[id]
                                                        .api_username
                                                "
                                            />
                                            <InputError
                                                :message="
                                                    apiForm.errors[
                                                        `providers.${id}.api_username`
                                                    ]
                                                "
                                                class="mt-1"
                                            />
                                        </div>

                                        <div class="space-y-2">
                                            <InputLabel
                                                :for="`api_key_${id}`"
                                                value="API Key"
                                            />
                                            <div class="relative">
                                                <TextInput
                                                    :id="`api_key_${id}`"
                                                    :type="
                                                        isFieldVisible(
                                                            id,
                                                            'api_key'
                                                        )
                                                            ? 'text'
                                                            : 'password'
                                                    "
                                                    class="block w-full pr-10 text-white border-gray-600 bg-gray-700/70"
                                                    v-model="
                                                        apiForm.providers[id]
                                                            .api_key
                                                    "
                                                />
                                                <button
                                                    type="button"
                                                    @click="
                                                        toggleFieldVisibility(
                                                            id,
                                                            'api_key'
                                                        )
                                                    "
                                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-white"
                                                >
                                                    <span class="text-sm">{{
                                                        isFieldVisible(
                                                            id,
                                                            "api_key"
                                                        )
                                                            ? "Hide"
                                                            : "Show"
                                                    }}</span>
                                                </button>
                                            </div>
                                            <InputError
                                                :message="
                                                    apiForm.errors[
                                                        `providers.${id}.api_key`
                                                    ]
                                                "
                                                class="mt-1"
                                            />
                                        </div>

                                        <div class="space-y-2">
                                            <InputLabel
                                                :for="`api_private_key_${id}`"
                                                value="Private/Secret Key"
                                            />
                                            <div class="relative">
                                                <TextInput
                                                    :id="`api_private_key_${id}`"
                                                    :type="
                                                        isFieldVisible(
                                                            id,
                                                            'private'
                                                        )
                                                            ? 'text'
                                                            : 'password'
                                                    "
                                                    class="block w-full pr-10 text-white border-gray-600 bg-gray-700/70"
                                                    v-model="
                                                        apiForm.providers[id]
                                                            .api_private_key
                                                    "
                                                />
                                                <button
                                                    type="button"
                                                    @click="
                                                        toggleFieldVisibility(
                                                            id,
                                                            'private'
                                                        )
                                                    "
                                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-white"
                                                >
                                                    <span class="text-sm">{{
                                                        isFieldVisible(
                                                            id,
                                                            "private"
                                                        )
                                                            ? "Hide"
                                                            : "Show"
                                                    }}</span>
                                                </button>
                                            </div>
                                            <InputError
                                                :message="
                                                    apiForm.errors[
                                                        `providers.${id}.api_private_key`
                                                    ]
                                                "
                                                class="mt-1"
                                            />
                                        </div>

                                        <div class="space-y-2">
                                            <InputLabel
                                                :for="`status_${id}`"
                                                value="Status"
                                            />
                                            <select
                                                :id="`status_${id}`"
                                                class="block w-full mt-1 text-white border-gray-600 rounded-md shadow-sm bg-gray-700/70"
                                                v-model="
                                                    apiForm.providers[id].status
                                                "
                                            >
                                                <option value="active">
                                                    Active
                                                </option>
                                                <option value="inactive">
                                                    Inactive
                                                </option>
                                            </select>
                                            <InputError
                                                :message="
                                                    apiForm.errors[
                                                        `providers.${id}.status`
                                                    ]
                                                "
                                                class="mt-1"
                                            />
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <p
                                            class="px-4 py-2 mt-4 rounded-lg cursor-pointer bg-primary text-primary-text"
                                            @click="
                                                getBalanceProvider(provider.id)
                                            "
                                        >
                                            Get Balance
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-6">
                                <PrimaryButton
                                    type="submit"
                                    :disabled="apiForm.processing"
                                    class="transition-colors duration-300 bg-indigo-600 hover:bg-indigo-700"
                                >
                                    <span v-if="apiForm.processing"
                                        >Saving...</span
                                    >
                                    <span v-else>Save API Settings</span>
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Security Tab -->
                <div
                    v-if="activeTab === 'security'"
                    class="space-y-8 transition-all duration-300 animate-fade-in"
                >
                    <div
                        class="p-6 border border-gray-700 rounded-lg shadow-lg bg-gray-800/70 backdrop-blur-sm"
                    >
                        <h3 class="mb-4 text-lg font-medium text-white">
                            Security Settings
                        </h3>
                        <form @submit.prevent="submitSecuritySettings">
                            <div class="space-y-6">
                                <div
                                    class="p-5 border border-gray-600 rounded-lg bg-gray-700/50"
                                >
                                    <h4
                                        class="mb-4 font-medium text-white text-md"
                                    >
                                        Authentication Security
                                    </h4>

                                    <div class="space-y-4">
                                        <div class="flex items-start">
                                            <div class="flex items-center h-5">
                                                <input
                                                    id="2fa_enabled"
                                                    type="checkbox"
                                                    class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 rounded focus:ring-indigo-500"
                                                    v-model="
                                                        securityForm.enable_2fa
                                                    "
                                                />
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label
                                                    for="2fa_enabled"
                                                    class="font-medium text-white"
                                                >
                                                    Enable 2FA for Admin
                                                </label>
                                                <p class="text-gray-400">
                                                    Require two-factor
                                                    authentication for all admin
                                                    accounts
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-start">
                                            <div class="flex items-center h-5">
                                                <input
                                                    id="ip_restriction"
                                                    type="checkbox"
                                                    class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 rounded focus:ring-indigo-500"
                                                    v-model="
                                                        securityForm.ip_restriction
                                                    "
                                                />
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label
                                                    for="ip_restriction"
                                                    class="font-medium text-white"
                                                >
                                                    IP Restriction
                                                </label>
                                                <p class="text-gray-400">
                                                    Only allow admin login from
                                                    specific IP addresses
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="p-5 border border-gray-600 rounded-lg bg-gray-700/50"
                                >
                                    <h4
                                        class="mb-4 font-medium text-white text-md"
                                    >
                                        Password Policy
                                    </h4>

                                    <div class="space-y-4">
                                        <div class="flex items-start">
                                            <div class="flex items-center h-5">
                                                <input
                                                    id="enforce_complex_password"
                                                    type="checkbox"
                                                    class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 rounded focus:ring-indigo-500"
                                                    v-model="
                                                        securityForm.enforce_complex_password
                                                    "
                                                />
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label
                                                    for="enforce_complex_password"
                                                    class="font-medium text-white"
                                                >
                                                    Enforce Complex Passwords
                                                </label>
                                                <p class="text-gray-400">
                                                    Require numbers, special
                                                    characters, and mixed case
                                                </p>
                                            </div>
                                        </div>

                                        <div class="space-y-2">
                                            <InputLabel
                                                for="password_expiry_days"
                                                value="Password Expiry (days)"
                                            />
                                            <TextInput
                                                id="password_expiry_days"
                                                type="number"
                                                class="block w-full mt-1 text-white border-gray-600 bg-gray-700/70"
                                                placeholder="90"
                                                v-model="
                                                    securityForm.password_expiry_days
                                                "
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-6">
                                <PrimaryButton
                                    type="submit"
                                    :disabled="securityForm.processing"
                                    class="transition-colors duration-300 bg-indigo-600 hover:bg-indigo-700"
                                >
                                    <span v-if="securityForm.processing"
                                        >Saving...</span
                                    >
                                    <span v-else>Save Security Settings</span>
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Fade animations */
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out forwards;
}

/* Add glassmorphism effect to the panel */
.backdrop-blur-sm {
    backdrop-filter: blur(8px);
}
</style>

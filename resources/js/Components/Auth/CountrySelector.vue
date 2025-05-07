<script setup>
import { ref, computed, watch, onMounted } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    id: {
        type: String,
        default: "country",
    },
    modelValue: {
        type: String,
        default: "+62", // Default to Indonesia
    },
    label: {
        type: String,
        default: "Country Code",
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

const isOpen = ref(false);
const searchQuery = ref("");
const countries = ref([
    { code: "ID", name: "Indonesia", dial_code: "+62", flag: "🇮🇩" },
    { code: "US", name: "United States", dial_code: "+1", flag: "🇺🇸" },
    { code: "GB", name: "United Kingdom", dial_code: "+44", flag: "🇬🇧" },
    { code: "AU", name: "Australia", dial_code: "+61", flag: "🇦🇺" },
    { code: "JP", name: "Japan", dial_code: "+81", flag: "🇯🇵" },
    { code: "SG", name: "Singapore", dial_code: "+65", flag: "🇸🇬" },
    { code: "MY", name: "Malaysia", dial_code: "+60", flag: "🇲🇾" },
    { code: "KR", name: "South Korea", dial_code: "+82", flag: "🇰🇷" },
    { code: "CN", name: "China", dial_code: "+86", flag: "🇨🇳" },
    { code: "CA", name: "Canada", dial_code: "+1", flag: "🇨🇦" },
]);

const selectedCountry = computed(() => {
    return (
        countries.value.find(
            (country) => country.dial_code === props.modelValue
        ) || countries.value[0]
    );
});

const filteredCountries = computed(() => {
    if (!searchQuery.value) return countries.value;

    const query = searchQuery.value.toLowerCase();
    return countries.value.filter(
        (country) =>
            country.name.toLowerCase().includes(query) ||
            country.dial_code.includes(query)
    );
});

function selectCountry(country) {
    emit("update:modelValue", country.dial_code);
    isOpen.value = false;
    searchQuery.value = "";
}

function toggleDropdown() {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        // Focus the search input when dropdown opens
        setTimeout(() => {
            document.getElementById("country-search")?.focus();
        }, 100);
    }
}

function handleClickOutside(event) {
    const dropdown = document.getElementById("country-dropdown");
    if (dropdown && !dropdown.contains(event.target)) {
        isOpen.value = false;
    }
}

// Debounce search input
let searchTimeout;
function handleSearch(event) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        searchQuery.value = event.target.value;
    }, 300);
}

onMounted(() => {
    document.addEventListener("mousedown", handleClickOutside);
});
</script>

<template>
    <div class="relative mb-4 country-selector">
        <InputLabel :for="id" :value="label" class="text-gray-300" />

        <div id="country-dropdown" class="relative mt-1">
            <button
                type="button"
                @click="toggleDropdown"
                class="flex items-center justify-between w-full px-4 py-2 text-white border border-gray-700 rounded-md cosmic-input bg-dark-lighter focus:outline-none focus:ring-2 focus:ring-primary"
            >
                <div class="flex items-center">
                    <span class="mr-2 text-xl">{{ selectedCountry.flag }}</span>
                    <span>{{ selectedCountry.dial_code }}</span>
                </div>
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 text-gray-400"
                    :class="{ 'transform rotate-180': isOpen }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 9l-7 7-7-7"
                    />
                </svg>
            </button>

            <!-- Dropdown menu -->
            <div
                v-show="isOpen"
                class="absolute z-10 w-full mt-1 overflow-auto border border-gray-700 rounded-md shadow-lg bg-dark-lighter max-h-60"
            >
                <!-- Search input -->
                <div class="sticky top-0 px-3 py-2 bg-dark-lighter">
                    <input
                        id="country-search"
                        type="text"
                        placeholder="Search country..."
                        @input="handleSearch"
                        class="w-full px-2 py-1 text-white rounded-md cosmic-input focus:outline-none focus:ring-2 focus:ring-primary"
                    />
                </div>

                <!-- Country list -->
                <ul class="py-1">
                    <li
                        v-for="country in filteredCountries"
                        :key="country.code"
                        @click="selectCountry(country)"
                        class="flex items-center px-3 py-2 cursor-pointer hover:bg-gray-700"
                    >
                        <span class="mr-2 text-xl">{{ country.flag }}</span>
                        <span class="text-white">{{ country.name }}</span>
                        <span class="ml-auto text-gray-400">{{
                            country.dial_code
                        }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <InputError :message="error" class="mt-2" />
    </div>
</template>

<style scoped>
.cosmic-input {
    background-color: rgba(31, 41, 55, 0.8);
    transition: all 0.3s ease;
}
</style>


<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from "vue";
import CosmicCard from "@/Components/Order/CosmicCard.vue";

// --- Countries mini-list (for brevity; full list can be expanded if needed) ---
const countries = [
    { code: "ID", name: "Indonesia", dial_code: "+62", flag: "🇮🇩", min_len: 9 },
    { code: "MY", name: "Malaysia", dial_code: "+60", flag: "🇲🇾", min_len: 9 },
    { code: "SG", name: "Singapore", dial_code: "+65", flag: "🇸🇬", min_len: 8 },
    { code: "TH", name: "Thailand", dial_code: "+66", flag: "🇹🇭", min_len: 8 },
    { code: "VN", name: "Vietnam", dial_code: "+84", flag: "🇻🇳", min_len: 9 },
    {
        code: "PH",
        name: "Philippines",
        dial_code: "+63",
        flag: "🇵🇭",
        min_len: 9,
    },
    {
        code: "US",
        name: "United States",
        dial_code: "+1",
        flag: "🇺🇸",
        min_len: 10,
    },
];

const props = defineProps({
    initialEmail: String,
    initialPhone: String,
    initialCountry: String,
});
const emit = defineEmits(["update:contact"]);

const email = ref(props.initialEmail || "");
const phone = ref(props.initialPhone || "");
const country = ref(props.initialCountry || "ID");

watch(
    [email, phone, country],
    () => {
        emit("update:contact", {
            email: email.value,
            phone: phone.value,
            country: country.value,
        });
    },
    { immediate: true }
);

const emailError = ref("");
function validateEmail(val) {
    // Very basic email regex
    const re = /^\S+@\S+\.\S+$/;
    return re.test(val);
}
watch(email, (newVal) => {
    if (!newVal) {
        emailError.value = "";
    } else if (!validateEmail(newVal)) {
        emailError.value = "Invalid email format";
    } else {
        emailError.value = "";
    }
});

const selectedCountry = computed(
    () => countries.find((c) => c.code === country.value) || countries[0]
);
const phoneError = ref("");
watch(phone, (val) => {
    if (val && val.length < selectedCountry.value.min_len) {
        phoneError.value = `Minimum ${selectedCountry.value.min_len} digits for ${selectedCountry.value.name}`;
    } else {
        phoneError.value = "";
    }
});

// Format phone number per country after input (simple)
function formatPhone(val) {
    return val.replace(/\D/g, "");
}

// Helper: show transaction alert only if email present and valid
const showAlert = computed(() => email.value && !emailError.value);

// Flag dropdown state and positioning
const dropdownOpen = ref(false);
const dropdownRef = ref(null);
const dropdownContainerRef = ref(null);
const dropdownPosition = ref({ top: false, bottom: true });
const isMobileView = ref(false);

// Auto-position dropdown based on available space
const positionDropdown = () => {
    if (!dropdownRef.value || !dropdownContainerRef.value) return;
    
    // Check if we're in mobile view
    isMobileView.value = window.innerWidth < 640;
    
    if (isMobileView.value) {
        // On mobile, position at bottom of viewport
        dropdownPosition.value = { top: false, bottom: true };
        return;
    }
    
    // Get container position
    const containerRect = dropdownContainerRef.value.getBoundingClientRect();
    const dropdownHeight = dropdownRef.value.scrollHeight;
    
    // Calculate space available above and below
    const spaceAbove = containerRect.top;
    const spaceBelow = window.innerHeight - containerRect.bottom;
    
    // If not enough space below but enough space above, flip to top
    if (spaceBelow < dropdownHeight && spaceAbove > dropdownHeight) {
        dropdownPosition.value = { top: true, bottom: false };
    } else {
        dropdownPosition.value = { top: false, bottom: true };
    }
};

function selectCountry(code) {
    country.value = code;
    closeDropdown();
    nextTick(() => document.getElementById("contact-phone")?.focus());
}

function closeDropdown() {
    dropdownOpen.value = false;
}

function toggleDropdown() {
    dropdownOpen.value = !dropdownOpen.value;
    if (dropdownOpen.value) {
        nextTick(() => {
            positionDropdown();
            // Add event listeners for clicking outside
            document.addEventListener('click', handleClickOutside);
        });
    }
}

function handleClickOutside(event) {
    if (dropdownContainerRef.value && !dropdownContainerRef.value.contains(event.target)) {
        closeDropdown();
    }
}

// Lifecycle hooks for event cleanup
onMounted(() => {
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    document.removeEventListener('click', handleClickOutside);
});

function handleResize() {
    if (dropdownOpen.value) {
        positionDropdown();
    }
}
</script>
<template>
    <CosmicCard :title="'Contact Details'" :step-number="5">
        <form
            class="relative grid grid-cols-1 gap-6 rounded-lg"
            autocomplete="off"
            @submit.prevent
        >
            <!-- Email -->
            <div class="flex flex-col">
                <label
                    for="contact-email"
                    class="mb-1 font-medium text-primary_text"
                    >Email (optional)</label
                >
                <input
                    id="contact-email"
                    v-model="email"
                    type="email"
                    autocomplete="email"
                    aria-label="Email address"
                    :aria-invalid="!!emailError"
                    class="px-4 py-3 border outline-none bg-secondary/20 text-primary_text focus:ring-2 focus:border-primary focus:bg-secondary/20/90 border-secondary rounded-xl placeholder-secondary cosmic-focus-meteor"
                    placeholder="example@email.com"
                />
                <transition name="fade">
                    <div
                        v-if="emailError"
                        class="mt-1 text-xs text-red-300 cosmic-error-particles animate-pulse"
                    >
                        {{ emailError }}
                    </div>
                </transition>
            </div>
            <!-- Phone with country -->
            <div class="flex flex-col">
                <label
                    for="contact-phone"
                    class="mb-1 font-medium text-primary_text"
                    >WhatsApp Number</label
                >
                <div class="flex items-stretch gap-6">
                    <!-- Country selector with improved positioning -->
                    <div class="relative" ref="dropdownContainerRef">
                        <button
                            type="button"
                            class="flex items-center gap-1 px-3 py-2 text-xl font-bold transition-all border rounded-full shadow select-none bg-secondary/20 border-secondary hover-scale"
                            @click="toggleDropdown"
                            aria-label="Choose country code"
                        >
                            <span class="mr-1 text-secondary/80">{{
                                selectedCountry.flag
                            }}</span>
                            <span class="text-sm font-semibold text-white/70">{{
                                selectedCountry.dial_code
                            }}</span>
                            <svg
                                class="w-4 h-4 ml-1 text-secondary"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                            <!-- satellite anim (orbit) -->
                            <span
                                class="absolute block w-4 h-4 -top-2 -right-2 animate-orbit-satellite"
                            >
                                <span
                                    class="block w-full h-full rounded-full bg-secondary opacity-60"
                                ></span>
                            </span>
                        </button>
                        
                        <!-- Improved Dropdown -->
                        <transition name="fade">
                            <div
                                v-if="dropdownOpen"
                                ref="dropdownRef"
                                class="cosmic-country-dropdown"
                                :class="{
                                    'cosmic-dropdown-top': dropdownPosition.top,
                                    'cosmic-dropdown-bottom': dropdownPosition.bottom,
                                    'cosmic-dropdown-mobile': isMobileView
                                }"
                            >
                                <div class="py-1 space-y-1 overflow-y-auto max-h-56">
                                    <button
                                        v-for="ct in countries"
                                        :key="ct.code"
                                        @click="selectCountry(ct.code)"
                                        type="button"
                                        class="flex items-center w-full px-2 py-2 space-x-2 text-left hover:bg-secondary/10"
                                        :class="{
                                            'bg-secondary/10': ct.code === country,
                                        }"
                                    >
                                        <span>{{ ct.flag }}</span>
                                        <span class="w-10 text-xs font-bold">{{
                                            ct.dial_code
                                        }}</span>
                                        <span class="text-primary_text">{{
                                            ct.name
                                        }}</span>
                                    </button>
                                </div>
                            </div>
                        </transition>
                    </div>
                    <!-- Phone input -->
                    <input
                        id="contact-phone"
                        v-model="phone"
                        type="text"
                        inputmode="tel"
                        autocomplete="tel"
                        :placeholder="'8xxxxxxxxx'"
                        class="flex-1 px-4 py-3 rounded-xl border-secondary bg-secondary/20 text-primary_text cosmic-focus-meteor placeholder-secondary"
                        @input="phone = formatPhone(phone)"
                    />
                </div>
                <span class="block mt-2 text-xs text-secondary">
                    This number will be contacted if issues occur.
                </span>
                <transition name="fade"
                    ><span
                        v-if="phoneError"
                        class="mt-1 text-xs text-red-200 cosmic-error-particles animate-pulse"
                        >{{ phoneError }}</span
                    ></transition
                >
            </div>

            <!-- Transaction alert (email provided) -->
            <transition name="slide-up">
                <div
                    v-if="showAlert"
                    class="flex items-center col-span-1 px-4 py-2 mt-2 space-x-2 border rounded-lg shadow md:col-span-2 bg-content_background border-secondary animate-pulse cosmic-alert-nebula"
                >
                    <!-- "Supernova icon" -->
                    <svg
                        class="w-6 h-6 mr-2 text-secondary animate-pulse"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            cx="12"
                            cy="12"
                            r="7"
                            fill="#33c3f0"
                            fill-opacity=".13"
                        />
                        <path
                            d="M12 2v4m0 12v4m10-10h-4M6 12H2m15.5-7.5l-2.9 2.9M8.4 18.4l-2.9 2.9m13.1 0l-2.9-2.9M8.4 5.6L5.5 2.7"
                            stroke="#33C3F0"
                            stroke-width="1.5"
                            stroke-linecap="round"
                        />
                    </svg>
                    <span class="text-sm font-semibold text-secondary"
                        >Transaction proof will be sent to above email
                        (optional)</span
                    >
                </div>
            </transition>
        </form>
    </CosmicCard>
</template>

<style scoped>
.cosmic-error-particles {
    /* Simulated effect: particle fade based on error text */
    letter-spacing: 1.5px;
    text-shadow: 0 1px 2px #9b87f5aa, 1px 2px 1px #33c3f099;
    filter: blur(0.1px) contrast(120%);
}
.cosmic-alert-nebula {
    box-shadow: 0 0 10px 0 #33c3f077, 0 0 8px #9b87f511;
}

/* Improved dropdown positioning */
.cosmic-country-dropdown {
    position: absolute;
    left: 0;
    z-index: 100;
    width: 56;
    border-radius: 0.5rem;
    border: 1px solid;
    border-color: var(--color-secondary, #33C3F0);
    background-color: var(--color-content-bg, #1F2937);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
    padding: 0.5rem;
    max-width: 240px;
}

.cosmic-dropdown-bottom {
    top: calc(100% + 0.5rem);
}

.cosmic-dropdown-top {
    bottom: calc(100% + 0.5rem);
}

.cosmic-dropdown-mobile {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    max-width: 100%;
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
    padding: 1rem;
    max-height: 60vh;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.5);
    background-color: var(--color-header-bg, #111827);
}

.cosmic-dropdown-mobile button {
    padding: 0.75rem;
}

.animate-orbit-satellite {
    animation: orbit-satellite 2.2s linear infinite;
}
@keyframes orbit-satellite {
    0% {
        transform: rotate(0deg) translateX(0.7rem) rotate(0deg);
    }
    100% {
        transform: rotate(360deg) translateX(0.7rem) rotate(-360deg);
    }
}
.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 220ms cubic-bezier(0.4, 0, 0.6, 1);
}
.slide-up-enter,
.slide-up-leave-to {
    opacity: 0;
    transform: translateY(36px);
}
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.22s cubic-bezier(0.4, 0, 0.6, 1);
}
.fade-enter,
.fade-leave-to {
    opacity: 0;
}
</style>

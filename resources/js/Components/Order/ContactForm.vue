<template>
    <div class="space-y-4">
        <CosmicCard title="Contact Information" :step-number="6">
            <div class="space-y-4">
                <div>
                    <label
                        for="email"
                        class="block text-sm font-medium text-gray-300"
                        >Email (Optional)</label
                    >
                    <input
                        id="email"
                        v-model="localEmail"
                        type="email"
                        class="w-full px-4 py-2 text-white transition-colors border rounded-lg bg-dark-lighter border-secondary/30 focus:border-secondary focus:outline-none"
                        placeholder="Your email address"
                    />
                </div>

                <!-- Country code selection -->
                <div class="flex items-center space-x-2">
                    <div class="w-1/3">
                        <label
                            for="country"
                            class="block text-sm font-medium text-gray-300"
                            >Country</label
                        >
                        <select
                            id="country"
                            v-model="selectedCountry"
                            class="w-full px-3 py-2 text-white transition-colors border rounded-lg bg-dark-lighter border-secondary/30 focus:border-secondary focus:outline-none"
                        >
                            <option value="ID">Indonesia (+62)</option>
                            <option value="MY">Malaysia (+60)</option>
                            <option value="SG">Singapore (+65)</option>
                            <option value="TH">Thailand (+66)</option>
                            <option value="VN">Vietnam (+84)</option>
                            <option value="PH">Philippines (+63)</option>
                            <option value="US">United States (+1)</option>
                            <option value="GB">United Kingdom (+44)</option>
                            <option value="AU">Australia (+61)</option>
                        </select>
                    </div>

                    <div class="w-2/3">
                        <label
                            for="phone"
                            class="block text-sm font-medium text-gray-300"
                            >WhatsApp Number
                            <span class="text-primary">*</span></label
                        >
                        <div class="flex">
                            <span
                                class="inline-flex items-center px-3 text-sm text-gray-300 border border-r-0 bg-dark-card border-secondary/30 rounded-l-md"
                            >
                                {{ countryPrefix }}
                            </span>
                            <input
                                id="phone"
                                v-model="localPhone"
                                type="text"
                                required
                                class="w-full px-4 py-2 text-white transition-colors border rounded-r-lg bg-dark-lighter border-secondary/30 focus:border-secondary focus:outline-none"
                                placeholder="WhatsApp number"
                            />
                        </div>
                        <p v-if="phoneError" class="mt-1 text-xs text-red-400">
                            {{ phoneError }}
                        </p>
                    </div>
                </div>

                <div
                    class="p-3 text-xs text-gray-400 rounded-lg bg-dark-lighter"
                >
                    <div class="flex items-start">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4 mr-2 text-secondary"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <p>
                            We'll send your order details and receipt to this
                            WhatsApp number. Make sure it's correct.
                        </p>
                    </div>
                </div>
            </div>
        </CosmicCard>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import CosmicCard from "@/Components/Order/CosmicCard.vue";
import { useCountryCode } from "@/Composables/useCountryCode";

const props = defineProps({
    initialEmail: {
        type: String,
        default: "",
    },
    initialPhone: {
        type: String,
        default: "",
    },
    initialCountry: {
        type: String,
        default: "ID",
    },
});

const emit = defineEmits(["update:contact"]);

const { countryCode, countryPrefix, setCountry, formatInternationalNumber } =
    useCountryCode();

const localEmail = ref(props.initialEmail || "");
const localPhone = ref(props.initialPhone || "");
const selectedCountry = ref(props.initialCountry || "ID");
const phoneError = ref("");

// When country changes, update the prefix
watch(selectedCountry, (newCountry) => {
    setCountry(newCountry);
});

// Format and validate phone number
const formattedPhone = computed(() => {
    return formatInternationalNumber(localPhone.value);
});

watch([localEmail, formattedPhone, selectedCountry], () => {
    validatePhone();

    emit("update:contact", {
        email: localEmail.value,
        phone: formattedPhone.value,
        country: selectedCountry.value,
    });
});

const validatePhone = () => {
    if (localPhone.value && localPhone.value.length < 7) {
        phoneError.value = "Phone number is too short";
    } else {
        phoneError.value = "";
    }
};

// Initialize country prefix
setCountry(selectedCountry.value);
</script>

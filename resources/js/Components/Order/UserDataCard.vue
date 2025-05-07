<template>
    <CosmicCard title="Game Account" :step-number="1">
        <!-- Quick Access Buttons - New Section -->
        <div v-if="savedAccounts.length > 0" class="mb-4 space-y-2">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-300">
                    Saved Accounts
                </h3>
                <button
                    v-if="savedAccounts.length > 0"
                    @click="clearAllSavedAccounts"
                    class="text-xs text-red-400 hover:text-red-300"
                >
                    Clear All
                </button>
            </div>

            <div class="relative">
                <div
                    class="flex gap-2 pb-2 overflow-x-auto scrollbar-none cosmic-accounts-container"
                    :class="{ 'justify-start': savedAccounts.length > 3 }"
                >
                    <button
                        v-for="account in savedAccounts"
                        :key="account.id"
                        @click="loadAccount(account)"
                        class="cosmic-account-btn flex-shrink-0 h-8 px-3 py-1 text-xs flex items-center gap-1.5 rounded-full bg-gradient-to-r from-primary/10 to-secondary/10 border border-transparent hover:border-primary/50 transition-all duration-300 relative group sm:h-10 sm:px-4 sm:py-1.5 sm:text-sm"
                        :class="{
                            'pulse-animation': pulsingAccountId === account.id,
                        }"
                    >
                        <span
                            class="max-w-[100px] truncate text-primary-text/70"
                        >
                            {{ getAccountDisplayName(account) }}
                        </span>
                        <span class="flex items-center text-xs text-gray-400">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-3 h-3 mr-0.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            {{ getTimeAgo(account.timestamp) }}
                        </span>

                        <!-- Hover tooltip with account data preview -->
                        <!-- <div
                            class="absolute z-20 invisible p-2 text-left transition-all duration-300 transform -translate-x-1/2 translate-y-2 rounded-md opacity-0 pointer-events-none bg-dark-card left-1/2 bottom-full w-44 shadow-cosmic group-hover:visible group-hover:opacity-100"
                        >
                            <div class="text-xs font-medium text-white">
                                Account Details
                            </div>
                            <div v-if="account.fields" class="mt-1 space-y-0.5">
                                <div
                                    v-for="(value, key) in account.fields"
                                    :key="key"
                                    class="flex justify-between gap-2"
                                >
                                    <span
                                        class="text-xs font-medium text-gray-400"
                                        >{{ formatFieldName(key) }}</span
                                    >
                                    <span
                                        class="text-xs text-white truncate max-w-[120px]"
                                        >{{ value }}</span
                                    >
                                </div>
                            </div>
                            <div
                                class="absolute w-2 h-2 transform rotate-45 -translate-x-1/2 bg-dark-card -bottom-1 left-1/2"
                            ></div>
                        </div> -->

                        <!-- Delete button -->
                        <!-- <button
                            @click.stop="deleteSavedAccount(account.id)"
                            class="absolute top-0 right-0 flex items-center justify-center w-4 h-4 text-white transition-opacity transform translate-x-1/2 -translate-y-1/2 rounded-full opacity-0 bg-red-500/80 hover:bg-red-500 group-hover:opacity-100"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-3 h-3"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M18 6L6 18M6 6l12 12"></path>
                            </svg>
                        </button> -->

                        <!-- Meteor trail effect -->
                        <!-- <div class="meteor-trail"></div> -->
                    </button>
                </div>

                <!-- Scrolling indicators -->
                <div
                    v-if="savedAccounts.length > (isMobile ? 3 : 5)"
                    class="absolute top-0 bottom-0 right-0 w-8 pointer-events-none bg-gradient-to-l from-content_background to-transparent"
                ></div>
            </div>
        </div>

        <form @submit.prevent class="space-y-4">
            <div class="space-y-4">
                <div class="grid grid-cols-2 space-x-4">
                    <DynamicInput
                        v-for="field in inputFields"
                        :key="field.id"
                        :field="field"
                        :initial-value="getAccountValue(field.name)"
                        :error="errors[field.name]"
                        @update:value="handleInputUpdate"
                    />
                </div>
            </div>

            <div class="flex items-center mt-2">
                <input
                    type="checkbox"
                    id="saveAccount"
                    v-model="saveAccount"
                    class="w-4 h-4 border-gray-600 rounded text-primary bg-dark-lighter focus:ring-primary/50"
                />
                <label for="saveAccount" class="ml-2 text-sm text-gray-300">
                    Save this account for later
                </label>
            </div>

            <div
                v-if="hasLoadedData"
                class="p-2 text-xs rounded-md text-secondary bg-secondary/10"
            >
                <span class="flex items-center">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-4 h-4 mr-1"
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
                    Account data loaded from previous session
                </span>
            </div>

            <div
                v-if="validationError"
                class="p-3 text-sm text-red-300 border border-red-800 rounded-md bg-red-900/20"
            >
                <span class="flex items-center">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-4 h-4 mr-1"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    {{ validationError }}
                </span>
            </div>
        </form>
    </CosmicCard>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import CosmicCard from "@/Components/Order/CosmicCard.vue";
import DynamicInput from "@/Components/Order/DynamicInput.vue";
import { useGameAccount } from "@/Composables/useGameAccount";
import { useAccountValidation } from "@/Composables/useAccountValidation";
import { useSavedAccounts } from "@/Composables/useSavedAccounts";
import { useToast } from "@/Composables/useToast";

const props = defineProps({
    inputFields: {
        type: Array,
        required: true,
    },
    produk: {
        type: Object,
        required: true,
    },
    validationError: {
        type: String,
        default: null,
    },
});

const emit = defineEmits([
    "update:account-data",
    "validation:error",
    "update:contact-data",
]);

const { accountData, saveAccount, updateAccountData, hasLoadedData } =
    useGameAccount(props.produk.slug);
const { validateInputFields } = useAccountValidation();
const { toast } = useToast();
const {
    getSavedAccounts,
    saveAccount: saveCookieAccount,
    loadSavedAccount,
    deleteSavedAccount: deleteAccount,
    getTimeAgo,
} = useSavedAccounts();

const errors = ref({});
const savedAccounts = ref([]);
const pulsingAccountId = ref(null);
const isMobile = ref(window.innerWidth < 640);

// Load saved accounts on component mount
onMounted(() => {
    loadSavedAccounts();
    window.addEventListener("resize", handleResize);

    // Initially emit the account data if we have saved data
    if (hasLoadedData.value) {
        emit("update:account-data", accountData.value);
    }
});

// Cleanup event listeners
const handleResize = () => {
    isMobile.value = window.innerWidth < 640;
};

// Load saved accounts from cookies
const loadSavedAccounts = () => {
    savedAccounts.value = getSavedAccounts(props.produk.slug);
};

// Get display name for quick access button
const getAccountDisplayName = (account) => {
    // Try to find a good identifier field (username, user_id, etc)
    const fields = account.fields;
    if (!fields) return "Account";

    const identifierFields = [
        "username",
        "user_id",
        "userid",
        "player_id",
        "account_id",
        "email",
    ];

    for (const field of identifierFields) {
        if (fields[field]) {
            return fields[field];
        }
    }

    // If no identifier fields found, use first non-empty field
    for (const [key, value] of Object.entries(fields)) {
        if (value) {
            return value;
        }
    }

    return "Account";
};

// Format field name for display in tooltip
const formatFieldName = (key) => {
    return key
        .replace(/_/g, " ")
        .replace(/([A-Z])/g, " $1")
        .replace(/^./, (str) => str.toUpperCase());
};

// Load saved account
const loadAccount = (account) => {
    if (!account || !account.fields) return;

    // Update all form fields
    for (const [key, value] of Object.entries(account.fields)) {
        updateAccountData(key, value);
    }

    // Pulse animation
    pulsingAccountId.value = account.id;
    setTimeout(() => {
        pulsingAccountId.value = null;
    }, 1500);

    // Show success notification
    toast.success("Account data loaded");

    // Update has loaded flag
    hasLoadedData.value = true;

    // Emit contact data if available
    if (account.contact) {
        emit("update:contact-data", account.contact);
    }

    // Emit account data update
    emit("update:account-data", accountData.value);
};

// Delete a saved account
const deleteSavedAccount = (id) => {
    deleteAccount(props.produk.slug, id);
    loadSavedAccounts();
    toast.success("Account removed");
};

// Clear all saved accounts
const clearAllSavedAccounts = () => {
    savedAccounts.value.forEach((account) => {
        deleteAccount(props.produk.slug, account.id);
    });
    loadSavedAccounts();
    toast.success("All saved accounts cleared");
};

const getAccountValue = (fieldName) => {
    return accountData.value[fieldName] || "";
};

const handleInputUpdate = ({ name, value }) => {
    updateAccountData(name, value);

    // Clear error for this field when user is typing
    if (errors.value[name]) {
        errors.value[name] = "";
    }

    emit("update:account-data", accountData.value);
};

// Validate all input fields
const validateFields = () => {
    const { isValid, errors: validationErrors } = validateInputFields(
        props.inputFields,
        accountData.value
    );
    errors.value = validationErrors;

    // Save account to cookie if checkbox is checked
    if (isValid && saveAccount.value) {
        saveCookieAccount(props.produk.slug, accountData.value);
        loadSavedAccounts(); // Refresh the saved accounts list
    }

    return isValid;
};

// Expose validateFields to parent
defineExpose({
    validateFields,
    accountData,
});

watch(
    accountData,
    (newData) => {
        emit("update:account-data", newData);
    },
    { deep: true }
);

// Watch for external validation errors
watch(
    () => props.validationError,
    (newError) => {
        if (newError) {
            // Focus first input if there's a validation error
            const firstInput = document.querySelector(".dynamic-input");
            if (firstInput) {
                firstInput.focus();
            }
        }
    }
);
</script>

<style scoped>
/* Cosmic account buttons styling */
.cosmic-accounts-container {
    scroll-behavior: smooth;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.cosmic-accounts-container::-webkit-scrollbar {
    display: none;
}

.cosmic-account-btn {
    position: relative;
    overflow: hidden;
    will-change: transform, box-shadow;
    transition: all 0.3s ease;
    box-shadow: 0 0 0px rgba(155, 135, 245, 0);
}

/* Meteor trail effect */
.meteor-trail {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(155, 135, 245, 0.2),
        rgba(51, 195, 240, 0.3),
        transparent
    );
    transform: skewX(-20deg);
    transition: left 0.3s ease;
    pointer-events: none;
}

.cosmic-account-btn:hover .meteor-trail {
    animation: meteor-fly 1s ease-in-out;
}

@keyframes meteor-fly {
    from {
        left: -100%;
    }
    to {
        left: 100%;
    }
}

/* Pulse animation when account is loaded */
.pulse-animation {
    animation: cosmic-pulse 1.5s ease;
}

@keyframes cosmic-pulse {
    0%,
    100% {
        box-shadow: 0 0 0 0 rgba(51, 195, 240, 0);
        transform: scale(1);
    }
    25% {
        box-shadow: 0 0 0 10px rgba(51, 195, 240, 0.4);
        transform: scale(1.03);
    }
    50% {
        box-shadow: 0 0 0 5px rgba(155, 135, 245, 0.3);
        transform: scale(1.02);
    }
    75% {
        box-shadow: 0 0 0 3px rgba(51, 195, 240, 0.2);
        transform: scale(1.01);
    }
}

/* Cosmic shadow for tooltip */
.shadow-cosmic {
    box-shadow: 0 4px 20px -2px rgba(51, 195, 240, 0.25),
        0 0 8px rgba(155, 135, 245, 0.3);
}

/* Quantum tunneling entrance effect for buttons */
@keyframes quantum-entrance {
    0% {
        transform: translateY(10px);
        opacity: 0;
        filter: blur(4px);
    }
    70% {
        transform: translateY(-2px);
        filter: blur(0);
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

.cosmic-account-btn {
    animation: quantum-entrance 0.4s ease-out;
    animation-fill-mode: backwards;
}

.cosmic-account-btn:nth-child(1) {
    animation-delay: 0.05s;
}
.cosmic-account-btn:nth-child(2) {
    animation-delay: 0.1s;
}
.cosmic-account-btn:nth-child(3) {
    animation-delay: 0.15s;
}
.cosmic-account-btn:nth-child(4) {
    animation-delay: 0.2s;
}
.cosmic-account-btn:nth-child(5) {
    animation-delay: 0.25s;
}

/* Respect reduced motion preference */
@media (prefers-reduced-motion: reduce) {
    .cosmic-account-btn,
    .meteor-trail,
    .pulse-animation {
        animation: none;
        transition: none;
    }

    .cosmic-account-btn:hover {
        transform: none;
    }
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .cosmic-account-btn {
        height: 2rem; /* h-8 */
        font-size: 0.75rem; /* text-xs */
        padding: 0.25rem 0.75rem; /* px-3 py-1 */
    }
}

@media (min-width: 641px) {
    .cosmic-account-btn {
        height: 2.5rem; /* h-10 */
        font-size: 0.875rem; /* text-sm */
        padding: 0.375rem 1rem; /* px-4 py-1.5 */
    }
}
</style>

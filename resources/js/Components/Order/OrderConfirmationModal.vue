<template>
    <div>
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center"
        >
            <!-- Backdrop -->
            <div class="fixed inset-0 transition-opacity" @click="cancelOrder">
                <div class="absolute inset-0 opacity-75 bg-dark-lighter"></div>
            </div>

            <!-- Modal Content -->
            <div
                class="z-10 w-11/12 max-w-md overflow-hidden transition-all transform rounded-lg bg-dark-card shadow-cosmic md:max-w-lg"
            >
                <!-- Modal Header -->
                <div
                    class="p-4 text-center border-b border-secondary/30 bg-primary/10"
                >
                    <h3 class="font-bold text-white">
                        {{
                            loading
                                ? "Verifying Account..."
                                : "Confirm Your Order"
                        }}
                    </h3>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="p-6 text-center">
                    <div class="inline-block w-16 h-16">
                        <svg
                            class="w-16 h-16 text-primary animate-spin"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                    </div>
                    <p class="mt-4 text-white">Verifying account details...</p>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="p-6">
                    <div class="p-4 mb-4 text-center rounded-lg bg-red-800/30">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="inline-block w-8 h-8 mb-2 text-red-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <p class="text-red-100">{{ error }}</p>
                    </div>
                    <div class="flex justify-center mt-6">
                        <button
                            @click="cancelOrder"
                            class="px-4 py-2 mr-2 font-medium text-white transition-colors rounded-lg bg-secondary hover:bg-secondary-hover focus:outline-none focus:ring-2 focus:ring-secondary/50"
                        >
                            Close
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div v-else class="p-6">
                    <!-- Account Summary -->
                    <div class="mb-6 space-y-3">
                        <div class="p-3 space-y-2 rounded-lg bg-primary/10">
                            <h4
                                class="flex items-center text-sm font-medium text-primary"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 mr-2"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12z"
                                        clip-rule="evenodd"
                                    />
                                    <path
                                        d="M10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 7a1 1 0 100-2 1 1 0 000 2z"
                                    />
                                </svg>
                                Account Details
                            </h4>
                            <div class="px-3 py-2 rounded-lg bg-dark-lighter">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="text-xs text-gray-400">
                                        Account ID:
                                    </div>
                                    <div
                                        class="text-xs font-semibold text-white"
                                    >
                                        {{ orderData.input_id }}
                                    </div>

                                    <div
                                        v-if="orderData.input_zone"
                                        class="text-xs text-gray-400"
                                    >
                                        Server ID:
                                    </div>
                                    <div
                                        v-if="orderData.input_zone"
                                        class="text-xs font-semibold text-white"
                                    >
                                        {{ orderData.input_zone }}
                                    </div>

                                    <div class="text-xs text-gray-400">
                                        Account Name:
                                    </div>
                                    <div
                                        class="text-xs font-semibold text-white"
                                    >
                                        <template
                                            v-if="
                                                orderSummary &&
                                                orderSummary.nickname
                                            "
                                        >
                                            {{ orderSummary.nickname }}
                                            <span
                                                class="inline-flex items-center ml-1 text-green-400"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="w-3 h-3"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </span>
                                        </template>
                                        <template
                                            v-else-if="
                                                orderSummary &&
                                                orderSummary.validation_error
                                            "
                                        >
                                            <span class="text-red-400">{{
                                                orderSummary.validation_error
                                            }}</span>
                                        </template>
                                        <template v-else>
                                            <span class="text-gray-400"
                                                >Not required</span
                                            >
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="p-3 space-y-2 rounded-lg bg-secondary/10">
                            <h4
                                class="flex items-center text-sm font-medium text-secondary"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 mr-2"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"
                                    />
                                    <path d="M7 2h6v4H7V2z" />
                                </svg>
                                Order Summary
                            </h4>
                            <div class="px-3 py-2 rounded-lg bg-dark-lighter">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="text-xs text-gray-400">
                                        Service:
                                    </div>
                                    <div
                                        class="text-xs font-semibold text-white"
                                    >
                                        {{ orderSummary?.layanan || "" }}
                                    </div>

                                    <div class="text-xs text-gray-400">
                                        Quantity:
                                    </div>
                                    <div
                                        class="text-xs font-semibold text-white"
                                    >
                                        {{
                                            orderSummary?.quantity ||
                                            orderData.quantity
                                        }}
                                    </div>

                                    <div class="text-xs text-gray-400">
                                        Base Price:
                                    </div>
                                    <div
                                        class="text-xs font-semibold text-white"
                                    >
                                        Rp
                                        {{
                                            formatPrice(
                                                orderSummary?.basePrice || 0
                                            )
                                        }}
                                    </div>

                                    <div
                                        v-if="orderSummary?.discount > 0"
                                        class="text-xs text-gray-400"
                                    >
                                        Discount:
                                    </div>
                                    <div
                                        v-if="orderSummary?.discount > 0"
                                        class="text-xs font-semibold text-green-400"
                                    >
                                        -Rp
                                        {{
                                            formatPrice(
                                                orderSummary?.discount || 0
                                            )
                                        }}
                                    </div>

                                    <div class="text-xs text-gray-400">
                                        Payment:
                                    </div>
                                    <div
                                        class="text-xs font-semibold text-white"
                                    >
                                        {{ orderSummary?.payment_method || "" }}
                                    </div>

                                    <div
                                        v-if="orderSummary?.payment_fee > 0"
                                        class="text-xs text-gray-400"
                                    >
                                        Fee:
                                    </div>
                                    <div
                                        v-if="orderSummary?.payment_fee > 0"
                                        class="text-xs font-semibold text-orange-400"
                                    >
                                        +Rp
                                        {{
                                            formatPrice(
                                                orderSummary?.payment_fee || 0
                                            )
                                        }}
                                    </div>

                                    <div
                                        class="text-xs font-medium text-gray-400"
                                    >
                                        Total Price:
                                    </div>
                                    <div class="text-xs font-bold text-primary">
                                        Rp
                                        {{
                                            formatPrice(
                                                orderSummary?.final_price || 0
                                            )
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="p-3 space-y-2 rounded-lg bg-primary/5">
                            <h4
                                class="flex items-center text-sm font-medium text-gray-300"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 mr-2"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        d="M2 3a1 1 0 011-1h14a1 1 0 011 1v14a1 1 0 01-1 1H3a1 1 0 01-1-1V3z"
                                    />
                                    <path d="M10 8a2 2 0 100-4 2 2 0 000 4z" />
                                    <path
                                        d="M10 8a2 2 0 100-4 2 2 0 000 4zm1 6a1 1 0 112 0v1a1 1 0 11-2 0v-1z"
                                    />
                                </svg>
                                Contact Information
                            </h4>
                            <div class="px-3 py-2 rounded-lg bg-dark-lighter">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="text-xs text-gray-400">
                                        WhatsApp:
                                    </div>
                                    <div
                                        class="text-xs font-semibold text-white"
                                    >
                                        {{ orderData.phone }}
                                    </div>

                                    <div
                                        v-if="orderData.email"
                                        class="text-xs text-gray-400"
                                    >
                                        Email:
                                    </div>
                                    <div
                                        v-if="orderData.email"
                                        class="text-xs font-semibold text-white"
                                    >
                                        {{ orderData.email }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between space-x-3">
                        <button
                            @click="cancelOrder"
                            class="flex-1 px-4 py-2 font-medium text-gray-300 transition-colors border border-gray-600 rounded-lg hover:bg-gray-700/30 focus:outline-none focus:ring-2 focus:ring-gray-500/50"
                        >
                            Cancel
                        </button>

                        <button
                            @click="confirmOrder"
                            :disabled="processingOrder"
                            :class="[
                                'flex-1 px-4 py-2 font-medium text-white transition-colors rounded-lg focus:outline-none focus:ring-2',
                                processingOrder
                                    ? 'bg-primary/50 cursor-not-allowed'
                                    : 'bg-primary hover:bg-primary-hover focus:ring-primary/50',
                            ]"
                        >
                            <template v-if="processingOrder">
                                <svg
                                    class="inline-block w-4 h-4 mr-1 text-white animate-spin"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Processing...
                            </template>
                            <template v-else> Confirm Order </template>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useToast } from "@/Composables/useToast";
import axios from "axios";

const props = defineProps({
    showModal: Boolean,
    orderData: Object,
});

const emit = defineEmits(["close", "confirmed"]);

const { toast } = useToast();
const loading = ref(true);
const error = ref(null);
const orderSummary = ref(null);
const processingOrder = ref(false);

watch(
    () => props.showModal,
    (show) => {
        if (show) {
            error.value = null;
            loading.value = true;
            orderSummary.value = null;
            validateOrder();
        }
    }
);

const validateOrder = async () => {
    try {
        const response = await axios.post(
            route("order.confirm"),
            props.orderData
        );

        if (response.data.status === "success") {
            orderSummary.value = response.data.orderSummary;
        } else {
            error.value = response.data.message || "An unknown error occurred";
        }
    } catch (err) {
        console.error("Order validation error:", err);
        error.value = err.response?.data?.message || "Failed to validate order";
    } finally {
        loading.value = false;
    }
};

const formatPrice = (price) => {
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

const cancelOrder = () => {
    emit("close");
};

const confirmOrder = async () => {
    if (processingOrder.value) return;

    processingOrder.value = true;
    try {
        const response = await axios.post(
            route("order.process"),
            props.orderData
        );

        if (response.data.status === "success") {
            toast.success("Order processed successfully!");
            emit("confirmed", response.data);

            // Handle redirect if needed
            if (response.data.redirect && response.data.payment_url) {
                window.location.href = response.data.payment_url;
            }
        } else {
            toast.error(response.data.message || "An unknown error occurred");
        }
    } catch (err) {
        console.error("Order processing error:", err);
        toast.error(err.response?.data?.message || "Failed to process order");
    } finally {
        processingOrder.value = false;
        emit("close");
    }
};
</script>

<style scoped>
.shadow-cosmic {
    box-shadow: 0 0 16px 0 rgba(155, 135, 245, 0.15),
        0 0 8px 0 rgba(51, 195, 240, 0.2);
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}
</style>

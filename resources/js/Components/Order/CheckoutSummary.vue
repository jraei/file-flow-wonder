<template>
    <CosmicCard :title="'Order Summary'">
        <div class="space-y-4">
            <!-- Order Summary -->
            <div class="space-y-3 rounded-lg">
                <h3 class="text-sm font-medium text-secondary">
                    Selected Service
                </h3>

                <!-- Selected Service Details -->
                <div v-if="selectedService" class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">Service:</span>
                        <span class="text-sm font-semibold text-white">{{
                            selectedService.nama_layanan
                        }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">Quantity:</span>
                        <span class="text-sm font-semibold text-white"
                            >{{ quantity }}x</span
                        >
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">Base Price:</span>
                        <span class="text-sm font-semibold text-white"
                            >Rp {{ formatPrice(basePrice) }}</span
                        >
                    </div>
                </div>
                <div v-else>
                    <div
                        class="flex items-center px-4 py-3 rounded-lg bg-primary/10"
                    >
                        <span class="text-sm text-primary"
                            >Please select a service first</span
                        >
                    </div>
                </div>

                <!-- Voucher Discount -->
                <div
                    v-if="voucher && selectedService"
                    class="flex items-center justify-between"
                >
                    <span class="flex items-center text-xs text-gray-400">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4 mr-1 text-green-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M5 2a2 2 0 00-2 2v14l3.5-2 3.5 2 3.5-2 3.5 2V4a2 2 0 00-2-2H5zm2.5 3a1.5 1.5 0 100 3 1.5 1.5 0 000-3zm6.207.293a1 1 0 00-1.414 0l-6 6a1 1 0 101.414 1.414l6-6a1 1 0 000-1.414z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        Voucher Discount:
                    </span>
                    <span class="text-sm font-semibold text-green-400"
                        >-Rp {{ formatPrice(voucherDiscount) }}</span
                    >
                </div>

                <!-- Payment Method -->
                <div
                    v-if="selectedService && selectedPayment"
                    class="flex items-center justify-between"
                >
                    <span class="text-xs text-gray-400">Payment Method:</span>
                    <span class="text-sm font-semibold text-white">{{
                        paymentInfo?.methodLabel || "Not selected"
                    }}</span>
                </div>

                <!-- Payment Fee -->
                <div
                    v-if="selectedService && paymentInfo && paymentInfo.fee > 0"
                    class="flex items-center justify-between"
                >
                    <span class="text-xs text-gray-400">Payment Fee:</span>
                    <span class="text-sm font-semibold text-orange-400"
                        >+Rp {{ formatPrice(paymentInfo.fee) }}</span
                    >
                </div>

                <!-- Total -->
                <div
                    v-if="selectedService"
                    class="pt-2 mt-2 border-t border-gray-700"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-300"
                            >Total:</span
                        >
                        <span class="text-lg font-bold text-primary"
                            >Rp {{ formatPrice(totalPrice) }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Contact Info -->
            <div
                v-if="selectedService && contact && contact.phone"
                class="p-4 space-y-2 border rounded-lg bg-primary/10 border-secondary/20"
            >
                <h3 class="text-sm font-medium text-primary">
                    Contact Information
                </h3>

                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">WhatsApp:</span>
                    <span class="text-sm font-semibold text-white">{{
                        contact.phone
                    }}</span>
                </div>

                <div
                    v-if="contact.email"
                    class="flex items-center justify-between"
                >
                    <span class="text-xs text-gray-400">Email:</span>
                    <span class="text-sm font-semibold text-white">{{
                        contact.email
                    }}</span>
                </div>
            </div>

            <!-- Call to Action -->
            <button
                @click="handleOrderProcess"
                :disabled="!isOrderReady || isValidating"
                :class="[
                    'w-full py-3 font-bold transition-all rounded-lg focus:outline-none focus:ring-2 relative overflow-hidden',
                    isOrderReady && !isValidating
                        ? 'bg-primary text-white hover:bg-primary-hover focus:ring-primary/50 shadow-glow-primary'
                        : 'bg-gray-600/50 text-gray-400 cursor-not-allowed',
                ]"
            >
                <div class="relative z-10 flex items-center justify-center">
                    <svg
                        v-if="isValidating"
                        class="w-5 h-5 mr-2 -ml-1 text-white animate-spin"
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
                    {{
                        isValidating ? "Validating Account..." : "Process Order"
                    }}
                    <svg
                        v-if="!isOrderReady && !isValidating"
                        xmlns="http://www.w3.org/2000/svg"
                        class="inline-block w-4 h-4 ml-1"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
                <div
                    class="absolute inset-0 transition-transform duration-700 bg-gradient-to-r from-primary/0 via-secondary/20 to-primary/0 cosmic-shine"
                ></div>
            </button>
        </div>
    </CosmicCard>
</template>

<script setup>
import { ref, computed } from "vue";
import CosmicCard from "@/Components/Order/CosmicCard.vue";
import { useToast } from "@/Composables/useToast";

const props = defineProps({
    produk: Object,
    "min-price": [String, Number],
    selectedService: Object,
    quantity: {
        type: Number,
        default: 1,
    },
    selectedPayment: [Object, String, Number, null],
    paymentInfo: Object,
    contact: Object,
    voucher: Object,
    isValidating: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["checkout", "openModal"]);
const { toast } = useToast();

const basePrice = computed(() => {
    if (!props.selectedService) return 0;
    let value =
        props.selectedService.harga_flashsale ??
        props.selectedService.harga_jual;
    return Math.ceil(value);
});

// Computed values
const price = computed(() => {
    if (!props.selectedService) return 0;
    let value = basePrice.value * props.quantity;
    return Math.ceil(value);
});

const voucherDiscount = computed(() => {
    if (!props.voucher || !props.selectedService) return 0;

    let discount = 0;

    // Fixed amount discount
    if (props.voucher.discount_type === "fixed") {
        discount = props.voucher.discount_value;
    } else {
        // Percentage discount
        discount = (price.value * props.voucher.discount_value) / 100;

        // Apply max discount cap if exists
        if (
            props.voucher.max_discount &&
            discount > props.voucher.max_discount
        ) {
            discount = props.voucher.max_discount;
        }
    }

    // Don't allow discount to exceed the base price
    return Math.min(discount, price.value);
});

const totalPrice = computed(() => {
    if (!props.selectedService) return 0;

    // Start with base price
    let total = price.value;

    // Subtract voucher discount if applicable
    if (props.voucher) {
        total -= voucherDiscount.value;
    }

    // Add payment fee if applicable
    if (props.paymentInfo) {
        const {
            fee_fixed = 0,
            fee_percent = 0,
            feeType = "none",
        } = props.paymentInfo;

        if (feeType === "fixed") {
            total += fee_fixed;
        } else if (feeType === "percent") {
            total += total * (fee_percent / 100);
        } else if (feeType === "mixed") {
            total += fee_fixed + total * (fee_percent / 100);
        }
    }

    return Math.ceil(total); // Round up to nearest integer
});

const isOrderReady = computed(() => {
    return (
        props.selectedService &&
        props.selectedPayment &&
        props.contact &&
        props.contact.phone &&
        props.contact.phone.length >= 7
    );
});

// Methods
const formatPrice = (price) => {
    let priceRounded = Math.ceil(price);
    return priceRounded.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

const handleOrderProcess = () => {
    console.log(props.selectedService);

    if (!isOrderReady.value) {
        // Check what's missing and show appropriate message
        if (!props.selectedService) {
            toast.error("Please select a service first");
        } else if (!props.selectedPayment) {
            toast.error("Please select a payment method");
        } else if (!props.contact?.phone || props.contact.phone.length < 7) {
            toast.error("Please enter a valid WhatsApp number");
        }
        return;
    }

    if (props.isValidating) {
        toast.info("Please wait while we validate your account");
        return;
    }

    // Prepare order data for confirmation
    const data = {
        layanan_id: props.selectedService.id,
        quantity: props.quantity,
        payment_method: props.selectedPayment,
        email: props.contact.email,
        phone: props.contact.phone,
        voucher_code: props.voucher?.code || null,
    };

    if (props.selectedService.flashSaleItem) {
        data.flashsale_item_id = props.selectedService.flashSaleItem.id;
    }

    // Emit event for account validation and modal opening
    emit("openModal", data);
};
</script>

<style scoped>
.shadow-glow-primary {
    box-shadow: 0 0 15px -2px rgba(155, 135, 245, 0.4);
}

.cosmic-shine {
    opacity: 0;
    transform: translateX(-100%);
}

button:not(:disabled):hover .cosmic-shine {
    opacity: 1;
    transform: translateX(100%);
}
</style>

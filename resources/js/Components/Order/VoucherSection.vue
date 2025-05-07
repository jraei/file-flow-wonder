<template>
    <CosmicCard title="Promo Code" :step-number="5">
        <div class="space-y-4">
            <!-- Input group -->
            <div class="flex w-full">
                <div class="relative flex-grow mr-2">
                    <input
                        type="text"
                        v-model="voucherCode"
                        placeholder="Enter promo code"
                        class="w-full px-4 py-2 border rounded-lg outline-none bg-secondary/20 text-primary_text focus:ring-2 focus:border-primary focus:bg-secondary/20/90 border-secondary placeholder-secondary"
                        :class="{ 'border-red-500': voucherError }"
                    />

                    <!-- Success animation -->
                    <div
                        v-if="voucherSuccess"
                        class="absolute inset-0 overflow-hidden pointer-events-none"
                    >
                        <div
                            v-for="i in 20"
                            :key="`success-${i}`"
                            class="absolute h-0.5 w-10 bg-secondary opacity-0"
                            :style="{
                                top: `${Math.random() * 100}%`,
                                left: `${Math.random() * 100}%`,
                                transform: `rotate(${Math.random() * 360}deg)`,
                                animationDelay: `${Math.random() * 0.5}s`,
                                animationDuration: `${
                                    Math.random() * 0.5 + 0.5
                                }s`,
                            }"
                            :class="{
                                'animate-success-particle': voucherSuccess,
                            }"
                        ></div>
                    </div>

                    <!-- Error animation -->
                    <div
                        v-if="voucherError"
                        class="absolute inset-0 overflow-hidden pointer-events-none"
                    >
                        <div
                            class="absolute h-0.5 w-20 bg-red-500 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 animate-meteor-burn"
                        ></div>
                    </div>

                    <div v-if="voucherError" class="mt-1 text-xs text-red-500">
                        {{ errorMessage }}
                    </div>
                </div>

                <button
                    @click="applyVoucher"
                    class="px-4 py-2 border-primary border bg-primary text-white rounded-lg hover:bg-primary/80 transition-colors min-w-[100px]"
                >
                    Apply
                </button>
            </div>

            <!-- Available promos button -->
            <button
                @click="showVoucherModal = true"
                class="flex items-center justify-center w-full px-4 py-2 space-x-2 transition-colors border rounded-lg border-secondary/50 text-secondary hover:bg-secondary/10"
            >
                <span
                    class="inline-block w-4 h-4 rounded-full bg-secondary/20 animate-ping-small"
                ></span>
                <span>Available Promos</span>
            </button>
        </div>

        <!-- Voucher Modal -->
        <Modal
            :show="showVoucherModal"
            @close="showVoucherModal = false"
            max-width="2xl"
        >
            <div class="p-6 bg-dark-lighter">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-white">
                        Available Promo Codes
                    </h2>
                    <button
                        @click="showVoucherModal = false"
                        class="text-gray-400 hover:text-white"
                    >
                        &times;
                    </button>
                </div>

                <div
                    v-if="activeVouchers.length"
                    class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[60vh] overflow-y-auto p-1"
                >
                    <VoucherCard
                        v-for="voucher in activeVouchers"
                        :key="voucher.code"
                        :voucher="voucher"
                        :total-amount="totalAmount"
                        @apply-voucher="applyFromModal"
                    />
                </div>
                <div v-else class="py-8 text-center text-gray-400">
                    No active promotions available at this time.
                </div>
            </div>
        </Modal>
    </CosmicCard>
</template>

<script setup>
import { ref, watch } from "vue";
import CosmicCard from "@/Components/Order/CosmicCard.vue";
import Modal from "@/Components/Modal.vue";
import VoucherCard from "@/Components/Order/VoucherCard.vue";
import { useToast } from "@/Composables/useToast";

const props = defineProps({
    activeVouchers: {
        type: Array,
        default: () => [],
    },
    totalAmount: {
        type: Number,
        default: 0,
    },
    selectedService: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["update:voucher"]);

const { toast } = useToast();
const voucherCode = ref("");
const showVoucherModal = ref(false);
const voucherSuccess = ref(false);
const voucherError = ref(false);
const errorMessage = ref("");

const applyVoucher = () => {
    if (!voucherCode.value) {
        showError("Please enter a promo code");
        return;
    }

    const voucher = props.activeVouchers.find(
        (v) => v.code.toLowerCase() === voucherCode.value.toLowerCase()
    );

    if (!voucher) {
        showError("Invalid promo code");
        return;
    }

    if (voucher.min_purchase > props.totalAmount) {
        showError(
            `Minimum purchase of Rp ${voucher.min_purchase.toLocaleString()} required`
        );
        return;
    }

    if (
        voucher.usage_limit !== null &&
        voucher.usage_count >= voucher.usage_limit
    ) {
        showError("Usage limit reached for this voucher");
        return;
    }

    // Success case
    voucherSuccess.value = true;
    voucherError.value = false;
    emit("update:voucher", voucher);
    toast.success(`Promo code "${voucher.code}" applied!`);

    // Reset success animation after a delay
    setTimeout(() => {
        voucherSuccess.value = false;
    }, 2000);
};

const applyFromModal = (code) => {
    voucherCode.value = code;
    showVoucherModal.value = false;
    applyVoucher();
};

const showError = (message) => {
    errorMessage.value = message;
    voucherError.value = true;
    voucherSuccess.value = false;

    // Reset error state after a delay
    setTimeout(() => {
        voucherError.value = false;
    }, 2000);
};

// Reset any error/success states when input changes
watch(voucherCode, () => {
    voucherError.value = false;
    voucherSuccess.value = false;
});
</script>

<style scoped>
.animate-success-particle {
    animation: success-particle 0.8s ease-out forwards;
}

@keyframes success-particle {
    0% {
        transform: scale(0) rotate(0deg);
        opacity: 0;
    }
    50% {
        opacity: 0.8;
    }
    100% {
        transform: scale(1) rotate(360deg);
        opacity: 0;
    }
}

.animate-meteor-burn {
    animation: meteor-burn 0.8s ease-out forwards;
}

@keyframes meteor-burn {
    0% {
        transform: translateX(-100%) translateY(-100%) rotate(45deg);
        opacity: 0;
        width: 0;
    }
    10% {
        opacity: 1;
    }
    70% {
        width: 100%;
    }
    100% {
        transform: translateX(100%) translateY(100%) rotate(45deg);
        opacity: 0;
        width: 0;
    }
}
</style>

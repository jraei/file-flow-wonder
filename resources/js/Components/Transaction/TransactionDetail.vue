<template>
    <div class="relative mt-8 overflow-hidden">
        <!-- Background effects -->
        <div
            class="absolute inset-0 bg-gradient-to-br from-primary/5 to-secondary/5 rounded-xl"
        ></div>

        <div class="relative p-6 bg-dark-card/70 rounded-xl backdrop-blur-sm">
            <!-- Header with status -->
            <div
                class="flex flex-col items-start justify-between gap-4 mb-6 sm:flex-row sm:items-center"
            >
                <div>
                    <h3 class="text-xl font-bold text-white">
                        Transaction Details
                    </h3>
                    <p class="text-sm text-gray-400">
                        {{ transaction.order_id }}
                    </p>
                </div>

                <div class="flex items-center space-x-3">
                    <span
                        class="px-3 py-1 text-sm font-medium border rounded-full"
                        :class="
                            statusClasses[transaction.status] ||
                            statusClasses.pending
                        "
                    >
                        <span
                            v-if="
                                transaction.status === 'pending' ||
                                transaction.status === 'processing'
                            "
                            class="inline-block h-2 w-2 rounded-full mr-1.5"
                            :class="
                                transaction.status === 'pending'
                                    ? 'bg-secondary animate-pulse'
                                    : 'bg-primary animate-pulse'
                            "
                        ></span>
                        {{
                            transaction.status.charAt(0).toUpperCase() +
                            transaction.status.slice(1)
                        }}
                    </span>

                    <button
                        @click="refreshTransaction"
                        class="p-2 text-white transition-colors rounded-full bg-dark-lighter/50 hover:bg-dark-lighter/80"
                        :disabled="isLoading"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4"
                            :class="{ 'animate-spin': isLoading }"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Left column: Game info -->
                <div class="space-y-6">
                    <!-- Game info card -->
                    <div class="p-4 space-y-4 rounded-lg bg-dark-lighter/20">
                        <h4 class="text-sm font-medium text-gray-400 uppercase">
                            Game Information
                        </h4>

                        <div class="flex items-center gap-3">
                            <div
                                v-if="transaction.layanan?.produk?.thumbnail"
                                class="flex-shrink-0 w-12 h-12 overflow-hidden rounded-lg bg-dark-lighter"
                            >
                                <img
                                    :src="transaction.layanan.produk.thumbnail"
                                    class="object-cover w-full h-full"
                                    :alt="transaction.layanan.produk.nama"
                                />
                            </div>

                            <div>
                                <h5 class="font-medium text-white">
                                    {{
                                        transaction.layanan?.produk?.nama ||
                                        "Unknown Game"
                                    }}
                                </h5>
                                <p class="text-sm text-gray-400">
                                    {{
                                        transaction.layanan?.nama_layanan || "-"
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Player ID info -->
                        <div class="grid grid-cols-2 text-sm gap-y-3">
                            <div class="text-gray-400">Game ID</div>
                            <div class="font-medium text-white">
                                {{ transaction.input_id }}
                            </div>

                            <template v-if="transaction.input_zone">
                                <div class="text-gray-400">Server/Zone</div>
                                <div class="font-medium text-white">
                                    {{ transaction.input_zone }}
                                </div>
                            </template>

                            <template v-if="transaction.nickname">
                                <div class="text-gray-400">Nickname</div>
                                <div class="font-medium text-white">
                                    {{ transaction.nickname }}
                                </div>
                            </template>

                            <div class="text-gray-400">Quantity</div>
                            <div class="font-medium text-white">
                                {{ transaction.quantity }}
                            </div>
                        </div>
                    </div>

                    <!-- Date info with celestial clock -->
                    <div class="p-4 space-y-4 rounded-lg bg-dark-lighter/20">
                        <h4 class="text-sm font-medium text-gray-400 uppercase">
                            Transaction Timeline
                        </h4>

                        <!-- Celestial Clock -->
                        <div class="relative w-24 h-24 mx-auto mb-4">
                            <!-- Orbit rings -->
                            <div
                                class="absolute inset-0 border rounded-full border-gray-700/30"
                            ></div>
                            <div
                                class="absolute inset-[5px] border border-gray-700/20 rounded-full"
                            ></div>
                            <div
                                class="absolute inset-[10px] border border-gray-700/10 rounded-full"
                            ></div>

                            <!-- Center star -->
                            <div
                                class="absolute inset-0 flex items-center justify-center"
                            >
                                <div
                                    class="w-4 h-4 rounded-full bg-primary glow-primary"
                                ></div>
                            </div>

                            <!-- Orbit planets -->
                            <div class="absolute inset-0">
                                <div class="celestial-orbit-fast">
                                    <div
                                        class="absolute w-2 h-2 transform -translate-x-1/2 rounded-full bg-secondary -top-1 left-1/2"
                                    ></div>
                                </div>

                                <div class="celestial-orbit-medium">
                                    <div
                                        class="h-3 w-3 bg-green-500/70 rounded-full absolute -top-1.5 left-1/2 transform -translate-x-1/2"
                                    ></div>
                                </div>

                                <div class="celestial-orbit-slow">
                                    <div
                                        class="h-2.5 w-2.5 bg-red-500/70 rounded-full absolute -top-1.5 left-1/2 transform -translate-x-1/2"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="flex items-center text-gray-400">
                                    <span
                                        class="inline-block h-1.5 w-1.5 bg-secondary rounded-full mr-1.5"
                                    ></span>
                                    Created
                                </span>
                                <span class="text-white">{{
                                    formatDate(transaction.created_at)
                                }}</span>
                            </div>

                            <template v-if="transaction.pembayaran">
                                <div class="flex items-center justify-between">
                                    <span
                                        class="flex items-center text-gray-400"
                                    >
                                        <span
                                            class="inline-block h-1.5 w-1.5 bg-green-500 rounded-full mr-1.5"
                                        ></span>
                                        Payment Due
                                    </span>
                                    <span
                                        :class="{
                                            'text-white': !hasExpired,
                                            'text-red-400': hasExpired,
                                        }"
                                    >
                                        {{
                                            formatDate(
                                                transaction.pembayaran
                                                    .expired_time
                                            )
                                        }}
                                    </span>
                                </div>
                            </template>

                            <template
                                v-if="
                                    transaction.updated_at !==
                                    transaction.created_at
                                "
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="flex items-center text-gray-400"
                                    >
                                        <span
                                            class="inline-block h-1.5 w-1.5 bg-red-500 rounded-full mr-1.5"
                                        ></span>
                                        Last Updated
                                    </span>
                                    <span class="text-white">{{
                                        formatDate(transaction.updated_at)
                                    }}</span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Right column: Payment -->
                <div class="space-y-6">
                    <!-- Price breakdown -->
                    <div class="p-4 space-y-4 rounded-lg bg-dark-lighter/20">
                        <h4 class="text-sm font-medium text-gray-400 uppercase">
                            Price Breakdown
                        </h4>

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Price</span>
                                <span class="text-white">{{
                                    formatPrice(transaction.price)
                                }}</span>
                            </div>

                            <template v-if="transaction.quantity > 1">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Quantity</span>
                                    <span class="text-white"
                                        >x{{ transaction.quantity }}</span
                                    >
                                </div>
                            </template>

                            <template v-if="transaction.discount > 0">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Discount</span>
                                    <span class="text-green-400"
                                        >-{{
                                            formatPrice(transaction.discount)
                                        }}</span
                                    >
                                </div>
                            </template>

                            <template v-if="transaction.pembayaran?.fee">
                                <div class="flex justify-between">
                                    <span class="text-gray-400"
                                        >Payment Fee</span
                                    >
                                    <span class="text-white">{{
                                        formatPrice(transaction.pembayaran.fee)
                                    }}</span>
                                </div>
                            </template>

                            <div class="my-2 border-t border-gray-700/50"></div>

                            <div class="flex justify-between font-medium">
                                <span class="text-white">Total</span>
                                <span class="text-base text-primary">
                                    {{
                                        formatPrice(
                                            transaction.pembayaran
                                                ?.total_price ||
                                                transaction.total_price
                                        )
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment method -->
                    <template
                        v-if="
                            transaction.status === 'pending' &&
                            transaction.pembayaran
                        "
                    >
                        <div
                            class="p-4 space-y-4 rounded-lg bg-dark-lighter/20"
                        >
                            <div class="flex items-center justify-between">
                                <h4
                                    class="text-sm font-medium text-gray-400 uppercase"
                                >
                                    Payment Method
                                </h4>

                                <!-- Countdown timer -->
                                <div
                                    v-if="
                                        !hasExpired &&
                                        transaction.pembayaran?.expired_time
                                    "
                                    :class="[
                                        'px-3 py-1 text-xs rounded-full font-mono font-medium',
                                        isWarningTime
                                            ? 'bg-red-500/20 text-red-400 border border-red-500/50'
                                            : 'bg-dark-lighter/80 text-gray-300',
                                    ]"
                                >
                                    <span
                                        v-if="isWarningTime"
                                        class="inline-block h-1.5 w-1.5 rounded-full bg-red-500 animate-pulse mr-1.5"
                                    ></span>
                                    {{
                                        remainingTime.hours
                                            .toString()
                                            .padStart(2, "0")
                                    }}:{{
                                        remainingTime.minutes
                                            .toString()
                                            .padStart(2, "0")
                                    }}:{{
                                        remainingTime.seconds
                                            .toString()
                                            .padStart(2, "0")
                                    }}
                                </div>

                                <div
                                    v-else-if="hasExpired"
                                    class="px-3 py-1 text-xs font-medium text-red-400 border rounded-full bg-red-500/20 border-red-500/50"
                                >
                                    EXPIRED
                                </div>
                            </div>

                            <!-- Payment Method Info -->
                            <div class="flex flex-col items-center space-y-4">
                                <div
                                    class="bg-white p-3 rounded-lg w-full max-w-[200px] flex items-center justify-center"
                                    :class="{ 'opacity-50': hasExpired }"
                                >
                                    <img
                                        v-if="
                                            transaction.pembayaran
                                                .pay_method_logo
                                        "
                                        :src="
                                            transaction.pembayaran
                                                .pay_method_logo
                                        "
                                        class="object-contain h-10"
                                        :alt="
                                            transaction.pembayaran
                                                .pay_method_name ||
                                            'Payment Method'
                                        "
                                    />
                                    <span
                                        v-else
                                        class="font-medium text-gray-800"
                                        >{{
                                            transaction.pembayaran
                                                .pay_method_name ||
                                            "Unknown Method"
                                        }}</span
                                    >
                                </div>

                                <div
                                    class="flex flex-col items-center w-full gap-2"
                                >
                                    <template
                                        v-if="
                                            transaction.pembayaran.payment_link
                                        "
                                    >
                                        <button
                                            @click="openPaymentLink"
                                            class="w-full max-w-xs px-4 py-2 text-sm font-medium text-white transition-colors rounded-lg bg-primary hover:bg-primary/80 disabled:opacity-50 disabled:cursor-not-allowed"
                                            :disabled="hasExpired"
                                        >
                                            Open Payment Link
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { router } from "@inertiajs/vue3";
import { useToast } from "@/Composables/useToast";

const props = defineProps({
    transaction: {
        type: Object,
        required: true,
    },
    serverTime: {
        type: String,
        required: true,
    },
});

const { toast } = useToast();
const isLoading = ref(false);
const timeDiff = ref(0);
const countdownInterval = ref(null);
const remainingTime = ref({
    hours: 0,
    minutes: 0,
    seconds: 0,
});

const statusClasses = {
    pending: "bg-secondary/20 text-secondary border-secondary",
    processing: "bg-primary/20 text-primary border-primary",
    completed: "bg-green-500/20 text-green-500 border-green-500",
    failed: "bg-red-500/20 text-red-500 border-red-500",
    cancelled: "bg-gray-500/20 text-gray-400 border-gray-400",
};

const formatDate = (dateString) => {
    if (!dateString) return "-";

    const date = new Date(dateString);
    date.setTime(date.getTime() + timeDiff.value);

    return new Intl.DateTimeFormat("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        day: "2-digit",
        month: "short",
        year: "numeric",
    }).format(date);
};

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price);
};

const hasExpired = computed(() => {
    if (!props.transaction.pembayaran?.expired_time) return false;

    const expiredTime = new Date(props.transaction.pembayaran.expired_time);
    const now = new Date();
    now.setTime(now.getTime() + timeDiff.value);

    return now > expiredTime;
});

const isWarningTime = computed(() => {
    // Less than 15 minutes
    return remainingTime.value.hours === 0 && remainingTime.value.minutes < 15;
});

const updateCountdown = () => {
    if (!props.transaction.pembayaran?.expired_time) return;

    const expiredTime = new Date(props.transaction.pembayaran.expired_time);
    const now = new Date();
    now.setTime(now.getTime() + timeDiff.value);

    let diff = expiredTime - now;

    if (diff <= 0) {
        // Time has expired
        remainingTime.value = { hours: 0, minutes: 0, seconds: 0 };
        clearInterval(countdownInterval.value);
        return;
    }

    // Calculate hours, minutes, seconds
    const hours = Math.floor(diff / (1000 * 60 * 60));
    diff -= hours * (1000 * 60 * 60);

    const minutes = Math.floor(diff / (1000 * 60));
    diff -= minutes * (1000 * 60);

    const seconds = Math.floor(diff / 1000);

    remainingTime.value = { hours, minutes, seconds };
};

const openPaymentLink = () => {
    if (!props.transaction.pembayaran?.payment_link) return;

    window.open(props.transaction.pembayaran.payment_link, "_blank");
};

const refreshTransaction = () => {
    if (isLoading.value) return;

    isLoading.value = true;
    router.get(
        "/cek-transaksi",
        {
            invoice: props.transaction.order_id,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                isLoading.value = false;
                toast.success("Transaction refreshed");
            },
            onError: () => {
                isLoading.value = false;
                toast.error("Failed to refresh transaction");
            },
        }
    );
};

onMounted(() => {
    // Calculate time difference between server and client
    timeDiff.value =
        new Date().getTime() - new Date(props.serverTime).getTime();

    // Start countdown
    updateCountdown();
    countdownInterval.value = setInterval(updateCountdown, 1000);
});

onBeforeUnmount(() => {
    if (countdownInterval.value) {
        clearInterval(countdownInterval.value);
    }
});
</script>

<style scoped>
.glow-primary {
    box-shadow: 0 0 15px 3px rgba(155, 135, 245, 0.5);
    animation: glow 2s ease-in-out infinite;
}

@keyframes glow {
    0%,
    100% {
        box-shadow: 0 0 10px 2px rgba(155, 135, 245, 0.5);
    }
    50% {
        box-shadow: 0 0 20px 5px rgba(155, 135, 245, 0.7);
    }
}

.celestial-orbit-fast {
    position: absolute;
    inset: 0;
    border-radius: 9999px;
    animation: orbit 10s linear infinite;
}

.celestial-orbit-medium {
    position: absolute;
    inset: 5px;
    border-radius: 9999px;
    animation: orbit 16s linear infinite;
}

.celestial-orbit-slow {
    position: absolute;
    inset: 10px;
    border-radius: 9999px;
    animation: orbit 25s linear infinite;
}

@keyframes orbit {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>

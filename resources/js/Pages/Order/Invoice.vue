<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import CosmicParticles from "@/Components/User/Flashsale/CosmicParticles.vue";
import { useToast } from "@/Composables/useToast";
import { CircleCheck, Download, AlertCircle, Clock } from "lucide-vue-next";

const props = defineProps({
    order: Object,
    payment: Object,
    product: Object,
    user: Object,
});

const { toast } = useToast();

// For countdown timer
const timeLeft = ref({
    days: 0,
    hours: 0,
    minutes: 0,
    seconds: 0,
    expired: false,
});

const countdownInterval = ref(null);
const isExpiringSoon = computed(() => {
    // Less than 15 minutes
    return (
        timeLeft.value.hours === 0 &&
        timeLeft.value.minutes < 15 &&
        !timeLeft.value.expired
    );
});

// Timeline status mapping
const statusMapping = {
    pending: 1, // Payment Pending
    processing: 2, // Processing
    completed: 3, // Completed
    failed: 3, // Final state (same position as completed but different display)
    cancelled: 3, // Final state (same position as completed but different display)
};

const currentStage = computed(() => {
    return statusMapping[props.order?.status] || 0; // Default to 0 if status not found
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const updateCountdown = () => {
    if (!props.payment || !props.payment.expired_time) {
        timeLeft.value.expired = true;
        return;
    }

    const expiryTime = new Date(props.payment.expired_time).getTime();
    const now = new Date().getTime();
    const diff = expiryTime - now;

    if (diff <= 0) {
        timeLeft.value.expired = true;
        timeLeft.value.days = 0;
        timeLeft.value.hours = 0;
        timeLeft.value.minutes = 0;
        timeLeft.value.seconds = 0;
        return;
    }

    timeLeft.value.days = Math.floor(diff / (1000 * 60 * 60 * 24));
    timeLeft.value.hours = Math.floor(
        (diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
    );
    timeLeft.value.minutes = Math.floor(
        (diff % (1000 * 60 * 60)) / (1000 * 60)
    );
    timeLeft.value.seconds = Math.floor((diff % (1000 * 60)) / 1000);
};

const downloadQRCode = async () => {
    if (!props.payment || !props.payment.qr_url) {
        toast.error("QR code not available");
        return;
    }

    try {
        // Create a temporary link to download the image
        const link = document.createElement("a");
        link.href = props.payment.qr_url;
        link.download = `QR_${props.order.order_id}.png`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        toast.success("QR Code download started");
    } catch (error) {
        toast.error("Failed to download QR Code");
    }
};

const redirectToPayment = () => {
    if (props.payment && props.payment.payment_link) {
        window.open(props.payment.payment_link, "_blank");
    } else {
        toast.error("Payment link not available");
    }
};

onMounted(() => {
    updateCountdown();
    countdownInterval.value = setInterval(updateCountdown, 1000);
});

onUnmounted(() => {
    if (countdownInterval.value) {
        clearInterval(countdownInterval.value);
    }
});
</script>

<template>
    <GuestLayout>
        <div class="max-w-6xl p-4 py-8 mx-auto">
            <!-- Cosmic Particles Background -->
            <CosmicParticles
                class="absolute inset-0 z-0 pointer-events-none"
                :particleCount="100"
            />

            <!-- Progress Timeline -->
            <div class="mb-8">
                <h1 class="mb-6 text-2xl font-bold text-white">
                    Invoice #{{ props.order.order_id }}
                </h1>

                <div class="relative flex items-center justify-between">
                    <!-- Progress Track -->
                    <div
                        class="absolute left-0 right-0 h-0.5 bg-gray-700"
                    ></div>

                    <!-- Stage 1: Created -->
                    <div class="relative z-10 flex flex-col items-center">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full"
                            :class="
                                currentStage >= 0
                                    ? 'bg-primary text-white'
                                    : 'bg-gray-700 text-gray-400'
                            "
                        >
                            <CircleCheck
                                v-if="currentStage > 0"
                                class="w-6 h-6"
                            />
                            <span v-else class="text-sm font-bold">1</span>
                        </div>
                        <div class="mt-2 text-center">
                            <div
                                class="text-xs font-medium md:text-base"
                                :class="
                                    currentStage >= 0
                                        ? 'text-primary'
                                        : 'text-gray-400'
                                "
                            >
                                Order Dibuat
                            </div>
                            <!-- show only for desktop md+ -->
                            <div class="hidden text-xs text-gray-400 lg:block">
                                Transaksi telah berhasil dibuat
                            </div>
                        </div>
                    </div>

                    <!-- Stage 2: Payment -->
                    <div class="relative z-10 flex flex-col items-center">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full"
                            :class="[
                                currentStage > 1
                                    ? 'bg-primary text-white'
                                    : currentStage === 1
                                    ? 'bg-primary text-white animate-pulse'
                                    : 'bg-gray-700 text-gray-400',
                            ]"
                        >
                            <CircleCheck
                                v-if="currentStage > 1"
                                class="w-6 h-6"
                            />
                            <span v-else class="text-sm font-bold">2</span>
                        </div>
                        <div class="mt-2 text-center">
                            <div
                                class="text-xs font-medium md:text-base"
                                :class="[
                                    currentStage > 1
                                        ? 'text-primary'
                                        : currentStage === 1
                                        ? 'text-primary'
                                        : 'text-gray-400',
                                ]"
                            >
                                Pembayaran
                            </div>
                            <div class="hidden text-xs text-gray-400 lg:block">
                                Silakan melakukan pembayaran
                            </div>
                        </div>
                    </div>

                    <!-- Stage 3: Processing -->
                    <div class="relative z-10 flex flex-col items-center">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full"
                            :class="[
                                currentStage > 2
                                    ? 'bg-primary text-white'
                                    : currentStage === 2
                                    ? 'bg-primary text-white animate-pulse'
                                    : 'bg-gray-700 text-gray-400',
                            ]"
                        >
                            <CircleCheck
                                v-if="currentStage > 2"
                                class="w-6 h-6"
                            />
                            <span v-else class="text-sm font-bold">3</span>
                        </div>
                        <div class="mt-2 text-center">
                            <div
                                class="text-xs font-medium md:text-base"
                                :class="[
                                    currentStage > 2
                                        ? 'text-primary'
                                        : currentStage === 2
                                        ? 'text-primary'
                                        : 'text-gray-400',
                                ]"
                            >
                                Proses
                            </div>
                            <div class="hidden text-xs text-gray-400 lg:block">
                                Pembelian sedang dalam proses
                            </div>
                        </div>
                    </div>

                    <!-- Stage 4: Completed -->
                    <div class="relative z-10 flex flex-col items-center">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full"
                            :class="[
                                currentStage === 3 &&
                                order.status === 'completed'
                                    ? 'bg-primary text-white'
                                    : currentStage === 3 &&
                                      order.status === 'failed'
                                    ? 'bg-red-500 text-white'
                                    : currentStage === 3 &&
                                      order.status === 'cancelled'
                                    ? 'bg-yellow-500 text-white'
                                    : 'bg-gray-700 text-gray-400',
                            ]"
                        >
                            <CircleCheck
                                v-if="
                                    currentStage === 3 &&
                                    order.status === 'completed'
                                "
                                class="w-6 h-6"
                            />
                            <AlertCircle
                                v-else-if="
                                    currentStage === 3 &&
                                    (order.status === 'failed' ||
                                        order.status === 'cancelled')
                                "
                                class="w-6 h-6"
                            />
                            <span v-else class="text-sm font-bold">4</span>
                        </div>
                        <div class="mt-2 text-center">
                            <div
                                class="text-xs font-medium md:text-base"
                                :class="[
                                    currentStage === 3 &&
                                    order.status === 'completed'
                                        ? 'text-primary'
                                        : currentStage === 3 &&
                                          order.status === 'failed'
                                        ? 'text-red-500'
                                        : currentStage === 3 &&
                                          order.status === 'cancelled'
                                        ? 'text-yellow-500'
                                        : 'text-gray-400',
                                ]"
                            >
                                {{
                                    order.status === "completed"
                                        ? "Order Selesai"
                                        : order.status === "failed"
                                        ? "Order Gagal"
                                        : order.status === "cancelled"
                                        ? "Order Dibatalkan"
                                        : "Order Selesai"
                                }}
                            </div>
                            <div class="hidden text-xs text-gray-400 lg:block">
                                {{
                                    order.status === "completed"
                                        ? "Transaksi telah berhasil diselesaikan"
                                        : order.status === "failed"
                                        ? "Transaksi gagal diproses"
                                        : order.status === "cancelled"
                                        ? "Transaksi telah dibatalkan"
                                        : "Transaksi telah berhasil diselesaikan"
                                }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Countdown Timer (for pending payment) -->
            <div
                v-if="
                    order.status === 'pending' && payment && !timeLeft.expired
                "
                class="p-3 mb-8 text-center rounded-lg"
                :class="
                    isExpiringSoon
                        ? 'bg-red-900/30 border border-red-700'
                        : 'bg-blue-900/30 border border-blue-700'
                "
            >
                <div
                    class="flex items-center justify-center gap-2 text-lg font-bold"
                    :class="isExpiringSoon ? 'text-red-400' : 'text-blue-400'"
                >
                    <Clock class="w-5 h-5" />
                    <span>
                        {{ timeLeft.days > 0 ? `${timeLeft.days} Hari ` : "" }}
                        {{ String(timeLeft.hours).padStart(2, "0") }} Jam
                        {{ String(timeLeft.minutes).padStart(2, "0") }} Menit
                        {{ String(timeLeft.seconds).padStart(2, "0") }} Detik
                    </span>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Left Column: Account Info & Payment Details -->
                <div class="space-y-6">
                    <!-- Game Account Info -->
                    <div
                        class="p-5 border rounded-lg border-secondary/50 bg-secondary/20 backdrop-blur-sm"
                    >
                        <h2 class="mb-4 text-lg font-medium text-white">
                            Informasi Akun
                        </h2>

                        <div class="flex">
                            <!-- Game Thumbnail -->
                            <div
                                class="flex-shrink-0 w-24 h-24 mr-4 overflow-hidden bg-gray-700 rounded"
                            >
                                <img
                                    :src="'/storage/' + product?.thumbnail"
                                    :alt="product?.nama"
                                    class="object-cover w-full h-full"
                                />
                            </div>

                            <!-- Account Details -->
                            <div class="flex-1">
                                <div class="mb-2">
                                    <p class="text-sm text-gray-400">
                                        {{ product?.nama }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ order?.layanan?.nama_layanan }}
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <div v-if="order.nickname" class="flex">
                                        <span class="w-24 text-sm text-gray-400"
                                            >Nickname</span
                                        >
                                        <span
                                            class="text-sm font-medium text-white"
                                            >: {{ order.nickname }}</span
                                        >
                                    </div>
                                    <div class="flex">
                                        <span class="w-24 text-sm text-gray-400"
                                            >ID</span
                                        >
                                        <span
                                            class="text-sm font-medium text-white"
                                            >: {{ order.input_id }}</span
                                        >
                                    </div>
                                    <div v-if="order.input_zone" class="flex">
                                        <span class="w-24 text-sm text-gray-400"
                                            >Server</span
                                        >
                                        <span
                                            class="text-sm font-medium text-white"
                                            >: {{ order.input_zone }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Breakdown -->
                    <div
                        class="p-5 border rounded-lg border-secondary/50 bg-secondary/20 backdrop-blur-sm"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-medium text-white">
                                Rincian Pembayaran
                            </h2>
                            <button
                                class="flex items-center text-sm text-blue-400 focus:outline-none"
                            >
                                <span class="mr-1">Detail</span>
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 9l-7 7-7-7"
                                    ></path>
                                </svg>
                            </button>
                        </div>

                        <div class="mb-4 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Harga</span>
                                <span class="text-white">{{
                                    formatCurrency(order.price)
                                }}</span>
                            </div>
                            <div
                                class="flex justify-between pb-3 border-b border-secondary/20"
                            >
                                <span class="text-gray-400">Jumlah</span>
                                <span class="text-white"
                                    >{{ order.quantity }}x</span
                                >
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Subtotal</span>
                                <span class="text-white">{{
                                    formatCurrency(order.price * order.quantity)
                                }}</span>
                            </div>
                            <div
                                v-if="payment && payment.fee > 0"
                                class="flex justify-between"
                            >
                                <span class="text-gray-400">Biaya</span>
                                <span class="text-white">{{
                                    formatCurrency(payment.fee)
                                }}</span>
                            </div>
                            <div
                                v-if="order.discount > 0"
                                class="flex justify-between"
                            >
                                <span class="text-gray-400">Diskon</span>
                                <span class="text-green-400"
                                    >-{{ formatCurrency(order.discount) }}</span
                                >
                            </div>
                        </div>
                    </div>
                    <div
                        class="p-5 border rounded-lg border-secondary bg-secondary/20 backdrop-blur-sm"
                    >
                        <div class="">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-300"
                                    >Total Pembayaran</span
                                >
                                <span class="text-lg font-bold text-primary">{{
                                    formatCurrency(payment?.total_price)
                                }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Payment Method & Instructions -->
                <div class="space-y-6">
                    <!-- Payment Method Info -->
                    <div
                        class="p-5 border rounded-lg border-secondary/50 bg-secondary/20 backdrop-blur-sm"
                    >
                        <h2 class="mb-4 text-lg font-medium text-white">
                            Metode Pembayaran
                        </h2>
                        <div class="mb-1 font-medium text-primary">
                            {{ payment?.payment_method }}
                        </div>

                        <!-- Payment Details -->
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Nomor Invoice</span>
                                <span class="text-white">{{
                                    order.order_id
                                }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400"
                                    >Status Pembayaran</span
                                >
                                <span
                                    :class="{
                                        'bg-red-500/20 text-red-400':
                                            payment?.status === 'pending',
                                        'bg-green-500/20 text-green-400':
                                            payment?.status === 'paid',
                                        'bg-yellow-500/20 text-yellow-400':
                                            payment?.status === 'failed' ||
                                            payment?.status === 'cancelled',
                                    }"
                                    class="px-2 py-0.5 rounded text-xs font-medium uppercase"
                                >
                                    {{
                                        payment?.status === "pending"
                                            ? "UNPAID"
                                            : payment?.status
                                    }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400"
                                    >Status Transaksi</span
                                >
                                <span
                                    :class="{
                                        'bg-yellow-500/20 text-yellow-400':
                                            order.status === 'pending',
                                        'bg-blue-500/20 text-blue-400':
                                            order.status === 'processing',
                                        'bg-green-500/20 text-green-400':
                                            order.status === 'completed',
                                        'bg-red-500/20 text-red-400':
                                            order.status === 'failed' ||
                                            order.status === 'cancelled',
                                    }"
                                    class="px-2 py-0.5 rounded text-xs font-medium uppercase"
                                >
                                    {{ order.status }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="order.status === 'pending'"
                            class="mt-4 text-gray-300"
                        >
                            <p>
                                Silahkan untuk melakukan pembayaran dengan
                                metode yang kami pilih.
                            </p>
                        </div>
                    </div>

                    <!-- QR Code / Payment Link -->
                    <div
                        v-if="order.status === 'pending' && payment"
                        class="p-5 border rounded-lg border-secondary/50 bg-secondary/20 backdrop-blur-sm"
                    >
                        <!-- QRIS Payment -->
                        <div
                            v-if="payment.qr_url"
                            class="flex flex-col items-center"
                        >
                            <img
                                :src="payment.qr_url"
                                alt="QR Code"
                                class="object-contain w-48 h-48 p-4 bg-white rounded-lg"
                                loading="lazy"
                            />

                            <button
                                @click="downloadQRCode"
                                class="flex items-center px-4 py-2 mt-4 font-medium text-white transition-all transform rounded-md bg-primary hover:bg-primary/80 hover:scale-105"
                            >
                                <Download class="w-5 h-5 mr-2" />
                                Unduh Kode QR
                            </button>

                            <p class="mt-2 text-xs text-gray-400">
                                Screenshot jika QR Code tidak bisa di download.
                            </p>
                        </div>

                        <!-- Non-QRIS Payment -->
                        <div
                            v-else-if="payment.payment_link"
                            class="flex flex-col items-center"
                        >
                            <button
                                @click="redirectToPayment"
                                class="flex items-center px-6 py-3 mt-2 font-medium text-white transition-all transform rounded-md bg-primary hover:bg-primary/80 hover:scale-105"
                            >
                                Complete Payment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Payment Instructions -->
            <div
                v-if="
                    order.status === 'pending' && payment && payment.instruksi
                "
                class="mt-6 backdrop-blur-sm"
            >
                <h2 class="mb-4 text-lg font-medium text-white">
                    Instruksi Pembayaran
                </h2>

                <div class="space-y-4">
                    <div
                        v-for="(instruction, index) in payment.instruksi"
                        :key="index"
                        class="p-4 border rounded-lg bg-secondary/20 border-secondary/50"
                    >
                        <h3 class="mb-2 font-medium text-primary">
                            {{ instruction.title }}
                        </h3>
                        <ol class="pl-5 space-y-1 text-gray-300 list-decimal">
                            <li
                                v-for="(step, stepIndex) in instruction.steps"
                                :key="stepIndex"
                                class="text-sm"
                            >
                                {{ step }}
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
/* Cosmic animations */
@keyframes cosmic-pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(155, 135, 245, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(155, 135, 245, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(155, 135, 245, 0);
    }
}

.cosmic-pulse {
    animation: cosmic-pulse 2s infinite;
}
</style>

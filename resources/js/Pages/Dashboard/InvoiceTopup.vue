
<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import GuestLayout from "@/Layouts/GuestLayout.vue";
import DashboardSidebar from "@/Components/Dashboard/Sidebar.vue";
import { Link } from "@inertiajs/vue3";
import { Wallet, ClockIcon as Clock, QrCode, CircleCheck, CircleX } from "lucide-vue-next";

const props = defineProps({
    deposit: Object,
    balance: Number,
});

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(amount);
}

// Timer countdown functionality
const remainingTime = ref(props.deposit.expired_time ? 
    Math.max(0, new Date(props.deposit.expired_time).getTime() - new Date().getTime()) : 0);
    
const formattedTime = computed(() => {
    if (remainingTime.value <= 0) return '00:00:00';
    
    const hours = Math.floor(remainingTime.value / (1000 * 60 * 60));
    const minutes = Math.floor((remainingTime.value % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((remainingTime.value % (1000 * 60)) / 1000);
    
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
});

const isExpired = computed(() => remainingTime.value <= 0);
const isPaid = computed(() => props.deposit.status === 'paid');
const isFailed = computed(() => props.deposit.status === 'failed' || props.deposit.status === 'cancelled');
const isPending = computed(() => props.deposit.status === 'pending' && !isExpired.value);

let timer;

onMounted(() => {
    // Update countdown every second
    timer = setInterval(() => {
        if (remainingTime.value > 0) {
            remainingTime.value -= 1000;
        } else {
            clearInterval(timer);
        }
    }, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});

// Format the invoice number
const formattedInvoiceId = computed(() => {
    return String(props.deposit.deposit_id).padStart(8, '0');
});

// Handle payment button click
const openPaymentLink = () => {
    if (props.deposit.payment_link) {
        window.open(props.deposit.payment_link, '_blank');
    }
};
</script>

<template>
    <GuestLayout>
        <div class="flex min-h-screen mx-auto bg-transparent max-w-7xl">
            <DashboardSidebar />
            
            <div class="flex-1 p-6">
                <Link
                    :href="route('dashboard.topup')"
                    class="flex items-center px-8 py-2 mb-6 rounded-lg text-secondary hover:cursor-pointer"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-6 h-6 text-secondary"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M11 19l-7-7 7-7m8 14l-7-7 7-7"
                        />
                    </svg>
                    <span class="ml-2">Back to Top Up</span>
                </Link>
                
                <!-- Invoice Container -->
                <div class="max-w-4xl mx-auto">
                    <!-- Status Banner -->
                    <div class="relative mb-6 overflow-hidden rounded-lg">
                        <div 
                            class="px-6 py-4 text-white"
                            :class="{
                                'bg-primary/50': isPending,
                                'bg-green-500/50': isPaid,
                                'bg-red-500/50': isFailed || isExpired
                            }"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <CircleCheck v-if="isPaid" class="w-6 h-6 mr-2" />
                                    <Clock v-if="isPending" class="w-6 h-6 mr-2" />
                                    <CircleX v-if="isFailed || isExpired" class="w-6 h-6 mr-2" />
                                    <span class="text-lg font-semibold">
                                        {{ 
                                            isPaid ? 'Payment Complete' : 
                                            isPending ? 'Awaiting Payment' : 
                                            'Payment Failed' 
                                        }}
                                    </span>
                                </div>
                                <div v-if="isPending" class="flex items-center">
                                    <Clock class="w-5 h-5 mr-1" />
                                    <span class="font-mono">{{ formattedTime }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Animated Background Effects -->
                        <div 
                            class="absolute inset-0 pointer-events-none opacity-30 -z-10"
                            :class="{
                                'animate-pulse': isPending
                            }"
                        >
                            <!-- Pending: Orbiting moons -->
                            <div v-if="isPending" class="absolute inset-0">
                                <div 
                                    v-for="i in 5" 
                                    :key="`moon-${i}`"
                                    class="absolute w-2 h-2 rounded-full bg-secondary"
                                    :style="{
                                        top: `${25 + 10 * Math.sin(i + Date.now() / 1000)}%`,
                                        left: `${20 * i + 10 * Math.cos(i + Date.now() / 1000)}%`,
                                        opacity: 0.7,
                                        animation: `orbit ${3 + i * 0.5}s infinite linear`
                                    }"
                                ></div>
                            </div>
                            
                            <!-- Paid: Supernova -->
                            <div v-if="isPaid" class="absolute inset-0 bg-green-500/20">
                                <div class="absolute inset-0 bg-gradient-radial from-green-300/50 to-transparent animate-pulse"></div>
                            </div>
                            
                            <!-- Expired/Failed: Black hole -->
                            <div v-if="isExpired || isFailed" class="absolute inset-0">
                                <div class="absolute inset-0 bg-gradient-radial from-transparent to-red-900/30"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Main Invoice Card -->
                    <div class="p-6 border shadow-xl rounded-2xl bg-gradient-to-b from-primary/30 to-content-background/80 border-secondary/10">
                        <div class="flex flex-col justify-between md:flex-row">
                            <div>
                                <h1 class="text-2xl font-bold text-primary">Invoice</h1>
                                <p class="text-secondary">#{{ formattedInvoiceId }}</p>
                                <p class="mt-2 text-sm text-gray-400">
                                    {{ new Date(deposit.created_at).toLocaleString() }}
                                </p>
                            </div>
                            
                            <div class="mt-4 md:mt-0">
                                <h2 class="font-semibold text-secondary">Payment Method</h2>
                                <div class="flex items-center mt-1">
                                    <img 
                                        :src="'/storage/' + deposit.pay_method.gambar" 
                                        alt="Payment Method"
                                        class="w-8 h-8 mr-2 rounded"
                                    />
                                    <span>{{ deposit.pay_method.nama }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-6 border-gray-700" />
                        
                        <!-- Payment Details -->
                        <div class="grid gap-6 md:grid-cols-2">
                            <!-- Left side: Amount details -->
                            <div class="space-y-4">
                                <h2 class="text-lg font-semibold text-primary">Amount Details</h2>
                                
                                <!-- Amount breakdown with cosmic styling -->
                                <div class="p-4 rounded-lg bg-secondary/10">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-gray-400">Subtotal</span>
                                        <span>{{ formatCurrency(deposit.amount) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-gray-400">Fee</span>
                                        <span>{{ formatCurrency(deposit.fee) }}</span>
                                    </div>
                                    <div class="h-px my-2 bg-gray-700"></div>
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold text-primary">Total</span>
                                        <span class="text-lg font-bold text-primary">
                                            {{ formatCurrency(deposit.amount + deposit.fee) }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Status info -->
                                <div class="p-4 space-y-2 border rounded-lg border-gray-700/50">
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-400">Status</span>
                                        <span 
                                            :class="{
                                                'text-yellow-400': isPending,
                                                'text-green-400': isPaid,
                                                'text-red-400': isFailed || isExpired
                                            }"
                                        >
                                            {{ 
                                                isPaid ? 'PAID' : 
                                                isPending ? 'PENDING' : 
                                                isExpired ? 'EXPIRED' : 'FAILED' 
                                            }}
                                        </span>
                                    </div>
                                    <div v-if="isPending" class="flex items-center justify-between">
                                        <span class="text-gray-400">Time Left</span>
                                        <span class="font-mono text-yellow-400">{{ formattedTime }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right side: QR code and payment actions -->
                            <div>
                                <h2 class="mb-4 text-lg font-semibold text-primary">Payment Method</h2>
                                
                                <!-- QR Code display if available -->
                                <div v-if="deposit.qr_url && isPending" class="flex flex-col items-center p-4 space-y-4 border rounded-lg border-secondary/30 bg-white/5">
                                    <QrCode class="w-6 h-6 text-secondary" />
                                    <p class="text-sm text-center text-gray-400">Scan this QR code to complete your payment</p>
                                    <img 
                                        :src="deposit.qr_url" 
                                        alt="Payment QR Code"
                                        class="w-48 h-48 mx-auto bg-white rounded-lg p-2"
                                    />
                                </div>
                                
                                <!-- Payment link button -->
                                <div v-if="deposit.payment_link && isPending" class="mt-4">
                                    <button 
                                        @click="openPaymentLink"
                                        class="w-full px-4 py-3 text-white transition-all duration-300 rounded-lg shadow-lg bg-primary/80 hover:bg-primary hover:shadow-glow-primary"
                                    >
                                        Open Payment Page
                                    </button>
                                    <p class="mt-2 text-xs text-center text-gray-400">
                                        You will be redirected to complete your payment
                                    </p>
                                </div>
                                
                                <!-- Completed or failed payment message -->
                                <div v-if="!isPending" class="p-6 text-center border rounded-lg border-gray-700/50">
                                    <CircleCheck v-if="isPaid" class="w-12 h-12 mx-auto mb-3 text-green-400" />
                                    <CircleX v-if="isFailed || isExpired" class="w-12 h-12 mx-auto mb-3 text-red-400" />
                                    
                                    <h3 class="mb-2 text-lg font-medium">
                                        {{ isPaid ? 'Payment Complete' : 'Payment Failed' }}
                                    </h3>
                                    <p class="text-sm text-gray-400">
                                        {{ 
                                            isPaid ? 'Your account has been topped up successfully.' : 
                                            'Your payment could not be processed. Please try again.' 
                                        }}
                                    </p>
                                    
                                    <div class="mt-4">
                                        <Link
                                            :href="route('dashboard.topup')"
                                            class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-primary/70 hover:bg-primary"
                                        >
                                            {{ isPaid ? 'Back to Dashboard' : 'Try Again' }}
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional notes or instructions -->
                        <div class="p-4 mt-6 text-sm border rounded-lg border-gray-700/50 text-gray-400">
                            <p class="mb-1 font-semibold">Important Notes:</p>
                            <ul class="ml-4 list-disc">
                                <li>Payment will be processed automatically once confirmed by the payment gateway.</li>
                                <li>For assistance, please contact our support team.</li>
                                <li>Keep your payment receipt for reference.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
/* Additional cosmic animations */
@keyframes orbit {
    from { transform: rotate(0deg) translateX(10px) rotate(0deg); }
    to { transform: rotate(360deg) translateX(10px) rotate(-360deg); }
}

.bg-gradient-radial {
    background: radial-gradient(circle, var(--tw-gradient-stops));
}

.shadow-glow-primary {
    box-shadow: 0 0 15px rgba(155, 135, 245, 0.5);
}
</style>

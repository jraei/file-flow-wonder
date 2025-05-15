<script setup>
import { ref, computed, watch } from "vue";
import { useToast } from "@/Composables/useToast";
import CosmicCard from "@/Components/Order/CosmicCard.vue";
import { usePage } from "@inertiajs/vue3";

const props = defineProps({
    // staticMethods: Object,
    dynamicMethods: Object,
    saldoMethod: Object,
    selectedService: Object,
    selectedPayment: [Object, String, Number, null],
    basePrice: {
        type: Number,
        required: true,
    },
    quantity: {
        type: Number,
        default: 1,
    },
    voucher: Object,
});

const { toast } = useToast();
let isToastActive = false;
const page = usePage();

const price = computed(() => {
    if (!props.selectedService) return 0;
    const value = props.selectedService.harga_jual * props.quantity;
    return Math.ceil(value);
});
console.log("base price", props.basePrice);

console.log(price.value);

const userBalance = computed(() => page.props.auth?.user?.saldo ?? 0);

// Calculate voucher discount
const voucherDiscount = computed(() => {
    if (!props.voucher) return 0;

    let discount = 0;

    // Fixed amount discount
    if (props.voucher.discount_type === "fixed") {
        discount = props.voucher.discount_value;
    }
    // Percentage discount
    else {
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

const emit = defineEmits(["update:selectedPayment", "update:fee"]);

// Utility for fee calculation (mirrors backend)
function calculateTotal(
    price,
    fee_fixed = 0,
    fee_percent = 0,
    feeType = "none"
) {
    let total = price - voucherDiscount.value;

    if (feeType === "fixed") {
        total = total + fee_fixed;
    } else if (feeType === "percent") {
        total = total + total * (fee_percent / 100);
    } else if (feeType === "mixed") {
        total = total + fee_fixed + total * (fee_percent / 100);
    }

    return Math.ceil(total); // bulatkan hasil akhir ke angka terdekat
}

// UI state
const expandedGroups = ref([]);

function selectDynamic(group, method) {
    const user = page.props.auth?.user;

    if (group === "Saldo Akun" && !user) {
        if (!isToastActive) {
            isToastActive = true;
            toast.error("Login terlebih dahulu untuk menggunakan saldo!");
            setTimeout(() => (isToastActive = false), 5000);
        }
        return;
    }

    // check min and max amount
    const finalPrice = price.value - voucherDiscount.value;

    if (method.min_amount && method.min_amount > finalPrice) {
        toast.error(`Minimal pembelian ${method.min_amount} untuk metode ini`);
        return;
    }
    if (method.max_amount && method.max_amount < finalPrice) {
        toast.error(`Maksimal pembelian ${method.max_amount} untuk metode ini`);
        return;
    }

    emit("update:selectedPayment", {
        type: group,
        channel: method.id,
        provider: method.payment_provider?.id,
    });

    emit("update:fee", {
        fee_fixed: method.fee_fixed,
        fee_percent: method.fee_percent,
        feeType: method.fee_type,
        methodLabel: method.nama,
        image: method.gambar,
    });
}

const isSelected = (payment) => {
    if (!props.selectedPayment) return false;
    if (payment.type)
        return (
            props.selectedPayment.type === payment.type &&
            !props.selectedPayment.channel
        );
    return (
        props.selectedPayment.type === payment.group &&
        props.selectedPayment.channel === payment.id
    );
};
</script>

<template>
    <CosmicCard :title="'Select Payment'" :step-number="4">
        <div class="space-y-4">
            <div class="relative flex flex-col gap-4">
                <!-- NaelCoin -->
                <button
                    @click="selectDynamic('Saldo Akun', props.saldoMethod)"
                    type="button"
                    class="relative flex items-center flex-1 min-w-0 p-4 pr-8 transition-all border-2 rounded-lg outline-none group bg-secondary/20 shadow-glow-primary"
                    :class="[
                        isSelected({
                            group: 'Saldo Akun',
                            id: props.saldoMethod?.id,
                        })
                            ? 'border-primary animate-pulse ring-2 ring-primary/40'
                            : 'border-secondary/60',
                    ]"
                >
                    <img
                        :src="props.saldoMethod?.gambar"
                        alt="NaelCoin"
                        class="w-10 h-10 mr-4 rounded"
                    />

                    <span class="flex-1 font-bold text-left text-primary_text"
                        >{{ props.saldoMethod?.nama }}
                        <span
                            class="block text-xs font-normal text-secondary/80"
                            v-if="userBalance"
                        >
                            Saldo: Rp {{ userBalance.toLocaleString() }}
                        </span>
                    </span>
                    <span class="ml-auto text-lg font-bold text-secondary">
                        Rp
                        {{
                            calculateTotal(
                                price,
                                props.saldoMethod?.fee_fixed ?? 0,
                                props.saldoMethod?.fee_percent ?? 0,
                                props.saldoMethod?.fee_type ?? "fixed"
                            ).toLocaleString()
                        }}
                    </span>
                    <span
                        class="absolute z-20 px-2 py-1 text-xs font-bold rounded-full shadow select-none -top-2 -right-2 bg-primary/90 text-primary_text"
                        style="
                            background-image: repeating-linear-gradient(
                                45deg,
                                rgba(51, 195, 240, 0.2) 0 3px,
                                transparent 0 8px
                            );
                        "
                    >
                        BEST PRICE
                    </span>
                </button>

                <!-- QRIS -->
                <!-- Dynamic QRIS -->
                <button
                    v-if="dynamicMethods.QRIS && dynamicMethods.QRIS.length"
                    @click="selectDynamic('QRIS', dynamicMethods.QRIS[0])"
                    type="button"
                    class="relative flex items-center flex-1 min-w-0 p-4 pr-8 transition-all border-2 rounded-lg outline-none group bg-secondary/20 shadow-glow-secondary"
                    :class="[
                        isSelected({
                            group: 'QRIS',
                            id: dynamicMethods.QRIS[0].id,
                        })
                            ? 'border-secondary animate-pulse ring-2 ring-secondary/40'
                            : 'border-secondary/60',
                    ]"
                >
                    <img
                        :src="dynamicMethods.QRIS[0].gambar"
                        alt="QRIS"
                        class="w-10 h-10 mr-4 rounded"
                    />
                    <div
                        class="flex flex-col flex-1 font-bold text-left text-primary_text"
                    >
                        <span class="">QRIS (Semua Pembayaran)</span>
                        <span class="text-xs font-light">
                            {{
                                dynamicMethods.QRIS[0].fee_type === "percent"
                                    ? `Fee: ${dynamicMethods.QRIS[0].fee_percent}%`
                                    : dynamicMethods.QRIS[0].fee_type ===
                                      "fixed"
                                    ? `Fee: Rp ${dynamicMethods.QRIS[0].fee_fixed?.toLocaleString()}`
                                    : `Fee: ${
                                          dynamicMethods.QRIS[0].fee_percent
                                      }% + Rp ${dynamicMethods.QRIS[0].fee_fixed?.toLocaleString()}`
                            }}
                        </span>
                    </div>
                    <span class="ml-auto text-lg font-bold text-primary">
                        Rp
                        {{
                            calculateTotal(
                                price,
                                dynamicMethods.QRIS[0].fee_fixed,
                                dynamicMethods.QRIS[0].fee_percent,
                                dynamicMethods.QRIS[0].fee_type
                            ).toLocaleString()
                        }}
                    </span>
                </button>
            </div>

            <!-- Dynamic Payment Groups -->
            <div v-for="(group, tipe) in dynamicMethods" :key="tipe">
                <div
                    class="mb-3 overflow-hidden border rounded-lg border-secondary/60"
                    v-if="
                        group[0].tipe !== 'Saldo Akun' &&
                        group[0].tipe !== 'QRIS'
                    "
                >
                    <!-- Collapsed Header -->
                    <button
                        type="button"
                        class="relative flex items-center justify-between w-full p-4 transition-all bg-secondary/20/80 hover:bg-primary/5"
                        @click="
                            expandedGroups = expandedGroups.includes(tipe)
                                ? expandedGroups.filter((g) => g !== tipe)
                                : [...expandedGroups, tipe]
                        "
                    >
                        <div class="flex items-center space-x-3">
                            <span class="font-semibold text-primary_text">{{
                                tipe
                            }}</span>
                            <!-- Icon collage -->
                            <div class="flex -space-x-2">
                                <img
                                    v-for="meth in group.slice(0, 3)"
                                    :key="meth.id"
                                    :src="meth.gambar"
                                    class="inline rounded-full shadow h-7 w-7"
                                    :alt="meth.nama"
                                />
                            </div>
                        </div>

                        <span
                            class="absolute -translate-y-1/2 right-4 top-1/2 text-secondary"
                        >
                            <svg
                                v-if="expandedGroups.includes(tipe)"
                                xmlns="http://www.w3.org/2000/svg"
                                class="inline w-4 h-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                            <svg
                                v-else
                                xmlns="http://www.w3.org/2000/svg"
                                class="inline w-4 h-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-width="2"
                                    d="M5 15l7-7 7 7"
                                />
                            </svg>
                        </span>
                    </button>
                    <!-- Expanded Body -->
                    <transition name="fade">
                        <div
                            v-if="expandedGroups.includes(tipe)"
                            class="grid grid-cols-1 gap-3 px-2 pt-2 pb-3 bg-secondary/20/90 md:grid-cols-2 cosmic-card-border"
                        >
                            <div
                                v-for="meth in group"
                                :key="meth.id"
                                @click="selectDynamic(tipe, meth)"
                                class="relative flex px-3 py-3 transition-all border-2 rounded-md cursor-pointer group bg-secondary/10"
                                :class="
                                    isSelected({ group: tipe, id: meth.id })
                                        ? 'border-primary animate-pulse ring-2 ring-primary/40 shadow-glow-primary'
                                        : 'border-secondary/60'
                                "
                            >
                                <img
                                    :src="meth.gambar"
                                    alt=""
                                    class="w-8 h-8 mr-3 rounded"
                                    loading="lazy"
                                />
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <span
                                            class="font-semibold text-primary_text"
                                            >{{ meth.nama }}</span
                                        >
                                        <span
                                            v-if="meth.is_recommended"
                                            class="px-1 ml-2 text-xs font-bold rounded bg-secondary/20 text-secondary cosmic-pulse"
                                            >Recommended</span
                                        >
                                    </div>
                                    <div class="text-xs text-secondary/80">
                                        {{
                                            meth.fee_type === "percent"
                                                ? `Fee: Rp ${meth.fee_percent}%`
                                                : meth.fee_type === "fixed"
                                                ? `Fee: Rp ${meth.fee_fixed?.toLocaleString()}`
                                                : `Fee: Rp ${
                                                      meth.fee_percent
                                                  }% + Rp ${meth.fee_fixed?.toLocaleString()}`
                                        }}
                                    </div>
                                    <div
                                        class="mt-1 text-sm font-bold text-primary"
                                    >
                                        Total: Rp
                                        {{
                                            calculateTotal(
                                                price,
                                                meth.fee_fixed,
                                                meth.fee_percent,
                                                meth.fee_type
                                            ).toLocaleString()
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        </div>
    </CosmicCard>
</template>

<style scoped>
.shadow-glow-primary {
    box-shadow: 0 0 12px 0 #9b87f566 !important;
}
.shadow-glow-secondary {
    box-shadow: 0 0 12px 0 #33c3f088 !important;
}
.cosmic-card-border {
    transition: box-shadow 0.28s cubic-bezier(0.4, 0, 0.2, 1),
        border 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.cosmic-pulse {
    animation: cosmicPulse 1s infinite;
}
@keyframes cosmicPulse {
    0%,
    100% {
        opacity: 0.8;
    }
    50% {
        opacity: 1;
        box-shadow: 0 0 6px 2px #33c3f044;
    }
}
.pulse {
    animation: pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import DashboardSidebar from "@/Components/Dashboard/Sidebar.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { computed } from "vue";
import { Image } from "lucide-vue-next";

const props = defineProps({
    balance: { type: Number, required: true },
    payMethods: { type: Object, required: true },
});

// get selected payment method
const payMethod = computed(() => {
    return props.payMethods.find((method) => method.nama == form.methodName);
});

// calculate fee amount
const feeAmount = computed(() => {
    if (payMethod.value) {
        if (payMethod.value.fee_type === "percent") {
            return (form.nominal * payMethod.value.fee) / 100;
        } else {
            return payMethod.value.fee;
        }
    }
});

function formatCurrency(amount) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(amount);
}

// form inertia handler
const form = useForm({
    nominal: "",
    methodName: "",
});

const paymentAmounts = [10000, 50000, 100000, 200000, 500000];
console.log(payMethod.value);
</script>

<template>
    <GuestLayout>
        <div class="flex min-h-screen mx-auto bg-transparent max-w-7xl">
            <DashboardSidebar />

            <div class="flex-1 p-6">
                <Link
                    :href="route('dashboard.balance')"
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
                    <span class="ml-2">Riwayat Deposit</span>
                </Link>
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <div
                            class="p-6 mb-6 border shadow-xl rounded-2xl bg-gradient-to-b from-primary/30 to-content-background/80 border-secondary/10"
                        >
                            <h1
                                class="mb-4 text-lg font-bold md:text-xl text-primary"
                            >
                                Top Up Saldo
                            </h1>
                            <div class="gap-3">
                                <div class="col-span-2">
                                    <label
                                        for="nominal"
                                        class="block mb-1 text-sm font-medium text-gray-300"
                                        >Nominal</label
                                    >
                                    <input
                                        id="nominal"
                                        v-model="form.nominal"
                                        type="number"
                                        min="1"
                                        class="w-full px-3 py-2 text-white border rounded-lg border-secondary bg-secondary/20 focus:ring-2 focus:ring-primary focus:border-transparent"
                                    />
                                </div>
                            </div>
                            <span
                                class="block mt-2 text-xs text-secondary"
                                v-if="form.nominal < 1000"
                            >
                                Minimal Deposit Rp 1.000</span
                            >
                            <div
                                class="grid grid-cols-2 gap-3 mt-4 md:grid-cols-3 md:gap-4"
                            >
                                <!-- make nominal card, when card clicked send value to form and change nominal -->
                                <div
                                    class="col-span-1 p-3 border rounded-lg cursor-pointer border-secondary/20 lg bg-secondary/20 hover:bg-secondary/30 hover:border-secondary"
                                    v-for="(nominal, index) in paymentAmounts"
                                    :key="index"
                                    :class="{
                                        'ring-1 ring-secondary border-secondary pointer-events-none bg-secondary/30':
                                            nominal === form.nominal,
                                    }"
                                    @click="form.nominal = nominal"
                                >
                                    <p
                                        class="mb-1 text-xs font-medium md:text-sm text-primary-text"
                                    >
                                        Saldo {{ formatCurrency(nominal) }}
                                    </p>
                                    <p
                                        class="text-xs font-medium md:text-sm text-primary-text"
                                    >
                                        {{ formatCurrency(nominal) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            class="p-6 border shadow-xl rounded-2xl bg-gradient-to-b from-primary/30 to-content-background/80 border-secondary/10"
                        >
                            <h1
                                class="mb-4 text-lg font-bold md:text-xl text-primary"
                            >
                                Metode Pembayaran
                            </h1>
                            <div
                                class="grid grid-cols-2 gap-3 mt-4 md:grid-cols-3 md:gap-4"
                            >
                                <template
                                    v-if="payMethods"
                                    v-for="(method, index) in payMethods"
                                    :key="index"
                                >
                                    <div
                                        class="relative col-span-1 p-3 border rounded-lg cursor-pointer border-secondary/20 lg bg-secondary/20 hover:bg-secondary/30 hover:border-secondary"
                                        v-if="method.tipe !== 'Saldo Akun'"
                                        @click="form.methodName = method.nama"
                                        :class="{
                                            'ring-1 ring-secondary border-secondary pointer-events-none bg-secondary/30':
                                                method.nama === form.methodName,
                                        }"
                                    >
                                        <img
                                            :src="'/storage/' + method.gambar"
                                            alt=""
                                            class="object-cover object-center w-8 h-8 mb-2 rounded-lg md:w-10 md:h-10"
                                        />
                                        <p
                                            class="mb-1 text-xs font-medium md:text-sm text-primary-text"
                                        >
                                            {{ method.nama }}
                                        </p>

                                        <p
                                            class="text-xs font-medium md:text-sm text-primary-text/70"
                                        >
                                            Fee
                                            {{
                                                method.fee_type === "percent"
                                                    ? `${method.fee}%`
                                                    : `${formatCurrency(
                                                          method.fee
                                                      )}`
                                            }}
                                        </p>
                                        <div
                                            class="absolute lg:block top-2 right-2"
                                        >
                                            <p
                                                class="px-2 py-1 mb-1 text-[10px] xl:text-xs font-medium rounded-full text-primary-text/80 bg-primary/60"
                                            >
                                                {{
                                                    method.payment_provider ===
                                                    "Manual"
                                                        ? "Manual"
                                                        : "Otomatis"
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-1">
                        <div
                            class="relative p-6 mb-6 overflow-hidden border shadow-xl rounded-2xl bg-gradient-to-b from-primary/30 to-content-background/80 border-secondary/10"
                        >
                            <!-- back to dashboard balance button -->
                            <div
                                class="absolute inset-0 z-0 pointer-events-none select-none animate-fade-in"
                            >
                                <!-- Subtle animated cosmic particles background -->
                                <svg
                                    class="w-full h-full opacity-50"
                                    viewBox="0 0 400 140"
                                    preserveAspectRatio="none"
                                >
                                    <circle
                                        v-for="n in 12"
                                        :key="n"
                                        :cx="Math.random() * 400"
                                        :cy="Math.random() * 140"
                                        :r="n % 3 === 0 ? 2 : 1.2"
                                        :fill="
                                            n % 2 == 0
                                                ? '#9b87f577'
                                                : '#33C3F077'
                                        "
                                    >
                                        <animate
                                            attributeName="cx"
                                            from="0"
                                            :to="400"
                                            dur="9s"
                                            repeatCount="indefinite"
                                        />
                                    </circle>
                                </svg>
                            </div>
                            <div
                                class="relative z-10 flex flex-row items-center justify-between"
                            >
                                <div>
                                    <div
                                        class="mb-1 text-xs font-semibold tracking-widest uppercase text-secondary"
                                    >
                                        Balance
                                    </div>
                                    <div
                                        class="mb-2 text-3xl font-bold text-white md:text-4xl"
                                        data-testid="balance-amount"
                                    >
                                        {{ formatCurrency(balance) }}
                                    </div>
                                    <div class="text-xs text-secondary/80">
                                        Your NaelCoin balance
                                    </div>
                                </div>
                                <div
                                    class="flex flex-col gap-2 mt-6 sm:mt-0 sm:flex-row"
                                ></div>
                            </div>
                            <!-- Decorative flying planet -->
                            <svg
                                class="absolute pointer-events-none -bottom-10 -right-10 w-36 opacity-40 rotate-6"
                                viewBox="0 0 100 100"
                            >
                                <circle
                                    cx="50"
                                    cy="50"
                                    r="26"
                                    fill="#9b87f5"
                                    fill-opacity="0.2"
                                />
                                <ellipse
                                    cx="62"
                                    cy="40"
                                    rx="8"
                                    ry="4"
                                    fill="#33C3F0"
                                    fill-opacity="0.28"
                                />
                            </svg>
                        </div>
                        <div
                            class="p-6 border shadow-xl rounded-2xl bg-gradient-to-b from-primary/30 to-content-background/80 border-secondary/10"
                            v-if="payMethod"
                        >
                            <div class="mb-4">
                                <div class="flex flex-row items-center">
                                    <img
                                        :src="'/storage/' + payMethod?.gambar"
                                        alt=""
                                        class="w-12 h-12 mr-3"
                                    />
                                    <div class="font-bold text-white">
                                        <p class="text-xs sm:text-sm">
                                            {{ form.methodName }}
                                        </p>
                                        <p
                                            class="text-xs sm:text-sm text-primary"
                                        >
                                            Fee
                                            {{
                                                payMethod?.fee_type ===
                                                "percent"
                                                    ? `${payMethod?.fee}%`
                                                    : `${formatCurrency(
                                                          payMethod?.fee
                                                      )}`
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <div
                                        class="flex items-center justify-between p-3 mb-2 rounded-lg bg-secondary/10"
                                    >
                                        <p
                                            class="text-xs font-medium md:text-sm text-primary-text/80"
                                        >
                                            Nominal
                                        </p>
                                        <p
                                            class="text-xs font-medium md:text-sm text-primary-text/80"
                                        >
                                            {{ formatCurrency(form.nominal) }}
                                        </p>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-3 mb-2 rounded-lg bg-secondary/10"
                                    >
                                        <p
                                            class="text-xs font-medium md:text-sm text-primary-text/80"
                                        >
                                            Biaya Admin
                                        </p>
                                        <p
                                            class="text-xs font-medium md:text-sm text-primary-text/80"
                                        >
                                            {{ formatCurrency(feeAmount) }}
                                        </p>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-3 mb-2 rounded-lg bg-secondary/10"
                                    >
                                        <p
                                            class="text-xs font-medium md:text-sm text-primary-text/80"
                                        >
                                            Total
                                        </p>
                                        <p
                                            class="text-xs font-medium md:text-sm text-primary-text/80"
                                        >
                                            {{
                                                formatCurrency(
                                                    feeAmount + form.nominal
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <!-- tombol bayar -->
                                    <button
                                        @click="submit"
                                        type="submit"
                                        class="w-full px-4 py-2 text-white transition-all duration-200 rounded-lg shadow bg-primary/70 hover:bg-primary-hover hover:shadow-glow-primary"
                                    >
                                        Bayar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- fallback if data method is not selected yet -->
                        <div
                            class="p-6 border shadow-xl rounded-2xl bg-gradient-to-b from-primary/30 to-content-background/80 border-secondary/10"
                            v-else
                        >
                            <div class="mb-4">
                                <div class="flex flex-row items-center">
                                    <Image
                                        class="w-12 h-12 mr-3 text-secondary/20"
                                    />
                                </div>
                                <div class="my-4">
                                    <div
                                        class="flex items-center justify-between p-3 py-5 mb-2 rounded-lg bg-secondary/10"
                                    >
                                        <p
                                            class="text-xs font-medium md:text-sm text-primary-text/80"
                                        ></p>
                                        <p
                                            class="text-xs font-medium md:text-sm text-primary-text/80"
                                        ></p>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-3 py-5 mb-2 rounded-lg bg-secondary/10"
                                    >
                                        <p
                                            class="text-xs font-medium md:text-sm text-primary-text/80"
                                        ></p>
                                        <p
                                            class="text-xs font-medium md:text-sm text-primary-text/80"
                                        ></p>
                                    </div>

                                    <!-- tombol bayar -->
                                    <div
                                        class="w-full px-4 py-5 text-white transition-all duration-200 rounded-lg bg-primary/20"
                                    ></div>
                                </div>
                                <span
                                    class="block text-xs md:text-sm text-secondary"
                                    v-if="!form.nominal"
                                    >Pilih nominal deposit terlebih
                                    dahulu..</span
                                >
                                <span
                                    class="block text-xs md:text-sm text-secondary"
                                    v-else-if="!payMethod"
                                    >Pilih metode pembayaran terlebih
                                    dahulu..</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

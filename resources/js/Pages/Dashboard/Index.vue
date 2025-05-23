<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import DashboardSidebar from "@/Components/Dashboard/Sidebar.vue";
import { ref } from "vue";
import { usePage, router } from "@inertiajs/vue3";

const page = usePage();

const props = defineProps({
    pembelian: { type: Object },
    totalPembelian: { type: Number },
    pendingPembelian: { type: Number },
    processingPembelian: { type: Number },
    completedPembelian: { type: Number },
    failedPembelian: { type: Number },
});

const sidebarCollapsed = ref(false);
function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
}

// format indonesian phone number from props
const formatPhoneNumber = (phone) => {
    if (!phone) return "";

    // Hilangkan spasi & karakter non-digit (jika perlu)
    const cleanNumber = phone.replace(/\D/g, "");

    // Jika dimulai dengan 0, ganti dengan +62
    if (cleanNumber.startsWith("0")) {
        return "+62" + cleanNumber.slice(1);
    }

    // Kalau sudah +62 atau bukan angka valid, biarkan
    return cleanNumber;
};

// get 2 first characters from user username
const getInitials = (name) => {
    const words = name.split(" ");
    return words
        .slice(0, 2)
        .map((word) => word.charAt(0).toUpperCase())
        .join("");
};

// format balance
const formatCurrency = (amount) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(amount);
};

// format date
const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("id-ID", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

// format status with badge color
const formatStatus = (status) => {
    const colors = {
        completed: "bg-green-500/60 text-green-100 shadow-glow-green",
        pending: "bg-yellow-500/60 text-yellow-900 shadow-glow-yellow",
        processing: "bg-blue-500/60 text-blue-100 shadow-glow-blue",
        failed: "bg-red-500/60 text-red-100 shadow-glow-red",
        cancelled: "bg-gray-400/80 text-gray-900",
    };
    const label = {
        completed: "Berhasil",
        pending: "Menunggu",
        processing: "Memproses",
        failed: "Gagal",
        cancelled: "Dibatalkan",
    };
    return `<span class="px-2 py-1 rounded text-xs font-semibold ${
        colors[status] || "bg-gray-500 text-white"
    }">${label[status] || status}</span>`;
};
</script>

<template>
    <GuestLayout>
        <div
            class="flex flex-col min-h-screen mx-auto bg-transparent lg:flex-row max-w-7xl"
        >
            <!-- Sidebar (collapsible) -->
            <DashboardSidebar
                :collapsed="sidebarCollapsed"
                @toggle="toggleSidebar"
            />

            <!-- Main Dashboard Content -->
            <div class="flex-1 p-4 transition-all duration-300 lg:p-8">
                <!-- Security Callout -->
                <div class="mb-6">
                    <div
                        class="flex items-center gap-3 p-4 rounded-lg shadow bg-primary/20 text-primary"
                    >
                        <div class="flex-shrink-0">
                            <svg
                                class="w-8 h-8 text-secondary animate-pulse"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke-opacity="0.15"
                                    fill="none"
                                />
                                <rect
                                    x="9"
                                    y="9"
                                    width="6"
                                    height="6"
                                    rx="1"
                                    class="fill-secondary opacity-30"
                                />
                            </svg>
                        </div>
                        <div>
                            <div class="font-semibold text-primary-text">
                                Tingkatkan keamanan!
                            </div>
                            <div class="text-xs text-white/70">
                                Gunakan fitur 2FA agar akun kamu lebih aman.
                                Klik
                                <a href="#" class="underline text-secondary"
                                    >disini</a
                                >
                                untuk melakukan pengaturan!
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Cards Row -->
                <div class="flex flex-col gap-6 mb-6 lg:flex-row">
                    <!-- Profile Card -->
                    <div class="flex-1 lg:max-w-sm">
                        <div
                            class="bg-secondary/20 rounded-2xl shadow-md p-6 flex items-center gap-5 relative min-h-[170px]"
                        >
                            <div
                                class="flex items-center justify-center w-16 h-16 text-3xl text-white uppercase border-4 border-white rounded-full shadow bg-gradient-to-br from-secondary to-primary ring-2 ring-primary"
                            >
                                {{ getInitials(page.props.auth.user.username) }}
                            </div>
                            <div>
                                <div class="text-xl font-semibold text-white">
                                    {{ page.props.auth.user.username }}
                                </div>
                                <div class="text-xs uppercase text-secondary">
                                    {{ page.props.auth.role }}
                                </div>
                                <div
                                    class="flex items-center gap-2 mt-2 text-sm text-white/80"
                                >
                                    <span>&#x260E;</span>
                                    <span>{{
                                        formatPhoneNumber(
                                            page.props.auth.user.phone
                                        )
                                    }}</span>
                                </div>
                            </div>
                            <button
                                class="absolute top-2 right-2 text-secondary hover:text-primary"
                            >
                                <svg
                                    width="22"
                                    height="22"
                                    fill="none"
                                    stroke="currentColor"
                                >
                                    <circle
                                        cx="11"
                                        cy="11"
                                        r="9"
                                        stroke="currentColor"
                                    />
                                    <path
                                        d="M11 7v4l2 2"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <!-- Balance Card -->
                    <div class="flex-1">
                        <div
                            class="relative bg-secondary/10 rounded-2xl shadow-md p-6 flex flex-col gap-3 min-h-[170px]"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <div
                                    class="flex items-center gap-2 font-semibold text-secondary"
                                >
                                    <svg
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle cx="12" cy="12" r="10" />
                                        <path d="M16 12l-4 4-4-4" />
                                    </svg>
                                    Balance
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        class="px-4 py-1 text-sm font-bold text-white transition rounded-lg shadow bg-gradient-to-r from-primary to-secondary hover:scale-105"
                                        @click="
                                            router.visit(
                                                route('dashboard.topup')
                                            )
                                        "
                                    >
                                        Top Up
                                    </button>
                                    <!-- <button
                                        class="px-4 py-1 text-sm font-semibold text-white transition rounded-lg bg-gray-500/60 hover:bg-gray-700/80"
                                    >
                                        Redeem
                                    </button> -->
                                </div>
                            </div>
                            <div class="flex flex-wrap items-end gap-1">
                                <div class="text-2xl font-bold text-white">
                                    {{
                                        formatCurrency(
                                            page.props.auth.user.saldo
                                        )
                                    }}
                                </div>
                                <div class="ml-2 text-xs text-white/50">
                                    IDR
                                    <!-- <span
                                        class="text-base font-normal text-secondary"
                                        >NaelCoin</span
                                    > -->
                                </div>
                            </div>
                            <div class="h-10 mt-3">
                                <!-- Placeholder for micro-graph (empty for now) -->
                                <div
                                    class="w-full h-full rounded bg-gradient-to-r from-secondary/30 to-primary/10"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction Overview -->
                <div>
                    <div class="mb-3 text-lg font-bold text-white">
                        Transaksi Hari Ini
                    </div>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
                        <div
                            class="flex flex-col items-center p-5 text-white rounded-xl bg-secondary/10"
                        >
                            <div class="text-lg font-bold">
                                {{ props.totalPembelian }}
                            </div>
                            <div class="mt-2 text-xs text-white/70">
                                Total Transaksi
                            </div>
                        </div>
                        <div
                            class="flex flex-col items-center p-5 text-white bg-yellow-600/30 rounded-xl"
                        >
                            <div class="text-lg font-bold">
                                {{ props.pendingPembelian }}
                            </div>
                            <div class="mt-2 text-xs">Menunggu</div>
                        </div>
                        <div
                            class="flex flex-col items-center p-5 text-white rounded-xl bg-secondary/10"
                        >
                            <div class="text-lg font-bold">
                                {{ props.processingPembelian }}
                            </div>
                            <div class="mt-2 text-xs text-white/70">
                                Diproses
                            </div>
                        </div>
                        <div
                            class="flex flex-col items-center p-5 text-white bg-green-700/30 rounded-xl"
                        >
                            <div class="text-lg font-bold">
                                {{ props.completedPembelian }}
                            </div>
                            <div class="mt-2 text-xs">Sukses</div>
                        </div>
                        <div
                            class="flex flex-col items-center p-5 text-white bg-red-700/30 rounded-xl"
                        >
                            <div class="text-lg font-bold">
                                {{ props.failedPembelian }}
                            </div>
                            <div class="mt-2 text-xs">Gagal</div>
                        </div>
                    </div>
                </div>

                <!-- Latest Transaction Table -->
                <div class="mt-8">
                    <div class="mb-3 text-lg font-semibold text-white">
                        Riwayat Transaksi Terbaru Hari Ini
                    </div>
                    <div class="overflow-x-auto rounded-lg shadow">
                        <table
                            class="w-full min-w-[600px] bg-secondary/10 text-primary-text"
                        >
                            <thead>
                                <tr class="bg-primary/10 text-secondary">
                                    <th class="p-3 text-left">Nomor Invoice</th>
                                    <th class="p-3 text-left">Item</th>
                                    <th class="p-3 text-left">User Input</th>
                                    <th class="p-3 text-right">Harga</th>
                                    <th class="p-3 text-left">Tanggal</th>
                                    <th class="p-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="border-b border-white/5"
                                    v-for="trx in props.pembelian"
                                >
                                    <td class="p-3">{{ trx.order_id }}</td>
                                    <td class="p-3">
                                        {{ trx.layanan.nama_layanan }}
                                    </td>
                                    <td class="p-3">
                                        {{
                                            trx.input_zone
                                                ? `${trx.input_id} (${trx.input_zone})`
                                                : trx.input_id
                                        }}
                                    </td>
                                    <td class="p-3 text-right">
                                        {{ trx.total_price }}
                                    </td>
                                    <td class="p-3">
                                        <!-- format date based on locale -->
                                        {{ formatDate(trx.created_at) }}
                                    </td>
                                    <td
                                        class="p-3"
                                        v-html="formatStatus(trx.status)"
                                    ></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import DashboardSidebar from "@/Components/Dashboard/Sidebar.vue";
import { ref } from "vue";

const sidebarCollapsed = ref(false);
function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
}
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
                                JU
                            </div>
                            <div>
                                <div class="text-xl font-semibold text-white">
                                    Justin
                                </div>
                                <div class="text-xs uppercase text-secondary">
                                    Member
                                </div>
                                <div
                                    class="flex items-center gap-2 mt-2 text-sm text-white/80"
                                >
                                    <span>&#x260E;</span>
                                    <span>+6285931018333</span>
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
                                    >
                                        Top Up
                                    </button>
                                    <button
                                        class="px-4 py-1 text-sm font-semibold text-white transition rounded-lg bg-gray-500/60 hover:bg-gray-700/80"
                                    >
                                        Redeem
                                    </button>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-end gap-1">
                                <div class="text-2xl font-bold text-white">
                                    0
                                    <span
                                        class="text-base font-normal text-secondary"
                                        >VSC</span
                                    >
                                </div>
                                <div class="ml-2 text-xs text-white/50">
                                    IDR
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
                        Transaksi Bulan Ini
                    </div>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
                        <div
                            class="flex flex-col items-center p-5 text-white rounded-xl bg-secondary/10"
                        >
                            <div class="text-lg font-bold">0</div>
                            <div class="mt-2 text-xs text-white/70">
                                Total Transaksi
                            </div>
                        </div>
                        <div
                            class="flex flex-col items-center p-5 text-white rounded-xl bg-secondary/10"
                        >
                            <div class="text-lg font-bold">0</div>
                            <div class="mt-2 text-xs text-white/70">
                                Total Penjualan
                            </div>
                        </div>
                        <div
                            class="flex flex-col items-center p-5 text-white bg-yellow-600/30 rounded-xl"
                        >
                            <div class="text-lg font-bold">0</div>
                            <div class="mt-2 text-xs">Menunggu</div>
                        </div>
                        <div
                            class="flex flex-col items-center p-5 text-white bg-green-700/30 rounded-xl"
                        >
                            <div class="text-lg font-bold">0</div>
                            <div class="mt-2 text-xs">Sukses</div>
                        </div>
                        <div
                            class="flex flex-col items-center p-5 text-white bg-red-700/30 rounded-xl"
                        >
                            <div class="text-lg font-bold">0</div>
                            <div class="mt-2 text-xs">Gagal</div>
                        </div>
                    </div>
                </div>

                <!-- Latest Transaction Table -->
                <div class="mt-8">
                    <div class="mb-3 text-lg font-semibold text-white">
                        Riwayat Transaksi Terbaru Bulan Ini
                    </div>
                    <div class="overflow-x-auto rounded-lg shadow">
                        <table
                            class="w-full min-w-[600px] bg-secondary/10 text-primary-text"
                        >
                            <thead>
                                <tr class="bg-primary/10 text-secondary">
                                    <th class="p-3 text-left">Nomor Invoice</th>
                                    <th class="p-3 text-left">ID Trx</th>
                                    <th class="p-3 text-left">Item</th>
                                    <th class="p-3 text-left">User Input</th>
                                    <th class="p-3 text-right">Harga</th>
                                    <th class="p-3 text-left">Tanggal</th>
                                    <th class="p-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-white/5">
                                    <td class="p-3">-</td>
                                    <td class="p-3">-</td>
                                    <td class="p-3">-</td>
                                    <td class="p-3">-</td>
                                    <td class="p-3 text-right">-</td>
                                    <td class="p-3">-</td>
                                    <td class="p-3">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

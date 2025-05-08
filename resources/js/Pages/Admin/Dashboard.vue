<script setup>
import { ref, computed, onMounted } from "vue";
import { Head, usePage, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    ChartArea,
    ChartPie,
    ArrowUp,
    ArrowDown,
    FileDown,
    Calendar,
    Loader,
} from "lucide-vue-next";

// Import chart libraries
import { Line } from "vue-chartjs";
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
    ArcElement,
} from "chart.js";
import { Doughnut } from "vue-chartjs";

// Register ChartJS components
ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
    ArcElement
);

// Get data from props
const props = defineProps({
    metrics: Object,
    charts: Object,
    tables: Object,
    period: String,
});

// Reactive state
const isLoading = ref(false);
const selectedPeriod = ref(props.period || "week");

// Format currency
const formatCurrency = (value) => {
    // Ubah ke bilangan bulat dulu (bisa pakai Math.floor, Math.round, atau Math.trunc)
    const integerValue = Math.floor(value); // atau Math.round(value) jika ingin pembulatan

    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        maximumFractionDigits: 0, // pastikan tidak ada desimal
    }).format(integerValue);
};

// Line chart configuration
const revenueChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            ticks: {
                color: "#F1F0FB", // Soft Gray
            },
            grid: {
                color: "#2226",
            },
        },
        x: {
            ticks: {
                color: "#F1F0FB", // Soft Gray
            },
            grid: {
                color: "#2226",
            },
        },
    },
    plugins: {
        tooltip: {
            mode: "index",
            intersect: false,
        },
        legend: {
            display: true,
            position: "top",
            labels: {
                color: "#F1F0FB", // Soft Gray
            },
        },
    },
    interaction: {
        intersect: false,
    },
    elements: {
        line: {
            tension: 0.4,
        },
        point: {
            radius: 4,
            hoverRadius: 6,
            borderWidth: 2,
            backgroundColor: "#1F2937", // Dark space
        },
    },
};

// Doughnut chart configuration
const pieChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: "top",
            labels: {
                color: "#F1F0FB", // Soft Gray
                padding: 16,
            },
        },
    },
    cutout: "60%",
    animation: {
        animateRotate: true,
        animateScale: true,
    },
};

// Helper function for status styling
const getStatusClass = (status) => {
    switch (status) {
        case "completed":
            return "bg-green-500/20 text-green-400";
        case "pending":
            return "bg-yellow-500/20 text-yellow-400";
        case "processing":
            return "bg-blue-500/20 text-blue-400";
        case "failed":
            return "bg-red-500/20 text-red-400";
        case "cancelled":
            return "bg-gray-500/20 text-gray-400";
        default:
            return "bg-gray-500/20 text-gray-400";
    }
};

// Handle period change
const changePeriod = (period) => {
    if (selectedPeriod.value === period) return;

    isLoading.value = true;
    selectedPeriod.value = period;

    router.visit(route("admin.dashboard", { period }), {
        preserveScroll: true,
        onSuccess: () => {
            isLoading.value = false;
        },
    });
};

// Handle data export
const exportData = (type) => {
    const endpoint = route("admin.dashboard.export", {
        period: selectedPeriod.value,
        type: type, // 'excel', 'csv', 'pdf'
    });

    window.open(endpoint, "_blank");
};

// Cosmic particle effect for chart
const initCosmicParticles = () => {
    const canvas = document.getElementById("cosmicParticles");
    if (!canvas) return;

    const ctx = canvas.getContext("2d");
    const particles = [];

    // Configure canvas
    canvas.width = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;

    // Create particles
    for (let i = 0; i < 50; i++) {
        particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            radius: Math.random() * 2 + 0.5,
            color: `rgba(155, 135, 245, ${Math.random() * 0.5 + 0.25})`,
            vx: Math.random() * 0.5 - 0.25,
            vy: Math.random() * 0.5 - 0.25,
        });
    }

    // Animation function
    const animate = () => {
        requestAnimationFrame(animate);
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        particles.forEach((particle) => {
            // Move particle
            particle.x += particle.vx;
            particle.y += particle.vy;

            // Wrap around edges
            if (particle.x < 0) particle.x = canvas.width;
            if (particle.x > canvas.width) particle.x = 0;
            if (particle.y < 0) particle.y = canvas.height;
            if (particle.y > canvas.height) particle.y = 0;

            // Draw particle
            ctx.beginPath();
            ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
            ctx.fillStyle = particle.color;
            ctx.fill();
        });
    };

    // Start animation
    animate();

    // Handle resize
    window.addEventListener("resize", () => {
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
    });
};

// Initialize animation on component mount
onMounted(() => {
    setTimeout(initCosmicParticles, 500);
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout title="Dashboard">
        <!-- <template #actions> -->
        <div class="flex items-center gap-4 px-6">
            <!-- Period Selector -->
            <div
                class="flex overflow-hidden border border-gray-700 rounded-lg bg-dark-card"
            >
                <button
                    v-for="period in ['day', 'week', 'month', 'year']"
                    :key="period"
                    @click="changePeriod(period)"
                    :class="[
                        'px-3 py-2 text-sm font-medium transition-all duration-300',
                        selectedPeriod === period
                            ? 'bg-primary/20 text-primary border-b-2 border-primary'
                            : 'text-gray-400 hover:text-white',
                    ]"
                >
                    <Calendar class="inline-block w-4 h-4 mr-1" />

                    {{ period.charAt(0).toUpperCase() + period.slice(1) }}
                </button>
            </div>

            <!-- Export Button -->
            <div class="relative" x-data="{ open: false }">
                <button
                    @click="exportData('excel')"
                    class="flex items-center px-4 py-2 text-sm font-medium text-white transition-all duration-300 border rounded-lg bg-primary/20 border-primary/30 hover:bg-primary/30"
                >
                    <FileDown class="w-4 h-4 mr-2" />
                    Export Data
                </button>
            </div>
        </div>
        <!-- </template> -->

        <div class="p-6">
            <!-- Loading Overlay -->
            <div
                v-if="isLoading"
                class="fixed inset-0 z-50 flex items-center justify-center bg-dark-card/80"
            >
                <div class="flex flex-col items-center">
                    <Loader class="w-12 h-12 text-primary animate-spin" />
                    <span class="mt-4 text-white">Loading cosmic data...</span>
                </div>
            </div>

            <!-- Cosmic Particles Canvas -->
            <canvas
                id="cosmicParticles"
                class="absolute inset-0 pointer-events-none"
            ></canvas>

            <!-- Stats Grid -->
            <div
                class="grid grid-cols-1 gap-6 mb-8 sm:grid-cols-2 lg:grid-cols-4"
            >
                <!-- Users Stat Card -->
                <div
                    class="p-6 transition-all duration-300 border border-gray-700 rounded-lg shadow-lg bg-gradient-to-br from-dark-card to-dark-lighter hover:shadow-glow-primary"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-400">
                                Total Users
                            </p>
                            <h2 class="mt-2 text-3xl font-bold text-white">
                                {{ metrics?.users?.total.toLocaleString() }}
                            </h2>
                            <div
                                :class="[
                                    'flex items-center mt-2 text-sm',
                                    metrics?.users?.isPositive
                                        ? 'text-green-400'
                                        : 'text-red-400',
                                ]"
                            >
                                <ArrowUp
                                    v-if="metrics?.users?.isPositive"
                                    class="w-4 h-4 mr-1"
                                />
                                <ArrowDown v-else class="w-4 h-4 mr-1" />
                                <span>
                                    {{
                                        Math.abs(
                                            metrics?.users?.growthPercent || 0
                                        )
                                    }}%
                                    {{
                                        metrics?.users?.isPositive
                                            ? "increase"
                                            : "decrease"
                                    }}
                                </span>
                            </div>
                        </div>
                        <div class="p-3 rounded-lg bg-indigo-500/20">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-8 h-8 text-primary"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                                />
                            </svg>
                        </div>
                    </div>
                    <!-- Sparkline graph -->
                    <div
                        class="w-full h-2 mt-4 overflow-hidden bg-gray-700 rounded-full"
                    >
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-primary to-secondary animate-pulse"
                            :style="{
                                width: `${Math.max(
                                    5,
                                    Math.abs(
                                        metrics?.users?.growthPercent || 0
                                    ) * 5
                                )}%`,
                            }"
                        ></div>
                    </div>
                </div>

                <!-- Revenue Stat Card -->
                <div
                    class="p-6 transition-all duration-300 border border-gray-700 rounded-lg shadow-lg bg-gradient-to-br from-dark-card to-dark-lighter hover:shadow-glow-secondary"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-400">
                                Total Revenue
                            </p>
                            <h2 class="mt-2 text-3xl font-bold text-white">
                                {{
                                    formatCurrency(metrics?.revenue?.total || 0)
                                }}
                            </h2>
                            <div
                                :class="[
                                    'flex items-center mt-2 text-sm',
                                    metrics?.revenue?.isPositive
                                        ? 'text-green-400'
                                        : 'text-red-400',
                                ]"
                            >
                                <ArrowUp
                                    v-if="metrics?.revenue?.isPositive"
                                    class="w-4 h-4 mr-1"
                                />
                                <ArrowDown v-else class="w-4 h-4 mr-1" />
                                <span>
                                    {{
                                        Math.abs(
                                            metrics?.revenue?.growthPercent || 0
                                        )
                                    }}%
                                    {{
                                        metrics?.revenue?.isPositive
                                            ? "increase"
                                            : "decrease"
                                    }}
                                </span>
                            </div>
                        </div>
                        <div class="p-3 rounded-lg bg-teal-500/20">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-8 h-8 text-secondary"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                    </div>
                    <!-- Sparkline graph -->
                    <div
                        class="w-full h-2 mt-4 overflow-hidden bg-gray-700 rounded-full"
                    >
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-secondary to-primary animate-pulse"
                            :style="{
                                width: `${Math.max(
                                    5,
                                    Math.abs(
                                        metrics?.revenue?.growthPercent || 0
                                    ) * 5
                                )}%`,
                            }"
                        ></div>
                    </div>
                </div>

                <!-- Orders Stat Card -->
                <div
                    class="p-6 transition-all duration-300 border border-gray-700 rounded-lg shadow-lg bg-gradient-to-br from-dark-card to-dark-lighter hover:shadow-glow-primary"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-400">
                                Total Orders
                            </p>
                            <h2 class="mt-2 text-3xl font-bold text-white">
                                {{ metrics?.orders?.total.toLocaleString() }}
                            </h2>
                            <div
                                :class="[
                                    'flex items-center mt-2 text-sm',
                                    metrics?.orders?.isPositive
                                        ? 'text-green-400'
                                        : 'text-red-400',
                                ]"
                            >
                                <ArrowUp
                                    v-if="metrics?.orders?.isPositive"
                                    class="w-4 h-4 mr-1"
                                />
                                <ArrowDown v-else class="w-4 h-4 mr-1" />
                                <span>
                                    {{
                                        Math.abs(
                                            metrics?.orders?.growthPercent || 0
                                        )
                                    }}%
                                    {{
                                        metrics?.orders?.isPositive
                                            ? "increase"
                                            : "decrease"
                                    }}
                                </span>
                            </div>
                        </div>
                        <div class="p-3 rounded-lg bg-pink-500/20">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-8 h-8 text-pink-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                                />
                            </svg>
                        </div>
                    </div>
                    <!-- Sparkline graph -->
                    <div
                        class="w-full h-2 mt-4 overflow-hidden bg-gray-700 rounded-full"
                    >
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-primary to-secondary animate-pulse"
                            :style="{
                                width: `${Math.max(
                                    5,
                                    Math.abs(
                                        metrics?.orders?.growthPercent || 0
                                    ) * 5
                                )}%`,
                            }"
                        ></div>
                    </div>
                </div>

                <!-- Products Stat Card -->
                <div
                    class="p-6 transition-all duration-300 border border-gray-700 rounded-lg shadow-lg bg-gradient-to-br from-dark-card to-dark-lighter hover:shadow-glow-secondary"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-400">
                                Active Products
                            </p>
                            <h2 class="mt-2 text-3xl font-bold text-white">
                                {{ metrics?.products?.total }}
                            </h2>
                            <div
                                :class="[
                                    'flex items-center mt-2 text-sm',
                                    metrics?.products?.isPositive
                                        ? 'text-green-400'
                                        : 'text-red-400',
                                ]"
                            >
                                <ArrowUp
                                    v-if="metrics?.products?.isPositive"
                                    class="w-4 h-4 mr-1"
                                />
                                <ArrowDown v-else class="w-4 h-4 mr-1" />
                                <span>
                                    {{
                                        Math.abs(
                                            metrics?.products?.growthPercent ||
                                                0
                                        )
                                    }}%
                                    {{
                                        metrics?.products?.isPositive
                                            ? "increase"
                                            : "decrease"
                                    }}
                                </span>
                            </div>
                        </div>
                        <div class="p-3 rounded-lg bg-purple-500/20">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-8 h-8 text-purple-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                />
                            </svg>
                        </div>
                    </div>
                    <!-- Sparkline graph -->
                    <div
                        class="w-full h-2 mt-4 overflow-hidden bg-gray-700 rounded-full"
                    >
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-secondary to-primary animate-pulse"
                            :style="{
                                width: `${Math.max(
                                    5,
                                    Math.abs(
                                        metrics?.products?.growthPercent || 0
                                    ) * 5
                                )}%`,
                            }"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-2">
                <!-- Revenue Chart -->
                <div
                    class="relative p-6 overflow-hidden border border-gray-700 rounded-lg shadow-lg bg-dark-card"
                >
                    <h3
                        class="flex items-center mb-6 text-xl font-semibold text-white"
                    >
                        <ChartArea class="w-5 h-5 mr-2" />
                        Revenue Trend
                    </h3>

                    <!-- Chart Background Effects -->
                    <div
                        class="absolute inset-0 z-0 bg-gradient-to-br from-primary/5 to-secondary/5"
                    ></div>
                    <div
                        class="absolute bottom-0 left-0 right-0 z-0 h-1/3 bg-gradient-to-t from-primary/10 to-transparent"
                    ></div>

                    <!-- Chart Container -->
                    <div class="relative z-10 w-full h-80">
                        <Line
                            v-if="charts?.revenue_trend?.labels?.length"
                            :data="{
                                labels: charts.revenue_trend.labels,
                                datasets: charts.revenue_trend.datasets,
                            }"
                            :options="revenueChartOptions"
                        />
                        <div
                            v-else
                            class="flex items-center justify-center h-full"
                        >
                            <p class="text-gray-400">
                                No data available for selected period
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Orders Chart -->
                <div
                    class="relative p-6 overflow-hidden border border-gray-700 rounded-lg shadow-lg bg-dark-card"
                >
                    <h3
                        class="flex items-center mb-6 text-xl font-semibold text-white"
                    >
                        <ChartPie class="w-5 h-5 mr-2" />
                        Order Statistics
                    </h3>

                    <!-- Chart Background Effects -->
                    <div
                        class="absolute inset-0 z-0 bg-gradient-to-br from-secondary/5 to-primary/5"
                    ></div>
                    <div
                        class="absolute bottom-0 left-0 right-0 z-0 h-1/3 bg-gradient-to-t from-secondary/10 to-transparent"
                    ></div>

                    <!-- Chart Container -->
                    <div class="relative z-10 w-full h-80">
                        <Doughnut
                            v-if="
                                charts?.order_stats?.statusDistribution?.labels
                                    ?.length
                            "
                            :data="charts.order_stats.statusDistribution"
                            :options="pieChartOptions"
                        />
                        <div
                            v-else
                            class="flex items-center justify-center h-full"
                        >
                            <p class="text-gray-400">
                                No data available for selected period
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions and Top Products -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Recent Transactions -->
                <div
                    class="overflow-hidden border border-gray-700 rounded-lg shadow-lg bg-dark-card"
                >
                    <div
                        class="flex items-center justify-between p-6 border-b border-gray-700"
                    >
                        <h3 class="text-xl font-semibold text-white">
                            Recent Transactions
                        </h3>
                        <router-link
                            :to="{ name: 'pembelians.index' }"
                            class="transition-colors text-secondary hover:text-secondary-hover"
                        >
                            View All
                        </router-link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-full">
                            <thead>
                                <tr class="bg-dark-lighter">
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase"
                                    >
                                        Transaction ID
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase"
                                    >
                                        User
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase"
                                    >
                                        Service
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase"
                                    >
                                        Amount
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase"
                                    >
                                        Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr
                                    v-for="transaction in tables?.recent_transactions"
                                    :key="transaction.id"
                                    class="transition-colors hover:bg-dark-lighter"
                                >
                                    <td
                                        class="px-6 py-4 text-sm font-medium text-white whitespace-nowrap"
                                    >
                                        {{ transaction.id }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-gray-300 whitespace-nowrap"
                                    >
                                        {{ transaction.user }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-gray-300 whitespace-nowrap"
                                    >
                                        {{ transaction.game }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm font-medium text-white whitespace-nowrap"
                                    >
                                        {{ formatCurrency(transaction.amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                getStatusClass(
                                                    transaction.status
                                                ),
                                                'px-2 py-1 text-xs rounded-full',
                                            ]"
                                        >
                                            {{ transaction.status }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-gray-300 whitespace-nowrap"
                                    >
                                        {{ transaction.date }}
                                    </td>
                                </tr>
                                <tr
                                    v-if="!tables?.recent_transactions?.length"
                                    class="hover:bg-dark-lighter"
                                >
                                    <td
                                        colspan="6"
                                        class="px-6 py-8 text-center text-gray-400"
                                    >
                                        No recent transactions found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Products -->
                <div
                    class="overflow-hidden border border-gray-700 rounded-lg shadow-lg bg-dark-card"
                >
                    <div
                        class="flex items-center justify-between p-6 border-b border-gray-700"
                    >
                        <h3 class="text-xl font-semibold text-white">
                            Top Services
                        </h3>
                        <router-link
                            :to="{ name: 'products.index' }"
                            class="transition-colors text-secondary hover:text-secondary-hover"
                        >
                            View All
                        </router-link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-full">
                            <thead>
                                <tr class="bg-dark-lighter">
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase"
                                    >
                                        Service
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase"
                                    >
                                        Sales
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase"
                                    >
                                        Revenue
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase"
                                    >
                                        Growth
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr
                                    v-for="product in tables?.top_products"
                                    :key="product.id"
                                    class="transition-colors hover:bg-dark-lighter"
                                >
                                    <td
                                        class="px-6 py-4 text-sm font-medium text-white whitespace-nowrap"
                                    >
                                        {{ product.name }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-gray-300 whitespace-nowrap"
                                    >
                                        {{ product.sales.toLocaleString() }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm font-medium text-white whitespace-nowrap"
                                    >
                                        {{ formatCurrency(product.revenue) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                :class="[
                                                    product.growth >= 0
                                                        ? 'text-green-400'
                                                        : 'text-red-400',
                                                    'flex items-center',
                                                ]"
                                            >
                                                <ArrowUp
                                                    v-if="product.growth >= 0"
                                                    class="w-4 h-4 mr-1"
                                                />
                                                <ArrowDown
                                                    v-else
                                                    class="w-4 h-4 mr-1"
                                                />
                                                <span
                                                    >{{
                                                        Math.abs(
                                                            product.growth
                                                        ).toFixed(1)
                                                    }}%</span
                                                >
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr
                                    v-if="!tables?.top_products?.length"
                                    class="hover:bg-dark-lighter"
                                >
                                    <td
                                        colspan="4"
                                        class="px-6 py-8 text-center text-gray-400"
                                    >
                                        No top products found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-8">
                <h3 class="mb-6 text-xl font-semibold text-white">
                    Quick Actions
                </h3>
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <!-- Add Product -->
                    <div
                        class="flex items-center p-6 space-x-4 transition-all duration-300 border border-gray-700 rounded-lg shadow-lg cursor-pointer bg-gradient-to-br from-dark-card to-dark-lighter hover:shadow-glow-primary"
                        @click="$inertia.visit(route('products.index'))"
                    >
                        <div class="p-3 rounded-lg bg-primary/20">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 text-primary"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-medium text-white">
                                Add New Product
                            </h4>
                            <p class="mt-1 text-sm text-gray-400">
                                Create a new product listing
                            </p>
                        </div>
                    </div>

                    <!-- Manage Orders -->
                    <div
                        class="flex items-center p-6 space-x-4 transition-all duration-300 border border-gray-700 rounded-lg shadow-lg cursor-pointer bg-gradient-to-br from-dark-card to-dark-lighter hover:shadow-glow-secondary"
                        @click="$inertia.visit(route('pembelians.index'))"
                    >
                        <div class="p-3 rounded-lg bg-secondary/20">
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
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-medium text-white">
                                Manage Orders
                            </h4>
                            <p class="mt-1 text-sm text-gray-400">
                                View and update order status
                            </p>
                        </div>
                    </div>

                    <!-- Add Banner -->
                    <div
                        class="flex items-center p-6 space-x-4 transition-all duration-300 border border-gray-700 rounded-lg shadow-lg cursor-pointer bg-gradient-to-br from-dark-card to-dark-lighter hover:shadow-glow-primary"
                        @click="$inertia.visit(route('banners.index'))"
                    >
                        <div class="p-3 rounded-lg bg-purple-500/20">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 text-purple-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-medium text-white">Add Banner</h4>
                            <p class="mt-1 text-sm text-gray-400">
                                Upload a new promotional banner
                            </p>
                        </div>
                    </div>

                    <!-- Website Settings -->
                    <div
                        class="flex items-center p-6 space-x-4 transition-all duration-300 border border-gray-700 rounded-lg shadow-lg cursor-pointer bg-gradient-to-br from-dark-card to-dark-lighter hover:shadow-glow-secondary"
                        @click="$inertia.visit(route('admin.settings'))"
                    >
                        <div class="p-3 rounded-lg bg-pink-500/20">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 text-pink-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-medium text-white">
                                Website Settings
                            </h4>
                            <p class="mt-1 text-sm text-gray-400">
                                Update site configuration
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

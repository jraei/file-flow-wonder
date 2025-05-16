<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { ref, onMounted, computed, nextTick } from "vue";
import { Head } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import Chart from "chart.js/auto";

const props = defineProps({
    metrics: Object,
    charts: Object,
    tables: Object,
    period: String,
});

// Charts references
const revenueChartCanvas = ref(null);
const statusChartCanvas = ref(null);
let revenueChart = null;
let statusChart = null;

// Period selector
const periodOptions = [
    { label: "Day", value: "day" },
    { label: "Week", value: "week" },
    { label: "Month", value: "month" },
    { label: "Year", value: "year" },
    { label: "Lifetime", value: "lifetime" },
];

// Custom date range
const showCustomDatePicker = ref(false);
const customDateRange = ref({
    start: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000)
        .toISOString()
        .split("T")[0],
    end: new Date().toISOString().split("T")[0],
});

// Product and service data
const selectedProductId = ref("");
const products = ref([]);
const topServices = ref([]);
const loading = ref(false);

// Flashsales and vouchers
const flashsales = ref([]);
const vouchers = ref([]);

// Format helpers
const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("id-ID", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

// Change period and reload dashboard
const changePeriod = (newPeriod) => {
    router.visit(route("admin.dashboard", { period: newPeriod }), {
        preserveState: true,
        preserveScroll: true,
    });
};

// Apply custom date range
const applyCustomRange = () => {
    router.visit(
        route("admin.dashboard", {
            period: "custom",
            start_date: customDateRange.value.start,
            end_date: customDateRange.value.end,
        }),
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
    showCustomDatePicker.value = false;
};

// Load product-specific services
const loadProductServices = async () => {
    loading.value = true;
    try {
        const response = await fetch(
            `/admin/dashboard/product-services${
                selectedProductId.value ? `/${selectedProductId.value}` : ""
            }?period=${props.period}`
        );

        const data = await response.json();
        topServices.value = data.services;
    } catch (error) {
        console.error("Failed to load services:", error);
        topServices.value = [];
    } finally {
        loading.value = false;
    }
};

// Calculate flashsale progress percentage
const calculateFlashsaleProgress = (event) => {
    const now = new Date();
    const start = new Date(event.event_start_date);
    const end = new Date(event.event_end_date);

    const totalDuration = end - start;
    const elapsed = now - start;

    if (elapsed < 0) return 0;
    if (elapsed > totalDuration) return 100;

    return (elapsed / totalDuration) * 100;
};

// Export functions
const exportTransactions = () => {
    window.location.href = route("admin.dashboard.export", {
        type: "transactions",
        period: props.period,
    });
};

const exportTopProducts = () => {
    window.location.href = route("admin.dashboard.export", {
        type: "products",
        period: props.period,
    });
};

// Initialize UI components
const setupCharts = () => {
    // Revenue trend chart
    if (revenueChartCanvas.value && props.charts?.revenue_trend) {
        const ctx = revenueChartCanvas.value.getContext("2d");

        // Destroy previous chart instance if it exists
        if (revenueChart) revenueChart.destroy();

        revenueChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: props.charts.revenue_trend.labels,
                datasets: props.charts.revenue_trend.datasets,
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "top",
                        labels: {
                            color: "rgba(255, 255, 255, 0.7)",
                            font: {
                                family: "'Inter', sans-serif",
                            },
                        },
                    },
                    tooltip: {
                        backgroundColor: "rgba(0, 0, 0, 0.7)",
                        titleColor: "#fff",
                        bodyColor: "#fff",
                        borderColor: "rgba(255, 255, 255, 0.2)",
                        borderWidth: 1,
                    },
                },
                scales: {
                    x: {
                        grid: {
                            color: "rgba(255, 255, 255, 0.05)",
                        },
                        ticks: {
                            color: "rgba(255, 255, 255, 0.5)",
                            font: {
                                family: "'Inter', sans-serif",
                            },
                        },
                    },
                    y: {
                        grid: {
                            color: "rgba(255, 255, 255, 0.05)",
                        },
                        ticks: {
                            color: "rgba(255, 255, 255, 0.5)",
                            font: {
                                family: "'Inter', sans-serif",
                            },
                            callback: function (value) {
                                return new Intl.NumberFormat("id-ID", {
                                    style: "currency",
                                    currency: "IDR",
                                    notation: "compact",
                                    compactDisplay: "short",
                                }).format(value);
                            },
                        },
                    },
                },
                interaction: {
                    mode: "index",
                    intersect: false,
                },
                animation: {
                    duration: 1000,
                },
                elements: {
                    point: {
                        radius: 3,
                        hoverRadius: 5,
                    },
                    line: {
                        tension: 0.2,
                    },
                },
            },
        });
    }

    // Order status chart
    if (statusChartCanvas.value && props.charts?.order_stats) {
        const ctx = statusChartCanvas.value.getContext("2d");

        // Destroy previous chart instance if it exists
        if (statusChart) statusChart.destroy();

        statusChart = new Chart(ctx, {
            type: "doughnut",
            data: props.charts.order_stats.statusDistribution,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "right",
                        labels: {
                            color: "rgba(255, 255, 255, 0.7)",
                            font: {
                                family: "'Inter', sans-serif",
                            },
                        },
                    },
                    tooltip: {
                        backgroundColor: "rgba(0, 0, 0, 0.7)",
                        titleColor: "#fff",
                        bodyColor: "#fff",
                    },
                },
                cutout: "65%",
                animation: {
                    animateRotate: true,
                    animateScale: true,
                },
            },
        });
    }
};

// Load initial data
const loadDashboardData = async () => {
    // Load products for the dropdown
    try {
        const response = await fetch("/admin/dashboard/products");
        const data = await response.json();
        products.value = data;
    } catch (error) {
        console.error("Failed to load products:", error);
        products.value = [];
    }

    // Load flashsale events
    try {
        const response = await fetch(
            `/admin/dashboard/flashsales?period=${props.period}`
        );
        const data = await response.json();
        flashsales.value = data;
    } catch (error) {
        console.error("Failed to load flashsales:", error);
        flashsales.value = [];
    }

    // Load vouchers
    try {
        const response = await fetch(
            `/admin/dashboard/vouchers?period=${props.period}`
        );
        const data = await response.json();
        vouchers.value = data;
    } catch (error) {
        console.error("Failed to load vouchers:", error);
        vouchers.value = [];
    }

    // Load initial services data (all products)
    loadProductServices();
};

// Set up everything when component mounts
onMounted(() => {
    nextTick(() => {
        setupCharts();
        loadDashboardData();
    });
});
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout title="Dashboard">
        <div class="w-full space-y-8">
            <!-- Period Selector -->
            <div
                class="flex flex-wrap items-center gap-3 p-4 mb-6 rounded-lg shadow-md bg-secondary/10"
            >
                <h2 class="mr-4 text-xl font-bold text-white">
                    Dashboard Analytics
                </h2>
                <div class="flex gap-2">
                    <button
                        v-for="option in periodOptions"
                        :key="option.value"
                        @click="changePeriod(option.value)"
                        :class="[
                            'px-3 py-1 rounded-lg transition-all duration-300 text-sm',
                            period === option.value
                                ? 'bg-primary text-white shadow-lg'
                                : 'bg-secondary/20 text-white/70 hover:bg-secondary/30',
                        ]"
                    >
                        {{ option.label }}
                    </button>
                </div>

                <!-- Custom Date Range -->
                <div class="flex items-center gap-2 ml-auto">
                    <button
                        @click="showCustomDatePicker = !showCustomDatePicker"
                        class="flex items-center gap-1 px-3 py-1 text-sm transition-all duration-300 rounded-lg bg-secondary/20 text-white/70 hover:bg-secondary/30"
                    >
                        <span>Custom Range</span>
                        <svg
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M3 6h18M3 12h18M3 18h18"></path>
                        </svg>
                    </button>
                    <div
                        class="px-3 py-1 text-sm text-white rounded-lg bg-primary/20"
                        v-if="period === 'custom'"
                    >
                        {{ formatDate(customDateRange.start) }} -
                        {{ formatDate(customDateRange.end) }}
                    </div>
                </div>

                <!-- Custom Date Picker (conditionally rendered) -->
                <div
                    v-if="showCustomDatePicker"
                    class="flex flex-wrap items-end w-full gap-4 p-4 mt-4 rounded-lg bg-secondary/20"
                >
                    <div>
                        <label class="block mb-1 text-sm text-white/70"
                            >Start Date</label
                        >
                        <input
                            type="date"
                            v-model="customDateRange.start"
                            class="px-3 py-2 text-white border rounded bg-secondary/30 border-secondary/40"
                        />
                    </div>
                    <div>
                        <label class="block mb-1 text-sm text-white/70"
                            >End Date</label
                        >
                        <input
                            type="date"
                            v-model="customDateRange.end"
                            class="px-3 py-2 text-white border rounded bg-secondary/30 border-secondary/40"
                        />
                    </div>
                    <button
                        @click="applyCustomRange"
                        class="px-4 py-2 text-white transition-all duration-300 rounded-lg bg-primary hover:bg-primary/90"
                    >
                        Apply
                    </button>
                    <button
                        @click="showCustomDatePicker = false"
                        class="px-4 py-2 transition-all duration-300 rounded-lg bg-secondary/40 text-white/80 hover:bg-secondary/60"
                    >
                        Cancel
                    </button>
                </div>
            </div>

            <!-- Key Metrics Cards -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Users Metric -->
                <div class="p-5 rounded-lg shadow-md bg-primary/20">
                    <div class="flex justify-between mb-2">
                        <h3 class="text-primary/80">Users</h3>
                        <div
                            class="text-xs px-2 py-0.5 rounded-full"
                            :class="{
                                'bg-green-500/20 text-green-300':
                                    metrics.users.isPositive,
                                'bg-red-500/20 text-red-300':
                                    !metrics.users.isPositive,
                            }"
                        >
                            {{ metrics.users.growthPercent > 0 ? "+" : ""
                            }}{{ metrics.users.growthPercent }}%
                        </div>
                    </div>
                    <div class="mb-1 text-2xl font-bold text-white">
                        {{ metrics.users.total }}
                    </div>
                    <div class="text-sm text-white/60">
                        {{ metrics.users.newUsers }} new in this period
                    </div>
                </div>

                <!-- Revenue Metric -->
                <div class="p-5 rounded-lg shadow-md bg-secondary/10">
                    <div class="flex justify-between mb-2">
                        <h3 class="text-secondary">Revenue</h3>
                        <div
                            class="text-xs px-2 py-0.5 rounded-full"
                            :class="{
                                'bg-green-500/20 text-green-300':
                                    metrics.revenue.isPositive,
                                'bg-red-500/20 text-red-300':
                                    !metrics.revenue.isPositive,
                            }"
                        >
                            {{ metrics.revenue.growthPercent > 0 ? "+" : ""
                            }}{{ metrics.revenue.growthPercent }}%
                        </div>
                    </div>
                    <div class="mb-1 text-2xl font-bold text-white">
                        {{ formatCurrency(metrics.revenue.total) }}
                    </div>
                    <div class="text-sm text-white/60">
                        {{ metrics.revenue.currency }}
                    </div>
                </div>

                <!-- Orders Metric -->
                <div class="p-5 rounded-lg shadow-md bg-primary/20">
                    <div class="flex justify-between mb-2">
                        <h3 class="text-primary/80">Orders</h3>
                        <div
                            class="text-xs px-2 py-0.5 rounded-full"
                            :class="{
                                'bg-green-500/20 text-green-300':
                                    metrics.orders.isPositive,
                                'bg-red-500/20 text-red-300':
                                    !metrics.orders.isPositive,
                            }"
                        >
                            {{ metrics.orders.growthPercent > 0 ? "+" : ""
                            }}{{ metrics.orders.growthPercent }}%
                        </div>
                    </div>
                    <div class="mb-1 text-2xl font-bold text-white">
                        {{ metrics.orders.total }}
                    </div>
                    <div class="text-sm text-white/60">Total transactions</div>
                </div>

                <!-- Products Metric -->
                <div class="p-5 rounded-lg shadow-md bg-secondary/10">
                    <div class="flex justify-between mb-2">
                        <h3 class="text-secondary">Products</h3>
                        <div
                            class="text-xs px-2 py-0.5 rounded-full"
                            :class="{
                                'bg-green-500/20 text-green-300':
                                    metrics.products.isPositive,
                                'bg-red-500/20 text-red-300':
                                    !metrics.products.isPositive,
                            }"
                        >
                            {{ metrics.products.growthPercent > 0 ? "+" : ""
                            }}{{ metrics.products.growthPercent }}%
                        </div>
                    </div>
                    <div class="mb-1 text-2xl font-bold text-white">
                        {{ metrics.products.total }}
                    </div>
                    <div class="text-sm text-white/60">Active products</div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
                <!-- Revenue Chart -->
                <div
                    class="p-5 rounded-lg shadow-md lg:col-span-3 bg-secondary/10"
                >
                    <h3 class="mb-4 text-xl font-semibold text-white">
                        Revenue Trend
                    </h3>
                    <div class="h-80">
                        <canvas ref="revenueChartCanvas"></canvas>
                    </div>
                </div>

                <!-- Status Distribution -->
                <div
                    class="p-5 rounded-lg shadow-md lg:col-span-2 bg-primary/20"
                >
                    <h3 class="mb-4 text-xl font-semibold text-white">
                        Order Status
                    </h3>
                    <div class="h-80">
                        <canvas ref="statusChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Product Selector and Service Analytics -->
            <div class="p-5 rounded-lg shadow-md bg-secondary/10">
                <div class="flex flex-wrap items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-white">
                        Product-Specific Analytics
                    </h3>

                    <div class="relative flex items-center space-x-2">
                        <select
                            v-model="selectedProductId"
                            @change="loadProductServices"
                            class="px-4 py-2 text-white border rounded-lg appearance-none bg-primary/20 border-primary/30"
                        >
                            <option value="">All Products</option>
                            <option
                                v-for="product in products"
                                :key="product.id"
                                :value="product.id"
                            >
                                {{ product.name }}
                            </option>
                        </select>

                        <!-- Cosmic selector icon -->
                        <div class="absolute pointer-events-none right-3">
                            <div
                                class="w-4 h-4 rounded-full bg-primary/50 animate-pulse"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Service Data Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-white/90">
                        <thead>
                            <tr class="text-left">
                                <th class="px-4 py-2 border-b border-white/10">
                                    Service Name
                                </th>
                                <th
                                    class="px-4 py-2 text-right border-b border-white/10"
                                >
                                    Orders
                                </th>
                                <th
                                    class="px-4 py-2 text-right border-b border-white/10"
                                >
                                    Revenue
                                </th>
                                <th
                                    class="px-4 py-2 text-right border-b border-white/10"
                                >
                                    Profit
                                </th>
                                <!-- <th
                                    class="px-4 py-2 text-right border-b border-white/10"
                                >
                                    Growth
                                </th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="loading">
                                <tr v-for="i in 5" :key="i">
                                    <td class="px-4 py-3">
                                        <div
                                            class="h-4 rounded bg-white/10 animate-pulse"
                                        ></div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div
                                            class="h-4 rounded bg-white/10 animate-pulse"
                                        ></div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div
                                            class="h-4 rounded bg-white/10 animate-pulse"
                                        ></div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div
                                            class="h-4 rounded bg-white/10 animate-pulse"
                                        ></div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div
                                            class="h-4 rounded bg-white/10 animate-pulse"
                                        ></div>
                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr
                                    v-for="service in topServices"
                                    :key="service.id"
                                    class="hover:bg-white/5"
                                >
                                    <td class="px-4 py-3">
                                        {{ service.name }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        {{ service.sales }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(service.revenue) }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(service.profit) }}
                                    </td>
                                    <!-- <td class="px-4 py-3 text-right">
                                        <div
                                            class="flex items-center justify-end"
                                        >
                                            <span
                                                :class="
                                                    service.growth > 0
                                                        ? 'text-green-400'
                                                        : 'text-red-400'
                                                "
                                            >
                                                {{
                                                    service.growth > 0
                                                        ? "+"
                                                        : ""
                                                }}{{ service.growth }}%
                                            </span>
                                            <svg
                                                v-if="service.growth > 0"
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                class="ml-1 text-green-400"
                                            >
                                                <path d="M7 17l5-5 5 5"></path>
                                                <path d="M7 7h10"></path>
                                            </svg>
                                            <svg
                                                v-else
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                class="ml-1 text-red-400"
                                            >
                                                <path d="M7 7l5 5 5-5"></path>
                                                <path d="M7 17h10"></path>
                                            </svg>
                                        </div>
                                    </td> -->
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Flashsale Event Monitoring -->
            <!-- <div class="p-5 rounded-lg shadow-md bg-primary/20">
                <h3 class="mb-4 text-xl font-semibold text-white">
                    Flashsale Events
                </h3>

                <div
                    v-if="flashsales.length === 0"
                    class="py-6 text-center text-white/60"
                >
                    No active flashsale events
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="event in flashsales"
                        :key="event.id"
                        class="p-4 rounded-lg bg-secondary/10"
                    >
                        <div
                            class="flex flex-wrap items-center justify-between mb-3"
                        >
                            <div>
                                <h4 class="text-lg font-medium text-secondary">
                                    {{ event.event_name }}
                                </h4>
                                <p class="text-sm text-white/60">
                                    {{ formatDate(event.event_start_date) }} -
                                    {{ formatDate(event.event_end_date) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="font-medium text-white">
                                    {{ event.item.length }} Items
                                </div>
                                <div class="text-sm text-white/60">
                                    {{ formatCurrency(event.total_revenue) }}
                                    Revenue
                                </div>
                            </div>
                        </div>

                        
                        <div
                            class="relative h-2 mb-3 overflow-hidden rounded-full bg-secondary/10"
                        >
                            <div
                                class="absolute top-0 left-0 h-full bg-gradient-to-r from-primary to-secondary"
                                :style="{
                                    width: `${calculateFlashsaleProgress(
                                        event
                                    )}%`,
                                }"
                            ></div>
                        </div>

                      
                        <div
                            v-if="event.item && event.item.length > 0"
                            class="mt-3"
                        >
                            <h5 class="mb-2 text-sm font-medium text-white/70">
                                Top Performing Items:
                            </h5>
                            <div class="flex flex-wrap gap-2">
                                <div
                                    v-for="item in event.top_items.slice(0, 3)"
                                    :key="item.id"
                                    class="px-3 py-1 text-xs rounded-full bg-white/10 text-white/90"
                                >
                                    {{ item.service_name }} ({{ item.sold }})
                                </div>
                                <div
                                    v-if="event.top_items.length > 3"
                                    class="px-3 py-1 text-xs rounded-full bg-white/10 text-white/90"
                                >
                                    +{{ event.top_items.length - 3 }} more
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->

            <!-- Voucher Utilization Dashboard -->
            <!-- <div class="p-5 rounded-lg shadow-md bg-secondary/10">
                <h3 class="mb-4 text-xl font-semibold text-white">
                    Voucher Utilization
                </h3>

                <div
                    v-if="vouchers.length === 0"
                    class="py-6 text-center text-white/60"
                >
                    No active vouchers
                </div>

                <div
                    v-else
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="voucher in vouchers"
                        :key="voucher.id"
                        class="p-4 rounded-lg bg-primary/20"
                    >
                        <div class="flex justify-between mb-2">
                            <h4 class="font-medium text-white">
                                {{ voucher.kode_voucher }}
                            </h4>
                            <span
                                class="text-sm"
                                :class="{
                                    'text-green-300':
                                        voucher.utilization_pct < 50,
                                    'text-yellow-300':
                                        voucher.utilization_pct >= 50 &&
                                        voucher.utilization_pct < 80,
                                    'text-red-300':
                                        voucher.utilization_pct >= 80,
                                }"
                            >
                                {{ Math.round(voucher.utilization_pct) }}% Used
                            </span>
                        </div>

                        <div class="mb-3 text-sm text-white/60">
                            {{ voucher.usage_count }}/{{ voucher.usage_limit }}
                            Redeemed
                        </div>

                        <div
                            class="relative h-3 overflow-hidden rounded-full bg-white/10"
                        >
                            <div
                                class="absolute top-0 left-0 h-full"
                                :class="{
                                    'bg-gradient-to-r from-green-500 to-green-300':
                                        voucher.utilization_pct < 50,
                                    'bg-gradient-to-r from-yellow-500 to-yellow-300':
                                        voucher.utilization_pct >= 50 &&
                                        voucher.utilization_pct < 80,
                                    'bg-gradient-to-r from-red-500 to-red-300':
                                        voucher.utilization_pct >= 80,
                                }"
                                :style="{
                                    width: `${voucher.utilization_pct}%`,
                                }"
                            ></div>

                            <div
                                class="absolute top-0 left-0 w-full h-full opacity-50"
                            >
                                <div
                                    v-for="i in 5"
                                    :key="i"
                                    class="absolute w-1 h-1 rounded-full bg-white/80 animate-pulse"
                                    :style="{
                                        left: `${Math.random() * 100}%`,
                                        top: `${Math.random() * 100}%`,
                                        animationDelay: `${i * 0.1}s`,
                                    }"
                                ></div>
                            </div>
                        </div>

                        <div
                            class="flex justify-between mt-3 text-xs text-white/60"
                        >
                            <span
                                >Valid until:
                                {{ formatDate(voucher.expired_at) }}</span
                            >
                            <span>{{ voucher.nilai }}% OFF</span>
                        </div>
                    </div>
                </div>
            </div> -->

            <!-- Table Section -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Recent Transactions -->
                <div class="p-5 rounded-lg shadow-md bg-secondary/10">
                    <div class="flex justify-between mb-4">
                        <h3 class="text-xl font-semibold text-white">
                            Recent Transactions
                        </h3>
                        <button
                            @click="exportTransactions"
                            class="flex items-center gap-1 px-3 py-1 text-white transition-colors rounded-lg bg-primary/20 hover:bg-primary/30"
                        >
                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M3 15v4c0 1.1.9 2 2 2h14a2 2 0 0 0 2-2v-4M17 9l-5 5-5-5M12 12.8V3"
                                ></path>
                            </svg>
                            <span>Export</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-white/90">
                            <thead>
                                <tr class="text-left">
                                    <th
                                        class="px-4 py-2 border-b border-white/10"
                                    >
                                        ID
                                    </th>
                                    <th
                                        class="px-4 py-2 border-b border-white/10"
                                    >
                                        User
                                    </th>
                                    <th
                                        class="px-4 py-2 border-b border-white/10"
                                    >
                                        Game
                                    </th>
                                    <th
                                        class="px-4 py-2 text-right border-b border-white/10"
                                    >
                                        Amount
                                    </th>
                                    <th
                                        class="px-4 py-2 text-right border-b border-white/10"
                                    >
                                        Profit
                                    </th>
                                    <th
                                        class="px-4 py-2 text-center border-b border-white/10"
                                    >
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="tx in tables.recent_transactions"
                                    :key="tx.id"
                                    class="hover:bg-white/5"
                                >
                                    <td class="px-4 py-2">{{ tx.id }}</td>
                                    <td class="px-4 py-2">{{ tx.user }}</td>
                                    <td class="px-4 py-2">{{ tx.game }}</td>
                                    <td class="px-4 py-2 text-right">
                                        {{ formatCurrency(tx.amount) }}
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        {{ formatCurrency(tx.profit || 0) }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="flex justify-center">
                                            <span
                                                class="px-2 py-1 text-xs rounded-full"
                                                :class="{
                                                    'bg-green-500/20 text-green-300':
                                                        tx.status ===
                                                        'completed',
                                                    'bg-yellow-500/20 text-yellow-300':
                                                        tx.status === 'pending',
                                                    'bg-red-500/20 text-red-300':
                                                        tx.status === 'failed',
                                                    'bg-blue-500/20 text-blue-300':
                                                        tx.status ===
                                                        'processing',
                                                    'bg-gray-500/20 text-gray-300':
                                                        tx.status ===
                                                        'cancelled',
                                                }"
                                            >
                                                {{ tx.status }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="p-5 rounded-lg shadow-md bg-primary/20">
                    <div class="flex justify-between mb-4">
                        <h3 class="text-xl font-semibold text-white">
                            Top Products
                        </h3>
                        <button
                            @click="exportTopProducts"
                            class="flex items-center gap-1 px-3 py-1 text-white transition-colors rounded-lg bg-secondary/20 hover:bg-secondary/30"
                        >
                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M3 15v4c0 1.1.9 2 2 2h14a2 2 0 0 0 2-2v-4M17 9l-5 5-5-5M12 12.8V3"
                                ></path>
                            </svg>
                            <span>Export</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-white/90">
                            <thead>
                                <tr class="text-left">
                                    <th
                                        class="px-4 py-2 border-b border-white/10"
                                    >
                                        Product
                                    </th>
                                    <th
                                        class="px-4 py-2 text-right border-b border-white/10"
                                    >
                                        Sales
                                    </th>
                                    <th
                                        class="px-4 py-2 text-right border-b border-white/10"
                                    >
                                        Revenue
                                    </th>
                                    <th
                                        class="px-4 py-2 text-right border-b border-white/10"
                                    >
                                        Profit
                                    </th>
                                    <!-- <th
                                        class="px-4 py-2 text-right border-b border-white/10"
                                    >
                                        Growth
                                    </th> -->
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="product in tables.top_products"
                                    :key="product.id"
                                    class="hover:bg-white/5"
                                >
                                    <td class="px-4 py-2">
                                        {{ product.name }}
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        {{ product.sales }}
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        {{ formatCurrency(product.revenue) }}
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        {{
                                            formatCurrency(product.profit || 0)
                                        }}
                                    </td>
                                    <!-- <td class="px-4 py-2 text-right">
                                        <div
                                            class="flex items-center justify-end"
                                        >
                                            <span
                                                :class="
                                                    product.growth >= 0
                                                        ? 'text-green-400'
                                                        : 'text-red-400'
                                                "
                                            >
                                                {{
                                                    product.growth >= 0
                                                        ? "+"
                                                        : ""
                                                }}{{ product.growth }}%
                                            </span>
                                            <svg
                                                v-if="product.growth >= 0"
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                class="ml-1 text-green-400"
                                            >
                                                <path d="M7 17l5-5 5 5"></path>
                                                <path d="M7 7h10"></path>
                                            </svg>
                                            <svg
                                                v-else
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                class="ml-1 text-red-400"
                                            >
                                                <path d="M7 7l5 5 5-5"></path>
                                                <path d="M7 17h10"></path>
                                            </svg>
                                        </div>
                                    </td> -->
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

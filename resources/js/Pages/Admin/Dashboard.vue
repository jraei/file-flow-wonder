<script setup>
import { ref, computed } from "vue";
import { Head } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PeriodSelector from "@/Components/Admin/Dashboard/PeriodSelector.vue";
import RevenueChart from "@/Components/Admin/Dashboard/RevenueChart.vue";
import OrderDistributionChart from "@/Components/Admin/Dashboard/OrderDistributionChart.vue";
import ExportButton from "@/Components/Admin/Dashboard/ExportButton.vue";
import { AdminDashboardService } from "@/Services/AdminDashboardService";

// Accept props from controller
const props = defineProps({
    dashboardData: Object,
    activePeriod: {
        type: String,
        default: 'weekly'
    }
});

// Extract data from props
const stats = computed(() => props.dashboardData?.metrics || {
    users: {
        total: 0,
        growthPercent: 0,
        isPositive: true,
    },
    revenue: {
        total: 0,
        currency: "USD",
        growthPercent: 0,
        isPositive: true,
    },
    orders: {
        total: 0,
        growthPercent: 0,
        isPositive: true,
    },
    products: {
        total: 0,
        growthPercent: 0,
        isPositive: true,
    },
});

const recentTransactions = computed(() => props.dashboardData?.tables?.recent_transactions || []);

const topProducts = computed(() => props.dashboardData?.tables?.top_products || []);

const revenueTrend = computed(() => props.dashboardData?.charts?.revenue_trend || []);

const orderDistribution = computed(() => props.dashboardData?.charts?.order_distribution || []);

// Helper functions
const getStatusClass = (status) => {
    switch (status) {
        case "completed":
        case "success":
            return "bg-green-500/20 text-green-400";
        case "pending":
        case "waiting":
            return "bg-yellow-500/20 text-yellow-400";
        case "failed":
        case "error":
            return "bg-red-500/20 text-red-400";
        default:
            return "bg-gray-500/20 text-gray-400";
    }
};

// Format currency
const formatCurrency = (value) => {
    return AdminDashboardService.formatCurrency(value);
};

// Format numbers
const formatNumber = (value) => {
    return AdminDashboardService.formatNumber(value);
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout title="Dashboard">
        <div class="p-6">
            <!-- Period Selector -->
            <PeriodSelector :activePeriod="activePeriod" />
            
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
                                {{ formatNumber(stats.users.total) }}
                            </h2>
                            <div
                                :class="[
                                    'flex items-center mt-2 text-sm',
                                    stats.users.isPositive
                                        ? 'text-green-400'
                                        : 'text-red-400',
                                ]"
                            >
                                <svg
                                    v-if="stats.users.isPositive"
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 mr-1"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <svg
                                    v-else
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 mr-1"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <span
                                    >{{ Math.abs(stats.users.growthPercent) }}%
                                    {{
                                        stats.users.isPositive
                                            ? "increase"
                                            : "decrease"
                                    }}</span
                                >
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
                    <!-- Mini Sparkline graph would go here in a real implementation -->
                    <div
                        class="w-full h-2 mt-4 overflow-hidden bg-gray-700 rounded-full"
                    >
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-primary to-secondary"
                            :style="{
                                width: `${Math.max(2, Math.abs(stats.users.growthPercent * 5))}%`,
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
                                {{ formatCurrency(stats.revenue.total) }}
                            </h2>
                            <div
                                :class="[
                                    'flex items-center mt-2 text-sm',
                                    stats.revenue.isPositive
                                        ? 'text-green-400'
                                        : 'text-red-400',
                                ]"
                            >
                                <svg
                                    v-if="stats.revenue.isPositive"
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 mr-1"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <svg
                                    v-else
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 mr-1"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <span
                                    >{{
                                        Math.abs(stats.revenue.growthPercent)
                                    }}%
                                    {{
                                        stats.revenue.isPositive
                                            ? "increase"
                                            : "decrease"
                                    }}</span
                                >
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
                    <div
                        class="w-full h-2 mt-4 overflow-hidden bg-gray-700 rounded-full"
                    >
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-secondary to-primary"
                            :style="{
                                width: `${Math.max(2, Math.abs(stats.revenue.growthPercent * 5))}%`,
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
                                {{ formatNumber(stats.orders.total) }}
                            </h2>
                            <div
                                :class="[
                                    'flex items-center mt-2 text-sm',
                                    stats.orders.isPositive
                                        ? 'text-green-400'
                                        : 'text-red-400',
                                ]"
                            >
                                <svg
                                    v-if="stats.orders.isPositive"
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 mr-1"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <svg
                                    v-else
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 mr-1"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <span
                                    >{{ Math.abs(stats.orders.growthPercent) }}%
                                    {{
                                        stats.orders.isPositive
                                            ? "increase"
                                            : "decrease"
                                    }}</span
                                >
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
                    <div
                        class="w-full h-2 mt-4 overflow-hidden bg-gray-700 rounded-full"
                    >
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-primary to-secondary"
                            :style="{
                                width: `${Math.max(2, Math.abs(stats.orders.growthPercent * 5))}%`,
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
                                {{ formatNumber(stats.products.total) }}
                            </h2>
                            <div
                                :class="[
                                    'flex items-center mt-2 text-sm',
                                    stats.products.isPositive
                                        ? 'text-green-400'
                                        : 'text-red-400',
                                ]"
                            >
                                <svg
                                    v-if="stats.products.isPositive"
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 mr-1"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <svg
                                    v-else
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 mr-1"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <span
                                    >{{
                                        Math.abs(stats.products.growthPercent)
                                    }}%
                                    {{
                                        stats.products.isPositive
                                            ? "increase"
                                            : "decrease"
                                    }}</span
                                >
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
                    <div
                        class="w-full h-2 mt-4 overflow-hidden bg-gray-700 rounded-full"
                    >
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-primary to-secondary"
                            :style="{
                                width: `${Math.max(2, Math.abs(stats.products.growthPercent * 5))}%`,
                            }"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-2">
                <!-- Revenue Chart -->
                <div
                    class="p-6 border border-gray-700 rounded-lg shadow-lg bg-dark-card"
                >
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-white">
                            Revenue Trend
                        </h3>
                        <ExportButton 
                            type="revenue" 
                            :data="revenueTrend" 
                            label="Export CSV"
                        />
                    </div>
                    <div class="w-full h-80">
                        <RevenueChart :chartData="revenueTrend" />
                    </div>
                </div>

                <!-- Order Distribution Chart -->
                <div
                    class="p-6 border border-gray-700 rounded-lg shadow-lg bg-dark-card"
                >
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-white">
                            Order Distribution
                        </h3>
                        <ExportButton 
                            type="distribution" 
                            :data="orderDistribution" 
                            label="Export CSV"
                        />
                    </div>
                    <div class="w-full h-80">
                        <OrderDistributionChart :chartData="orderDistribution" />
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
                        <div class="flex gap-2">
                            <ExportButton 
                                type="transactions" 
                                :data="recentTransactions" 
                                label="Export CSV"
                            />
                            <button
                                class="transition-colors text-secondary hover:text-secondary-hover"
                                @click="$inertia.visit(route('pembelians.index'))"
                            >
                                View All
                            </button>
                        </div>
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
                                        Game
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
                                    v-for="transaction in recentTransactions"
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
                                <tr v-if="recentTransactions.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-sm text-center text-gray-400">
                                        No transactions found
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
                            Top Products
                        </h3>
                        <div class="flex gap-2">
                            <ExportButton 
                                type="products" 
                                :data="topProducts" 
                                label="Export CSV"
                            />
                            <button
                                class="transition-colors text-secondary hover:text-secondary-hover"
                                @click="$inertia.visit(route('products.index'))"
                            >
                                View All
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-full">
                            <thead>
                                <tr class="bg-dark-lighter">
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase"
                                    >
                                        Product
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
                                    v-for="product in topProducts"
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
                                        {{ formatNumber(product.sales) }}
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
                                                <svg
                                                    v-if="product.growth >= 0"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="w-4 h-4 mr-1"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                                <svg
                                                    v-else
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="w-4 h-4 mr-1"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-

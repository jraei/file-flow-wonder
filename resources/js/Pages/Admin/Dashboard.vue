<script setup>
import { ref } from "vue";
import { Head } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

// Mock data for charts and statistics
// In a real application, this would come from the backend
const stats = ref({
    users: {
        total: 1250,
        growthPercent: 12.5,
        isPositive: true,
    },
    revenue: {
        total: 18500,
        currency: "USD",
        growthPercent: 8.2,
        isPositive: true,
    },
    orders: {
        total: 3784,
        growthPercent: -2.4,
        isPositive: false,
    },
    products: {
        total: 152,
        growthPercent: 5.1,
        isPositive: true,
    },
});

const recentTransactions = ref([
    {
        id: "TX-1234",
        user: "John Doe",
        amount: 25.99,
        status: "completed",
        date: "2023-09-15",
        game: "Mobile Legends",
    },
    {
        id: "TX-1235",
        user: "Jane Smith",
        amount: 49.99,
        status: "pending",
        date: "2023-09-15",
        game: "Free Fire",
    },
    {
        id: "TX-1236",
        user: "Robert Brown",
        amount: 15.0,
        status: "completed",
        date: "2023-09-14",
        game: "PUBG Mobile",
    },
    {
        id: "TX-1237",
        user: "Alice Johnson",
        amount: 100.0,
        status: "failed",
        date: "2023-09-14",
        game: "Genshin Impact",
    },
    {
        id: "TX-1238",
        user: "Chris Wilson",
        amount: 30.5,
        status: "completed",
        date: "2023-09-13",
        game: "Mobile Legends",
    },
]);

const topProducts = ref([
    {
        id: 1,
        name: "Mobile Legends",
        sales: 1280,
        revenue: 15360,
        growth: 12.4,
    },
    { id: 2, name: "Free Fire", sales: 980, revenue: 11760, growth: 8.1 },
    { id: 3, name: "PUBG Mobile", sales: 820, revenue: 9840, growth: 5.7 },
    { id: 4, name: "Genshin Impact", sales: 650, revenue: 7800, growth: 10.2 },
    { id: 5, name: "Valorant", sales: 520, revenue: 6240, growth: -2.3 },
]);

// Helper function for status styling
const getStatusClass = (status) => {
    switch (status) {
        case "completed":
            return "bg-green-500/20 text-green-400";
        case "pending":
            return "bg-yellow-500/20 text-yellow-400";
        case "failed":
            return "bg-red-500/20 text-red-400";
        default:
            return "bg-gray-500/20 text-gray-400";
    }
};

// Format currency
const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(value);
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout title="Dashboard">
        <div class="p-6">
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
                                {{ stats.users.total.toLocaleString() }}
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
                                width: `${stats.users.growthPercent * 5}%`,
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
                    <!-- Mini Sparkline graph would go here in a real implementation -->
                    <div
                        class="w-full h-2 mt-4 overflow-hidden bg-gray-700 rounded-full"
                    >
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-secondary to-primary"
                            :style="{
                                width: `${stats.revenue.growthPercent * 5}%`,
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
                                {{ stats.orders.total.toLocaleString() }}
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
                    <!-- Mini Sparkline graph would go here in a real implementation -->
                    <div
                        class="w-full h-2 mt-4 overflow-hidden bg-gray-700 rounded-full"
                    >
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-primary to-secondary"
                            :style="{
                                width: `${Math.max(
                                    2,
                                    Math.abs(stats.orders.growthPercent) * 5
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
                                {{ stats.products.total }}
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
                    <!-- Mini Sparkline graph would go here in a real implementation -->
                    <div
                        class="w-full h-2 mt-4 overflow-hidden bg-gray-700 rounded-full"
                    >
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-primary to-secondary"
                            :style="{
                                width: `${stats.products.growthPercent * 5}%`,
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
                    <h3 class="mb-6 text-xl font-semibold text-white">
                        Revenue Trend
                    </h3>
                    <div class="w-full h-80">
                        <!-- This would be replaced with an actual chart component in a real implementation -->
                        <div
                            class="flex items-center justify-center w-full h-full p-4 border border-gray-700 rounded-lg bg-dark-lighter"
                        >
                            <div class="text-center">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-16 h-16 mx-auto mb-4 text-gray-500"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    />
                                </svg>
                                <p class="text-gray-400">
                                    Revenue Chart Visualization
                                </p>
                                <p class="mt-2 text-sm text-gray-500">
                                    This would be a line chart showing revenue
                                    trends over time
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders Chart -->
                <div
                    class="p-6 border border-gray-700 rounded-lg shadow-lg bg-dark-card"
                >
                    <h3 class="mb-6 text-xl font-semibold text-white">
                        Order Statistics
                    </h3>
                    <div class="w-full h-80">
                        <!-- This would be replaced with an actual chart component in a real implementation -->
                        <div
                            class="flex items-center justify-center w-full h-full p-4 border border-gray-700 rounded-lg bg-dark-lighter"
                        >
                            <div class="text-center">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-16 h-16 mx-auto mb-4 text-gray-500"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"
                                    />
                                </svg>
                                <p class="text-gray-400">
                                    Order Statistics Visualization
                                </p>
                                <p class="mt-2 text-sm text-gray-500">
                                    This would be a pie/doughnut chart showing
                                    order distribution
                                </p>
                            </div>
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
                        <button
                            class="transition-colors text-secondary hover:text-secondary-hover"
                        >
                            View All
                        </button>
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
                        <button
                            class="transition-colors text-secondary hover:text-secondary-hover"
                        >
                            View All
                        </button>
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
                                                        d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
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

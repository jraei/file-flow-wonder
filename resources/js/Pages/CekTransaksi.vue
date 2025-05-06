<template>
    <GuestLayout>
        <div class="py-8 md:py-12">
            <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="mb-10 text-center">
                    <h1 class="mb-2 text-3xl font-bold text-white md:text-4xl">
                        Transaction Tracker
                    </h1>
                    <p class="text-gray-300">
                        Verify your cosmic purchase journey
                    </p>
                </div>

                <!-- Search Card -->
                <CosmicSearchCard
                    :search-query="searchQuery"
                    :error="error"
                    :search-result="searchResult"
                />

                <!-- Transaction Detail -->
                <!-- <div v-if="searchResult">
                    <TransactionDetail
                        :transaction="searchResult"
                        :server-time="serverTime"
                    />
                </div> -->

                <!-- Live Transactions -->
                <LiveTransactionsTable
                    :transactions="transactions"
                    :server-time="serverTime"
                />
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { Head } from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import CosmicSearchCard from "@/Components/Transaction/CosmicSearchCard.vue";
import LiveTransactionsTable from "@/Components/Transaction/LiveTransactionsTable.vue";
import TransactionDetail from "@/Components/Transaction/TransactionDetail.vue";

defineProps({
    transactions: {
        type: Object,
        required: true,
    },
    searchResult: {
        type: Object,
        default: null,
    },
    searchQuery: {
        type: String,
        default: "",
    },
    serverTime: {
        type: String,
        required: true,
    },
    error: {
        type: String,
        default: null,
    },
});

onMounted(() => {
    // Add global CSS for animations
    const style = document.createElement("style");
    style.innerHTML = `
        @keyframes warpTunnel {
            0% { filter: brightness(1); }
            50% { filter: brightness(1.3) blur(2px); }
            100% { filter: brightness(1) blur(0); }
        }
        .warping {
            animation: warpTunnel 0.5s forwards;
        }
    `;
    document.head.appendChild(style);
});
</script>

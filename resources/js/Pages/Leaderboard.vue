<template>
    <GuestLayout>
        <div class="relative">
            <!-- Cosmic Navbar -->
            <!-- <CosmicNavbar /> -->

            <main
                class="min-h-screen pt-16 pb-32 overflow-hidden bg-gradient-to-b from-content_background to-transparent backdrop-blur-sm"
            >
                <!-- Cosmic Leaderboard Container -->
                <div class="container relative px-4 py-12 mx-auto">
                    <!-- Cosmic Decorations -->
                    <CosmicStars />

                    <h1
                        class="mb-8 text-3xl font-bold text-center text-white md:text-4xl"
                    >
                        <span
                            class="text-transparent bg-gradient-to-r from-primary to-secondary bg-clip-text"
                        >
                            Cosmic Leaderboard
                        </span>
                    </h1>

                    <!-- Tabs Container -->
                    <CosmicTabs
                        :time-periods="['Today', 'This Week', 'This Month']"
                        :active-tab="activeTab"
                        @tab-change="handleTabChange"
                    />

                    <!-- Leaderboard Rankings -->
                    <CosmicLeaderboardRankings
                        :rankings="currentRankings"
                        :loading="loading"
                    />

                    <!-- Footer Navigation -->
                    <div class="mt-12 text-center">
                        <Link
                            :href="route('index')"
                            class="inline-flex items-center px-6 py-3 space-x-2 text-lg text-white transition-all duration-300 border rounded-full bg-primary/40 hover:bg-primary/60 backdrop-blur-sm border-primary/30"
                        >
                            <span>Return to Home</span>
                        </Link>
                    </div>
                </div>
            </main>

            <!-- Cosmic Footer -->
            <!-- <CosmicFooter /> -->
        </div>
    </GuestLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Link } from "@inertiajs/vue3";
import CosmicNavbar from "@/Components/User/Navigation/CosmicNavbar.vue";
import CosmicFooter from "@/Components/User/Footer/CosmicFooter.vue";
import CosmicTabs from "@/Components/Leaderboard/CosmicTabs.vue";
import CosmicLeaderboardRankings from "@/Components/Leaderboard/CosmicLeaderboardRankings.vue";
import CosmicStars from "@/Components/Leaderboard/CosmicStars.vue";
import { useToast } from "@/Composables/useToast";
import GuestLayout from "@/Layouts/GuestLayout.vue";

// Define props
const props = defineProps({
    daily: Array,
    weekly: Array,
    monthly: Array,
    serverTime: String,
});

const { toast } = useToast();
const loading = ref(false);
const activeTab = ref("Today");

// Compute current rankings based on active tab
const currentRankings = computed(() => {
    switch (activeTab.value) {
        case "Today":
            return props.daily || [];
        case "This Week":
            return props.weekly || [];
        case "This Month":
            return props.monthly || [];
        default:
            return [];
    }
});

// Handle tab change
const handleTabChange = (tab) => {
    loading.value = true;
    setTimeout(() => {
        activeTab.value = tab;
        loading.value = false;
    }, 300);
};
</script>

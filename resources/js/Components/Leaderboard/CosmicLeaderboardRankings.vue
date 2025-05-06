<template>
    <div class="relative max-w-4xl mx-auto">
        <!-- Loading overlay -->
        <div
            v-if="loading"
            class="absolute inset-0 z-10 flex items-center justify-center bg-dark-DEFAULT/80 backdrop-blur-sm rounded-xl"
        >
            <div class="cosmic-loader">
                <div class="planet"></div>
                <div class="ring"></div>
                <p class="mt-8 text-primary/80">Loading cosmic data...</p>
            </div>
        </div>

        <!-- No data state -->
        <div v-if="!loading && rankings.length === 0" class="py-16 text-center">
            <CircleOff
                class="w-16 h-16 mx-auto mb-4 text-secondary opacity-30"
            />
            <p class="text-xl text-gray-400">
                No cosmic purchases detected yet
            </p>
            <p class="text-gray-500">
                Be the first to appear on the leaderboard!
            </p>
        </div>

        <template v-else>
            <!-- Top 3 special cards -->
            <div class="grid grid-cols-1 gap-4 mb-8 md:grid-cols-3">
                <CosmicRankCard
                    v-for="(item, index) in topThree"
                    :key="`top-${index}`"
                    :rank="index + 1"
                    :username="item.username"
                    :total="item.total"
                    :formatted-total="item.formatted_total"
                    :is-top-three="true"
                    :class="{
                        'order-2 md:order-1': index === 1,
                        'order-1 md:order-2 transform md:-translate-y-4':
                            index === 0,
                        'order-3': index === 2,
                    }"
                />
            </div>

            <!-- Regular ranking cards -->
            <div
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                v-if="regularRankings.length > 0"
            >
                <CosmicRankCard
                    v-for="(item, index) in regularRankings"
                    :key="`regular-${index}`"
                    :rank="index + 4"
                    :username="item.username"
                    :total="item.total"
                    :formatted-total="item.formatted_total"
                />
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { CircleOff } from "lucide-vue-next";
import CosmicRankCard from "./CosmicRankCard.vue";

const props = defineProps({
    rankings: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

// Split rankings into top three and regular rankings
const topThree = computed(() => props.rankings.slice(0, 3));
const regularRankings = computed(() => props.rankings.slice(3));
</script>

<style scoped>
.cosmic-loader {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.planet {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(145deg, #9b87f5, #33c3f0);
    box-shadow: 0 0 20px rgba(155, 135, 245, 0.5);
    animation: pulse 1.5s ease-in-out infinite alternate;
}

.ring {
    position: absolute;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 2px solid rgba(155, 135, 245, 0.3);
    border-left-color: rgba(51, 195, 240, 0.8);
    animation: spin 1.2s linear infinite;
}

@keyframes pulse {
    from {
        transform: scale(0.8);
        opacity: 0.8;
    }
    to {
        transform: scale(1.1);
        opacity: 1;
    }
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>

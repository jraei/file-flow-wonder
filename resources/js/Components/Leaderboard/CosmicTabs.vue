<template>
    <div class="relative mb-10">
        <!-- Cosmic decoration for tabs -->
        <div class="absolute inset-0 -z-10 opacity-20">
            <div
                class="absolute top-0 w-32 h-32 rounded-full left-1/4 bg-primary/20 filter blur-xl"
            ></div>
            <div
                class="absolute bottom-0 w-24 h-24 rounded-full right-1/4 bg-secondary/30 filter blur-xl"
            ></div>
        </div>

        <div class="relative flex flex-wrap justify-center gap-4">
            <button
                v-for="(tab, index) in timePeriods"
                :key="tab"
                @click="handleTabClick(tab)"
                class="relative px-5 py-3 overflow-hidden text-lg font-medium transition-all duration-300 rounded-full group"
                :class="[
                    activeTab === tab
                        ? 'text-white bg-primary/20 border border-primary/50'
                        : 'text-gray-300 hover:text-white border border-white/10 hover:border-white/30',
                ]"
            >
                <!-- Tab indicator animation -->
                <div
                    v-if="activeTab === tab"
                    class="absolute inset-0 -z-10 bg-gradient-to-r from-primary/40 to-secondary/20 opacity-60"
                ></div>

                <!-- Tab icon based on period -->
                <div class="flex items-center gap-2">
                    <div
                        class="relative flex items-center justify-center w-6 h-6"
                    >
                        <component
                            :is="getTabIcon(index)"
                            class="w-5 h-5"
                            :class="{ 'animate-pulse': activeTab === tab }"
                        />

                        <!-- Special animations for active tab -->
                        <template v-if="activeTab === tab">
                            <div
                                v-if="index === 0"
                                class="absolute w-1 h-1 rounded-full bg-secondary animate-ping"
                            ></div>
                            <div
                                v-if="index === 1"
                                class="absolute w-8 h-8 border rounded-full border-secondary/30 animate-pulse"
                            ></div>
                            <div
                                v-if="index === 2"
                                class="absolute w-10 h-10 border rounded-full border-primary/20 animate-orbit"
                            ></div>
                        </template>
                    </div>

                    <span>{{ tab }}</span>
                </div>

                <!-- Particle burst effect on hover -->
                <div class="absolute inset-0 -z-20">
                    <div
                        v-for="i in 6"
                        :key="i"
                        class="absolute w-1 h-1 transition-opacity duration-200 rounded-full opacity-0 bg-secondary/50 group-hover:opacity-100"
                        :style="{
                            top: `${Math.random() * 100}%`,
                            left: `${Math.random() * 100}%`,
                            animationDelay: `${i * 0.1}s`,
                            transform: 'scale(0)',
                        }"
                        :class="{ 'group-hover:animate-ping-small': true }"
                    ></div>
                </div>
            </button>
        </div>
    </div>
</template>

<script setup>
import { Orbit, Moon, Diameter, CircleOff } from "lucide-vue-next";

const props = defineProps({
    timePeriods: {
        type: Array,
        default: () => ["Today", "This Week", "This Month"],
    },
    activeTab: {
        type: String,
        default: "Today",
    },
});

const emit = defineEmits(["tab-change"]);

const getTabIcon = (index) => {
    switch (index) {
        case 0:
            return Orbit;
        case 1:
            return CircleOff;
        case 2:
            return Diameter;
        default:
            return CircleOff;
    }
};

const handleTabClick = (tab) => {
    if (tab !== props.activeTab) {
        emit("tab-change", tab);
    }
};
</script>

<style scoped>
@keyframes float {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}

@keyframes orbit {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>

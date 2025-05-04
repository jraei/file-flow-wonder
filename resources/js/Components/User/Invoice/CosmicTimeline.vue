<template>
    <div class="w-full mb-8">
        <div class="relative hidden sm:block">
            <!-- Desktop Timeline -->
            <div class="absolute w-full h-1 bg-gray-700 top-6"></div>
            <div
                class="absolute h-1 transition-all duration-500 bg-primary/70 top-6"
                :style="{ width: progressPercentage + '%' }"
            ></div>
            <div class="flex justify-between">
                <div
                    v-for="(stage, index) in stages"
                    :key="index"
                    class="relative flex flex-col items-center"
                >
                    <div
                        :class="[
                            'w-12 h-12 flex items-center justify-center rounded-full mb-2 transition-all duration-300',
                            currentStageIndex >= index
                                ? 'bg-primary shadow-lg shadow-primary/30'
                                : 'bg-gray-700',
                            currentStageIndex === index &&
                                'animate-pulse ring-4 ring-primary/30',
                        ]"
                    >
                        <component
                            :is="stage.icon"
                            :class="[
                                'w-6 h-6',
                                currentStageIndex >= index
                                    ? 'text-white'
                                    : 'text-gray-400',
                            ]"
                        />
                    </div>
                    <span
                        :class="[
                            'text-sm font-medium',
                            currentStageIndex >= index
                                ? 'text-primary'
                                : 'text-gray-400',
                        ]"
                        >{{ stage.title }}</span
                    >
                    <span
                        :class="[
                            'text-xs',
                            currentStageIndex >= index
                                ? 'text-gray-300'
                                : 'text-gray-500',
                        ]"
                        >{{ stage.subtitle }}</span
                    >
                </div>
            </div>
        </div>

        <!-- Mobile Timeline -->
        <div class="sm:hidden">
            <div class="flex flex-col space-y-4">
                <div
                    v-for="(stage, index) in stages"
                    :key="index"
                    :class="[
                        'flex items-center space-x-3',
                        currentStageIndex === index
                            ? 'opacity-100'
                            : 'opacity-70',
                    ]"
                >
                    <div
                        :class="[
                            'w-10 h-10 flex items-center justify-center rounded-full',
                            currentStageIndex >= index
                                ? 'bg-primary shadow-lg shadow-primary/30'
                                : 'bg-gray-700',
                            currentStageIndex === index &&
                                'animate-pulse ring-4 ring-primary/30',
                        ]"
                    >
                        <component
                            :is="stage.icon"
                            :class="[
                                'w-5 h-5',
                                currentStageIndex >= index
                                    ? 'text-white'
                                    : 'text-gray-400',
                            ]"
                        />
                    </div>
                    <div class="flex flex-col">
                        <span
                            :class="[
                                'text-sm font-medium',
                                currentStageIndex >= index
                                    ? 'text-primary'
                                    : 'text-gray-400',
                            ]"
                            >{{ stage.title }}</span
                        >
                        <span
                            :class="[
                                'text-xs',
                                currentStageIndex >= index
                                    ? 'text-gray-300'
                                    : 'text-gray-500',
                            ]"
                            >{{ stage.subtitle }}</span
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { CircleCheck, Circle, Download } from "lucide-vue-next";

const props = defineProps({
    currentStage: {
        type: String,
        required: true,
        validator: (value) => {
            return ["created", "payment", "processing", "completed"].includes(
                value
            );
        },
    },
});

const stages = [
    {
        title: "Transaksi Dibuat",
        subtitle: "Transaksi telah berhasil dibuat",
        icon: Circle,
        value: "created",
    },
    {
        title: "Pembayaran",
        subtitle: "Silakan melakukan pembayaran",
        icon: Download,
        value: "payment",
    },
    {
        title: "Sedang Di Proses",
        subtitle: "Pembelian sedang dalam proses",
        icon: Supernova,
        value: "processing",
    },
    {
        title: "Transaksi Selesai",
        subtitle: "Transaksi telah berhasil dilakukan",
        icon: CircleCheck,
        value: "completed",
    },
];

const stageValues = ["created", "payment", "processing", "completed"];

const currentStageIndex = computed(() => {
    return stageValues.indexOf(props.currentStage);
});

const progressPercentage = computed(() => {
    const totalStages = stageValues.length - 1;
    const currentIndex = currentStageIndex.value;

    if (currentIndex <= 0) return 0;
    if (currentIndex >= totalStages) return 100;

    return (currentIndex / totalStages) * 100;
});
</script>

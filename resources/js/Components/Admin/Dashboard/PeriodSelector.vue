
<template>
    <div class="mb-4 flex gap-2">
        <div class="flex p-1 bg-dark-lighter rounded-lg">
            <button 
                v-for="period in periods" 
                :key="period.value" 
                @click="selectPeriod(period.value)"
                :class="[
                    'px-4 py-1.5 rounded-md transition-all duration-300',
                    activePeriod === period.value 
                        ? 'bg-gradient-to-r from-primary to-secondary text-white shadow-lg' 
                        : 'text-gray-400 hover:text-white'
                ]"
            >
                <div class="flex items-center gap-2">
                    <component :is="period.icon" class="w-4 h-4" />
                    <span>{{ period.label }}</span>
                </div>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { CalendarDays, Calendar, CalendarRange, CalendarCheck } from 'lucide-vue-next';

const props = defineProps({
    activePeriod: {
        type: String,
        default: 'weekly'
    }
});

const periods = [
    { label: 'Daily', value: 'daily', icon: Calendar },
    { label: 'Weekly', value: 'weekly', icon: CalendarDays },
    { label: 'Monthly', value: 'monthly', icon: CalendarRange },
    { label: 'Yearly', value: 'yearly', icon: CalendarCheck }
];

const selectPeriod = (period) => {
    // Navigate to same page with different period
    router.get(
        route('admin.index'),
        { period },
        { preserveState: true, preserveScroll: true }
    );
};
</script>

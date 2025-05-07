
<template>
    <button
        @click="handleExport"
        class="flex items-center gap-2 px-3 py-1.5 text-xs rounded-lg border border-gray-600 bg-dark-lighter text-gray-300 hover:bg-gray-700 hover:text-white transition duration-300"
    >
        <Download v-if="!exporting" class="w-4 h-4" />
        <div v-else class="w-4 h-4 border-2 border-t-transparent border-white rounded-full animate-spin"></div>
        <span>{{ exporting ? 'Exporting...' : label }}</span>
    </button>
</template>

<script setup>
import { ref } from 'vue';
import { Download } from 'lucide-vue-next';
import { AdminDashboardService } from '@/Services/AdminDashboardService';

const props = defineProps({
    type: {
        type: String,
        required: true
    },
    data: {
        type: Array,
        required: true
    },
    label: {
        type: String,
        default: 'Export'
    }
});

const exporting = ref(false);

const handleExport = () => {
    exporting.value = true;
    
    setTimeout(() => {
        AdminDashboardService.exportToCSV(props.type, props.data);
        exporting.value = false;
    }, 800); // Add slight delay for better user experience
};
</script>

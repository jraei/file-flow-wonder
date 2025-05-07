
<template>
    <div class="relative w-full h-full">
        <!-- Loading state -->
        <div v-if="loading" class="absolute inset-0 flex items-center justify-center">
            <div class="cosmic-loader"></div>
        </div>
        
        <!-- Chart canvas -->
        <canvas ref="chartCanvas" class="w-full h-full"></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import Chart from 'chart.js/auto';
import { AdminDashboardService } from '@/Services/AdminDashboardService';

const props = defineProps({
    chartData: {
        type: Array,
        required: true
    }
});

const chartCanvas = ref(null);
const chart = ref(null);
const loading = ref(true);

// Initialize chart on component mount
onMounted(() => {
    if (chartCanvas.value && props.chartData) {
        initChart();
    }
});

// Watch for chart data changes
watch(() => props.chartData, (newValue) => {
    if (newValue && chartCanvas.value) {
        if (chart.value) {
            chart.value.destroy();
        }
        initChart();
    }
}, { deep: true });

const initChart = () => {
    loading.value = true;
    
    if (!props.chartData || !chartCanvas.value) return;
    
    const formattedData = AdminDashboardService.prepareOrderDistributionChartData(props.chartData);
    
    // Create chart
    chart.value = new Chart(chartCanvas.value, {
        type: 'doughnut',
        data: formattedData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#D1D5DB',
                        padding: 15,
                        font: {
                            size: 11
                        },
                        boxWidth: 15,
                        boxHeight: 15,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: '#1F2937',
                    titleColor: '#9b87f5',
                    bodyColor: '#ffffff',
                    borderColor: '#9b87f5',
                    borderWidth: 1,
                    padding: 10
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 1500,
                easing: 'easeOutQuart'
            },
            cutout: '70%',
            // Add hover scale effect
            onHover: (event, elements) => {
                if (elements && elements.length) {
                    event.chart.canvas.style.cursor = 'pointer';
                } else {
                    event.chart.canvas.style.cursor = 'default';
                }
            }
        }
    });
    
    loading.value = false;
};
</script>

<style scoped>
.cosmic-loader {
    width: 48px;
    height: 48px;
    border: 3px solid #9b87f5;
    border-bottom-color: transparent;
    border-radius: 50%;
    display: inline-block;
    box-sizing: border-box;
    animation: cosmic-loader-rotation 1s linear infinite;
}

@keyframes cosmic-loader-rotation {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>


<template>
    <div class="relative w-full h-full">
        <!-- Loading state -->
        <div v-if="loading" class="absolute inset-0 flex items-center justify-center">
            <div class="cosmic-loader"></div>
        </div>
        
        <!-- Chart canvas -->
        <canvas ref="chartCanvas" class="w-full h-full"></canvas>
        
        <!-- Cosmic decoration overlay -->
        <div class="absolute top-0 right-0 pointer-events-none">
            <div class="cosmic-star animate-pulse"></div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import Chart from 'chart.js/auto';
import { AdminDashboardService } from '@/Services/AdminDashboardService';

const props = defineProps({
    chartData: {
        type: Object,
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
    
    const formattedData = AdminDashboardService.prepareRevenueChartData(props.chartData);
    
    const ctx = chartCanvas.value.getContext('2d');
    
    // Create gradient for the area fill
    const gradientFill = ctx.createLinearGradient(0, 0, 0, chartCanvas.value.height);
    gradientFill.addColorStop(0, 'rgba(155, 135, 245, 0.3)');
    gradientFill.addColorStop(0.5, 'rgba(51, 195, 240, 0.15)');
    gradientFill.addColorStop(1, 'rgba(31, 41, 55, 0)');
    
    // Update the chart background
    formattedData.datasets[0].backgroundColor = gradientFill;
    
    // Create chart
    chart.value = new Chart(ctx, {
        type: 'line',
        data: formattedData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1F2937',
                    titleColor: '#9b87f5',
                    bodyColor: '#ffffff',
                    borderColor: '#9b87f5',
                    borderWidth: 1,
                    padding: 10,
                    displayColors: false,
                    callbacks: {
                        label: (context) => {
                            return AdminDashboardService.formatCurrency(context.parsed.y);
                        }
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeOutQuart'
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#9CA3AF',
                        maxRotation: 45,
                        minRotation: 45
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.05)'
                    },
                    ticks: {
                        color: '#9CA3AF',
                        callback: (value) => {
                            return AdminDashboardService.formatCurrency(value);
                        }
                    },
                    beginAtZero: true
                }
            },
            interaction: {
                mode: 'nearest',
                intersect: false,
                axis: 'x'
            },
            elements: {
                point: {
                    radius: 4,
                    hitRadius: 10,
                    hoverRadius: 6,
                },
                line: {
                    tension: 0.4
                }
            }
        }
    });
    
    // Add shooting stars effect
    // (Using CSS animation instead of programmatic for performance)
    
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

.cosmic-star {
    width: 12px;
    height: 12px;
    background-color: #9b87f5;
    clip-path: polygon(
        50% 0%, 61% 35%, 98% 35%, 68% 57%, 
        79% 91%, 50% 70%, 21% 91%, 32% 57%, 
        2% 35%, 39% 35%
    );
    filter: drop-shadow(0 0 8px rgba(155, 135, 245, 0.8));
}
</style>

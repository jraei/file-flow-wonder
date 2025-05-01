
<template>
    <div 
        class="voucher-card p-4 relative overflow-hidden rounded-lg border transition-all duration-300"
        :class="[
            isValid ? 'border-secondary shadow-glow-secondary cursor-pointer' : 
                     'border-gray-600 opacity-75'
        ]"
        @click="isValid && handleApply()"
    >
        <!-- Cosmic particles background -->
        <div class="absolute inset-0 overflow-hidden z-0">
            <div v-if="isValid" class="cosmic-particles">
                <span v-for="i in 10" :key="`particle-${i}`" 
                      class="absolute rounded-full bg-secondary/30"
                      :style="{
                          width: `${Math.random() * 4 + 1}px`,
                          height: `${Math.random() * 4 + 1}px`,
                          top: `${Math.random() * 100}%`,
                          left: `${Math.random() * 100}%`,
                          animationDuration: `${Math.random() * 5 + 5}s`,
                          animationDelay: `${Math.random() * 5}s`
                      }"
                ></span>
            </div>
        </div>

        <!-- Badge for limit reached -->
        <div v-if="!isValid" class="absolute -right-8 top-6 transform rotate-45 bg-red-500/80 text-white text-xs py-1 px-8 font-medium z-10">
            LIMIT REACHED
        </div>

        <!-- Main content -->
        <div class="relative z-10">
            <div class="flex justify-between items-start mb-4">
                <!-- Discount value -->
                <div class="text-xl font-bold text-white">
                    <span v-if="voucher.discount_type === 'fixed'">
                        Rp {{ voucher.discount_value.toLocaleString() }}
                    </span>
                    <span v-else>
                        Up to {{ voucher.discount_value }}%
                    </span>
                </div>
                
                <!-- Code pill -->
                <div class="bg-primary/30 text-white text-xs rounded-full px-3 py-1">
                    {{ voucher.code }}
                </div>
            </div>
            
            <!-- Voucher details -->
            <div class="space-y-1 text-sm text-gray-300 mb-4">
                <div v-if="voucher.end_date" class="flex items-center space-x-1">
                    <span class="text-secondary">⌚</span>
                    <span>Valid until {{ voucher.end_date }}</span>
                </div>
                <div v-if="voucher.max_discount" class="flex items-center space-x-1">
                    <span class="text-secondary">⌚</span>
                    <span>Max discount Rp {{ voucher.max_discount.toLocaleString() }}</span>
                </div>
                <div v-if="voucher.min_purchase" class="flex items-center space-x-1">
                    <span class="text-secondary">⌚</span>
                    <span>Min purchase Rp {{ voucher.min_purchase.toLocaleString() }}</span>
                </div>
                <div v-if="voucher.usage_limit" class="flex items-center space-x-1">
                    <span class="text-secondary">⌚</span>
                    <span>Limit: {{ voucher.usage_count }}/{{ voucher.usage_limit }} uses</span>
                </div>
            </div>

            <!-- Apply button -->
            <div class="flex justify-end">
                <button 
                    v-if="isValid" 
                    class="bg-secondary hover:bg-secondary/80 text-white px-4 py-1 rounded text-sm transition-all"
                    @click.stop="handleApply"
                >
                    APPLY
                </button>
                <button 
                    v-else
                    class="bg-gray-700 text-gray-400 px-4 py-1 rounded text-sm cursor-not-allowed"
                    disabled
                >
                    UNAVAILABLE
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    voucher: {
        type: Object,
        required: true
    },
    totalAmount: {
        type: Number,
        default: 0
    }
});

const emit = defineEmits(['apply-voucher']);

const isValid = computed(() => {
    // Check if meets minimum purchase
    if (props.voucher.min_purchase > props.totalAmount) {
        return false;
    }
    
    // Check if usage limit is reached
    if (props.voucher.usage_limit !== null && 
        props.voucher.usage_count >= props.voucher.usage_limit) {
        return false;
    }
    
    return true;
});

const handleApply = () => {
    emit('apply-voucher', props.voucher.code);
};
</script>

<style scoped>
.shadow-glow-secondary {
    box-shadow: 0 0 15px rgba(51, 195, 240, 0.3);
}

.cosmic-particles span {
    animation: float-particle infinite both;
    opacity: 0.6;
}

@keyframes float-particle {
    0%, 100% {
        transform: translateY(0) translateX(0);
        opacity: 0.3;
    }
    50% {
        transform: translateY(-10px) translateX(5px);
        opacity: 0.8;
    }
}
</style>

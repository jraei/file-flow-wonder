<script setup>
import { ref, watch } from 'vue';
import CosmicCard from './CosmicCard.vue';
import { Plus, Minus } from 'lucide-vue-next';

const props = defineProps({
    maxQuantity: {
        type: Number,
        default: 1000
    },
    initialQuantity: {
        type: Number,
        default: 1
    },
    disabled: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:quantity']);

const quantity = ref(props.initialQuantity);
const isInvalid = ref(false);

// Handle input validation
const validateQuantity = () => {
    if (quantity.value < 1) {
        quantity.value = 1;
        showError();
    } else if (quantity.value > props.maxQuantity) {
        quantity.value = props.maxQuantity;
        showError();
    }
    
    emit('update:quantity', quantity.value);
};

// Show error state with animation
const showError = () => {
    isInvalid.value = true;
    setTimeout(() => {
        isInvalid.value = false;
    }, 1000);
};

// Handle direct input changes
const handleInputChange = (e) => {
    const value = parseInt(e.target.value, 10);
    quantity.value = isNaN(value) ? 1 : value;
    validateQuantity();
};

// Increment/decrement functions
const increment = () => {
    if (props.disabled) return;
    quantity.value = Math.min(props.maxQuantity, quantity.value + 1);
    emit('update:quantity', quantity.value);
};

const decrement = () => {
    if (props.disabled) return;
    quantity.value = Math.max(1, quantity.value - 1);
    emit('update:quantity', quantity.value);
};

// Watch for initialQuantity changes
watch(() => props.initialQuantity, (newVal) => {
    if (newVal !== quantity.value) {
        quantity.value = newVal;
    }
});
</script>

<template>
    <CosmicCard title="Purchase Quantity" :stepNumber="3">
        <div class="flex items-center justify-center w-full space-x-4">
            <!-- Decrement Button -->
            <button
                @click="decrement"
                :disabled="disabled || quantity <= 1"
                class="flex items-center justify-center w-10 h-10 text-white transition-all rounded-full cosmic-button bg-primary/50 disabled:opacity-50 disabled:cursor-not-allowed"
                :class="{'opacity-50 cursor-not-allowed': disabled || quantity <= 1}"
            >
                <Minus class="w-5 h-5" />
                <span class="sr-only">Decrease</span>
            </button>
            
            <!-- Quantity Input -->
            <div 
                :class="[
                    'relative w-24 h-12 overflow-hidden rounded-lg',
                    isInvalid ? 'cosmic-input-error' : 'cosmic-input'
                ]"
            >
                <input
                    type="number"
                    min="1"
                    :max="maxQuantity"
                    v-model="quantity"
                    @change="handleInputChange"
                    @blur="validateQuantity"
                    :disabled="disabled"
                    class="w-full h-full px-4 text-center text-white bg-transparent border border-secondary/30 focus:border-secondary focus:outline-none"
                />
            </div>
            
            <!-- Increment Button -->
            <button
                @click="increment"
                :disabled="disabled || quantity >= maxQuantity"
                class="flex items-center justify-center w-10 h-10 text-white transition-all rounded-full cosmic-button bg-primary/50 disabled:opacity-50 disabled:cursor-not-allowed"
                :class="{'opacity-50 cursor-not-allowed': disabled || quantity >= maxQuantity}"
            >
                <Plus class="w-5 h-5" />
                <span class="sr-only">Increase</span>
            </button>
        </div>
        
        <!-- Max quantity message -->
        <p class="mt-3 text-xs text-center text-primary-text/70">
            Maximum quantity: {{ maxQuantity }}
        </p>
    </CosmicCard>
</template>

<style scoped>
.cosmic-button {
    position: relative;
    overflow: hidden;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(51, 195, 240, 0.2);
}

.cosmic-button:hover:not(:disabled) {
    transform: translateY(-2px);
    background-color: rgba(155, 135, 245, 0.6);
    box-shadow: 0 6px 16px rgba(51, 195, 240, 0.3);
}

.cosmic-button:active:not(:disabled) {
    transform: translateY(0);
}

.cosmic-button::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(
        circle,
        rgba(51, 195, 240, 0.1) 0%,
        transparent 70%
    );
    z-index: -1;
    transform: scale(0);
    opacity: 0;
    transition: transform 0.5s, opacity 0.5s;
}

.cosmic-button:hover::before:not(:disabled) {
    transform: scale(1);
    opacity: 1;
}

.cosmic-input {
    background: linear-gradient(
        170deg,
        rgba(155, 135, 245, 0.05) 0%,
        rgba(51, 195, 240, 0.15) 100%
    );
    transition: all 0.3s ease;
}

.cosmic-input-error {
    background: linear-gradient(
        170deg,
        rgba(239, 68, 68, 0.2) 0%,
        rgba(239, 68, 68, 0.1) 100%
    );
    animation: supernova-pulse 0.6s ease-in-out;
    box-shadow: 0 0 15px rgba(239, 68, 68, 0.4);
}

@keyframes supernova-pulse {
    0%, 100% {
        box-shadow: 0 0 5px rgba(239, 68, 68, 0.4);
    }
    50% {
        box-shadow: 0 0 20px rgba(239, 68, 68, 0.8);
    }
}

/* Hide arrows for number input */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type=number] {
    -moz-appearance: textfield;
}
</style>

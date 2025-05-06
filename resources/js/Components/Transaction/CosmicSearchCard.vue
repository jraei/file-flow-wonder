
<template>
    <div class="relative overflow-hidden rounded-xl p-6 bg-gradient-to-br from-header_background/80 to-content_background/80 shadow-lg backdrop-blur-sm">
        <!-- Particle background -->
        <div id="cosmic-search-particles" class="absolute inset-0 pointer-events-none overflow-hidden"></div>
        
        <!-- Nebula background -->
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-primary/10 via-transparent to-transparent opacity-70"></div>
        
        <div class="relative z-10">
            <h2 class="text-2xl font-bold text-white mb-4 text-center">Track Your Cosmic Transaction</h2>
            <p class="text-secondary mb-6 text-center">Enter your invoice ID to verify its status in the universe</p>
            
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <input 
                        v-model="searchInput"
                        type="text" 
                        placeholder="Enter invoice ID (e.g., TRX2023...)" 
                        class="w-full px-4 py-3 rounded-lg bg-dark-card/50 border border-primary/30 text-white focus:outline-none focus:border-secondary transition-all duration-300"
                        :class="{ 'animate-pulse-border': isFocused }"
                        @focus="handleFocus"
                        @blur="handleBlur"
                        @keyup.enter="handleSearch"
                    />
                    
                    <!-- Focus effects -->
                    <div v-if="isFocused" class="absolute inset-0 rounded-lg pointer-events-none">
                        <div class="absolute top-0 left-1/4 w-1 h-1 bg-primary rounded-full animate-ping"></div>
                        <div class="absolute bottom-0 right-1/3 w-1 h-1 bg-secondary rounded-full animate-pulse"></div>
                    </div>
                </div>
                
                <button 
                    @click="handleSearch" 
                    :disabled="isSearching"
                    class="px-6 py-3 bg-gradient-to-r from-primary to-primary/80 hover:from-secondary hover:to-secondary/90 text-white rounded-lg transition-all duration-300 transform hover:scale-105 disabled:opacity-70 disabled:cursor-not-allowed"
                >
                    <span v-if="!isSearching">Search Transaction</span>
                    <div v-else class="flex items-center justify-center">
                        <div class="h-5 w-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                        <span>Searching...</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { router } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import { debounce } from 'lodash';

const props = defineProps({
    searchQuery: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: null,
    },
    searchResult: {
        type: Object,
        default: null,
    }
});

const { toast } = useToast();
const searchInput = ref(props.searchQuery || '');
const isSearching = ref(false);
const isFocused = ref(false);

// Debounced search input handling
const debouncedValidateInput = debounce((value) => {
    if (value.length > 0 && value.length < 4) {
        // Show quick validation feedback
        addQuantumGlowEffect('error');
    } else if (value.length >= 4) {
        addQuantumGlowEffect('success');
    }
}, 500);

// Watch for input changes to trigger validation
watch(searchInput, (newValue) => {
    debouncedValidateInput(newValue);
});

// Watch for errors from props
watch(() => props.error, (newError) => {
    if (newError) {
        toast.error(newError);
        addBlackHoleEffect();
    }
});

// Watch for search results
watch(() => props.searchResult, (newResult) => {
    if (newResult) {
        addMeteorShowerEffect();
    }
}, { immediate: true });

const handleSearch = () => {
    if (!searchInput.value || searchInput.value.trim().length < 4) {
        toast.error('Please enter a valid transaction ID or invoice number');
        addQuantumGlowEffect('error');
        return;
    }
    
    isSearching.value = true;
    addWarpTunnelEffect();
    
    router.get('/cek-transaksi', {
        invoice: searchInput.value.trim()
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isSearching.value = false;
        },
        onError: () => {
            isSearching.value = false;
            toast.error('Error searching for transaction. Please try again.');
        }
    });
};

const handleFocus = () => {
    isFocused.value = true;
};

const handleBlur = () => {
    isFocused.value = false;
};

// Cosmic visual effects
const addQuantumGlowEffect = (type) => {
    const input = document.querySelector('input[placeholder*="invoice"]');
    if (!input) return;
    
    input.classList.add('quantum-glow');
    input.classList.add(type === 'error' ? 'quantum-glow-error' : 'quantum-glow-success');
    
    setTimeout(() => {
        input.classList.remove('quantum-glow', 'quantum-glow-error', 'quantum-glow-success');
    }, 1000);
};

const addMeteorShowerEffect = () => {
    const container = document.getElementById('cosmic-search-particles');
    if (!container) return;
    
    // Create meteors
    for (let i = 0; i < 12; i++) {
        const meteor = document.createElement('div');
        meteor.classList.add('meteor');
        meteor.style.left = `${Math.random() * 100}%`;
        meteor.style.top = `${Math.random() * -50}%`;
        meteor.style.animationDuration = `${0.5 + Math.random() * 1}s`;
        meteor.style.animationDelay = `${Math.random() * 0.5}s`;
        container.appendChild(meteor);
        
        // Remove meteor after animation
        setTimeout(() => {
            container.removeChild(meteor);
        }, 2000);
    }
};

const addBlackHoleEffect = () => {
    const container = document.getElementById('cosmic-search-particles');
    if (!container) return;
    
    const blackhole = document.createElement('div');
    blackhole.classList.add('blackhole');
    container.appendChild(blackhole);
    
    // Remove black hole after animation
    setTimeout(() => {
        container.removeChild(blackhole);
    }, 3000);
};

const addWarpTunnelEffect = () => {
    document.body.classList.add('warping');
    setTimeout(() => {
        document.body.classList.remove('warping');
    }, 500);
};

onMounted(() => {
    // Initialize particles
    const particleContainer = document.getElementById('cosmic-search-particles');
    if (particleContainer) {
        createNebulaParticles(particleContainer);
    }
    
    // If there's an error from props, show it
    if (props.error) {
        toast.error(props.error);
    }
    
    // If there's already a search result, show meteor shower
    if (props.searchResult) {
        setTimeout(addMeteorShowerEffect, 500);
    }
});

const createNebulaParticles = (container) => {
    for (let i = 0; i < 50; i++) {
        const particle = document.createElement('div');
        particle.classList.add('cosmic-particle');
        
        // Random position
        particle.style.left = `${Math.random() * 100}%`;
        particle.style.top = `${Math.random() * 100}%`;
        
        // Random size
        const size = Math.random() * 3 + 1;
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        
        // Random opacity and animation duration
        particle.style.opacity = Math.random() * 0.7 + 0.3;
        particle.style.animationDuration = `${Math.random() * 8 + 4}s`;
        
        container.appendChild(particle);
    }
};
</script>

<style scoped>
.animate-pulse-border {
    animation: pulseBorder 2s infinite;
    box-shadow: 0 0 15px 1px var(--color-primary);
}

@keyframes pulseBorder {
    0% { box-shadow: 0 0 5px 1px rgba(155, 135, 245, 0.5); }
    50% { box-shadow: 0 0 15px 3px rgba(155, 135, 245, 0.8); }
    100% { box-shadow: 0 0 5px 1px rgba(155, 135, 245, 0.5); }
}

.cosmic-particle {
    position: absolute;
    background-color: white;
    border-radius: 50%;
    opacity: 0.6;
    animation: float linear infinite;
}

@keyframes float {
    0% {
        transform: translateY(0) translateX(0);
        opacity: 0;
    }
    10% {
        opacity: 0.8;
    }
    90% {
        opacity: 0.4;
    }
    100% {
        transform: translateY(-120px) translateX(20px);
        opacity: 0;
    }
}

.meteor {
    position: absolute;
    width: 2px;
    height: 50px;
    background: linear-gradient(to bottom, rgba(155, 135, 245, 0), rgba(155, 135, 245, 1));
    transform: rotate(45deg);
    animation: meteorFall forwards;
    z-index: 5;
}

@keyframes meteorFall {
    0% {
        transform: translateY(-10%) translateX(-10%) rotate(45deg);
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    70% {
        opacity: 1;
    }
    100% {
        transform: translateY(120%) translateX(120%) rotate(45deg);
        opacity: 0;
    }
}

.blackhole {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: radial-gradient(circle, rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0) 70%);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    animation: blackholeExpand 2s forwards;
    z-index: 10;
}

@keyframes blackholeExpand {
    0% {
        width: 0;
        height: 0;
        opacity: 0;
    }
    30% {
        width: 100px;
        height: 100px;
        opacity: 0.8;
    }
    100% {
        width: 10px;
        height: 10px;
        opacity: 0;
    }
}

.quantum-glow {
    transition: all 0.3s ease;
}

.quantum-glow-success {
    box-shadow: 0 0 15px 5px rgba(51, 195, 240, 0.7);
    border-color: rgba(51, 195, 240, 1) !important;
}

.quantum-glow-error {
    box-shadow: 0 0 15px 5px rgba(255, 71, 87, 0.7);
    border-color: rgba(255, 71, 87, 1) !important;
}

:global(.warping) {
    animation: warpEffect 0.5s forwards;
}

@keyframes warpEffect {
    0% {
        filter: brightness(1);
    }
    50% {
        filter: brightness(1.5) blur(3px);
    }
    100% {
        filter: brightness(1) blur(0);
    }
}
</style>

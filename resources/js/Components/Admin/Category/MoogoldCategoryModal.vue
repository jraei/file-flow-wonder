
<script setup>
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    category: Object,
    staticCategories: Object,
});

const emit = defineEmits(['close']);

const searchQuery = ref('');
const isLoading = ref(false);
const selectedCode = ref(props.category?.kode_kategori || null);
const errorMessage = ref('');
const successMessage = ref('');

// Filter categories based on search query
const filteredCategories = computed(() => {
    if (!props.staticCategories) return {};
    if (!searchQuery.value) return props.staticCategories;
    
    const lowercaseQuery = searchQuery.value.toLowerCase();
    const filtered = {};
    
    Object.entries(props.staticCategories).forEach(([code, name]) => {
        if (name.toLowerCase().includes(lowercaseQuery) || 
            code.includes(lowercaseQuery)) {
            filtered[code] = name;
        }
    });
    
    return filtered;
});

// Close modal with optional refresh
const close = (refresh = false) => {
    searchQuery.value = '';
    errorMessage.value = '';
    successMessage.value = '';
    emit('close', refresh);
};

// Link Moogold category
const linkCategory = async () => {
    if (!selectedCode.value) {
        errorMessage.value = 'Please select a Moogold category.';
        return;
    }
    
    isLoading.value = true;
    errorMessage.value = '';
    
    try {
        const response = await axios.post(route('categories.link-moogold', props.category.id), {
            kode_kategori: selectedCode.value
        });
        
        successMessage.value = response.data.message || 'Category linked successfully.';
        
        // Auto close after success
        setTimeout(() => {
            close(true);
        }, 1500);
        
    } catch (error) {
        console.error('Error linking Moogold category:', error);
        errorMessage.value = error.response?.data?.message || 'Failed to link category.';
    } finally {
        isLoading.value = false;
    }
};

// Select a category
const selectCategory = (code) => {
    selectedCode.value = code;
};
</script>

<template>
    <Modal :show="show" @close="close" max-width="2xl">
        <div class="border border-gray-700 rounded-lg p-3 sm:p-4 md:p-6 bg-dark-card max-h-[80vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-white flex items-center space-x-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/20 text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    </span>
                    <span>Link Moogold Category</span>
                </h3>
                <button @click="close" class="text-gray-400 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Current Category Info -->
            <div class="mb-4 p-3 rounded-lg bg-dark-lighter border border-primary/30 animate-fade-in">
                <p class="text-sm text-gray-400">Current Category</p>
                <h4 class="font-medium text-white">{{ category?.kategori_name }}</h4>
                <div v-if="category?.kode_kategori" class="mt-2">
                    <p class="text-xs text-gray-400">Linked Moogold Category</p>
                    <div class="flex items-center space-x-2 mt-1">
                        <span class="px-2.5 py-0.5 bg-orange-500/20 text-orange-400 text-xs rounded-full border border-orange-500/30 animate-pulse">
                            {{ category.kode_kategori }}
                        </span>
                        <span class="text-sm text-gray-300">
                            {{ staticCategories?.[category.kode_kategori] || 'Unknown' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Error & Success Messages -->
            <div v-if="errorMessage" class="mb-4 p-2 bg-red-500/20 border border-red-500/30 rounded-md text-red-400 text-sm animate-fade-in">
                {{ errorMessage }}
            </div>
            
            <div v-if="successMessage" class="mb-4 p-2 bg-green-500/20 border border-green-500/30 rounded-md text-green-400 text-sm animate-fade-in">
                {{ successMessage }}
            </div>

            <!-- Search Input -->
            <div class="relative mb-4">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    v-model="searchQuery"
                    type="text"
                    class="w-full pl-10 py-2 bg-dark-sidebar border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-white"
                    placeholder="Search Moogold categories..."
                />
            </div>

            <!-- Categories List with Cosmic Theme -->
            <div class="space-y-2 max-h-96 overflow-y-auto cosmic-scrollbar">
                <div v-if="Object.keys(filteredCategories).length === 0" class="py-6 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 mb-3 rounded-full bg-secondary/20">
                        <span class="text-xl">🔍</span>
                    </div>
                    <p class="text-gray-400">No matching categories found.</p>
                </div>
                
                <div
                    v-for="(name, code) in filteredCategories"
                    :key="code"
                    class="p-3 rounded-lg transition-all cursor-pointer relative overflow-hidden"
                    :class="[
                        selectedCode === code 
                            ? 'bg-primary/30 border border-primary/50' 
                            : 'bg-dark-lighter border border-gray-700 hover:border-primary/30'
                    ]"
                    @click="selectCategory(code)"
                >
                    <!-- Cosmic background effect -->
                    <div class="absolute inset-0 z-0 overflow-hidden">
                        <div v-if="selectedCode === code" class="micro-stars"></div>
                    </div>
                    
                    <div class="relative z-10 flex items-center justify-between">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 bg-orange-500/20 text-orange-400 text-xs rounded-full border border-orange-500/30">
                                    {{ code }}
                                </span>
                                <h5 class="font-medium text-white">{{ name }}</h5>
                            </div>
                        </div>
                        
                        <div v-if="selectedCode === code" class="h-5 w-5 text-primary animate-pulse">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-2">
                <button
                    @click="close"
                    class="px-4 py-2 text-gray-300 rounded-lg bg-dark-lighter hover:text-white"
                >
                    Cancel
                </button>
                <button
                    @click="linkCategory"
                    :disabled="isLoading || !selectedCode"
                    class="px-4 py-2 text-white rounded-lg flex items-center justify-center space-x-1 transition-all"
                    :class="[
                        isLoading || !selectedCode
                            ? 'bg-primary/50 cursor-not-allowed'
                            : 'bg-primary hover:bg-primary-hover hover:shadow-glow-primary'
                    ]"
                >
                    <svg v-if="isLoading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span v-else class="text-lg">🔗</span>
                    <span>{{ isLoading ? 'Linking...' : 'Link Category' }}</span>
                </button>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
.cosmic-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: rgba(155, 135, 245, 0.3) rgba(31, 41, 55, 0.5);
}

.cosmic-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.cosmic-scrollbar::-webkit-scrollbar-track {
    background: rgba(31, 41, 55, 0.5);
    border-radius: 8px;
}

.cosmic-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(155, 135, 245, 0.3);
    border-radius: 8px;
    border: 1px solid rgba(155, 135, 245, 0.1);
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.micro-stars {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(1px 1px at 20px 30px, white, rgba(0,0,0,0)),
                      radial-gradient(1px 1px at 40px 70px, white, rgba(0,0,0,0)),
                      radial-gradient(1px 1px at 80px 10px, white, rgba(0,0,0,0)),
                      radial-gradient(1px 1px at 130px 90px, white, rgba(0,0,0,0)),
                      radial-gradient(1px 1px at 160px 30px, white, rgba(0,0,0,0));
    background-repeat: repeat;
    background-size: 200px 200px;
    opacity: 0.3;
    animation: stars-move 8s linear infinite;
    will-change: transform;
}

@keyframes stars-move {
    from {
        transform: translateY(0);
    }
    to {
        transform: translateY(-100px);
    }
}
</style>


<template>
  <div class="bg-dark-card/60 border border-secondary/20 rounded-xl shadow-cosmic overflow-hidden">
    <div 
      class="px-4 py-3 border-b border-secondary/20 bg-primary/10 flex justify-between items-center cursor-pointer"
      @click="isOpen = !isOpen"
    >
      <h3 class="text-white font-medium">Instruksi Pembayaran</h3>
      <svg 
        xmlns="http://www.w3.org/2000/svg" 
        :class="[
          'w-5 h-5 text-gray-400 transition-transform',
          isOpen ? 'transform rotate-180' : ''
        ]" 
        fill="none" 
        viewBox="0 0 24 24" 
        stroke="currentColor"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </div>
    <div v-if="isOpen" class="p-4">
      <div v-if="instructions && instructions.length > 0">
        <div v-for="(step, index) in instructions" :key="index" class="mb-2 flex">
          <div class="flex-shrink-0 mr-3 mt-0.5">
            <div class="w-5 h-5 rounded-full bg-primary/20 border border-primary/40 flex items-center justify-center text-xs text-primary">
              {{ index + 1 }}
            </div>
          </div>
          <div class="text-gray-300 text-sm">
            {{ step }}
          </div>
        </div>
      </div>
      <div v-else class="text-gray-400 text-sm italic">
        Tidak ada instruksi pembayaran yang tersedia
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  instruksi: {
    type: [String, Array, Object],
    default: null,
  },
});

const isOpen = ref(true);

const instructions = computed(() => {
  if (!props.instruksi) return [];
  
  if (typeof props.instruksi === 'string') {
    // If it's a string, try to parse it as JSON, otherwise split by new lines
    try {
      const parsed = JSON.parse(props.instruksi);
      if (Array.isArray(parsed)) return parsed;
      return [props.instruksi];
    } catch (e) {
      return props.instruksi.split('\n').filter(line => line.trim());
    }
  }
  
  if (Array.isArray(props.instruksi)) {
    return props.instruksi;
  }
  
  // If it's an object, convert to array
  if (typeof props.instruksi === 'object') {
    return Object.values(props.instruksi);
  }
  
  return [];
});
</script>

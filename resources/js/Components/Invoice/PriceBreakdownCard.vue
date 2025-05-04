
<template>
  <div class="bg-dark-card border border-secondary/20 rounded-xl overflow-hidden mb-6">
    <!-- Header with toggle -->
    <div 
      class="p-4 flex justify-between items-center cursor-pointer bg-dark-lighter"
      @click="isOpen = !isOpen"
    >
      <h3 class="text-lg font-medium text-white">Rincian Pembayaran</h3>
      <svg
        xmlns="http://www.w3.org/2000/svg"
        :class="['h-5 w-5 transform transition-transform', isOpen ? 'rotate-180' : '']"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <polyline points="6 9 12 15 18 9"></polyline>
      </svg>
    </div>
    
    <!-- Price breakdown details -->
    <div 
      v-show="isOpen"
      class="p-4 transition-all duration-300"
    >
      <!-- Price -->
      <div class="flex justify-between items-center py-2 text-sm">
        <div class="text-gray-400">Harga</div>
        <div class="text-white font-medium">{{ formatCurrency(pembayaran.price) }}</div>
      </div>
      
      <!-- Quantity -->
      <div class="flex justify-between items-center py-2 text-sm border-b border-gray-700">
        <div class="text-gray-400">Jumlah</div>
        <div class="text-white font-medium">1x</div>
      </div>
      
      <!-- Subtotal -->
      <div class="flex justify-between items-center py-2 text-sm">
        <div class="text-gray-400">Subtotal</div>
        <div class="text-white font-medium">{{ formatCurrency(pembayaran.price) }}</div>
      </div>
      
      <!-- Fee (if available) -->
      <div 
        v-if="pembayaran.fee > 0" 
        class="flex justify-between items-center py-2 text-sm border-b border-gray-700"
      >
        <div class="text-gray-400">Biaya</div>
        <div class="text-white font-medium">{{ formatCurrency(pembayaran.fee) }}</div>
      </div>
    </div>
    
    <!-- Total (always visible) -->
    <div class="p-4 bg-primary/10 border-t border-secondary/20">
      <div class="flex justify-between items-center">
        <div class="text-gray-200 font-medium">Total Pembayaran</div>
        <div class="text-lg text-primary font-bold flex items-center">
          {{ formatCurrency(pembayaran.total_price) }}
          <button
            class="ml-2 text-gray-400 hover:text-primary transition-colors"
            @click="copyToClipboard(pembayaran.total_price.toString())"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-4 w-4"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
              <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
  pembayaran: {
    type: Object,
    required: true
  }
});

const isOpen = ref(false);
const { toast } = useToast();

const formatCurrency = (amount) => {
  if (typeof amount !== 'number') {
    return 'Rp 0';
  }
  
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount).replace('IDR', 'Rp');
};

const copyToClipboard = (text) => {
  navigator.clipboard.writeText(text)
    .then(() => {
      toast({
        title: 'Copied!',
        description: 'Amount copied to clipboard',
        variant: 'default',
      });
    })
    .catch(err => {
      console.error('Copy failed:', err);
    });
};
</script>

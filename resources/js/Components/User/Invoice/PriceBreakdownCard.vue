
<template>
  <div class="bg-dark-card/60 border border-secondary/20 rounded-xl shadow-cosmic overflow-hidden">
    <div 
      class="px-4 py-3 border-b border-secondary/20 bg-primary/10 flex justify-between items-center cursor-pointer"
      @click="isOpen = !isOpen"
    >
      <h3 class="text-white font-medium">Rincian Pembayaran</h3>
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
    <div 
      v-if="isOpen"
      class="p-4 space-y-3"
    >
      <div class="flex justify-between">
        <div class="text-gray-400">Harga</div>
        <div class="text-white">Rp {{ formatNumber(price) }}</div>
      </div>
      
      <div class="flex justify-between">
        <div class="text-gray-400">Jumlah</div>
        <div class="text-white">{{ quantity }}x</div>
      </div>
      
      <div class="flex justify-between">
        <div class="text-gray-400">Subtotal</div>
        <div class="text-white">Rp {{ formatNumber(subtotal) }}</div>
      </div>
      
      <div class="flex justify-between">
        <div class="text-gray-400">Biaya</div>
        <div class="text-white">Rp {{ formatNumber(fee) }}</div>
      </div>
      
      <div class="border-t border-gray-700 my-2"></div>
      
      <div class="flex justify-between">
        <div class="text-primary font-medium">Total Pembayaran</div>
        <div class="text-primary font-bold">
          Rp {{ formatNumber(totalPrice) }}
          <button 
            class="ml-2 text-secondary hover:text-secondary/80 transition-colors" 
            @click="copyToClipboard(totalPrice.toString())"
          >
            <svg 
              xmlns="http://www.w3.org/2000/svg" 
              class="w-4 h-4" 
              viewBox="0 0 20 20" 
              fill="currentColor"
            >
              <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
              <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z" />
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
  price: {
    type: Number,
    required: true,
  },
  quantity: {
    type: Number,
    default: 1,
  },
  subtotal: {
    type: Number,
    required: true,
  },
  fee: {
    type: Number,
    required: true,
  },
  totalPrice: {
    type: Number,
    required: true,
  },
});

const { showToast } = useToast();
const isOpen = ref(false);

const formatNumber = (number) => {
  return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

const copyToClipboard = (text) => {
  navigator.clipboard.writeText(text)
    .then(() => {
      showToast('Amount copied to clipboard', 'success');
    })
    .catch(() => {
      showToast('Failed to copy amount', 'error');
    });
};
</script>

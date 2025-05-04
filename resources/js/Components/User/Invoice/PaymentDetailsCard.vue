
<template>
  <div class="bg-dark-card/60 border border-secondary/20 rounded-xl shadow-cosmic overflow-hidden">
    <div class="px-4 py-3 border-b border-secondary/20 bg-primary/10">
      <h3 class="text-white font-medium">Metode Pembayaran</h3>
    </div>
    <div class="p-4 space-y-4">
      <div class="text-white font-medium">{{ paymentMethod }}</div>
      
      <div class="space-y-3">
        <div class="flex justify-between">
          <div class="text-gray-400">Nomor Invoice</div>
          <div class="text-white flex items-center">
            {{ reference }}
            <button 
              class="ml-2 text-secondary hover:text-secondary/80 transition-colors" 
              @click="copyToClipboard(reference)"
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
        
        <div class="flex justify-between">
          <div class="text-gray-400">Status Pembayaran</div>
          <div 
            :class="[
              'px-2 py-0.5 text-xs rounded uppercase font-medium',
              paymentStatus === 'paid' ? 'bg-green-900/30 text-green-400' :
              paymentStatus === 'pending' ? 'bg-yellow-900/30 text-yellow-400' :
              'bg-red-900/30 text-red-400'
            ]"
          >
            {{ paymentStatusLabel }}
          </div>
        </div>
        
        <div class="flex justify-between">
          <div class="text-gray-400">Status Transaksi</div>
          <div 
            :class="[
              'px-2 py-0.5 text-xs rounded uppercase font-medium',
              orderStatus === 'completed' ? 'bg-green-900/30 text-green-400' :
              orderStatus === 'processing' ? 'bg-blue-900/30 text-blue-400' :
              orderStatus === 'pending' ? 'bg-yellow-900/30 text-yellow-400' :
              'bg-red-900/30 text-red-400'
            ]"
          >
            {{ orderStatusLabel }}
          </div>
        </div>
        
        <div v-if="message" class="text-gray-300 text-sm border-t border-gray-700 pt-3 mt-3">
          {{ message }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
  paymentMethod: {
    type: String,
    required: true,
  },
  reference: {
    type: String,
    required: true,
  },
  paymentStatus: {
    type: String,
    required: true,
  },
  orderStatus: {
    type: String,
    required: true,
  },
  message: {
    type: String,
    default: '',
  },
});

const { showToast } = useToast();

const paymentStatusLabel = computed(() => {
  const statusMap = {
    'paid': 'PAID',
    'pending': 'UNPAID',
    'failed': 'FAILED',
    'cancelled': 'CANCELLED',
  };
  return statusMap[props.paymentStatus] || 'UNKNOWN';
});

const orderStatusLabel = computed(() => {
  const statusMap = {
    'completed': 'COMPLETED',
    'processing': 'PROCESSING',
    'pending': 'PENDING',
    'failed': 'FAILED',
    'cancelled': 'CANCELLED',
  };
  return statusMap[props.orderStatus] || 'UNKNOWN';
});

const copyToClipboard = (text) => {
  navigator.clipboard.writeText(text)
    .then(() => {
      showToast('Reference copied to clipboard', 'success');
    })
    .catch(() => {
      showToast('Failed to copy reference', 'error');
    });
};
</script>


<template>
  <div class="bg-dark-card border border-secondary/20 rounded-xl p-4 mb-6">
    <h3 class="text-lg font-medium text-white mb-4">Metode Pembayaran</h3>
    
    <div class="mb-6">
      <div class="text-sm text-gray-400 mb-2">
        {{ pembayaran.payment_method || 'QRIS (Semua Pembayaran)' }}
      </div>
      
      <!-- Payment Details Grid -->
      <div class="grid gap-3">
        <!-- Invoice Number -->
        <div class="flex justify-between items-center">
          <div class="text-sm text-gray-400">Nomor Invoice</div>
          <div class="text-sm text-white font-medium flex items-center">
            {{ pembelian.order_id }}
            <button 
              class="ml-2 text-gray-400 hover:text-primary transition-colors"
              @click="copyToClipboard(pembelian.order_id)"
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
        
        <!-- Payment Status -->
        <div class="flex justify-between items-center">
          <div class="text-sm text-gray-400">Status Pembayaran</div>
          <div class="text-xs px-2 py-1 rounded font-medium" :class="getStatusClass(pembayaran.status)">
            {{ getStatusLabel(pembayaran.status) }}
          </div>
        </div>
        
        <!-- Transaction Status -->
        <div class="flex justify-between items-center">
          <div class="text-sm text-gray-400">Status Transaksi</div>
          <div class="text-xs px-2 py-1 rounded font-medium" :class="getStatusClass(pembelian.status)">
            {{ getStatusLabel(pembelian.status) }}
          </div>
        </div>
        
        <!-- Message -->
        <div v-if="currentStage === 2" class="pt-3 border-t border-gray-700 mt-2">
          <div class="text-sm text-white">
            Silakan untuk melakukan pembayaran dengan metode yang kamu pilih.
          </div>
        </div>
      </div>
    </div>
    
    <!-- Payment QR Code (if available) -->
    <div v-if="showQrCode" class="mt-6 flex flex-col items-center">
      <div class="bg-white p-4 rounded-md w-48 h-48 flex items-center justify-center mb-4">
        <img 
          v-if="pembayaran.qris_url" 
          :src="pembayaran.qris_url" 
          alt="QR Code" 
          class="max-w-full max-h-full"
        />
        <div v-else class="bg-gray-100 w-full h-full flex items-center justify-center">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-10 w-10 text-gray-400"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <path d="M12 8v8M8 12h8"></path>
          </svg>
        </div>
      </div>
      
      <!-- Download QR Button -->
      <button 
        @click="downloadQrCode"
        class="bg-secondary/20 hover:bg-secondary/30 text-secondary hover:text-white transition-colors duration-200 py-3 px-4 rounded-md text-sm font-medium w-full flex items-center justify-center cosmic-hover"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5 mr-2"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
          <polyline points="7 10 12 15 17 10"></polyline>
          <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
        Unduh Kode QR
      </button>
      
      <p class="text-xs text-gray-400 mt-2">
        Screenshot jika QR Code tidak bisa di download
      </p>
    </div>
    
    <!-- Payment URL Button -->
    <div v-else-if="pembayaran.payment_link" class="mt-6">
      <a 
        :href="pembayaran.payment_link" 
        target="_blank"
        class="bg-primary hover:bg-primary-hover text-white py-3 px-4 rounded-md text-sm font-medium flex items-center justify-center cosmic-hover w-full"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5 mr-2"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <circle cx="12" cy="12" r="10"></circle>
          <path d="M12 8v4M12 16h.01"></path>
        </svg>
        Lanjutkan ke Pembayaran
      </a>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import useInvoiceStatus from '@/Composables/useInvoiceStatus';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
  pembelian: {
    type: Object,
    required: true
  },
  pembayaran: {
    type: Object,
    default: null
  },
  currentStage: {
    type: Number,
    default: 1
  }
});

const { toast } = useToast();
const { getStatusLabel, getStatusClass } = useInvoiceStatus();

const showQrCode = computed(() => {
  return props.currentStage === 2 && 
         props.pembayaran && 
         props.pembayaran.payment_method && 
         props.pembayaran.payment_method.toLowerCase().includes('qris');
});

const copyToClipboard = (text) => {
  navigator.clipboard.writeText(text)
    .then(() => {
      toast({
        title: 'Copied!',
        description: 'Invoice number copied to clipboard',
        variant: 'default',
      });
    })
    .catch(err => {
      console.error('Copy failed:', err);
    });
};

const downloadQrCode = () => {
  if (!props.pembayaran || !props.pembayaran.qris_url) {
    toast({
      title: 'Error',
      description: 'QR Code not available',
      variant: 'destructive',
    });
    return;
  }
  
  // Create a temporary link to download the image
  const link = document.createElement('a');
  link.href = props.pembayaran.qris_url;
  link.download = `QR_${props.pembelian.order_id}.png`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};
</script>

<style scoped>
.cosmic-hover {
  position: relative;
  overflow: hidden;
}

.cosmic-hover:before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    90deg, 
    transparent, 
    rgba(155, 135, 245, 0.2), 
    transparent
  );
  transition: left 0.7s;
}

.cosmic-hover:hover:before {
  left: 100%;
}
</style>

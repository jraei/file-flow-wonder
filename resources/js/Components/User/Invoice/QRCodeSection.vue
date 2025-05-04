
<template>
  <div v-if="qrUrl" class="flex flex-col items-center space-y-4">
    <div class="bg-white p-4 rounded-xl w-64 h-64 flex items-center justify-center">
      <img :src="qrUrl" alt="QR Code" class="w-full h-full object-contain" />
    </div>
    
    <button 
      class="w-full bg-primary/20 hover:bg-primary/30 text-primary border border-primary/40 rounded-md py-3 px-6 transition-colors flex items-center justify-center space-x-2"
      @click="downloadQR"
    >
      <svg 
        xmlns="http://www.w3.org/2000/svg" 
        class="w-5 h-5" 
        viewBox="0 0 20 20" 
        fill="currentColor"
      >
        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
      </svg>
      <span>Unduh Kode QR</span>
    </button>
    
    <div class="text-xs text-gray-400">
      Screenshot jika QR Code tidak bisa di download
    </div>
  </div>
  
  <div v-else-if="paymentLink" class="flex flex-col items-center space-y-4">
    <button 
      class="w-full bg-primary hover:bg-primary/80 text-white rounded-md py-3 px-6 transition-colors flex items-center justify-center space-x-2"
      @click="redirectToPayment"
    >
      <svg 
        xmlns="http://www.w3.org/2000/svg" 
        class="w-5 h-5" 
        viewBox="0 0 20 20" 
        fill="currentColor"
      >
        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
      </svg>
      <span>Lanjutkan Ke Pembayaran</span>
    </button>
  </div>
</template>

<script setup>
import { useToast } from '@/Composables/useToast';

const props = defineProps({
  qrUrl: {
    type: String,
    default: null,
  },
  paymentLink: {
    type: String,
    default: null,
  },
  orderId: {
    type: String,
    required: true,
  },
});

const { showToast } = useToast();

const downloadQR = () => {
  // Create a temporary anchor element
  const link = document.createElement('a');
  link.href = props.qrUrl;
  link.download = `QR_${props.orderId}.png`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  
  showToast('QR Code is being downloaded', 'success');
};

const redirectToPayment = () => {
  if (props.paymentLink) {
    window.open(props.paymentLink, '_blank');
  } else {
    showToast('Payment link not available', 'error');
  }
};
</script>

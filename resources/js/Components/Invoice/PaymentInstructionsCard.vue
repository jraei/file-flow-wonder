
<template>
  <div class="bg-dark-card border border-secondary/20 rounded-xl overflow-hidden mb-6">
    <!-- Header with toggle -->
    <div 
      class="p-4 flex justify-between items-center cursor-pointer bg-dark-lighter"
      @click="isOpen = !isOpen"
    >
      <h3 class="text-lg font-medium text-white">Instruksi Pembayaran</h3>
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
    
    <!-- Instructions content -->
    <div 
      v-show="isOpen"
      class="p-4 transition-all duration-300 cosmic-instructions"
    >
      <div v-if="pembayaran && pembayaran.instruksi" class="text-white text-sm space-y-3">
        <div v-html="formattedInstructions"></div>
      </div>
      <div v-else class="text-white text-sm space-y-3">
        <h4 class="font-medium">Cara Melakukan Pembayaran</h4>
        <ul class="list-disc pl-5 space-y-2">
          <li>Download Barcode atau Screenshot jika tidak bisa di download</li>
          <li>Buka aplikasi E-wallet atau mobile banking pilih Scan Barcode</li>
          <li>Insert Image from Gallery dan pilih gambar Barcode</li>
          <li>Pastikan nama penerima <span class="text-primary font-medium">VF1NSTORE.ID</span></li>
          <li>Lalu Bayar dan selesai</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  pembayaran: {
    type: Object,
    default: null
  }
});

const isOpen = ref(false);

// Format instructions with cosmic styling
const formattedInstructions = computed(() => {
  if (!props.pembayaran || !props.pembayaran.instruksi) return '';
  
  return props.pembayaran.instruksi
    // Convert bullet points to cosmic planets
    .replace(/^-\s+(.*)$/gm, '<div class="cosmic-bullet">$1</div>')
    // Style important text
    .replace(/(VFINSTORE\.ID)/gi, '<span class="text-primary font-medium">$1</span>')
    // Style links as shooting stars
    .replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" class="cosmic-link" target="_blank">$1</a>');
});
</script>

<style scoped>
.cosmic-instructions :deep(.cosmic-bullet) {
  position: relative;
  padding-left: 1.5rem;
  margin-bottom: 0.5rem;
}

.cosmic-instructions :deep(.cosmic-bullet:before) {
  content: '';
  position: absolute;
  left: 0;
  top: 0.25rem;
  width: 0.75rem;
  height: 0.75rem;
  background: linear-gradient(135deg, #9b87f5 0%, #33C3F0 100%);
  border-radius: 50%;
  box-shadow: 0 0 5px rgba(155, 135, 245, 0.6);
}

.cosmic-instructions :deep(.cosmic-link) {
  position: relative;
  color: #33C3F0;
  text-decoration: none;
  padding-right: 1rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
  display: inline-block;
}

.cosmic-instructions :deep(.cosmic-link:after) {
  content: '';
  position: absolute;
  top: 50%;
  right: 0;
  width: 0.5rem;
  height: 0.5rem;
  background: #33C3F0;
  border-radius: 50%;
  transform: translateY(-50%);
  box-shadow: 0 0 5px rgba(51, 195, 240, 0.6);
}

.cosmic-instructions :deep(.cosmic-link:hover) {
  text-decoration: underline;
}
</style>

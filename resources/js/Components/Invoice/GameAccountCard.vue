
<template>
  <div class="bg-dark-card border border-secondary/20 rounded-xl p-4 mb-6">
    <div class="flex items-start">
      <!-- Game icon/image -->
      <div class="mr-4">
        <div class="w-20 h-20 rounded-lg overflow-hidden bg-content_background">
          <img 
            v-if="pembelian.layanan && pembelian.layanan.produk && pembelian.layanan.produk.gambar" 
            :src="pembelian.layanan.produk.gambar" 
            class="w-full h-full object-cover"
            :alt="pembelian.layanan?.produk?.nama"
          />
          <div v-else class="w-full h-full flex items-center justify-center bg-dark-lighter">
            <svg 
              xmlns="http://www.w3.org/2000/svg"
              class="w-10 h-10 text-gray-400"
              viewBox="0 0 24 24" 
              fill="none" 
              stroke="currentColor" 
              stroke-width="2" 
              stroke-linecap="round" 
              stroke-linejoin="round"
            >
              <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
              <line x1="8" y1="21" x2="16" y2="21"></line>
              <line x1="12" y1="17" x2="12" y2="21"></line>
            </svg>
          </div>
        </div>
      </div>
      
      <!-- Account Information -->
      <div class="flex-1">
        <h3 class="text-lg font-medium text-white mb-2">Informasi Akun</h3>
        
        <div class="space-y-2">
          <!-- Nickname (if available) -->
          <div v-if="pembelian.nickname" class="grid grid-cols-3 gap-2">
            <div class="text-sm text-gray-400">Nickname</div>
            <div class="col-span-2 text-sm text-white font-medium">: {{ pembelian.nickname }}</div>
          </div>
          
          <!-- ID -->
          <div class="grid grid-cols-3 gap-2">
            <div class="text-sm text-gray-400">ID</div>
            <div class="col-span-2 text-sm text-white font-medium">: {{ pembelian.input_id }}</div>
          </div>
          
          <!-- Server/Zone (if available) -->
          <div v-if="pembelian.input_zone" class="grid grid-cols-3 gap-2">
            <div class="text-sm text-gray-400">Server</div>
            <div class="col-span-2 text-sm text-white font-medium">: {{ pembelian.input_zone }}</div>
          </div>
          
          <!-- Additional dynamic fields from callback_data -->
          <template v-if="pembelian.callback_data && typeof pembelian.callback_data === 'object'">
            <div 
              v-for="(value, key) in pembelian.callback_data" 
              :key="key" 
              class="grid grid-cols-3 gap-2"
            >
              <div class="text-sm text-gray-400">{{ formatFieldName(key) }}</div>
              <div class="col-span-2 text-sm text-white font-medium">: {{ value }}</div>
            </div>
          </template>
        </div>
      </div>
    </div>
    
    <!-- Game/Product Name -->
    <div class="mt-4 border-t border-gray-700 pt-2">
      <div class="text-sm text-gray-400">
        {{ pembelian.layanan?.produk?.nama || 'Game Product' }}
      </div>
      <div class="text-sm font-medium text-white">
        {{ pembelian.layanan?.nama_layanan || 'Service' }}
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  pembelian: {
    type: Object,
    required: true
  }
});

// Helper function to format field names
const formatFieldName = (key) => {
  // Convert snake_case or camelCase to Title Case
  return key
    .replace(/_/g, ' ')
    .replace(/([A-Z])/g, ' $1')
    .replace(/^./, (str) => str.toUpperCase());
};
</script>

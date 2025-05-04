
<template>
  <GuestLayout>
    <div class="relative px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <!-- Background cosmic particles -->
      <div class="absolute inset-0 z-0">
        <CosmicParticles />
      </div>
      
      <!-- Main content container -->
      <div class="relative z-10">
        <!-- Back button -->
        <div class="mb-6">
          <a 
            href="/" 
            class="inline-flex items-center text-sm text-gray-400 hover:text-primary transition-colors"
          >
            <svg 
              xmlns="http://www.w3.org/2000/svg" 
              class="h-4 w-4 mr-1" 
              viewBox="0 0 24 24" 
              fill="none" 
              stroke="currentColor" 
              stroke-width="2" 
              stroke-linecap="round" 
              stroke-linejoin="round"
            >
              <path d="m15 18-6-6 6-6"/>
            </svg>
            Kembali ke Beranda
          </a>
        </div>
        
        <!-- Progress timeline -->
        <ProgressTimeline 
          :current-stage="currentStage" 
          :stage-labels="stageLabels"
          :stage-descriptions="stageDescriptions"
          :pembayaran="pembayaran"
        />
        
        <!-- Main content grid -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mt-4">
          <!-- Left column - Account details and payments -->
          <div class="md:col-span-7">
            <!-- Game Account Card -->
            <GameAccountCard :pembelian="pembelian" />
            
            <!-- Price breakdown -->
            <PriceBreakdownCard 
              v-if="pembayaran" 
              :pembayaran="pembayaran" 
            />
            
            <!-- Payment Instructions -->
            <PaymentInstructionsCard 
              v-if="pembayaran" 
              :pembayaran="pembayaran" 
            />
          </div>
          
          <!-- Right column - Payment details -->
          <div class="md:col-span-5">
            <PaymentDetailsCard 
              :pembelian="pembelian" 
              :pembayaran="pembayaran"
              :current-stage="currentStage" 
            />
            
            <!-- Support contact -->
            <div class="bg-dark-card border border-secondary/20 rounded-xl p-4 mb-6">
              <h3 class="text-lg font-medium text-white mb-2">Butuh Bantuan?</h3>
              <p class="text-sm text-gray-400 mb-4">
                Hubungi tim dukungan kami jika Anda memiliki pertanyaan atau mengalami masalah.
              </p>
              <a 
                href="https://wa.me/+6285123456789" 
                target="_blank"
                class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md text-sm font-medium flex items-center justify-center cosmic-hover w-full"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5 mr-2"
                  viewBox="0 0 24 24"
                  fill="currentColor"
                >
                  <path 
                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"
                  />
                </svg>
                Chat Customer Service
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <Toast />
  </GuestLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import CosmicParticles from '@/Components/User/Flashsale/CosmicParticles.vue';
import Toast from '@/Components/Toast.vue';
import ProgressTimeline from '@/Components/Invoice/ProgressTimeline.vue';
import GameAccountCard from '@/Components/Invoice/GameAccountCard.vue';
import PriceBreakdownCard from '@/Components/Invoice/PriceBreakdownCard.vue';
import PaymentDetailsCard from '@/Components/Invoice/PaymentDetailsCard.vue';
import PaymentInstructionsCard from '@/Components/Invoice/PaymentInstructionsCard.vue';
import useInvoiceStatus from '@/Composables/useInvoiceStatus';

// Get order data from props
const props = defineProps({
  order: {
    type: Object,
    required: true,
    default: () => ({}),
  }
});

const pembelian = computed(() => props.order);
const pembayaran = computed(() => props.order?.pembayaran);

// Use the invoice status composable
const { 
  getStageNumber, 
  stageLabels, 
  stageDescriptions,
  getStatusLabel,
  getStatusClass 
} = useInvoiceStatus(pembelian, pembayaran);

// Get current stage
const currentStage = computed(() => getStageNumber.value);

// Set up auto-refresh for pending orders
let refreshInterval;

onMounted(() => {
  // If the order is in a non-final state, refresh periodically
  if (['pending', 'processing'].includes(pembelian.value?.status)) {
    refreshInterval = setInterval(() => {
      window.location.reload();
    }, 30000); // Refresh every 30 seconds
  }
  
  return () => {
    if (refreshInterval) clearInterval(refreshInterval);
  };
});
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

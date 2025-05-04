
<template>
  <GuestLayout>
    <div class="relative px-4 py-16 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="absolute inset-0 z-0">
        <CosmicParticles />
      </div>
      
      <div class="relative z-10 max-w-lg mx-auto overflow-hidden shadow-cosmic rounded-xl bg-dark-card border border-secondary/20">
        <div class="px-4 py-5 border-b sm:px-6 border-secondary/20 bg-primary/10">
          <h3 class="text-xl font-medium leading-6 text-center text-white">Payment Processing</h3>
        </div>
        
        <div class="px-4 py-8 text-center sm:p-6">
          <div class="inline-block p-4 mb-6 rounded-full bg-primary/10">
            <svg class="w-12 h-12 text-primary animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
          
          <h2 class="mb-2 text-2xl font-bold text-white">Processing Payment</h2>
          <p class="mb-6 text-gray-400">Order #{{ $page.props.order_id }}</p>
          
          <p class="mb-8 text-gray-300">We are connecting you to our payment provider. Please do not close this window.</p>
          
          <div class="p-4 mb-6 text-left rounded-lg bg-dark-lighter">
            <div class="flex items-center space-x-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-secondary" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
              <p class="text-sm text-secondary">This page will automatically redirect to the payment gateway.</p>
            </div>
          </div>
          
          <a 
            href="/" 
            class="inline-flex items-center px-4 py-2 mt-4 text-sm font-medium transition-colors border rounded-md text-gray-300 border-gray-600 hover:bg-gray-700"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Return to Home
          </a>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import { onMounted, ref } from "vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import CosmicParticles from "@/Components/User/Flashsale/CosmicParticles.vue";
import { usePage } from "@inertiajs/vue3";

const $page = usePage();

onMounted(() => {
  // In a real implementation, we would fetch payment information and redirect
  // console.log('Order ID:', $page.props.order_id);
  
  // This is a placeholder for the actual payment gateway redirect
  // setTimeout(() => {
  //   window.location.href = 'https://payment-gateway.example/pay/' + $page.props.order_id;
  // }, 3000);
});
</script>

<style scoped>
.shadow-cosmic {
  box-shadow: 0 0 25px -5px rgba(155, 135, 245, 0.15), 0 0 10px -5px rgba(51, 195, 240, 0.2);
}
</style>

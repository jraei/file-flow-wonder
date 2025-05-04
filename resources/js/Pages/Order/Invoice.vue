
<template>
  <GuestLayout>
    <div class="relative px-4 py-12 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="absolute inset-0 z-0">
        <CosmicParticles />
      </div>

      <div class="relative z-10">
        <!-- Progress Timeline -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-white mb-6">Progress Transaksi</h1>
          <CosmicTimeline :current-stage="getCurrentStage()" />
        </div>
        
        <!-- Countdown Timer -->
        <div 
          v-if="order && pembayaran.status === 'pending'" 
          class="mb-6 py-2 px-4 rounded-lg bg-primary/10 border border-primary/20 inline-flex"
        >
          <CosmicCountdown :expiry-time="pembayaran.expired_time" />
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Left Column -->
          <div class="space-y-6">
            <!-- Game Account Card -->
            <GameAccountCard
              :icon="getProdukImage()"
              :game="order.layanan.produk.nama"
              :game-description="order.layanan.produk.deskripsi || ''"
              :nickname="order.nickname"
              :id="order.input_id"
              :server="order.input_zone"
            />

            <!-- Price Breakdown Card -->
            <PriceBreakdownCard
              :price="pembayaran.price"
              :quantity="1"
              :subtotal="pembayaran.price"
              :fee="pembayaran.fee"
              :total-price="pembayaran.total_price"
            />
            
            <!-- Payment Instructions -->
            <PaymentInstructions
              :instruksi="pembayaran.instruksi"
            />
          </div>

          <!-- Right Column -->
          <div class="space-y-6">
            <!-- Payment Details Card -->
            <PaymentDetailsCard
              :payment-method="getPaymentMethodName()"
              :reference="order.order_id"
              :payment-status="pembayaran.status"
              :order-status="order.status"
              :message="getStatusMessage()"
            />

            <!-- QR Code or Payment Button -->
            <div class="bg-dark-card/60 border border-secondary/20 rounded-xl shadow-cosmic p-6">
              <QRCodeSection
                :qr-url="pembayaran.qr_url"
                :payment-link="pembayaran.payment_link"
                :order-id="order.order_id"
              />
            </div>
          </div>
        </div>
        
        <!-- Return to Home Button -->
        <div class="mt-8 text-center">
          <a
            href="/"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-300 transition-colors border border-gray-600 rounded-md hover:bg-gray-700"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-4 h-4 mr-2"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z"
                clip-rule="evenodd"
              />
            </svg>
            Return to Home
          </a>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import { computed } from "vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import CosmicParticles from "@/Components/User/Flashsale/CosmicParticles.vue";
import CosmicTimeline from "@/Components/User/Invoice/CosmicTimeline.vue";
import CosmicCountdown from "@/Components/User/Invoice/CosmicCountdown.vue";
import GameAccountCard from "@/Components/User/Invoice/GameAccountCard.vue";
import PaymentDetailsCard from "@/Components/User/Invoice/PaymentDetailsCard.vue";
import PriceBreakdownCard from "@/Components/User/Invoice/PriceBreakdownCard.vue";
import QRCodeSection from "@/Components/User/Invoice/QRCodeSection.vue";
import PaymentInstructions from "@/Components/User/Invoice/PaymentInstructions.vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const order = computed(() => page.props.order);
const pembayaran = computed(() => page.props.order.pembayaran);

const getCurrentStage = () => {
  const status = order.value.status;
  const paymentStatus = pembayaran.value.status;
  
  if (status === 'completed') {
    return 'completed';
  }
  
  if (status === 'processing') {
    return 'processing';
  }
  
  if (paymentStatus === 'paid') {
    return 'processing';
  }
  
  return 'payment';
};

const getPaymentMethodName = () => {
  return pembayaran.value.payment_method || 'Unknown Method';
};

const getStatusMessage = () => {
  const status = order.value.status;
  const paymentStatus = pembayaran.value.status;
  
  if (paymentStatus === 'pending') {
    return 'Silakan untuk melakukan pembayaran dengan metode yang kamu pilih.';
  }
  
  if (paymentStatus === 'paid' && status === 'pending') {
    return 'Pembayaran telah diterima, pesanan akan segera diproses.';
  }
  
  if (status === 'processing') {
    return 'Pesanan sedang diproses oleh sistem kami.';
  }
  
  if (status === 'completed') {
    return 'Transaksi telah selesai. Terima kasih telah berbelanja!';
  }
  
  return '';
};

const getProdukImage = () => {
  const produk = order.value.layanan.produk;
  return produk.gambar || 'https://via.placeholder.com/100';
};
</script>

<style scoped>
.shadow-cosmic {
  box-shadow: 0 0 25px -5px rgba(155, 135, 245, 0.15),
    0 0 10px -5px rgba(51, 195, 240, 0.2);
}
</style>

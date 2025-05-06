
<template>
  <div class="relative">
    <!-- Cosmic Navbar -->
    <CosmicNavbar />
    
    <main class="min-h-screen pt-16 pb-32 bg-gradient-to-b from-content_background to-dark-DEFAULT overflow-hidden">
      <!-- Cosmic Leaderboard Container -->
      <div class="container mx-auto px-4 py-12 relative">
        <!-- Cosmic Decorations -->
        <CosmicStars />
        
        <h1 class="text-3xl md:text-4xl font-bold text-center text-white mb-8">
          <span class="bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
            Cosmic Leaderboard
          </span>
        </h1>
        
        <!-- Tabs Container -->
        <CosmicTabs
          :time-periods="['Today', 'This Week', 'This Month']"
          :active-tab="activeTab"
          @tab-change="handleTabChange"
        />
        
        <!-- Leaderboard Rankings -->
        <CosmicLeaderboardRankings 
          :rankings="currentRankings" 
          :loading="loading"
        />
        
        <!-- Footer Navigation -->
        <div class="mt-12 text-center">
          <Link 
            :href="route('index')" 
            class="inline-flex items-center space-x-2 px-6 py-3 text-lg text-white bg-primary/40 hover:bg-primary/60 transition-all duration-300 rounded-full backdrop-blur-sm border border-primary/30"
          >
            <span>Return to Home</span>
          </Link>
        </div>
      </div>
    </main>
    
    <!-- Cosmic Footer -->
    <CosmicFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import CosmicNavbar from '@/Components/User/Navigation/CosmicNavbar.vue';
import CosmicFooter from '@/Components/User/Footer/CosmicFooter.vue';
import CosmicTabs from '@/Components/Leaderboard/CosmicTabs.vue';
import CosmicLeaderboardRankings from '@/Components/Leaderboard/CosmicLeaderboardRankings.vue';
import CosmicStars from '@/Components/Leaderboard/CosmicStars.vue';
import { useToast } from '@/Composables/useToast';

// Define props
const props = defineProps({
  daily: Array,
  weekly: Array,
  monthly: Array,
  serverTime: String,
});

const { toast } = useToast();
const loading = ref(false);
const activeTab = ref('Today');

// Compute current rankings based on active tab
const currentRankings = computed(() => {
  switch (activeTab.value) {
    case 'Today':
      return props.daily || [];
    case 'This Week':
      return props.weekly || [];
    case 'This Month':
      return props.monthly || [];
    default:
      return [];
  }
});

// Handle tab change
const handleTabChange = (tab) => {
  loading.value = true;
  setTimeout(() => {
    activeTab.value = tab;
    loading.value = false;
  }, 300);
};

onMounted(() => {
  // Welcome message
  toast.info('Welcome to the Cosmic Leaderboard! 🌌');
});
</script>

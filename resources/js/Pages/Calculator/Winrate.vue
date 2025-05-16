
<script setup>
import { ref, computed, watch } from "vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head } from "@inertiajs/vue3";
import { debounce } from "lodash";
import CosmicStars from "@/Components/Leaderboard/CosmicStars.vue";

// Form state management
const totalMatches = ref("");
const currentWinRate = ref("");
const desiredWinRate = ref("");

// Validation errors
const errors = ref({
  totalMatches: "",
  currentWinRate: "",
  desiredWinRate: "",
});

// Result states
const calculating = ref(false);
const result = ref(null);
const resultMessage = ref("");

// Validation functions
const validateTotalMatches = () => {
  const value = Number(totalMatches.value);
  
  if (!totalMatches.value) {
    errors.value.totalMatches = "Please enter the total matches";
    return false;
  }
  
  if (isNaN(value) || value <= 0 || !Number.isInteger(value)) {
    errors.value.totalMatches = "Please enter a valid match count (positive whole number)";
    return false;
  }
  
  errors.value.totalMatches = "";
  return true;
};

const validateCurrentWinRate = () => {
  const value = Number(currentWinRate.value);
  
  if (!currentWinRate.value) {
    errors.value.currentWinRate = "Please enter your current win rate";
    return false;
  }
  
  if (isNaN(value) || value < 0 || value > 100) {
    errors.value.currentWinRate = "Please enter a valid win rate (0-100%)";
    return false;
  }
  
  // Check if current win rate is possible with the given matches
  if (validateTotalMatches()) {
    const totalMatchesValue = Number(totalMatches.value);
    const currentWins = Math.round((value / 100) * totalMatchesValue);
    
    if (currentWins > totalMatchesValue) {
      errors.value.currentWinRate = "Win rate is impossible with given matches";
      return false;
    }
  }
  
  errors.value.currentWinRate = "";
  return true;
};

const validateDesiredWinRate = () => {
  const desiredValue = Number(desiredWinRate.value);
  const currentValue = Number(currentWinRate.value);
  
  if (!desiredWinRate.value) {
    errors.value.desiredWinRate = "Please enter your desired win rate";
    return false;
  }
  
  if (isNaN(desiredValue) || desiredValue < 0 || desiredValue > 100) {
    errors.value.desiredWinRate = "Please enter a valid win rate (0-100%)";
    return false;
  }
  
  if (desiredValue <= currentValue) {
    errors.value.desiredWinRate = "Desired win rate must be higher than current";
    return false;
  }
  
  errors.value.desiredWinRate = "";
  return true;
};

// Debounced validation
const debouncedValidation = debounce(() => {
  validateTotalMatches();
  validateCurrentWinRate();
  validateDesiredWinRate();
}, 300);

// Watch for input changes
watch([totalMatches, currentWinRate, desiredWinRate], () => {
  // Reset result when inputs change
  result.value = null;
  resultMessage.value = "";
  
  // Validate with debounce
  debouncedValidation();
});

// Check if all inputs are valid
const isFormValid = computed(() => {
  return (
    validateTotalMatches() && 
    validateCurrentWinRate() && 
    validateDesiredWinRate()
  );
});

// Calculate win rate
const calculateWinRate = () => {
  if (!isFormValid.value) return;
  
  calculating.value = true;
  resultMessage.value = "";
  
  try {
    // Parse values
    const total = Number(totalMatches.value);
    const current = Number(currentWinRate.value);
    const desired = Number(desiredWinRate.value);
    
    // Calculate current wins
    const currentWins = (current / 100) * total;
    
    // Handle edge case when desired win rate is 100%
    if (desired === 100) {
      result.value = "∞"; // Infinity symbol for 100% desired
      resultMessage.value = "Reaching 100% win rate would require winning all future games!";
    } else {
      // Calculate required wins
      const requiredWins = Math.ceil((desired * total - 100 * currentWins) / (100 - desired));
      
      if (requiredWins <= 0) {
        result.value = 0;
        resultMessage.value = "You've already achieved this win rate!";
      } else {
        result.value = requiredWins;
        resultMessage.value = `You need about ${requiredWins} wins without losing to reach a ${desired}% Win Rate.`;
      }
    }
  } catch (e) {
    resultMessage.value = "An error occurred during calculation. Please check your inputs.";
  } finally {
    calculating.value = false;
  }
};

// Reset form
const resetForm = () => {
  totalMatches.value = "";
  currentWinRate.value = "";
  desiredWinRate.value = "";
  result.value = null;
  resultMessage.value = "";
  errors.value = {
    totalMatches: "",
    currentWinRate: "",
    desiredWinRate: "",
  };
};
</script>

<template>
  <Head title="Mobile Legends Win Rate Calculator" />
  
  <GuestLayout>
    <div class="relative py-8 md:py-12">
      <!-- Cosmic Background -->
      <div class="absolute inset-0 overflow-hidden -z-10">
        <div class="absolute inset-0 bg-gradient-to-b from-header_background to-content_background/50"></div>
        <CosmicStars />
      </div>
      
      <div class="container px-4 mx-auto max-w-4xl">
        <!-- Header Section -->
        <div class="mb-8 text-center">
          <h1 class="text-3xl font-bold text-primary md:text-4xl mb-2">
            Mobile Legends Win Rate Calculator
          </h1>
          <p class="text-white/70 max-w-2xl mx-auto">
            Calculate how many consecutive wins you need to achieve your desired win rate percentage in Mobile Legends.
          </p>
        </div>
        
        <!-- Calculator Card -->
        <div class="bg-secondary/10 backdrop-blur-sm rounded-xl shadow-lg border border-primary/20 overflow-hidden">
          <!-- Form Section -->
          <div class="p-6 md:p-8">
            <form @submit.prevent="calculateWinRate" class="space-y-6">
              <!-- Grid Layout for Desktop -->
              <div class="grid gap-6 md:grid-cols-3">
                <!-- Total Matches Input -->
                <div class="space-y-2">
                  <label for="total-matches" class="block text-sm font-medium text-white">
                    Total Matches
                  </label>
                  <div class="relative">
                    <input
                      id="total-matches"
                      v-model="totalMatches"
                      type="text"
                      placeholder="e.g. 100"
                      class="w-full px-4 py-2 bg-secondary/5 border border-primary/30 rounded-lg text-white placeholder-white/30 focus:ring-2 focus:ring-primary/50 focus:border-primary focus:outline-none transition-colors"
                      :class="{'border-red-500': errors.totalMatches}"
                      @blur="validateTotalMatches"
                    />
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-secondary">
                      <span class="text-xs">matches</span>
                    </div>
                  </div>
                  <p v-if="errors.totalMatches" class="mt-1 text-sm text-red-500">
                    {{ errors.totalMatches }}
                  </p>
                </div>
                
                <!-- Current Win Rate Input -->
                <div class="space-y-2">
                  <label for="current-win-rate" class="block text-sm font-medium text-white">
                    Current Win Rate
                  </label>
                  <div class="relative">
                    <input
                      id="current-win-rate"
                      v-model="currentWinRate"
                      type="text"
                      placeholder="e.g. 48.5"
                      class="w-full px-4 py-2 bg-secondary/5 border border-primary/30 rounded-lg text-white placeholder-white/30 focus:ring-2 focus:ring-primary/50 focus:border-primary focus:outline-none transition-colors"
                      :class="{'border-red-500': errors.currentWinRate}"
                      @blur="validateCurrentWinRate"
                    />
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-secondary">
                      <span class="text-xs">%</span>
                    </div>
                  </div>
                  <p v-if="errors.currentWinRate" class="mt-1 text-sm text-red-500">
                    {{ errors.currentWinRate }}
                  </p>
                </div>
                
                <!-- Desired Win Rate Input -->
                <div class="space-y-2">
                  <label for="desired-win-rate" class="block text-sm font-medium text-white">
                    Desired Win Rate
                  </label>
                  <div class="relative">
                    <input
                      id="desired-win-rate"
                      v-model="desiredWinRate"
                      type="text"
                      placeholder="e.g. 60"
                      class="w-full px-4 py-2 bg-secondary/5 border border-primary/30 rounded-lg text-white placeholder-white/30 focus:ring-2 focus:ring-primary/50 focus:border-primary focus:outline-none transition-colors"
                      :class="{'border-red-500': errors.desiredWinRate}"
                      @blur="validateDesiredWinRate"
                    />
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-secondary">
                      <span class="text-xs">%</span>
                    </div>
                  </div>
                  <p v-if="errors.desiredWinRate" class="mt-1 text-sm text-red-500">
                    {{ errors.desiredWinRate }}
                  </p>
                </div>
              </div>
              
              <!-- Action Buttons -->
              <div class="flex flex-col sm:flex-row gap-4 pt-2">
                <button
                  type="submit"
                  class="flex-1 px-6 py-3 font-medium text-white transition-all rounded-lg shadow-lg bg-primary hover:bg-primary-hover disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-glow-primary text-center"
                  :disabled="!isFormValid || calculating"
                >
                  <span v-if="calculating">Calculating...</span>
                  <span v-else>Calculate Required Wins</span>
                </button>
                
                <button
                  type="button"
                  @click="resetForm"
                  class="px-6 py-3 font-medium transition-all border rounded-lg text-secondary border-secondary/30 hover:bg-secondary/10 text-center"
                >
                  Reset
                </button>
              </div>
            </form>
          </div>
          
          <!-- Result Display Section -->
          <div v-if="result !== null || resultMessage" 
               class="p-6 md:p-8 bg-gradient-to-b from-primary/10 to-secondary/5 border-t border-primary/20">
            <div class="text-center">
              <h2 class="text-xl font-semibold text-white mb-2">Result</h2>
              
              <div v-if="result !== null" class="flex items-center justify-center">
                <div class="relative inline-flex items-center justify-center">
                  <!-- Animated ring around the result -->
                  <div class="absolute animate-ping-small rounded-full h-28 w-28 bg-primary/20"></div>
                  <div class="absolute rounded-full h-24 w-24 bg-primary/30 animate-pulse-slow"></div>
                  
                  <!-- Result number -->
                  <div class="relative flex items-center justify-center w-20 h-20 text-4xl font-bold text-white bg-secondary/20 rounded-full z-10 border border-secondary/50">
                    {{ result }}
                  </div>
                </div>
              </div>
              
              <p class="mt-4 text-lg text-primary-text">{{ resultMessage }}</p>
            </div>
            
            <!-- Cosmic decoration -->
            <div class="absolute top-2 right-2 w-20 h-20 opacity-10 pointer-events-none">
              <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" class="text-secondary">
                <path fill="currentColor" d="M37.9,-65.7C47.4,-53.4,52.8,-40,59.4,-27.1C66,-14.2,73.9,-1.9,71.7,8.3C69.6,18.5,57.4,26.6,47.9,36.6C38.5,46.6,31.7,58.6,21.4,64.6C11.1,70.6,-2.8,70.5,-16.9,68.1C-31,65.7,-45.3,60.9,-54.3,51C-63.4,41.2,-67.3,26.1,-70.1,10.6C-72.9,-4.9,-74.8,-20.9,-67.5,-31C-60.2,-41.1,-43.7,-45.3,-30.2,-55.6C-16.7,-65.8,-6.4,-82.1,4.9,-89.6C16.2,-97.1,28.5,-78,37.9,-65.7Z" transform="translate(100 100)" />
              </svg>
            </div>
          </div>
        </div>
        
        <!-- How It Works Section -->
        <div class="mt-10 bg-secondary/10 backdrop-blur-sm rounded-xl p-6 border border-secondary/20">
          <h2 class="mb-4 text-xl font-semibold text-white">How It Works</h2>
          
          <div class="space-y-4 text-white/80">
            <div>
              <h3 class="font-medium text-secondary">The Formula</h3>
              <p class="mt-1">
                The calculator uses the following formula to determine how many consecutive wins you need:
              </p>
              <div class="p-3 mt-2 font-mono text-sm bg-dark-DEFAULT/60 rounded-lg overflow-x-auto">
                <code class="text-primary">
                  currentWins = (currentWinRate / 100) * totalMatches;
                </code>
                <br/>
                <code class="text-secondary">
                  requiredWins = (desiredWinRate * totalMatches - 100 * currentWins) / (100 - desiredWinRate);
                </code>
              </div>
            </div>
            
            <div>
              <h3 class="font-medium text-secondary">Example</h3>
              <p>
                If you have 200 total matches with a current win rate of 49% and want to achieve a 55% win rate:
              </p>
              <ul class="pl-5 mt-1 list-disc">
                <li>Current wins = (49/100) * 200 = 98 wins</li>
                <li>Required wins = (55 * 200 - 100 * 98) / (100 - 55) = (11000 - 9800) / 45 = 1200 / 45 = 26.67 ≈ 27 wins</li>
              </ul>
              <p class="mt-1">
                So you would need 27 consecutive wins without losing to reach a 55% win rate.
              </p>
            </div>
          </div>
        </div>
        
        <!-- Tips Section -->
        <div class="mt-6 text-center text-white/60 text-sm">
          <p>
            Remember that this calculator assumes you won't lose any matches while working toward your desired win rate.
          </p>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>

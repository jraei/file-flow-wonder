
<template>
  <div class="fixed top-4 right-4 z-50 flex flex-col gap-2 w-full max-w-sm">
    <div
      v-for="t in toasts"
      :key="t.id"
      class="bg-dark-card border border-secondary/20 rounded-lg shadow-lg p-4 animate-fade-in flex gap-3 items-start"
      :class="{
        'border-red-500/50': t.variant === 'destructive',
        'border-green-500/50': t.variant === 'success',
      }"
    >
      <div 
        class="rounded-full p-1"
        :class="{
          'bg-primary/10 text-primary': t.variant === 'default',
          'bg-red-500/10 text-red-500': t.variant === 'destructive',
          'bg-green-500/10 text-green-500': t.variant === 'success'
        }"
      >
        <svg 
          v-if="t.variant === 'destructive'"
          xmlns="http://www.w3.org/2000/svg" 
          width="16" 
          height="16" 
          viewBox="0 0 24 24" 
          fill="none" 
          stroke="currentColor" 
          stroke-width="2" 
          stroke-linecap="round" 
          stroke-linejoin="round"
        >
          <circle cx="12" cy="12" r="10" />
          <line x1="12" y1="8" x2="12" y2="12" />
          <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
        <svg 
          v-else-if="t.variant === 'success'"
          xmlns="http://www.w3.org/2000/svg" 
          width="16" 
          height="16" 
          viewBox="0 0 24 24" 
          fill="none" 
          stroke="currentColor" 
          stroke-width="2" 
          stroke-linecap="round" 
          stroke-linejoin="round"
        >
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
          <polyline points="22 4 12 14.01 9 11.01" />
        </svg>
        <svg 
          v-else
          xmlns="http://www.w3.org/2000/svg" 
          width="16" 
          height="16" 
          viewBox="0 0 24 24" 
          fill="none" 
          stroke="currentColor" 
          stroke-width="2" 
          stroke-linecap="round" 
          stroke-linejoin="round"
        >
          <circle cx="12" cy="12" r="10"></circle>
          <path d="M12 8v4"></path>
          <path d="M12 16h.01"></path>
        </svg>
      </div>
      <div class="flex-1">
        <h4 class="text-sm font-medium text-white mb-1">{{ t.title }}</h4>
        <p 
          v-if="t.description" 
          class="text-xs text-gray-400"
        >
          {{ t.description }}
        </p>
      </div>
      <button 
        @click="dismissToast(t.id)" 
        class="text-gray-400 hover:text-white transition-colors"
      >
        <svg 
          xmlns="http://www.w3.org/2000/svg" 
          width="16" 
          height="16" 
          viewBox="0 0 24 24" 
          fill="none" 
          stroke="currentColor" 
          stroke-width="2" 
          stroke-linecap="round" 
          stroke-linejoin="round"
        >
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { useToast } from '@/Composables/useToast';

const { toasts, dismissToast } = useToast();
</script>

<style>
@keyframes fade-in {
  from { 
    opacity: 0; 
    transform: translateY(-1rem);
  }
  to { 
    opacity: 1; 
    transform: translateY(0); 
  }
}

.animate-fade-in {
  animation: fade-in 0.2s ease-out;
}
</style>

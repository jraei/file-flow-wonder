
<template>
  <div class="absolute inset-0 z-10 flex items-center justify-between px-4">
    <button 
      @click="$emit('prev')" 
      class="cyberpunk-button flex items-center justify-center rounded-full bg-dark/30 backdrop-blur-sm h-10 w-10 transition-all border border-primary/30 hover:border-primary/70 group"
      :class="{ 'md:h-12 md:w-12': true }"
    >
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 text-primary/70 group-hover:text-primary group-hover:shadow-glow-primary">
        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 6l-6 6l6 6"/>
      </svg>
    </button>
    
    <button 
      @click="$emit('next')" 
      class="cyberpunk-button flex items-center justify-center rounded-full bg-dark/30 backdrop-blur-sm h-10 w-10 transition-all border border-primary/30 hover:border-primary/70 group"
      :class="{ 'md:h-12 md:w-12': true }"
    >
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 text-primary/70 group-hover:text-primary group-hover:shadow-glow-primary">
        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 6l6 6l-6 6"/>
      </svg>
    </button>
  </div>
</template>

<script setup>
defineEmits(['prev', 'next']);
</script>

<style scoped>
.cyberpunk-button {
  opacity: 0.3;
  transition: opacity 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
}

.cyberpunk-button:hover {
  opacity: 0.7;
  transform: scale(1.1);
  box-shadow: 0 0 15px rgba(155, 135, 245, 0.5);
}
</style>

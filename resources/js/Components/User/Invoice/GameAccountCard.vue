
<template>
  <div class="bg-dark-card/60 border border-secondary/20 rounded-xl shadow-cosmic overflow-hidden">
    <div class="px-4 py-3 border-b border-secondary/20 bg-primary/10">
      <h3 class="text-white font-medium">Informasi Akun</h3>
    </div>
    <div class="flex p-4">
      <div class="mr-5">
        <img :src="icon" :alt="game" class="w-24 h-24 rounded-lg object-cover" />
        <div class="mt-2 text-xs text-center text-gray-300">
          {{ game }}
          <div class="text-xxs text-gray-400">{{ gameDescription }}</div>
        </div>
      </div>
      <div class="flex flex-col space-y-2">
        <template v-if="nickname">
          <div>
            <div class="text-xs text-gray-400">Nickname</div>
            <div class="text-sm text-white">: {{ nickname }}</div>
          </div>
        </template>
        <div>
          <div class="text-xs text-gray-400">ID</div>
          <div class="text-sm text-white">: {{ id }}</div>
        </div>
        <div v-if="server">
          <div class="text-xs text-gray-400">Server</div>
          <div class="text-sm text-white">: {{ server }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  icon: {
    type: String,
    required: true,
  },
  game: {
    type: String,
    required: true,
  },
  gameDescription: {
    type: String,
    default: '',
  },
  nickname: {
    type: String,
    default: null,
  },
  id: {
    type: String,
    required: true,
  },
  server: {
    type: String,
    default: null,
  },
});
</script>

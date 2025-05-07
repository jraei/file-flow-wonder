
<script setup>
import { onMounted, ref } from "vue";

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute("autofocus")) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary focus:ring-primary dark:focus:border-primary dark:focus:ring-primary"
        v-model="model"
        ref="input"
    />
</template>

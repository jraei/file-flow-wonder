<script setup>
import { MessageSquare } from "lucide-vue-next";
import { computed } from "vue";

const props = defineProps({
    waNumber: {
        type: String,
        required: true,
    },
});

const waLink = computed(() => {
    // Format the WhatsApp number for the URL
    const formattedNumber = props.waNumber.replace(/[^0-9]/g, "");
    return `https://wa.me/${formattedNumber}`;
});
</script>

<template>
    <a
        :href="waLink"
        target="_blank"
        rel="noopener noreferrer"
        class="flex items-center gap-3 p-4 text-white transition-all rounded-lg cosmic-help-card bg-gradient-to-br from-primary/20 to-secondary/10 hover:translate-y-[-2px] hover:scale-[1.02] border border-secondary/20"
    >
        <div
            class="flex items-center justify-center w-10 h-10 rounded-full bg-secondary/20 cosmic-icon-container"
        >
            <MessageSquare class="w-5 h-5 text-secondary" />
            <!-- Comet trail effect -->
            <div class="absolute comet-trail"></div>
        </div>

        <div>
            <h4 class="font-bold leading-tight">Need help?</h4>
            <p class="text-sm font-medium text-primary-text/80">
                Contact admin here
            </p>
        </div>
    </a>
</template>

<style scoped>
.cosmic-help-card {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    overflow: hidden;
}

.cosmic-icon-container {
    position: relative;
    overflow: visible;
}

.comet-trail {
    width: 20px;
    height: 2px;
    background: linear-gradient(to left, rgba(51, 195, 240, 0.8), transparent);
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg) translateX(-20px);
    opacity: 0;
    transition: opacity 0.3s ease, transform 0.3s ease;
    z-index: -1;
}

.cosmic-help-card:hover .comet-trail {
    opacity: 1;
    transform: translate(-50%, -50%) rotate(-45deg) translateX(0);
}
</style>

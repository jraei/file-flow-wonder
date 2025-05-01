<template>
    <div class="w-full mb-8">
        <CosmicCard title="Game Description">
            <div class="relative z-10 overflow-hidden">
                <!-- Nebula background effect -->
                <div
                    class="absolute inset-0 z-0 bg-gradient-to-br from-primary/5 to-secondary/10 opacity-30"
                ></div>

                <!-- Shooting stars -->
                <div class="absolute inset-0 z-0 overflow-hidden">
                    <div
                        v-for="i in 3"
                        :key="`star-${i}`"
                        class="absolute h-0.5 w-20 bg-white opacity-0"
                        :style="{
                            top: `${10 + i * 30}%`,
                            left: '-10%',
                            transform: 'rotate(15deg)',
                            animationDelay: `${i * 5}s`,
                        }"
                        :class="`shooting-star-${i}`"
                    ></div>
                </div>

                <!-- Description Content -->
                <div
                    class="relative z-10 prose-sm prose text-gray-200 md:prose-base lg:prose-lg max-w-none"
                    v-html="sanitizedDescription"
                ></div>
            </div>
        </CosmicCard>
    </div>
</template>

<script setup>
import { computed } from "vue";
import CosmicCard from "@/Components/Order/CosmicCard.vue";

const props = defineProps({
    description: {
        type: String,
        default: "",
    },
});

// Basic sanitization - in production you'd use a proper sanitizer library
const sanitizedDescription = computed(() => {
    if (!props.description)
        return "<p>No description available for this product.</p>";

    // Allow only basic HTML tags
    const allowedTags = [
        "p",
        "br",
        "b",
        "i",
        "strong",
        "em",
        "ul",
        "ol",
        "li",
        "h1",
        "h2",
        "h3",
        "h4",
    ];

    // Very simple sanitization (for demo purposes)
    let sanitized = props.description;

    // Replace script tags and other potentially dangerous tags
    sanitized = sanitized.replace(
        /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,
        ""
    );
    sanitized = sanitized.replace(
        /<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi,
        ""
    );

    return sanitized;
});
</script>

<style scoped>
.shooting-star-1 {
    animation: shooting-star 8s linear infinite;
}

.shooting-star-2 {
    animation: shooting-star 12s linear infinite;
}

.shooting-star-3 {
    animation: shooting-star 15s linear infinite;
}

@keyframes shooting-star {
    0% {
        transform: translateX(0) rotate(15deg);
        opacity: 0;
        width: 0;
    }
    5% {
        opacity: 0.8;
        width: 100px;
    }
    20% {
        transform: translateX(120%) rotate(15deg);
        opacity: 0;
    }
    100% {
        transform: translateX(120%) rotate(15deg);
        opacity: 0;
    }
}

/* Override prose defaults for dark theme */
:deep(.prose) {
    color: theme("colors.gray.200");
}

:deep(.prose a) {
    color: theme("colors.secondary.DEFAULT");
}

:deep(.prose strong) {
    color: theme("colors.primary.text");
}

:deep(.prose h1, .prose h2, .prose h3, .prose h4) {
    color: theme("colors.primary.text");
}

:deep(.prose ul li::before) {
    background-color: theme("colors.secondary.DEFAULT");
}

:deep(.prose ol li::before) {
    color: theme("colors.secondary.DEFAULT");
}

:deep(.prose hr) {
    border-color: theme("colors.primary.DEFAULT");
    opacity: 0.2;
}
</style>

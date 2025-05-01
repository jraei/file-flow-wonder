<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import CosmicIcon from "@/Components/User/Navigation/CosmicIcon.vue";

// Import necessary icons from lucide-vue-next
import {
    Instagram,
    Facebook,
    Youtube,
    MessageCircle,
    Mail,
} from "lucide-vue-next";

const props = defineProps({
    socialData: {
        type: Object,
        required: true,
    },
});

// Prepare social links with appropriate icons and URLs
const socialLinks = computed(() => {
    return [
        {
            name: "Instagram",
            url: props.socialData.support_instagram,
            icon: Instagram,
            bgClass:
                "hover:bg-gradient-to-br from-purple-500 via-pink-500 to-orange-400",
            iconClass: "text-white",
        },
        {
            name: "Facebook",
            url: props.socialData.support_facebook,
            icon: Facebook,
            bgClass: "hover:bg-blue-600",
            iconClass: "text-white",
        },
        {
            name: "Youtube",
            url: props.socialData.support_youtube,
            icon: Youtube,
            bgClass: "hover:bg-red-600",
            iconClass: "text-white",
        },
        {
            name: "WhatsApp",
            url: `https://wa.me/${props.socialData.support_whatsapp}`,
            icon: MessageCircle,
            bgClass: "hover:bg-green-500",
            iconClass: "text-white",
        },
        {
            name: "Email",
            url: `mailto:${props.socialData.support_email}`,
            icon: Mail,
            bgClass: "hover:bg-primary",
            iconClass: "text-white",
        },
    ];
});
</script>

<template>
    <div class="flex mt-4 space-x-3">
        <a
            v-for="link in socialLinks"
            :key="link.name"
            :href="link.url"
            target="_blank"
            rel="noopener noreferrer"
            class="relative flex items-center justify-center transition-all duration-300 border rounded-full group w-9 h-9 bg-white/5 border-white/10"
            :class="link.bgClass"
        >
            <!-- Social icon -->
            <component
                :is="link.icon"
                :size="20"
                class="transition-all duration-300"
                :class="link.iconClass"
            />

            <!-- Orbiting mini-planet - appears on hover -->
            <!-- <div
                class="absolute w-2 h-2 transition-opacity duration-300 rounded-full opacity-0 bg-secondary group-hover:opacity-100"
                style="
                    animation: orbit 3s linear infinite;
                    transform-origin: center;
                "
            ></div> -->
        </a>
    </div>
</template>

<style scoped>
@keyframes orbit {
    from {
        transform: rotate(0deg) translateX(15px) rotate(0deg);
    }
    to {
        transform: rotate(360deg) translateX(15px) rotate(-360deg);
    }
}
</style>

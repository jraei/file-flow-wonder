
<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    links: {
        type: Array,
        required: true
    }
});
</script>

<template>
    <ul class="space-y-2">
        <li v-for="(link, index) in links" :key="`nav-${index}`">
            <Link 
                :href="route(link.route)" 
                class="inline-block text-white/70 hover:text-primary transition-all duration-200 group relative"
            >
                <span class="relative z-10">{{ link.name }}</span>
                
                <!-- Hover effects -->
                <span 
                    class="absolute inset-0 z-0 bg-primary/0 group-hover:bg-primary/5 rounded transform scale-75 opacity-0 group-hover:opacity-100 group-hover:scale-100 transition-all duration-300"
                ></span>
                
                <!-- Star dots decorative element -->
                <span 
                    class="absolute -top-1 -right-2 w-1 h-1 bg-primary rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                ></span>
            </Link>
        </li>
    </ul>
</template>

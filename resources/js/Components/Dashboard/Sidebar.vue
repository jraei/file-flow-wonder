<script setup>
import { ref, computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

// Lucide icon names allowed
import {
    Compass,
    BanknoteArrowUp,
    CreditCard,
    Handshake,
    CircleDollarSign,
} from "lucide-vue-next";
const props = defineProps({
    collapsed: Boolean,
});
const emit = defineEmits(["toggle"]);

// Route list for navigation
const navItems = [
    {
        name: "Dashboard",
        route: "dashboard",
        icon: Compass,
    },
    {
        name: "Transaksi",
        route: "dashboard.transactions",
        icon: BanknoteArrowUp,
    },
    {
        name: "Mutasi",
        route: "dashboard.mutations",
        icon: CreditCard,
    },
    {
        name: "Afiliasi",
        route: "dashboard.affiliate",
        icon: Handshake,
    },
];

const page = usePage();
const currentRoute = computed(() => page.url);
</script>

<template>
    <aside
        :class="[
            'hidden lg:flex flex-col bg-transparent min-h-screen  z-30 transition-all duration-300 mt-5',
            props.collapsed ? 'w-20' : 'w-56',
        ]"
    >
        <!-- VSCredits Logo Box -->
        <!-- Nav -->
        <nav class="flex-1 mt-3 space-y-3">
            <div>
                <Link
                    :href="route('dashboard.balance')"
                    :class="[
                        'flex items-center px-4 py-2 rounded-lg transition-all font-medium gap-3 group',
                        route().current('dashboard.balance') ||
                        route().current('dashboard.topup')
                            ? 'bg-gradient-to-r from-primary/50 to-secondary/40 text-white ring-2 ring-primary/70 animate-pulse'
                            : 'text-primary-text/80 hover:bg-primary/10 hover:text-secondary',
                    ]"
                >
                    <!-- <img src="/favicon.ico" class="w-7 h-7" /> -->
                    <component
                        :is="CircleDollarSign"
                        class="w-6 h-6"
                        aria-hidden="true"
                    />
                    <span v-if="!props.collapsed" class="text-base"
                        >NaelCoin</span
                    >
                </Link>
            </div>
            <div v-for="item in navItems" :key="item.name">
                <Link
                    :href="route(item.route)"
                    :class="[
                        'flex items-center px-4 py-2 rounded-lg transition-all font-medium gap-3 group',
                        route().current(item.route)
                            ? 'bg-gradient-to-r from-primary/50 to-secondary/40 text-white ring-2 ring-primary/70 animate-pulse'
                            : 'text-primary-text/80 hover:bg-primary/10 hover:text-secondary',
                    ]"
                >
                    <component
                        :is="item.icon"
                        class="w-6 h-6"
                        aria-hidden="true"
                    />
                    <span v-if="!props.collapsed" class="text-base">{{
                        item.name
                    }}</span>
                </Link>
            </div>
        </nav>
        <!-- Collapse Button -->
        <button
            class="p-2 mx-auto my-4 transition bg-none text-secondary hover:text-primary"
            @click="$emit('toggle')"
            aria-label="Toggle sidebar"
        >
            <svg
                :class="['w-6 h-6', props.collapsed ? 'rotate-180' : '']"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
            >
                <path
                    d="M9 18l6-6-6-6"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </button>
    </aside>
</template>

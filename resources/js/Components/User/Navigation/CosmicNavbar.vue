<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import DesktopTierOne from "./DesktopTierOne.vue";
import DesktopTierTwo from "./DesktopTierTwo.vue";
import MobileBreadcrumbs from "./MobileBreadcrumbs.vue";
import CosmicStarfield from "./CosmicStarfield.vue";
import { usePage } from "@inertiajs/vue3";
import { LogOut, Compass } from "lucide-vue-next";

const page = usePage();
const user = page.props.auth.user;
const userRole = page.props.auth.role;

const isMobileMenuOpen = ref(false);
const isScrolled = ref(false);

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;

    // Disable body scroll when mobile menu is open
    if (isMobileMenuOpen.value) {
        document.body.style.overflow = "hidden";
    } else {
        document.body.style.overflow = "";
    }
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
    document.body.style.overflow = "";
};

const checkScroll = () => {
    isScrolled.value = window.scrollY > 20;
};

onMounted(() => {
    window.addEventListener("scroll", checkScroll);
    checkScroll(); // Initial check
});

onUnmounted(() => {
    window.removeEventListener("scroll", checkScroll);
});

const navLinks = [
    {
        name: "Topup",
        icon: "🌌",
        route: "index",
        active: false,
    },
    {
        name: "Cek Transaksi",
        icon: "📊",
        route: "cek-transaksi",
        active: false,
    },
    {
        name: "Leaderboard",
        icon: "🏆",
        route: "leaderboard",
        active: false,
    },
    {
        name: "Kalkulator",
        icon: "🧮",
        route: "dashboard",
        active: false,
        dropdown: [
            { name: "Winrate", icon: "🌠", route: "calculator.winrate" },
            {
                name: "Magic Wheel",
                icon: "🎡",
                route: "calculator.magic-wheel",
            },
            { name: "Zodiac", icon: "♈️", route: "calculator.zodiac" },
        ],
    },
];
</script>

<template>
    <header
        class="sticky top-0 z-50 w-full transition-all duration-300"
        :class="{ 'shadow-md backdrop-blur-md': isScrolled }"
    >
        <div class="relative">
            <!-- Cosmic background with starfield -->
            <div class="absolute inset-0 overflow-hidden">
                <div
                    class="absolute inset-0 bg-gradient-to-b from-header_background to-content_background/50"
                ></div>
                <CosmicStarfield class="opacity-50" />
            </div>

            <!-- Main navigation container -->
            <div class="relative z-10">
                <!-- Desktop Two-Tier Navigation (md and above) -->
                <div class="hidden md:block">
                    <DesktopTierOne :is-scrolled="isScrolled" />
                    <DesktopTierTwo :nav-links="navLinks" :user :userRole />
                </div>

                <!-- Mobile Navigation (below md) -->
                <div class="block md:hidden">
                    <MobileBreadcrumbs
                        :is-open="isMobileMenuOpen"
                        :toggle-menu="toggleMobileMenu"
                        :close-menu="closeMobileMenu"
                        :nav-links="navLinks"
                        :user
                        :userRole
                    />
                </div>
            </div>
        </div>
    </header>
</template>

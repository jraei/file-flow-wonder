<script setup>
import { ref, computed, resolveComponent } from "vue";
import { Link } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import ProductSearch from "./ProductSearch.vue";
import CosmicIcon from "./CosmicIcon.vue";
import {
    Compass,
    LogOut,
    BanknoteArrowUp,
    UserRoundCog,
} from "lucide-vue-next";

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    toggleMenu: {
        type: Function,
        required: true,
    },
    closeMenu: {
        type: Function,
        required: true,
    },
    navLinks: {
        type: Array,
        required: true,
    },
    authenticatedLinks: {
        type: Array,
    },
    user: {
        type: Object,
    },
    userRole: {
        type: String,
    },
});

const showSearch = ref(false);
const isSearchFocused = ref(false);
const expandedItems = ref([]);

const isAuthenticated = computed(() => {
    return !!props.user;
});

const toggleSearch = () => {
    showSearch.value = !showSearch.value;
};

const toggleExpand = (index) => {
    if (expandedItems.value.includes(index)) {
        expandedItems.value = expandedItems.value.filter((i) => i !== index);
    } else {
        expandedItems.value.push(index);
    }
};

// Function to get icon name from emoji
const getIconName = (emojiName) => {
    const iconMappings = {
        "🌌": "topup",
        "📊": "transaction",
        "🏆": "leaderboard",
        "🧮": "calculator",
        "🌠": "winrate",
        "🎡": "magicwheel",
        "♈️": "zodiac",
    };

    return iconMappings[emojiName] || "default";
};

const isAdmin = computed(() => {
    return props.userRole === "admin";
});
</script>

<template>
    <div>
        <!-- Mobile Header -->
        <div class="px-4 py-3 mx-auto max-w-7xl">
            <div class="flex items-center justify-between">
                <!-- Left: Logo -->
                <div class="flex items-center flex-shrink-0">
                    <Link :href="route('dashboard')">
                        <ApplicationLogo class="w-auto h-8 text-primary" />
                    </Link>
                </div>

                <!-- Right: Controls -->
                <div class="flex items-center space-x-4">
                    <!-- Search Icon -->
                    <button
                        class="flex items-center justify-center w-10 h-10 text-gray-300 transition-all rounded-full hover:bg-primary/10"
                        @click="toggleSearch"
                    >
                        <CosmicIcon name="search" size="md" />
                    </button>

                    <!-- Language -->
                    <button class="flex items-center text-lg text-secondary">
                        🇮🇩
                    </button>

                    <!-- Menu Toggle (Hamburger) -->
                    <button
                        class="relative flex items-center justify-center w-10 h-10 text-gray-300 transition-all rounded-full hover:bg-primary/10"
                        @click="toggleMenu"
                    >
                        <div
                            v-if="!isOpen"
                            class="flex flex-col items-end space-y-1.5"
                        >
                            <div
                                class="w-6 h-0.5 bg-gray-300 rounded-full"
                            ></div>
                            <div
                                class="w-4 h-0.5 bg-gray-300 rounded-full"
                            ></div>
                            <div
                                class="w-5 h-0.5 bg-gray-300 rounded-full"
                            ></div>
                        </div>
                        <svg
                            v-else
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-6 h-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>

                        <!-- Cosmic glow effect -->
                        <div
                            class="absolute inset-0 transition-opacity rounded-full opacity-0 bg-primary/20 filter blur-sm hover:opacity-100"
                        ></div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Search Bar (when activated) -->
        <div
            v-if="showSearch"
            class="fixed inset-x-0 top-0 z-50 p-4 transition-all transform bg-primary/30"
            :class="{ 'shadow-glow-primary': isSearchFocused }"
        >
            <div class="flex items-center">
                <button class="mr-3 text-gray-300" @click="toggleSearch">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-6 h-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                        />
                    </svg>
                </button>
                <div class="flex-1">
                    <ProductSearch
                        @focus="isSearchFocused = true"
                        @blur="isSearchFocused = false"
                        :is-focused="isSearchFocused"
                    />
                </div>
            </div>
        </div>

        <!-- Mobile Menu (Breadcrumb Panel) -->
        <div v-if="isOpen" class="fixed inset-0 z-40 flex">
            <!-- Backdrop -->
            <div
                class="fixed inset-0 transition-opacity duration-300 bg-black/60 backdrop-blur-sm"
                @click="closeMenu"
            ></div>

            <!-- Slide-in Panel -->
            <div
                class="relative flex flex-col w-4/5 h-full max-w-sm overflow-y-auto transition-all duration-300 transform bg-gradient-to-br from-primary/50 to-bg-content_background backdrop-blur-md"
            >
                <!-- Panel Header -->
                <div
                    class="flex items-center justify-between p-4 border-b border-primary/20"
                >
                    <Link :href="route('dashboard')" @click="closeMenu">
                        <ApplicationLogo class="w-auto h-8 text-primary" />
                    </Link>
                    <button
                        class="flex items-center justify-center w-10 h-10 text-gray-300 rounded-full hover:bg-primary/10"
                        @click="closeMenu"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-6 h-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links -->
                <div class="flex-1 px-2 py-4 space-y-1">
                    <template v-for="(link, index) in navLinks" :key="index">
                        <!-- Regular Link or Dropdown Trigger -->
                        <div
                            v-if="link.dropdown"
                            class="relative"
                            :key="`dropdown-${index}`"
                        >
                            <button
                                class="flex items-center justify-between w-full px-4 py-3 text-gray-200 transition-all rounded-md hover:bg-primary/10 hover:text-primary"
                                :class="{
                                    'bg-primary/5 text-primary': link.active,
                                }"
                                @click="toggleExpand(index)"
                            >
                                <div class="flex items-center">
                                    <CosmicIcon
                                        :name="getIconName(link.icon)"
                                        size="md"
                                        className="mr-3"
                                    />
                                    <span class="font-medium">{{
                                        link.name
                                    }}</span>
                                </div>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-5 h-5 transition-transform duration-300"
                                    :class="{
                                        'rotate-180':
                                            expandedItems.includes(index),
                                    }"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 9l-7 7-7-7"
                                    />
                                </svg>
                            </button>

                            <!-- Dropdown Items (Accordion) -->
                            <div
                                v-if="expandedItems.includes(index)"
                                class="mt-1 mb-1 overflow-hidden transition-all duration-300 rounded-md bg-primary/5"
                            >
                                <Link
                                    v-for="(item, itemIndex) in link.dropdown"
                                    :key="`${index}-${itemIndex}`"
                                    :href="route(item.route)"
                                    class="flex items-center py-3 pl-12 pr-4 text-gray-200 transition-all hover:bg-primary/10 hover:text-primary"
                                    @click="closeMenu"
                                >
                                    <CosmicIcon
                                        :name="getIconName(item.icon)"
                                        size="md"
                                        className="mr-2 text-primary-text/70"
                                    />
                                    <div>
                                        <p class="font-medium">
                                            {{ item.name }}
                                        </p>
                                        <p class="text-xs text-primary-text/60">
                                            {{
                                                item.name === "Winrate"
                                                    ? "Calculate matches needed"
                                                    : item.name ===
                                                      "Magic Wheel"
                                                    ? "Estimate diamond cost"
                                                    : "Calculate skin probability"
                                            }}
                                        </p>
                                    </div>
                                </Link>
                            </div>
                        </div>

                        <!-- Regular Link (No Dropdown) -->
                        <Link
                            v-else
                            :href="route(link.route)"
                            class="flex items-center px-4 py-3 text-gray-200 transition-all rounded-md hover:bg-primary/10 hover:text-primary"
                            :class="{
                                'bg-primary/5 text-primary': route().current(
                                    link.route
                                ),
                            }"
                            @click="closeMenu"
                        >
                            <CosmicIcon
                                :name="getIconName(link.icon)"
                                size="md"
                                className="mr-3"
                            />
                            <span class="font-medium">{{ link.name }}</span>
                        </Link>

                        <!-- authenticated links -->
                    </template>
                    <div
                        v-if="isAuthenticated"
                        class="pt-2 border-t border-primary/20"
                    >
                        <Link
                            :href="route('dashboard')"
                            class="flex items-center px-4 py-3 text-gray-200 transition-all rounded-md hover:bg-primary/10 hover:text-primary"
                            :class="{
                                'bg-primary/5 text-primary':
                                    route().current('dashboard'),
                            }"
                            @click="closeMenu"
                        >
                            <Compass class="w-5 h-5 mr-3" />

                            <span class="font-medium">Dashboard</span>
                        </Link>
                        <Link
                            :href="route('dashboard.transactions')"
                            class="flex items-center px-4 py-3 text-gray-200 transition-all rounded-md hover:bg-primary/10 hover:text-primary"
                            :class="{
                                'bg-primary/5 text-primary':
                                    route().current('dashboard'),
                            }"
                            @click="closeMenu"
                        >
                            <BanknoteArrowUp class="w-5 h-5 mr-3" />

                            <span class="font-medium">Riwayat Transaksi</span>
                        </Link>
                    </div>
                </div>

                <!-- Auth Buttons -->
                <div
                    class="p-4 border-t border-primary/20"
                    v-if="!isAuthenticated"
                >
                    <div class="grid grid-cols-2 gap-3">
                        <Link
                            :href="route('login')"
                            class="flex items-center justify-center px-4 py-2 text-sm font-medium text-center text-gray-200 transition-all rounded-md bg-dark/card hover:bg-primary/20 hover:text-white"
                            @click="closeMenu"
                        >
                            <span class="mr-1.5">
                                <CosmicIcon name="login" size="md" />
                            </span>
                            <span>Login</span>
                        </Link>

                        <Link
                            :href="route('register')"
                            class="flex items-center justify-center px-4 py-2 text-sm font-medium text-center text-white transition-all rounded-md bg-primary hover:bg-primary-hover"
                            @click="closeMenu"
                        >
                            <span class="mr-1.5">
                                <CosmicIcon name="register" size="md" />
                            </span>
                            <span>Register</span>
                        </Link>
                    </div>
                </div>

                <div
                    class="p-4 border-t border-primary/20"
                    v-if="isAuthenticated"
                >
                    <div class="grid grid-cols-2 gap-3">
                        <Link
                            :href="route('admin.dashboard')"
                            class="flex items-center justify-center px-4 py-2 text-sm font-medium text-center text-white transition-all rounded-md bg-primary hover:bg-primary-hover"
                            @click="closeMenu"
                        >
                            <span class="mr-1.5">
                                <LogOut class="w-5 h-5" />
                            </span>
                            <span>Logout</span>
                        </Link>
                        <Link
                            :href="route('admin.dashboard')"
                            class="flex items-center justify-center px-4 py-2 text-sm font-medium text-center text-white transition-all rounded-md bg-primary hover:bg-primary-hover"
                            @click="closeMenu"
                            v-if="isAdmin"
                        >
                            <span class="mr-1.5">
                                <UserRoundCog class="w-5 h-5" />
                            </span>
                            <span>Admin</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Link } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";
import CosmicIcon from "./CosmicIcon.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

const props = defineProps({
    navLinks: {
        type: Array,
        required: true,
    },
    user: {
        type: Object,
    },
    userRole: {
        type: String,
    },
});

const isAuthenticated = computed(() => {
    return !!props.user;
});

// check is user admin
const isAdmin = computed(() => {
    return props.userRole === "admin";
});

const activeDropdown = ref(null);
const dropdownRefs = ref([]);

const toggleDropdown = (index) => {
    if (activeDropdown.value === index) {
        activeDropdown.value = null;
    } else {
        activeDropdown.value = index;
    }
};

const closeDropdown = () => {
    activeDropdown.value = null;
};

// Handle clicks outside the dropdown to close it
const handleClickOutside = (event) => {
    if (
        activeDropdown.value !== null &&
        dropdownRefs.value[activeDropdown.value] &&
        !dropdownRefs.value[activeDropdown.value].contains(event.target)
    ) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener("mousedown", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("mousedown", handleClickOutside);
});

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
</script>

<template>
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14">
            <!-- Left: Navigation Links -->
            <div class="flex space-x-4">
                <div
                    v-for="(link, index) in navLinks"
                    :key="index"
                    class="relative"
                    :ref="
                        (el) => {
                            if (el) dropdownRefs[index] = el;
                        }
                    "
                    @mouseleave="closeDropdown"
                >
                    <button
                        v-if="link.dropdown"
                        @click="toggleDropdown(index)"
                        @mouseenter="toggleDropdown(index)"
                        class="flex items-center px-3 py-2 space-x-1 text-sm text-gray-200 transition-all rounded-md group hover:bg-primary/10 hover:text-primary"
                        :class="{
                            'text-primary bg-primary/5':
                                link.active || activeDropdown === index,
                        }"
                    >
                        <CosmicIcon
                            :name="getIconName(link.icon)"
                            size="md"
                            className="mr-1.5"
                        />
                        <span>{{ link.name }}</span>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4 transition-transform duration-200"
                            :class="{ 'rotate-180': activeDropdown === index }"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd"
                            />
                        </svg>

                        <!-- Hover effect: orbiting planets -->
                        <div
                            class="absolute inset-0 transition-opacity duration-300 opacity-0 -z-10 group-hover:opacity-100"
                        >
                            <div
                                class="absolute w-1 h-1 rounded-full top-1 right-1 bg-primary animate-pulse-slow"
                            ></div>
                            <div
                                class="absolute bottom-1 left-2 w-1.5 h-1.5 bg-secondary rounded-full animate-ping"
                            ></div>
                        </div>
                    </button>

                    <NavLink
                        v-else
                        :href="route(link.route)"
                        :active="link.active"
                        class="relative flex items-center px-3 py-2 space-x-1 text-sm text-gray-200 transition-all rounded-md group hover:bg-primary/10 hover:text-primary"
                    >
                        <CosmicIcon
                            :name="getIconName(link.icon)"
                            size="md"
                            className="mr-1.5"
                        />
                        <span>{{ link.name }}</span>

                        <!-- Hover effect: orbiting planets -->
                        <div
                            class="absolute inset-0 transition-opacity duration-300 opacity-0 -z-10 group-hover:opacity-100"
                        >
                            <div
                                class="absolute w-1 h-1 rounded-full top-1 right-1 bg-primary animate-pulse-slow"
                            ></div>
                            <div
                                class="absolute bottom-1 left-2 w-1.5 h-1.5 bg-secondary rounded-full animate-ping"
                            ></div>
                        </div>
                    </NavLink>

                    <!-- Enhanced Dropdown menu with fixed position -->
                    <div
                        v-if="link.dropdown && activeDropdown === index"
                        class="fixed z-50 w-64 mt-1 transition-all duration-300 origin-top-right"
                        :style="{
                            top: `${
                                dropdownRefs[index]?.getBoundingClientRect()
                                    .bottom + window.scrollY
                            }px`,
                            left: `${
                                dropdownRefs[index]?.getBoundingClientRect()
                                    .left
                            }px`,
                        }"
                    >
                        <div
                            class="overflow-hidden border rounded-md bg-gradient-to-b from-content_background to-content_background/90 backdrop-blur-sm shadow-glow-primary border-primary/30"
                        >
                            <!-- Constellation Background (Cosmetic Enhancement) -->
                            <div
                                class="absolute inset-0 overflow-hidden pointer-events-none opacity-5"
                            >
                                <div
                                    class="absolute w-1 h-1 rounded-full top-[10%] left-[20%] bg-white"
                                ></div>
                                <div
                                    class="absolute w-1 h-1 rounded-full top-[15%] left-[22%] bg-white"
                                ></div>
                                <div
                                    class="absolute w-1 h-1 rounded-full top-[20%] left-[25%] bg-white"
                                ></div>
                                <div
                                    class="absolute w-1 h-1 rounded-full top-[30%] left-[40%] bg-white"
                                ></div>
                                <div
                                    class="absolute w-1 h-1 rounded-full top-[70%] left-[80%] bg-white"
                                ></div>
                                <div
                                    class="absolute w-1 h-1 rounded-full top-[60%] left-[70%] bg-white"
                                ></div>
                                <div
                                    class="absolute w-1 h-1 rounded-full top-[50%] left-[60%] bg-white"
                                ></div>
                                <div
                                    class="absolute w-1.5 h-1.5 rounded-full top-[40%] left-[30%] bg-white"
                                ></div>
                            </div>

                            <div class="py-2">
                                <Link
                                    v-for="(item, itemIndex) in link.dropdown"
                                    :key="itemIndex"
                                    :href="route(item.route)"
                                    class="block p-3 transition-all hover:bg-primary/10 group"
                                >
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="flex-shrink-0 p-1.5 bg-primary/10 rounded-full transition-colors group-hover:bg-primary/20"
                                        >
                                            <CosmicIcon
                                                :name="getIconName(item.icon)"
                                                size="md"
                                                className="text-primary"
                                            />
                                        </div>
                                        <div class="flex-1">
                                            <p
                                                class="font-medium text-primary-text"
                                            >
                                                {{ item.name }}
                                            </p>
                                            <p
                                                class="mt-0.5 text-xs text-primary-text/60"
                                            >
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

                                        <!-- Orbiting Planet (Cosmetic Enhancement) -->
                                        <div
                                            class="relative w-6 h-6 transition-opacity opacity-0 group-hover:opacity-100"
                                        >
                                            <div
                                                class="absolute inset-0 w-1.5 h-1.5 bg-secondary rounded-full animate-ping"
                                            ></div>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Auth Buttons -->
            <div class="flex items-center space-x-3">
                <template v-if="!isAuthenticated">
                    <Link
                        :href="route('login')"
                        class="flex items-center px-4 py-2 text-sm font-medium text-gray-200 transition-all rounded-full bg-dark/card hover:bg-primary-hover/50 hover:text-white"
                    >
                        <span class="mr-1.5">
                            <CosmicIcon name="login" size="md" />
                        </span>
                        <span>Login</span>
                    </Link>

                    <Link
                        :href="route('register')"
                        class="flex items-center px-4 py-2 text-sm font-medium text-white transition-all bg-transparent rounded-full hover:bg-primary-hover/50"
                    >
                        <span class="mr-1.5">
                            <CosmicIcon name="register" size="md" />
                        </span>
                        <span>Register</span>
                    </Link>
                </template>

                <template v-else>
                    <div class="relative ms-3">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <span class="inline-flex rounded-md">
                                    <button
                                        type="button"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out border border-transparent rounded-md text-primary-text/80 hover:text-primary focus:outline-none"
                                    >
                                        {{ props.user.username }}

                                        <svg
                                            class="ms-2 -me-0.5 h-4 w-4"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </span>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')">
                                    Profile
                                </DropdownLink>
                                <DropdownLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                >
                                    Log Out
                                </DropdownLink>
                                <DropdownLink :href="route('dashboard')">
                                    Dashboard
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                    <Link
                        :href="route('admin.dashboard')"
                        class="flex items-center px-4 py-2 space-x-2 text-sm font-medium transition-all bg-transparent rounded-full text-white/90 hover:bg-primary-hover/50"
                        v-if="isAdmin"
                    >
                        <span>Admin</span>
                        <span class="mr-1.5">
                            <CosmicIcon name="login" size="md" />
                        </span>
                    </Link>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import Dropdown from "@/Components/Dropdown.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import Swal from "../Components/Swal.vue";

const showingNavigationDropdown = ref(false);
const isSidebarCollapsed = ref(false);
const isMobileMenuOpen = ref(false);
const dropdowns = ref({
    products: false,
    users: false,
    settings: false,
    promotions: false,
    produkInput: false,
});

const props = defineProps({
    title: {
        type: String,
        default: null,
    },
});

const page = usePage();
const judulWeb = computed(() => page.props.web_details.judul_web);
const user = computed(() => page.props.auth.user);
const logoHeader = page.props.web_details.logo_header;

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
};

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const toggleDropdown = (dropdown) => {
    dropdowns.value[dropdown] = !dropdowns.value[dropdown];
};
</script>

<template>
    <div class="flex min-h-screen text-white bg-dark-sidebar">
        <!-- Floating Sidebar - Desktop -->
        <div
            :class="[
                'fixed top-0 left-0 h-full z-30 transform transition-all duration-300 ease-in-out',
                isSidebarCollapsed ? 'w-20' : 'w-64',
                'hidden lg:block',
            ]"
        >
            <div
                class="flex flex-col h-full px-2 py-6 bg-dark-sidebar rounded-r-xl shadow-float"
            >
                <!-- Logo section -->
                <div class="flex items-center justify-center px-4 mb-8">
                    <Link :href="route('index')">
                        <img :src="logoHeader" alt="logo" class="w-12 h-12" />
                    </Link>
                    <span
                        v-if="!isSidebarCollapsed"
                        class="ml-3 text-xl font-bold text-transparent bg-gradient-to-r from-primary to-secondary bg-clip-text"
                    >
                        {{ judulWeb }}
                    </span>
                </div>

                <!-- Navigation Links -->
                <div
                    class="flex-1 px-2 space-y-1 overflow-y-auto scrollbar-hide"
                >
                    <!-- Dashboard -->
                    <Link
                        :href="route('admin.dashboard')"
                        :class="[
                            'flex items-center rounded-lg p-3 text-base font-medium transition-all duration-200',
                            route().current('admin.dashboard')
                                ? 'bg-primary text-white shadow-glow-primary'
                                : 'text-gray-300 hover:bg-dark-lighter hover:text-white',
                        ]"
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
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
                            />
                        </svg>
                        <span v-if="!isSidebarCollapsed" class="ml-3"
                            >Dashboard</span
                        >
                    </Link>

                    <!-- Banners & Item Thumbnail Dropdown -->
                    <div>
                        <button
                            @click="toggleDropdown('images')"
                            :class="[
                                'w-full flex items-center justify-between rounded-lg p-3 text-base font-medium transition-all duration-200',
                                route().current('banners.index') ||
                                route().current('item-thumbnails.index')
                                    ? 'bg-primary text-white shadow-glow-primary'
                                    : 'text-gray-300 hover:bg-dark-lighter hover:text-white',
                            ]"
                        >
                            <div class="flex items-center">
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
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                                <span v-if="!isSidebarCollapsed" class="ml-3"
                                    >Images</span
                                >
                            </div>
                            <svg
                                v-if="!isSidebarCollapsed"
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 ml-2 transition-transform duration-200"
                                :class="{ 'rotate-180': dropdowns.images }"
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

                        <div
                            v-if="!isSidebarCollapsed && dropdowns.images"
                            class="pl-6 mt-1 space-y-1 transition-all duration-200"
                        >
                            <Link
                                :href="route('banners.index')"
                                :class="[
                                    'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                    route().current('banners.index')
                                        ? 'text-primary'
                                        : 'text-gray-300 hover:text-white',
                                ]"
                            >
                                <span>Banners</span>
                            </Link>
                            <Link
                                :href="route('item-thumbnails.index')"
                                :class="[
                                    'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                    route().current('item-thumbnails.index')
                                        ? 'text-primary'
                                        : 'text-gray-300 hover:text-white',
                                ]"
                            >
                                <span>Items Thumbnail</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Products & Services Dropdown -->
                    <div>
                        <button
                            @click="toggleDropdown('products')"
                            :class="[
                                'w-full flex items-center justify-between rounded-lg p-3 text-base font-medium transition-all duration-200',
                                route().current('categories.index') ||
                                route().current('products.index') ||
                                route().current('services.index')
                                    ? 'bg-primary text-white shadow-glow-primary'
                                    : 'text-gray-300 hover:bg-dark-lighter hover:text-white',
                            ]"
                        >
                            <div class="flex items-center">
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
                                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                                    />
                                </svg>
                                <span v-if="!isSidebarCollapsed" class="ml-3"
                                    >Products</span
                                >
                            </div>
                            <svg
                                v-if="!isSidebarCollapsed"
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 ml-2 transition-transform duration-200"
                                :class="{ 'rotate-180': dropdowns.products }"
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

                        <div
                            v-if="!isSidebarCollapsed && dropdowns.products"
                            class="pl-6 mt-1 space-y-1 transition-all duration-200"
                        >
                            <Link
                                :href="route('categories.index')"
                                :class="[
                                    'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                    route().current('categories.index')
                                        ? 'text-primary'
                                        : 'text-gray-300 hover:text-white',
                                ]"
                            >
                                <span>Categories</span>
                            </Link>
                            <Link
                                :href="route('products.index')"
                                :class="[
                                    'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                    route().current('products.index')
                                        ? 'text-primary'
                                        : 'text-gray-300 hover:text-white',
                                ]"
                            >
                                <span>Products</span>
                            </Link>
                            <Link
                                :href="route('services.index')"
                                :class="[
                                    'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                    route().current('services.index')
                                        ? 'text-primary'
                                        : 'text-gray-300 hover:text-white',
                                ]"
                            >
                                <span>Services</span>
                            </Link>
                            <Link
                                :href="route('profit-produks.index')"
                                :class="[
                                    'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                    route().current('profit-produks.index')
                                        ? 'text-primary'
                                        : 'text-gray-300 hover:text-white',
                                ]"
                            >
                                <span>Profit Settings</span>
                            </Link>
                        </div>
                    </div>
                    <!-- Produk Input Option Fields Dropdown -->
                    <div>
                        <button
                            @click="toggleDropdown('produkInput')"
                            :class="[
                                'w-full flex items-center justify-between rounded-lg p-3 text-base font-medium transition-all duration-200',
                                route().current(
                                    'admin.produk-input-fields.index'
                                ) ||
                                route().current(
                                    'admin.produk-input-options.index'
                                )
                                    ? 'bg-primary text-white shadow-glow-primary'
                                    : 'text-gray-300 hover:bg-dark-lighter hover:text-white',
                            ]"
                        >
                            <div class="flex items-center">
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
                                        d="M4 6h16M4 10h16M4 14h10M4 18h6"
                                    />
                                </svg>

                                <span v-if="!isSidebarCollapsed" class="ml-3"
                                    >Custom Input</span
                                >
                            </div>
                            <svg
                                v-if="!isSidebarCollapsed"
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 ml-2 transition-transform duration-200"
                                :class="{
                                    'rotate-180': dropdowns.produkInput,
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

                        <div
                            v-if="!isSidebarCollapsed && dropdowns.produkInput"
                            class="pl-6 mt-1 space-y-1 transition-all duration-200"
                        >
                            <Link
                                :href="route('admin.produk-input-fields.index')"
                                :class="[
                                    'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                    route().current(
                                        'admin.produk-input-fields.index'
                                    )
                                        ? 'text-primary'
                                        : 'text-gray-300 hover:text-white',
                                ]"
                            >
                                <span>Input Fields</span>
                            </Link>
                            <Link
                                :href="
                                    route('admin.produk-input-options.index')
                                "
                                :class="[
                                    'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                    route().current(
                                        'admin.produk-input-options.index'
                                    )
                                        ? 'text-primary'
                                        : 'text-gray-300 hover:text-white',
                                ]"
                            >
                                <span>Input Options</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Payments Dropdown -->
                    <div>
                        <button
                            @click="toggleDropdown('payments')"
                            :class="[
                                'w-full flex items-center justify-between rounded-lg p-3 text-base font-medium transition-all duration-200',
                                route().current('admin.payment-providers') ||
                                route().current('pay-methods.index') ||
                                route().current('deposits.index')
                                    ? 'bg-primary text-white shadow-glow-primary'
                                    : 'text-gray-300 hover:bg-dark-lighter hover:text-white',
                            ]"
                        >
                            <div class="flex items-center">
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
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                    />
                                </svg>
                                <span v-if="!isSidebarCollapsed" class="ml-3"
                                    >Payments</span
                                >
                            </div>
                            <svg
                                v-if="!isSidebarCollapsed"
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 ml-2 transition-transform duration-200"
                                :class="{ 'rotate-180': dropdowns.payments }"
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

                        <div
                            v-if="!isSidebarCollapsed && dropdowns.payments"
                            class="pl-6 mt-1 space-y-1 transition-all duration-200"
                        >
                            <Link
                                :href="route('admin.payment-providers')"
                                :class="[
                                    'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                    route().current('admin.payment-providers')
                                        ? 'text-primary'
                                        : 'text-gray-300 hover:text-white',
                                ]"
                            >
                                <span>Payment Providers</span>
                            </Link>
                            <Link
                                :href="route('pay-methods.index')"
                                :class="[
                                    'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                    route().current('pay-methods.index')
                                        ? 'text-primary'
                                        : 'text-gray-300 hover:text-white',
                                ]"
                            >
                                <span>Payment Methods</span>
                            </Link>
                            <Link
                                :href="route('deposits.index')"
                                :class="[
                                    'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                    route().current('deposits.index')
                                        ? 'text-primary'
                                        : 'text-gray-300 hover:text-white',
                                ]"
                            >
                                <span>Deposits</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Promotion -->
                    <div>
                        <button
                            @click="toggleDropdown('promotions')"
                            :class="[
                                'w-full flex items-center justify-between rounded-lg p-3 text-base font-medium transition-all duration-200',
                                route().current('vouchers.index') ||
                                route().current('flashsale-events.index') ||
                                route().current('flashsale-items.index')
                                    ? 'bg-primary text-white shadow-glow-primary'
                                    : 'text-gray-300 hover:bg-dark-lighter hover:text-white',
                            ]"
                        >
                            <div class="flex items-center">
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
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                    />
                                </svg>
                                <span v-if="!isSidebarCollapsed" class="ml-3"
                                    >Promotions</span
                                >
                            </div>
                            <svg
                                v-if="!isSidebarCollapsed"
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 ml-2 transition-transform duration-200"
                                :class="{ 'rotate-180': dropdowns.promotions }"
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

                        <div
                            v-if="!isSidebarCollapsed && dropdowns.promotions"
                            class="pl-6 mt-1 space-y-1 transition-all duration-200"
                        >
                            <Link
                                :href="route('vouchers.index')"
                                :class="[
                                    'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                    route().current('vouchers.index')
                                        ? 'text-primary'
                                        : 'text-gray-300 hover:text-white',
                                ]"
                            >
                                <span>Vouchers</span>
                            </Link>
                            <Link
                                :href="route('flashsale-events.index')"
                                :class="[
                                    'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                    route().current('pay-methods.index')
                                        ? 'text-primary'
                                        : 'text-gray-300 hover:text-white',
                                ]"
                            >
                                <span>Flashsale Events</span>
                            </Link>
                            <Link
                                :href="route('flashsale-items.index')"
                                :class="[
                                    'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                    route().current('deposits.index')
                                        ? 'text-primary'
                                        : 'text-gray-300 hover:text-white',
                                ]"
                            >
                                <span>Manage Flashsale</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Transactions -->
                    <Link
                        :href="route('pembelians.index')"
                        :class="[
                            'flex items-center rounded-lg p-3 text-base font-medium transition-all duration-200',
                            route().current('pembelians.index')
                                ? 'bg-primary text-white shadow-glow-primary'
                                : 'text-gray-300 hover:bg-dark-lighter hover:text-white',
                        ]"
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
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2h-2a2 2 0 01-2-2v-2zM13 7a4 4 0 11-8 0 4 4 0 018 0z"
                            />
                        </svg>
                        <span v-if="!isSidebarCollapsed" class="ml-3"
                            >Transactions</span
                        >
                    </Link>

                    <!-- Users -->
                    <Link
                        :href="route('users.index')"
                        :class="[
                            'flex items-center rounded-lg p-3 text-base font-medium transition-all duration-200',
                            route().current('users.index')
                                ? 'bg-primary text-white shadow-glow-primary'
                                : 'text-gray-300 hover:bg-dark-lighter hover:text-white',
                        ]"
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
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                            />
                        </svg>
                        <span v-if="!isSidebarCollapsed" class="ml-3"
                            >Users</span
                        >
                    </Link>

                    <!-- Settings -->
                    <Link
                        :href="route('admin.settings')"
                        :class="[
                            'flex items-center rounded-lg p-3 text-base font-medium transition-all duration-200',
                            route().current('admin.settings')
                                ? 'bg-primary text-white shadow-glow-primary'
                                : 'text-gray-300 hover:bg-dark-lighter hover:text-white',
                        ]"
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
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                        <span v-if="!isSidebarCollapsed" class="ml-3"
                            >Settings</span
                        >
                    </Link>
                </div>

                <!-- Sidebar Footer - Toggle Button -->
                <div class="px-2 pt-4">
                    <button
                        @click="toggleSidebar"
                        class="flex items-center justify-center w-full p-2 text-gray-300 rounded-lg hover:bg-dark-lighter hover:text-white"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-6 h-6 transition-transform duration-300"
                            :class="{ 'rotate-180': isSidebarCollapsed }"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M11 19l-7-7 7-7m8 14l-7-7 7-7"
                            />
                        </svg>
                        <span v-if="!isSidebarCollapsed" class="ml-2"
                            >Collapse</span
                        >
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div
            v-if="isMobileMenuOpen"
            class="fixed inset-0 z-40 lg:hidden"
            @click="toggleMobileMenu"
        >
            <div
                class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"
            ></div>
        </div>

        <div
            :class="[
                'fixed inset-y-0 left-0 z-50 w-64 bg-dark-sidebar p-6 transform transition-transform duration-300 ease-in-out lg:hidden',
                isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <!-- Mobile menu header -->
            <div class="flex items-center justify-between mb-8">
                <Link
                    :href="route('admin.dashboard')"
                    class="flex items-center"
                >
                    <img :src="logoHeader" alt="logo" class="w-10 h-10" />

                    <span
                        class="ml-3 text-xl font-bold text-transparent bg-gradient-to-r from-primary to-secondary bg-clip-text"
                    >
                        {{ judulWeb }}
                    </span>
                </Link>
                <button
                    @click="toggleMobileMenu"
                    class="text-gray-300 hover:text-white"
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

            <!-- Mobile menu links -->
            <div class="space-y-1">
                <!-- Same links as desktop sidebar but adapted for mobile -->
                <Link
                    :href="route('admin.dashboard')"
                    :class="[
                        'flex items-center rounded-lg p-3 text-base font-medium transition-all duration-200',
                        route().current('admin.dashboard')
                            ? 'bg-primary text-white shadow-glow-primary'
                            : 'text-gray-300 hover:bg-dark-lighter hover:text-white',
                    ]"
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
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
                        />
                    </svg>
                    <span class="ml-3">Dashboard</span>
                </Link>

                <!-- Banners & Item Thumbnail Dropdown -->
                <div>
                    <button
                        @click="toggleDropdown('images')"
                        :class="[
                            'w-full flex items-center justify-between rounded-lg p-3 text-base font-medium transition-all duration-200',
                            route().current('banners.index') ||
                            route().current('item-thumbnails.index')
                                ? 'bg-primary text-white shadow-glow-primary'
                                : 'text-gray-300 hover:bg-dark-lighter hover:text-white',
                        ]"
                    >
                        <div class="flex items-center">
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
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                            <span class="ml-3">Images</span>
                        </div>
                        <svg
                            v-if="!isSidebarCollapsed"
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4 ml-2 transition-transform duration-200"
                            :class="{ 'rotate-180': dropdowns.images }"
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

                    <div
                        v-if="!isSidebarCollapsed && dropdowns.images"
                        class="pl-6 mt-1 space-y-1 transition-all duration-200"
                    >
                        <Link
                            :href="route('banners.index')"
                            :class="[
                                'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                route().current('banners.index')
                                    ? 'text-primary'
                                    : 'text-gray-300 hover:text-white',
                            ]"
                        >
                            <span>Banners</span>
                        </Link>
                        <Link
                            :href="route('item-thumbnails.index')"
                            :class="[
                                'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                route().current('item-thumbnails.index')
                                    ? 'text-primary'
                                    : 'text-gray-300 hover:text-white',
                            ]"
                        >
                            <span>Items Thumbnail</span>
                        </Link>
                    </div>
                </div>

                <!-- Products & Services Dropdown -->
                <div>
                    <button
                        @click="toggleDropdown('products')"
                        :class="[
                            'w-full flex items-center justify-between rounded-lg p-3 text-base font-medium transition-all duration-200',
                            route().current('categories.index') ||
                            route().current('products.index') ||
                            route().current('services.index')
                                ? 'bg-primary text-white shadow-glow-primary'
                                : 'text-gray-300 hover:bg-dark-lighter hover:text-white',
                        ]"
                    >
                        <div class="flex items-center">
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
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                                />
                            </svg>
                            <span v-if="!isSidebarCollapsed" class="ml-3"
                                >Products</span
                            >
                        </div>
                        <svg
                            v-if="!isSidebarCollapsed"
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4 ml-2 transition-transform duration-200"
                            :class="{ 'rotate-180': dropdowns.products }"
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

                    <div
                        v-if="!isSidebarCollapsed && dropdowns.products"
                        class="pl-6 mt-1 space-y-1 transition-all duration-200"
                    >
                        <Link
                            :href="route('categories.index')"
                            :class="[
                                'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                route().current('categories.index')
                                    ? 'text-primary'
                                    : 'text-gray-300 hover:text-white',
                            ]"
                        >
                            <span>Categories</span>
                        </Link>
                        <Link
                            :href="route('products.index')"
                            :class="[
                                'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                route().current('products.index')
                                    ? 'text-primary'
                                    : 'text-gray-300 hover:text-white',
                            ]"
                        >
                            <span>Products</span>
                        </Link>
                        <Link
                            :href="route('services.index')"
                            :class="[
                                'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                route().current('services.index')
                                    ? 'text-primary'
                                    : 'text-gray-300 hover:text-white',
                            ]"
                        >
                            <span>Services</span>
                        </Link>
                        <Link
                            :href="route('profit-produks.index')"
                            :class="[
                                'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                route().current('profit-produks.index')
                                    ? 'text-primary'
                                    : 'text-gray-300 hover:text-white',
                            ]"
                        >
                            <span>Profit Settings</span>
                        </Link>
                    </div>
                </div>
                <!-- Produk Input Option Fields Dropdown -->
                <div>
                    <button
                        @click="toggleDropdown('produkInput')"
                        :class="[
                            'w-full flex items-center justify-between rounded-lg p-3 text-base font-medium transition-all duration-200',
                            route().current(
                                'admin.produk-input-fields.index'
                            ) ||
                            route().current('admin.produk-input-options.index')
                                ? 'bg-primary text-white shadow-glow-primary'
                                : 'text-gray-300 hover:bg-dark-lighter hover:text-white',
                        ]"
                    >
                        <div class="flex items-center">
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
                                    d="M4 6h16M4 10h16M4 14h10M4 18h6"
                                />
                            </svg>

                            <span v-if="!isSidebarCollapsed" class="ml-3"
                                >Custom Input</span
                            >
                        </div>
                        <svg
                            v-if="!isSidebarCollapsed"
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4 ml-2 transition-transform duration-200"
                            :class="{
                                'rotate-180': dropdowns.produkInput,
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

                    <div
                        v-if="!isSidebarCollapsed && dropdowns.produkInput"
                        class="pl-6 mt-1 space-y-1 transition-all duration-200"
                    >
                        <Link
                            :href="route('admin.produk-input-fields.index')"
                            :class="[
                                'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                route().current(
                                    'admin.produk-input-fields.index'
                                )
                                    ? 'text-primary'
                                    : 'text-gray-300 hover:text-white',
                            ]"
                        >
                            <span>Input Fields</span>
                        </Link>
                        <Link
                            :href="route('admin.produk-input-options.index')"
                            :class="[
                                'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                route().current(
                                    'admin.produk-input-options.index'
                                )
                                    ? 'text-primary'
                                    : 'text-gray-300 hover:text-white',
                            ]"
                        >
                            <span>Input Options</span>
                        </Link>
                    </div>
                </div>

                <!-- Payments Dropdown -->
                <div>
                    <button
                        @click="toggleDropdown('payments')"
                        :class="[
                            'w-full flex items-center justify-between rounded-lg p-3 text-base font-medium transition-all duration-200',
                            route().current('admin.payment-providers') ||
                            route().current('pay-methods.index') ||
                            route().current('deposits.index')
                                ? 'bg-primary text-white shadow-glow-primary'
                                : 'text-gray-300 hover:bg-dark-lighter hover:text-white',
                        ]"
                    >
                        <div class="flex items-center">
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
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                />
                            </svg>
                            <span v-if="!isSidebarCollapsed" class="ml-3"
                                >Payments</span
                            >
                        </div>
                        <svg
                            v-if="!isSidebarCollapsed"
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4 ml-2 transition-transform duration-200"
                            :class="{ 'rotate-180': dropdowns.payments }"
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

                    <div
                        v-if="!isSidebarCollapsed && dropdowns.payments"
                        class="pl-6 mt-1 space-y-1 transition-all duration-200"
                    >
                        <Link
                            :href="route('admin.payment-providers')"
                            :class="[
                                'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                route().current('admin.payment-providers')
                                    ? 'text-primary'
                                    : 'text-gray-300 hover:text-white',
                            ]"
                        >
                            <span>Payment Providers</span>
                        </Link>
                        <Link
                            :href="route('pay-methods.index')"
                            :class="[
                                'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                route().current('pay-methods.index')
                                    ? 'text-primary'
                                    : 'text-gray-300 hover:text-white',
                            ]"
                        >
                            <span>Payment Methods</span>
                        </Link>
                        <Link
                            :href="route('deposits.index')"
                            :class="[
                                'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                route().current('deposits.index')
                                    ? 'text-primary'
                                    : 'text-gray-300 hover:text-white',
                            ]"
                        >
                            <span>Deposits</span>
                        </Link>
                    </div>
                </div>

                <!-- Promotion -->
                <div>
                    <button
                        @click="toggleDropdown('promotions')"
                        :class="[
                            'w-full flex items-center justify-between rounded-lg p-3 text-base font-medium transition-all duration-200',
                            route().current('vouchers.index') ||
                            route().current('flashsale-events.index') ||
                            route().current('flashsale-items.index')
                                ? 'bg-primary text-white shadow-glow-primary'
                                : 'text-gray-300 hover:bg-dark-lighter hover:text-white',
                        ]"
                    >
                        <div class="flex items-center">
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
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                />
                            </svg>
                            <span v-if="!isSidebarCollapsed" class="ml-3"
                                >Promotions</span
                            >
                        </div>
                        <svg
                            v-if="!isSidebarCollapsed"
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4 ml-2 transition-transform duration-200"
                            :class="{ 'rotate-180': dropdowns.promotions }"
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

                    <div
                        v-if="!isSidebarCollapsed && dropdowns.promotions"
                        class="pl-6 mt-1 space-y-1 transition-all duration-200"
                    >
                        <Link
                            :href="route('vouchers.index')"
                            :class="[
                                'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                route().current('vouchers.index')
                                    ? 'text-primary'
                                    : 'text-gray-300 hover:text-white',
                            ]"
                        >
                            <span>Vouchers</span>
                        </Link>
                        <Link
                            :href="route('flashsale-events.index')"
                            :class="[
                                'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                route().current('pay-methods.index')
                                    ? 'text-primary'
                                    : 'text-gray-300 hover:text-white',
                            ]"
                        >
                            <span>Flashsale Events</span>
                        </Link>
                        <Link
                            :href="route('flashsale-items.index')"
                            :class="[
                                'flex items-center rounded-lg p-2 text-sm font-medium transition-all duration-200',
                                route().current('deposits.index')
                                    ? 'text-primary'
                                    : 'text-gray-300 hover:text-white',
                            ]"
                        >
                            <span>Manage Flashsale</span>
                        </Link>
                    </div>
                </div>

                <Link
                    :href="route('pembelians.index')"
                    :class="[
                        'flex items-center rounded-lg p-3 text-base font-medium transition-all duration-200',
                        route().current('pembelians.index')
                            ? 'bg-primary text-white shadow-glow-primary'
                            : 'text-gray-300 hover:bg-dark-lighter hover:text-white',
                    ]"
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
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2h-2a2 2 0 01-2-2v-2zM13 7a4 4 0 11-8 0 4 4 0 018 0z"
                        />
                    </svg>
                    <span class="ml-3">Transactions</span>
                </Link>
                <Link
                    :href="route('users.index')"
                    :class="[
                        'flex items-center rounded-lg p-3 text-base font-medium transition-all duration-200',
                        route().current('users.index')
                            ? 'bg-primary text-white shadow-glow-primary'
                            : 'text-gray-300 hover:bg-dark-lighter hover:text-white',
                    ]"
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
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                        />
                    </svg>
                    <span class="ml-3">Users</span>
                </Link>

                <Link
                    :href="route('admin.settings')"
                    :class="[
                        'flex items-center rounded-lg p-3 text-base font-medium transition-all duration-200',
                        route().current('admin.settings')
                            ? 'bg-primary text-white shadow-glow-primary'
                            : 'text-gray-300 hover:bg-dark-lighter hover:text-white',
                    ]"
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
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                    </svg>
                    <span class="ml-3">Settings</span>
                </Link>

                <!-- Add other mobile menu links here, following the same pattern -->
                <!-- For brevity, I'm not repeating all menu items, but in a real implementation, you would include all menu items -->
            </div>
        </div>

        <!-- Main Content -->
        <div
            :class="[
                'flex-1 flex flex-col min-h-screen transition-all duration-300 ease-in-out',
                isSidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64',
            ]"
        >
            <!-- Top Navigation Bar -->
            <header class="border-b border-gray-700 shadow-md bg-dark">
                <div
                    class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8"
                >
                    <!-- Mobile menu button -->
                    <button
                        @click="toggleMobileMenu"
                        class="inline-flex items-center justify-center p-2 text-gray-300 rounded-md hover:text-white hover:bg-dark-sidebar lg:hidden"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-6 h-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                v-if="!isMobileMenuOpen"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                            <path
                                v-else
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>

                    <!-- Search field -->
                    <div class="flex-1 max-w-md mx-4">
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-5 h-5 text-gray-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    />
                                </svg>
                            </div>
                            <input
                                type="text"
                                placeholder="Search..."
                                class="block w-full py-2 pl-10 pr-3 text-gray-200 placeholder-gray-400 border border-transparent rounded-lg bg-dark focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            />
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="relative ml-3">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    class="flex items-center text-sm font-medium text-gray-300 transition duration-150 ease-in-out hover:text-white focus:outline-none"
                                >
                                    <div
                                        class="flex items-center justify-center w-10 h-10 font-bold text-white rounded-full bg-gradient-to-r from-primary to-secondary"
                                    >
                                        {{
                                            user.username
                                                .charAt(0)
                                                .toUpperCase()
                                        }}
                                    </div>
                                    <div
                                        class="flex-col items-start hidden ml-2 sm:flex"
                                    >
                                        <span class="text-white">{{
                                            user.username
                                        }}</span>
                                        <span class="text-xs text-gray-400">{{
                                            user.level
                                        }}</span>
                                    </div>
                                    <svg
                                        class="ml-2 -mr-0.5 h-4 w-4"
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
                            </template>

                            <template #content>
                                <div
                                    class="border border-gray-700 rounded-md shadow-lg bg-dark-card"
                                >
                                    <div
                                        class="px-4 py-3 text-sm border-b border-gray-700"
                                    >
                                        <div class="font-semibold text-white">
                                            {{ user.username }}
                                        </div>
                                        <div class="text-gray-400 truncate">
                                            {{ user.email }}
                                        </div>
                                    </div>
                                    <div class="py-1">
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                            class="hover:bg-dark-lighter hover:text-white"
                                        >
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('admin.settings')"
                                            class="hover:bg-dark-lighter hover:text-white"
                                        >
                                            Settings
                                        </DropdownLink>
                                    </div>
                                    <div class="py-1 border-t border-gray-700">
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                            class="w-full text-left hover:bg-dark-lighter hover:text-white"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </div>
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 bg-dark">
                <!-- Page Heading -->
                <header class="">
                    <div
                        class="px-4 pt-6 pb-2 mx-auto max-w-7xl sm:px-6 lg:px-8"
                    >
                        <h1
                            v-if="props.title"
                            class="text-2xl font-bold text-white"
                        >
                            {{ props.title }}
                        </h1>
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
                <div class="px-4 py-6 mx-auto sm:px-6 lg:px-8 max-w-7xl">
                    <Swal
                        v-if="$page?.props.flash?.status"
                        :status="$page?.props.flash?.status"
                    />

                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>

<style>
body {
    @apply bg-dark overflow-x-hidden min-h-screen;
    width: 100vw;
    overscroll-behavior: none;
}

/* Ensure modal content doesn't overflow */
.fixed.inset-0 {
    overflow-y: auto;
    height: 100%;
    max-height: 100vh;
}

/* Make DataTable responsive */

/* Hide horizontal overflow on body */
.overflow-x-hidden {
    overflow-x: hidden;
}

/* Adjust spacing for mobile */
@media (max-width: 640px) {
    .p-6 {
        padding: 1rem;
    }

    .space-y-4 > * + * {
        margin-top: 0.75rem;
    }

    .grid-cols-2 {
        grid-template-columns: 1fr;
    }
}
</style>

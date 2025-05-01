<script setup>
import CosmicNavbar from "@/Components/User/Navigation/CosmicNavbar.vue";
import CosmicFooter from "@/Components/User/Footer/CosmicFooter.vue";
</script>

<template>
    <div>
        <div class="min-h-screen bg-content_background">
            <!-- Cosmic Navigation System -->
            <CosmicNavbar />

            <!-- Page Heading -->
            <header
                class="bg-white shadow dark:bg-gray-800"
                v-if="$slots.header"
            >
                <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>

            <!-- Cosmic Footer System -->
            <CosmicFooter />
        </div>
    </div>
</template>

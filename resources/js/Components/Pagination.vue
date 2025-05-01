<script setup>
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    links: Array,
    currentPage: Number,
    perPage: Number,
    totalEntries: Number,
});

// Menghitung indeks data yang ditampilkan
const startEntry = computed(() => {
    return props.totalEntries === 0
        ? 0
        : (props.currentPage - 1) * props.perPage + 1;
});

const endEntry = computed(() => {
    return Math.min(props.currentPage * props.perPage, props.totalEntries);
});

const visiblePages = computed(() => {
    if (!props.links || props.links.length <= 3) return [];

    const pages = props.links.slice(1, -1);
    const currentPage = pages.find((page) => page.active)?.label;

    if (!currentPage) return pages;

    const currentIndex = pages.findIndex((page) => page.label === currentPage);
    const totalPages = pages.length;
    const maxVisible = 3;
    const halfVisible = Math.floor(maxVisible / 2);

    let start = Math.max(0, currentIndex - halfVisible);
    let end = Math.min(totalPages - 1, currentIndex + halfVisible);

    if (currentIndex < halfVisible) {
        end = Math.min(totalPages - 1, maxVisible - 1);
    }

    if (currentIndex > totalPages - halfVisible - 1) {
        start = Math.max(0, totalPages - maxVisible);
    }

    const visible = [];

    if (start > 0) {
        visible.push(pages[0]);
        if (start > 1) {
            visible.push({ label: "...", url: null });
        }
    }

    for (let i = start; i <= end; i++) {
        visible.push(pages[i]);
    }

    if (end < totalPages - 1) {
        if (end < totalPages - 2) {
            visible.push({ label: "...", url: null });
        }
        visible.push(pages[totalPages - 1]);
    }

    return visible;
});

const displayLinks = computed(() => {
    if (!props.links || props.links.length <= 3) return props.links;

    const previousLink = {
        ...props.links[0],
        label: "&laquo;",
    };

    const nextLink = {
        ...props.links[props.links.length - 1],
        label: "&raquo;",
    };

    return [previousLink, ...visiblePages.value, nextLink];
});
</script>

<template>
    <div
        v-if="displayLinks && displayLinks.length > 3"
        class="grid grid-cols-2 mx-4 mt-2"
    >
        <!-- Menampilkan jumlah data yang sedang ditampilkan -->
        <div class="flex mb-4 text-xs text-gray-300 sm:text-sm sm:mb-0">
            Showing {{ startEntry }} to {{ endEntry }} of
            {{ totalEntries }} entries
        </div>

        <!-- Navigation -->
        <div class="flex flex-wrap justify-center sm:justify-end">
            <template v-for="(link, key) in displayLinks" :key="key">
                <div
                    v-if="link.url === null"
                    class="px-3 py-2 mb-1 mr-1 text-sm text-gray-500 border border-gray-700 rounded-md bg-dark-lighter"
                    v-html="link.label"
                />
                <Link
                    v-else
                    class="px-3 py-2 mb-1 mr-1 text-sm transition-colors border border-gray-700 rounded-md hover:bg-primary/20 focus:border-primary focus:text-primary"
                    :class="{
                        'bg-primary/20 text-primary': link.active,
                        'text-gray-300 bg-dark-lighter': !link.active,
                    }"
                    :href="link.url"
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>

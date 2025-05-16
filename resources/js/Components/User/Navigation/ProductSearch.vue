<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from "vue";
import { debounce } from "lodash";
import axios from "axios";
import { Link } from "@inertiajs/vue3";
import CosmicIcon from "./CosmicIcon.vue";

const props = defineProps({
    isFocused: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["focus", "blur"]);

const searchQuery = ref("");
const searchResults = ref([]);
const isLoading = ref(false);
const showResults = ref(false);
const searchInputRef = ref(null);
const resultsRef = ref(null);

// Improved debounced search - increased to 500ms for performance
const performSearch = debounce(async (query) => {
    if (!query || query.length < 2) {
        searchResults.value = [];
        showResults.value = false;
        return;
    }

    try {
        isLoading.value = true;
        const response = await axios.get(
            route("api.search.products", { query })
        );
        // Limit to 5 results as specified
        searchResults.value = response.data.slice(0, 5);
        showResults.value = true;
    } catch (error) {
        console.error("Search error:", error);
        searchResults.value = [];
    } finally {
        isLoading.value = false;
    }
}, 100); // Increased to 500ms as required

// Watch for changes to search query
watch(searchQuery, (newValue) => {
    performSearch(newValue);
});

const handleFocus = () => {
    emit("focus");
    if (searchQuery.value.length >= 2) {
        showResults.value = true;
    }
};

const handleBlur = (event) => {
    // Delay hiding results to allow for clicking on results
    setTimeout(() => {
        if (!resultsRef.value?.contains(document.activeElement)) {
            showResults.value = false;
            emit("blur");
        }
    }, 100);
};

// Close results when clicking outside
const handleClickOutside = (event) => {
    if (
        searchInputRef.value &&
        !searchInputRef.value.contains(event.target) &&
        resultsRef.value &&
        !resultsRef.value.contains(event.target)
    ) {
        showResults.value = false;
    }
};

// Position dropdown correctly relative to viewport
const updateResultsPosition = () => {
    if (!resultsRef.value || !showResults.value) return;

    const inputRect = searchInputRef.value?.getBoundingClientRect();
    if (!inputRect) return;

    const viewportHeight = window.innerHeight;
    const resultsHeight = resultsRef.value.offsetHeight;

    // Check if there's enough space below
    const spaceBelow = viewportHeight - inputRect.bottom;

    if (spaceBelow < resultsHeight && inputRect.top > resultsHeight) {
        // Position above if not enough space below
        resultsRef.value.style.top = "auto";
        resultsRef.value.style.bottom = "100%";
        resultsRef.value.style.marginTop = "0";
        resultsRef.value.style.marginBottom = "0.5rem";
    } else {
        // Position below (default)
        resultsRef.value.style.top = "100%";
        resultsRef.value.style.bottom = "auto";
        resultsRef.value.style.marginTop = "0.5rem";
        resultsRef.value.style.marginBottom = "0";
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
    window.addEventListener("resize", updateResultsPosition);
    window.addEventListener("scroll", updateResultsPosition);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
    window.removeEventListener("resize", updateResultsPosition);
    window.removeEventListener("scroll", updateResultsPosition);
});

watch(showResults, (newValue) => {
    if (newValue) {
        nextTick(() => {
            updateResultsPosition();
        });
    }
});
</script>

<template>
    <div class="relative w-full">
        <div
            class="relative flex items-center transition-all rounded-full ring-1 ring-primary hover:ring-secondary"
            :class="{ 'ring-2 ring-primary': isFocused }"
        >
            <!-- Search Icon -->
            <div
                class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
            >
                <CosmicIcon name="search" size="md" className="text-gray-400" />
            </div>

            <!-- Search Input -->
            <input
                ref="searchInputRef"
                v-model="searchQuery"
                type="text"
                placeholder="Search across the cosmos..."
                class="w-full py-2 pl-10 pr-4 border-none rounded-full bg-primary/10 text-primary-text focus:ring-0 placeholder-primary-text/40"
                @focus="handleFocus"
                @blur="handleBlur"
            />

            <!-- Loading Indicator -->
            <div
                v-if="isLoading"
                class="absolute inset-y-0 right-0 flex items-center pr-3"
            >
                <div
                    class="w-5 h-5 border-2 border-t-2 rounded-full border-primary animate-spin"
                ></div>
            </div>

            <!-- Particle effects when focused -->
            <div
                v-if="isFocused"
                class="absolute inset-0 overflow-hidden pointer-events-none opacity-20"
            >
                <div
                    class="absolute top-0 w-1 h-1 rounded-full left-1/4 bg-secondary animate-ping"
                ></div>
                <div
                    class="absolute top-3/4 left-3/4 w-1.5 h-1.5 bg-primary rounded-full animate-pulse-slow"
                ></div>
                <div
                    class="absolute w-1 h-1 rounded-full top-1/2 left-1/3 bg-secondary animate-pulse"
                ></div>
                <div
                    class="absolute top-1/4 right-1/4 w-0.5 h-0.5 bg-primary rounded-full animate-ping"
                ></div>
            </div>
        </div>

        <!-- Search Results - Optimized with absolute positioning and max height -->
        <div
            v-if="showResults && searchResults.length > 0"
            ref="resultsRef"
            class="absolute z-50 w-full mt-2 overflow-hidden overflow-y-auto transition-all duration-300 origin-top border rounded-md bg-content_background shadow-glow-primary max-h-60 border-primary/30"
            style="max-height: 50vh; will-change: transform, opacity"
        >
            <div class="py-1">
                <Link
                    v-for="(product, index) in searchResults"
                    :key="product.id"
                    :href="route('order.index', product.slug)"
                    class="flex items-center gap-3 px-4 py-2 transition-all hover:bg-primary/10"
                    @mousedown.prevent
                >
                    <!-- Product Thumbnail -->
                    <div
                        class="flex-shrink-0 w-10 h-10 overflow-hidden rounded-md shadow-sm bg-primary/5"
                    >
                        <img
                            :src="'/storage/' + product.thumbnail"
                            :alt="product.nama"
                            class="object-cover w-full h-full"
                            loading="lazy"
                        />
                    </div>

                    <!-- Product Name -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm truncate text-primary-text">
                            {{ product.nama }}
                        </p>
                    </div>

                    <!-- Orbiting Planet (Cosmetic Enhancement) -->
                    <!-- <div class="relative w-3 h-3">
                        <div
                            class="absolute inset-0 w-2 h-2 rounded-full bg-secondary animate-pulse-slow"
                        ></div>
                    </div> -->
                </Link>
            </div>
        </div>

        <!-- No Results Message -->
        <div
            v-else-if="showResults && searchQuery.length >= 2 && !isLoading"
            ref="resultsRef"
            class="absolute z-50 w-full mt-2 transition-all duration-300 origin-top border rounded-md bg-content_background shadow-glow-primary border-primary/30"
        >
            <div
                class="relative px-4 py-3 text-sm text-center text-primary-text/70"
            >
                <p>No products found in this galaxy...</p>
                <!-- Nebula Pulse Effect (Cosmetic Enhancement) -->
                <div class="absolute inset-0 pointer-events-none opacity-20">
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-primary/0 via-primary/20 to-primary/0 animate-pulse-slow"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</template>

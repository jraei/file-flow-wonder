
<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";

const props = defineProps({
    align: {
        type: String,
        default: "right",
    },
    width: {
        type: String,
        default: "48",
    },
});

const open = ref(false);
const dropdownRef = ref(null);
const triggerRef = ref(null);
const dropdownStyles = ref({});

const emit = defineEmits(["toggleDropdown"]);

const toggleDropdown = () => {
    open.value = !open.value;
    emit("toggleDropdown", open.value);

    if (open.value) {
        nextTick(() => {
            positionDropdown();
        });
    }
};

const positionDropdown = () => {
    if (!dropdownRef.value || !triggerRef.value) return;

    const triggerRect = triggerRef.value.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    
    // Get dropdown dimensions after it's rendered
    const dropdownRect = dropdownRef.value.getBoundingClientRect();
    
    // Calculate dropdown width (use trigger width as minimum)
    const width = Math.max(triggerRect.width, dropdownRect.width);
    
    // Set default position (below trigger)
    let top = triggerRect.bottom + 4;
    let left = triggerRect.left;
    
    // Handle horizontal positioning - prevent overflow
    if (left + width > viewportWidth) {
        // Align right edge of dropdown with right edge of trigger
        left = Math.max(0, triggerRect.right - width);
    }
    
    // Handle vertical positioning - flip if not enough space below
    if (top + dropdownRect.height > viewportHeight) {
        // Position above the trigger
        top = Math.max(8, triggerRect.top - dropdownRect.height - 4);
    }
    
    // Apply positioning - use fixed to avoid scroll issues
    dropdownStyles.value = {
        position: "absolute",
        top: `${top - window.scrollY}px`,
        left: `${left}px`,
        zIndex: 9999,
        width: `${width}px`,
        minWidth: `${triggerRect.width}px`,
    };
};

const closeDropdown = () => {
    open.value = false;
};

// Handle window resize to reposition dropdown
const handleResize = () => {
    if (open.value) {
        positionDropdown();
    }
};

// Handle scroll events to keep dropdown positioned correctly
const handleScroll = () => {
    if (open.value) {
        positionDropdown();
    }
};

onMounted(() => {
    window.addEventListener("resize", handleResize);
    window.addEventListener("scroll", handleScroll, true);
});

onUnmounted(() => {
    window.removeEventListener("resize", handleResize);
    window.removeEventListener("scroll", handleScroll, true);
});
</script>

<template>
    <div class="relative">
        <button ref="triggerRef" @click="toggleDropdown">
            <slot name="trigger" />
        </button>

        <!-- Backdrop for closing dropdown when clicking outside -->
        <div
            v-if="open"
            class="fixed inset-0 z-40"
            @click="closeDropdown"
        ></div>

        <!-- Dropdown content with improved transitions -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-show="open"
                ref="dropdownRef"
                class="absolute border rounded-md shadow-lg bg-secondary/20 backdrop-blur border-primary/60 will-change-transform"
                :style="dropdownStyles"
            >
                <slot name="content" />
            </div>
        </Transition>
    </div>
</template>

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

const toggleDropdown = async () => {
    open.value = !open.value;
    emit("toggleDropdown", open.value);

    if (open.value) {
        await nextTick();
        positionDropdown();
    }
};

const positionDropdown = () => {
    if (!dropdownRef.value || !triggerRef.value) return;

    const triggerRect = triggerRef.value.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;

    const dropdownRect = dropdownRef.value.getBoundingClientRect();

    let top = triggerRect.bottom + 4; // Default position below button
    let left = triggerRect.left;

    // Horizontal positioning - prevent overflow on the right side
    if (left + dropdownRect.width > viewportWidth) {
        left = Math.max(0, viewportWidth - dropdownRect.width - 8);
    }

    // Vertical positioning - flip to above trigger if not enough space below
    if (top + dropdownRect.height > viewportHeight) {
        top = Math.max(8, triggerRect.top - dropdownRect.height - 4);
    }

    // Apply positioning - use fixed to avoid scroll issues
    dropdownStyles.value = {
        position: "fixed",
        top: `${top}px`,
        left: `${left}px`,
        zIndex: 9999,
        width: dropdownRect.width > 0 ? `${dropdownRect.width}px` : undefined,
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
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="scale-95 opacity-0"
            enter-to-class="scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="scale-100 opacity-100"
            leave-to-class="scale-95 opacity-0"
        >
            <div
                v-show="open"
                ref="dropdownRef"
                class="w-48 border rounded-md shadow-lg bg-secondary/20 backdrop-blur border-primary/60 will-change-transform"
                :style="dropdownStyles"
            >
                <slot name="content" />
            </div>
        </Transition>
    </div>
</template>

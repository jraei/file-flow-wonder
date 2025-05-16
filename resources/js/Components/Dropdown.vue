<script setup>
import { ref, nextTick, onMounted, onUnmounted } from "vue";

const open = ref(false);
const triggerRef = ref(null);
const dropdownRef = ref(null);

const toggleDropdown = async () => {
    open.value = !open.value;

    if (open.value) {
        await nextTick(); // pastikan DOM sudah update sebelum kalkulasi
        positionDropdown();
    }
};

const closeDropdown = () => {
    open.value = false;
};

const positionDropdown = () => {
    // tidak perlu manual jika pakai absolute + relative
    // biarkan Tailwind atur dengan kelas
};

const handleClickOutside = (e) => {
    if (
        dropdownRef.value &&
        !dropdownRef.value.contains(e.target) &&
        !triggerRef.value.contains(e.target)
    ) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
    <!-- Wrapper harus relative -->
    <div class="relative inline-block text-left">
        <!-- Trigger -->
        <button ref="triggerRef" @click="toggleDropdown" type="button">
            <slot name="trigger" />
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <div
                v-show="open"
                ref="dropdownRef"
                class="absolute right-0 z-50 w-40 mt-2 border rounded-md shadow-lg backdrop-blur border-primary/60 bg-secondary/20"
            >
                <slot name="content" />
            </div>
        </Transition>
    </div>
</template>

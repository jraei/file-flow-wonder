<template>
    <div
        class="sticky bottom-0 left-0 z-50 w-full px-4 py-2 shadow-lg bg-primary dark:bg-dark-card dark:border-gray-700"
    >
        <div class="flex justify-center">
            <button
                @click="updateColors"
                :disabled="isLoading"
                class="flex items-center px-6 py-3 space-x-2 font-medium transition-all duration-200 rounded-lg shadow-md text-primary-text bg-primary hover:bg-primary-hover hover:shadow-lg disabled:opacity-50"
            >
                <svg
                    v-if="isLoading"
                    class="w-5 h-5 mr-2 animate-spin"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                </svg>
                <span>{{
                    isLoading ? "Updating Colors..." : "Update Colors"
                }}</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import Swal from "sweetalert2";

const props = defineProps({
    colors: {
        type: Object,
        required: true,
    },
});

const isLoading = ref(false);

const updateColors = async () => {
    isLoading.value = true;

    console.log("Colors to update:", props.colors);

    try {
        // Save current colors to localStorage as a backup
        localStorage.setItem("savedColors", JSON.stringify(props.colors));

        // Send API request to update colors
        const response = await axios.post("/api/update-colors", props.colors);

        if (response.data.success) {
            Swal.fire({
                title: "Success!",
                text: "Colors updated successfully. Reloading page to apply changes.",
                icon: "success",
                timer: 2000,
                showConfirmButton: false,
            });

            // Force page reload after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    } catch (error) {
        console.error("Error updating colors:", error);

        Swal.fire({
            title: "Error!",
            text: error.response?.data?.message || "Failed to update colors",
            icon: "error",
        });

        isLoading.value = false;
    }
};
</script>

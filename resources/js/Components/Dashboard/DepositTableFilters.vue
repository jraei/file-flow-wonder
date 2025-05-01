<script setup>
import { ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";

// Pass modelValue for two-way binding for each filter option
const props = defineProps({
    filters: {
        type: Object,
        required: true,
    },
});
const emit = defineEmits(["update:filters"]);
const localFilters = ref({
    status: props.filters.status || "",
    date_start: props.filters.date_start || "",
    date_end: props.filters.date_end || "",
});

// Watch for external filter changes
watch(
    () => props.filters,
    (newVal) => {
        Object.assign(localFilters.value, newVal);
    }
);

function updateFilters() {
    emit("update:filters", { ...localFilters.value });
}

const statusOptions = [
    { value: "", label: "Semua Status" },
    { value: "pending", label: "Menunggu" },
    { value: "paid", label: "Berhasil" },
    { value: "failed", label: "Gagal" },
    { value: "cancelled", label: "Dibatalkan" },
];

// Debounced update on input changes
let timer;
watch(localFilters, () => {
    clearTimeout(timer);
    timer = setTimeout(updateFilters, 300);
});
</script>

<template>
    <div class="flex flex-wrap items-end gap-3 mb-4 animate-fade-in">
        <div>
            <label class="block mb-1 text-xs font-semibold text-secondary"
                >Status</label
            >
            <select
                v-model="localFilters.status"
                class="px-3 py-2 text-white border rounded-lg shadow-sm bg-secondary/20 border-primary/40 focus:ring-primary focus:border-primary"
            >
                <option
                    v-for="opt in statusOptions"
                    :key="opt.value"
                    :value="opt.value"
                >
                    {{ opt.label }}
                </option>
            </select>
        </div>
        <div>
            <label class="block mb-1 text-xs font-semibold text-secondary"
                >Tanggal Awal</label
            >
            <input
                type="date"
                v-model="localFilters.date_start"
                class="px-3 py-2 text-white border rounded-lg shadow-sm bg-secondary/20 border-primary/40 focus:ring-primary focus:border-primary"
                max="9999-12-31"
            />
        </div>
        <div>
            <label class="block mb-1 text-xs font-semibold text-secondary"
                >Tanggal Akhir</label
            >
            <input
                type="date"
                v-model="localFilters.date_end"
                class="px-3 py-2 text-white border rounded-lg shadow-sm bg-secondary/20 border-primary/40 focus:ring-primary focus:border-primary"
                max="9999-12-31"
            />
        </div>
    </div>
</template>

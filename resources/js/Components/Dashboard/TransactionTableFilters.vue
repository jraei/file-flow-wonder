<script setup>
import { ref, watch, nextTick, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
import { Calendar } from "lucide-vue-next";

const props = defineProps({
    filters: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["update:filters"]);

const statusOptions = [
    { value: "", label: "All Status" },
    { value: "pending", label: "Pending" },
    { value: "processing", label: "Processing" },
    { value: "completed", label: "Completed" },
    { value: "failed", label: "Failed" },
    { value: "cancelled", label: "Cancelled" },
];

const status = ref(props.filters.status || "");
const dateStart = ref(props.filters.date_start || "");
const dateEnd = ref(props.filters.date_end || "");
const search = ref(props.filters.search || "");
const showDatepicker = ref(false);

// Setup date range picker
let dateRangePicker = null;

const initDatePicker = async () => {
    await nextTick();
    const dateRange = document.getElementById("transaction-date-range");

    if (dateRange) {
        dateRangePicker = flatpickr(dateRange, {
            mode: "range",
            dateFormat: "Y-m-d",
            defaultDate: [dateStart.value, dateEnd.value].filter(Boolean),
            onChange: (selectedDates) => {
                if (selectedDates.length === 2) {
                    dateStart.value = selectedDates[0]
                        .toISOString()
                        .split("T")[0];
                    dateEnd.value = selectedDates[1]
                        .toISOString()
                        .split("T")[0];
                    applyFilters();
                    showDatepicker.value = false;
                }
            },
            onClose: () => {
                showDatepicker.value = false;
            },
        });
    }
};

// Apply filters when they change
function applyFilters() {
    emit("update:filters", {
        status: status.value,
        date_start: dateStart.value,
        date_end: dateEnd.value,
        search: search.value,
    });
}

function clearDateRange() {
    dateStart.value = "";
    dateEnd.value = "";
    if (dateRangePicker) {
        dateRangePicker.clear();
    }
    applyFilters();
}

// Export transactions with current filters
function exportTransactions() {
    window.location.href = route("dashboard.transactions.export", {
        status: status.value,
        date_start: dateStart.value,
        date_end: dateEnd.value,
        search: search.value,
        sort_by: props.filters.sort_by,
        sort_order: props.filters.sort_order,
    });
}

// Watch for date picker toggling
watch(showDatepicker, (val) => {
    if (val) {
        initDatePicker();
    }
});

// Initialize date picker when component mounts
onMounted(() => {
    if (dateStart.value || dateEnd.value) {
        initDatePicker();
    }
});
</script>

<template>
    <div class="p-4 mb-4 space-y-4 cosmic-filter-card">
        <h3 class="text-lg font-bold text-white">Filter Transactions</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <!-- Status Filter -->
            <div class="relative starlight-dropdown">
                <select
                    v-model="status"
                    @change="applyFilters"
                    class="w-full px-4 py-2 text-white bg-transparent border rounded-lg appearance-none border-primary/30 focus:border-primary focus:ring-1 focus:ring-primary"
                >
                    <option
                        v-for="option in statusOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
                <div
                    class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none"
                >
                    <div class="w-4 h-4 text-primary">
                        <svg
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
                    </div>
                </div>
                <!-- Starlight Effect -->
                <div class="absolute inset-0 pointer-events-none">
                    <div
                        class="absolute w-1 h-1 bg-primary rounded-full opacity-60 animate-pulse"
                        style="top: 25%; left: 10%"
                    ></div>
                    <div
                        class="absolute w-1 h-1 bg-secondary rounded-full opacity-60 animate-pulse"
                        style="top: 60%; left: 80%"
                    ></div>
                    <div
                        class="absolute w-1 h-1 bg-primary rounded-full opacity-60 animate-pulse"
                        style="top: 80%; left: 30%"
                    ></div>
                </div>
            </div>

            <!-- Date Range Picker -->
            <div class="relative warp-speed-datepicker">
                <div class="relative">
                    <input
                        id="transaction-date-range"
                        type="text"
                        readonly
                        placeholder="Select Date Range"
                        :value="
                            dateStart && dateEnd
                                ? `${dateStart} - ${dateEnd}`
                                : ''
                        "
                        class="w-full px-4 py-2 text-white bg-transparent border rounded-lg border-primary/30 focus:border-primary focus:outline-none placeholder-gray-500"
                        @click="showDatepicker = !showDatepicker"
                    />
                    <button
                        v-if="dateStart && dateEnd"
                        @click.prevent="clearDateRange"
                        class="absolute right-9 top-2.5 text-gray-400 hover:text-white"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>
                    <div
                        class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-primary"
                    >
                        <Calendar class="w-5 h-5" />
                    </div>
                </div>

                <!-- Warp Speed Effect -->
                <div
                    class="absolute inset-0 overflow-hidden pointer-events-none rounded-lg"
                >
                    <div
                        v-for="i in 5"
                        :key="i"
                        class="absolute h-[1px] bg-gradient-to-r from-transparent via-primary to-transparent"
                        :style="`top: ${20 * i}%; width: 100%; opacity: ${
                            0.2 * i
                        }; transform: translateX(${
                            i % 2 ? -50 : 50
                        }%); animation: warp-speed ${
                            1 + i * 0.5
                        }s linear infinite;`"
                    ></div>
                </div>
            </div>

            <!-- Search Input -->
            <div class="flex space-x-2">
                <div class="flex-grow">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search transactions..."
                        class="w-full px-4 py-2 text-white bg-transparent border rounded-lg border-primary/30 focus:border-primary focus:outline-none placeholder-gray-500"
                        @keyup.enter="applyFilters"
                    />
                </div>
                <button
                    class="px-4 py-2 font-bold text-white rounded-lg bg-primary hover:bg-primary/80 focus:outline-none cosmic-export-btn"
                    @click="exportTransactions"
                >
                    <span class="flex items-center space-x-1">
                        <span>Export</span>
                        <div class="relative w-4 h-4">
                            <!-- Planet Export Icon -->
                            <div
                                class="absolute inset-0 w-4 h-4 border-2 border-white rounded-full cosmic-rotate"
                            ></div>
                            <div
                                class="absolute w-1 h-1 bg-white rounded-full cosmic-orbit"
                                style="
                                    top: -3px;
                                    left: 50%;
                                    transform: translateX(-50%);
                                "
                            ></div>
                        </div>
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.cosmic-filter-card {
    background: linear-gradient(
        180deg,
        rgba(26, 31, 44, 0.8) 0%,
        rgba(31, 41, 55, 0.6) 100%
    );
    border: 1px solid rgba(155, 135, 245, 0.2);
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.starlight-dropdown select {
    background: rgba(31, 41, 55, 0.6);
}

.starlight-dropdown select option {
    background-color: #1a1f2c;
    color: white;
}

@keyframes warp-speed {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

.cosmic-rotate {
    animation: rotate 10s linear infinite;
}

.cosmic-orbit {
    animation: orbit 3s linear infinite;
}

@keyframes rotate {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

@keyframes orbit {
    0% {
        transform: translateX(-50%) rotate(0deg) translateX(10px);
    }
    100% {
        transform: translateX(-50%) rotate(360deg) translateX(10px);
    }
}
</style>

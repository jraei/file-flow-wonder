<script setup>
import { ref, watch, computed } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
    columns: {
        type: Array,
        required: true,
    },
    searchable: {
        type: Boolean,
        default: true,
    },
    createable: {
        type: Boolean,
        default: true,
    },
    editable: {
        type: Boolean,
        default: true,
    },
    pagination: {
        type: Object,
        default: () => ({ current_page: 1, per_page: 10, total: 0 }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    route: {
        type: String,
        default: null,
    },
});

const emit = defineEmits([
    "edit",
    "delete",
    "view",
    "page-change",
    "search",
    "sort",
]);

const searchQuery = ref(props.filters.search || "");
const sortColumn = ref(props.filters.sort || "");
const sortDirection = ref(props.filters.direction || "asc");

const handleSearch = () => {
    if (props.route) {
        // Use the route() helper to generate the correct URL
        const routeName = props.route;
        router.get(
            route(routeName),
            {
                search: searchQuery.value,
                sort: sortColumn.value,
                direction: sortDirection.value,
            },
            {
                preserveState: true,
                replace: true,
            }
        );
    } else {
        emit("search", searchQuery.value);
    }
};

const handleSort = (column) => {
    if (!column.key) return;

    if (sortColumn.value === column.key) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
    } else {
        sortColumn.value = column.key;
        sortDirection.value = "asc";
    }

    if (props.route) {
        // Use the route() helper to generate the correct URL
        const routeName = props.route;
        router.get(
            route(routeName),
            {
                search: searchQuery.value,
                sort: sortColumn.value,
                direction: sortDirection.value,
            },
            {
                preserveState: true,
                replace: true,
            }
        );
    } else {
        emit("sort", {
            column: sortColumn.value,
            direction: sortDirection.value,
        });
    }
};

const handleAction = (action, item) => {
    emit(action, item);
};

// Debounce search input
let searchTimeout;
watch(searchQuery, (newValue) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        handleSearch();
    }, 500); // Increased debounce time for better server performance
});

// Initialize sorted column on mount if provided in props
watch(
    () => props.filters,
    (newFilters) => {
        if (newFilters.sort) {
            sortColumn.value = newFilters.sort;
        }
        if (newFilters.direction) {
            sortDirection.value = newFilters.direction;
        }
        if (
            newFilters.search !== undefined &&
            searchQuery.value !== newFilters.search
        ) {
            searchQuery.value = newFilters.search || "";
        }
    },
    { immediate: true }
);
</script>

<template>
    <div
        class="overflow-hidden bg-transparent border shadow border-secondary/20 rounded-2xl"
    >
        <!-- Table Header with Search -->
        <div
            class="flex flex-col items-center justify-between p-4 space-y-4 border-b border-primary/30 sm:flex-row sm:space-y-0"
        >
            <h2 class="text-lg font-semibold text-white">
                <slot name="title">Data Table</slot>
            </h2>

            <div class="flex w-full space-x-2 sm:w-auto">
                <div
                    v-if="searchable"
                    class="relative flex-grow sm:flex-grow-0 sm:w-64"
                >
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
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search..."
                        class="block w-full py-2 pl-10 pr-3 text-gray-200 placeholder-gray-400 border rounded-lg border-secondary/50 bg-secondary/30 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                    />
                </div>

                <!-- show add button when createable is true -->
                <slot name="addButton" v-if="createable">
                    <button
                        class="flex items-center px-4 py-2 space-x-2 text-white transition-all duration-200 rounded-lg shadow-lg bg-primary hover:bg-primary-hover hover:shadow-glow-primary"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                            />
                        </svg>
                        <span>Add New</span>
                    </button>
                </slot>
            </div>
        </div>

        <!-- Table Body -->
        <div class="overflow-x-auto max-w-[90vw]">
            <table class="w-full divide-y divide-primary/30 min-w-max">
                <thead class="">
                    <tr>
                        <!-- Tambahkan Kolom "ID" sebagai ID dari loop -->
                        <th
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase"
                        >
                            ID
                        </th>
                        <th
                            v-for="column in columns.filter(
                                (col) => col.key !== 'id'
                            )"
                            :key="column.key"
                            scope="col"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase transition-colors cursor-pointer hover:text-white"
                            @click="handleSort(column)"
                        >
                            <div class="flex items-center space-x-1">
                                <span>{{ column.label }}</span>
                                <!-- Sort Direction Indicator -->
                                <div
                                    v-if="sortColumn === column.key"
                                    class="ml-1"
                                >
                                    <svg
                                        v-if="sortDirection === 'asc'"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-4 h-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 15l7-7 7 7"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-4 h-4"
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
                                </div>
                            </div>
                        </th>
                        <!-- show action column when editable is true -->

                        <th
                            scope="col"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-300 uppercase"
                            v-if="editable"
                        >
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary/30">
                    <tr
                        v-for="(item, index) in data"
                        :key="index"
                        class="transition-colors hover:bg-secondary/30"
                    >
                        <!-- Kolom Nomor Urut (ID dari Looping) -->
                        <td class="px-6 py-4 text-gray-200 whitespace-nowrap">
                            {{ index + 1 }}
                        </td>
                        <td
                            v-for="column in columns.filter(
                                (col) => col.key !== 'id'
                            )"
                            :key="`${index}-${column.key}`"
                            class="px-6 py-4 whitespace-nowrap"
                        >
                            <slot
                                :name="`cell(${column.key})`"
                                :item="item"
                                :column="column"
                            >
                                <div
                                    v-if="column.format"
                                    v-html="
                                        column.format(item[column.key], item)
                                    "
                                ></div>
                                <span v-else class="text-gray-200">{{
                                    item[column.key]
                                }}</span>
                            </slot>
                        </td>
                        <td
                            class="px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap"
                            v-if="editable"
                        >
                            <slot name="actions" :item="item">
                                <button
                                    @click="handleAction('view', item)"
                                    class="transition-colors text-secondary hover:text-secondary-hover"
                                >
                                    View
                                </button>
                                <button
                                    @click="handleAction('edit', item)"
                                    class="transition-colors text-primary hover:text-primary-hover"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="handleAction('delete', item)"
                                    class="text-red-400 transition-colors hover:text-red-500"
                                >
                                    Delete
                                </button>
                            </slot>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-if="data.length === 0">
                        <td
                            :colspan="columns.length + 1"
                            class="px-6 py-12 text-center"
                        >
                            <div class="flex flex-col items-center">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-12 h-12 mb-4 text-primary"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                                    />
                                </svg>
                                <p class="text-lg text-primary">
                                    No data found
                                </p>
                                <p class="mt-1 text-sm text-primary">
                                    <span v-if="searchQuery"
                                        >Try adjusting your search query</span
                                    >
                                    <span v-else>No records exist yet</span>
                                </p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

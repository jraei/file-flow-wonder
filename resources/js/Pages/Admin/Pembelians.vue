<script setup>
import { ref, computed, getCurrentInstance } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import Modal from "@/Components/Modal.vue";
import Dropdown from "@/Components/Dropdown.vue";
import Pagination from "@/Components/Pagination.vue";
import axios from "axios";

const props = defineProps({
    pembelians: Object,
    errors: Object,
    filters: Object,
});

const { proxy } = getCurrentInstance();

// Computed property for purchases data
const pembelians = computed(() => props.pembelians.data || []);

// Column definitions for the table
const columns = [
    { key: "id", label: "ID" },
    {
        key: "user",
        label: "Username",
        format: (value, item) => {
            return value
                ? `<a href="${route(
                      "users.show",
                      value.id
                  )}" class="text-primary hover:underline">${
                      value.username
                  }</a>`
                : "N/A";
        },
    },
    {
        key: "layanan",
        label: "Service",
        format: (value) => {
            return value ? value.nama_layanan : "N/A";
        },
    },
    {
        key: "price",
        label: "Price",
        format: (value, item) => {
            return `<span class="font-medium">Rp ${Number(
                item.price
            ).toLocaleString("id-ID")}</span>`;
        },
    },
    {
        key: "profit",
        label: "Profit",
        format: (value, item) => {
            const profitClass =
                item.profit >= 0
                    ? "font-bold text-green-500 bg-green-500/10 px-2 py-1 rounded"
                    : "font-bold text-red-400 bg-red-500/10 px-2 py-1 rounded";
            return `<span class="${profitClass}" title="Profit from this transaction">Rp ${Number(
                item.profit
            ).toLocaleString("id-ID")}</span>`;
        },
    },
    {
        key: "pembayaran",
        label: "Payment Method",
        format: (value) => {
            return value ? value.payment_method : "N/A";
        },
    },
    {
        key: "status",
        label: "Status",
        format: (value, item) => {
            let statusClasses = "";

            switch (item.status) {
                case "completed":
                    statusClasses = "bg-green-500/20 text-green-400";
                    break;
                case "pending":
                    statusClasses = "bg-gray-500/20 text-gray-400";
                    break;
                case "processing":
                    statusClasses = "bg-blue-500/20 text-blue-400";
                    break;
                case "failed":
                case "cancelled":
                    statusClasses = "bg-red-500/20 text-red-400";
                    break;
                default:
                    statusClasses = "bg-gray-500/20 text-gray-400";
            }

            return `<span class="${statusClasses} px-2 py-1 rounded-xl text-xs">${item.status}</span>`;
        },
    },
];

// Status filter
const statusOptions = [
    { value: "", label: "All Status" },
    { value: "pending", label: "Pending" },
    { value: "processing", label: "Processing" },
    { value: "completed", label: "Completed" },
    { value: "failed", label: "Failed" },
];
const selectedStatus = ref(props.filters.filter || "");

// View modal states
const showViewModal = ref(false);
const selectedPurchase = ref(null);
const isLoading = ref(false);
const processingPurchase = ref(false);

// Handle filter change
const handleFilterChange = (event) => {
    router.get(
        route("pembelians.index"),
        {
            search: props.filters.search,
            sort: props.filters.sort,
            direction: props.filters.direction,
            filter: event.target.value,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

// Handle view action
const handleView = async (item) => {
    isLoading.value = true;
    selectedPurchase.value = { ...item, loading: true };
    showViewModal.value = true;

    try {
        const response = await axios.get(route("pembelians.show", item.id));
        selectedPurchase.value = response.data.pembelian;
    } catch (error) {
        console.error("Error fetching purchase details:", error);
        proxy.$showSwalConfirm({
            title: "Error",
            text: "Failed to load purchase details",
            icon: "error",
        });
    } finally {
        isLoading.value = false;
    }
};

// Handle process action
const handleProcess = (item) => {
    proxy.$showSwalConfirm({
        title: "Process Purchase",
        text: "Are you sure you want to mark this purchase as completed?",
        icon: "question",
        confirmButtonText: "Yes, process it",
        onConfirm: async () => {
            try {
                processingPurchase.value = true;
                const response = await axios.patch(
                    route("pembelians.process", item.id)
                );

                // Update the purchase in the data array
                const index = pembelians.value.findIndex(
                    (p) => p.id === item.id
                );
                if (index !== -1) {
                    pembelians.value[index] = response.data.pembelian;
                }

                // If the purchase is currently being viewed, update the modal data
                if (
                    selectedPurchase.value &&
                    selectedPurchase.value.id === item.id
                ) {
                    selectedPurchase.value = response.data.pembelian;
                }

                proxy.$showSwalConfirm({
                    title: "Success",
                    text: "Purchase has been processed successfully",
                    icon: "success",
                    showCancelButton: false,
                });
            } catch (error) {
                console.error("Error processing purchase:", error);
                proxy.$showSwalConfirm({
                    title: "Error",
                    text:
                        error.response?.data?.error ||
                        "Failed to process purchase",
                    icon: "error",
                    showCancelButton: false,
                });
            } finally {
                processingPurchase.value = false;
            }
        },
    });
};

// Handle delete action
const handleDelete = (item) => {
    proxy.$showSwalConfirm({
        title: "Delete Purchase",
        text: "Are you sure you want to delete this purchase? This action cannot be undone.",
        icon: "warning",
        confirmButtonText: "Yes, delete it",
        onConfirm: () => {
            router.delete(route("pembelians.destroy", item.id), {
                preserveScroll: true,
            });

            // Close modal if the deleted purchase is being viewed
            if (
                selectedPurchase.value &&
                selectedPurchase.value.id === item.id
            ) {
                showViewModal.value = false;
            }
        },
    });
};

// Format date helper function
const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    const date = new Date(dateString);
    return date.toLocaleString("id-ID", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedPurchase.value = null;
};
</script>

<template>
    <Head title="Purchases" />

    <AdminLayout title="Purchase Management">
        <div
            v-if="Object.keys(errors).length > 0"
            class="px-4 py-3 mb-4 text-sm text-white rounded-lg bg-red-500/80"
        >
            <ul v-for="error in errors">
                <li>{{ error }}</li>
            </ul>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h1 class="text-2xl font-bold text-white">Transactions</h1>

            <!-- Status Filter Dropdown -->
            <div class="relative">
                <select
                    v-model="selectedStatus"
                    @change="handleFilterChange"
                    class="block w-full py-2 pl-3 pr-10 text-base text-gray-200 border border-gray-700 rounded-lg appearance-none bg-dark-sidebar focus:outline-none focus:ring-2 focus:ring-primary"
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
                    <svg
                        class="w-4 h-4 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 9l-7 7-7-7"
                        ></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Purchase Data Table -->
        <div
            class="w-full border rounded-lg shadow-lg border-primary/40 bg-dark-card"
        >
            <DataTable
                :data="pembelians"
                :columns="columns"
                :filters="filters"
                route="pembelians.index"
                class="max-w-full whitespace-nowrap"
            >
                <template #title>Purchase Transactions</template>

                <!-- No add button for read-only interface -->
                <template #addButton></template>

                <!-- Custom actions column -->
                <template #actions="{ item }">
                    <div class="flex items-center justify-end space-x-2">
                        <!-- View button -->
                        <button
                            @click="handleView(item)"
                            class="px-3 py-1 text-xs text-white transition-colors rounded-md bg-secondary/80 hover:bg-secondary"
                        >
                            View
                        </button>

                        <!-- Process button (only for pending purchases) -->
                        <button
                            v-if="item.status === 'pending'"
                            @click="handleProcess(item)"
                            class="px-3 py-1 text-xs text-white transition-colors rounded-md bg-primary/80 hover:bg-primary"
                            :disabled="processingPurchase"
                        >
                            Process
                        </button>

                        <!-- Delete button -->
                        <button
                            @click="handleDelete(item)"
                            class="px-3 py-1 text-xs text-white transition-colors rounded-md bg-red-500/80 hover:bg-red-500"
                        >
                            Delete
                        </button>
                    </div>
                </template>
            </DataTable>

            <!-- Pagination component -->
            <Pagination :links="props.pembelians.links" />
        </div>

        <!-- View Purchase Modal -->
        <Modal :show="showViewModal" @close="closeViewModal" max-width="5xl">
            <div
                class="p-3 sm:p-4 md:p-6 border border-gray-700 rounded-lg bg-dark-card max-h-[80vh] overflow-y-auto"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white sm:text-xl">
                        Purchase Details
                    </h3>
                    <button
                        @click="closeViewModal"
                        class="text-gray-400 hover:text-white"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5 sm:w-6 sm:h-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Loading state -->
                <div v-if="isLoading" class="flex justify-center py-6 sm:py-8">
                    <div
                        class="w-8 h-8 border-4 rounded-full sm:w-10 sm:h-10 animate-spin border-primary border-t-transparent"
                    ></div>
                </div>

                <!-- Purchase details -->
                <div v-else-if="selectedPurchase" class="space-y-4">
                    <!-- Basic info section -->
                    <div
                        class="p-4 border border-gray-700 rounded-lg bg-dark-lighter"
                    >
                        <h4 class="mb-3 text-lg font-medium text-primary">
                            Basic Information
                        </h4>
                        <div
                            class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
                        >
                            <!-- Order ID -->
                            <div>
                                <p class="text-sm text-gray-400">Order ID</p>
                                <p class="font-medium text-white">
                                    {{ selectedPurchase.order_id }}
                                </p>
                            </div>

                            <!-- Order Type -->
                            <div>
                                <p class="text-sm text-gray-400">Order Type</p>
                                <p class="font-medium text-white">
                                    {{ selectedPurchase.order_type }}
                                </p>
                            </div>

                            <!-- Status -->
                            <div>
                                <p class="text-sm text-gray-400">Status</p>
                                <p>
                                    <span
                                        :class="{
                                            'bg-green-500/20 text-green-400':
                                                selectedPurchase.status ===
                                                'completed',
                                            'bg-gray-500/20 text-gray-400':
                                                selectedPurchase.status ===
                                                'pending',
                                            'bg-blue-500/20 text-blue-400':
                                                selectedPurchase.status ===
                                                'processing',
                                            'bg-red-500/20 text-red-400': [
                                                'failed',
                                                'cancelled',
                                            ].includes(selectedPurchase.status),
                                        }"
                                        class="px-2 py-1 text-xs rounded-full"
                                    >
                                        {{ selectedPurchase.status }}
                                    </span>
                                </p>
                            </div>

                            <!-- Date Created -->
                            <div>
                                <p class="text-sm text-gray-400">Created At</p>
                                <p class="font-medium text-white">
                                    {{
                                        formatDate(selectedPurchase.created_at)
                                    }}
                                </p>
                            </div>

                            <!-- Date Updated -->
                            <div>
                                <p class="text-sm text-gray-400">Last Update</p>
                                <p class="font-medium text-white">
                                    {{
                                        formatDate(selectedPurchase.updated_at)
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Customer & Service info -->
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <!-- Customer info section -->
                        <div
                            class="p-4 border border-gray-700 rounded-lg bg-dark-lighter"
                        >
                            <h4 class="mb-3 text-lg font-medium text-secondary">
                                Customer Information
                            </h4>
                            <div class="space-y-3">
                                <div v-if="selectedPurchase.user">
                                    <p class="text-sm text-gray-400">
                                        Username
                                    </p>
                                    <p class="font-medium text-white">
                                        {{ selectedPurchase.user.username }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400">
                                        Nickname
                                    </p>
                                    <p class="font-medium text-white">
                                        {{ selectedPurchase.nickname || "N/A" }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400">User ID</p>
                                    <p class="font-medium text-white">
                                        {{ selectedPurchase.input_id }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400">
                                        Zone/Server
                                    </p>
                                    <p class="font-medium text-white">
                                        {{
                                            selectedPurchase.input_zone || "N/A"
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Service info section -->
                        <div
                            class="p-4 border border-gray-700 rounded-lg bg-dark-lighter"
                        >
                            <h4 class="mb-3 text-lg font-medium text-primary">
                                Service Information
                            </h4>
                            <div class="space-y-3">
                                <div v-if="selectedPurchase.layanan">
                                    <p class="text-sm text-gray-400">
                                        Service Name
                                    </p>
                                    <p class="font-medium text-white">
                                        {{
                                            selectedPurchase.layanan
                                                .nama_layanan
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400">Price</p>
                                    <p class="font-medium text-white">
                                        Rp
                                        {{
                                            Number(
                                                selectedPurchase.price
                                            ).toLocaleString("id-ID")
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400">Profit</p>
                                    <p
                                        :class="
                                            selectedPurchase.profit >= 0
                                                ? 'text-green-400'
                                                : 'text-red-400'
                                        "
                                        class="font-bold"
                                    >
                                        Rp
                                        {{
                                            Number(
                                                selectedPurchase.profit
                                            ).toLocaleString("id-ID")
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment info -->
                    <div
                        class="p-4 border border-gray-700 rounded-lg bg-dark-lighter"
                    >
                        <h4 class="mb-3 text-lg font-medium text-secondary">
                            Payment Information
                        </h4>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div v-if="selectedPurchase.pembayaran">
                                <p class="text-sm text-gray-400">
                                    Payment Method
                                </p>
                                <p class="font-medium text-white">
                                    {{
                                        selectedPurchase.pembayaran
                                            .payment_method || "N/A"
                                    }}
                                </p>
                            </div>
                            <div v-if="selectedPurchase.pembayaran">
                                <p class="text-sm text-gray-400">
                                    Payment Reference
                                </p>
                                <p class="font-medium text-white">
                                    {{
                                        selectedPurchase.pembayaran
                                            .payment_reference || "N/A"
                                    }}
                                </p>
                            </div>
                            <div v-if="selectedPurchase.pembayaran">
                                <p class="text-sm text-gray-400">
                                    Payment Status
                                </p>
                                <p>
                                    <span
                                        :class="{
                                            'bg-green-500/20 text-green-400':
                                                selectedPurchase.pembayaran
                                                    .status === 'paid',
                                            'bg-gray-500/20 text-gray-400':
                                                selectedPurchase.pembayaran
                                                    .status === 'pending',
                                            'bg-red-500/20 text-red-400': [
                                                'failed',
                                                'cancelled',
                                            ].includes(
                                                selectedPurchase.pembayaran
                                                    .status
                                            ),
                                        }"
                                        class="px-2 py-1 text-xs rounded-full"
                                    >
                                        {{ selectedPurchase.pembayaran.status }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div
                        class="flex flex-col justify-end pt-4 space-y-2 sm:flex-row sm:space-y-0 sm:space-x-3"
                    >
                        <!-- Process button (only for pending purchases) -->
                        <button
                            v-if="selectedPurchase.status === 'pending'"
                            @click="handleProcess(selectedPurchase)"
                            :disabled="processingPurchase"
                            class="w-full px-4 py-2 text-white transition-all duration-200 rounded-lg shadow-lg sm:w-auto bg-primary hover:bg-primary-hover hover:shadow-glow-primary"
                        >
                            <span v-if="processingPurchase">Processing...</span>
                            <span v-else>Mark as Completed</span>
                        </button>

                        <!-- Delete button -->
                        <button
                            @click="handleDelete(selectedPurchase)"
                            class="w-full px-4 py-2 text-white transition-all duration-200 bg-red-500 rounded-lg shadow-lg hover:bg-red-600 sm:w-auto"
                        >
                            Delete Purchase
                        </button>

                        <!-- Close button -->
                        <button
                            @click="closeViewModal"
                            class="w-full px-4 py-2 text-gray-300 rounded-lg bg-dark-lighter hover:text-white sm:w-auto"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

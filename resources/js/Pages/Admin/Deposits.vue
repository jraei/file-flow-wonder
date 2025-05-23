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
    deposits: Object,
    errors: Object,
    filters: Object,
    payMethods: Array,
    user: Array,
});

const { proxy } = getCurrentInstance();

// Deposits data from props
const deposits = computed(() => props.deposits.data || []);

// Column definitions for the table
const columns = [
    { key: "deposit_id", label: "Deposit ID" },
    {
        key: "user",
        label: "Username",
        format: (value, item) => {
            return value.username;
        },
    },
    {
        key: "pay_method",
        label: "Payment Method",
        format: (value, item) => {
            return value.nama;
        },
    },
    {
        key: "amount",
        label: "Amount",
        format: (value) => {
            return new Intl.NumberFormat("id-ID", {
                style: "currency",
                currency: "IDR",
                minimumFractionDigits: 0,
            }).format(value);
        },
    },
    {
        key: "status",
        label: "Status",
        format: (value) => {
            const statusClasses = getStatusClass(value);
            return `<span class="${statusClasses} px-2 py-1 rounded-xl text-xs">${value}</span>`;
        },
    },
];

// Helper function to get status classes
const getStatusClass = (status) => {
    switch (status) {
        case "paid":
            return "bg-green-100 text-green-800";
        case "failed":
            return "bg-red-100 text-red-800";
        case "cancelled":
            return "bg-gray-100 text-gray-800";
        default:
            return "bg-yellow-100 text-yellow-800"; // pending
    }
};

// Format date helper function
const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("id-ID", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

// Format date and time helper function
const formatDateTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("id-ID", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

// View modal states
const showViewModal = ref(false);
const selectedDeposit = ref(null);
const isLoading = ref(false);

// Handle view action
const handleView = async (item) => {
    isLoading.value = true;
    selectedDeposit.value = { ...item, loading: true };
    showViewModal.value = true;

    try {
        const response = await axios.get(route("deposits.show", item.id));
        selectedDeposit.value = response.data.deposit;
    } catch (error) {
        console.error("Error fetching deposit details:", error);
        proxy.$showSwalConfirm({
            title: "Error",
            text: "Failed to load deposit details",
            icon: "error",
        });
    } finally {
        isLoading.value = false;
    }
};

const handleDelete = (item) => {
    proxy.$showSwalConfirm({
        onConfirm: () => {
            // Delete deposit logic
            router.delete(route("deposits.destroy", item.id), {
                preserveScroll: true,
            });
        },
    });
};

// Form modal states
const showForm = ref(false);
const currentDeposit = ref(null);

const closeForm = () => {
    showForm.value = false;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedDeposit.value = null;
};
</script>

<template>
    <Head title="Deposits" />

    <AdminLayout title="Deposits">
        <div
            v-if="Object.keys(errors).length > 0"
            class="px-4 py-3 mb-4 text-sm text-white rounded-lg bg-red-500/80"
        >
            <ul v-for="error in errors">
                <li>{{ error }}</li>
            </ul>
        </div>
        <div
            class="w-full border rounded-lg shadow-lg border-primary/40 bg-dark-card"
        >
            <DataTable
                :data="deposits"
                :columns="columns"
                :filters="filters"
                route="deposits.index"
                class="max-w-full whitespace-nowrap"
            >
                <template #title> Deposits </template>

                <template #addButton>
                    <button
                        @click="openAddForm"
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
                        <span>Add Deposit</span>
                    </button>
                </template>

                <template #actions="{ item }">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                class="inline-flex items-center justify-center p-2 text-gray-400 transition-colors rounded-full hover:text-white hover:bg-dark-lighter"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-5 h-5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"
                                    />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <div
                                class="py-1 border rounded-lg shadow-lg border-primary/60 bg-dark-card"
                            >
                                <button
                                    @click="handleView(item)"
                                    class="block w-full px-4 py-2 text-sm text-left text-gray-300 hover:bg-dark-lighter hover:text-secondary"
                                >
                                    View
                                </button>
                                <button
                                    @click="handleDelete(item)"
                                    class="block w-full px-4 py-2 text-sm text-left text-gray-300 hover:bg-dark-lighter hover:text-red-400"
                                >
                                    Delete
                                </button>
                            </div>
                        </template>
                    </Dropdown>
                </template>
            </DataTable>
            <!-- Pagination component -->
            <Pagination :links="props.deposits.links" />
        </div>

        <!-- View Deposit Modal -->
        <Modal :show="showViewModal" @close="closeViewModal" max-width="2xl">
            <div
                class="p-3 sm:p-4 md:p-6 border border-gray-700 rounded-lg bg-dark-card max-h-[80vh] overflow-y-auto"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white sm:text-xl">
                        Deposit Details
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

                <div v-if="isLoading" class="flex justify-center py-6 sm:py-8">
                    <div
                        class="w-8 h-8 border-4 rounded-full sm:w-10 sm:h-10 animate-spin border-primary border-t-transparent"
                    ></div>
                </div>

                <div v-else-if="selectedDeposit" class="space-y-3 sm:space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Deposit ID
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedDeposit.deposit_id }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Username
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedDeposit.user.username }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Payment Method
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedDeposit.pay_method.nama }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Provider Reference
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedDeposit.provider_reference }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Amount
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{
                                    new Intl.NumberFormat("id-ID", {
                                        style: "currency",
                                        currency: "IDR",
                                        minimumFractionDigits: 0,
                                    }).format(selectedDeposit.amount)
                                }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Status
                            </p>
                            <p>
                                <span
                                    :class="
                                        getStatusClass(selectedDeposit.status)
                                    "
                                    class="px-2 py-1 text-xs rounded-full"
                                >
                                    {{ selectedDeposit.status }}
                                </span>
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Created At
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ formatDateTime(selectedDeposit.created_at) }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Updated At
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ formatDateTime(selectedDeposit.updated_at) }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex flex-col justify-end pt-3 space-y-2 sm:flex-row sm:pt-4 sm:space-y-0 sm:space-x-3"
                    >
                        <button
                            @click="closeViewModal"
                            class="w-full px-4 py-2 text-gray-300 rounded-lg sm:w-auto bg-dark-lighter hover:text-white"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

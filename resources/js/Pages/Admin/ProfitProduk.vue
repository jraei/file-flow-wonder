<script setup>
import { ref, computed, watch, getCurrentInstance } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import Modal from "@/Components/Modal.vue";
import Dropdown from "@/Components/Dropdown.vue";
import Pagination from "@/Components/Pagination.vue";
import PricePreview from "@/Components/PricePreview.vue";
import axios from "axios";

const props = defineProps({
    profitProduks: Object,
    products: Array,
    roles: Array,
    errors: Object,
    filters: Object,
});

// Data
const profitProduks = computed(() => props.profitProduks.data || []);
const services = ref([]);

const { proxy } = getCurrentInstance();

// Column definitions for the table
const columns = [
    { key: "id", label: "ID" },
    {
        key: "produk_name",
        label: "Product",
        format: (_, item) => {
            return `
                <div class="flex items-center space-x-2">
                    <img src="/storage/${item.produk.thumbnail}" alt="${item.produk.nama}" class="w-8 h-8 rounded-sm object-cover">
                    <span>${item.produk.nama}</span>
                </div>
            `;
        },
    },
    {
        key: "role_name",
        label: "User Role",
        format: (_, item) => {
            return `<span>${item.user_role.display_name}</span>`;
        },
    },
    {
        key: "type",
        label: "Type",
        format: (value) => {
            return `<span class="px-2 py-1 rounded-xl text-xs 
                    ${
                        value === "percent"
                            ? "bg-blue-500/20 text-blue-400"
                            : "bg-purple-500/20 text-purple-400"
                    }">
                    ${value === "percent" ? "Percentage" : "Multiplier"}
                </span>`;
        },
    },
    {
        key: "value",
        label: "Value",
        format: (value, item) => {
            return `<span>${value}${
                item.type === "percent" ? "%" : "x"
            }</span>`;
        },
    },
];

// View modal states
const showViewModal = ref(false);
const selectedProfitProduk = ref(null);
const isLoading = ref(false);

// Handle view action
const handleView = async (item) => {
    isLoading.value = true;
    selectedProfitProduk.value = { ...item, loading: true };
    showViewModal.value = true;

    try {
        const response = await axios.get(route("profit-produks.show", item.id));
        selectedProfitProduk.value = response.data.profitProduk;

        // Load services for this product to preview pricing
        if (selectedProfitProduk.value.produk_id) {
            loadServicesForProduct(selectedProfitProduk.value.produk_id);
        }
    } catch (error) {
        console.error("Error fetching profit produk details:", error);
        proxy.$showSwalConfirm({
            title: "Error",
            text: "Failed to load profit setting details",
            icon: "error",
        });
    } finally {
        isLoading.value = false;
    }
};

const handleEdit = (item) => {
    openEditForm(item);
};

const handleDelete = (item) => {
    proxy.$showSwalConfirm({
        onConfirm: () => {
            router.delete(route("profit-produks.destroy", item.id), {
                preserveScroll: true,
            });
        },
    });
};

// Form modal states
const showForm = ref(false);
const formMode = ref("add"); // 'add' or 'edit'
const currentProfitProduk = ref(null);

const openAddForm = () => {
    formMode.value = "add";
    currentProfitProduk.value = {
        produk_id: "",
        user_roles_id: "",
        type: "percent",
        value: 1.0,
    };
    showForm.value = true;
};

const openEditForm = (profitProduk) => {
    if (showViewModal.value) {
        showViewModal.value = false;
    }
    formMode.value = "edit";
    currentProfitProduk.value = {
        id: profitProduk.id,
        produk_id: profitProduk.produk_id,
        user_roles_id: profitProduk.user_roles_id,
        type: profitProduk.type,
        value: profitProduk.value,
    };
    loadServicesForProduct(profitProduk.produk_id);
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedProfitProduk.value = null;
    services.value = [];
};

const saveProfitProduk = () => {
    if (formMode.value === "add") {
        router.post(route("profit-produks.store"), currentProfitProduk.value, {
            preserveScroll: true,
            onSuccess: () => {
                closeForm();
            },
        });
    } else {
        router.put(
            route("profit-produks.update", currentProfitProduk.value.id),
            currentProfitProduk.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeForm();
                },
            }
        );
    }
};

// Load services for a product to preview pricing
const loadServicesForProduct = async (productId) => {
    if (!productId) {
        services.value = [];
        return;
    }

    try {
        // In a real app, you would make an API call to fetch services
        // For now, we'll simulate with mock data
        services.value = [
            {
                id: 1,
                layanan: "60 Diamonds",
                kode_produk: "ML60",
                harga_beli: 12000,
            },
            {
                id: 2,
                layanan: "120 Diamonds",
                kode_produk: "ML120",
                harga_beli: 24000,
            },
            {
                id: 3,
                layanan: "240 Diamonds",
                kode_produk: "ML240",
                harga_beli: 46000,
            },
        ];
    } catch (error) {
        console.error("Error loading services:", error);
        services.value = [];
    }
};

// Calculate price based on profit setting
const calculatePrice = (basePrice) => {
    if (!currentProfitProduk.value) return basePrice;

    const value = parseFloat(currentProfitProduk.value.value);

    if (currentProfitProduk.value.type === "percent") {
        return basePrice + (basePrice * value) / 100;
    } else {
        // multiplier
        return basePrice * value;
    }
};

// Watch for product changes to load services
watch(
    () => currentProfitProduk.value?.produk_id,
    (newValue) => {
        if (newValue) {
            loadServicesForProduct(newValue);
        }
    }
);
</script>

<template>
    <Head title="Profit Products" />

    <AdminLayout title="Profit Product Settings">
        <div
            v-if="Object.keys(errors || {}).length > 0"
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
                :data="profitProduks"
                :columns="columns"
                :filters="filters"
                route="profit-produks.index"
                class="max-w-full whitespace-nowrap"
            >
                <template #title> Profit Product Settings </template>

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
                        <span>Add Profit Setting</span>
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
                                    @click="handleEdit(item)"
                                    class="block w-full px-4 py-2 text-sm text-left text-gray-300 hover:bg-dark-lighter hover:text-primary"
                                >
                                    Edit
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
            <Pagination :links="props.profitProduks.links" />
        </div>

        <!-- Price Preview Component -->
        <PricePreview :products="products" />

        <!-- Add/Edit Form Modal -->
        <div
            v-if="showForm"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black bg-opacity-50"
            @click.self="closeForm"
        >
            <div
                class="relative w-full max-w-md mx-4 p-3 border border-gray-700 rounded-lg shadow-lg sm:p-4 md:p-6 md:max-w-xl lg:max-w-2xl bg-dark-card max-h-[90vh] overflow-y-auto"
                @click.stop
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-white">
                        {{
                            formMode === "add"
                                ? "Add New Profit Setting"
                                : "Edit Profit Setting"
                        }}
                    </h3>
                    <button
                        @click="closeForm"
                        class="text-gray-400 hover:text-white"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-6 h-6"
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

                <form
                    @submit.prevent="saveProfitProduk"
                    class="overflow-visible"
                >
                    <div class="space-y-3 sm:space-y-4">
                        <!-- Product Selection -->
                        <div>
                            <label
                                for="produk_id"
                                class="block mb-1 text-sm font-medium text-gray-300"
                            >
                                Product
                            </label>
                            <select
                                id="produk_id"
                                v-model="currentProfitProduk.produk_id"
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                required
                            >
                                <option value="">Select Product</option>
                                <option
                                    v-for="product in products"
                                    :key="product.id"
                                    :value="product.id"
                                >
                                    {{ product.nama }}
                                </option>
                            </select>
                        </div>

                        <!-- Role Selection -->
                        <div>
                            <label
                                for="user_roles_id"
                                class="block mb-1 text-sm font-medium text-gray-300"
                            >
                                User Role
                            </label>
                            <select
                                id="user_roles_id"
                                v-model="currentProfitProduk.user_roles_id"
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                required
                            >
                                <option value="">Select Role</option>
                                <option
                                    v-for="role in roles"
                                    :key="role.id"
                                    :value="role.id"
                                >
                                    {{ role.display_name }}
                                </option>
                            </select>
                        </div>

                        <div
                            class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4"
                        >
                            <!-- Profit Type -->
                            <div>
                                <label
                                    for="type"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                >
                                    Profit Type
                                </label>
                                <select
                                    id="type"
                                    v-model="currentProfitProduk.type"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    required
                                >
                                    <option value="percent">
                                        Percentage (%)
                                    </option>
                                    <option value="multiplier">
                                        Multiplier (x)
                                    </option>
                                </select>
                            </div>

                            <!-- Value Input -->
                            <div>
                                <label
                                    for="value"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                >
                                    Value
                                </label>
                                <div class="relative">
                                    <input
                                        id="value"
                                        v-model="currentProfitProduk.value"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                        required
                                    />
                                    <span
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 pointer-events-none"
                                    >
                                        {{
                                            currentProfitProduk.type ===
                                            "percent"
                                                ? "%"
                                                : "x"
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Service Prices Table -->
                        <div v-if="services.length > 0" class="mt-4">
                            <h4 class="mb-2 text-sm font-medium text-gray-300">
                                Price Preview
                            </h4>
                            <div class="overflow-x-auto">
                                <table
                                    class="w-full text-sm text-left text-gray-300"
                                >
                                    <thead
                                        class="text-xs text-gray-400 uppercase bg-dark-lighter"
                                    >
                                        <tr>
                                            <th class="px-4 py-3">Service</th>
                                            <th class="px-4 py-3">
                                                Base Price
                                            </th>
                                            <th class="px-4 py-3">
                                                Calculated Price
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="service in services"
                                            :key="service.id"
                                            class="border-b border-gray-700"
                                        >
                                            <td class="px-4 py-2">
                                                {{ service.layanan }}
                                            </td>
                                            <td class="px-4 py-2">
                                                {{ service.harga_beli }}
                                            </td>
                                            <td
                                                class="px-4 py-2 font-medium text-primary"
                                            >
                                                {{
                                                    calculatePrice(
                                                        service.harga_beli
                                                    ).toFixed(2)
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div
                            class="flex flex-col justify-end pt-3 space-y-2 sm:flex-row sm:pt-4 sm:space-y-0 sm:space-x-3"
                        >
                            <button
                                type="button"
                                @click="closeForm"
                                class="w-full px-4 py-2 text-gray-300 rounded-lg bg-dark-lighter hover:text-white sm:w-auto"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="w-full px-4 py-2 text-white transition-all duration-200 rounded-lg shadow-lg bg-primary hover:bg-primary-hover hover:shadow-glow-primary sm:w-auto"
                            >
                                {{
                                    formMode === "add"
                                        ? "Add Profit Setting"
                                        : "Update Profit Setting"
                                }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- View Modal -->
        <Modal :show="showViewModal" @close="closeViewModal" max-width="2xl">
            <div
                class="p-3 sm:p-4 md:p-6 border border-gray-700 rounded-lg bg-dark-card max-h-[80vh] overflow-y-auto"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white sm:text-xl">
                        Profit Setting Details
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

                <div
                    v-else-if="selectedProfitProduk"
                    class="space-y-3 sm:space-y-4"
                >
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Product Info -->
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Product
                            </p>
                            <div class="flex items-center space-x-2 mt-1">
                                <img
                                    :src="
                                        '/storage/' +
                                        selectedProfitProduk.produk.thumbnail
                                    "
                                    :alt="selectedProfitProduk.produk.nama"
                                    class="w-8 h-8 rounded-sm object-cover"
                                />
                                <p
                                    class="text-sm font-medium text-white truncate sm:text-base"
                                >
                                    {{ selectedProfitProduk.produk.nama }}
                                </p>
                            </div>
                        </div>

                        <!-- Role Info -->
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                User Role
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{
                                    selectedProfitProduk.user_role.display_name
                                }}
                            </p>
                        </div>

                        <!-- Profit Type -->
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Profit Type
                            </p>
                            <p>
                                <span
                                    :class="
                                        selectedProfitProduk.type === 'percent'
                                            ? 'bg-blue-500/20 text-blue-400'
                                            : 'bg-purple-500/20 text-purple-400'
                                    "
                                    class="px-2 py-1 text-xs rounded-full"
                                >
                                    {{
                                        selectedProfitProduk.type === "percent"
                                            ? "Percentage"
                                            : "Multiplier"
                                    }}
                                </span>
                            </p>
                        </div>

                        <!-- Value -->
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Value
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedProfitProduk.value
                                }}{{
                                    selectedProfitProduk.type === "percent"
                                        ? "%"
                                        : "x"
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Service Preview -->
                    <div v-if="services.length > 0" class="mt-4">
                        <h4 class="mb-2 text-sm font-medium text-gray-300">
                            Price Preview
                        </h4>
                        <div class="overflow-x-auto">
                            <table
                                class="w-full text-sm text-left text-gray-300"
                            >
                                <thead
                                    class="text-xs text-gray-400 uppercase bg-dark-lighter"
                                >
                                    <tr>
                                        <th class="px-4 py-3">Service</th>
                                        <th class="px-4 py-3">Base Price</th>
                                        <th class="px-4 py-3">
                                            Calculated Price
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="service in services"
                                        :key="service.id"
                                        class="border-b border-gray-700"
                                    >
                                        <td class="px-4 py-2">
                                            {{ service.layanan }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ service.harga_beli }}
                                        </td>
                                        <td
                                            class="px-4 py-2 font-medium text-primary"
                                        >
                                            {{
                                                calculatePrice(
                                                    service.harga_beli
                                                ).toFixed(2)
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div
                        class="flex flex-col justify-end pt-3 space-y-2 sm:flex-row sm:pt-4 sm:space-y-0 sm:space-x-3"
                    >
                        <button
                            @click="openEditForm(selectedProfitProduk)"
                            class="w-full px-4 py-2 text-white transition-all duration-200 rounded-lg shadow-lg sm:w-auto bg-primary hover:bg-primary-hover hover:shadow-glow-primary"
                        >
                            Edit Profit Setting
                        </button>
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

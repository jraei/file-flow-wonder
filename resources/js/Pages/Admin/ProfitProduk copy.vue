<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import Modal from "@/Components/Modal.vue";
import Dropdown from "@/Components/Dropdown.vue";
import Pagination from "@/Components/Pagination.vue";
import axios from "axios";

const props = defineProps({
    profitProduks: Object,
    products: Object,
    roles: Object,
    errors: Object,
    filters: Object,
});

const page = usePage();

const profitProduks = computed(() => props.profitProduks.data || []);
const showPriceModal = ref(false);
const selectedProductId = ref(null);
const pricePreview = ref(null);
const isLoadingPreview = ref(false);

const columns = [
    { key: "id", label: "ID" },
    {
        key: "produk_id",
        label: "Produk",
        format: (value, item) => {
            const product = props.products.find(
                (p) => Number(p.id) === Number(value)
            );
            return product ? product.nama : value;
        },
    },
    {
        key: "user_roles_id",
        label: "Role",
        format: (value, item) => {
            const role = props.roles.find(
                (r) => Number(r.id) === Number(value)
            );
            return role ? role.display_name : value;
        },
    },
    { key: "type", label: "Type" },
    {
        key: "value",
        label: "Value",
        format: (value, item) => {
            if (item.type === "percent") {
                return `${value}%`;
            }
            return value;
        },
    },
];

const showForm = ref(false);
const formMode = ref("add");
const currentData = ref({
    produk_id: "",
    user_roles_id: "",
    type: "percent",
    value: "",
});

// For bulk operations
const showBulkForm = ref(false);
const bulkData = ref({
    product_ids: [],
    user_roles_id: "",
    type: "percent",
    value: "",
});
const isProcessingBulk = ref(false);
const selectedProducts = ref([]);
const selectedProductsDetails = ref([]);

onMounted(() => {
    // Check if we're coming from the Products page with selected products
    const storedProductIds = localStorage.getItem("selectedProductIds");

    if (storedProductIds && page.url.includes("bulk_edit=true")) {
        try {
            const productIds = JSON.parse(storedProductIds);
            if (Array.isArray(productIds) && productIds.length > 0) {
                bulkData.value.product_ids = productIds;
                fetchSelectedProductDetails(productIds);
                showBulkForm.value = true;
            }
            // Clear the localStorage after use
            localStorage.removeItem("selectedProductIds");
        } catch (e) {
            console.error("Error parsing stored product IDs:", e);
        }
    }
});

const fetchSelectedProductDetails = async (productIds) => {
    try {
        // Assuming you have an endpoint to fetch products by IDs
        const productDetails = props.products.filter((product) =>
            productIds.includes(product.id)
        );

        selectedProductsDetails.value = productDetails;
    } catch (error) {
        console.error("Error fetching product details:", error);
    }
};

const openForm = () => {
    formMode.value = "add";
    currentData.value = {
        produk_id: "",
        user_roles_id: "",
        type: "percent",
        value: "",
    };
    showForm.value = true;
};

const openEditForm = (item) => {
    formMode.value = "edit";
    currentData.value = { ...item };
    showForm.value = true;
};

const openBulkForm = () => {
    bulkData.value = {
        product_ids: [],
        user_roles_id: "",
        type: "percent",
        value: "",
    };
    showBulkForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
    showBulkForm.value = false;
};

const loadPricePreview = async (productId) => {
    isLoadingPreview.value = true;
    selectedProductId.value = productId;
    pricePreview.value = null;
    showPriceModal.value = true;

    try {
        const response = await axios.post(
            route("profit-produks.preview"),
            {
                produk_id: productId,
            },
            {
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
            }
        );

        if (response.data.success) {
            pricePreview.value = response.data;
        } else {
            console.error("Error loading price preview:", response.data);
        }
    } catch (error) {
        console.error("Error loading price preview:", error);
    } finally {
        isLoadingPreview.value = false;
    }
};

const saveForm = () => {
    if (formMode.value === "add") {
        router.post(route("profit-produks.store"), currentData.value, {
            preserveScroll: true,
        });
    } else {
        router.put(
            route("profit-produks.update", currentData.value.id),
            currentData.value,
            {
                preserveScroll: true,
            }
        );
    }

    closeForm();
};

const submitBulkUpdate = async () => {
    if (
        !bulkData.value.user_roles_id ||
        !bulkData.value.type ||
        !bulkData.value.value ||
        bulkData.value.product_ids.length === 0
    ) {
        // Show validation error
        Swal.fire({
            icon: "error",
            title: "Validation Error",
            text: "Please fill in all required fields",
        });
        return;
    }

    isProcessingBulk.value = true;

    try {
        const response = await axios.post(
            route("profit-produks.bulkUpdate"),
            bulkData.value
        );

        if (response.data.success) {
            // Show success animation with supernova effect
            Swal.fire({
                icon: "success",
                title: "Success!",
                text: response.data.message,
                background: "#1f2937",
                color: "#fff",
                iconColor: "#9b87f5",
                showConfirmButton: false,
                timer: 2000,
                showClass: {
                    popup: "animate__animated animate__fadeInDown",
                },
                hideClass: {
                    popup: "animate__animated animate__fadeOutUp",
                },
                didOpen: () => {
                    // Add custom supernova effect
                    const swalIcon = document.querySelector(".swal2-icon");
                    if (swalIcon) {
                        swalIcon.classList.add("supernova-effect");
                        const style = document.createElement("style");
                        style.innerHTML = `
                            .supernova-effect {
                                animation: pulse-glow 1.5s infinite alternate;
                                box-shadow: 0 0 15px #9b87f5, 0 0 30px #9b87f5;
                            }
                            @keyframes pulse-glow {
                                0% {
                                    box-shadow: 0 0 5px #9b87f5, 0 0 10px #9b87f5;
                                    transform: scale(1);
                                }
                                100% {
                                    box-shadow: 0 0 20px #9b87f5, 0 0 40px #9b87f5;
                                    transform: scale(1.05);
                                }
                            }
                        `;
                        document.head.appendChild(style);
                    }
                },
            });

            // Refresh data
            router.reload();
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text:
                    response.data.message || "Failed to update profit settings",
                background: "#1f2937",
                color: "#fff",
            });
        }
    } catch (error) {
        console.error("Error updating profit settings:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text:
                error.response?.data?.message || "An unexpected error occurred",
            background: "#1f2937",
            color: "#fff",
        });
    } finally {
        isProcessingBulk.value = false;
        closeForm();
    }
};

const handleDelete = (item) => {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#9b87f5",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
        background: "#1f2937",
        color: "#fff",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route("profit-produks.destroy", item.id), {
                preserveScroll: true,
            });
        }
    });
};
</script>

<template>
    <Head title="Profit Produk" />

    <AdminLayout title="Profit Produk">
        <div class="p-4 mb-4 rounded-lg bg-dark-card">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-start gap-3">
                    <div class="flex-grow max-w-xs">
                        <label
                            for="product_filter"
                            class="block mb-1 text-sm font-medium text-gray-300"
                            >Product</label
                        >
                        <select
                            id="product_filter"
                            v-model="filters.product"
                            @change="
                                router.get(
                                    route('profit-produks.index'),
                                    {
                                        product: filters.product,
                                        role: filters.role,
                                        type: filters.type,
                                        search: filters.search,
                                        sort: filters.sort,
                                        direction: filters.direction,
                                    },
                                    {
                                        preserveState: true,
                                        replace: true,
                                    }
                                )
                            "
                            class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                            <option value="">All Products</option>
                            <option
                                v-for="product in products"
                                :key="product.id"
                                :value="product.id"
                            >
                                {{ product.nama }}
                            </option>
                        </select>
                    </div>

                    <div class="flex-grow max-w-xs">
                        <label
                            for="role_filter"
                            class="block mb-1 text-sm font-medium text-gray-300"
                            >Role</label
                        >
                        <select
                            id="role_filter"
                            v-model="filters.role"
                            @change="
                                router.get(
                                    route('profit-produks.index'),
                                    {
                                        product: filters.product,
                                        role: filters.role,
                                        type: filters.type,
                                        search: filters.search,
                                        sort: filters.sort,
                                        direction: filters.direction,
                                    },
                                    {
                                        preserveState: true,
                                        replace: true,
                                    }
                                )
                            "
                            class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                            <option value="">All Roles</option>
                            <option
                                v-for="role in roles"
                                :key="role.id"
                                :value="role.id"
                            >
                                {{ role.display_name }}
                            </option>
                        </select>
                    </div>

                    <div class="flex-grow max-w-xs">
                        <label
                            for="type_filter"
                            class="block mb-1 text-sm font-medium text-gray-300"
                            >Type</label
                        >
                        <select
                            id="type_filter"
                            v-model="filters.type"
                            @change="
                                router.get(
                                    route('profit-produks.index'),
                                    {
                                        product: filters.product,
                                        role: filters.role,
                                        type: filters.type,
                                        search: filters.search,
                                        sort: filters.sort,
                                        direction: filters.direction,
                                    },
                                    {
                                        preserveState: true,
                                        replace: true,
                                    }
                                )
                            "
                            class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                            <option value="">All Types</option>
                            <option value="percent">Percent</option>
                            <option value="multiplier">Multiplier</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 mt-2 sm:mt-0">
                    <button
                        @click="openBulkForm"
                        class="flex items-center px-3 py-2 space-x-2 text-white transition-all duration-200 rounded-lg shadow-lg bg-secondary hover:bg-secondary/80"
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
                        <span>Set Bulk Profit</span>
                    </button>
                </div>
            </div>
        </div>

        <div
            class="w-full border rounded-lg shadow-lg border-primary/40 bg-dark-card"
        >
            <div class="max-w-full overflow-x-auto">
                <DataTable
                    :data="profitProduks"
                    :columns="columns"
                    :filters="filters"
                    route="profit-produks.index"
                    class="w-full whitespace-nowrap"
                >
                    <template #title> Profit Settings </template>

                    <template #addButton>
                        <button
                            @click="openForm"
                            class="flex items-center px-3 py-2 space-x-2 text-white transition-all duration-200 rounded-lg shadow-lg sm:px-4 bg-primary hover:bg-primary-hover hover:shadow-glow-primary"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 sm:w-5 sm:h-5"
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
                            <span>Add Profit</span>
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
                                    class="py-1 border border-gray-700 rounded-lg shadow-lg bg-dark-card"
                                >
                                    <button
                                        @click="openEditForm(item)"
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
                                    <button
                                        @click="
                                            loadPricePreview(item.produk_id)
                                        "
                                        class="block w-full px-4 py-2 text-sm text-left text-gray-300 hover:bg-dark-lighter hover:text-blue-400"
                                    >
                                        Preview Prices
                                    </button>
                                </div>
                            </template>
                        </Dropdown>
                    </template>
                </DataTable>
            </div>
            <Pagination :links="profitProduks.links" />
        </div>

        <!-- Single Profit Form Modal -->
        <div
            v-if="showForm"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black/50"
            @click.self="closeForm"
        >
            <div
                class="relative w-full max-w-md p-6 mx-4 border border-gray-700 rounded-lg shadow-lg bg-dark-card"
                @click.stop
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white sm:text-xl">
                        {{ formMode === "add" ? "Add Profit" : "Edit Profit" }}
                    </h3>
                    <button
                        @click="closeForm"
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

                <form @submit.prevent="saveForm" class="space-y-4">
                    <div>
                        <label
                            for="produk_id"
                            class="block mb-1 text-sm font-medium text-gray-300"
                            >Product</label
                        >
                        <select
                            id="produk_id"
                            v-model="currentData.produk_id"
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

                    <div>
                        <label
                            for="user_roles_id"
                            class="block mb-1 text-sm font-medium text-gray-300"
                            >Role</label
                        >
                        <select
                            id="user_roles_id"
                            v-model="currentData.user_roles_id"
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

                    <div>
                        <label
                            for="type"
                            class="block mb-1 text-sm font-medium text-gray-300"
                            >Type</label
                        >
                        <select
                            id="type"
                            v-model="currentData.type"
                            class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                            required
                        >
                            <option value="percent">Percent (%)</option>
                            <option value="multiplier">Multiplier (x)</option>
                        </select>
                    </div>

                    <div>
                        <label
                            for="value"
                            class="block mb-1 text-sm font-medium text-gray-300"
                            >Value</label
                        >
                        <input
                            id="value"
                            v-model="currentData.value"
                            type="number"
                            step="0.01"
                            min="0.01"
                            class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                            :placeholder="
                                currentData.type === 'percent'
                                    ? 'Enter percentage'
                                    : 'Enter multiplier'
                            "
                            required
                        />
                    </div>

                    <div class="flex justify-end pt-3 space-x-3">
                        <button
                            type="button"
                            @click="closeForm"
                            class="px-4 py-2 text-gray-300 rounded-lg bg-dark-lighter hover:text-white"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-white transition-all duration-200 rounded-lg shadow-lg bg-primary hover:bg-primary-hover hover:shadow-glow-primary"
                        >
                            {{ formMode === "add" ? "Add" : "Save" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bulk Profit Modal -->
        <div
            v-if="showBulkForm"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black/70"
            @click.self="closeForm"
        >
            <div
                class="relative w-full max-w-3xl p-6 mx-4 border rounded-lg shadow-lg border-primary/40 bg-dark-card"
                @click.stop
                style="
                    background-image: url('https://images.unsplash.com/photo-1534796636912-3b95b3ab5986?q=80&w=1000');
                    background-size: cover;
                    background-position: center;
                "
            >
                <!-- Overlay for better text readability -->
                <div class="absolute inset-0 rounded-lg bg-black/70"></div>

                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-white">
                            Bulk Profit Assignment
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

                    <!-- Selected products display -->
                    <div v-if="selectedProductsDetails.length > 0" class="mb-6">
                        <h4 class="mb-3 text-lg font-medium text-blue-300">
                            Selected Products ({{
                                selectedProductsDetails.length
                            }})
                        </h4>
                        <div
                            class="p-3 overflow-y-auto border rounded-lg border-gray-700/50 bg-dark-sidebar/30 max-h-40"
                        >
                            <div
                                v-for="(
                                    product, index
                                ) in selectedProductsDetails"
                                :key="product.id"
                                class="flex items-center justify-between py-1 border-b border-gray-700/30 last:border-0"
                            >
                                <span class="text-gray-200">{{
                                    product.nama
                                }}</span>
                                <button
                                    @click="
                                        selectedProductsDetails.splice(
                                            index,
                                            1
                                        );
                                        bulkData.product_ids.splice(
                                            bulkData.product_ids.indexOf(
                                                product.id
                                            ),
                                            1
                                        );
                                    "
                                    class="text-gray-400 hover:text-red-400"
                                >
                                    <svg
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
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="submitBulkUpdate" class="space-y-4">
                        <!-- Add products if none are already selected -->
                        <div v-if="!bulkData.product_ids.length">
                            <label
                                for="bulk_product_ids"
                                class="block mb-1 text-sm font-medium text-gray-300"
                                >Products</label
                            >
                            <select
                                id="bulk_product_ids"
                                v-model="bulkData.product_ids"
                                multiple
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar/80 focus:ring-2 focus:ring-primary focus:border-transparent"
                                size="4"
                                required
                            >
                                <option
                                    v-for="product in products"
                                    :key="product.id"
                                    :value="product.id"
                                >
                                    {{ product.nama }}
                                </option>
                            </select>
                            <span class="text-xs text-gray-400"
                                >Hold Ctrl/Cmd to select multiple</span
                            >
                        </div>

                        <div>
                            <label
                                for="bulk_user_roles_id"
                                class="block mb-1 text-sm font-medium text-gray-300"
                                >Role</label
                            >
                            <select
                                id="bulk_user_roles_id"
                                v-model="bulkData.user_roles_id"
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar/80 focus:ring-2 focus:ring-primary focus:border-transparent"
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

                        <div>
                            <label
                                for="bulk_type"
                                class="block mb-1 text-sm font-medium text-gray-300"
                                >Type</label
                            >
                            <select
                                id="bulk_type"
                                v-model="bulkData.type"
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar/80 focus:ring-2 focus:ring-primary focus:border-transparent"
                                required
                            >
                                <option value="percent">Percent (%)</option>
                                <option value="multiplier">
                                    Multiplier (x)
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                for="bulk_value"
                                class="block mb-1 text-sm font-medium text-gray-300"
                                >Value</label
                            >
                            <div class="flex space-x-2">
                                <input
                                    id="bulk_value"
                                    v-model="bulkData.value"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar/80 focus:ring-2 focus:ring-primary focus:border-transparent"
                                    :placeholder="
                                        bulkData.type === 'percent'
                                            ? 'Enter percentage'
                                            : 'Enter multiplier'
                                    "
                                    required
                                />
                                <span
                                    class="flex items-center justify-center w-10 h-10 text-lg font-bold text-white rounded-full bg-primary/50"
                                >
                                    {{
                                        bulkData.type === "percent" ? "%" : "x"
                                    }}
                                </span>
                            </div>

                            <!-- Value slider for visual adjustment -->
                            <div class="mt-2">
                                <input
                                    v-if="bulkData.type === 'percent'"
                                    type="range"
                                    min="1"
                                    max="100"
                                    step="1"
                                    v-model.number="bulkData.value"
                                    class="w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer"
                                />
                                <input
                                    v-else
                                    type="range"
                                    min="1"
                                    max="5"
                                    step="0.1"
                                    v-model.number="bulkData.value"
                                    class="w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer"
                                />
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 space-x-3">
                            <button
                                type="button"
                                @click="closeForm"
                                class="px-4 py-2 text-gray-300 rounded-lg bg-dark-lighter/80 hover:text-white"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="flex items-center px-4 py-2 space-x-2 text-white transition-all duration-200 rounded-lg shadow-lg bg-primary hover:bg-primary-hover hover:shadow-glow-primary"
                                :disabled="isProcessingBulk"
                            >
                                <svg
                                    v-if="isProcessingBulk"
                                    class="w-5 h-5 animate-spin"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
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
                                <span>Apply to All Selected Products</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Price Preview Modal -->
        <Modal
            :show="showPriceModal"
            @close="showPriceModal = false"
            max-width="4xl"
        >
            <div
                class="p-6 border border-gray-700 rounded-lg bg-dark-card max-h-[80vh] overflow-y-auto"
            >
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-white">Price Preview</h3>
                    <button
                        @click="showPriceModal = false"
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

                <div v-if="isLoadingPreview" class="flex justify-center py-8">
                    <div
                        class="w-10 h-10 border-4 rounded-full animate-spin border-primary border-t-transparent"
                    ></div>
                </div>

                <div v-else-if="pricePreview && pricePreview.success">
                    <div
                        class="flex flex-wrap items-center gap-4 p-4 mb-6 border border-gray-700 rounded-lg bg-dark-lighter"
                    >
                        <div
                            v-if="pricePreview.product.thumbnail"
                            class="flex-shrink-0"
                        >
                            <img
                                :src="pricePreview.product.thumbnail"
                                :alt="pricePreview.product.name"
                                class="object-cover w-16 h-16 rounded-lg sm:w-20 sm:h-20"
                            />
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-white">
                                {{ pricePreview.product.name }}
                            </h4>
                            <p
                                v-if="pricePreview.product.brand"
                                class="text-sm text-gray-400"
                            >
                                {{ pricePreview.product.brand }}
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-dark-lighter">
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase"
                                    >
                                        Service
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase"
                                    >
                                        Base Price
                                    </th>
                                    <th
                                        scope="col"
                                        colspan="2"
                                        v-for="(price, index) in pricePreview
                                            .pricing[0]?.role_prices"
                                        :key="'role-' + index"
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase"
                                    >
                                        {{ price.role_name }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-700 bg-dark-card"
                            >
                                <tr
                                    v-for="(
                                        service, index
                                    ) in pricePreview.pricing"
                                    :key="'service-' + index"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div
                                                    class="text-sm font-medium text-white"
                                                >
                                                    {{ service.name }}
                                                </div>
                                                <div
                                                    class="text-xs text-gray-400"
                                                >
                                                    {{ service.code }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-white whitespace-nowrap"
                                    >
                                        {{
                                            service.base_price.toLocaleString()
                                        }}
                                    </td>
                                    <template
                                        v-for="(
                                            price, priceIndex
                                        ) in service.role_prices"
                                        :key="'price-' + priceIndex"
                                    >
                                        <td
                                            class="px-6 py-4 text-sm whitespace-nowrap"
                                        >
                                            <div
                                                :class="[
                                                    'font-medium',
                                                    price.final_price >
                                                    service.base_price
                                                        ? 'text-green-400'
                                                        : price.final_price <
                                                          service.base_price
                                                        ? 'text-red-400'
                                                        : 'text-white',
                                                ]"
                                            >
                                                {{
                                                    price.final_price.toLocaleString()
                                                }}
                                            </div>
                                            <div
                                                v-if="price.profit_type"
                                                class="text-xs text-gray-400"
                                            >
                                                {{
                                                    price.profit_type ===
                                                    "percent"
                                                        ? "+" +
                                                          price.profit_value +
                                                          "%"
                                                        : "x" +
                                                          price.profit_value
                                                }}
                                            </div>
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm text-white whitespace-nowrap"
                                        >
                                            <div
                                                :class="[
                                                    'text-xs',
                                                    price.final_price >
                                                    service.base_price
                                                        ? 'text-green-400'
                                                        : price.final_price <
                                                          service.base_price
                                                        ? 'text-red-400'
                                                        : 'text-gray-400',
                                                ]"
                                            >
                                                {{
                                                    price.final_price >
                                                    service.base_price
                                                        ? "+" +
                                                          (
                                                              price.final_price -
                                                              service.base_price
                                                          ).toLocaleString()
                                                        : price.final_price <
                                                          service.base_price
                                                        ? "-" +
                                                          (
                                                              service.base_price -
                                                              price.final_price
                                                          ).toLocaleString()
                                                        : "0"
                                                }}
                                            </div>
                                        </td>
                                    </template>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div
                    v-else-if="pricePreview && !pricePreview.success"
                    class="p-4 text-center text-red-400"
                >
                    {{ pricePreview.message || "Failed to load price preview" }}
                </div>

                <div class="flex justify-end mt-6">
                    <button
                        @click="showPriceModal = false"
                        class="px-4 py-2 text-gray-300 rounded-lg bg-dark-lighter hover:text-white"
                    >
                        Close
                    </button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

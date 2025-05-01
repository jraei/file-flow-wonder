<script setup>
import { ref, computed, watch } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import Modal from "@/Components/Modal.vue";
import Dropdown from "@/Components/Dropdown.vue";
import Pagination from "@/Components/Pagination.vue";
import { Link } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
    fields: Object,
    products: Array,
    filters: Object,
    errors: Object,
});

// Table data
const inputFields = computed(() => props.fields.data || []);

// Column definitions for the table
const columns = [
    { key: "id", label: "ID" },
    { key: "product", label: "Product" },
    { key: "name", label: "Field Name" },
    { key: "label", label: "Label" },
    { key: "type", label: "Type" },
    { key: "required", label: "Required" },
    { key: "order", label: "Order" },
];

// Form modal states
const showForm = ref(false);
const formMode = ref("add");
const currentField = ref(null);

// Product filter state
const selectedProductId = ref(props.filters.product_id || "");

// Watch for product filter changes
watch(selectedProductId, (newValue) => {
    router.get(
        route("admin.produk-input-fields.index"),
        { product_id: newValue || null },
        {
            preserveState: true,
            replace: true,
        }
    );
});

const openAddForm = () => {
    formMode.value = "add";
    currentField.value = {
        produk_id: selectedProductId.value || "",
        name: "",
        label: "",
        type: "text",
        required: true,
        order: 0,
    };
    showForm.value = true;
};

const openEditForm = (field) => {
    formMode.value = "edit";
    // Create a copy to avoid direct mutation
    currentField.value = { ...field };
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
    currentField.value = null;
};

const saveField = () => {
    if (formMode.value === "add") {
        router.post(
            route("admin.produk-input-fields.store"),
            currentField.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeForm();
                },
            }
        );
    } else {
        router.put(
            route("admin.produk-input-fields.update", currentField.value.id),
            currentField.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeForm();
                },
            }
        );
    }
};

const getTypeClass = (type) => {
    switch (type) {
        case "text":
            return "bg-blue-500/20 text-blue-400";
        case "number":
            return "bg-green-500/20 text-green-400";
        case "select":
            return "bg-purple-500/20 text-purple-400";
        default:
            return "bg-gray-500/20 text-gray-400";
    }
};

const handleDelete = (item) => {
    // Show confirmation dialog before deleting
    if (
        window.confirm(
            "Are you sure you want to delete this field? All associated options will also be deleted."
        )
    ) {
        router.delete(route("admin.produk-input-fields.destroy", item.id), {
            preserveScroll: true,
        });
    }
};

const manageOptions = (field) => {
    if (field.type === "select") {
        router.get(
            route("admin.produk-input-options.index", { field_id: field.id })
        );
    } else {
        alert("Only select fields can have options");
    }
};
</script>

<template>
    <Head title="Product Input Fields" />

    <AdminLayout title="Product Input Fields">
        <div
            v-if="Object.keys(errors).length > 0"
            class="px-4 py-3 mb-4 text-sm text-white rounded-lg bg-red-500/80"
        >
            <ul>
                <li v-for="(error, key) in errors" :key="key">
                    {{ error }}
                </li>
            </ul>
        </div>

        <div class="flex items-center justify-between mb-4">
            <div class="w-64">
                <label
                    for="product_filter"
                    class="block mb-1 text-sm font-medium text-gray-300"
                >
                    Filter by Product
                </label>
                <select
                    id="product_filter"
                    v-model="selectedProductId"
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
        </div>

        <div
            class="w-full border rounded-lg shadow-lg border-primary/40 bg-dark-card"
        >
            <DataTable
                :data="inputFields"
                :columns="columns"
                :filters="filters"
                route="admin.produk-input-fields.index"
                class="max-w-full whitespace-nowrap"
                @delete="handleDelete"
            >
                <template #title> Product Input Fields </template>

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
                        <span>Add Field</span>
                    </button>
                </template>

                <!-- Custom Cell Renderers -->
                <template #cell(product)="{ item }">
                    <div class="flex items-center space-x-2">
                        <span class="text-gray-200">
                            {{
                                item.produk
                                    ? item.produk.nama
                                    : "Unknown Product"
                            }}
                        </span>
                    </div>
                </template>

                <template #cell(type)="{ item }">
                    <span
                        :class="getTypeClass(item.type)"
                        class="px-2 py-1 text-xs rounded-xl"
                    >
                        {{ item.type }}
                    </span>
                </template>

                <template #cell(required)="{ item }">
                    <span
                        :class="
                            item.required ? 'text-green-400' : 'text-gray-400'
                        "
                    >
                        {{ item.required ? "Yes" : "No" }}
                    </span>
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
                                    @click="openEditForm(item)"
                                    class="block w-full px-4 py-2 text-sm text-left text-gray-300 hover:bg-dark-lighter hover:text-primary"
                                >
                                    Edit
                                </button>
                                <button
                                    v-if="item.type === 'select'"
                                    @click="manageOptions(item)"
                                    class="block w-full px-4 py-2 text-sm text-left text-gray-300 hover:bg-dark-lighter hover:text-secondary"
                                >
                                    Manage Options
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
            <Pagination :links="props.fields.links" />
        </div>

        <!-- Add/Edit Field Modal -->
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
                                ? "Add New Input Field"
                                : "Edit Input Field"
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

                <form @submit.prevent="saveField" class="overflow-visible">
                    <div class="space-y-3 sm:space-y-4">
                        <!-- Product Field -->
                        <div>
                            <label
                                for="produk_id"
                                class="block mb-1 text-sm font-medium text-gray-300"
                                >Product</label
                            >
                            <select
                                id="produk_id"
                                v-model="currentField.produk_id"
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                required
                            >
                                <option value="" disabled>
                                    Select Product
                                </option>
                                <option
                                    v-for="product in products"
                                    :key="product.id"
                                    :value="product.id"
                                >
                                    {{ product.nama }}
                                </option>
                            </select>
                        </div>

                        <div
                            class="grid grid-cols-1 gap-3 sm:gap-4 sm:grid-cols-2"
                        >
                            <!-- Name Field (System identifier) -->
                            <div>
                                <label
                                    for="name"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Field Name (system)</label
                                >
                                <input
                                    id="name"
                                    v-model="currentField.name"
                                    type="text"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="user_id, server_id, etc."
                                    required
                                />
                                <p class="mt-1 text-xs text-gray-400">
                                    Alphanumeric and underscore only
                                </p>
                            </div>

                            <!-- Label Field (Display name) -->
                            <div>
                                <label
                                    for="label"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Label (display)</label
                                >
                                <input
                                    id="label"
                                    v-model="currentField.label"
                                    type="text"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="User ID, Server ID, etc."
                                    required
                                />
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-1 gap-3 sm:gap-4 sm:grid-cols-3"
                        >
                            <!-- Type Field -->
                            <div>
                                <label
                                    for="type"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Field Type</label
                                >
                                <select
                                    id="type"
                                    v-model="currentField.type"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    required
                                >
                                    <option value="text">Text</option>
                                    <option value="number">Number</option>
                                    <option value="select">Select</option>
                                </select>
                            </div>

                            <!-- Required Field -->
                            <div>
                                <label
                                    for="required"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Required</label
                                >
                                <select
                                    id="required"
                                    v-model="currentField.required"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                >
                                    <option :value="true">Yes</option>
                                    <option :value="false">No</option>
                                </select>
                            </div>

                            <!-- Order Field -->
                            <div>
                                <label
                                    for="order"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Display Order</label
                                >
                                <input
                                    id="order"
                                    v-model.number="currentField.order"
                                    type="number"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="0"
                                />
                            </div>
                        </div>

                        <!-- Type warning -->
                        <div
                            v-if="
                                formMode === 'edit' &&
                                currentField.type !== 'select' &&
                                currentField.originalType === 'select'
                            "
                            class="p-3 text-sm text-yellow-400 rounded-lg bg-yellow-500/20"
                        >
                            <p>
                                <strong>Warning:</strong> Changing from select
                                to another field type will delete all associated
                                options.
                            </p>
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
                                        ? "Create Field"
                                        : "Update Field"
                                }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

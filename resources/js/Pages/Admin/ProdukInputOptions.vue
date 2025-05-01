<script setup>
import { ref, computed } from "vue";
import { Head, router, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import Modal from "@/Components/Modal.vue";
import Dropdown from "@/Components/Dropdown.vue";
import Pagination from "@/Components/Pagination.vue";
import axios from "axios";

const props = defineProps({
    options: Object,
    fields: Array,
    currentField: Object,
    filters: Object,
    errors: Object,
});

// Table data
const inputOptions = computed(() => props.options.data || []);

// Column definitions for the table
const columns = [
    { key: "id", label: "ID" },
    { key: "field", label: "Field" },
    { key: "label", label: "Display Label" },
    { key: "value", label: "System Value" },
];

// Form modal states
const showForm = ref(false);
const formMode = ref("add");
const currentOption = ref(null);

// Bulk add modal state
const showBulkForm = ref(false);
const bulkOptions = ref("");
const bulkSaving = ref(false);

// Field filter state
const selectedFieldId = ref(props.filters.field_id || "");

// Get field name for header
const fieldHeader = computed(() => {
    if (props.currentField) {
        const productName = props.currentField.produk
            ? props.currentField.produk.nama
            : "Unknown Product";
        return `${props.currentField.label} (${productName})`;
    }
    return "All Options";
});

const openAddForm = () => {
    formMode.value = "add";
    currentOption.value = {
        produk_input_field_id: selectedFieldId.value || "",
        label: "",
        value: "",
    };
    showForm.value = true;
};

const openEditForm = (option) => {
    formMode.value = "edit";
    // Create a copy to avoid direct mutation
    currentOption.value = { ...option };
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
    currentOption.value = null;
};

const openBulkForm = () => {
    bulkOptions.value = "";
    showBulkForm.value = true;
};

const closeBulkForm = () => {
    showBulkForm.value = false;
    bulkOptions.value = "";
};

const saveOption = () => {
    if (formMode.value === "add") {
        router.post(
            route("admin.produk-input-options.store"),
            currentOption.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeForm();
                },
            }
        );
    } else {
        router.put(
            route("admin.produk-input-options.update", currentOption.value.id),
            currentOption.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeForm();
                },
            }
        );
    }
};

const handleDelete = (item) => {
    // Show confirmation dialog before deleting
    if (window.confirm("Are you sure you want to delete this option?")) {
        router.delete(route("admin.produk-input-options.destroy", item.id), {
            preserveScroll: true,
        });
    }
};

const saveBulkOptions = async () => {
    bulkSaving.value = true;

    try {
        // Parse the textarea content into options array
        // Format expected: label = value (one per line)
        const lines = bulkOptions.value.trim().split("\n");
        const parsedOptions = [];

        for (const line of lines) {
            if (line.trim()) {
                // Try to split by equals sign
                const parts = line.split("=");

                if (parts.length === 2) {
                    parsedOptions.push({
                        label: parts[0].trim(),
                        value: parts[1].trim(),
                    });
                } else {
                    // If no equals sign, use the same value for both
                    const value = line.trim();
                    parsedOptions.push({
                        label: value,
                        value: value.toLowerCase().replace(/[^a-z0-9_]/g, "_"),
                    });
                }
            }
        }

        if (parsedOptions.length === 0) {
            throw new Error("No valid options found");
        }

        // Send to backend
        const response = await axios.post(
            route("admin.produk-input-options.bulk-store"),
            {
                produk_input_field_id: selectedFieldId.value,
                options: parsedOptions,
            }
        );

        // Refresh the page on success
        router.reload();

        // Close modal
        closeBulkForm();
    } catch (error) {
        console.error("Error saving bulk options:", error);
        alert(
            error.response?.data?.message ||
                error.message ||
                "Failed to save options"
        );
    } finally {
        bulkSaving.value = false;
    }
};

// Generate a value from label
const generateValueFromLabel = (label) => {
    if (!label) return "";
    // Convert to lowercase and replace spaces and special chars with underscore
    return label.toLowerCase().replace(/[^a-z0-9_]/g, "_");
};

// Watch for label changes to auto-generate value
const updateValueFromLabel = () => {
    if (
        currentOption.value &&
        !currentOption.value.value &&
        currentOption.value.label
    ) {
        currentOption.value.value = generateValueFromLabel(
            currentOption.value.label
        );
    }
};
</script>

<template>
    <Head title="Product Input Options" />

    <AdminLayout title="Product Input Options">
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

        <!-- Breadcrumbs -->
        <div class="flex items-center mb-4 space-x-2 text-sm">
            <Link
                :href="route('admin.produk-input-fields.index')"
                class="text-gray-400 hover:text-primary"
            >
                Input Fields
            </Link>
            <span class="text-gray-500">/</span>
            <span class="font-medium text-white">{{ fieldHeader }}</span>
        </div>

        <div class="flex items-center justify-between mb-4">
            <div class="w-64" v-if="!currentField">
                <label
                    for="field_filter"
                    class="block mb-1 text-sm font-medium text-gray-300"
                >
                    Filter by Field
                </label>
                <select
                    id="field_filter"
                    v-model="selectedFieldId"
                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                    @change="
                        router.get(
                            route('admin.produk-input-options.index'),
                            { field_id: selectedFieldId },
                            { preserveState: true }
                        )
                    "
                >
                    <option value="">All Fields</option>
                    <option
                        v-for="field in fields"
                        :key="field.id"
                        :value="field.id"
                    >
                        {{ field.label }} ({{ field.produk.nama }})
                    </option>
                </select>
            </div>
        </div>

        <div
            class="w-full border rounded-lg shadow-lg border-primary/40 bg-dark-card"
        >
            <DataTable
                :data="inputOptions"
                :columns="columns"
                :filters="filters"
                route="admin.produk-input-options.index"
                class="max-w-full whitespace-nowrap"
                @delete="handleDelete"
            >
                <template #title> Field Options </template>

                <template #addButton>
                    <div class="flex space-x-2">
                        <button
                            v-if="currentField"
                            @click="openBulkForm"
                            class="flex items-center px-4 py-2 space-x-2 text-white transition-all duration-200 rounded-lg shadow-lg bg-secondary hover:bg-secondary-hover hover:shadow-glow-secondary"
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
                                    d="M4 6h16M4 12h16m-7 6h7"
                                />
                            </svg>
                            <span>Bulk Add</span>
                        </button>

                        <button
                            v-if="selectedFieldId"
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
                            <span>Add Option</span>
                        </button>
                    </div>
                </template>

                <!-- Custom Cell Renderers -->
                <template #cell(field)="{ item }">
                    <div class="flex items-center space-x-2">
                        <span class="text-gray-200">
                            {{ item.input_field?.label || "Unknown Field" }}
                            <span class="text-xs text-gray-400">
                                {{ console.log(item) }}
                                ({{
                                    item.input_field?.produk?.nama ||
                                    "Unknown Product"
                                }})
                            </span>
                        </span>
                    </div>
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
            <Pagination :links="props.options.links" />
        </div>

        <!-- Add/Edit Option Modal -->
        <div
            v-if="showForm"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black bg-opacity-50"
            @click.self="closeForm"
        >
            <div
                class="relative w-full max-w-md mx-4 p-3 border border-gray-700 rounded-lg shadow-lg sm:p-4 md:p-6 bg-dark-card max-h-[90vh] overflow-y-auto"
                @click.stop
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-white">
                        {{
                            formMode === "add"
                                ? "Add New Option"
                                : "Edit Option"
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

                <form @submit.prevent="saveOption" class="overflow-visible">
                    <div class="space-y-3 sm:space-y-4">
                        <!-- Field Selection -->
                        <div v-if="formMode === 'add' && !selectedFieldId">
                            <label
                                for="produk_input_field_id"
                                class="block mb-1 text-sm font-medium text-gray-300"
                                >Field</label
                            >
                            <select
                                id="produk_input_field_id"
                                v-model="currentOption.produk_input_field_id"
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                required
                            >
                                <option value="" disabled>Select Field</option>
                                <option
                                    v-for="field in fields"
                                    :key="field.id"
                                    :value="field.id"
                                >
                                    {{ field.label }} ({{ field.produk.nama }})
                                </option>
                            </select>
                        </div>

                        <!-- Label Field -->
                        <div>
                            <label
                                for="label"
                                class="block mb-1 text-sm font-medium text-gray-300"
                                >Display Label</label
                            >
                            <input
                                id="label"
                                v-model="currentOption.label"
                                type="text"
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                placeholder="Shown in dropdown"
                                required
                                @input="updateValueFromLabel"
                            />
                        </div>

                        <!-- Value Field -->
                        <div>
                            <label
                                for="value"
                                class="block mb-1 text-sm font-medium text-gray-300"
                                >System Value</label
                            >
                            <input
                                id="value"
                                v-model="currentOption.value"
                                type="text"
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                placeholder="Sent to backend"
                                required
                            />
                            <p class="mt-1 text-xs text-gray-400">
                                Alphanumeric and underscore only
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
                                        ? "Create Option"
                                        : "Update Option"
                                }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bulk Add Modal -->
        <div
            v-if="showBulkForm"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black bg-opacity-50"
            @click.self="closeBulkForm"
        >
            <div
                class="relative w-full max-w-md mx-4 p-3 border border-gray-700 rounded-lg shadow-lg sm:p-4 md:p-6 bg-dark-card max-h-[90vh] overflow-y-auto"
                @click.stop
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-white">
                        Bulk Add Options
                    </h3>
                    <button
                        @click="closeBulkForm"
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

                <div class="space-y-4">
                    <div>
                        <label
                            for="bulk_options"
                            class="block mb-1 text-sm font-medium text-gray-300"
                        >
                            Enter Options (one per line)
                        </label>
                        <textarea
                            id="bulk_options"
                            v-model="bulkOptions"
                            rows="10"
                            class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="Label = value
Second Label = second_value
Third Label"
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-400">
                            Format: "Label = value" or just "Label" (value will
                            be auto-generated)
                        </p>
                    </div>

                    <div
                        class="flex flex-col justify-end pt-3 space-y-2 sm:flex-row sm:pt-4 sm:space-y-0 sm:space-x-3"
                    >
                        <button
                            type="button"
                            @click="closeBulkForm"
                            class="w-full px-4 py-2 text-gray-300 rounded-lg bg-dark-lighter hover:text-white sm:w-auto"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            @click="saveBulkOptions"
                            class="w-full px-4 py-2 text-white transition-all duration-200 rounded-lg shadow-lg bg-primary hover:bg-primary-hover hover:shadow-glow-primary sm:w-auto"
                            :disabled="bulkSaving || !bulkOptions.trim()"
                        >
                            <span v-if="bulkSaving">Saving...</span>
                            <span v-else>Save Options</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

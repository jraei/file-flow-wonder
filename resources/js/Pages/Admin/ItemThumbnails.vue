<script setup>
import { ref, computed, getCurrentInstance, watch } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import Dropdown from "@/Components/Dropdown.vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    thumbnails: Object,
    products: Array,
    errors: Object,
    filters: Object,
});

const { proxy } = getCurrentInstance();

const thumbnails = computed(() => props.thumbnails.data || []);

const columns = [
    { key: "id", label: "ID" },
    {
        key: "produk",
        label: "Product",
        format: (value, item) => {
            const produkName = item.produk ? item.produk.nama : "Unknown";
            return `<div class="flex items-center">
                    <div class="flex items-center justify-center flex-shrink-0 w-8 h-8 text-xs font-bold text-white bg-gray-600 rounded-full">
                        ${produkName.substring(0, 2).toUpperCase()}
                    </div>
                    <div class="ml-3">
                        <div class="text-sm font-medium text-gray-200">${produkName}</div>
                        <div class="text-xs text-gray-400">${
                            item.produk ? item.produk.brand : ""
                        }</div>
                    </div>
                </div>`;
        },
    },
    {
        key: "image_path",
        label: "Thumbnail",
        format: (value) => {
            return `<div class="flex items-center justify-center">
                    <div class="w-12 h-12 overflow-hidden transition-transform duration-200 rounded-full shadow-lg hover:scale-150">
                        <img src="/storage/${value}" class="object-cover w-full h-full" />
                    </div>
                </div>`;
        },
    },
    {
        key: "min_item",
        label: "Min Item",
        format: (value, item) => {
            if (item.is_static) {
                return '<span class="text-xs text-gray-400">N/A</span>';
            }
            return value !== null ? value : "-";
        },
    },
    {
        key: "max_item",
        label: "Max Item",
        format: (value, item) => {
            if (item.is_static) {
                return '<span class="text-xs text-gray-400">N/A</span>';
            }
            return value !== null ? value : "-";
        },
    },
    {
        key: "is_static",
        label: "Static",
        format: (value) => {
            const toggleClass = value ? "bg-primary" : "bg-gray-600";

            return `<div class="flex items-center justify-center">
                    <div class="${toggleClass} w-10 h-5 rounded-full relative">
                        <div class="absolute top-0.5 ${
                            value ? "right-0.5" : "left-0.5"
                        } bg-white w-4 h-4 rounded-full transition-all duration-200"></div>
                    </div>
                </div>`;
        },
    },
    {
        key: "default_for_produk",
        label: "Default",
        format: (value) => {
            const radioClass = value
                ? "border-primary bg-primary"
                : "border-gray-600 bg-dark-sidebar";

            return `<div class="flex items-center justify-center">
                    <div class="${radioClass} w-5 h-5 rounded-full border-2 flex items-center justify-center">
                        ${
                            value
                                ? '<div class="w-2 h-2 bg-white rounded-full"></div>'
                                : ""
                        }
                    </div>
                </div>`;
        },
    },
];

const handleDelete = (item) => {
    proxy.$showSwalConfirm({
        onConfirm: () => {
            router.delete(route("item-thumbnails.destroy", item.id), {
                preserveScroll: true,
            });
        },
    });
};

const handleToggleStatic = (item) => {
    router.patch(
        route("item-thumbnails.toggle-static", item.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                // No need to handle, page will refresh
            },
            onError: (errors) => {
                proxy.$swal.fire({
                    icon: "error",
                    title: "Error",
                    text: Object.values(errors)[0],
                });
            },
        }
    );
};

const handleToggleDefault = (item) => {
    router.patch(
        route("item-thumbnails.toggle-default", item.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                // No need to handle, page will refresh
            },
            onError: (errors) => {
                proxy.$swal.fire({
                    icon: "error",
                    title: "Error",
                    text: Object.values(errors)[0],
                });
            },
        }
    );
};

const showForm = ref(false);
const showBulkForm = ref(false);
const formMode = ref("add");
const currentData = ref({});
const selectedItems = ref([]);
const productFilter = ref(props.filters.product_id || "");

watch(productFilter, (newValue) => {
    router.get(
        route("item-thumbnails.index"),
        {
            search: props.filters.search,
            sort: props.filters.sort,
            direction: props.filters.direction,
            product_id: newValue || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
});

const openAddForm = () => {
    formMode.value = "add";
    imagePreviews.value.image_path = null;
    currentData.value = {
        produk_id: "",
        image_path: "",
        min_item: 1,
        max_item: 10,
        is_static: false,
        default_for_produk: false,
    };
    showForm.value = true;
};

const openEditForm = (item) => {
    console.log("Edit", item);

    formMode.value = "edit";
    currentData.value = { ...item };
    showForm.value = true;
};

const openBulkForm = () => {
    imagePreviews.value.image_path = null;
    currentData.value = {
        produk_ids: [],
        min_item: 1,
        max_item: 10,
        is_static: false,
        default_for_produk: false,
        image_path: "",
    };
    showBulkForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
};

const closeBulkForm = () => {
    showBulkForm.value = false;
};

const saveDataForm = () => {
    if (formMode.value === "add") {
        router.post(route("item-thumbnails.store"), currentData.value, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                closeForm();
            },
        });
    } else if (formMode.value === "edit") {
        currentData.value._method = "put";

        console.log("ID to update:", currentData.value.id);

        router.post(
            route("item-thumbnails.update", {
                item_thumbnail: currentData.value.id,
            }),
            currentData.value,
            {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => {
                    closeForm();
                },
            }
        );
    }
};

const saveBulkForm = () => {
    router.post(route("item-thumbnails.bulk-assign"), currentData.value, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            closeBulkForm();
        },
    });
};

const imagePreviews = ref({
    image_path: null,
});

const getImagePreview = (field) => {
    return computed(() => {
        if (
            typeof currentData.value[field] === "string" &&
            currentData.value[field]
        ) {
            if (currentData.value[field].startsWith("http")) {
                return currentData.value[field];
            } else {
                return `/storage/${currentData.value[field]}`;
            }
        } else if (imagePreviews.value[field]) {
            return imagePreviews.value[field];
        }
        return null;
    });
};

const handleFileUpload = (event, field) => {
    const file = event.target.files[0];
    if (file) {
        currentData.value[field] = file;
        imagePreviews.value[field] = URL.createObjectURL(file);
    }
};

const rangeConflictDetected = computed(() => {
    // Only perform check if we're in range mode and have valid min/max values
    if (
        currentData.value.is_static ||
        !currentData.value.min_item ||
        !currentData.value.max_item ||
        !currentData.value.produk_id
    ) {
        return false;
    }

    // Look for conflicts in existing thumbnails
    return thumbnails.value.some(
        (thumbnail) =>
            thumbnail.produk_id === currentData.value.produk_id &&
            !thumbnail.is_static &&
            // Skip comparing with self in edit mode
            (formMode.value !== "edit" ||
                thumbnail.id !== currentData.value.id) &&
            // Check for range overlap
            ((currentData.value.min_item <= thumbnail.max_item &&
                currentData.value.max_item >= thumbnail.min_item) ||
                (currentData.value.min_item >= thumbnail.min_item &&
                    currentData.value.min_item <= thumbnail.max_item) ||
                (currentData.value.max_item >= thumbnail.min_item &&
                    currentData.value.max_item <= thumbnail.max_item))
    );
});
</script>

<template>
    <Head title="Item Thumbnails" />

    <AdminLayout title="Item Thumbnails">
        <div
            v-if="Object.keys(errors).length > 0"
            class="px-4 py-3 mb-4 text-sm text-white rounded-lg bg-red-500/80"
        >
            <ul v-for="error in errors">
                <li>{{ error }}</li>
            </ul>
        </div>

        <!-- Product Filter -->
        <div class="flex justify-end mb-4">
            <div class="flex items-center space-x-2">
                <label
                    for="productFilter"
                    class="text-sm font-medium text-gray-300"
                    >Filter by Product:</label
                >
                <select
                    id="productFilter"
                    v-model="productFilter"
                    class="w-64 px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
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
            <!-- Improved table container with explicit scrolling -->
            <div class="max-w-full overflow-x-auto">
                <DataTable
                    :data="thumbnails"
                    :columns="columns"
                    :filters="filters"
                    route="item-thumbnails.index"
                    class="w-full whitespace-nowrap"
                >
                    <template #title> Thumbnail Rules </template>

                    <template #addButton>
                        <div class="flex space-x-2">
                            <button
                                @click="openAddForm"
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
                                <span>Add Rule</span>
                            </button>

                            <button
                                @click="openBulkForm"
                                class="flex items-center px-3 py-2 space-x-2 text-white transition-all duration-200 rounded-lg shadow-lg sm:px-4 bg-secondary hover:bg-secondary-hover"
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
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
                                    />
                                </svg>
                                <span>Bulk Assign</span>
                            </button>
                        </div>
                    </template>

                    <template #cell(is_static)="{ item }">
                        <div
                            @click="handleToggleStatic(item)"
                            class="flex items-center justify-center cursor-pointer"
                        >
                            <div
                                :class="
                                    item.is_static
                                        ? 'bg-primary'
                                        : 'bg-gray-600'
                                "
                                class="relative w-10 h-5 transition-colors duration-200 rounded-full"
                            >
                                <div
                                    :class="
                                        item.is_static
                                            ? 'right-0.5'
                                            : 'left-0.5'
                                    "
                                    class="absolute top-0.5 bg-white w-4 h-4 rounded-full transition-all duration-200"
                                ></div>
                            </div>
                        </div>
                    </template>

                    <template #cell(default_for_produk)="{ item }">
                        <div
                            @click="handleToggleDefault(item)"
                            class="flex items-center justify-center cursor-pointer"
                        >
                            <div
                                :class="[
                                    item.default_for_produk
                                        ? 'border-primary bg-primary'
                                        : 'border-gray-600 bg-dark-sidebar',
                                ]"
                                class="flex items-center justify-center w-5 h-5 transition-colors duration-200 border-2 rounded-full"
                            >
                                <div
                                    v-if="item.default_for_produk"
                                    class="w-2 h-2 bg-white rounded-full"
                                ></div>
                            </div>
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
                                </div>
                            </template>
                        </Dropdown>
                    </template>
                </DataTable>
            </div>
            <Pagination :links="props.thumbnails.links" />
        </div>

        <!-- Add/Edit Form Modal -->
        <div
            v-if="showForm"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black/50"
            @click.self="closeForm"
        >
            <div
                class="relative w-full max-w-md mx-4 p-3 border border-gray-700 rounded-lg shadow-lg sm:p-4 md:p-6 md:max-w-xl bg-dark-card max-h-[90vh] overflow-y-auto"
                @click.stop
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white sm:text-xl">
                        {{
                            formMode === "add"
                                ? "Add New Thumbnail Rule"
                                : "Edit Thumbnail Rule"
                        }}
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

                <form @submit.prevent="saveDataForm" class="overflow-visible">
                    <div class="space-y-3 sm:space-y-4">
                        <div>
                            <label
                                for="produk_id"
                                class="block mb-1 text-sm font-medium text-gray-300"
                            >
                                Product
                            </label>
                            <select
                                id="produk_id"
                                v-model="currentData.produk_id"
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
                                    {{ product.nama }} ({{ product.brand }})
                                </option>
                            </select>
                        </div>

                        <!-- Conflict Warning -->
                        <div
                            v-if="rangeConflictDetected"
                            class="p-3 text-sm text-red-400 border rounded-lg bg-red-500/20 border-red-500/50"
                        >
                            <strong class="font-bold">Warning:</strong> This
                            range conflicts with an existing thumbnail rule.
                            Please adjust the min/max values or set as static.
                        </div>

                        <div class="flex items-center">
                            <label
                                for="is_static"
                                class="mr-3 text-sm font-medium text-gray-300"
                            >
                                Static Thumbnail
                            </label>
                            <div
                                @click="
                                    currentData.is_static =
                                        !currentData.is_static
                                "
                                class="cursor-pointer"
                            >
                                <div
                                    :class="
                                        currentData.is_static
                                            ? 'bg-primary'
                                            : 'bg-gray-600'
                                    "
                                    class="relative w-10 h-5 transition-colors duration-200 rounded-full"
                                >
                                    <div
                                        :class="
                                            currentData.is_static
                                                ? 'right-0.5'
                                                : 'left-0.5'
                                        "
                                        class="absolute top-0.5 bg-white w-4 h-4 rounded-full transition-all duration-200"
                                    ></div>
                                </div>
                            </div>
                            <div class="ml-3 text-xs text-gray-400">
                                {{
                                    currentData.is_static
                                        ? "Always use this thumbnail"
                                        : "Use for specific quantity range"
                                }}
                            </div>
                        </div>

                        <div
                            v-if="!currentData.is_static"
                            class="grid grid-cols-2 gap-3"
                        >
                            <div>
                                <label
                                    for="min_item"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                >
                                    Minimum Items
                                </label>
                                <input
                                    id="min_item"
                                    v-model="currentData.min_item"
                                    type="number"
                                    min="0"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    required
                                />
                            </div>
                            <div>
                                <label
                                    for="max_item"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                >
                                    Maximum Items
                                </label>
                                <input
                                    id="max_item"
                                    v-model="currentData.max_item"
                                    type="number"
                                    min="0"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    required
                                />
                            </div>
                        </div>

                        <div class="flex items-center">
                            <label
                                for="default_for_produk"
                                class="mr-3 text-sm font-medium text-gray-300"
                            >
                                Default Thumbnail
                            </label>
                            <div
                                @click="
                                    currentData.default_for_produk =
                                        !currentData.default_for_produk
                                "
                                class="cursor-pointer"
                            >
                                <div
                                    :class="[
                                        currentData.default_for_produk
                                            ? 'border-primary bg-primary'
                                            : 'border-gray-600 bg-dark-sidebar',
                                    ]"
                                    class="flex items-center justify-center w-5 h-5 transition-colors duration-200 border-2 rounded-full"
                                >
                                    <div
                                        v-if="currentData.default_for_produk"
                                        class="w-2 h-2 bg-white rounded-full"
                                    ></div>
                                </div>
                            </div>
                            <div class="ml-3 text-xs text-gray-400">
                                Use as fallback when no specific rule matches
                            </div>
                        </div>

                        <div>
                            <label
                                for="image_path"
                                class="block mb-1 text-sm font-medium text-gray-300"
                            >
                                Thumbnail Image
                            </label>

                            <!-- Image Preview -->
                            <div
                                v-if="getImagePreview('image_path').value"
                                class="relative mb-2 group"
                            >
                                <img
                                    :src="getImagePreview('image_path').value"
                                    alt="Thumbnail Preview"
                                    class="object-cover w-32 h-32 border rounded-lg shadow-md border-primary/60"
                                />
                                <div
                                    class="absolute inset-0 flex items-center justify-center transition-opacity duration-200 rounded-lg opacity-0 bg-black/60 group-hover:opacity-100"
                                >
                                    <span class="text-sm text-white"
                                        >Click to replace</span
                                    >
                                </div>
                            </div>

                            <!-- File Upload Area -->
                            <div
                                class="relative p-4 transition-colors duration-200 border-2 border-gray-600 border-dashed rounded-lg cursor-pointer hover:border-primary/60"
                                @click="$refs.imageInput.click()"
                            >
                                <input
                                    ref="imageInput"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="
                                        handleFileUpload($event, 'image_path')
                                    "
                                />
                                <div
                                    class="text-center"
                                    v-if="!getImagePreview('image_path').value"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-10 h-10 mx-auto text-gray-500"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-400">
                                        Click to upload thumbnail image
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        JPEG, PNG or WebP up to 10MB
                                    </p>
                                </div>
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
                                :disabled="rangeConflictDetected"
                                :class="{
                                    'opacity-50 cursor-not-allowed':
                                        rangeConflictDetected,
                                }"
                            >
                                {{
                                    formMode === "add"
                                        ? "Create Rule"
                                        : "Update Rule"
                                }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bulk Assign Modal -->
        <div
            v-if="showBulkForm"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black/50"
            @click.self="closeBulkForm"
        >
            <div
                class="relative w-full max-w-md mx-4 p-3 border border-gray-700 rounded-lg shadow-lg sm:p-4 md:p-6 md:max-w-xl bg-dark-card max-h-[90vh] overflow-y-auto"
                @click.stop
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white sm:text-xl">
                        Bulk Assign Thumbnails
                    </h3>
                    <button
                        @click="closeBulkForm"
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

                <form @submit.prevent="saveBulkForm" class="overflow-visible">
                    <div class="space-y-3 sm:space-y-4">
                        <div>
                            <label
                                for="produk_ids"
                                class="block mb-1 text-sm font-medium text-gray-300"
                            >
                                Products
                            </label>
                            <select
                                id="produk_ids"
                                v-model="currentData.produk_ids"
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                multiple
                                required
                                size="5"
                            >
                                <option
                                    v-for="product in products"
                                    :key="product.id"
                                    :value="product.id"
                                >
                                    {{ product.nama }} ({{ product.brand }})
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Hold Ctrl/Cmd to select multiple products
                            </p>
                        </div>

                        <div class="flex items-center">
                            <label
                                for="bulk_is_static"
                                class="mr-3 text-sm font-medium text-gray-300"
                            >
                                Static Thumbnail
                            </label>
                            <div
                                @click="
                                    currentData.is_static =
                                        !currentData.is_static
                                "
                                class="cursor-pointer"
                            >
                                <div
                                    :class="
                                        currentData.is_static
                                            ? 'bg-primary'
                                            : 'bg-gray-600'
                                    "
                                    class="relative w-10 h-5 transition-colors duration-200 rounded-full"
                                >
                                    <div
                                        :class="
                                            currentData.is_static
                                                ? 'right-0.5'
                                                : 'left-0.5'
                                        "
                                        class="absolute top-0.5 bg-white w-4 h-4 rounded-full transition-all duration-200"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="!currentData.is_static"
                            class="grid grid-cols-2 gap-3"
                        >
                            <div>
                                <label
                                    for="bulk_min_item"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                >
                                    Minimum Items
                                </label>
                                <input
                                    id="bulk_min_item"
                                    v-model="currentData.min_item"
                                    type="number"
                                    min="0"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    required
                                />
                            </div>
                            <div>
                                <label
                                    for="bulk_max_item"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                >
                                    Maximum Items
                                </label>
                                <input
                                    id="bulk_max_item"
                                    v-model="currentData.max_item"
                                    type="number"
                                    min="0"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    required
                                />
                            </div>
                        </div>

                        <div class="flex items-center">
                            <label
                                for="bulk_default_for_produk"
                                class="mr-3 text-sm font-medium text-gray-300"
                            >
                                Default Thumbnail
                            </label>
                            <div
                                @click="
                                    currentData.default_for_produk =
                                        !currentData.default_for_produk
                                "
                                class="cursor-pointer"
                            >
                                <div
                                    :class="[
                                        currentData.default_for_produk
                                            ? 'border-primary bg-primary'
                                            : 'border-gray-600 bg-dark-sidebar',
                                    ]"
                                    class="flex items-center justify-center w-5 h-5 transition-colors duration-200 border-2 rounded-full"
                                >
                                    <div
                                        v-if="currentData.default_for_produk"
                                        class="w-2 h-2 bg-white rounded-full"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label
                                for="bulk_image_path"
                                class="block mb-1 text-sm font-medium text-gray-300"
                            >
                                Thumbnail Image
                            </label>

                            <!-- Image Preview -->
                            <div
                                v-if="getImagePreview('image_path').value"
                                class="relative mb-2 group"
                            >
                                <img
                                    :src="getImagePreview('image_path').value"
                                    alt="Thumbnail Preview"
                                    class="object-cover w-32 h-32 border rounded-lg shadow-md border-primary/60"
                                />
                                <div
                                    class="absolute inset-0 flex items-center justify-center transition-opacity duration-200 rounded-lg opacity-0 bg-black/60 group-hover:opacity-100"
                                >
                                    <span class="text-sm text-white"
                                        >Click to replace</span
                                    >
                                </div>
                            </div>

                            <!-- File Upload Area -->
                            <div
                                class="relative p-4 transition-colors duration-200 border-2 border-gray-600 border-dashed rounded-lg cursor-pointer hover:border-primary/60"
                                @click="$refs.bulkImageInput.click()"
                            >
                                <input
                                    ref="bulkImageInput"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="
                                        handleFileUpload($event, 'image_path')
                                    "
                                />
                                <div
                                    class="text-center"
                                    v-if="!getImagePreview('image_path').value"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-10 h-10 mx-auto text-gray-500"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-400">
                                        Click to upload thumbnail image
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        JPEG, PNG or WebP up to 10MB
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="p-3 mt-4 border rounded-lg bg-secondary/20 border-secondary/30"
                        >
                            <p class="text-xs text-gray-300">
                                <strong class="text-secondary">Note:</strong>
                                This operation will add the same thumbnail rule
                                to all selected products. Conflicts with
                                existing rules will be skipped.
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
                                type="submit"
                                class="w-full px-4 py-2 text-white transition-all duration-200 rounded-lg shadow-lg bg-secondary hover:bg-secondary-hover sm:w-auto"
                            >
                                Apply to Selected Products
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

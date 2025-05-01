<script setup>
import { ref, computed, getCurrentInstance, watch } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import Modal from "@/Components/Modal.vue";
import Dropdown from "@/Components/Dropdown.vue";
import Pagination from "@/Components/Pagination.vue";
import axios from "axios";

const props = defineProps({
    products: Object,
    kategori_list: Object,
    provider_list: Object,
    errors: Object,
    filters: Object,
});

const { proxy } = getCurrentInstance();

const products = computed(() => props.products.data || []);

const columns = [
    { key: "id", label: "ID" },
    { key: "nama", label: "Produk" },
    {
        key: "kategori_id",
        label: "Kategori",
        format: (value) => {
            const kategori = props.kategori_list.find(
                (item) => Number(item.id) === Number(value)
            );
            return kategori ? kategori.kategori_name : "Tidak Diketahui";
        },
    },
    { key: "brand", label: "Brand" },
    {
        key: "provider_id",
        label: "Provider",
        format: (value) => {
            const provider = props.provider_list.find(
                (item) => Number(item.id) === Number(value)
            );

            return provider ? provider.provider_name : "Tidak Diketahui";
        },
    },
    { key: "validasi_id", label: "Validasi ID" },
    {
        key: "status",
        label: "Status",
        format: (value) => {
            const statusClasses =
                value === "active"
                    ? "bg-green-500/20 text-green-400"
                    : "bg-red-500/20 text-red-400";

            return `<span class="${statusClasses} px-2 py-1 rounded-full text-xs">${value}</span>`;
        },
    },
];

// Provider selection
const selectedProvider = ref(props.filters?.provider_id || "");

watch(
    () => selectedProvider.value,
    (newValue) => {
        router.get(
            route("products.index"),
            {
                provider_id: newValue,
                search: props.filters?.search,
                sort: props.filters?.sort,
                direction: props.filters?.direction,
            },
            {
                preserveState: true,
                replace: true,
            }
        );
    }
);

const getServicesFromAPI = () => {
    if (!selectedProvider.value) {
        proxy.$showSwalConfirm({
            title: "Error",
            text: "Please select a provider first",
            icon: "error",
        });
        return;
    }

    isLoading.value = true;
    router.post(
        route("products.getProductsByProvider", selectedProvider.value),
        {},
        {
            onFinish: () => {
                isLoading.value = false;
            },
        }
    );
};

// Delete services by provider
const deleteServicesByProvider = () => {
    if (!selectedProvider.value) {
        proxy.$showSwalConfirm({
            title: "Error",
            text: "Please select a provider first",
            icon: "error",
        });
        return;
    }

    proxy.$showSwalConfirm({
        title: "Warning",
        text: `Are you sure you want to delete all services from this provider?`,
        icon: "warning",
        confirmButtonText: "Yes, delete all",
        onConfirm: () => {
            router.delete(route("services.deleteLayanan"), {
                data: { provider_id: selectedProvider.value },
                preserveScroll: true,
            });
        },
    });
};

const showViewModal = ref(false);
const selectedData = ref(null);
const isLoading = ref(false);

const handleView = async (item) => {
    isLoading.value = true;
    selectedData.value = { ...item, loading: true };
    showViewModal.value = true;

    try {
        const response = await axios.get(route("products.show", item.id));
        selectedData.value = response.data.product;
    } catch (error) {
        console.error("Error fetching product details:", error);
        proxy.$showSwalConfirm({
            title: "Error",
            text: "Failed to load product details",
            icon: "error",
        });
    } finally {
        isLoading.value = false;
    }
};

const handleEdit = (item) => {
    imagePreviews.value.thumbnail = null;
    openEditForm(item);
};

const handleDelete = (item) => {
    proxy.$showSwalConfirm({
        onConfirm: () => {
            router.delete(route("products.destroy", item.id), {
                preserveScroll: true,
            });
        },
    });
};

const showForm = ref(false);
const formMode = ref("add");
const currentData = ref({
    nama: "",
    developer: "",
    brand: "",
    kategori_id: "",
    slug: "",
    provider_id: "",
    validasi_id: "",
    deskripsi_game: "",
    petunjuk_field: "",
    thumbnail: "",
    banner: "",
    status: "active",
});

const openAddForm = () => {
    imagePreviews.value.petunjuk_field = null;
    imagePreviews.value.thumbnail = null;
    imagePreviews.value.banner = null;
    formMode.value = "add";
    currentData.value = {
        nama: "",
        status: "active",
    };
    showForm.value = true;
};

const openEditForm = (data) => {
    if (showViewModal.value) {
        showViewModal.value = false;
    }
    formMode.value = "edit";
    currentData.value = { ...data };
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedData.value = null;
};

const saveDataForm = () => {
    if (formMode.value === "add") {
        router.post(route("products.store"), currentData.value, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                currentData.value.category_name = "";
            },
        });
    } else {
        currentData.value._method = "put";
        router.post(
            route("products.update", currentData.value.id),
            currentData.value,
            {
                preserveScroll: true,
            }
        );
    }

    closeForm();
};

watch(
    () => currentData.value.nama,
    (newVal) => {
        if (newVal) {
            currentData.value.slug = newVal
                .toLowerCase()
                .replace(/\s+/g, "-")
                .replace(/[^\w-]+/g, "")
                .replace(/--+/g, "-")
                .trim();
        } else {
            currentData.value.slug = "";
        }
    }
);

const imagePreviews = ref({
    petunjuk_field: null,
    thumbnail: null,
    banner: null,
});

const getImagePreview = (field) => {
    return computed(() => {
        if (
            typeof currentData.value[field] === "string" &&
            currentData.value[field]
        ) {
            return `/storage/${currentData.value[field]}`;
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
</script>

<template>
    <Head title="Products" />

    <AdminLayout title="Products">
        <div
            v-if="Object.keys(errors).length > 0"
            class="px-4 py-3 mb-4 text-sm text-white rounded-lg bg-red-500/80"
        >
            <ul v-for="error in errors">
                <li>{{ error }}</li>
            </ul>
        </div>

        <!-- Provider selection and action buttons -->
        <div class="p-4 mb-4 rounded-lg bg-dark-card">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex-grow max-w-xs">
                    <label
                        for="provider_filter"
                        class="block mb-1 text-sm font-medium text-gray-300"
                        >Select Provider</label
                    >
                    <select
                        id="provider_filter"
                        v-model="selectedProvider"
                        class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                    >
                        <option value="">All Providers</option>
                        <option
                            v-for="provider in provider_list"
                            :key="provider.id"
                            :value="provider.id"
                        >
                            {{ provider.provider_name }}
                        </option>
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2 mt-2 sm:mt-0">
                    <button
                        @click="getServicesFromAPI"
                        class="flex items-center px-3 py-2 space-x-2 text-white transition-all duration-200 rounded-lg shadow-lg bg-primary hover:bg-primary-hover hover:shadow-glow-primary"
                        :disabled="isLoading"
                    >
                        <svg
                            v-if="isLoading"
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
                        <svg
                            v-else
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
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                            />
                        </svg>
                        <span>Get Products from API</span>
                    </button>

                    <button
                        v-if="selectedProvider"
                        @click="deleteServicesByProvider"
                        class="flex items-center px-3 py-2 space-x-2 text-white transition-all duration-200 bg-red-600 rounded-lg shadow-lg hover:bg-red-700"
                        :disabled="isLoading"
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
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                        </svg>
                        <span>Delete Products</span>
                    </button>
                </div>
            </div>
        </div>

        <div
            class="w-full border rounded-lg shadow-lg border-primary/40 bg-dark-card"
        >
            <!-- Improved table container with explicit scrolling -->
            <div class="max-w-full overflow-x-auto">
                <DataTable
                    :data="products"
                    :columns="columns"
                    :filters="filters"
                    route="products.index"
                    class="w-full whitespace-nowrap"
                >
                    <template #title> List Products </template>

                    <template #addButton>
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
                            <span>Add Products</span>
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

                    <template #cell(icon)="{ item }">
                        <div
                            class="flex items-center justify-center w-8 h-8 text-white rounded-full bg-primary/20"
                        >
                            {{ item.icon }}
                        </div>
                    </template>
                </DataTable>
            </div>
            <Pagination :links="props.products.links" />
        </div>

        <!-- Modified Form Modal -->
        <div
            v-if="showForm"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black/50"
            @click.self="closeForm"
        >
            <div
                class="relative w-full max-w-md mx-4 p-3 border border-gray-700 rounded-lg shadow-lg sm:p-4 md:p-6 md:max-w-xl lg:max-w-2xl bg-dark-card max-h-[90vh] overflow-y-auto"
                @click.stop
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white sm:text-xl">
                        {{
                            formMode === "add"
                                ? "Add New Products"
                                : "Edit Products"
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
                        <div
                            class="grid grid-cols-1 gap-3 sm:gap-4 sm:grid-cols-2"
                        >
                            <div>
                                <label
                                    for="nama"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Product</label
                                >
                                <input
                                    id="nama"
                                    v-model="currentData.nama"
                                    type="text"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Product Name"
                                    name="nama"
                                    required
                                />
                            </div>
                            <div>
                                <label
                                    for="developer"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Developer</label
                                >
                                <input
                                    id="developer"
                                    v-model="currentData.developer"
                                    type="text"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Developer Name"
                                    name="developer"
                                    required
                                />
                            </div>
                            <div>
                                <label
                                    for="brand"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Brand</label
                                >
                                <input
                                    id="brand"
                                    v-model="currentData.brand"
                                    type="text"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Brand Name"
                                    name="brand"
                                    required
                                />
                            </div>
                            <div v-if="kategori_list">
                                <label
                                    for="kategori_id"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Kategori</label
                                >
                                <select
                                    name="kategori_id"
                                    id="kategori_id"
                                    v-model="currentData.kategori_id"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                >
                                    <option
                                        v-for="(item, index) in kategori_list"
                                        :value="item.id"
                                        :key="index"
                                    >
                                        {{ item.kategori_name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    for="slug"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Slug</label
                                >
                                <input
                                    id="slug"
                                    v-model="currentData.slug"
                                    type="text"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Slug Name"
                                    name="slug"
                                    :disabled="formMode == 'add'"
                                    required
                                />
                            </div>

                            <div>
                                <label
                                    for="provider_id"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Provider</label
                                >
                                <select
                                    name="provider_id"
                                    id="provider_id"
                                    v-model="currentData.provider_id"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                >
                                    <option
                                        v-for="(item, index) in provider_list"
                                        :value="item.id"
                                        :key="index"
                                    >
                                        {{ item.provider_name.toUpperCase() }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    for="validasi_id"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Validasi ID</label
                                >
                                <input
                                    id="validasi_id"
                                    v-model="currentData.validasi_id"
                                    type="text"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Validasi ID"
                                    name="validasi_id"
                                    required
                                />
                            </div>

                            <div>
                                <label
                                    for="status"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Status</label
                                >
                                <select
                                    id="status"
                                    name="status"
                                    v-model="currentData.status"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="col-span-1 sm:col-span-2">
                                <div
                                    class="grid grid-cols-1 gap-3 sm:gap-4 sm:grid-cols-2"
                                >
                                    <div>
                                        <label
                                            for="petunjuk_field"
                                            class="block mb-1 text-sm font-medium text-gray-300"
                                            >Petunjuk</label
                                        >
                                        <div
                                            v-if="
                                                getImagePreview(
                                                    'petunjuk_field'
                                                ).value
                                            "
                                            class="mb-2"
                                        >
                                            <img
                                                :src="
                                                    getImagePreview(
                                                        'petunjuk_field'
                                                    ).value
                                                "
                                                alt="Preview Petunjuk"
                                                class="object-cover w-24 h-24 border rounded-lg shadow-md sm:w-32 sm:h-32 border-primary/60"
                                            />
                                        </div>

                                        <input
                                            id="petunjuk_field"
                                            @change="
                                                handleFileUpload(
                                                    $event,
                                                    'petunjuk_field'
                                                )
                                            "
                                            type="file"
                                            class="w-full px-2 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                            placeholder="Petunjuk"
                                            name="petunjuk_field"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            for="thumbnail"
                                            class="block mb-1 text-sm font-medium text-gray-300"
                                            >Thumbnail</label
                                        >
                                        <div
                                            v-if="
                                                getImagePreview('thumbnail')
                                                    .value
                                            "
                                            class="mb-2"
                                        >
                                            <img
                                                :src="
                                                    getImagePreview('thumbnail')
                                                        .value
                                                "
                                                alt="Thumbnail"
                                                class="object-cover w-24 h-24 border rounded-lg shadow-md sm:w-32 sm:h-32 border-primary/60"
                                            />
                                        </div>

                                        <input
                                            id="thumbnail"
                                            @change="
                                                handleFileUpload(
                                                    $event,
                                                    'thumbnail'
                                                )
                                            "
                                            type="file"
                                            class="w-full px-2 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                            placeholder="Thumbnail"
                                            name="thumbnail"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            for="banner"
                                            class="block mb-1 text-sm font-medium text-gray-300"
                                            >Banner</label
                                        >
                                        <div
                                            v-if="
                                                getImagePreview('banner').value
                                            "
                                            class="mb-2"
                                        >
                                            <img
                                                :src="
                                                    getImagePreview('banner')
                                                        .value
                                                "
                                                alt="banner"
                                                class="object-cover w-24 h-24 border rounded-lg shadow-md sm:w-32 sm:h-32 border-primary/60"
                                            />
                                        </div>

                                        <input
                                            id="banner"
                                            @change="
                                                handleFileUpload(
                                                    $event,
                                                    'banner'
                                                )
                                            "
                                            type="file"
                                            class="w-full px-2 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                            placeholder="banner"
                                            name="banner"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-span-1 sm:col-span-2">
                                <label
                                    for="deskripsi_game"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Deskripsi Game</label
                                >
                                <textarea
                                    id="deskripsi_game"
                                    v-model="currentData.deskripsi_game"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Deskripsi Game"
                                    name="deskripsi_game"
                                    required
                                    rows="2"
                                />
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
                                        ? "Create Product"
                                        : "Update Product"
                                }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modified View Modal -->
        <Modal :show="showViewModal" @close="closeViewModal" max-width="2xl">
            <div
                class="p-3 sm:p-4 md:p-6 border border-gray-700 rounded-lg bg-dark-card max-h-[80vh] overflow-y-auto"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white sm:text-xl">
                        Product Details
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

                <div v-else-if="selectedData" class="space-y-3 sm:space-y-4">
                    <div
                        class="grid grid-cols-1 gap-2 sm:gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
                    >
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Produk ID
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.id }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Product
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.nama }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Kategori
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.kategori.kategori_name }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Provider
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.provider.provider_name }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Developer
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.developer }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Brand
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.brand }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">Slug</p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.slug }}
                            </p>
                        </div>

                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Validasi ID
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.validasi_id }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Status
                            </p>
                            <p>
                                <span
                                    :class="
                                        selectedData.status === 'active'
                                            ? 'bg-green-500/20 text-green-400'
                                            : 'bg-red-500/20 text-red-400'
                                    "
                                    class="px-2 py-1 text-xs rounded-full"
                                >
                                    {{ selectedData.status }}
                                </span>
                            </p>
                        </div>

                        <div
                            class="col-span-1 p-2 rounded-lg sm:p-3 sm:col-span-2 lg:col-span-4 bg-dark-lighter"
                        >
                            <div
                                class="grid grid-cols-1 gap-3 sm:gap-4 sm:grid-cols-2"
                            >
                                <div v-if="selectedData.petunjuk_field">
                                    <p class="text-xs text-gray-400 sm:text-sm">
                                        Petunjuk
                                    </p>
                                    <img
                                        :src="
                                            '/storage/' +
                                            selectedData.petunjuk_field
                                        "
                                        alt="Preview Petunjuk"
                                        class="object-cover w-24 h-24 mt-2 border rounded-lg shadow-md sm:w-32 sm:h-32 border-primary/60"
                                    />
                                </div>
                                <div v-if="selectedData.thumbnail">
                                    <p class="text-xs text-gray-400 sm:text-sm">
                                        Thumbnail
                                    </p>
                                    <img
                                        :src="
                                            '/storage/' + selectedData.thumbnail
                                        "
                                        alt="Preview Thumbnail"
                                        class="object-cover w-24 h-24 mt-2 border rounded-lg shadow-md sm:w-32 sm:h-32 border-primary/60"
                                    />
                                </div>
                                <div v-if="selectedData.banner">
                                    <p class="text-xs text-gray-400 sm:text-sm">
                                        Banner
                                    </p>
                                    <img
                                        :src="'/storage/' + selectedData.banner"
                                        alt="Preview Banner"
                                        class="object-cover w-24 h-24 mt-2 border rounded-lg shadow-md sm:w-32 sm:h-32 border-primary/60"
                                    />
                                </div>
                            </div>
                        </div>

                        <div
                            class="col-span-1 p-2 rounded-lg sm:p-3 sm:col-span-2 lg:col-span-4 bg-dark-lighter"
                        >
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Deskripsi Game
                            </p>
                            <p
                                class="text-sm font-medium text-white break-words sm:text-base"
                            >
                                {{ selectedData.deskripsi_game }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex flex-col justify-end pt-3 space-y-2 sm:flex-row sm:pt-4 sm:space-y-0 sm:space-x-3"
                    >
                        <button
                            @click="openEditForm(selectedData)"
                            class="w-full px-4 py-2 text-white transition-all duration-200 rounded-lg shadow-lg sm:w-auto bg-primary hover:bg-primary-hover hover:shadow-glow-primary"
                        >
                            Edit Product
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

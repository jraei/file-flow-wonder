
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
    { 
        key: "select", 
        label: "",
        sortable: false, 
        class: "w-10" 
    },
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
    { key: "reference", label: "reference" },
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
// Category filter
const selectedCategories = ref([]);
// Selected products for bulk actions
const selectedProducts = ref([]);
const selectAll = ref(false);

// Game validation options
const validationGames = [
    '8 Ball Pool', 'AOV', 'Apex Legends', 'Call Of Duty', 'Dragon City',
    'Dragon Raja', 'Free Fire', 'Genshin Impact', 'Higgs Domino',
    'Honkai Impact', 'Lords Mobile', 'Marvel Super War', 'Mobile Legends',
    'Mobile Legends Adventure', 'Point Blank', 'Ragnarok M', 
    'Tom Jerry Chase', 'Top Eleven', 'Valorant'
];

// Search term for game validation
const gameSearchTerm = ref('');
const filteredGames = computed(() => {
    if (!gameSearchTerm.value) return validationGames;
    return validationGames.filter(game => 
        game.toLowerCase().includes(gameSearchTerm.value.toLowerCase())
    );
});

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

watch(
    () => selectedCategories.value,
    (newValue) => {
        // Implement category filter
        if (newValue && newValue.length > 0) {
            router.get(
                route("products.index"),
                {
                    provider_id: selectedProvider.value,
                    kategori_ids: newValue,
                    search: props.filters?.search,
                    sort: props.filters?.sort,
                    direction: props.filters?.direction,
                },
                {
                    preserveState: true,
                    replace: true,
                }
            );
        } else {
            router.get(
                route("products.index"),
                {
                    provider_id: selectedProvider.value,
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
    }
);

// Watch for selectAll changes
watch(
    () => selectAll.value,
    (newValue) => {
        if (newValue) {
            // Select all products
            selectedProducts.value = products.value.map(product => product.id);
        } else {
            // Deselect all products
            selectedProducts.value = [];
        }
    }
);

// Method to toggle product selection
const toggleProductSelection = (productId) => {
    const index = selectedProducts.value.indexOf(productId);
    if (index === -1) {
        selectedProducts.value.push(productId);
    } else {
        selectedProducts.value.splice(index, 1);
    }
    
    // Update selectAll state
    selectAll.value = selectedProducts.value.length === products.value.length;
};

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

// Bulk actions
const performBulkAction = (action) => {
    if (selectedProducts.value.length === 0) {
        proxy.$showSwalConfirm({
            title: "Error",
            text: "Please select at least one product",
            icon: "error",
        });
        return;
    }

    if (action === 'delete') {
        proxy.$showSwalConfirm({
            title: "Warning",
            text: `Are you sure you want to delete ${selectedProducts.value.length} selected products?`,
            icon: "warning",
            confirmButtonText: "Yes, delete them",
            onConfirm: () => {
                router.post(route("products.bulk-action"), {
                    product_ids: selectedProducts.value,
                    action: 'delete'
                }, {
                    preserveScroll: true,
                    onSuccess: () => {
                        selectedProducts.value = [];
                        selectAll.value = false;
                    }
                });
            },
        });
    } else if (action === 'profit') {
        showProfitModal.value = true;
    }
};

const showViewModal = ref(false);
const showValidationModal = ref(false);
const showProfitModal = ref(false);
const selectedData = ref(null);
const isLoading = ref(false);
const currentValidationId = ref('');
const bulkProfitValue = ref(0);
const profitType = ref('percent'); // percent or fixed

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

const openValidationModal = (item) => {
    selectedData.value = item;
    currentValidationId.value = item.validasi_id;
    showValidationModal.value = true;
    gameSearchTerm.value = '';
};

const saveValidationID = () => {
    router.post(route("products.update-validation", selectedData.value.id), {
        validasi_id: currentValidationId.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showValidationModal.value = false;
        }
    });
};

const saveBulkProfit = () => {
    router.post(route("profit-produks.bulk-update"), {
        product_ids: selectedProducts.value,
        value: bulkProfitValue.value,
        type: profitType.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showProfitModal.value = false;
            selectedProducts.value = [];
            selectAll.value = false;
        }
    });
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
    reference: "",
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

const closeValidationModal = () => {
    showValidationModal.value = false;
    currentValidationId.value = '';
};

const closeProfitModal = () => {
    showProfitModal.value = false;
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

// Format percentage for slider display
const formattedProfitValue = computed(() => {
    if (profitType.value === 'percent') {
        return bulkProfitValue.value + '%';
    } else {
        return 'x' + bulkProfitValue.value;
    }
});
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
                <div class="flex flex-wrap items-center gap-3">
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

                    <!-- Category filter - NEW -->
                    <div class="flex-grow max-w-xs">
                        <label
                            for="category_filter"
                            class="block mb-1 text-sm font-medium text-gray-300"
                            >Filter by Category</label
                        >
                        <select
                            id="category_filter"
                            v-model="selectedCategories"
                            class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                            multiple
                        >
                            <option
                                v-for="category in kategori_list"
                                :key="category.id"
                                :value="category.id"
                            >
                                {{ category.kategori_name }}
                            </option>
                        </select>
                    </div>
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

            <!-- Bulk Actions - NEW -->
            <div v-if="selectedProducts.length > 0" class="flex flex-wrap items-center gap-2 mt-3 border-t border-gray-700 pt-3">
                <span class="text-sm text-gray-300">{{ selectedProducts.length }} products selected</span>
                <div class="flex flex-wrap gap-2">
                    <button 
                        @click="performBulkAction('profit')"
                        class="flex items-center px-3 py-1 space-x-2 text-sm text-white transition-all duration-200 rounded-lg shadow-lg bg-secondary hover:bg-secondary/80"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Set Profit</span>
                    </button>
                    <button 
                        @click="performBulkAction('delete')"
                        class="flex items-center px-3 py-1 space-x-2 text-sm text-white transition-all duration-200 rounded-lg shadow-lg bg-red-600 hover:bg-red-700"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Delete Selected</span>
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

                    <!-- Custom cell rendering for checkbox column -->
                    <template #cell(select)="{ item }">
                        <div class="flex items-center justify-center">
                            <input 
                                type="checkbox" 
                                :checked="selectedProducts.includes(item.id)" 
                                @change="toggleProductSelection(item.id)"
                                class="w-4 h-4 rounded text-primary bg-dark-sidebar border-gray-700 focus:ring-2 focus:ring-primary"
                            />
                        </div>
                    </template>

                    <!-- Custom header for checkbox column -->
                    <template #header(select)>
                        <div class="flex items-center justify-center">
                            <input 
                                type="checkbox" 
                                v-model="selectAll"
                                class="w-4 h-4 rounded text-primary bg-dark-sidebar border-gray-700 focus:ring-2 focus:ring-primary"
                            />
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
                                        @click="openValidationModal(item)"
                                        class="block w-full px-4 py-2 text-sm text-left text-gray-300 hover:bg-dark-lighter hover:text-blue-400"
                                    >
                                        Set Validation
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
                                    for="reference"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Reference</label
                                >
                                <input
                                    id="reference"
                                    v-model="currentData.reference"
                                    type="text"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Reference code"
                                    name="reference"
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
                                reference
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.reference }}
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

        <!-- Game Validation ID Modal - NEW -->
        <Modal :show="showValidationModal" @close="closeValidationModal" max-width="lg">
            <div class="p-3 sm:p-4 md:p-6 border border-gray-700 rounded-lg bg-dark-card max-h-[80vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white sm:text-xl">
                        Set Game Validation
                    </h3>
                    <button
                        @click="closeValidationModal"
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

                <div class="mb-4">
                    <label for="game_search" class="block mb-1 text-sm font-medium text-gray-300">
                        Search Games
                    </label>
                    <input
                        id="game_search"
                        v-model="gameSearchTerm"
                        type="text"
                        class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                        placeholder="Search for a game..."
                    />
                </div>

                <!-- Galaxy map interface -->
                <div class="grid grid-cols-2 gap-2 mb-4 sm:grid-cols-3 md:grid-cols-4">
                    <div 
                        v-for="game in filteredGames" 
                        :key="game"
                        @click="currentValidationId = game"
                        :class="[
                            'p-3 text-center rounded-lg cursor-pointer transition-all duration-200 hover:transform hover:scale-105',
                            currentValidationId === game 
                                ? 'bg-primary/40 border-2 border-primary shadow-glow-primary' 
                                : 'bg-dark-lighter border border-gray-700 hover:border-primary/60'
                        ]"
                    >
                        <!-- Pulsing effect for selected game -->
                        <div class="relative">
                            <div 
                                v-if="currentValidationId === game"
                                class="absolute inset-0 rounded-full bg-primary/20 animate-ping"
                            ></div>
                            <div class="flex items-center justify-center w-10 h-10 mx-auto mb-2 text-white rounded-full bg-primary/30">
                                {{ game.charAt(0) }}
                            </div>
                        </div>
                        <p class="text-xs font-medium text-white sm:text-sm">{{ game }}</p>
                    </div>
                </div>

                <div class="flex flex-col justify-end pt-3 space-y-2 sm:flex-row sm:pt-4 sm:space-y-0 sm:space-x-3">
                    <button
                        type="button"
                        @click="closeValidationModal"
                        class="w-full px-4 py-2 text-gray-300 rounded-lg bg-dark-lighter hover:text-white sm:w-auto"
                    >
                        Cancel
                    </button>
                    <button
                        @click="saveValidationID"
                        class="w-full px-4 py-2 text-white transition-all duration-200 rounded-lg shadow-lg bg-primary hover:bg-primary-hover hover:shadow-glow-primary sm:w-auto"
                    >
                        Save Validation
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Bulk Profit Modal - NEW -->
        <Modal :show="showProfitModal" @close="closeProfitModal" max-width="md">
            <div class="p-3 sm:p-4 md:p-6 border border-gray-700 rounded-lg bg-dark-card max-h-[80vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white sm:text-xl">
                        Set Bulk Profit
                    </h3>
                    <button
                        @click="closeProfitModal"
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

                <div class="space-y-4">
                    <!-- Profit Type Selection -->
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-300">
                            Profit Type
                        </label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input 
                                    type="radio" 
                                    v-model="profitType" 
                                    value="percent" 
                                    class="text-primary focus:ring-primary"
                                />
                                <span class="ml-2 text-white">Percentage</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input 
                                    type="radio" 
                                    v-model="profitType" 
                                    value="multiplier" 
                                    class="text-primary focus:ring-primary"
                                />
                                <span class="ml-2 text-white">Multiplier</span>
                            </label>
                        </div>
                    </div>

                    <!-- Cosmic slider with stars -->
                    <div class="mb-4">
                        <label for="profit_value" class="block mb-1 text-sm font-medium text-gray-300">
                            Profit Value: <span class="text-primary font-bold">{{ formattedProfitValue }}</span>
                        </label>
                        <div class="relative">
                            <!-- Cosmic stars background -->
                            <div class="absolute inset-0 overflow-hidden rounded-lg">
                                <div class="absolute inset-0 opacity-20 bg-dark-sidebar">
                                    <div v-for="i in 20" :key="i"
                                        :style="{
                                            position: 'absolute',
                                            top: `${Math.random() * 100}%`,
                                            left: `${Math.random() * 100}%`,
                                            width: `${Math.random() * 3 + 1}px`,
                                            height: `${Math.random() * 3 + 1}px`,
                                            borderRadius: '50%',
                                            backgroundColor: 'white'
                                        }"
                                    ></div>
                                </div>
                            </div>
                            
                            <input
                                id="profit_value"
                                v-model="bulkProfitValue"
                                :min="profitType === 'percent' ? 0 : 1"
                                :max="profitType === 'percent' ? 100 : 5"
                                :step="profitType === 'percent' ? 1 : 0.1"
                                type="range"
                                class="w-full h-2 rounded-lg appearance-none cursor-pointer bg-primary/30"
                            />
                        </div>
                        <div class="flex justify-between mt-1 text-xs text-gray-400">
                            <span>{{ profitType === 'percent' ? '0%' : 'x1' }}</span>
                            <span>{{ profitType === 'percent' ? '100%' : 'x5' }}</span>
                        </div>
                    </div>

                    <div class="mt-2">
                        <label for="profit_input" class="block mb-1 text-sm font-medium text-gray-300">
                            Manual Input
                        </label>
                        <input
                            id="profit_input"
                            v-model.number="bulkProfitValue"
                            type="number"
                            :min="profitType === 'percent' ? 0 : 1"
                            :max="profitType === 'percent' ? 100 : 5"
                            :step="profitType === 'percent' ? 1 : 0.1"
                            class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                        />
                    </div>

                    <div class="mt-3 p-3 bg-dark-sidebar/50 rounded-lg">
                        <p class="text-sm text-gray-300">
                            This will set <span class="text-primary font-medium">{{ formattedProfitValue }}</span> profit for 
                            <span class="text-primary font-medium">{{ selectedProducts.length }}</span> selected products.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col justify-end pt-3 space-y-2 sm:flex-row sm:pt-4 sm:space-y-0 sm:space-x-3">
                    <button
                        type="button"
                        @click="closeProfitModal"
                        class="w-full px-4 py-2 text-gray-300 rounded-lg bg-dark-lighter hover:text-white sm:w-auto"
                    >
                        Cancel
                    </button>
                    <button
                        @click="saveBulkProfit"
                        class="w-full px-4 py-2 text-white transition-all duration-200 rounded-lg shadow-lg bg-primary hover:bg-primary-hover hover:shadow-glow-primary sm:w-auto group"
                    >
                        <span class="relative inline-block">
                            <!-- Supernova animation triggered on hover -->
                            <span class="absolute inset-0 transform scale-0 rounded-full bg-primary opacity-0 group-hover:opacity-100 group-hover:scale-[3] group-hover:animate-ping"></span>
                            <span class="relative">Apply Profit</span>
                        </span>
                    </button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

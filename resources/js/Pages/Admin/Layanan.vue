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
    services: Object,
    produkList: Object,
    providerList: Object,
    errors: Object,
    filters: Object,
});

const { proxy } = getCurrentInstance();

const services = computed(() => props.services.data || []);

const columns = [
    { key: "id", label: "ID" },
    { key: "nama_layanan", label: "Service Name" },
    { key: "kode_layanan", label: "Service Code" },
    { key: "produk_id", label: "Product" },
    { key: "provider_id", label: "Provider" },
    { key: "harga_beli", label: "Base Price" },
    { key: "harga_beli_idr", label: "Base Price (IDR)" },
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
            route("services.index"),
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

// Currency rate update form
const showRateKursForm = ref(false);
const rateKursData = ref({
    rate_kurs: "",
    provider_id: "",
    provider_name: "",
});

const openRateKursForm = (provider) => {
    rateKursData.value = {
        rate_kurs: provider.rate_kurs,
        provider_id: provider.id,
        provider_name: provider.provider_name,
    };
    showRateKursForm.value = true;
};

const closeRateKursForm = () => {
    showRateKursForm.value = false;
};

const updateRateKurs = () => {
    router.patch(
        route("providers.updateRateKurs", rateKursData.value.provider_id),
        { rate_kurs: rateKursData.value.rate_kurs },
        {
            preserveScroll: true,
            onSuccess: () => {
                closeRateKursForm();
            },
        }
    );
};

// Get services from API
const isLoading = ref(false);

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
        route("services.getServicesByProvider", selectedProvider.value),
        {
            onSuccess: () => {
                isLoading.value = false;
            },
        }
    );
};

const syncPrices = () => {
    isLoading.value = true;
    router.post(route("providers.syncHargaLayanan"), {
        onSuccess: () => {
            isLoading.value = false;
        },
    });
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

// Service view/edit/delete handlers
const showViewModal = ref(false);
const selectedData = ref(null);
const isViewLoading = ref(false);

const handleView = async (item) => {
    isViewLoading.value = true;
    selectedData.value = { ...item, loading: true };
    showViewModal.value = true;

    try {
        const response = await axios.get(route("services.show", item.id));
        selectedData.value = response.data.layanan;
    } catch (error) {
        console.error("Error fetching service details:", error);
        proxy.$showSwalConfirm({
            title: "Error",
            text: "Failed to load service details",
            icon: "error",
        });
    } finally {
        isViewLoading.value = false;
    }
};

const handleEdit = (item) => {
    imagePreviews.value.thumbnail = null;
    openEditForm(item);
};

const handleDelete = (item) => {
    proxy.$showSwalConfirm({
        title: "Warning",
        text: `Are you sure you want to delete "${item.nama_layanan}"?`,
        icon: "warning",
        onConfirm: () => {
            router.delete(route("services.destroy", item.id), {
                preserveScroll: true,
            });
        },
    });
};

// Form handling
const showForm = ref(false);
const formMode = ref("add");
const currentData = ref({});

const openAddForm = () => {
    formMode.value = "add";
    imagePreviews.value.gambar = null;
    currentData.value = {
        nama_layanan: "",
        kode_layanan: "",
        produk_id: "",
        provider_id: selectedProvider.value || "",
        harga_beli: 0,
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
        router.post(route("services.store"), currentData.value, {
            forceFormData: true,
            preserveScroll: true,
        });
    } else {
        currentData.value._method = "put";
        router.post(
            route("services.update", currentData.value.id),
            currentData.value,
            {
                forceFormData: true,
                preserveScroll: true,
            }
        );
    }

    closeForm();
};

// Image handling
const imagePreviews = ref({
    gambar: null,
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

// Format currency
const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
};

// Get product name or provider name
const getProductName = (id) => {
    if (!props.produkList) return "Loading...";
    const product = props.produkList.find((p) => p.id == id);
    return product ? product.nama : "Unknown Product";
};

const getProviderName = (id) => {
    if (!props.providerList) return "Loading...";
    const provider = props.providerList.find((p) => p.id == id);
    return provider ? provider.provider_name : "Unknown Provider";
};

const getProviderRateKurs = (id) => {
    if (!props.providerList) return 1;
    const provider = props.providerList.find((p) => p.id == id);
    return provider ? provider.rate_kurs : 1;
};

// Calculate price in IDR (for form preview)
const calculatedPriceIDR = computed(() => {
    if (!currentData.value.harga_beli || !currentData.value.provider_id)
        return 0;
    const rate = getProviderRateKurs(currentData.value.provider_id);
    return currentData.value.harga_beli * rate;
});
</script>

<template>
    <Head title="Services Management" />

    <AdminLayout title="Services Management">
        <div
            v-if="Object.keys(errors || {}).length > 0"
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
                            v-for="provider in providerList"
                            :key="provider.id"
                            :value="provider.id"
                        >
                            {{ provider.provider_name }}
                        </option>
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2 mt-2 sm:mt-0">
                    <button
                        @click="syncPrices"
                        class="flex items-center px-3 py-2 space-x-2 text-white transition-all duration-200 rounded-lg shadow-lg bg-secondary hover:bg-secondary-hover hover:shadow-glow-secondary"
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
                        <span>Sync Prices</span>
                    </button>

                    <button
                        v-if="selectedProvider"
                        @click="
                            openRateKursForm(
                                providerList.find(
                                    (p) => p.id == selectedProvider
                                )
                            )
                        "
                        class="flex items-center px-3 py-2 space-x-2 text-white transition-all duration-200 rounded-lg shadow-lg bg-secondary hover:bg-secondary-hover hover:shadow-glow-secondary"
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
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <span>Update Rate</span>
                    </button>

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
                        <span>Get Services from API</span>
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
                        <span>Delete Services</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Services Data Table -->
        <div
            class="w-full border rounded-lg shadow-lg border-primary/40 bg-dark-card"
        >
            <!-- Improved table container with explicit scrolling -->
            <div class="max-w-full overflow-x-auto">
                <DataTable
                    :data="services"
                    :columns="columns"
                    :filters="filters"
                    route="services.index"
                    class="w-full whitespace-nowrap"
                >
                    <template #title>Services List</template>

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
                            <span>Add Service</span>
                        </button>
                    </template>

                    <!-- Custom cell renderers -->
                    <template #cell(produk_id)="{ item }">
                        <span class="text-gray-200">{{
                            getProductName(item.produk_id)
                        }}</span>
                    </template>

                    <template #cell(provider_id)="{ item }">
                        <span class="text-gray-200">{{
                            getProviderName(item.provider_id)
                        }}</span>
                    </template>

                    <template #cell(harga_beli)="{ item }">
                        <span class="text-gray-200">{{ item.harga_beli }}</span>
                    </template>

                    <template #cell(harga_beli_idr)="{ item }">
                        <span class="text-gray-200">{{
                            formatCurrency(item.harga_beli_idr)
                        }}</span>
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
                </DataTable>
            </div>
            <Pagination :links="props.services.links" />
        </div>

        <!-- Service Form Modal -->
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
                                ? "Add New Service"
                                : "Edit Service"
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
                                    for="nama_layanan"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Service Name</label
                                >
                                <input
                                    id="nama_layanan"
                                    v-model="currentData.nama_layanan"
                                    type="text"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Service Name"
                                    required
                                />
                            </div>
                            <div>
                                <label
                                    for="kode_layanan"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Service Code</label
                                >
                                <input
                                    id="kode_layanan"
                                    v-model="currentData.kode_layanan"
                                    type="text"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Service Code"
                                    required
                                />
                            </div>
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
                                    <option value="" disabled>
                                        Select Product
                                    </option>
                                    <option
                                        v-for="product in produkList"
                                        :key="product.id"
                                        :value="product.id"
                                    >
                                        {{ product.nama }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label
                                    for="provider_id"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Provider</label
                                >
                                <select
                                    id="provider_id"
                                    v-model="currentData.provider_id"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    required
                                >
                                    <option value="" disabled>
                                        Select Provider
                                    </option>
                                    <option
                                        v-for="provider in providerList"
                                        :key="provider.id"
                                        :value="provider.id"
                                    >
                                        {{ provider.provider_name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label
                                    for="harga_beli"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Base Price</label
                                >
                                <input
                                    id="harga_beli"
                                    v-model="currentData.harga_beli"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Base Price"
                                    required
                                />
                            </div>
                            <div>
                                <label
                                    for="harga_beli_idr_preview"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Base Price (IDR) Preview</label
                                >
                                <input
                                    id="harga_beli_idr_preview"
                                    :value="formatCurrency(calculatedPriceIDR)"
                                    type="text"
                                    class="w-full px-3 py-2 text-white bg-gray-700 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                    readonly
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
                                    v-model="currentData.status"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    required
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    for="catatan"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Notes</label
                                >
                                <input
                                    id="catatan"
                                    v-model="currentData.catatan"
                                    type="text"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Notes"
                                />
                            </div>

                            <div class="sm:col-span-2">
                                <label
                                    for="gambar"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Image</label
                                >
                                <div
                                    v-if="getImagePreview('gambar').value"
                                    class="mb-2"
                                >
                                    <img
                                        :src="getImagePreview('gambar').value"
                                        alt="Service Preview"
                                        class="object-cover w-24 h-24 border rounded-lg shadow-md sm:w-32 sm:h-32 border-primary/60"
                                    />
                                </div>

                                <input
                                    id="gambar"
                                    @change="handleFileUpload($event, 'gambar')"
                                    type="file"
                                    class="w-full px-2 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    accept="image/*"
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
                                        ? "Create Service"
                                        : "Update Service"
                                }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Rate Kurs Update Modal -->
        <div
            v-if="showRateKursForm"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black/50"
            @click.self="closeRateKursForm"
        >
            <div
                class="relative w-full max-w-md p-3 mx-4 border border-gray-700 rounded-lg shadow-lg sm:p-4 md:p-6 bg-dark-card"
                @click.stop
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white sm:text-xl">
                        Update Currency Rate
                    </h3>
                    <button
                        @click="closeRateKursForm"
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

                <form @submit.prevent="updateRateKurs">
                    <div class="space-y-4">
                        <div>
                            <label
                                for="provider_name"
                                class="block mb-1 text-sm font-medium text-gray-300"
                                >Provider</label
                            >
                            <input
                                id="provider_name"
                                type="text"
                                :value="rateKursData.provider_name"
                                class="w-full px-3 py-2 text-white bg-gray-700 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                readonly
                            />
                        </div>
                        <div>
                            <label
                                for="rate_kurs"
                                class="block mb-1 text-sm font-medium text-gray-300"
                                >Currency Rate (1 unit = X IDR)</label
                            >
                            <input
                                id="rate_kurs"
                                v-model="rateKursData.rate_kurs"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                placeholder="Currency Rate"
                                required
                            />
                            <p class="mt-1 text-xs text-gray-400">
                                This will recalculate prices for all services
                                from this provider.
                            </p>
                        </div>
                        <div class="flex justify-end pt-4 space-x-3">
                            <button
                                type="button"
                                @click="closeRateKursForm"
                                class="px-4 py-2 text-gray-300 rounded-lg bg-dark-lighter hover:text-white"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 text-white transition-all duration-200 rounded-lg shadow-lg bg-secondary hover:bg-secondary-hover hover:shadow-glow-secondary"
                            >
                                Update Rate
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Service View Modal -->
        <Modal :show="showViewModal" @close="closeViewModal" max-width="2xl">
            <div
                class="p-3 sm:p-4 md:p-6 border border-gray-700 rounded-lg bg-dark-card max-h-[80vh] overflow-y-auto"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white sm:text-xl">
                        Service Details
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

                <div
                    v-if="isViewLoading"
                    class="flex justify-center py-6 sm:py-8"
                >
                    <div
                        class="w-8 h-8 border-4 rounded-full sm:w-10 sm:h-10 animate-spin border-primary border-t-transparent"
                    ></div>
                </div>

                <div v-else-if="selectedData" class="space-y-3 sm:space-y-4">
                    <div
                        class="grid grid-cols-1 gap-2 sm:gap-3 sm:grid-cols-2 md:grid-cols-3"
                    >
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">ID</p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.id }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Service Name
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.nama_layanan }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Service Code
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.kode_layanan }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Product
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{
                                    selectedData.produk
                                        ? selectedData.produk.nama
                                        : getProductName(selectedData.produk_id)
                                }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Provider
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{
                                    selectedData.provider
                                        ? selectedData.provider.provider_name
                                        : getProviderName(
                                              selectedData.provider_id
                                          )
                                }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Base Price
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ formatCurrency(selectedData.harga_beli) }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Base Price (IDR)
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{
                                    formatCurrency(selectedData.harga_beli_idr)
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
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Notes
                            </p>
                            <p
                                class="text-sm font-medium text-white sm:text-base"
                            >
                                {{ selectedData.catatan || "-" }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Created At
                            </p>
                            <p
                                class="text-sm font-medium text-white sm:text-base"
                            >
                                {{
                                    new Date(
                                        selectedData.created_at
                                    ).toLocaleString()
                                }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Updated At
                            </p>
                            <p
                                class="text-sm font-medium text-white sm:text-base"
                            >
                                {{
                                    new Date(
                                        selectedData.updated_at
                                    ).toLocaleString()
                                }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="selectedData.gambar"
                        class="p-3 rounded-lg bg-dark-lighter"
                    >
                        <p class="mb-2 text-sm text-gray-400">Image</p>
                        <img
                            :src="'/storage/' + selectedData.gambar"
                            alt="Service Image"
                            class="object-cover h-auto max-w-full border rounded-lg shadow-md max-h-48 border-primary/60"
                        />
                    </div>

                    <div
                        class="flex flex-col justify-end pt-3 space-y-2 sm:flex-row sm:pt-4 sm:space-y-0 sm:space-x-3"
                    >
                        <button
                            @click="openEditForm(selectedData)"
                            class="w-full px-4 py-2 text-white transition-all duration-200 rounded-lg shadow-lg sm:w-auto bg-primary hover:bg-primary-hover hover:shadow-glow-primary"
                        >
                            Edit Service
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

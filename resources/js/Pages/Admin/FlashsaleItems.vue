<script setup>
import { ref, watch, computed, getCurrentInstance } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import Modal from "@/Components/Modal.vue";
import Dropdown from "@/Components/Dropdown.vue";
import Pagination from "@/Components/Pagination.vue";
import axios from "axios";

const props = defineProps({
    flashsaleItems: Object,
    events: Array,
    selectedEventId: [String, Number],
    errors: Object,
    filters: Object,
});

const { proxy } = getCurrentInstance();

// Active event
const selectedEvent = ref(props.selectedEventId || null);

// Column definitions for the table
const columns = [
    { key: "id", label: "ID" },
    {
        key: "layanan_name",
        label: "Service",
        format: (_, item) => {
            return item.layanan ? item.layanan.nama_layanan : "—";
        },
    },
    {
        key: "original_price",
        label: "Buy Price",
        format: (_, item) => {
            return item.layanan
                ? "Rp " + item.layanan.harga_beli_idr ||
                      "Rp " + item.layanan.harga_beli
                : "—";
        },
    },
    {
        key: "harga_flashsale",
        label: "Flash Price",
        format: (value) => {
            return "Rp " + value;
        },
    },
    {
        key: "stok_tersedia",
        label: "Stock",
        format: (value) => {
            return value === null ? "∞" : value;
        },
    },
    {
        key: "batas_user",
        label: "User Limit",
        format: (value) => {
            return value === null ? "∞" : value;
        },
    },
    {
        key: "status",
        label: "Status",
        format: (value) => {
            const statusClasses =
                value === "active"
                    ? "bg-green-500/20 text-green-400"
                    : "bg-red-500/20 text-red-400";

            return `<span class="${statusClasses} px-2 py-1 rounded-xl text-xs">${value}</span>`;
        },
    },
];

// View modal states
const showViewModal = ref(false);
const selectedItem = ref(null);
const isLoading = ref(false);

// Form modal states
const showForm = ref(false);
const formMode = ref("add"); // 'add' or 'edit'
const currentItem = ref(null);

// Bulk assign modal states
const showBulkAssignModal = ref(false);
const bulkAssignData = ref({
    flashsale_event_id: props.selectedEventId || "",
    layanan_ids: [],
    markup_percentage: 10,
    stok_tersedia: null,
    batas_user: null,
    status: "active",
});
const availableServices = ref([]);
const selectedServices = ref([]);
const isLoadingServices = ref(false);
const serviceSearchQuery = ref("");

// Handle event selection
const handleEventChange = (eventId) => {
    selectedEvent.value = eventId;
    router.get(
        route("flashsale-items.index"),
        {
            event_id: eventId,
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
    selectedItem.value = { ...item, loading: true };
    showViewModal.value = true;

    try {
        const response = await axios.get(
            route("flashsale-items.show", item.id)
        );
        selectedItem.value = response.data.item;
    } catch (error) {
        console.error("Error fetching item details:", error);
        proxy.$showSwalConfirm({
            title: "Error",
            text: "Failed to load item details",
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
        title: "Are you sure?",
        text: `Delete flash sale item for "${
            item.layanan?.name || "this service"
        }"?`,
        icon: "warning",
        confirmButtonText: "Yes, delete it!",
        onConfirm: () => {
            router.delete(route("flashsale-items.destroy", item.id), {
                preserveScroll: true,
            });
        },
    });
};

const openAddForm = () => {
    if (!selectedEvent.value) {
        proxy.$showSwalConfirm({
            title: "Error",
            text: "Please select a flash sale event first",
            icon: "error",
            showCancelButton: false,
            confirmButtonText: "OK",
        });
        return;
    }

    formMode.value = "add";
    currentItem.value = {
        flashsale_event_id: selectedEvent.value,
        layanan_id: "",
        harga_flashsale: 0,
        stok_tersedia: null,
        batas_user: null,
        status: "active",
    };
    showForm.value = true;

    // Load available services
    loadAvailableServices();
};

const loadAvailableServices = async () => {
    if (!selectedEvent.value) return;

    isLoadingServices.value = true;
    // console.log("Loading available services for event:", selectedEvent.value);

    try {
        // Make sure this URL matches exactly what's defined in the routes
        const response = await axios.get(
            route("flashsale-items.available-services"),
            {
                params: { event_id: selectedEvent.value },
            }
        );

        console.log("Available services:", response.data.services);

        availableServices.value = response.data.services || [];
    } catch (error) {
        console.error("Error fetching available services:", error);
        proxy.$showSwalConfirm({
            title: "Error",
            text: "Layanan/Event tidak ditemukan!",
            icon: "error",
            showCancelButton: false,
            confirmButtonText: "OK",
        });
    } finally {
        isLoadingServices.value = false;
    }
};

const openEditForm = (item) => {
    if (showViewModal.value) {
        showViewModal.value = false;
    }

    formMode.value = "edit";

    currentItem.value = { ...item };
    showForm.value = true;
};

const openBulkAssignModal = () => {
    if (!selectedEvent.value) {
        proxy.$showSwalConfirm({
            title: "Error",
            text: "Please select a flash sale event first",
            icon: "error",
            showCancelButton: false,
            confirmButtonText: "OK",
        });
        return;
    }

    bulkAssignData.value = {
        flashsale_event_id: selectedEvent.value,
        layanan_ids: [],
        markup_percentage: 10,
        stok_tersedia: null,
        batas_user: null,
        status: "active",
    };

    selectedServices.value = [];
    loadAvailableServices();
    showBulkAssignModal.value = true;
};

const closeForm = () => {
    showForm.value = false;
};

const closeBulkAssignModal = () => {
    showBulkAssignModal.value = false;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedItem.value = null;
};

const saveItem = () => {
    if (formMode.value === "add") {
        router.post(route("flashsale-items.store"), currentItem.value, {
            preserveScroll: true,
            onSuccess: () => {
                closeForm();
            },
        });
    } else {
        router.put(
            route("flashsale-items.update", currentItem.value.id),
            currentItem.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeForm();
                },
            }
        );
    }
};

const handleBulkAssign = () => {
    // Collect selected service IDs
    bulkAssignData.value.layanan_ids = selectedServices.value.map(
        (service) => service.id
    );

    if (bulkAssignData.value.layanan_ids.length === 0) {
        proxy.$showSwalConfirm({
            title: "Error",
            text: "Please select at least one service",
            icon: "error",
            showCancelButton: false,
            confirmButtonText: "OK",
        });
        return;
    }

    router.post(route("flashsale-items.bulk-assign"), bulkAssignData.value, {
        preserveScroll: true,
        onSuccess: () => {
            closeBulkAssignModal();
        },
    });
};

const handleBulkDelete = () => {
    if (!selectedEvent.value) return;

    proxy.$showSwalConfirm({
        title: "Are you sure?",
        text: "Delete ALL flash sale items for this event? This action cannot be undone.",
        icon: "warning",
        confirmButtonText: "Yes, delete all",
        onConfirm: async () => {
            try {
                const response = await axios.delete(
                    route("flashsale-items.bulk-delete"),
                    {
                        data: { flashsale_event_id: selectedEvent.value },
                    }
                );

                proxy.$showSwalConfirm({
                    title: "Success",
                    text: response.data.message,
                    icon: "success",
                    showCancelButton: false,
                    confirmButtonText: "OK",
                    onConfirm: () => {
                        // Refresh the list
                        router.reload();
                    },
                });
            } catch (error) {
                console.error("Error during bulk delete:", error);
                proxy.$showSwalConfirm({
                    title: "Error",
                    text: "Failed to delete items",
                    icon: "error",
                    showCancelButton: false,
                    confirmButtonText: "OK",
                });
            }
        },
    });
};

// Service selection for bulk assign
const toggleServiceSelection = (service) => {
    const index = selectedServices.value.findIndex((s) => s.id === service.id);
    if (index >= 0) {
        selectedServices.value.splice(index, 1);
    } else {
        selectedServices.value.push(service);
    }
};

const isServiceSelected = (service) => {
    return selectedServices.value.some((s) => s.id === service.id);
};

// Filter services based on search query
const filteredServices = computed(() => {
    if (!serviceSearchQuery.value) return availableServices.value;

    const query = serviceSearchQuery.value.toLowerCase();
    return availableServices.value.filter((service) => {
        return (
            service.nama_layanan.toLowerCase().includes(query) ||
            service.produk?.nama?.toLowerCase().includes(query)
        );
    });
});

// Get current event name
const currentEventName = computed(() => {
    if (!selectedEvent.value) return "All Events";
    const event = props.events.find(
        (e) => e.id === Number(selectedEvent.value)
    );
    return event ? event.event_name : "Unknown Event";
});

// Calculate discount percentage
const calculateDiscount = (original, sale) => {
    if (!original || !sale) return 0;
    const discount = ((original - sale) / original) * 100;
    return Math.round(discount);
};

// Calculate flash sale price based on percentage
const calculateMarkupPrice = (service, percentage) => {
    const price = service.harga_beli_idr || service.harga_beli;
    if (!price) return 0;

    const markup = price * (1 + percentage / 100);
    return Math.max(1, Math.ceil(markup));
};

// Reset all discounted prices when percentage changes
watch(
    () => bulkAssignData.value.markup_percentage,
    () => {
        // Recalculate would happen in the template
    }
);
</script>

<template>
    <Head title="Flash Sale Items" />

    <AdminLayout title="Flash Sale Items">
        <div
            v-if="Object.keys(errors).length > 0"
            class="px-4 py-3 mb-4 text-sm text-white rounded-lg bg-red-500/80"
        >
            <ul>
                <li v-for="(error, key) in errors" :key="key">{{ error }}</li>
            </ul>
        </div>

        <!-- Event Selection and Actions -->
        <div
            class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between"
        >
            <div class="flex flex-col w-full gap-2 md:w-1/3">
                <label class="text-sm font-medium text-gray-300"
                    >Select Flash Sale Event</label
                >
                <select
                    v-model="selectedEvent"
                    @change="handleEventChange(selectedEvent)"
                    class="px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                >
                    <option value="">All Events</option>
                    <option
                        v-for="event in events"
                        :key="event.id"
                        :value="event.id"
                    >
                        {{ event.event_name }}
                    </option>
                </select>
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    @click="openAddForm"
                    class="flex items-center px-4 py-2 space-x-2 text-white transition-all duration-200 rounded-lg shadow-lg bg-primary hover:bg-primary-hover hover:shadow-glow-primary"
                    :disabled="!selectedEvent"
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
                    <span>Add Item</span>
                </button>

                <button
                    @click="openBulkAssignModal"
                    class="flex items-center px-4 py-2 space-x-2 text-white transition-all duration-200 rounded-lg shadow-lg bg-secondary hover:bg-secondary-hover"
                    :disabled="!selectedEvent"
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
                            d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z"
                        />
                    </svg>
                    <span>Bulk Assign</span>
                </button>

                <button
                    v-if="selectedEvent"
                    @click="handleBulkDelete"
                    class="flex items-center px-4 py-2 space-x-2 text-white transition-all duration-200 bg-red-600 rounded-lg shadow-lg hover:bg-red-700"
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
                    <span>Delete All</span>
                </button>
            </div>
        </div>

        <div
            class="w-full border rounded-lg shadow-lg border-primary/40 bg-dark-card"
        >
            <DataTable
                :data="flashsaleItems.data"
                :columns="columns"
                :filters="filters"
                route="flashsale-items.index"
                class="max-w-full"
            >
                <template #title>
                    Flash Sale Items
                    <span
                        v-if="selectedEvent"
                        class="ml-2 text-sm font-normal text-gray-400"
                    >
                        for {{ currentEventName }}
                    </span>
                </template>

                <template #addButton>
                    <!-- Button moved to top of page for better UX -->
                    <div></div>
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
            <Pagination :links="flashsaleItems.links" />
        </div>

        <!-- Add/Edit Flash Sale Item Modal -->
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
                                ? "Add Flash Sale Item"
                                : "Edit Flash Sale Item"
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

                <form @submit.prevent="saveItem" class="space-y-4">
                    <!-- Service Selection (only in add mode) -->
                    <div v-if="formMode === 'add'">
                        <label
                            for="layanan_id"
                            class="block mb-1 text-sm font-medium text-gray-300"
                            >Service</label
                        >
                        <select
                            id="layanan_id"
                            v-model="currentItem.layanan_id"
                            class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                            required
                        >
                            <option value="">Select a service</option>
                            <option
                                v-for="service in availableServices"
                                :key="service.id"
                                :value="service.id"
                            >
                                {{ service.nama_layanan }} (Rp
                                {{
                                    service.harga_beli_idr ||
                                    service.harga_beli
                                }})
                            </option>
                        </select>
                    </div>

                    <!-- Service Display (in edit mode) -->
                    <div v-else class="p-3 rounded-lg bg-dark-lighter">
                        <p class="text-xs text-gray-400">Service</p>
                        <p class="font-medium text-white">
                            {{
                                currentItem.layanan?.nama_layanan ||
                                "Unknown Service"
                            }}
                        </p>
                    </div>

                    <!-- Price Fields -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Original Price (disabled) -->
                        <div
                            v-if="formMode === 'edit' || currentItem.layanan_id"
                        >
                            <label
                                class="block mb-1 text-sm font-medium text-gray-300"
                                >Buy Price</label
                            >
                            <input
                                type="text"
                                :value="
                                    formMode === 'edit'
                                        ? currentItem.layanan?.harga_beli_idr ||
                                          currentItem.layanan?.harga_beli ||
                                          0
                                        : availableServices.find(
                                              (s) =>
                                                  s.id ===
                                                  currentItem.layanan_id
                                          )?.harga_beli_idr ||
                                          availableServices.find(
                                              (s) =>
                                                  s.id ===
                                                  currentItem.layanan_id
                                          )?.harga_beli ||
                                          0
                                "
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar"
                                disabled
                            />
                        </div>

                        <!-- Flash Sale Price -->
                        <div>
                            <label
                                for="harga_flashsale"
                                class="block mb-1 text-sm font-medium text-gray-300"
                                >Flash Sale Price</label
                            >
                            <input
                                id="harga_flashsale"
                                v-model="currentItem.harga_flashsale"
                                type="number"
                                min="1"
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                required
                            />
                        </div>
                    </div>

                    <!-- Stock & User Limit -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Stock -->
                        <div>
                            <label
                                for="stok_tersedia"
                                class="block mb-1 text-sm font-medium text-gray-300"
                                >Stock (Leave empty for unlimited)</label
                            >
                            <input
                                id="stok_tersedia"
                                v-model="currentItem.stok_tersedia"
                                type="number"
                                min="1"
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                            />
                        </div>

                        <!-- User Limit -->
                        <div>
                            <label
                                for="batas_user"
                                class="block mb-1 text-sm font-medium text-gray-300"
                                >User Limit (Leave empty for unlimited)</label
                            >
                            <input
                                id="batas_user"
                                v-model="currentItem.batas_user"
                                type="number"
                                min="1"
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                            />
                        </div>
                    </div>

                    <!-- Status Field -->
                    <div>
                        <label
                            for="status"
                            class="block mb-1 text-sm font-medium text-gray-300"
                            >Status</label
                        >
                        <select
                            id="status"
                            v-model="currentItem.status"
                            class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div
                        class="flex flex-col-reverse items-center justify-end pt-4 space-y-2 space-y-reverse sm:flex-row sm:space-y-0 sm:space-x-2"
                    >
                        <button
                            type="button"
                            @click="closeForm"
                            class="w-full px-4 py-2 mt-2 text-gray-300 rounded-lg bg-dark-lighter hover:text-white sm:w-auto sm:mt-0"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="w-full px-4 py-2 text-white transition-all duration-200 rounded-lg shadow-lg bg-primary hover:bg-primary-hover hover:shadow-glow-primary sm:w-auto"
                        >
                            {{
                                formMode === "add" ? "Add Item" : "Update Item"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bulk Assign Modal -->
        <div
            v-if="showBulkAssignModal"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black bg-opacity-50"
            @click.self="closeBulkAssignModal"
        >
            <div
                class="relative w-full max-w-3xl mx-4 p-3 border border-gray-700 rounded-lg shadow-lg sm:p-4 md:p-6 bg-dark-card max-h-[90vh] overflow-y-auto"
                @click.stop
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-white">
                        Bulk Assign Services to Flash Sale
                    </h3>
                    <button
                        @click="closeBulkAssignModal"
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

                <form @submit.prevent="handleBulkAssign" class="space-y-6">
                    <!-- Discount Settings -->
                    <div class="p-4 space-y-2 rounded-lg bg-dark-lighter">
                        <h4 class="mb-2 text-sm font-bold text-gray-300">
                            Markup Settings
                        </h4>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <!-- Discount Percentage -->
                            <div>
                                <label
                                    for="markup_percentage"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Markup Percentage</label
                                >
                                <div class="relative">
                                    <input
                                        id="markup_percentage"
                                        v-model="
                                            bulkAssignData.markup_percentage
                                        "
                                        type="number"
                                        min="1"
                                        max="100"
                                        class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                        required
                                    />
                                    <span
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400"
                                        >%</span
                                    >
                                </div>
                            </div>

                            <!-- Stock -->
                            <div>
                                <label
                                    for="bulk_stok_tersedia"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Stock (Leave empty for unlimited)</label
                                >
                                <input
                                    id="bulk_stok_tersedia"
                                    v-model="bulkAssignData.stok_tersedia"
                                    type="number"
                                    min="1"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                />
                            </div>

                            <!-- User Limit -->
                            <div>
                                <label
                                    for="bulk_batas_user"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >User Limit (Leave empty for
                                    unlimited)</label
                                >
                                <input
                                    id="bulk_batas_user"
                                    v-model="bulkAssignData.batas_user"
                                    type="number"
                                    min="1"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                />
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label
                                for="bulk_status"
                                class="block mb-1 text-sm font-medium text-gray-300"
                                >Status</label
                            >
                            <select
                                id="bulk_status"
                                v-model="bulkAssignData.status"
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Service Selection -->
                    <div class="p-4 space-y-4 rounded-lg bg-dark-lighter">
                        <div
                            class="flex flex-col mb-4 space-y-2 sm:flex-row sm:justify-between sm:items-center sm:space-y-0"
                        >
                            <h4 class="text-sm font-medium text-gray-300">
                                Select Services
                            </h4>

                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-5 h-5 text-gray-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                        />
                                    </svg>
                                </div>
                                <input
                                    v-model="serviceSearchQuery"
                                    type="text"
                                    placeholder="Search services..."
                                    class="w-full py-2 pl-10 pr-4 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                />
                            </div>
                        </div>

                        <div
                            v-if="isLoadingServices"
                            class="flex justify-center py-6"
                        >
                            <div
                                class="w-8 h-8 border-4 rounded-full animate-spin border-primary border-t-transparent"
                            ></div>
                        </div>

                        <div
                            v-else-if="availableServices.length === 0"
                            class="py-6 text-center text-gray-400"
                        >
                            No services available for this flash sale event.
                        </div>

                        <div
                            v-else
                            class="grid grid-cols-1 gap-2 overflow-y-auto max-h-64 md:grid-cols-2"
                        >
                            <div
                                v-for="service in filteredServices"
                                :key="service.id"
                                class="p-3 border border-gray-700 rounded-lg cursor-pointer hover:border-primary"
                                :class="{
                                    'bg-primary/10 border-primary':
                                        isServiceSelected(service),
                                }"
                                @click="toggleServiceSelection(service)"
                            >
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="font-medium text-white">
                                            {{ service.nama_layanan }}
                                        </p>
                                        <p class="text-xs text-gray-400">
                                            {{
                                                service.produk?.nama ||
                                                "No product"
                                            }}
                                        </p>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <div
                                            class="flex items-center space-x-2"
                                        >
                                            <span class="text-xs text-gray-400"
                                                >Original:</span
                                            >
                                            <span
                                                class="font-medium text-white"
                                                >{{
                                                    service.harga_beli_idr ||
                                                    service.harga_beli
                                                }}</span
                                            >
                                        </div>
                                        <div
                                            class="flex items-center space-x-2"
                                        >
                                            <span class="text-xs text-gray-400"
                                                >Flash Sale:</span
                                            >
                                            <span
                                                class="font-medium text-green-400"
                                            >
                                                {{
                                                    calculateMarkupPrice(
                                                        service,
                                                        bulkAssignData.markup_percentage
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="flex items-center justify-between mt-2"
                                >
                                    <div class="flex items-center space-x-2">
                                        <svg
                                            v-if="isServiceSelected(service)"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="w-4 h-4 text-primary"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 13l4 4L19 7"
                                            />
                                        </svg>
                                        <span
                                            v-if="isServiceSelected(service)"
                                            class="text-xs text-primary"
                                            >Selected</span
                                        >
                                        <span
                                            v-else
                                            class="text-xs text-gray-400"
                                            >Click to select</span
                                        >
                                    </div>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full bg-primary/20 text-primary"
                                    >
                                        {{ bulkAssignData.markup_percentage }}%
                                        Markup
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Services Counter -->
                        <div class="flex items-center justify-between mt-4">
                            <span class="text-sm text-gray-300">
                                {{ selectedServices.length }} services selected
                            </span>

                            <button
                                type="button"
                                @click="
                                    selectedServices = filteredServices.slice()
                                "
                                class="px-3 py-1 text-xs text-white transition-colors rounded-lg bg-secondary hover:bg-secondary-hover"
                            >
                                Select All ({{ filteredServices.length }})
                            </button>
                        </div>
                    </div>

                    <div
                        class="flex flex-col-reverse items-center justify-end pt-4 space-y-2 space-y-reverse sm:flex-row sm:space-y-0 sm:space-x-2"
                    >
                        <button
                            type="button"
                            @click="closeBulkAssignModal"
                            class="w-full px-4 py-2 mt-2 text-gray-300 rounded-lg bg-dark-lighter hover:text-white sm:w-auto sm:mt-0"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="w-full px-4 py-2 text-white transition-all duration-200 rounded-lg shadow-lg bg-primary hover:bg-primary-hover hover:shadow-glow-primary sm:w-auto"
                            :disabled="selectedServices.length === 0"
                        >
                            Add {{ selectedServices.length }} Services to Flash
                            Sale
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- View Flash Sale Item Modal -->
        <Modal :show="showViewModal" @close="closeViewModal" max-width="2xl">
            <div
                class="p-3 sm:p-4 md:p-6 border border-gray-700 rounded-lg bg-dark-card max-h-[80vh] overflow-y-auto"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white sm:text-xl">
                        Flash Sale Item Details
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

                <div v-else-if="selectedItem" class="space-y-6">
                    <!-- Service Info -->
                    <div class="p-4 rounded-lg bg-dark-lighter">
                        <h4 class="mb-3 text-sm font-medium text-gray-300">
                            Service Information
                        </h4>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <p class="text-xs text-gray-400">
                                    Service Name
                                </p>
                                <p class="text-sm font-medium text-white">
                                    {{ selectedItem.layanan?.nama_layanan }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Product</p>
                                <p class="text-sm font-medium text-white">
                                    {{ selectedItem.layanan?.produk?.nama }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Provider</p>
                                <p class="text-sm font-medium text-white">
                                    {{
                                        selectedItem.layanan?.provider
                                            ?.provider_name
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Price Comparison -->
                    <div class="p-4 rounded-lg bg-dark-lighter">
                        <h4 class="mb-3 text-sm font-medium text-gray-300">
                            Price Information
                        </h4>

                        <div
                            class="flex items-center justify-between p-3 mb-3 rounded-lg bg-dark-sidebar"
                        >
                            <div>
                                <p class="text-xs text-gray-400">
                                    Regular Price
                                </p>
                                <p class="text-2xl font-medium text-white">
                                    {{
                                        selectedItem.layanan?.harga_beli_idr ||
                                        selectedItem.layanan?.harga_beli
                                    }}
                                </p>
                            </div>
                            <div
                                class="p-2 text-white rounded-full bg-primary/20"
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
                                        d="M19 9l-7 7-7-7"
                                    />
                                </svg>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-400">
                                    Flash Sale Price
                                </p>
                                <p class="text-2xl font-medium text-green-400">
                                    {{ selectedItem.harga_flashsale }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-center p-2 rounded-lg bg-dark-sidebar"
                        >
                            <span
                                class="px-3 py-1 text-sm font-medium text-green-400 rounded-full bg-green-500/20"
                            >
                                {{
                                    calculateDiscount(
                                        selectedItem.layanan?.harga_beli_idr ||
                                            selectedItem.layanan?.harga_beli,
                                        selectedItem.harga_flashsale
                                    )
                                }}% OFF
                            </span>
                        </div>
                    </div>

                    <!-- Stock & Limits -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="p-4 rounded-lg bg-dark-lighter">
                            <p class="text-sm text-gray-400">Stock</p>
                            <p class="text-xl font-medium text-white">
                                {{
                                    selectedItem.stok_tersedia === null
                                        ? "∞ (Unlimited)"
                                        : selectedItem.stok_tersedia
                                }}
                            </p>
                        </div>
                        <div class="p-4 rounded-lg bg-dark-lighter">
                            <p class="text-sm text-gray-400">User Limit</p>
                            <p class="text-xl font-medium text-white">
                                {{
                                    selectedItem.batas_user === null
                                        ? "∞ (Unlimited)"
                                        : selectedItem.batas_user
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Event & Status -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="p-4 rounded-lg bg-dark-lighter">
                            <p class="text-sm text-gray-400">
                                Flash Sale Event
                            </p>
                            <p class="text-sm font-medium text-white">
                                {{ selectedItem.flashsale_event?.event_name }}
                                <span
                                    :class="
                                        selectedItem.flashsale_event?.status ===
                                        'active'
                                            ? 'bg-green-500/20 text-green-400'
                                            : 'bg-red-500/20 text-red-400'
                                    "
                                    class="px-2 py-1 text-xs rounded-full"
                                >
                                    {{ selectedItem.flashsale_event?.status }}
                                </span>
                            </p>
                        </div>
                        <div class="p-4 rounded-lg bg-dark-lighter">
                            <p class="text-sm text-gray-400">Item Status</p>
                            <div class="mt-2">
                                <span
                                    :class="
                                        selectedItem.status === 'active'
                                            ? 'bg-green-500/20 text-green-400'
                                            : 'bg-red-500/20 text-red-400'
                                    "
                                    class="px-2 py-1 text-sm rounded-full"
                                >
                                    {{ selectedItem.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex flex-col justify-end pt-3 space-y-2 sm:flex-row sm:pt-4 sm:space-y-0 sm:space-x-3"
                    >
                        <button
                            @click="openEditForm(selectedItem)"
                            class="w-full px-4 py-2 text-white transition-all duration-200 rounded-lg shadow-lg sm:w-auto bg-primary hover:bg-primary-hover hover:shadow-glow-primary"
                        >
                            Edit Item
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

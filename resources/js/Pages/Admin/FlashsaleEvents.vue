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
    flashsaleEvents: Object,
    errors: Object,
    filters: Object,
});

const { proxy } = getCurrentInstance();

// Column definitions for the table
const columns = [
    { key: "id", label: "ID" },
    { key: "event_name", label: "Event Name" },
    {
        key: "event_start_date",
        label: "Start Date",
        format: (value) => {
            return new Date(value).toLocaleString();
        },
    },
    {
        key: "event_end_date",
        label: "End Date",
        format: (value) => {
            return new Date(value).toLocaleString();
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
    { key: "item_count", label: "Items" },
];

// View modal states
const showViewModal = ref(false);
const selectedEvent = ref(null);
const isLoading = ref(false);

// Form modal states
const showForm = ref(false);
const formMode = ref("add"); // 'add' or 'edit'
const currentEvent = ref(null);

// Date validation
const dateValidation = ref({
    start_date_error: "",
    end_date_error: "",
});

// Handle view action
const handleView = async (item) => {
    isLoading.value = true;
    selectedEvent.value = { ...item, loading: true };
    showViewModal.value = true;

    try {
        const response = await axios.get(
            route("flashsale-events.show", item.id)
        );
        selectedEvent.value = response.data.flashsaleEvent;
    } catch (error) {
        console.error("Error fetching event details:", error);
        proxy.$showSwalConfirm({
            title: "Error",
            text: "Failed to load event details",
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
        text: `Delete flash sale event "${item.event_name}"?`,
        icon: "warning",
        confirmButtonText: "Yes, delete it!",
        onConfirm: () => {
            router.delete(route("flashsale-events.destroy", item.id), {
                preserveScroll: true,
            });
        },
    });
};

const toggleEventStatus = async (item) => {
    try {
        const response = await axios.patch(
            route("flashsale-events.toggle-status", item.id)
        );

        // Update the item status in the table
        item.status = response.data.status;

        proxy.$showSwalConfirm({
            title: "Success",
            text: response.data.message,
            icon: "success",
            showCancelButton: false,
            confirmButtonText: "OK",
        });
    } catch (error) {
        console.error("Error toggling status:", error);

        let errorMessage = "Failed to toggle event status";
        if (error.response?.data?.errors?.event_dates) {
            errorMessage = error.response.data.errors.event_dates[0];
        }

        proxy.$showSwalConfirm({
            title: "Error",
            text: errorMessage,
            icon: "error",
            showCancelButton: false,
            confirmButtonText: "OK",
        });
    }
};

const openAddForm = () => {
    formMode.value = "add";
    currentEvent.value = {
        event_name: "",
        event_start_date: new Date().toISOString().split(".")[0].slice(0, 16), // Format for datetime-local input
        event_end_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000)
            .toISOString()
            .split(".")[0]
            .slice(0, 16), // One week from now
        status: "active",
    };
    dateValidation.value = { start_date_error: "", end_date_error: "" };
    showForm.value = true;
};

const openEditForm = (event) => {
    if (showViewModal.value) {
        showViewModal.value = false;
    }

    formMode.value = "edit";

    // Format dates for datetime-local input
    const startDate = new Date(event.event_start_date);
    const endDate = new Date(event.event_end_date);

    currentEvent.value = {
        ...event,
        event_start_date: startDate.toISOString().split(".")[0].slice(0, 16),
        event_end_date: endDate.toISOString().split(".")[0].slice(0, 16),
    };

    dateValidation.value = { start_date_error: "", end_date_error: "" };
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedEvent.value = null;
};

const validateDates = () => {
    dateValidation.value = { start_date_error: "", end_date_error: "" };

    const startDate = new Date(currentEvent.value.event_start_date);
    const endDate = new Date(currentEvent.value.event_end_date);
    const now = new Date();

    // For new events, start date must be in the future
    if (formMode.value === "add" && startDate < now) {
        dateValidation.value.start_date_error =
            "Start date must be in the future";
        return false;
    }

    // End date must be after start date
    if (endDate <= startDate) {
        dateValidation.value.end_date_error =
            "End date must be after start date";
        return false;
    }

    return true;
};

const saveEvent = () => {
    if (!validateDates()) {
        return;
    }

    if (formMode.value === "add") {
        router.post(route("flashsale-events.store"), currentEvent.value, {
            preserveScroll: true,
            onSuccess: () => {
                closeForm();
            },
        });
    } else {
        router.put(
            route("flashsale-events.update", currentEvent.value.id),
            currentEvent.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeForm();
                },
            }
        );
    }
};

const viewItems = (event) => {
    router.visit(route("flashsale-items.index", { event_id: event.id }));
};

// Calculate status for timeline display
const getTimelineStatus = (event) => {
    const now = new Date();
    const startDate = new Date(event.event_start_date);
    const endDate = new Date(event.event_end_date);

    if (now < startDate) return "upcoming";
    if (now > endDate) return "ended";
    return "active";
};
</script>

<template>
    <Head title="Flash Sale Events" />

    <AdminLayout title="Flash Sale Events">
        <div
            v-if="Object.keys(errors).length > 0"
            class="px-4 py-3 mb-4 text-sm text-white rounded-lg bg-red-500/80"
        >
            <ul>
                <li v-for="(error, key) in errors" :key="key">{{ error }}</li>
            </ul>
        </div>
        <div
            class="w-full border rounded-lg shadow-lg border-primary/40 bg-dark-card"
        >
            <DataTable
                :data="flashsaleEvents.data"
                :columns="columns"
                :filters="filters"
                route="flashsale-events.index"
                class="max-w-full"
            >
                <template #title> Flash Sale Events </template>

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
                        <span>Add Flash Sale Event</span>
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
                                    @click="viewItems(item)"
                                    class="block w-full px-4 py-2 text-sm text-left text-gray-300 hover:bg-dark-lighter hover:text-blue-400"
                                >
                                    Manage Items
                                </button>
                                <button
                                    @click="toggleEventStatus(item)"
                                    class="block w-full px-4 py-2 text-sm text-left text-gray-300 hover:bg-dark-lighter hover:text-yellow-400"
                                >
                                    Toggle Status
                                </button>
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
            <Pagination :links="flashsaleEvents.links" />
        </div>

        <!-- Add/Edit Flash Sale Event Modal -->
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
                                ? "Add New Flash Sale Event"
                                : "Edit Flash Sale Event"
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

                <form @submit.prevent="saveEvent" class="space-y-4">
                    <!-- Event Name Field -->
                    <div>
                        <label
                            for="event_name"
                            class="block mb-1 text-sm font-medium text-gray-300"
                            >Event Name</label
                        >
                        <input
                            id="event_name"
                            v-model="currentEvent.event_name"
                            type="text"
                            class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="Enter event name"
                            required
                        />
                    </div>

                    <!-- Date Range Fields -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Start Date -->
                        <div>
                            <label
                                for="event_start_date"
                                class="block mb-1 text-sm font-medium text-gray-300"
                                >Start Date & Time</label
                            >
                            <input
                                id="event_start_date"
                                v-model="currentEvent.event_start_date"
                                type="datetime-local"
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                required
                            />
                            <p
                                v-if="dateValidation.start_date_error"
                                class="mt-1 text-sm text-red-400"
                            >
                                {{ dateValidation.start_date_error }}
                            </p>
                        </div>

                        <!-- End Date -->
                        <div>
                            <label
                                for="event_end_date"
                                class="block mb-1 text-sm font-medium text-gray-300"
                                >End Date & Time</label
                            >
                            <input
                                id="event_end_date"
                                v-model="currentEvent.event_end_date"
                                type="datetime-local"
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                required
                            />
                            <p
                                v-if="dateValidation.end_date_error"
                                class="mt-1 text-sm text-red-400"
                            >
                                {{ dateValidation.end_date_error }}
                            </p>
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
                            v-model="currentEvent.status"
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
                                formMode === "add"
                                    ? "Create Event"
                                    : "Update Event"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- View Flash Sale Event Modal -->
        <Modal :show="showViewModal" @close="closeViewModal" max-width="4xl">
            <div
                class="p-3 sm:p-4 md:p-6 border border-gray-700 rounded-lg bg-dark-card max-h-[80vh] overflow-y-auto"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white sm:text-xl">
                        Flash Sale Event Details
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

                <div v-else-if="selectedEvent" class="space-y-6">
                    <!-- Event Details -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="p-3 rounded-lg bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Event ID
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedEvent.id }}
                            </p>
                        </div>
                        <div class="p-3 rounded-lg bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">Name</p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedEvent.event_name }}
                            </p>
                        </div>
                        <div class="p-3 rounded-lg bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Start Date
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{
                                    new Date(
                                        selectedEvent.event_start_date
                                    ).toLocaleString()
                                }}
                            </p>
                        </div>
                        <div class="p-3 rounded-lg bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                End Date
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{
                                    new Date(
                                        selectedEvent.event_end_date
                                    ).toLocaleString()
                                }}
                            </p>
                        </div>
                        <div class="p-3 rounded-lg bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Status
                            </p>
                            <p>
                                <span
                                    :class="
                                        selectedEvent.status === 'active'
                                            ? 'bg-green-500/20 text-green-400'
                                            : 'bg-red-500/20 text-red-400'
                                    "
                                    class="px-2 py-1 text-xs rounded-full"
                                >
                                    {{ selectedEvent.status }}
                                </span>
                            </p>
                        </div>
                        <div class="p-3 rounded-lg bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Items Count
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{
                                    selectedEvent.item
                                        ? selectedEvent.item.length
                                        : selectedEvent.item_count || 0
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="p-4 rounded-lg bg-dark-lighter">
                        <h4 class="mb-3 text-sm font-medium text-gray-300">
                            Event Timeline
                        </h4>
                        <div class="relative pt-1">
                            <div class="flex mb-2">
                                <div class="w-full">
                                    <div
                                        class="relative flex h-2 mb-4 overflow-hidden text-xs rounded bg-dark-sidebar"
                                    >
                                        <div
                                            :class="{
                                                'bg-blue-500':
                                                    getTimelineStatus(
                                                        selectedEvent
                                                    ) === 'upcoming',
                                                'bg-green-500':
                                                    getTimelineStatus(
                                                        selectedEvent
                                                    ) === 'active',
                                                'bg-gray-500':
                                                    getTimelineStatus(
                                                        selectedEvent
                                                    ) === 'ended',
                                            }"
                                            class="flex flex-col justify-center text-center text-white transition-all duration-1000 ease-out shadow-none whitespace-nowrap"
                                            :style="{ width: '100%' }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="flex justify-between text-xs text-gray-400"
                            >
                                <span>{{
                                    new Date(
                                        selectedEvent.event_start_date
                                    ).toLocaleDateString()
                                }}</span>
                                <span>
                                    <span
                                        :class="{
                                            'bg-blue-500/20 text-blue-400':
                                                getTimelineStatus(
                                                    selectedEvent
                                                ) === 'upcoming',
                                            'bg-green-500/20 text-green-400':
                                                getTimelineStatus(
                                                    selectedEvent
                                                ) === 'active',
                                            'bg-gray-500/20 text-gray-400':
                                                getTimelineStatus(
                                                    selectedEvent
                                                ) === 'ended',
                                        }"
                                        class="px-2 py-1 rounded-full"
                                    >
                                        {{ getTimelineStatus(selectedEvent) }}
                                    </span>
                                </span>
                                <span>{{
                                    new Date(
                                        selectedEvent.event_end_date
                                    ).toLocaleDateString()
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Item List -->
                    <div
                        v-if="
                            selectedEvent.item && selectedEvent.item.length > 0
                        "
                        class="p-4 rounded-lg bg-dark-lighter"
                    >
                        <h4 class="mb-3 text-sm font-medium text-gray-300">
                            Flash Sale Items ({{ selectedEvent.item.length }})
                        </h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-400 uppercase">
                                    <tr>
                                        <th class="px-3 py-2">Service</th>
                                        <th class="px-3 py-2">Flash Price</th>
                                        <th class="px-3 py-2">Stock</th>
                                        <th class="px-3 py-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="item in selectedEvent.item"
                                        :key="item.id"
                                        class="border-t border-gray-700"
                                    >
                                        <td class="px-3 py-2 text-white">
                                            {{ item.layanan?.name || "—" }}
                                        </td>
                                        <td class="px-3 py-2 text-white">
                                            {{ item.harga_flashsale }}
                                        </td>
                                        <td class="px-3 py-2 text-white">
                                            {{ item.stok || "∞" }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <span
                                                :class="
                                                    item.status === 'active'
                                                        ? 'bg-green-500/20 text-green-400'
                                                        : 'bg-red-500/20 text-red-400'
                                                "
                                                class="px-2 py-1 text-xs rounded-full"
                                            >
                                                {{ item.status }}
                                            </span>
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
                            @click="viewItems(selectedEvent)"
                            class="w-full px-4 py-2 text-white transition-all duration-200 rounded-lg shadow-lg sm:w-auto bg-secondary hover:bg-secondary-hover"
                        >
                            Manage Items
                        </button>
                        <button
                            @click="openEditForm(selectedEvent)"
                            class="w-full px-4 py-2 text-white transition-all duration-200 rounded-lg shadow-lg sm:w-auto bg-primary hover:bg-primary-hover hover:shadow-glow-primary"
                        >
                            Edit Event
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

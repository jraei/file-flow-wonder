<script setup>
import { ref, computed, getCurrentInstance } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import Modal from "@/Components/Modal.vue";
import Dropdown from "@/Components/Dropdown.vue";
import Pagination from "@/Components/Pagination.vue";
import axios from "axios";

const props = defineProps({
    vouchers: Object,
    errors: Object,
    filters: Object,
});

const { proxy } = getCurrentInstance();

// flash message
// nyalakan kalau mau pake alert biasa
// const page = usePage();
// const successMessage = computed(() => page.props.flash?.success || null);

// Mock data for Voucher
// In a real application, this would come from the backend
const vouchers = computed(() => props.vouchers.data || []);

// Column definitions for the table
const columns = [
    { key: "id", label: "ID" },
    { key: "code", label: "Kode" },
    {
        key: "discount_type",
        label: "Tipe",
        format(value) {
            if (value === "percentage") {
                return "%";
            } else if (value === "fixed") {
                return "Fixed";
            }
        },
    },
    {
        key: "discount_value",
        label: "Jumlah Diskon",
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
const selectedData = ref(null);
const isLoading = ref(false);

// Handle view action
const handleView = async (item) => {
    isLoading.value = true;
    selectedData.value = { ...item, loading: true };
    showViewModal.value = true;

    try {
        const response = await axios.get(route("vouchers.show", item.id));

        selectedData.value = response.data.voucher;
    } catch (error) {
        console.error("Error fetching vouchers details:", error);
        proxy.$showSwalConfirm({
            title: "Error",
            text: "Failed to load vouchers details",
            icon: "error",
        });
    } finally {
        isLoading.value = false;
    }
};

const handleEdit = (item) => {
    console.log("Edit", item);
    openEditForm(item);
    // In a real app, you would redirect to the edit page or show a modal
};

const handleDelete = (item) => {
    proxy.$showSwalConfirm({
        onConfirm: () => {
            // Logika penghapusan data
            router.delete(route("vouchers.destroy", item.id), {
                preserveScroll: true,
            });
        },
    });

    // In a real app, you would show a confirmation dialog before deleting
};

// Form modal states
const showForm = ref(false);
const formMode = ref("add"); // 'add' or 'edit'
const currentData = ref(null);

const openAddForm = () => {
    formMode.value = "add";
    currentData.value = {
        discount_type: "percentage",
        min_purchase: 0,
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
    // In a real app, you would send a request to the backend
    // console.log("Save category", currentData.value);

    if (formMode.value === "add") {
        // Simulate adding a new category
        router.post(route("vouchers.store"), currentData.value, {
            // preserveState: false,
            preserveScroll: true,
            onSuccess: () => {
                // successMessage.value = page.props.flash.success;
                // currentData.value.category_name = "";
            },
        });
    } else {
        // Simulate updating an existing category
        router.put(
            route("vouchers.update", currentData.value.id),
            currentData.value,
            {
                preserveScroll: true,
            }
        );
    }

    closeForm();
};
</script>

<template>
    <Head title="Vouchers" />

    <AdminLayout title="Vouchers">
        <!-- nyalakan kalau mau pake alert biasa -->
        <!-- <div
            v-if="successMessage"
            class="px-8 py-3 mb-4 text-sm text-white bg-green-500/80"
        >
            {{ successMessage }}
        </div> -->
        <div
            v-if="Object.keys(errors).length > 0"
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
                :data="vouchers"
                :columns="columns"
                :filters="filters"
                route="vouchers.index"
                class="max-w-full whitespace-nowrap"
            >
                <template #title> Voucher List </template>

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
                        <span>Add Voucher</span>
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

                <template #cell(icon)="{ item }">
                    <div
                        class="flex items-center justify-center w-8 h-8 text-white rounded-full bg-primary/20"
                    >
                        {{ item.icon }}
                    </div>
                </template>
            </DataTable>
            <!-- Pagination component -->
            <Pagination :links="props.vouchers.links" />
        </div>

        <!-- Add/Edit Category Modal -->
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
                                ? "Add New Vouchers"
                                : "Edit Vouchers"
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

                <form @submit.prevent="saveDataForm" class="overflow-visible">
                    <div class="space-y-3 sm:space-y-4">
                        <div
                            class="grid grid-cols-1 gap-3 sm:gap-4 sm:grid-cols-2"
                        >
                            <!-- Name Field -->
                            <div>
                                <label
                                    for="code"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Kode Voucher</label
                                >
                                <input
                                    id="code"
                                    v-model="currentData.code"
                                    type="text"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Kode Voucher"
                                    name="code"
                                    required
                                />
                            </div>

                            <div>
                                <label
                                    for="discount_type"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Tipe Diskon</label
                                >
                                <select
                                    id="discount_type"
                                    v-model="currentData.discount_type"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    name="discount_type"
                                    required
                                >
                                    <option value="percentage">
                                        Persentase (%)
                                    </option>
                                    <option value="fixed">
                                        Angka Fix (Rp)
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label
                                    for="discount_value"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Jumlah Diskon</label
                                >
                                <input
                                    id="discount_value"
                                    v-model="currentData.discount_value"
                                    type="number"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Bisa Rp / %"
                                    name="discount_value"
                                    required
                                />
                            </div>
                            <div>
                                <label
                                    for="min_purchase"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Minimum Pembelian
                                </label>
                                <input
                                    id="min_purchase"
                                    v-model="currentData.min_purchase"
                                    type="number"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Minimal Beli (Rp)"
                                    name="min_purchase"
                                    required
                                />
                            </div>
                            <div
                                v-if="
                                    currentData.discount_type === 'percentage'
                                "
                            >
                                <label
                                    for="max_discount"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Maksimal Diskon
                                    <span class="text-red-500"
                                        >(Opsional)</span
                                    ></label
                                >
                                <input
                                    id="max_discount"
                                    v-model="currentData.max_discount"
                                    type="number"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Jumlah maksimal diskon (Rp)"
                                    name="max_discount"
                                />
                            </div>
                            <div>
                                <label
                                    for="usage_limit"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Limit Penggunaan Voucher
                                    <span class="text-red-500"
                                        >(Opsional)</span
                                    ></label
                                >
                                <input
                                    id="usage_limit"
                                    v-model="currentData.usage_limit"
                                    type="number"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Maksimal penggunaan voucher"
                                    name="usage_limit"
                                />
                            </div>
                            <div>
                                <label
                                    for="end_date"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Waktu berakhir
                                    <span class="text-red-500"
                                        >(Opsional)</span
                                    ></label
                                >
                                <input
                                    id="end_date"
                                    v-model="currentData.end_date"
                                    type="datetime-local"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Waktu berakhir voucher"
                                    name="end_date"
                                />
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
                                    name="status"
                                    v-model="currentData.status"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label
                                    for="description"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Deskripsi Voucher
                                    <span class="text-red-500">(Opsional)</span>
                                </label>
                                <input
                                    id="description"
                                    v-model="currentData.description"
                                    type="text"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Deskripsi Voucher"
                                    name="description"
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
                                        ? "Create Voucher"
                                        : "Update Voucher"
                                }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- View Category Modal -->
        <Modal :show="showViewModal" @close="closeViewModal" max-width="2xl">
            <div
                class="p-3 sm:p-4 md:p-6 border border-gray-700 rounded-lg bg-dark-card max-h-[80vh] overflow-y-auto"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white sm:text-xl">
                        Voucher Details
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
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Voucher ID
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.id }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Kode Voucher
                            </p>
                            <p
                                class="text-sm font-medium text-green-500 truncate sm:text-base"
                            >
                                {{ selectedData.code }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Tipe Diskon
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.discount_type }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Jumlah Diskon
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{
                                    selectedData.discount_type === "percentage"
                                        ? selectedData.discount_value + " %"
                                        : "Rp " + selectedData.discount_value
                                }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Minimum Pembelian
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                Rp {{ selectedData.min_purchase }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Maksimal Diskon
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{
                                    selectedData.max_discount
                                        ? "Rp " + selectedData.max_discount
                                        : "-"
                                }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Limit Penggunaan
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.usage_limit || "Unlimited" }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Jumlah Terpakai
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.usage_count }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Waktu Mulai
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                <!-- format ke waktu jakarta , Indonesia -->
                                {{
                                    new Date(
                                        selectedData.start_date
                                    ).toLocaleString("id-ID", {
                                        timeZone: "Asia/Jakarta",
                                    })
                                }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Waktu Berakhir
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{
                                    selectedData.end_date
                                        ? new Date(
                                              selectedData.end_date
                                          ).toLocaleString("id-ID", {
                                              timeZone: "Asia/Jakarta",
                                          })
                                        : "Lifetime"
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
                        <div
                            class="p-2 rounded-lg sm:col-span-3 sm:p-3 bg-dark-lighter"
                        >
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Deskripsi
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.description || "-" }}
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
                            Edit Voucher
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

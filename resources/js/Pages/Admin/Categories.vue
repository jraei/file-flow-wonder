<script setup>
import { ref, computed, getCurrentInstance } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import Modal from "@/Components/Modal.vue";
import Dropdown from "@/Components/Dropdown.vue";
import Pagination from "@/Components/Pagination.vue";
import MoogoldCategoryModal from "@/Components/Admin/Category/MoogoldCategoryModal.vue";
import BulkAssignModal from "@/Components/Admin/Category/BulkAssignModal.vue";
import axios from "axios";

const props = defineProps({
    categories: Object,
    errors: Object,
    filters: Object,
    staticCategories: Object,
});

const { proxy } = getCurrentInstance();

// flash message
// nyalakan kalau mau pake alert biasa
// const page = usePage();
// const successMessage = computed(() => page.props.flash?.success || null);

// Mock data for categories
// In a real application, this would come from the backend
const categories = computed(() => props.categories.data || []);

// Column definitions for the table
const columns = [
    { key: "id", label: "ID" },
    { key: "kategori_name", label: "Kategori" },
    {
        key: "kode_kategori",
        label: "Moogold Code",
        format: (value, item) => {
            if (!value)
                return '<span class="text-xs text-gray-400">Not linked</span>';

            const categoryName =
                props.staticCategories && props.staticCategories[value]
                    ? props.staticCategories[value]
                    : "Unknown";

            return `<span class="px-2 py-0.5 rounded-full text-xs bg-orange-500/20 text-orange-400 border border-orange-500/30">${value}</span>
                   <span class="ml-1 text-xs text-gray-300">${categoryName}</span>`;
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
const selectedCategory = ref(null);
const isLoading = ref(false);

// Handle view action
const handleView = async (item) => {
    isLoading.value = true;
    selectedCategory.value = { ...item, loading: true };
    showViewModal.value = true;

    try {
        const response = await axios.get(route("categories.show", item.id));

        selectedCategory.value = response.data.category;
    } catch (error) {
        console.error("Error fetching category details:", error);
        proxy.$showSwalConfirm({
            title: "Error",
            text: "Failed to load category details",
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
            router.delete(route("categories.destroy", item.id), {
                preserveScroll: true,
            });
        },
    });

    // In a real app, you would show a confirmation dialog before deleting
};

// Moogold linking modal
const showMoogoldModal = ref(false);
const handleLinkMoogold = (item) => {
    selectedCategory.value = item;
    showMoogoldModal.value = true;
};

// Bulk assign modal
const showBulkAssignModal = ref(false);
const handleBulkAssign = (item) => {
    selectedCategory.value = item;
    showBulkAssignModal.value = true;
};

// Form modal states
const showForm = ref(false);
const formMode = ref("add"); // 'add' or 'edit'
const currentCategory = ref(null);

const openAddForm = () => {
    formMode.value = "add";
    currentCategory.value = {
        kategori_name: "",
        status: "active",
    };
    showForm.value = true;
};

const openEditForm = (category) => {
    if (showViewModal.value) {
        showViewModal.value = false;
    }
    formMode.value = "edit";
    currentCategory.value = { ...category };
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedCategory.value = null;
};

// Close modals and optionally refresh data
const handleModalClose = (refresh = false) => {
    showMoogoldModal.value = false;
    showBulkAssignModal.value = false;
    if (refresh) {
        router.reload({ only: ["categories"] });
    }
};

const saveCategory = () => {
    // In a real app, you would send a request to the backend
    console.log("Save category", currentCategory.value);

    if (formMode.value === "add") {
        // Simulate adding a new category
        router.post(route("categories.store"), currentCategory.value, {
            // preserveState: false,
            preserveScroll: true,
            onSuccess: () => {
                // successMessage.value = page.props.flash.success;
                currentCategory.value.category_name = "";
            },
        });
    } else {
        // Simulate updating an existing category
        router.put(
            route("categories.update", currentCategory.value.id),
            currentCategory.value,
            {
                preserveScroll: true,
            }
        );
    }

    closeForm();
};
</script>

<template>
    <Head title="Categories" />

    <AdminLayout title="Categories">
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
                :data="categories"
                :columns="columns"
                :filters="filters"
                route="categories.index"
                class="max-w-full whitespace-nowrap"
            >
                <template #title> Game Categories </template>

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
                        <span>Add Category</span>
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
                                <hr class="my-1 border-gray-700" />
                                <button
                                    @click="handleLinkMoogold(item)"
                                    class="block w-full px-4 py-2 text-sm text-left text-gray-300 hover:bg-dark-lighter hover:text-orange-400"
                                >
                                    <span class="flex items-center space-x-1">
                                        <span>🔗</span>
                                        <span>Link Moogold</span>
                                    </span>
                                </button>
                                <button
                                    @click="handleBulkAssign(item)"
                                    class="block w-full px-4 py-2 text-sm text-left text-gray-300 hover:bg-dark-lighter hover:text-blue-400"
                                >
                                    <span class="flex items-center space-x-1">
                                        <span>📦</span>
                                        <span>Manage Products</span>
                                    </span>
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
            <Pagination :links="props.categories.links" />
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
                                ? "Add New Category"
                                : "Edit Category"
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

                <form @submit.prevent="saveCategory" class="overflow-visible">
                    <div class="space-y-3 sm:space-y-4">
                        <div
                            class="grid grid-cols-1 gap-3 sm:gap-4 sm:grid-cols-2"
                        >
                            <!-- Name Field -->
                            <div>
                                <label
                                    for="kategori_name"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Kategori</label
                                >
                                <input
                                    id="kategori_name"
                                    v-model="currentCategory.kategori_name"
                                    type="text"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Category Name"
                                    name="kategori_name"
                                    required
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
                                    v-model="currentCategory.status"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
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
                                        ? "Create Category"
                                        : "Update Category"
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
                        Category Details
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

                <div
                    v-else-if="selectedCategory"
                    class="space-y-3 sm:space-y-4"
                >
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Category ID
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedCategory.id }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">Name</p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedCategory.kategori_name }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Status
                            </p>
                            <p>
                                <span
                                    :class="
                                        selectedCategory.status === 'active'
                                            ? 'bg-green-500/20 text-green-400'
                                            : 'bg-red-500/20 text-red-400'
                                    "
                                    class="px-2 py-1 text-xs rounded-full"
                                >
                                    {{ selectedCategory.status }}
                                </span>
                            </p>
                        </div>
                        <div class="p-3 rounded-lg bg-dark-lighter">
                            <p class="text-sm text-gray-400">Product Count</p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedCategory.product_count || 0 }}
                            </p>
                        </div>

                        <!-- Moogold Category Info -->
                        <div
                            class="col-span-1 p-3 rounded-lg bg-dark-lighter sm:col-span-2"
                        >
                            <p class="text-sm text-gray-400">
                                Moogold Category
                            </p>
                            <div class="mt-1">
                                <div
                                    v-if="selectedCategory.kode_kategori"
                                    class="flex items-center space-x-2"
                                >
                                    <span
                                        class="px-2 py-0.5 bg-orange-500/20 text-orange-400 text-xs rounded-full border border-orange-500/30 nebula-pulse"
                                    >
                                        {{ selectedCategory.kode_kategori }}
                                    </span>
                                    <span class="text-sm text-white">
                                        {{
                                            staticCategories &&
                                            staticCategories[
                                                selectedCategory.kode_kategori
                                            ]
                                                ? staticCategories[
                                                      selectedCategory
                                                          .kode_kategori
                                                  ]
                                                : "Unknown Category"
                                        }}
                                    </span>
                                </div>
                                <p v-else class="text-sm text-gray-400">
                                    Not linked to any Moogold category
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex flex-col justify-end pt-3 space-y-2 sm:flex-row sm:pt-4 sm:space-y-0 sm:space-x-3"
                    >
                        <button
                            @click="handleBulkAssign(selectedCategory)"
                            class="w-full px-4 py-2 text-white transition-all duration-200 bg-blue-600 rounded-lg shadow-lg sm:w-auto hover:bg-blue-700"
                        >
                            <span
                                class="flex items-center justify-center space-x-1"
                            >
                                <span>📦</span>
                                <span>Manage Products</span>
                            </span>
                        </button>
                        <button
                            @click="handleLinkMoogold(selectedCategory)"
                            class="w-full px-4 py-2 text-white transition-all duration-200 bg-orange-600 rounded-lg shadow-lg sm:w-auto hover:bg-orange-700"
                        >
                            <span
                                class="flex items-center justify-center space-x-1"
                            >
                                <span>🔗</span>
                                <span>Link Moogold</span>
                            </span>
                        </button>
                        <button
                            @click="openEditForm(selectedCategory)"
                            class="w-full px-4 py-2 text-white transition-all duration-200 rounded-lg shadow-lg sm:w-auto bg-primary hover:bg-primary-hover hover:shadow-glow-primary"
                        >
                            Edit Category
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

        <!-- Moogold Link Modal -->
        <MoogoldCategoryModal
            :show="showMoogoldModal"
            :category="selectedCategory"
            :staticCategories="staticCategories"
            @close="handleModalClose"
        />

        <!-- Bulk Assign Modal -->
        <BulkAssignModal
            :show="showBulkAssignModal"
            :category="selectedCategory"
            @close="handleModalClose"
        />
    </AdminLayout>
</template>

<style scoped>
/* Pulsing nebula borders */
.nebula-pulse {
    animation: nebula-border-pulse 3s infinite alternate;
}

@keyframes nebula-border-pulse {
    0% {
        box-shadow: 0 0 5px rgba(249, 115, 22, 0.3);
    }
    100% {
        box-shadow: 0 0 15px rgba(249, 115, 22, 0.7);
    }
}

/* Prevent animations for users who prefer reduced motion */
@media (prefers-reduced-motion: reduce) {
    .nebula-pulse {
        animation: none;
    }
}
</style>

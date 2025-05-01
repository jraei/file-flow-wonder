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
    users: Object,
    errors: Object,
    role: Object,
    filters: Object,
});

const { proxy } = getCurrentInstance();

// Data from server-side pagination with search and sort applied
const users = computed(() => props.users.data || []);

// Column definitions for the table
const columns = [
    { key: "id", label: "ID" },
    { key: "username", label: "Username" },
    { key: "email", label: "email" },
    { key: "phone", label: "phone" },
    { key: "saldo", label: "saldo" },
    {
        key: "user_role_id",
        label: "Role",
        format: (value) => {
            const role = props.role.find(
                (item) => Number(item.id) === Number(value)
            );
            return role ? role.display_name : "Tidak Diketahui";
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
const selectedData = ref(null);
const isLoading = ref(false);

// Handle view action
const handleView = async (item) => {
    isLoading.value = true;
    selectedData.value = { ...item, loading: true };
    showViewModal.value = true;

    try {
        const response = await axios.get(route("users.show", item.id));
        selectedData.value = response.data.data;
    } catch (error) {
        console.error("Error fetching user details:", error);
        proxy.$showSwalConfirm({
            title: "Error",
            text: "Failed to load user details",
            icon: "error",
        });
    } finally {
        isLoading.value = false;
    }
};

const handleEdit = (item) => {
    console.log("Edit", item);
    openEditForm(item);
};

const handleDelete = (item) => {
    proxy.$showSwalConfirm({
        onConfirm: () => {
            // Delete logic
            router.delete(route("users.destroy", item.id), {
                preserveScroll: true,
            });
        },
    });
};

// Form modal states
const showForm = ref(false);
const formMode = ref("add"); // 'add' or 'edit'
const currentData = ref(null);

const openAddForm = () => {
    formMode.value = "add";
    currentData.value = {
        username: "",
        email: "",
        phone: "",
        saldo: 0,
        user_role_id: "",
        // password: "",
        // password_confirmation: "",
        status: "active",
    };
    showForm.value = true;
};

const openEditForm = (data) => {
    if (showViewModal.value) {
        showViewModal.value = false;
    }
    formMode.value = "edit";
    currentData.value = { ...data, password: "", password_confirmation: "" };
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
        router.post(route("users.store"), currentData.value, {
            preserveScroll: true,
            onSuccess: () => {
                currentData.value = {
                    username: "",
                    email: "",
                    phone: "",
                    saldo: 0,
                    user_role_id: "",
                    password: "",
                    password_confirmation: "",
                    status: "active",
                };
                closeForm();
            },
        });
    } else {
        router.put(
            route("users.update", currentData.value.id),
            currentData.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeForm();
                },
            }
        );
    }
};
</script>

<template>
    <Head title="Users" />

    <AdminLayout title="Users">
        <!-- Flash messages and errors -->
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
                :data="users"
                :columns="columns"
                :filters="filters"
                route="users.index"
                class="max-w-full whitespace-nowrap"
            >
                <template #title> User List </template>

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
                        <span>Add User</span>
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
            </DataTable>
            <!-- Pagination component -->
            <Pagination
                :links="props.users.links"
                :currentPage="props.users.current_page"
                :perPage="props.users.per_page"
                :totalEntries="props.users.total"
            />
        </div>

        <!-- Add/Edit User Modal -->
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
                        {{ formMode === "add" ? "Add New User" : "Edit User" }}
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
                            <!-- Username Field -->
                            <div>
                                <label
                                    for="username"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Username</label
                                >
                                <input
                                    id="username"
                                    v-model="currentData.username"
                                    type="text"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Username"
                                    name="username"
                                    required
                                />
                            </div>

                            <!-- Email Field -->
                            <div>
                                <label
                                    for="email"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Email</label
                                >
                                <input
                                    id="email"
                                    v-model="currentData.email"
                                    type="email"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Email Address"
                                    name="email"
                                    required
                                />
                            </div>

                            <!-- Phone Field -->
                            <div>
                                <label
                                    for="phone"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Phone</label
                                >
                                <input
                                    id="phone"
                                    v-model="currentData.phone"
                                    type="text"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Phone Number"
                                    name="phone"
                                    required
                                />
                            </div>

                            <!-- Saldo Field -->
                            <div>
                                <label
                                    for="saldo"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >Balance</label
                                >
                                <input
                                    id="saldo"
                                    v-model="currentData.saldo"
                                    type="number"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="0"
                                    name="saldo"
                                    required
                                />
                            </div>

                            <!-- Role user_role_id Field -->
                            <div>
                                <label
                                    for="user_role_id"
                                    class="block mb-1 text-sm font-medium text-gray-300"
                                    >User Role</label
                                >
                                <select
                                    id="user_role_id"
                                    v-model="currentData.user_role_id"
                                    class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                    name="user_role_id"
                                    required
                                >
                                    <option
                                        v-for="roleItem in role"
                                        :key="roleItem.id"
                                        :value="roleItem.id"
                                    >
                                        {{ roleItem.display_name }}
                                    </option>
                                </select>
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
                                    <option value="nonactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <!-- Password Fields -->
                        <div>
                            <label
                                for="password"
                                class="block mb-1 text-sm font-medium text-gray-300"
                                >Password</label
                            >
                            <input
                                id="password"
                                v-model="currentData.password"
                                type="password"
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                placeholder="Password"
                                name="password"
                            />
                        </div>

                        <div>
                            <label
                                for="password_confirmation"
                                class="block mb-1 text-sm font-medium text-gray-300"
                                >Confirm Password</label
                            >
                            <input
                                id="password_confirmation"
                                v-model="currentData.password_confirmation"
                                type="password"
                                class="w-full px-3 py-2 text-white border border-gray-700 rounded-lg bg-dark-sidebar focus:ring-2 focus:ring-primary focus:border-transparent"
                                placeholder="Confirm Password"
                                name="password_confirmation"
                            />
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
                                        ? "Create User"
                                        : "Update User"
                                }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- View User Modal -->
        <Modal :show="showViewModal" @close="closeViewModal" max-width="2xl">
            <div
                class="p-3 sm:p-4 md:p-6 border border-gray-700 rounded-lg bg-dark-card max-h-[80vh] overflow-y-auto"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white sm:text-xl">
                        User Details
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
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                User ID
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.id }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Username
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.username }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Email
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.email }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Phone
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.phone }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Balance
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.saldo }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">Role</p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{
                                    role.find(
                                        (role) =>
                                            role.id ===
                                            selectedData.user_role_id
                                    ).display_name
                                }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Balance
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                {{ selectedData.saldo }}
                            </p>
                        </div>
                        <div class="p-2 rounded-lg sm:p-3 bg-dark-lighter">
                            <p class="text-xs text-gray-400 sm:text-sm">
                                Registered At
                            </p>
                            <p
                                class="text-sm font-medium text-white truncate sm:text-base"
                            >
                                <!-- format ke waktu jakarta indonesia -->
                                {{
                                    new Date(
                                        selectedData.created_at
                                    ).toLocaleString("id-ID", {
                                        timeZone: "Asia/Jakarta",
                                    })
                                }}
                                WIB
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
                    </div>

                    <div
                        class="flex flex-col justify-end pt-3 space-y-2 sm:flex-row sm:pt-4 sm:space-y-0 sm:space-x-3"
                    >
                        <button
                            @click="openEditForm(selectedData)"
                            class="w-full px-4 py-2 text-white transition-all duration-200 rounded-lg shadow-lg sm:w-auto bg-primary hover:bg-primary-hover hover:shadow-glow-primary"
                        >
                            Edit User
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

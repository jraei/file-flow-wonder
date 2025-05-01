<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import Modal from "@/Components/Modal.vue";

// Define props
const props = defineProps({
    providers: Object,
    filters: Object,
});

// Table columns
const columns = [
    { key: "id", label: "#" },
    { key: "provider_name", label: "Provider Name" },
    { key: "kode_merchant", label: "Merchant Code" },
    {
        key: "status",
        label: "Status",
        format: (status) => {
            const color =
                status === "active"
                    ? "bg-green-100 text-green-800"
                    : "bg-red-100 text-red-800";
            return `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${color}">${status}</span>`;
        },
    },
];

// State for edit modal
const editModal = ref(false);
const currentProvider = ref({});
const showApiKey = ref(false);
const showPrivateKey = ref(false);
const isLoading = ref(false);
const isMethodLoading = ref(false);

// Open edit modal
const editProvider = (provider) => {
    currentProvider.value = JSON.parse(JSON.stringify(provider));
    showApiKey.value = false;
    showPrivateKey.value = false;
    editModal.value = true;
};

// Toggle status
const toggleStatus = (provider) => {
    isLoading.value = true;

    router.patch(
        route("payment-providers.update", provider.id),
        {
            status: provider.status === "active" ? "inactive" : "active",
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                isLoading.value = false;
            },
            onError: () => {
                isLoading.value = false;
            },
        }
    );
};

// Update provider
const updateProvider = () => {
    isLoading.value = true;

    router.patch(
        route("payment-providers.update", currentProvider.value.id),
        {
            kode_merchant: currentProvider.value.kode_merchant,
            api_id: currentProvider.value.api_id,
            api_key: currentProvider.value.api_key,
            private_key: currentProvider.value.private_key,
            status: currentProvider.value.status,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                editModal.value = false;
                isLoading.value = false;
            },
            onError: () => {
                isLoading.value = false;
            },
        }
    );
};

// Get payment methods
const getPaymentMethods = (provider) => {
    isMethodLoading.value = true;

    router.post(
        route("payment-providers.get-methods-by-provider", provider.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                isMethodLoading.value = false;
            },
            onError: () => {
                isMethodLoading.value = false;
            },
        }
    );
};

// Toggle password visibility
const toggleApiKeyVisibility = () => {
    showApiKey.value = !showApiKey.value;
};

const togglePrivateKeyVisibility = () => {
    showPrivateKey.value = !showPrivateKey.value;
};
</script>

<template>
    <Head title="Payment Providers" />

    <AdminLayout title="Payment Providers">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden shadow-sm bg-dark-card sm:rounded-lg"
                >
                    <div
                        class="overflow-hidden border shadow-md border-primary/40 sm:rounded-lg"
                    >
                        <!-- DataTable Component -->
                        <DataTable
                            :data="providers.data"
                            :columns="columns"
                            :pagination="providers"
                            :filters="filters"
                            route="admin.payment-providers"
                            @view="editProvider"
                        >
                            <template #title> Payment Gateways </template>
                            <!-- Custom Table Cell Templates -->
                            <template #cell(status)="{ item }">
                                <div class="flex items-center">
                                    <!-- Toggle Switch -->
                                    <button
                                        type="button"
                                        class="relative inline-flex flex-shrink-0 h-6 transition-colors duration-200 ease-in-out border-2 border-transparent rounded-full cursor-pointer w-11 focus:outline-none"
                                        :class="
                                            item.status === 'active'
                                                ? 'bg-green-500'
                                                : 'bg-gray-200'
                                        "
                                        @click="toggleStatus(item)"
                                        :disabled="isLoading"
                                    >
                                        <span
                                            class="inline-block w-5 h-5 transition duration-200 ease-in-out transform bg-white rounded-full shadow pointer-events-none ring-0"
                                            :class="
                                                item.status === 'active'
                                                    ? 'translate-x-5'
                                                    : 'translate-x-0'
                                            "
                                        ></span>
                                    </button>
                                    <span
                                        class="ml-2 text-sm"
                                        :class="
                                            item.status === 'active'
                                                ? 'text-green-600'
                                                : 'text-gray-500'
                                        "
                                    >
                                        {{
                                            item.status
                                                .charAt(0)
                                                .toUpperCase() +
                                            item.status.slice(1)
                                        }}
                                    </span>
                                </div>
                            </template>

                            <!-- Custom Actions Template -->
                            <template #actions="{ item }">
                                <button
                                    @click="editProvider(item)"
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-white transition-colors rounded-md md:text-sm bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-4 h-4 mr-1"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                        />
                                    </svg>
                                    Edit
                                </button>
                                <button
                                    @click="getPaymentMethods(item)"
                                    class="inline-flex items-center px-2 py-1 ml-2 text-xs font-medium text-white transition-colors rounded-md md:text-sm bg-secondary hover:bg-secondary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-secondary"
                                    :disabled="isMethodLoading"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-4 h-4 mr-1"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                        />
                                    </svg>
                                    {{
                                        isMethodLoading
                                            ? "Loading..."
                                            : "Get Methods"
                                    }}
                                </button>
                            </template>

                            <!-- Custom Add Button (Empty to remove add button) -->
                            <template #addButton>
                                <!-- Intentionally left empty to hide "Add New" button -->
                            </template>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <Modal :show="editModal" @close="editModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-white">
                    Edit Payment Provider
                </h2>

                <div class="mt-6 space-y-6">
                    <!-- Provider Name (Disabled) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300"
                            >Provider Name</label
                        >
                        <input
                            type="text"
                            v-model="currentProvider.provider_name"
                            class="block w-full mt-1 bg-gray-100 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            disabled
                        />
                    </div>

                    <!-- Merchant Code -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300"
                            >Merchant Code</label
                        >
                        <input
                            type="text"
                            v-model="currentProvider.kode_merchant"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>

                    <!-- API ID -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300"
                            >API ID</label
                        >
                        <input
                            type="text"
                            v-model="currentProvider.api_id"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>

                    <!-- API Key (with toggle visibility) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300"
                            >API Key</label
                        >
                        <div class="relative mt-1">
                            <input
                                :type="showApiKey ? 'text' : 'password'"
                                v-model="currentProvider.api_key"
                                class="block w-full pr-10 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                            <button
                                type="button"
                                @click="toggleApiKeyVisibility"
                                class="absolute inset-y-0 right-0 flex items-center pr-3"
                            >
                                <svg
                                    v-if="showApiKey"
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
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    />
                                </svg>
                                <svg
                                    v-else
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
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Private Key (with toggle visibility) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300"
                            >Private Key</label
                        >
                        <div class="relative mt-1">
                            <input
                                :type="showPrivateKey ? 'text' : 'password'"
                                v-model="currentProvider.private_key"
                                class="block w-full pr-10 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                            <button
                                type="button"
                                @click="togglePrivateKeyVisibility"
                                class="absolute inset-y-0 right-0 flex items-center pr-3"
                            >
                                <svg
                                    v-if="showPrivateKey"
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
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    />
                                </svg>
                                <svg
                                    v-else
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
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Status Toggle -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300"
                            >Status</label
                        >
                        <div class="flex items-center mt-1">
                            <button
                                type="button"
                                class="relative inline-flex flex-shrink-0 h-6 transition-colors duration-200 ease-in-out border-2 border-transparent rounded-full cursor-pointer w-11 focus:outline-none"
                                :class="
                                    currentProvider.status === 'active'
                                        ? 'bg-green-500'
                                        : 'bg-gray-200'
                                "
                                @click="
                                    currentProvider.status =
                                        currentProvider.status === 'active'
                                            ? 'inactive'
                                            : 'active'
                                "
                            >
                                <span
                                    class="inline-block w-5 h-5 transition duration-200 ease-in-out transform bg-white rounded-full shadow pointer-events-none ring-0"
                                    :class="
                                        currentProvider.status === 'active'
                                            ? 'translate-x-5'
                                            : 'translate-x-0'
                                    "
                                ></span>
                            </button>
                            <span
                                class="ml-2 text-sm"
                                :class="
                                    currentProvider.status === 'active'
                                        ? 'text-green-600'
                                        : 'text-gray-500'
                                "
                            >
                                {{
                                    currentProvider.status === "active"
                                        ? "Active"
                                        : "Inactive"
                                }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 mt-6">
                    <button
                        type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-300 border rounded-md shadow-sm border-primary bg-dark-lighter hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                        @click="editModal = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                        @click="updateProvider"
                        :disabled="isLoading"
                    >
                        {{ isLoading ? "Saving..." : "Save Changes" }}
                    </button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

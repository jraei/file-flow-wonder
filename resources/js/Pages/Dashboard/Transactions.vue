<script setup>
import { ref } from "vue";
import { router, usePage, Link } from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import DashboardSidebar from "@/Components/Dashboard/Sidebar.vue";
import TransactionTableFilters from "@/Components/Dashboard/TransactionTableFilters.vue";
import DataTable from "@/Components/DataTable.vue";

const page = usePage();
const props = defineProps({
    transactions: { type: Object, required: true },
    filters: { type: Object, required: true },
});

// Local table filter state (to sync with backend queries)
const filters = ref({
    status: props.filters.status || "",
    date_start: props.filters.date_start || "",
    date_end: props.filters.date_end || "",
    search: props.filters.search || "",
    sort_by: props.filters.sort_by || "created_at",
    sort_order: props.filters.sort_order || "desc",
    page: 1,
    per_page: props.transactions.per_page || 10,
});

function handleFiltersUpdate(newFilters) {
    filters.value = { ...filters.value, ...newFilters, page: 1 };
    reloadTable();
}

function handleTableSort({ column, direction }) {
    filters.value.sort_by = column;
    filters.value.sort_order = direction;
    reloadTable();
}

function handleSearch(newQuery) {
    filters.value.search = newQuery;
    filters.value.page = 1;
    reloadTable();
}

function reloadTable() {
    router.get(route("dashboard.transactions"), filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["transactions"],
    });
}

// TABLE DEFINITION
const columns = [
    { key: "order_id", label: "Invoice" },
    {
        key: "layanan",
        label: "Item",
        format: (val) => {
            if (!val) return "Unknown";
            return val.length > 50
                ? val.nama_layanan.substring(0, 47) + "..."
                : val.nama_layanan;
        },
    },
    {
        key: "input_details",
        label: "User Input",
        format: (_, item) => {
            if (item.input_zone) {
                return `${item.input_id} (${item.input_zone})`;
            }
            return item.input_id || "N/A";
        },
    },
    {
        key: "price",
        label: "Price",
        format: (val) => formatCurrency(val),
    },
    {
        key: "created_at",
        label: "Date",
        format: (val) => formatDate(val),
    },
    {
        key: "status",
        label: "Status",
        format: statusBadge,
    },
];

function statusBadge(status) {
    const colors = {
        completed: "bg-green-500/60 text-green-100 shadow-glow-green",
        pending: "bg-yellow-500/60 text-yellow-900 shadow-glow-yellow",
        processing: "bg-blue-500/60 text-blue-100 shadow-glow-blue",
        failed: "bg-red-500/60 text-red-100 shadow-glow-red",
        cancelled: "bg-gray-400/80 text-gray-900",
    };
    const label = {
        completed: "Berhasil",
        pending: "Menunggu",
        processing: "Memproses",
        failed: "Gagal",
        cancelled: "Dibatalkan",
    };
    return `<span class="px-2 py-1 rounded text-xs font-semibold ${
        colors[status] || "bg-gray-500 text-white"
    }">${label[status] || status}</span>`;
}

function formatCurrency(amount) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(amount);
}

function formatDate(dateString) {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date
        .toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        })
        .replace(/\./g, ":");
}
</script>

<template>
    <GuestLayout>
        <div class="flex min-h-screen mx-auto bg-transparent max-w-7xl">
            <DashboardSidebar />
            <div class="flex-1 p-2 sm:p-6">
                <h1 class="mb-6 text-2xl font-bold text-white">
                    Riwayat Transaksi
                </h1>

                <!-- Transaction Filters -->
                <TransactionTableFilters
                    :filters="filters"
                    @update:filters="handleFiltersUpdate"
                    class="animate-fade-in"
                />

                <!-- Transaction History DataTable -->
                <div
                    class="relative overflow-hidden shadow-lg rounded-2xl animate-fade-in cosmic-table-gradient"
                >
                    <DataTable
                        :data="transactions.data"
                        :columns="columns"
                        class="text-primary-text"
                        :pagination="{
                            current_page: transactions.current_page,
                            per_page: transactions.per_page,
                            total: transactions.total,
                        }"
                        :filters="{
                            search: filters.search,
                            sort: filters.sort_by,
                            direction: filters.sort_order,
                        }"
                        :route="'dashboard.transactions'"
                        :searchable="true"
                        :createable="false"
                        :editable="false"
                        @sort="handleTableSort"
                        @search="handleSearch"
                        @page-change="
                            (p) => {
                                filters.page = p;
                                reloadTable();
                            }
                        "
                    >
                        <template #title>Riwayat Transaksi</template>
                        <!-- rows starfield effect -->
                        <template #cell(created_at)="{ item }">
                            <span class="relative z-10">
                                {{ formatDate(item.created_at) }}
                            </span>
                            <div
                                class="absolute top-0 left-0 w-full h-full pointer-events-none -z-1 opacity-10"
                            >
                                <svg viewBox="0 0 100 1" class="w-full h-4">
                                    <circle
                                        cx="10"
                                        cy="0.5"
                                        r="0.5"
                                        fill="#9b87f5"
                                    />
                                    <circle
                                        cx="33"
                                        cy="0.7"
                                        r="0.3"
                                        fill="#33C3F0"
                                    />
                                    <circle
                                        cx="82"
                                        cy="0.3"
                                        r="0.7"
                                        fill="#9b87f5"
                                    />
                                </svg>
                            </div>
                        </template>
                        <template #cell(status)="{ item }">
                            <div
                                class="cosmic-badge"
                                :class="`status-${item.status}`"
                                v-html="statusBadge(item.status)"
                            ></div>
                        </template>
                        <template #cell(order_id)="{ item }">
                            <Link
                                :href="route('order.invoice', item.order_id)"
                                class="text-primary hover:underline"
                            >
                                {{ item.order_id }}
                            </Link>
                        </template>
                        <!-- Responsive stacking for mobile -->
                        <template #responsive-row="{ item }">
                            <div class="px-4 py-2 space-y-2">
                                <div class="flex justify-between">
                                    <span class="font-semibold text-gray-400"
                                        >Invoice:</span
                                    >
                                    <Link
                                        :href="route('invoice', item.order_id)"
                                        class="text-primary hover:underline"
                                    >
                                        {{ item.order_id }}
                                    </Link>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold text-gray-400"
                                        >Item:</span
                                    >
                                    <span>{{
                                        item.layanan?.nama_layanan || "Unknown"
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold text-gray-400"
                                        >User Input:</span
                                    >
                                    <span>
                                        {{
                                            item.input_zone
                                                ? `${item.input_id} (${item.input_zone})`
                                                : item.input_id
                                        }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold text-gray-400"
                                        >Price:</span
                                    >
                                    <span>{{
                                        formatCurrency(item.price)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold text-gray-400"
                                        >Date:</span
                                    >
                                    <span>{{
                                        formatDate(item.created_at)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold text-gray-400"
                                        >Status:</span
                                    >
                                    <div
                                        v-html="statusBadge(item.status)"
                                    ></div>
                                </div>
                            </div>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
.cosmic-table-gradient {
    background: linear-gradient(
        109.6deg,
        rgba(155, 135, 245, 0.06) 11.2%,
        rgba(51, 195, 240, 0.1) 91.1%
    );
}

.cosmic-badge {
    display: inline-block;
}

.status-completed {
    filter: drop-shadow(0 0 5px rgba(72, 187, 120, 0.5));
}

.status-pending {
    filter: drop-shadow(0 0 5px rgba(237, 137, 54, 0.5));
}

.status-processing {
    filter: drop-shadow(0 0 5px rgba(66, 153, 225, 0.5));
}

.status-failed {
    filter: drop-shadow(0 0 5px rgba(229, 62, 62, 0.5));
}

.shadow-glow-green {
    box-shadow: 0 0 8px rgba(72, 187, 120, 0.6);
}

.shadow-glow-yellow {
    box-shadow: 0 0 8px rgba(237, 137, 54, 0.6);
}

.shadow-glow-blue {
    box-shadow: 0 0 8px rgba(66, 153, 225, 0.6);
}

.shadow-glow-red {
    box-shadow: 0 0 8px rgba(229, 62, 62, 0.6);
}

/* Responsive styles */
@media (max-width: 640px) {
    .cosmic-badge {
        display: inline-flex;
        justify-content: center;
    }
}
</style>

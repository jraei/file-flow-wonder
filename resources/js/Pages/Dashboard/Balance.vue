<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import DashboardSidebar from "@/Components/Dashboard/Sidebar.vue";
import CosmicBalanceCard from "@/Components/Dashboard/CosmicBalanceCard.vue";
import DepositTableFilters from "@/Components/Dashboard/DepositTableFilters.vue";
import DataTable from "@/Components/DataTable.vue";
import { router, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const page = usePage();
const props = defineProps({
    balance: { type: Number, required: true },
    deposits: { type: Object, required: true },
});

// Local table filter state (to sync with backend queries)
const filters = ref({
    status: "",
    date_start: "",
    date_end: "",
    search: "",
    sort_by: "created_at",
    sort_order: "desc",
    page: 1,
    per_page: props.deposits.per_page || 10,
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
    router.get(route("dashboard.balance"), filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["balance", "deposits"],
    });
}

// TABLE DEFINITION
const columns = [
    { key: "deposit_id", label: "ID Invoice" },
    { key: "created_at", label: "Tanggal" },
    { key: "amount", label: "Nominal", format: (val) => formatCurrency(val) },
    { key: "status", label: "Status", format: statusBadge },
    { key: "provider_reference", label: "Referensi" },
];

function statusBadge(status) {
    const colors = {
        paid: "bg-green-500/60 text-green-100",
        pending: "bg-yellow-500/60 text-yellow-900",
        failed: "bg-red-500/60 text-red-100",
        cancelled: "bg-gray-400/80 text-gray-900",
    };
    const label = {
        paid: "Berhasil",
        pending: "Menunggu",
        failed: "Gagal",
        cancelled: "Dibatalkan",
    };
    return `<span class="px-2 rounded text-xs font-semibold ${
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

function toTopup() {
    router.get(route("dashboard.topup"));
}

// Modal stub for voucher redeem
const showVoucherModal = ref(false);
</script>

<template>
    <GuestLayout>
        <div class="flex min-h-screen mx-auto bg-transparent max-w-7xl">
            <DashboardSidebar />
            <div class="flex-1 p-2 sm:p-6">
                <!-- Balance Cosmic Card -->
                <CosmicBalanceCard
                    :balance="balance"
                    @topup="toTopup"
                    @redeem="showVoucherModal = true"
                    class="animate-fade-in"
                />
                <!-- Voucher Modal Placeholder -->
                <Teleport to="body">
                    <div
                        v-if="showVoucherModal"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
                        @click.self="showVoucherModal = false"
                    >
                        <div
                            class="bg-content-background border border-primary/20 rounded-xl shadow-xl p-8 w-[99vw] max-w-md animate-scale-in"
                        >
                            <h3 class="mb-2 text-xl font-bold text-white">
                                Redeem Voucher
                            </h3>
                            <p class="mb-4 text-xs text-secondary">
                                Masukkan kode voucher Anda.
                            </p>
                            <form @submit.prevent="showVoucherModal = false">
                                <input
                                    type="text"
                                    class="w-full px-3 py-2 mb-3 text-white border rounded border-primary/30 bg-dark-sidebar placeholder-secondary focus:outline-none focus:ring-2 focus:ring-primary"
                                    placeholder="Code..."
                                    maxlength="32"
                                />
                                <div class="flex justify-end gap-2 mt-1">
                                    <button
                                        type="button"
                                        @click="showVoucherModal = false"
                                        class="px-3 py-1 font-semibold text-white rounded bg-secondary/30 hover:bg-secondary/60"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        type="submit"
                                        class="px-4 py-1 font-bold text-white transition rounded bg-primary/90 hover:bg-primary"
                                    >
                                        Redeem
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </Teleport>
                <!-- Table Filters -->
                <DepositTableFilters
                    :filters="filters"
                    @update:filters="handleFiltersUpdate"
                />
                <!-- Deposit History DataTable -->
                <div
                    class="relative overflow-hidden shadow-lg rounded-2xl animate-fade-in cosmic-table-gradient"
                >
                    <DataTable
                        :data="deposits.data"
                        :columns="columns"
                        :pagination="{
                            current_page: deposits.current_page,
                            per_page: deposits.per_page,
                            total: deposits.total,
                        }"
                        :filters="{
                            search: filters.search,
                            sort: filters.sort_by,
                            direction: filters.sort_order,
                        }"
                        :route="'dashboard.balance'"
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
                        <template #title> Riwayat Deposit </template>
                        <!-- rows starfield effect -->
                        <template #cell(created_at)="{ item }">
                            <span class="relative z-10">
                                {{
                                    new Date(item.created_at).toLocaleString(
                                        "id-ID"
                                    )
                                }}
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
</style>

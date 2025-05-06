<template>
    <div
        class="relative mt-8 overflow-hidden shadow-lg rounded-xl backdrop-blur-sm"
    >
        <!-- Animated asteroid belt border -->
        <div
            class="absolute -inset-[1px] bg-gradient-to-r from-secondary/10 via-primary/30 to-bg-content_background/10 opacity-70 rounded-xl animate-border-flow"
        ></div>

        <div class="relative transactions-table rounded-xl">
            <!-- Horizontal scroll container with scroll shadows -->
            <div
                class="relative overflow-x-auto lg:overflow-x-visible -webkit-overflow-scrolling-touch"
            >
                <table
                    class="w-full min-w-[800px] text-sm text-left text-gray-300"
                >
                    <thead
                        class="text-xs text-gray-200 uppercase bg-primary/50"
                    >
                        <tr>
                            <th
                                scope="col"
                                class="px-3 py-3 sm:px-4 whitespace-nowrap"
                            >
                                Date
                            </th>
                            <th
                                scope="col"
                                class="px-3 py-3 sm:px-4 whitespace-nowrap"
                            >
                                Invoice
                            </th>
                            <th
                                scope="col"
                                class="px-3 py-3 sm:px-4 whitespace-nowrap"
                            >
                                Phone
                            </th>
                            <th
                                scope="col"
                                class="px-3 py-3 sm:px-4 whitespace-nowrap"
                            >
                                Produk
                            </th>
                            <th
                                scope="col"
                                class="px-3 py-3 sm:px-4 whitespace-nowrap"
                            >
                                Price
                            </th>
                            <th
                                scope="col"
                                class="px-3 py-3 sm:px-4 whitespace-nowrap"
                            >
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template
                            v-if="
                                transactions.data &&
                                transactions.data.length > 0
                            "
                        >
                            <tr
                                v-for="(
                                    transaction, index
                                ) in transactions.data"
                                :key="transaction.id"
                                class="transition-colors border-b border-secondary/10 hover:bg-primary/20"
                                :class="getRowAnimationClass(index)"
                                :data-transaction-id="transaction.id"
                            >
                                <td
                                    class="px-3 py-2 text-sm sm:px-4 sm:py-3 sm:text-base whitespace-nowrap"
                                >
                                    {{ formatDate(transaction.created_at) }}
                                </td>
                                <td
                                    class="px-3 py-2 text-sm sm:px-4 sm:py-3 sm:text-base whitespace-nowrap"
                                >
                                    {{ transaction.masked_order_id }}
                                </td>
                                <td
                                    class="px-3 py-2 text-sm sm:px-4 sm:py-3 sm:text-base whitespace-nowrap"
                                >
                                    {{ transaction.masked_phone }}
                                </td>
                                <td
                                    class="px-3 py-2 text-sm sm:px-4 sm:py-3 sm:text-base whitespace-nowrap"
                                >
                                    <div class="flex items-center gap-2">
                                        <div
                                            v-if="
                                                transaction.layanan?.produk
                                                    ?.thumbnail
                                            "
                                            class="w-6 h-6 overflow-hidden rounded-full bg-primary"
                                        >
                                            <img
                                                :src="
                                                    '/storage/' +
                                                    transaction.layanan.produk
                                                        .thumbnail
                                                "
                                                class="object-cover w-full h-full"
                                                :alt="
                                                    transaction.layanan.produk
                                                        .nama
                                                "
                                            />
                                        </div>
                                        <span
                                            v-if="
                                                transaction.layanan?.produk
                                                    ?.nama
                                            "
                                            class="truncate max-w-[150px]"
                                        >
                                            {{
                                                transaction.layanan.produk.nama
                                            }}
                                        </span>
                                        <span v-else>-</span>
                                    </div>
                                </td>
                                <td
                                    class="px-3 py-2 text-sm sm:px-4 sm:py-3 sm:text-base whitespace-nowrap"
                                >
                                    {{ transaction.masked_price }}
                                </td>
                                <td
                                    class="px-3 py-2 text-sm sm:px-4 sm:py-3 sm:text-base"
                                >
                                    <span
                                        class="px-2.5 py-1 text-xs rounded-full border font-medium inline-flex items-center justify-center whitespace-nowrap"
                                        :class="
                                            statusClasses[transaction.status] ||
                                            statusClasses.pending
                                        "
                                    >
                                        <!-- Status pulse indicator -->
                                        <span
                                            v-if="
                                                transaction.status ===
                                                    'pending' ||
                                                transaction.status ===
                                                    'processing'
                                            "
                                            class="h-1.5 w-1.5 rounded-full mr-1.5"
                                            :class="
                                                transaction.status === 'pending'
                                                    ? 'bg-secondary animate-pulse'
                                                    : 'bg-primary animate-pulse'
                                            "
                                        ></span>
                                        {{
                                            transaction.status
                                                .charAt(0)
                                                .toUpperCase() +
                                            transaction.status.slice(1)
                                        }}
                                    </span>
                                </td>
                            </tr>
                        </template>
                        <tr v-else class="border-b border-primary/20">
                            <td
                                colspan="6"
                                class="px-4 py-8 text-center text-gray-400"
                            >
                                <div class="flex flex-col items-center">
                                    <!-- Empty state with constellation pattern -->
                                    <div class="relative w-32 h-32 mb-4">
                                        <div
                                            class="absolute inset-0 flex items-center justify-center"
                                        >
                                            <div
                                                class="flex items-center justify-center w-20 h-20 rounded-full bg-primary"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="w-10 h-10 text-gray-500"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                    />
                                                </svg>
                                            </div>
                                        </div>
                                        <!-- Constellation dots -->
                                        <div
                                            class="absolute w-1 h-1 rounded-full top-1/4 left-1/4 bg-primary"
                                        ></div>
                                        <div
                                            class="absolute w-1 h-1 rounded-full top-3/4 left-1/2 bg-secondary"
                                        ></div>
                                        <div
                                            class="absolute w-1 h-1 rounded-full top-1/2 left-3/4 bg-primary"
                                        ></div>
                                        <div
                                            class="absolute top-1/3 left-2/3 h-0.5 w-0.5 bg-white rounded-full animate-pulse"
                                        ></div>
                                        <div
                                            class="absolute top-2/3 left-1/3 h-0.5 w-0.5 bg-white rounded-full animate-pulse"
                                        ></div>
                                        <!-- Constellation lines -->
                                        <svg
                                            class="absolute inset-0 w-full h-full"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <line
                                                x1="25%"
                                                y1="25%"
                                                x2="50%"
                                                y2="75%"
                                                class="stroke-primary/30 stroke-[0.5]"
                                            />
                                            <line
                                                x1="50%"
                                                y1="75%"
                                                x2="75%"
                                                y2="50%"
                                                class="stroke-primary/30 stroke-[0.5]"
                                            />
                                            <line
                                                x1="75%"
                                                y1="50%"
                                                x2="25%"
                                                y2="25%"
                                                class="stroke-secondary/30 stroke-[0.5]"
                                            />
                                        </svg>
                                    </div>
                                    <p class="mb-1 text-gray-400">
                                        No transactions found
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        The cosmic void is peaceful today
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="transactions.meta && transactions.meta.last_page > 1"
                class="flex justify-center py-4 bg-secondary/20"
            >
                <nav class="flex items-center space-x-1">
                    <button
                        @click="changePage(transactions.meta.current_page - 1)"
                        :disabled="transactions.meta.current_page === 1"
                        class="px-3 py-1 text-gray-400 rounded bg-dark-lighter/50 hover:bg-dark-lighter disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Previous
                    </button>

                    <div class="flex items-center space-x-1">
                        <template
                            v-for="page in transactions.meta.last_page"
                            :key="page"
                        >
                            <button
                                v-if="
                                    page === 1 ||
                                    page === transactions.meta.last_page ||
                                    (page >=
                                        transactions.meta.current_page - 1 &&
                                        page <=
                                            transactions.meta.current_page + 1)
                                "
                                @click="changePage(page)"
                                :class="[
                                    'px-3 py-1 rounded',
                                    page === transactions.meta.current_page
                                        ? 'bg-primary text-white'
                                        : 'bg-dark-lighter/50 text-gray-400 hover:bg-dark-lighter/80',
                                ]"
                            >
                                {{ page }}
                            </button>
                            <span
                                v-else-if="
                                    page === 2 ||
                                    page === transactions.meta.last_page - 1
                                "
                                class="px-2 text-gray-500"
                                >...</span
                            >
                        </template>
                    </div>

                    <button
                        @click="changePage(transactions.meta.current_page + 1)"
                        :disabled="
                            transactions.meta.current_page ===
                            transactions.meta.last_page
                        "
                        class="px-3 py-1 text-gray-400 rounded bg-dark-lighter/50 hover:bg-dark-lighter disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Next
                    </button>
                </nav>
            </div>

            <!-- Realtime indicator -->
            <div class="absolute top-3 right-3">
                <div class="flex items-center text-xs text-gray-400">
                    <span
                        class="h-1.5 w-1.5 rounded-full bg-secondary animate-pulse mr-1"
                    ></span>
                    <span>Live updates</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, onBeforeUnmount } from "vue";
import { router } from "@inertiajs/vue3";
import { useToast } from "@/Composables/useToast";

const props = defineProps({
    transactions: {
        type: Object,
        required: true,
    },
    serverTime: {
        type: String,
        required: true,
    },
});

const { toast } = useToast();
const isPolling = ref(false);
const pollingInterval = ref(null);
const timeDiff = ref(0);
const entanglementPairs = reactive({});

// Status badges with color mapping
const statusClasses = {
    pending: "bg-secondary/20 text-secondary border-secondary",
    processing: "bg-primary/20 text-primary border-primary",
    completed: "bg-green-500/20 text-green-500 border-green-500",
    failed: "bg-red-500/20 text-red-500 border-red-500",
    cancelled: "bg-gray-500/20 text-gray-400 border-gray-400",
};

// Format date based on server time
const formatDate = (dateString) => {
    const date = new Date(dateString);
    date.setTime(date.getTime() + timeDiff.value);

    return new Intl.DateTimeFormat("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
        day: "2-digit",
        month: "short",
    }).format(date);
};

// Start polling for live updates
const startPolling = () => {
    if (pollingInterval.value) return;

    pollingInterval.value = setInterval(() => {
        if (isPolling.value) return;

        isPolling.value = true;
        router.get(
            "/cek-transaksi",
            {},
            {
                preserveState: true,
                preserveScroll: true,
                only: ["transactions", "serverTime"],
                onSuccess: () => {
                    isPolling.value = false;
                    addWarpTunnelEffect();
                    findEntangledTransactions();
                },
                onError: () => {
                    isPolling.value = false;
                },
            }
        );
    }, 15000); // Poll every 15 seconds
};

// Handle pagination
const changePage = (page) => {
    router.get(
        "/cek-transaksi",
        {
            page: page,
        },
        {
            preserveScroll: true,
            only: ["transactions"],
        }
    );
};

// Animation classes for rows based on their index
const getRowAnimationClass = (index) => {
    const delay = index * 50;
    return `animate-fade-in opacity-0 [animation-delay:${delay}ms] [animation-fill-mode:forwards]`;
};

// Add warp tunnel effect during refresh
const addWarpTunnelEffect = () => {
    const table = document.querySelector(".transactions-table");
    if (!table) return;

    table.classList.add("warping");
    setTimeout(() => {
        table.classList.remove("warping");
    }, 500);
};

// Find related transactions (quantum entanglement)
// const findEntangledTransactions = () => {
//     if (!props.transactions.data || props.transactions.data.length < 2) return;

//     // Clear previous entanglements
//     Object.keys(entanglementPairs).forEach((key) => {
//         const elements = document.querySelectorAll(`.entanglement-${key}`);
//         elements.forEach((el) => el.remove());
//     });

//     // Reset entanglements
//     Object.keys(entanglementPairs).forEach((key) => {
//         delete entanglementPairs[key];
//     });

//     // Find transactions with the same input_id (same player)
//     const inputIdGroups = {};
//     props.transactions.data.forEach((transaction) => {
//         if (!transaction.input_id) return;

//         if (!inputIdGroups[transaction.input_id]) {
//             inputIdGroups[transaction.input_id] = [];
//         }
//         inputIdGroups[transaction.input_id].push(transaction.id);
//     });

//     // Create entanglement lines for transactions with the same input_id
//     Object.keys(inputIdGroups).forEach((inputId) => {
//         const transactions = inputIdGroups[inputId];
//         if (transactions.length < 2) return;

//         // Store entanglement pairs
//         entanglementPairs[inputId] = transactions;

//         // Draw entanglement lines after DOM update
//         setTimeout(() => {
//             drawEntanglementLines(inputId, transactions);
//         }, 500);
//     });
// };

// Draw quantum entanglement lines between related transactions
const drawEntanglementLines = (inputId, transactionIds) => {
    const rows = transactionIds
        .map((id) => document.querySelector(`tr[data-transaction-id="${id}"]`))
        .filter(Boolean);

    if (rows.length < 2) return;

    // Create SVG overlay
    const svgContainer = document.createElement("div");
    svgContainer.classList.add(`entanglement-${inputId}`);
    svgContainer.style.position = "absolute";
    svgContainer.style.inset = "0";
    svgContainer.style.pointerEvents = "none";
    svgContainer.style.zIndex = "1";

    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("width", "100%");
    svg.setAttribute("height", "100%");
    svg.setAttribute("class", "quantum-entanglement");
    svgContainer.appendChild(svg);

    // Connect rows with entanglement lines
    for (let i = 0; i < rows.length - 1; i++) {
        for (let j = i + 1; j < rows.length; j++) {
            const row1 = rows[i];
            const row2 = rows[j];

            if (!row1 || !row2) continue;

            const rect1 = row1.getBoundingClientRect();
            const rect2 = row2.getBoundingClientRect();
            const tableRect = document
                .querySelector("table")
                .getBoundingClientRect();

            // Calculate relative positions
            const x1 = rect1.left - tableRect.left + rect1.width / 8;
            const y1 = rect1.top - tableRect.top + rect1.height / 2;
            const x2 = rect2.left - tableRect.left + rect2.width / 8;
            const y2 = rect2.top - tableRect.top + rect2.height / 2;

            // Create entanglement line
            const line = document.createElementNS(
                "http://www.w3.org/2000/svg",
                "line"
            );
            line.setAttribute("x1", x1);
            line.setAttribute("y1", y1);
            line.setAttribute("x2", x2);
            line.setAttribute("y2", y2);
            line.setAttribute("stroke", "rgba(155, 135, 245, 0.3)");
            line.setAttribute("stroke-width", "1");
            line.setAttribute("stroke-dasharray", "3,3");
            svg.appendChild(line);

            // Add particle animations
            for (let p = 0; p < 2; p++) {
                const particle = document.createElementNS(
                    "http://www.w3.org/2000/svg",
                    "circle"
                );
                particle.setAttribute("r", "2");
                particle.setAttribute(
                    "fill",
                    p === 0
                        ? "rgba(155, 135, 245, 0.7)"
                        : "rgba(51, 195, 240, 0.7)"
                );

                // Add animation
                const animateMotion = document.createElementNS(
                    "http://www.w3.org/2000/svg",
                    "animateMotion"
                );
                animateMotion.setAttribute("dur", p === 0 ? "3s" : "4s");
                animateMotion.setAttribute("repeatCount", "indefinite");
                animateMotion.setAttribute("path", `M${x1},${y1} L${x2},${y2}`);
                particle.appendChild(animateMotion);

                svg.appendChild(particle);
            }
        }
    }

    document.querySelector(".transactions-table").appendChild(svgContainer);
};

onMounted(() => {
    // Calculate time difference between server and client
    timeDiff.value =
        new Date().getTime() - new Date(props.serverTime).getTime();

    // Add transaction ID attributes to table rows after initial render
    setTimeout(() => {
        if (props.transactions.data) {
            props.transactions.data.forEach((transaction) => {
                const row = document.querySelector(
                    `tr[data-key="${transaction.id}"]`
                );
                if (row) {
                    row.setAttribute("data-transaction-id", transaction.id);
                }
            });

            // Find entangled transactions
            // findEntangledTransactions();
        }
    }, 500);

    startPolling();
});

onBeforeUnmount(() => {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
    }
});
</script>

<style scoped>
@keyframes border-flow {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

.animate-border-flow {
    background-size: 300% 300%;
    animation: border-flow 5s ease infinite;
}

.animate-fade-in {
    animation: fadeIn 0.5s ease forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.warping {
    animation: warpTunnel 0.5s forwards;
}

@keyframes warpTunnel {
    0% {
        filter: brightness(1);
    }
    50% {
        filter: brightness(1.3) blur(2px);
    }
    100% {
        filter: brightness(1) blur(0);
    }
}

.quantum-entanglement {
    opacity: 0;
    animation: fadeEntanglement 0.5s forwards 0.3s;
}

@keyframes fadeEntanglement {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.overflow-x-auto {
    -webkit-overflow-scrolling: touch;
}
</style>

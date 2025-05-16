<script setup>
defineProps({
    name: {
        type: String,
        required: true,
    },
    size: {
        type: String,
        default: "md",
    },
    className: {
        type: String,
        default: "",
    },
});

// Size mappings
const sizeClasses = {
    sm: "w-4 h-4",
    md: "w-5 h-5",
    lg: "w-6 h-6",
    xl: "w-8 h-8",
};
</script>

<template>
    <span
        :class="[
            sizeClasses[size],
            className,
            'inline-flex items-center justify-center',
        ]"
    >
        <!-- Topup Icon -->
        <svg
            v-if="name === 'topup'"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="w-full h-full"
        >
            <path
                d="M12 2L12 10M12 10L7 6M12 10L17 6M4.5 10H19.5C20.9 10 22 11.1 22 12.5V20C22 21.1 21.1 22 20 22H4C2.9 22 2 21.1 2 20V12.5C2 11.1 3.1 10 4.5 10Z"
            />
            <path d="M6 16H7.5M11 16H12.5M16 16H17.5" />
        </svg>

        <!-- Transaction Check Icon -->
        <svg
            v-else-if="name === 'transaction'"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="w-full h-full"
        >
            <path d="M3 3v18h18" />
            <path d="M18 17V9" />
            <path d="M13 17V5" />
            <path d="M8 17v-3" />
            <path d="M21 21l-3-3 3-3" />
        </svg>

        <!-- Leaderboard Icon -->
        <svg
            v-else-if="name === 'leaderboard'"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="w-full h-full"
        >
            <path d="M8 21v-5" />
            <path d="M12 21v-9" />
            <path d="M16 21v-7" />
            <path d="M6 8l3-3 3 3 6-6" />
            <path d="M18 5v3h-3" />
        </svg>

        <!-- Calculator Icon -->
        <svg
            v-else-if="name === 'calculator'"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="w-full h-full"
        >
            <rect x="4" y="2" width="16" height="20" rx="2" />
            <line x1="8" x2="16" y1="6" y2="6" />
            <line x1="8" x2="8" y1="14" y2="18" />
            <line x1="12" x2="12" y1="14" y2="18" />
            <line x1="16" x2="16" y1="14" y2="18" />
            <line x1="8" x2="16" y1="10" y2="10" />
            <line x1="8" x2="16" y1="14" y2="14" />
            <line x1="8" x2="16" y1="18" y2="18" />
        </svg>

        <!-- Login Icon -->
        <svg
            v-else-if="name === 'login'"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="w-full h-full"
        >
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
            <polyline points="10 17 15 12 10 7" />
            <line x1="15" y1="12" x2="3" y2="12" />
        </svg>

        <!-- Register Icon -->
        <svg
            v-else-if="name === 'register'"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="w-full h-full"
        >
            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
            <circle cx="8.5" cy="7" r="4" />
            <line x1="20" y1="8" x2="20" y2="14" />
            <line x1="23" y1="11" x2="17" y2="11" />
        </svg>

        <!-- Winrate Icon -->
        <svg
            v-else-if="name === 'winrate'"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="w-full h-full"
        >
            <path d="M12 2v8" />
            <path d="m4.93 10.93 1.41 1.41" />
            <path d="M2 18h2" />
            <path d="M20 18h2" />
            <path d="m19.07 10.93-1.41 1.41" />
            <path d="M22 22H2" />
            <path d="m8 22 4-10 4 10" />
        </svg>

        <!-- Magic Wheel Icon -->
        <svg
            v-else-if="name === 'magicwheel'"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="w-full h-full"
        >
            <circle cx="12" cy="12" r="10" />
            <circle cx="12" cy="12" r="4" />
            <line x1="12" y1="2" x2="12" y2="4" />
            <line x1="12" y1="20" x2="12" y2="22" />
            <line x1="2" y1="12" x2="4" y2="12" />
            <line x1="20" y1="12" x2="22" y2="12" />
            <line x1="4.93" y1="4.93" x2="6.34" y2="6.34" />
            <line x1="17.66" y1="17.66" x2="19.07" y2="19.07" />
            <line x1="4.93" y1="19.07" x2="6.34" y2="17.66" />
            <line x1="17.66" y1="6.34" x2="19.07" y2="4.93" />
        </svg>

        <!-- Zodiac Icon -->
        <svg
            v-else-if="name === 'zodiac'"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="w-full h-full"
        >
            <circle cx="12" cy="12" r="10" />
            <path d="M8 12h8" />
            <path d="m8.5 9 7 6" />
            <path d="m8.5 15 7-6" />
        </svg>

        <!-- Search Icon -->
        <svg
            v-else-if="name === 'search'"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="w-full h-full"
        >
            <circle cx="11" cy="11" r="8" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>

        <!-- Default Icon (Fallback) -->
        <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="w-full h-full"
        >
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
    </span>
</template>

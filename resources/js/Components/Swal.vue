<template></template>
<script setup>
import { onMounted, onUpdated } from "vue";
const props = defineProps({
    status: {
        type: Object,
        default: {},
    },
});

function showSwal() {
    Swal.fire({
        title: props.status.action,
        text: props.status.text,
        icon: props.status.type,
        theme: "dark",
        customClass: {
            popup: "bg-dark-card rounded-xl",
            confirmButton:
                "bg-primary text-white shadow-glow-primary px-12 rounded-lg",
        },
    });
}

onMounted(() => {
    if (props.status) {
        showSwal();
    }
});

onUpdated(() => {
    if (props.status) {
        showSwal();
    }
});
</script>

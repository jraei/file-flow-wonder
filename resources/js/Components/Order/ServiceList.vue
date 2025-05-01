<script setup>
import { ref, computed } from "vue";
import CosmicCard from "./CosmicCard.vue";
import ServiceCard from "./ServiceCard.vue";
import { ShoppingBag, BadgePercent } from "lucide-vue-next";

const props = defineProps({
    services: {
        type: Array,
        required: true,
    },
    flashsaleItems: {
        type: Array,
        default: () => [],
    },
    flashsaleEvents: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["selectService"]);

const selectedService = ref(null);

const selectService = (service) => {
    selectedService.value = service;
    emit("selectService", service);
};

// Group services by flashsale events or regular
const regularServices = computed(() => {
    return props.services;
});

const flashsaleServiceGroups = computed(() => {
    const groups = [];

    if (!props.flashsaleItems || props.flashsaleItems.length === 0) {
        return groups;
    }

    // Group flashsale items by event ID
    const groupedByEvent = {};

    props.flashsaleItems.forEach((service) => {
        const eventId = service.flashSaleItem.flashsale_event_id;

        if (!groupedByEvent[eventId]) {
            groupedByEvent[eventId] = {
                event:
                    props.flashsaleEvents.find((e) => e?.id === eventId) ?? {},
                services: [],
            };
        }

        groupedByEvent[eventId].services.push(service);
    });

    // Convert the object to an array
    Object.values(groupedByEvent).forEach((group) => {
        if (group.event && group.services.length > 0) {
            groups.push(group);
        }
    });

    return groups;
});
</script>

<template>
    <CosmicCard title="Select Nominal" :stepNumber="2">
        <div class="space-y-6">
            <!-- Flashsale Services -->
            <div
                v-for="group in flashsaleServiceGroups"
                :key="group.event.id"
                class="space-y-3"
            >
                <div class="flex items-center space-x-2">
                    <BadgePercent
                        class="w-5 h-5 text-secondary animate-pulse"
                    />
                    <h4 class="text-white">{{ group.event.event_name }}</h4>
                </div>

                <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4">
                    <ServiceCard
                        v-for="service in group.services"
                        :key="service.id"
                        :service="service"
                        :isSelected="
                            selectedService && selectedService.id === service.id
                        "
                        :isFlashsale="true"
                        @select="selectService"
                    />
                </div>
            </div>

            <!-- Regular Services -->
            <div v-if="regularServices.length > 0" class="space-y-3">
                <div class="flex items-center space-x-2">
                    <ShoppingBag class="w-5 h-5 text-secondary" />
                    <h4 class="text-white">Best Seller</h4>
                </div>

                <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4">
                    <ServiceCard
                        v-for="service in regularServices"
                        :key="service.id"
                        :service="service"
                        :isSelected="
                            selectedService && selectedService.id === service.id
                        "
                        @select="selectService"
                    />
                </div>
            </div>

            <!-- No services message -->
            <div
                v-if="
                    regularServices.length === 0 &&
                    flashsaleServiceGroups.length === 0
                "
                class="p-4 text-center text-primary-text/80"
            >
                No services available at the moment
            </div>
        </div>
    </CosmicCard>
</template>


<script setup>
import { ref, computed, onMounted, watch } from "vue";
import Modal from "@/Components/Modal.vue";
import Checkbox from "@/Components/Checkbox.vue";
import CosmicCard from "@/Components/Order/CosmicCard.vue";
import CosmicParticles from "@/Components/User/Flashsale/CosmicParticles.vue";
import { useCookieStorage } from "@/Composables/useCookieStorage";
import { usePhoneFormatting } from "@/Composables/usePhoneFormatting";
import { useToast } from "@/Composables/useToast";

const props = defineProps({
    inputFields: Array,
    produk: Object,
});

const emit = defineEmits(['input-data-change']);

const { setCookie, getCookie } = useCookieStorage();
const { formatInternationalPhone } = usePhoneFormatting();
const { toast } = useToast();

const showModal = ref(false);
const saveIdForFuture = ref(false);
const inputValues = ref({});
const countryCode = ref('ID');

// Initialize form values
onMounted(() => {
    // Initialize with empty values for all fields
    props.inputFields.forEach(field => {
        inputValues.value[field.name] = '';
    });
    
    // Try to load saved values from cookie
    loadSavedGameAccount();
});

// Watch for changes in saved fields checkbox
watch(saveIdForFuture, (newVal) => {
    if (newVal) {
        // Show a toast message that ID will be saved for future use
        toast.info("Your game ID will be saved for future purchases");
    }
});

// Watch for any changes in input values
watch(inputValues, (newVal) => {
    emit('input-data-change', {
        inputValues: newVal,
        countryCode: countryCode.value,
        saveForFuture: saveIdForFuture.value
    });
}, { deep: true });

// Load saved account details if available
const loadSavedGameAccount = () => {
    const savedAccounts = getCookie('saved_game_accounts') || {};
    const gameKey = props.produk.nama;
    
    if (savedAccounts && savedAccounts[gameKey]) {
        const accountData = savedAccounts[gameKey];
        
        // Map saved values to form fields
        Object.keys(accountData).forEach(key => {
            if (inputValues.value.hasOwnProperty(key)) {
                inputValues.value[key] = accountData[key];
            }
        });
        
        // Set the save checkbox to true since we have saved data
        saveIdForFuture.value = true;
    }
};

// Save account details to cookie
const saveGameAccount = () => {
    if (!saveIdForFuture.value) return;
    
    const gameKey = props.produk.nama;
    const savedAccounts = getCookie('saved_game_accounts') || {};
    
    // Only save ID fields, not contact info
    const fieldsToSave = {};
    props.inputFields.forEach(field => {
        // Avoid saving contact fields
        if (!['email', 'phone', 'contact', 'whatsapp'].some(term => field.name.toLowerCase().includes(term))) {
            fieldsToSave[field.name] = inputValues.value[field.name];
        }
    });
    
    // Update the cookie with new data for this game
    savedAccounts[gameKey] = fieldsToSave;
    setCookie('saved_game_accounts', savedAccounts, 90); // Save for 90 days
};

// Handle input change
const handleInputChange = () => {
    if (saveIdForFuture.value) {
        saveGameAccount();
    }
};
</script>

<template>
    <div class="absolute inset-0 z-0">
        <CosmicParticles />
    </div>
    <CosmicCard :title="'Masukkan Data Akun'" :step-number="1">
        <form class="relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <template v-for="field in inputFields" :key="field.id">
                    <div class="space-y-2">
                        <label
                            :for="`field_${field.id}`"
                            class="block text-sm font-semibold text-primary-text"
                        >
                            {{ field.label }}
                            <span
                                v-if="field.required"
                                class="ml-1 text-secondary"
                                >*</span
                            >
                        </label>

                        <!-- Text Input -->
                        <input
                            v-if="field.type === 'text'"
                            type="text"
                            :id="`field_${field.id}`"
                            :name="field.name"
                            :required="field.required"
                            v-model="inputValues[field.name]"
                            class="w-full rounded-lg outline-none bg-secondary/20 border-secondary/30 placeholder-secondary/50 focus:ring-secondary focus:border-primary/70 focus:bg-secondary/20/90 text-primary-text cosmic-input-effect"
                            @change="handleInputChange"
                        />

                        <!-- Number Input -->
                        <input
                            v-else-if="field.type === 'number'"
                            type="number"
                            :id="`field_${field.id}`"
                            :name="field.name"
                            :required="field.required"
                            v-model="inputValues[field.name]"
                            class="w-full rounded-lg outline-none bg-secondary/20 border-secondary/30 placeholder-secondary/50 focus:ring-secondary focus:border-primary/70 focus:bg-secondary/20/90 text-primary-text cosmic-input-effect"
                            @change="handleInputChange"
                        />

                        <!-- Select Input -->
                        <select
                            v-else-if="field.type === 'select'"
                            :id="`field_${field.id}`"
                            :name="field.name"
                            :required="field.required"
                            v-model="inputValues[field.name]"
                            class="w-full rounded-lg bg-secondary/20 border-secondary/30 focus:border-secondary focus:ring focus:ring-secondary/50 text-primary-text cosmic-input-effect"
                            @change="handleInputChange"
                        >
                            <option value="" disabled selected>
                                Select an option
                            </option>
                            <option
                                v-for="option in field.options"
                                :key="option.id"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                </template>

                <!-- WhatsApp Input with Country Code (Shown after dynamic fields) -->
                <div class="space-y-2 col-span-full md:col-span-1">
                    <label
                        for="phone"
                        class="block text-sm font-semibold text-primary-text"
                    >
                        WhatsApp Number
                        <span class="ml-1 text-secondary">*</span>
                    </label>
                    <div class="flex space-x-2">
                        <select
                            v-model="countryCode"
                            class="w-24 rounded-lg bg-secondary/20 border-secondary/30 focus:border-secondary focus:ring focus:ring-secondary/50 text-primary-text cosmic-input-effect"
                        >
                            <option value="ID">ID (+62)</option>
                            <option value="MY">MY (+60)</option>
                            <option value="SG">SG (+65)</option>
                            <option value="US">US (+1)</option>
                        </select>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            placeholder="812xxxxxxx"
                            v-model="inputValues.phone"
                            required
                            class="flex-1 rounded-lg outline-none bg-secondary/20 border-secondary/30 placeholder-secondary/50 focus:ring-secondary focus:border-primary/70 focus:bg-secondary/20/90 text-primary-text cosmic-input-effect"
                        />
                    </div>
                </div>

                <!-- Optional Email Field -->
                <div class="space-y-2 col-span-full md:col-span-1">
                    <label
                        for="email"
                        class="block text-sm font-semibold text-primary-text"
                    >
                        Email (Optional)
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        v-model="inputValues.email"
                        placeholder="email@example.com"
                        class="w-full rounded-lg outline-none bg-secondary/20 border-secondary/30 placeholder-secondary/50 focus:ring-secondary focus:border-primary/70 focus:bg-secondary/20/90 text-primary-text cosmic-input-effect"
                    />
                </div>
            </div>

            <!-- Footer Elements -->
            <div
                class="flex flex-col gap-4 mt-8 md:flex-row md:items-center md:justify-between"
            >
                <!-- Save ID Checkbox -->
                <div class="flex items-center">
                    <Checkbox v-model:checked="saveIdForFuture" class="" />
                    <label for="saveId" class="ml-2 text-sm text-primary-text">
                        Simpan ID untuk pembelian berikutnya
                    </label>
                </div>

                <!-- Purchase Guide Button -->
                <button
                    type="button"
                    @click="showModal = true"
                    class="flex items-center justify-center gap-2 px-4 py-2 text-white transition-colors rounded-md bg-primary/80 hover:bg-primary"
                >
                    <span>Panduan Pembelian</span>
                </button>
            </div>
        </form>
    </CosmicCard>

    <!-- Cosmic Modal -->
    <Modal :show="showModal" @close="showModal = false" max-width="xl">
        <div
            class="p-4 border rounded-lg md:p-6 bg-gradient-to-br from-primary/90 to-secondary/50 border-secondary/50 text-primary-text"
        >
            <h3 class="mb-4 text-xl font-bold text-center">
                Panduan Pembelian
            </h3>

            <div class="space-y-6">
                <!-- Purchase Guide Image -->
                <div
                    v-if="produk.petunjuk_field"
                    class="max-w-full mx-auto overflow-hidden rounded-lg"
                >
                    <img
                        :src="`/storage/${produk.petunjuk_field}`"
                        alt="Purchase Guide"
                        class="w-full object-contain max-h-[60vh]"
                    />
                </div>

                <!-- Purchase Guide Text -->
                <div
                    v-if="produk.deskripsi_game"
                    class="prose-sm prose md:prose-base max-w-none text-primary-text"
                >
                    <p>{{ produk.deskripsi_game }}</p>
                </div>
            </div>

            <!-- Close Button -->
            <div class="mt-6 text-center">
                <button
                    type="button"
                    @click="showModal = false"
                    class="px-6 py-2 text-white transition-colors rounded-md bg-primary hover:bg-primary-hover"
                >
                    Close
                </button>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
.cosmic-input-effect {
    transition: all 0.3s ease;
    transform: translateZ(0);
    will-change: transform, box-shadow;
}

.cosmic-input-effect:focus {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(155, 135, 245, 0.15);
}

/* Add hover effect for select dropdowns */
select.cosmic-input-effect option {
    background-color: #1F2937;
    color: white;
}

select.cosmic-input-effect:hover {
    background-color: rgba(51, 195, 240, 0.25);
}
</style>


<template>
    <CosmicCard title="Game Account">
        <form @submit.prevent class="space-y-4">
            <div class="space-y-4">
                <DynamicInput 
                    v-for="field in inputFields" 
                    :key="field.id"
                    :field="field"
                    :initial-value="getAccountValue(field.name)"
                    :error="errors[field.name]"
                    @update:value="handleInputUpdate"
                />
            </div>
            
            <div class="flex items-center mt-2">
                <input 
                    type="checkbox" 
                    id="saveAccount" 
                    v-model="saveAccount"
                    class="w-4 h-4 text-primary bg-dark-lighter rounded border-gray-600 focus:ring-primary/50"
                >
                <label for="saveAccount" class="ml-2 text-sm text-gray-300">
                    Save this account for later
                </label>
            </div>
            
            <div v-if="hasLoadedData" class="p-2 text-xs text-secondary bg-secondary/10 rounded-md">
                <span class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Account data loaded from previous session
                </span>
            </div>

            <div v-if="validationError" class="p-3 text-sm text-red-300 bg-red-900/20 border border-red-800 rounded-md">
                <span class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ validationError }}
                </span>
            </div>
        </form>
    </CosmicCard>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import CosmicCard from '@/Components/Order/CosmicCard.vue';
import DynamicInput from '@/Components/Order/DynamicInput.vue';
import { useGameAccount } from '@/Composables/useGameAccount';
import { useAccountValidation } from '@/Composables/useAccountValidation';

const props = defineProps({
    inputFields: {
        type: Array,
        required: true
    },
    produk: {
        type: Object,
        required: true
    },
    validationError: {
        type: String,
        default: null
    }
});

const emit = defineEmits(['update:account-data', 'validation:error']);

const { accountData, saveAccount, updateAccountData, hasLoadedData } = useGameAccount(props.produk.slug);
const { validateInputFields } = useAccountValidation();
const errors = ref({});

const getAccountValue = (fieldName) => {
    return accountData.value[fieldName] || '';
};

const handleInputUpdate = ({ name, value }) => {
    updateAccountData(name, value);
    
    // Clear error for this field when user is typing
    if (errors.value[name]) {
        errors.value[name] = '';
    }
    
    emit('update:account-data', accountData.value);
};

// Validate all input fields
const validateFields = () => {
    const { isValid, errors: validationErrors } = validateInputFields(props.inputFields, accountData.value);
    errors.value = validationErrors;
    return isValid;
};

// Expose validateFields to parent
defineExpose({
    validateFields,
    accountData
});

watch(accountData, (newData) => {
    emit('update:account-data', newData);
}, { deep: true });

// Watch for external validation errors
watch(() => props.validationError, (newError) => {
    if (newError) {
        // Focus first input if there's a validation error
        const firstInput = document.querySelector('.dynamic-input');
        if (firstInput) {
            firstInput.focus();
        }
    }
});

onMounted(() => {
    // Initially emit the account data if we have saved data
    if (hasLoadedData.value) {
        emit('update:account-data', accountData.value);
    }
});
</script>

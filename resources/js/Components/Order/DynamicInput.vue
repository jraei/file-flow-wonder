<template>
    <div class="w-full space-y-2">
        <label :for="fieldId" class="block text-sm font-medium text-gray-300">
            {{ field.label }}
            <span v-if="field.required" class="text-primary">*</span>
        </label>

        <div v-if="field.type === 'text' || field.type === 'number'">
            <input
                :id="fieldId"
                :type="field.type"
                v-model="inputValue"
                :required="field.required"
                :name="field.name"
                class="w-full px-4 py-2 text-white transition-colors border rounded-lg dynamic-input bg-secondary/20 border-secondary focus:outline-none"
                :class="[
                    errorMessage
                        ? 'border-red-500 focus:border-red-400'
                        : 'border-secondary/30 focus:border-secondary',
                ]"
                :placeholder="`Enter ${field.label.toLowerCase()}`"
            />
        </div>

        <div v-else-if="field.type === 'select'">
            <select
                :id="fieldId"
                v-model="inputValue"
                :required="field.required"
                :name="field.name"
                class="w-full px-4 py-2 text-white transition-colors border rounded-lg dynamic-input bg-dark-lighter focus:outline-none"
                :class="[
                    errorMessage
                        ? 'border-red-500 focus:border-red-400'
                        : 'border-secondary/30 focus:border-secondary',
                ]"
            >
                <option value="" disabled>
                    Select {{ field.label.toLowerCase() }}
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

        <p v-if="errorMessage" class="mt-1 text-xs text-red-400">
            {{ errorMessage }}
        </p>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";

const props = defineProps({
    field: {
        type: Object,
        required: true,
    },
    initialValue: {
        type: [String, Number],
        default: "",
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:value"]);

const fieldId = computed(() => `field_${props.field.name}`);
const errorMessage = ref(props.error);
const inputValue = ref(props.initialValue || "");

watch(inputValue, (newValue) => {
    emit("update:value", {
        name: props.field.name,
        value: newValue,
    });
});

watch(
    () => props.error,
    (newError) => {
        errorMessage.value = newError;
    }
);

watch(
    () => props.initialValue,
    (newValue) => {
        if (newValue !== undefined && newValue !== inputValue.value) {
            inputValue.value = newValue;
        }
    }
);

onMounted(() => {
    if (props.initialValue) {
        inputValue.value = props.initialValue;
    }
});
</script>

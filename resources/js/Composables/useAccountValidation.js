import { ref, reactive } from "vue";
import { useToast } from "@/Composables/useToast";
import axios from "axios";

export function useAccountValidation() {
    const { toast } = useToast();
    const isValidating = ref(false);
    const validationError = ref(null);
    const cachedUsernames = reactive({});
    const validationTimeout = 5 * 60 * 1000; // 5 minutes

    // Basic validation function for account input fields
    const validateInputFields = (fields, inputData) => {
        const errors = {};
        let isValid = true;

        fields.forEach((field) => {
            const value = inputData[field.name];

            // Check required fields
            if (
                field.required &&
                (!value || (typeof value === "string" && value.trim() === ""))
            ) {
                errors[field.name] = `${field.label} is required`;
                isValid = false;
                return;
            }

            // Check numeric fields
            if (field.type === "number" && value && !/^[0-9]+$/.test(value)) {
                errors[field.name] = `${field.label} must contain only numbers`;
                isValid = false;
                return;
            }
        });

        return {
            isValid,
            errors,
        };
    };

    return {
        isValidating,
        validationError,
        validateInputFields,
        cachedUsernames,
    };
}

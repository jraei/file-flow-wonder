import { ref, reactive } from "vue";
import { useToast } from "@/Composables/useToast";
import axios from "axios";

export function useAccountValidation() {
    const { toast } = useToast();
    const isValidating = ref(false);
    const validationError = ref(null);
    const cachedUsernames = reactive({});
    const validationTimeout = 5 * 60 * 1000; // 5 minutes

    const validateGameAccount = async (produkSlug, validasiId, inputs) => {
        // Check if we have a cached username that's still valid
        const cacheKey = `${produkSlug}-${JSON.stringify(inputs)}`;
        const cachedData = cachedUsernames[cacheKey];

        if (
            cachedData &&
            Date.now() - cachedData.timestamp < validationTimeout
        ) {
            return {
                status: "success",
                username: cachedData.username,
                timestamp: cachedData.timestamp,
            };
        }

        isValidating.value = true;
        validationError.value = null;

        try {
            // Prepare API payload
            const payload = {
                produk_slug: produkSlug,
                inputs: inputs,
                validasi_id: validasiId,
            };

            // Call the API endpoint
            const response = await axios.post("/api/validate-account", payload);

            if (response.data.status === "success") {
                // Cache the successful response
                cachedUsernames[cacheKey] = {
                    username: response.data.username,
                    timestamp: Date.now(),
                };

                return {
                    status: "success",
                    username: response.data.username,
                };
            } else {
                validationError.value =
                    response.data.message || "Failed to validate account";
                return {
                    status: "error",
                    message: validationError.value,
                };
            }
        } catch (error) {
            const errorMessage =
                error.response?.data?.message || "Error validating account";
            validationError.value = errorMessage;
            toast.error(errorMessage);
            return {
                status: "error",
                message: errorMessage,
            };
        } finally {
            isValidating.value = false;
        }
    };

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
        validateGameAccount,
        validateInputFields,
        cachedUsernames,
    };
}


import { ref, reactive, computed } from 'vue';
import { useCookies } from "@/Composables/useCookies";

export function useGameAccount(productSlug) {
    const { setCookie, getCookie } = useCookies();
    const cookieName = 'saved_game_accounts';
    const expiryDays = 30;
    
    const accountData = ref({});
    const saveAccount = ref(false);
    const hasLoadedData = ref(false);
    
    // Load saved account data on initialization
    const loadSavedAccount = () => {
        try {
            const savedAccounts = getCookie(cookieName);
            if (savedAccounts && savedAccounts[productSlug]) {
                // Load the account data for this specific product
                accountData.value = { ...savedAccounts[productSlug].fields };
                saveAccount.value = true;
                hasLoadedData.value = true;
            }
        } catch (error) {
            console.error("Error loading saved account:", error);
        }
    };
    
    // Save account data to cookie
    const saveAccountToCookie = () => {
        if (!saveAccount.value || !productSlug || Object.keys(accountData.value).length === 0) {
            return;
        }
        
        try {
            let savedAccounts = getCookie(cookieName) || {};
            
            // Update or create account data for this product
            savedAccounts[productSlug] = {
                fields: { ...accountData.value },
                timestamp: new Date().toISOString()
            };
            
            // Save to cookie
            setCookie(cookieName, savedAccounts, expiryDays);
        } catch (error) {
            console.error("Error saving account:", error);
        }
    };
    
    // Update a specific field in the account data
    const updateAccountData = (fieldName, value) => {
        accountData.value = { ...accountData.value, [fieldName]: value };
        
        // Auto-save if the save checkbox is checked
        if (saveAccount.value) {
            saveAccountToCookie();
        }
    };
    
    // Watch for changes in the save checkbox
    const watchSaveAccount = (newValue) => {
        if (newValue) {
            saveAccountToCookie();
        }
    };
    
    // Load saved data on initialization
    loadSavedAccount();
    
    return {
        accountData,
        saveAccount,
        updateAccountData,
        hasLoadedData
    };
}

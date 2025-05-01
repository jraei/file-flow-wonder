
import { ref, watch } from 'vue';
import { useCookies } from './useCookies';

export function useGameAccount(productSlug) {
    const { setCookie, getCookie } = useCookies();
    const COOKIE_KEY = 'game_accounts';
    
    const accountData = ref({});
    const saveAccount = ref(false);
    
    // Load saved account data if available
    const loadSavedAccount = () => {
        const savedAccounts = getCookie(COOKIE_KEY) || {};
        if (savedAccounts[productSlug]) {
            accountData.value = savedAccounts[productSlug].fields || {};
            return true;
        }
        return false;
    };
    
    // Save account data to cookie
    const saveAccountData = () => {
        if (!saveAccount.value || !productSlug) return;
        
        const allAccounts = getCookie(COOKIE_KEY) || {};
        allAccounts[productSlug] = {
            fields: accountData.value,
            timestamp: new Date().toISOString()
        };
        
        setCookie(COOKIE_KEY, allAccounts, 30);
    };
    
    // Watch for changes to save account flag
    watch(saveAccount, (newVal) => {
        if (newVal) {
            saveAccountData();
        }
    });
    
    const updateAccountData = (fieldName, value) => {
        accountData.value = {
            ...accountData.value,
            [fieldName]: value
        };
        
        if (saveAccount.value) {
            saveAccountData();
        }
    };
    
    // Initialize by loading saved data
    const hasLoadedData = loadSavedAccount();
    
    return {
        accountData,
        saveAccount,
        updateAccountData,
        saveAccountData,
        hasLoadedData
    };
}

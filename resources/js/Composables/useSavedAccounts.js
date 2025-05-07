import { useCookies } from "@/Composables/useCookies";
import { v4 as uuidv4 } from "@/util/uuid";

export function useSavedAccounts() {
    const { getCookie, setCookie, deleteCookie } = useCookies();

    // Generate a simple UUID for IDs
    const generateUUID = () => {
        return uuidv4();
    };

    // Get saved accounts for a specific product
    const getSavedAccounts = (productSlug) => {
        try {
            const key = `saved_account_${productSlug}`;
            const savedAccounts = getCookie(key) || [];
            return Array.isArray(savedAccounts) ? savedAccounts : [];
        } catch (error) {
            console.error("Error getting saved accounts:", error);
            return [];
        }
    };

    // Save a new account
    const saveAccount = (productSlug, fields, contact) => {
        try {
            const key = `saved_account_${productSlug}`;
            const savedAccounts = getSavedAccounts(productSlug);

            // Create new account entry
            const newAccount = {
                id: generateUUID(),
                fields,
                contact: {
                    email: contact?.email || "",
                    phone: contact?.phone || "",
                },
                timestamp: new Date().toISOString(),
            };

            // Add to beginning of array (most recent first)
            savedAccounts.unshift(newAccount);

            // Keep only the 5 most recent entries
            const trimmedAccounts = savedAccounts.slice(0, 5);

            // Save to cookie with 7-day expiration
            setCookie(key, trimmedAccounts, 7);

            return newAccount;
        } catch (error) {
            console.error("Error saving account:", error);
            return null;
        }
    };

    // Load a saved account
    const loadSavedAccount = (productSlug, id) => {
        try {
            const savedAccounts = getSavedAccounts(productSlug);
            return savedAccounts.find((account) => account.id === id) || null;
        } catch (error) {
            console.error("Error loading saved account:", error);
            return null;
        }
    };

    // Delete a saved account
    const deleteSavedAccount = (productSlug, id) => {
        try {
            const key = `saved_account_${productSlug}`;
            const savedAccounts = getSavedAccounts(productSlug);
            const filteredAccounts = savedAccounts.filter(
                (account) => account.id !== id
            );

            setCookie(key, filteredAccounts, 7);
            return true;
        } catch (error) {
            console.error("Error deleting saved account:", error);
            return false;
        }
    };

    // Calculate how long ago the account was saved
    const getTimeAgo = (timestamp) => {
        try {
            const now = new Date();
            const saved = new Date(timestamp);
            const diffMs = now - saved;

            const diffMins = Math.floor(diffMs / (1000 * 60));
            const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
            const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

            if (diffMins < 60) {
                return `${diffMins}m ago`;
            } else if (diffHours < 24) {
                return `${diffHours}h ago`;
            } else {
                return `${diffDays}d ago`;
            }
        } catch (error) {
            return "Unknown";
        }
    };

    // Check if cookies are working
    const areCookiesEnabled = () => {
        try {
            // Try to set a test cookie
            document.cookie = "testcookie=1; max-age=10";
            const cookiesEnabled = document.cookie.indexOf("testcookie") !== -1;
            return cookiesEnabled;
        } catch (e) {
            return false;
        }
    };

    return {
        getSavedAccounts,
        saveAccount,
        loadSavedAccount,
        deleteSavedAccount,
        getTimeAgo,
        areCookiesEnabled,
    };
}

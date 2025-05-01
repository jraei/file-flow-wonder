
/**
 * Composable for handling phone number formatting and internationalization
 */
export function usePhoneFormatting() {
    /**
     * Format a phone number with country code
     * @param {string} number - The phone number
     * @param {string} countryCode - Country code (e.g., 'ID', 'US', etc.)
     * @returns {string} Formatted international phone number
     */
    const formatInternationalPhone = (number, countryCode = 'ID') => {
        if (!number) return '';

        // Strip all non-numeric characters
        let cleaned = number.replace(/\D/g, '');
        
        // Remove leading zeros
        cleaned = cleaned.replace(/^0+/, '');
        
        // Map of country codes
        const countryCodes = {
            'ID': '62', // Indonesia
            'MY': '60', // Malaysia
            'SG': '65', // Singapore
            'US': '1',  // United States
            // Add more country codes as needed
        };
        
        // Get the proper prefix
        const prefix = countryCodes[countryCode] || '62'; // Default to Indonesia
        
        return `+${prefix}${cleaned}`;
    };

    /**
     * Validate if a phone number is correctly formatted
     */
    const validatePhoneNumber = (number) => {
        if (!number) return false;
        // Basic validation - must be at least 10 digits when non-numeric chars are removed
        return number.replace(/\D/g, '').length >= 10;
    };

    return {
        formatInternationalPhone,
        validatePhoneNumber
    };
}

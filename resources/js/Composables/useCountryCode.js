
import { ref, computed } from 'vue';

export function useCountryCode() {
    const countryCode = ref('ID'); // Default to Indonesia
    const countryPrefix = ref('+62'); // Default to Indonesia prefix
    
    const countryCodes = {
        'ID': '+62',   // Indonesia
        'MY': '+60',   // Malaysia
        'SG': '+65',   // Singapore
        'TH': '+66',   // Thailand
        'VN': '+84',   // Vietnam
        'PH': '+63',   // Philippines
        'US': '+1',    // United States
        'GB': '+44',   // United Kingdom
        'AU': '+61',   // Australia
        // Add more country codes as needed
    };
    
    const setCountry = (code) => {
        if (countryCodes[code]) {
            countryCode.value = code;
            countryPrefix.value = countryCodes[code];
        }
    };
    
    const formatInternationalNumber = (number) => {
        if (!number) return '';
        
        // Remove any existing plus signs and leading zeros
        let cleanNumber = number.replace(/^\+|^0+/g, '');
        
        // Check if the number already includes the country code
        const currentPrefix = countryPrefix.value.replace('+', '');
        if (!cleanNumber.startsWith(currentPrefix)) {
            return `${countryPrefix.value}${cleanNumber}`;
        } else {
            return `+${cleanNumber}`;
        }
    };
    
    return {
        countryCode,
        countryPrefix,
        setCountry,
        formatInternationalNumber
    };
}

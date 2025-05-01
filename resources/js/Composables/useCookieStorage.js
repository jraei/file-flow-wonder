
/**
 * Composable for managing persistent data via cookies
 */
export function useCookieStorage() {
    const setCookie = (name, value, days = 30) => {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = `expires=${date.toUTCString()}`;
        const cookieValue = typeof value === 'object' ? JSON.stringify(value) : value;
        document.cookie = `${name}=${cookieValue};${expires};path=/;SameSite=Lax`;
    };

    const getCookie = (name) => {
        const nameEQ = `${name}=`;
        const cookies = document.cookie.split(';');
        
        for (let i = 0; i < cookies.length; i++) {
            let cookie = cookies[i];
            while (cookie.charAt(0) === ' ') {
                cookie = cookie.substring(1);
            }
            
            if (cookie.indexOf(nameEQ) === 0) {
                const cookieValue = cookie.substring(nameEQ.length, cookie.length);
                try {
                    // Try to parse as JSON
                    return JSON.parse(cookieValue);
                } catch (e) {
                    // Return as string if not valid JSON
                    return cookieValue;
                }
            }
        }
        
        return null;
    };

    const removeCookie = (name) => {
        document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/`;
    };

    return {
        setCookie,
        getCookie,
        removeCookie
    };
}

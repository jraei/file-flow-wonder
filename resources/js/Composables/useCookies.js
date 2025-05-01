
export function useCookies() {
    // Set a cookie with a specified expiration time
    const setCookie = (name, value, days = 7) => {
        try {
            const d = new Date();
            d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = "expires=" + d.toUTCString();
            const stringValue = JSON.stringify(value);
            document.cookie = name + "=" + encodeURIComponent(stringValue) + ";" + expires + ";path=/";
            return true;
        } catch (error) {
            console.error("Error setting cookie:", error);
            return false;
        }
    };

    // Get a cookie by name
    const getCookie = (name) => {
        try {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) {
                    const rawValue = decodeURIComponent(c.substring(nameEQ.length, c.length));
                    return JSON.parse(rawValue);
                }
            }
            return null;
        } catch (error) {
            console.error("Error getting cookie:", error);
            return null;
        }
    };

    // Delete a cookie by name
    const deleteCookie = (name) => {
        document.cookie = name + '=; Max-Age=-99999999; path=/';
    };

    return {
        setCookie,
        getCookie,
        deleteCookie
    };
}

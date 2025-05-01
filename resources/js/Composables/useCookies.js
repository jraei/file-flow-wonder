
export function useCookies() {
    const setCookie = (name, value, days = 30) => {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = "; expires=" + date.toUTCString();
        document.cookie = name + "=" + (JSON.stringify(value) || "") + expires + "; path=/; SameSite=Strict";
    };

    const getCookie = (name) => {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) {
                try {
                    return JSON.parse(c.substring(nameEQ.length, c.length));
                } catch (e) {
                    return c.substring(nameEQ.length, c.length);
                }
            }
        }
        return null;
    };

    const deleteCookie = (name) => {
        document.cookie = name + '=; Max-Age=-99999999; path=/';
    };

    return {
        setCookie,
        getCookie,
        deleteCookie
    };
}

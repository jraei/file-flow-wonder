export default {
    install: (app) => {
        if (
            typeof window !== "undefined" &&
            typeof window.Swal === "undefined" &&
            typeof document !== "undefined"
        ) {
            // Dynamically import SweetAlert2 if needed
            import("sweetalert2").then((Swal) => {
                window.Swal = Swal.default;
            });
        }

        app.config.globalProperties.$swal = window.Swal;
    },
};

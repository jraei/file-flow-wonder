// plugins/swalPlugin.js
import Swal from "sweetalert2";

export default {
    install(app) {
        app.config.globalProperties.$showSwalConfirm = ({
            title = "Hapus Data",
            text = "Apakah Anda yakin ingin menghapus data ini?",
            icon = "warning",
            confirmButtonText = "Ya, hapus!",
            cancelButtonText = "Batal",
            theme = "dark",
            customClass = {
                popup: "bg-dark-card rounded-xl",
                confirmButton:
                    "bg-primary text-white shadow-glow-primary  rounded-lg",
            },
            onConfirm = () => {
                Swal.fire("Berhasil!", "Aksi telah dikonfirmasi.", "success");
            },
        }) => {
            Swal.fire({
                title,
                text,
                icon,
                theme,
                showCancelButton: true,
                confirmButtonText,
                cancelButtonText,
                customClass,
            }).then((result) => {
                if (result.isConfirmed) {
                    onConfirm();
                }
            });
        };
    },
};

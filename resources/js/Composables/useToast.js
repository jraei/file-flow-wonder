
import Swal from 'sweetalert2';

export const useToast = () => {
    const toast = {
        success(message) {
            Swal.fire({
                title: 'Success!',
                text: message,
                icon: 'success',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#1F2937',
                color: '#E5E7EB',
                iconColor: '#9b87f5'
            });
        },
        error(message) {
            Swal.fire({
                title: 'Error!',
                text: message,
                icon: 'error',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                background: '#1F2937',
                color: '#E5E7EB',
                iconColor: '#33C3F0'
            });
        },
        warning(message) {
            Swal.fire({
                title: 'Warning!',
                text: message,
                icon: 'warning',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                background: '#1F2937',
                color: '#E5E7EB',
                iconColor: '#33C3F0'
            });
        },
        info(message) {
            Swal.fire({
                title: 'Info',
                text: message,
                icon: 'info',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#1F2937',
                color: '#E5E7EB',
                iconColor: '#9b87f5'
            });
        },
    };

    return { toast };
};

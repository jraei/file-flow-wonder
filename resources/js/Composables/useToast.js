
import { inject, onMounted } from 'vue';

export function useToast() {
    const swal = inject('$swal');

    const showToast = (message, type = 'success') => {
        if (!swal) {
            console.error('Sweetalert2 is not available');
            return;
        }

        const Toast = swal.mixin({
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = swal.stopTimer;
                toast.onmouseleave = swal.resumeTimer;
            }
        });

        Toast.fire({
            icon: type,
            title: message,
            background: type === 'error' ? '#2D3748' : '#1A202C',
            color: '#E2E8F0',
            iconColor: type === 'success' ? '#9b87f5' : '#f56565'
        });
    };

    return {
        showToast
    };
}

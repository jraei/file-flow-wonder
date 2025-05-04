
import { ref, onMounted } from 'vue';

export function useToast() {
  const isVisible = ref(false);
  const message = ref('');
  const type = ref('success'); // success, error, warning, info
  let timeout = null;

  // Check if we're in a browser environment
  const isBrowser = typeof window !== 'undefined';
  
  // Check if Swal is available
  const hasSwal = isBrowser && typeof window.Swal !== 'undefined';
  
  onMounted(() => {
    // Clean up any existing timeout on component mount
    return () => {
      if (timeout) {
        clearTimeout(timeout);
      }
    };
  });

  const showToast = (msg, toastType = 'success', duration = 3000) => {
    if (hasSwal) {
      // Use SweetAlert if available
      window.Swal.fire({
        toast: true,
        position: 'top-end',
        icon: toastType,
        title: msg,
        showConfirmButton: false,
        timer: duration,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.addEventListener('mouseenter', window.Swal.stopTimer);
          toast.addEventListener('mouseleave', window.Swal.resumeTimer);
        }
      });
    } else {
      // Fallback to basic toast
      message.value = msg;
      type.value = toastType;
      isVisible.value = true;
      
      if (timeout) {
        clearTimeout(timeout);
      }
      
      timeout = setTimeout(() => {
        isVisible.value = false;
      }, duration);
    }
  };

  return {
    isVisible,
    message,
    type,
    showToast
  };
}

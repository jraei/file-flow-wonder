
import { ref } from 'vue';

const toasts = ref([]);
let toastTimeout = null;

export function useToast() {
  const toast = ({ title, description, variant = 'default', duration = 3000 }) => {
    const id = Date.now();
    
    toasts.value.push({
      id,
      title,
      description,
      variant,
    });
    
    if (toastTimeout) {
      clearTimeout(toastTimeout);
    }
    
    toastTimeout = setTimeout(() => {
      toasts.value = toasts.value.filter(t => t.id !== id);
    }, duration);
  };
  
  const dismissToast = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
  };
  
  return {
    toast,
    toasts,
    dismissToast
  };
}

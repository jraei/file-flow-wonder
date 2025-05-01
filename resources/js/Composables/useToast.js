
/**
 * Composable for displaying toast notifications
 */
export function useToast() {
    const toast = {
        /**
         * Show success toast
         * @param {string} message - Message to display
         * @param {number} duration - Duration in ms (default: 3000ms)
         */
        success: (message, duration = 3000) => {
            // Use an existing toast library if available
            if (window.$toast) {
                window.$toast.success(message);
                return;
            }
            
            // Fallback basic implementation
            showToast(message, 'success', duration);
        },
        
        /**
         * Show error toast
         * @param {string} message - Message to display
         * @param {number} duration - Duration in ms (default: 5000ms)
         */
        error: (message, duration = 5000) => {
            // Use an existing toast library if available
            if (window.$toast) {
                window.$toast.error(message);
                return;
            }
            
            // Fallback basic implementation
            showToast(message, 'error', duration);
        },
        
        /**
         * Show info toast
         * @param {string} message - Message to display
         * @param {number} duration - Duration in ms (default: 3000ms)
         */
        info: (message, duration = 3000) => {
            // Use an existing toast library if available
            if (window.$toast) {
                window.$toast.info(message);
                return;
            }
            
            // Fallback basic implementation
            showToast(message, 'info', duration);
        },
        
        /**
         * Show warning toast
         * @param {string} message - Message to display
         * @param {number} duration - Duration in ms (default: 4000ms)
         */
        warning: (message, duration = 4000) => {
            // Use an existing toast library if available
            if (window.$toast) {
                window.$toast.warning(message);
                return;
            }
            
            // Fallback basic implementation
            showToast(message, 'warning', duration);
        }
    };
    
    /**
     * Internal method to show a toast notification
     */
    const showToast = (message, type, duration) => {
        // Create container if doesn't exist
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.position = 'fixed';
            container.style.top = '1rem';
            container.style.right = '1rem';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        
        // Create toast element
        const toast = document.createElement('div');
        toast.style.minWidth = '200px';
        toast.style.padding = '0.75rem 1rem';
        toast.style.marginBottom = '0.5rem';
        toast.style.borderRadius = '0.375rem';
        toast.style.boxShadow = '0 2px 5px rgba(0, 0, 0, 0.2)';
        toast.style.animation = 'fadeIn 0.3s, fadeOut 0.3s ' + (duration/1000 - 0.3) + 's';
        toast.style.display = 'flex';
        toast.style.alignItems = 'center';
        
        // Set type-specific styles
        switch (type) {
            case 'success':
                toast.style.backgroundColor = 'rgba(34, 197, 94, 0.9)';
                toast.style.color = 'white';
                break;
            case 'error':
                toast.style.backgroundColor = 'rgba(239, 68, 68, 0.9)';
                toast.style.color = 'white';
                break;
            case 'info':
                toast.style.backgroundColor = 'rgba(59, 130, 246, 0.9)';
                toast.style.color = 'white';
                break;
            case 'warning':
                toast.style.backgroundColor = 'rgba(245, 158, 11, 0.9)';
                toast.style.color = 'white';
                break;
        }
        
        // Set content
        toast.textContent = message;
        
        // Add to container
        container.appendChild(toast);
        
        // Remove after duration
        setTimeout(() => {
            container.removeChild(toast);
            if (container.childElementCount === 0) {
                document.body.removeChild(container);
            }
        }, duration);
    };
    
    // Add animation styles if they don't exist
    if (!document.getElementById('toast-animations')) {
        const style = document.createElement('style');
        style.id = 'toast-animations';
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes fadeOut {
                from { opacity: 1; transform: translateY(0); }
                to { opacity: 0; transform: translateY(-20px); }
            }
        `;
        document.head.appendChild(style);
    }
    
    return { toast };
}

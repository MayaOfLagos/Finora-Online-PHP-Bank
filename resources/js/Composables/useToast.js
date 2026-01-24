/**
 * useToast Composable
 * Toast notification utilities using PrimeVue
 */
import { useToast as usePrimeToast } from 'primevue/usetoast';

export function useToast() {
    const toast = usePrimeToast();

    /**
     * Show success toast
     */
    const success = (message, title = 'Success') => {
        toast.add({
            severity: 'success',
            summary: title,
            detail: message,
            life: 3000,
        });
    };

    /**
     * Show error toast
     */
    const error = (message, title = 'Error') => {
        toast.add({
            severity: 'error',
            summary: title,
            detail: message,
            life: 5000,
        });
    };

    /**
     * Show warning toast
     */
    const warn = (message, title = 'Warning') => {
        toast.add({
            severity: 'warn',
            summary: title,
            detail: message,
            life: 4000,
        });
    };

    /**
     * Show info toast
     */
    const info = (message, title = 'Info') => {
        toast.add({
            severity: 'info',
            summary: title,
            detail: message,
            life: 3000,
        });
    };

    /**
     * Show custom toast
     */
    const show = (options) => {
        toast.add({
            life: 3000,
            ...options,
        });
    };

    /**
     * Clear all toasts
     */
    const clear = () => {
        toast.removeAllGroups();
    };

    return {
        success,
        error,
        warn,
        info,
        show,
        clear,
    };
}

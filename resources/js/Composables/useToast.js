/**
 * useToast Composable
 * Toast notification utilities using Vue Toastification
 * 
 * @see https://github.com/Maronato/vue-toastification
 */
import { useToast as useVueToast } from 'vue-toastification';

export function useToast() {
    const toast = useVueToast();

    /**
     * Show success toast
     * @param {string} message - The message to display
     * @param {string} [title] - Optional title (prepended to message)
     * @param {object} [options] - Additional options
     */
    const success = (message, title = null, options = {}) => {
        const content = title ? `${title}: ${message}` : message;
        toast.success(content, {
            timeout: 3000,
            ...options,
        });
    };

    /**
     * Show error toast
     * @param {string} message - The message to display
     * @param {string} [title] - Optional title (prepended to message)
     * @param {object} [options] - Additional options
     */
    const error = (message, title = null, options = {}) => {
        const content = title ? `${title}: ${message}` : message;
        toast.error(content, {
            timeout: 6000,
            ...options,
        });
    };

    /**
     * Show warning toast
     * @param {string} message - The message to display
     * @param {string} [title] - Optional title (prepended to message)
     * @param {object} [options] - Additional options
     */
    const warn = (message, title = null, options = {}) => {
        const content = title ? `${title}: ${message}` : message;
        toast.warning(content, {
            timeout: 4000,
            ...options,
        });
    };

    /**
     * Show info toast
     * @param {string} message - The message to display
     * @param {string} [title] - Optional title (prepended to message)
     * @param {object} [options] - Additional options
     */
    const info = (message, title = null, options = {}) => {
        const content = title ? `${title}: ${message}` : message;
        toast.info(content, {
            timeout: 3000,
            ...options,
        });
    };

    /**
     * Show default toast
     * @param {string} message - The message to display
     * @param {object} [options] - Additional options
     */
    const show = (message, options = {}) => {
        toast(message, {
            timeout: 3000,
            ...options,
        });
    };

    /**
     * Dismiss a specific toast by ID
     * @param {string|number} toastId - The toast ID to dismiss
     */
    const dismiss = (toastId) => {
        toast.dismiss(toastId);
    };

    /**
     * Clear all toasts
     */
    const clear = () => {
        toast.clear();
    };

    /**
     * Update an existing toast
     * @param {string|number} toastId - The toast ID to update
     * @param {object} options - New options including content
     */
    const update = (toastId, options = {}) => {
        toast.update(toastId, options);
    };

    /**
     * Show a loading toast that can be updated later
     * @param {string} message - The loading message
     * @param {object} [options] - Additional options
     * @returns {string|number} - The toast ID for updating later
     */
    const loading = (message, options = {}) => {
        return toast.info(message, {
            timeout: false,
            closeOnClick: false,
            closeButton: false,
            draggable: false,
            ...options,
        });
    };

    /**
     * Show a promise toast that updates based on promise state
     * @param {Promise} promise - The promise to track
     * @param {object} messages - Messages for pending, success, error states
     * @param {object} [options] - Additional options
     */
    const promise = async (promiseToTrack, messages = {}, options = {}) => {
        const {
            pending = 'Processing...',
            success: successMsg = 'Success!',
            error: errorMsg = 'Something went wrong',
        } = messages;

        const toastId = loading(pending);

        try {
            const result = await promiseToTrack;
            toast.update(toastId, {
                content: typeof successMsg === 'function' ? successMsg(result) : successMsg,
                options: {
                    type: 'success',
                    timeout: 3000,
                    closeOnClick: true,
                    closeButton: 'button',
                    draggable: true,
                    ...options,
                },
            });
            return result;
        } catch (err) {
            toast.update(toastId, {
                content: typeof errorMsg === 'function' ? errorMsg(err) : errorMsg,
                options: {
                    type: 'error',
                    timeout: 6000,
                    closeOnClick: true,
                    closeButton: 'button',
                    draggable: true,
                    ...options,
                },
            });
            throw err;
        }
    };

    return {
        // Main methods
        success,
        error,
        warn,
        warning: warn, // alias
        info,
        show,
        
        // Control methods
        dismiss,
        clear,
        update,
        
        // Advanced methods
        loading,
        promise,
        
        // Raw toast instance for advanced usage
        toast,
    };
}

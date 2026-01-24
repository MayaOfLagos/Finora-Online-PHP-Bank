/**
 * useConfirm Composable
 * Confirmation dialog utilities using PrimeVue
 */
import { useConfirm as usePrimeConfirm } from 'primevue/useconfirm';

export function useConfirm() {
    const confirm = usePrimeConfirm();

    /**
     * Show danger/delete confirmation
     */
    const danger = (options) => {
        return new Promise((resolve) => {
            confirm.require({
                message: options.message || 'Are you sure you want to proceed?',
                header: options.header || 'Confirmation',
                icon: options.icon || 'pi pi-exclamation-triangle',
                rejectClass: 'p-button-secondary p-button-outlined',
                acceptClass: 'p-button-danger',
                acceptLabel: options.acceptLabel || 'Delete',
                rejectLabel: options.rejectLabel || 'Cancel',
                accept: () => resolve(true),
                reject: () => resolve(false),
            });
        });
    };

    /**
     * Show standard confirmation
     */
    const basic = (options) => {
        return new Promise((resolve) => {
            confirm.require({
                message: options.message || 'Are you sure you want to proceed?',
                header: options.header || 'Confirmation',
                icon: options.icon || 'pi pi-question-circle',
                rejectClass: 'p-button-secondary p-button-outlined',
                acceptLabel: options.acceptLabel || 'Confirm',
                rejectLabel: options.rejectLabel || 'Cancel',
                accept: () => resolve(true),
                reject: () => resolve(false),
            });
        });
    };

    /**
     * Show transfer confirmation
     */
    const transfer = (options) => {
        return new Promise((resolve) => {
            confirm.require({
                message: `Are you sure you want to transfer ${options.amount} to ${options.recipient}?`,
                header: 'Confirm Transfer',
                icon: 'pi pi-send',
                rejectClass: 'p-button-secondary p-button-outlined',
                acceptClass: 'p-button-success',
                acceptLabel: 'Confirm Transfer',
                rejectLabel: 'Cancel',
                accept: () => resolve(true),
                reject: () => resolve(false),
            });
        });
    };

    return {
        danger,
        basic,
        transfer,
    };
}

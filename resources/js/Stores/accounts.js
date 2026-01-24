/**
 * Accounts Store
 * Manages bank accounts state
 */
import { defineStore } from 'pinia';
import { usePage } from '@inertiajs/vue3';

export const useAccountsStore = defineStore('accounts', {
    state: () => ({
        selectedAccountId: null,
        showBalances: true,
    }),

    getters: {
        /**
         * Get all user accounts from page props
         */
        accounts() {
            const page = usePage();
            return page.props.accounts || [];
        },

        /**
         * Get the primary account
         */
        primaryAccount() {
            return this.accounts.find(acc => acc.is_primary) || this.accounts[0];
        },

        /**
         * Get selected account or primary
         */
        selectedAccount() {
            if (this.selectedAccountId) {
                return this.accounts.find(acc => acc.id === this.selectedAccountId);
            }
            return this.primaryAccount;
        },

        /**
         * Calculate total balance across all accounts
         */
        totalBalance() {
            return this.accounts.reduce((sum, acc) => sum + (acc.balance || 0), 0);
        },

        /**
         * Get accounts grouped by type
         */
        accountsByType() {
            const grouped = {};
            this.accounts.forEach(account => {
                const type = account.type || 'other';
                if (!grouped[type]) {
                    grouped[type] = [];
                }
                grouped[type].push(account);
            });
            return grouped;
        },
    },

    actions: {
        /**
         * Select an account
         */
        selectAccount(accountId) {
            this.selectedAccountId = accountId;
        },

        /**
         * Toggle balance visibility
         */
        toggleBalances() {
            this.showBalances = !this.showBalances;
        },

        /**
         * Clear selection
         */
        clearSelection() {
            this.selectedAccountId = null;
        },
    },

    persist: {
        paths: ['showBalances', 'selectedAccountId'],
    },
});

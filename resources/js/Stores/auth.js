/**
 * Auth Store
 * Manages authentication state
 */
import { defineStore } from 'pinia';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        // Additional auth state if needed
    }),

    getters: {
        /**
         * Get the current authenticated user
         */
        user() {
            const page = usePage();
            return page.props.auth?.user || null;
        },

        /**
         * Check if user is authenticated
         */
        isAuthenticated() {
            return !!this.user;
        },

        /**
         * Get user's initials for avatar
         */
        userInitials() {
            if (!this.user?.name) return 'U';
            return this.user.name
                .split(' ')
                .map(n => n[0])
                .slice(0, 2)
                .join('')
                .toUpperCase();
        },

        /**
         * Get user's first name
         */
        firstName() {
            if (!this.user?.name) return 'User';
            return this.user.name.split(' ')[0];
        },
    },

    actions: {
        // Add any auth-related actions here
    },
});

import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, Link } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';

// PrimeVue
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import ConfirmationService from 'primevue/confirmationservice';
import Tooltip from 'primevue/tooltip';
import Ripple from 'primevue/ripple';

// Vue Toastification
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';

// PrimeVue CSS
import 'primeicons/primeicons.css';

// Vue Toastification options
const toastOptions = {
    position: 'top-right',
    timeout: 4000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: 'button',
    icon: true,
    rtl: false,
    transition: 'Vue-Toastification__slideBlurred',
    maxToasts: 5,
    newestOnTop: true,
    filterBeforeCreate: (toast, toasts) => {
        // Prevent duplicate toasts
        if (toasts.filter(t => t.content === toast.content).length !== 0) {
            return false;
        }
        return toast;
    },
};

const appName =
    import.meta.env.VITE_APP_NAME || 'Finora Bank';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    // Lazy load pages for code splitting (faster initial load)
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`,
        import.meta.glob('./Pages/**/*.vue')), // Removed eager: true for lazy loading
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();

        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue)
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                    options: {
                        prefix: 'p',
                        darkModeSelector: '.dark',
                        cssLayer: false,
                    }
                },
                ripple: true,
            })
            .use(Toast, toastOptions)
            .use(ConfirmationService)
            .directive('tooltip', Tooltip)
            .directive('ripple', Ripple)
            .component('Link', Link); // Register Inertia Link globally

        return app.mount(el);
    },
    progress: {
        // Sleek progress bar for SPA navigation
        delay: 100, // Show after 100ms delay (feels instant for fast loads)
        color: '#10B981', // Emerald green to match brand
        includeCSS: true,
        showSpinner: false, // Cleaner without spinner
    },
});
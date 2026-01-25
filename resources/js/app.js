import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';

// PrimeVue
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import Tooltip from 'primevue/tooltip';
import Ripple from 'primevue/ripple';

// PrimeVue CSS
import 'primeicons/primeicons.css';

const appName =
    import.meta.env.VITE_APP_NAME || 'Finora Bank';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`,
        import.meta.glob('./Pages/**/*.vue', { eager: true })),
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
            .use(ToastService)
            .use(ConfirmationService)
            .directive('tooltip', Tooltip)
            .directive('ripple', Ripple);

        return app.mount(el);
    },
    progress: {
        color: '#4F46E5',
    },
});
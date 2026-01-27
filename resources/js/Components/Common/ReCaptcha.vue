<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    siteKey: {
        type: String,
        required: true,
    },
    version: {
        type: String,
        default: 'v2',
        validator: (value) => ['v2', 'v3'].includes(value),
    },
    action: {
        type: String,
        default: 'submit',
    },
    theme: {
        type: String,
        default: 'light',
        validator: (value) => ['light', 'dark'].includes(value),
    },
    size: {
        type: String,
        default: 'normal',
        validator: (value) => ['compact', 'normal'].includes(value),
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['verify', 'expire', 'error', 'ready']);

const recaptchaContainer = ref(null);
const widgetId = ref(null);
const token = ref('');
const isReady = ref(false);
const isLoading = ref(true);
const error = ref(null);

// Generate unique callback names
const callbackId = `recaptcha_${Math.random().toString(36).substr(2, 9)}`;

// Load reCAPTCHA script
const loadScript = () => {
    return new Promise((resolve, reject) => {
        // Check if already loaded
        if (window.grecaptcha && window.grecaptcha.render) {
            resolve();
            return;
        }

        // Check if script is already being loaded
        const existingScript = document.querySelector('script[src*="recaptcha"]');
        if (existingScript) {
            existingScript.addEventListener('load', resolve);
            existingScript.addEventListener('error', reject);
            return;
        }

        // Create callback function
        window[callbackId] = () => {
            resolve();
            delete window[callbackId];
        };

        // Determine API URL based on version
        let apiUrl = 'https://www.google.com/recaptcha/api.js';
        if (props.version === 'v3') {
            apiUrl += `?render=${props.siteKey}`;
        } else {
            apiUrl += `?onload=${callbackId}&render=explicit`;
        }

        // Create and append script
        const script = document.createElement('script');
        script.src = apiUrl;
        script.async = true;
        script.defer = true;
        script.onerror = reject;
        document.head.appendChild(script);
    });
};

// Initialize reCAPTCHA v2
const initV2 = () => {
    if (!recaptchaContainer.value || widgetId.value !== null) return;

    try {
        widgetId.value = window.grecaptcha.render(recaptchaContainer.value, {
            sitekey: props.siteKey,
            theme: props.theme,
            size: props.size,
            callback: onVerify,
            'expired-callback': onExpire,
            'error-callback': onError,
        });
        isReady.value = true;
        isLoading.value = false;
        emit('ready');
    } catch (e) {
        console.error('reCAPTCHA render error:', e);
        error.value = 'Failed to load security verification';
        isLoading.value = false;
        emit('error', e);
    }
};

// Initialize reCAPTCHA v3
const initV3 = () => {
    isReady.value = true;
    isLoading.value = false;
    emit('ready');
};

// Execute reCAPTCHA v3
const executeV3 = async () => {
    if (props.version !== 'v3' || !window.grecaptcha) return '';

    try {
        const result = await window.grecaptcha.execute(props.siteKey, { action: props.action });
        token.value = result;
        emit('verify', result);
        return result;
    } catch (e) {
        console.error('reCAPTCHA v3 execute error:', e);
        emit('error', e);
        return '';
    }
};

// v2 callback handlers
const onVerify = (response) => {
    token.value = response;
    emit('verify', response);
};

const onExpire = () => {
    token.value = '';
    emit('expire');
};

const onError = () => {
    error.value = 'Verification failed';
    emit('error', new Error('reCAPTCHA error'));
};

// Reset reCAPTCHA v2
const reset = () => {
    if (props.version === 'v2' && widgetId.value !== null && window.grecaptcha) {
        window.grecaptcha.reset(widgetId.value);
        token.value = '';
    }
};

// Get current token (for v2, use directly; for v3, execute first)
const getToken = async () => {
    if (props.version === 'v3') {
        return await executeV3();
    }
    return token.value;
};

// Expose methods to parent
defineExpose({
    reset,
    getToken,
    executeV3,
    token,
    isReady,
});

onMounted(async () => {
    if (!props.siteKey) {
        isLoading.value = false;
        return;
    }

    try {
        await loadScript();

        // Wait for grecaptcha to be ready
        if (window.grecaptcha && window.grecaptcha.ready) {
            window.grecaptcha.ready(() => {
                if (props.version === 'v2') {
                    initV2();
                } else {
                    initV3();
                }
            });
        } else {
            // Fallback for older grecaptcha versions
            setTimeout(() => {
                if (props.version === 'v2') {
                    initV2();
                } else {
                    initV3();
                }
            }, 500);
        }
    } catch (e) {
        console.error('Failed to load reCAPTCHA:', e);
        error.value = 'Failed to load security verification';
        isLoading.value = false;
        emit('error', e);
    }
});

onUnmounted(() => {
    // Cleanup callback if it exists
    if (window[callbackId]) {
        delete window[callbackId];
    }
});

// Watch for theme changes (v2 only - requires re-render)
watch(() => props.theme, () => {
    if (props.version === 'v2' && widgetId.value !== null) {
        // reCAPTCHA v2 doesn't support dynamic theme changes
        // Would need to destroy and re-render
    }
});
</script>

<template>
    <!-- reCAPTCHA v2 Widget -->
    <div v-if="version === 'v2'" class="recaptcha-container">
        <!-- Loading state -->
        <div v-if="isLoading" class="flex items-center justify-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 min-h-[78px]">
            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                <i class="pi pi-spin pi-spinner text-lg"></i>
                <span class="text-sm">Loading security verification...</span>
            </div>
        </div>

        <!-- Error state -->
        <div v-else-if="error" class="p-4 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg border border-red-200 dark:border-red-800 text-sm">
            <div class="flex items-center gap-2">
                <i class="pi pi-exclamation-triangle"></i>
                <span>{{ error }}</span>
            </div>
        </div>

        <!-- reCAPTCHA widget container -->
        <div 
            v-show="!isLoading && !error"
            ref="recaptchaContainer"
            class="recaptcha-widget"
            :class="{ 'opacity-50 pointer-events-none': disabled }"
        ></div>
    </div>

    <!-- reCAPTCHA v3 is invisible - no UI needed -->
    <div v-else class="recaptcha-v3-badge">
        <!-- v3 badge is automatically added by Google -->
        <!-- This invisible container is for reference only -->
    </div>
</template>

<style scoped>
.recaptcha-container {
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
    display: flex;
    justify-content: center;
    width: 100%;
    overflow: visible;
}

.recaptcha-widget {
    transform-origin: left top;
    display: flex;
    justify-content: center;
}

/* Make reCAPTCHA responsive on mobile */
@media (max-width: 400px) {
    .recaptcha-widget {
        transform: scale(0.95);
        margin-left: -8px;
    }
}

/* Hide v3 badge styling - Google requires it to be visible or use text attribution */
.recaptcha-v3-badge {
    /* Intentionally empty - badge is managed by Google */
}
</style>

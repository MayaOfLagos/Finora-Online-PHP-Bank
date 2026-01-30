<script setup>
/**
 * Register Page - Multi-Step Registration Form
 * Pure Tailwind CSS Implementation for Better Performance
 * 4-Step Wizard: Personal → Contact → Account → Security
 */
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import Skeleton from 'primevue/skeleton';
import ReCaptcha from '@/Components/Common/ReCaptcha.vue';
import SeoHead from '@/Components/Common/SeoHead.vue';
import PagePreloader from '@/Components/Common/PagePreloader.vue';

const page = usePage();
const siteName = computed(() => page.props.settings?.general?.site_name || page.props.settings?.general?.app_name || 'Finora Bank');
const siteTagline = computed(() => page.props.settings?.general?.app_tagline || 'Secure Banking Platform');
const siteInitial = computed(() => siteName.value.charAt(0) || 'F');
const logoLight = computed(() => page.props.settings?.branding?.logo_light);
const logoDark = computed(() => page.props.settings?.branding?.logo_dark);
const copyrightText = computed(() => page.props.settings?.branding?.copyright_text || siteName.value);

const props = defineProps({
    countries: {
        type: Array,
        default: () => []
    },
    accountTypes: {
        type: Array,
        default: () => []
    },
    currencies: {
        type: Array,
        default: () => []
    },
    errors: {
        type: Object,
        default: () => ({})
    },
    recaptcha: {
        type: Object,
        default: () => ({ enabled: false, siteKey: '', version: 'v2' }),
    },
});

const toast = useToast();

// Loading states
const isPageLoading = ref(true);
const isProcessing = ref(false);
const showContent = ref(false);

const handlePreloaderComplete = () => {
    isPageLoading.value = false;
    setTimeout(() => {
        showContent.value = true;
    }, 100);
};

// Current step (0-3)
const currentStep = ref(0);

// Animated quotes for side panel
const quotes = ref([]);
const currentQuoteIndex = ref(0);
const quoteVisible = ref(true);
let quoteInterval = null;

const setQuotes = () => {
    quotes.value = [
        { text: 'Banking made simple, secure, and smart.', author: siteName.value },
        { text: 'Your financial future starts here.', author: siteName.value },
        { text: 'Experience the next generation of banking.', author: siteName.value },
        { text: 'Where security meets convenience.', author: siteTagline.value },
    ];
};

watch(siteName, () => setQuotes(), { immediate: true });

onMounted(() => {
    quoteInterval = setInterval(() => {
        quoteVisible.value = false;
        setTimeout(() => {
            const quoteCount = quotes.value.length || 1;
            currentQuoteIndex.value = (currentQuoteIndex.value + 1) % quoteCount;
            quoteVisible.value = true;
        }, 500);
    }, 5000);
});

onUnmounted(() => {
    if (quoteInterval) clearInterval(quoteInterval);
});

const currentQuote = computed(() => quotes.value[currentQuoteIndex.value] || { text: '', author: '' });

// Form data
const formData = ref({
    // Personal Info
    first_name: '',
    last_name: '',
    middle_name: '',
    username: '',
    
    // Contact Info
    email: '',
    phone: '',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    country: '',
    
    // Account Info
    account_type: '',
    currency: 'USD',
    
    // Security Info
    password: '',
    password_confirmation: '',
    transaction_pin: '',
    transaction_pin_confirmation: '',
    agree_terms: false,
    agree_privacy: false,
});

// Password visibility toggles
const showPassword = ref(false);
const showPasswordConfirm = ref(false);

const recaptchaRef = ref(null);
const recaptchaToken = ref('');

// Modal dialogs state
const showTermsModal = ref(false);
const showPrivacyModal = ref(false);

// Step configuration with PrimeIcons
const steps = [
    { label: 'Personal', shortLabel: 'Info', icon: 'pi-user' },
    { label: 'Contact', shortLabel: 'Contact', icon: 'pi-map-marker' },
    { label: 'Account', shortLabel: 'Account', icon: 'pi-wallet' },
    { label: 'Security', shortLabel: 'Security', icon: 'pi-shield' }
];

// Currency options (with fallback if prop is empty)
const currencyOptions = computed(() => {
    if (props.currencies && props.currencies.length > 0) {
        return props.currencies;
    }
    return [
        { label: 'US Dollar ($)', value: 'USD' },
        { label: 'Euro (€)', value: 'EUR' },
        { label: 'British Pound (£)', value: 'GBP' },
        { label: 'Nigerian Naira (₦)', value: 'NGN' },
    ];
});

// Country options (with fallback if prop is empty)
const countryOptions = computed(() => {
    if (props.countries && props.countries.length > 0) {
        return props.countries;
    }
    return [
        { label: 'United States', value: 'US' },
        { label: 'United Kingdom', value: 'GB' },
        { label: 'Canada', value: 'CA' },
        { label: 'Nigeria', value: 'NG' },
    ];
});

// Account type options (with fallback if prop is empty)
const accountTypeOptions = computed(() => {
    if (props.accountTypes && props.accountTypes.length > 0) {
        return props.accountTypes.map(type => ({
            ...type,
            id: type.value,
            name: type.label,
            icon: type.value === 'SAV' ? 'money-bill' : 
                  type.value === 'CHK' ? 'credit-card' : 
                  type.value === 'BUS' ? 'briefcase' : 
                  type.value === 'PRM' ? 'star' : 'wallet',
        }));
    }
    return [
        { id: 'SAV', value: 'SAV', name: 'Savings Account', label: 'Savings Account', icon: 'money-bill', description: 'Earn interest on your balance' },
        { id: 'CHK', value: 'CHK', name: 'Checking Account', label: 'Checking Account', icon: 'credit-card', description: 'Daily transactions' },
        { id: 'BUS', value: 'BUS', name: 'Business Account', label: 'Business Account', icon: 'briefcase', description: 'For business needs' }
    ];
});

// Password strength calculator
const passwordStrength = computed(() => {
    const password = formData.value.password;
    if (!password) return { score: 0, label: '', color: '' };
    
    let score = 0;
    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
    if (/\d/.test(password)) score++;
    if (/[^a-zA-Z0-9]/.test(password)) score++;
    
    const levels = [
        { score: 0, label: '', color: '' },
        { score: 1, label: 'Very Weak', color: 'bg-red-500' },
        { score: 2, label: 'Weak', color: 'bg-orange-500' },
        { score: 3, label: 'Fair', color: 'bg-yellow-500' },
        { score: 4, label: 'Good', color: 'bg-blue-500' },
        { score: 5, label: 'Strong', color: 'bg-green-500' },
    ];
    
    return { score, ...levels[score] };
});

// Validation for each step
const stepErrors = computed(() => {
    const errors = {};
    
    // Step 0: Personal
    if (currentStep.value === 0) {
        if (!formData.value.first_name) errors.first_name = 'First name is required';
        if (!formData.value.last_name) errors.last_name = 'Last name is required';
        if (!formData.value.username) errors.username = 'Username is required';
        if (formData.value.username && formData.value.username.length < 3) errors.username = 'Username must be at least 3 characters';
    }
    
    // Step 1: Contact
    if (currentStep.value === 1) {
        if (!formData.value.email) errors.email = 'Email is required';
        if (formData.value.email && !isValidEmail(formData.value.email)) errors.email = 'Invalid email format';
        if (!formData.value.phone) errors.phone = 'Phone number is required';
        if (!formData.value.address) errors.address = 'Address is required';
        if (!formData.value.city) errors.city = 'City is required';
        if (!formData.value.country) errors.country = 'Country is required';
    }
    
    // Step 2: Account
    if (currentStep.value === 2) {
        if (!formData.value.account_type) errors.account_type = 'Please select an account type';
    }
    
    // Step 3: Security
    if (currentStep.value === 3) {
        if (!formData.value.password) errors.password = 'Password is required';
        if (formData.value.password && formData.value.password.length < 8) errors.password = 'Password must be at least 8 characters';
        if (formData.value.password !== formData.value.password_confirmation) errors.password_confirmation = 'Passwords do not match';
        if (!formData.value.transaction_pin) errors.transaction_pin = 'Transaction PIN is required';
        if (formData.value.transaction_pin && formData.value.transaction_pin.length !== 6) errors.transaction_pin = 'PIN must be 6 digits';
        if (formData.value.transaction_pin !== formData.value.transaction_pin_confirmation) errors.transaction_pin_confirmation = 'PINs do not match';
        if (!formData.value.agree_terms) errors.agree_terms = 'You must agree to the terms';
        if (!formData.value.agree_privacy) errors.agree_privacy = 'You must agree to the privacy policy';
    }
    
    return errors;
});

const canProceed = computed(() => {
    return Object.keys(stepErrors.value).length === 0;
});

// Helper functions
const isValidEmail = (email) => {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
};

const formatPinInput = (value) => {
    return value.replace(/\D/g, '').slice(0, 6);
};

const nextStep = () => {
    if (canProceed.value && currentStep.value < 3) {
        currentStep.value++;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const prevStep = () => {
    if (currentStep.value > 0) {
        currentStep.value--;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const submitForm = async () => {
    if (!canProceed.value) {
        toast.error('Please fill all required fields correctly', 'Validation Error');
        return;
    }
    
    isProcessing.value = true;
    
    try {
        // Get reCAPTCHA token if enabled
        let captchaToken = '';
        if (props.recaptcha?.enabled && recaptchaRef.value) {
            try {
                const tokenPromise = Promise.resolve(recaptchaRef.value.getToken());
                captchaToken = await Promise.race([
                    tokenPromise,
                    new Promise((_, reject) => 
                        setTimeout(() => reject(new Error('reCAPTCHA timeout')), 10000)
                    )
                ]);
                
                if (props.recaptcha?.version === 'v2' && !captchaToken) {
                    toast.error('Please complete the reCAPTCHA verification checkbox.', 'Verification Required');
                    isProcessing.value = false;
                    return;
                }
            } catch (captchaError) {
                console.error('reCAPTCHA error:', captchaError);
                toast.error(captchaError.message || 'Please complete the security verification.', 'Verification Failed');
                isProcessing.value = false;
                return;
            }
        }
        
        router.post(route('register'), {
            ...formData.value,
            recaptcha_token: captchaToken,
        }, {
            onSuccess: () => {
                toast.success(`Welcome to ${siteName.value}!`, 'Registration Successful');
            },
            onError: (errors) => {
                if (props.recaptcha?.enabled && props.recaptcha?.version === 'v2' && recaptchaRef.value) {
                    recaptchaRef.value.reset();
                }
                
                const errorMessage = errors.recaptcha_token || errors.email || errors.username || 'Please check the form for errors';
                toast.error(errorMessage, 'Registration Failed');
                
                if (errors.first_name || errors.last_name || errors.username) currentStep.value = 0;
                else if (errors.email || errors.phone || errors.address) currentStep.value = 1;
                else if (errors.account_type) currentStep.value = 2;
                else if (errors.password || errors.transaction_pin || errors.recaptcha_token) currentStep.value = 3;
            },
            onFinish: () => {
                isProcessing.value = false;
            }
        });
    } catch (error) {
        console.error('Form submission error:', error);
        toast.error('An unexpected error occurred. Please try again.', 'Submission Error');
        isProcessing.value = false;
    }
};

// Watch for server-side errors
watch(() => props.errors, (newErrors) => {
    if (Object.keys(newErrors).length > 0) {
        const firstError = Object.values(newErrors)[0];
        toast.error(firstError, 'Error');
    }
}, { deep: true });

// Icon mapping (for check icon and account type icons)
const getIconPath = (icon) => {
    const icons = {
        'check': 'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z',
        'money-bill': 'M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z',
        'credit-card': 'M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm2 0v2h12V5H4zm0 4v6h12v-6H4z',
        'briefcase': 'M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z',
        'star': 'M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z',
        'wallet': 'M3 6a3 3 0 013-3h12a3 3 0 013 3v12a3 3 0 01-3 3H6a3 3 0 01-3-3V6zm3-1a1 1 0 00-1 1v2h14V6a1 1 0 00-1-1H6zM5 11v7a1 1 0 001 1h12a1 1 0 001-1v-7H5zm10 3a1 1 0 100-2 1 1 0 000 2z',
    };
    return icons[icon] || icons['wallet'];
};
</script>

<template>
    <SeoHead title="Create Account" :description="siteTagline" />
    <PagePreloader :min-load-time="1200" @complete="handlePreloaderComplete" />
    
    <!-- Animated Gradient Background -->
    <Transition
        enter-active-class="transition-all duration-500 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
    >
    <div v-show="showContent" class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-900 dark:via-indigo-950 dark:to-purple-950">
        <div class="flex min-h-screen">
            
            <!-- Left Side - Branding Panel with BG Image (Hidden on mobile) -->
            <div class="hidden lg:block lg:w-2/5 xl:w-5/12 p-3">
                <div class="relative h-full w-full rounded-3xl overflow-hidden shadow-2xl">
                    <!-- Background Image -->
                    <div 
                        class="absolute inset-0 bg-cover bg-center"
                        style="background-image: url('/images/register-bg.jpg');"
                    ></div>
                    
                    <!-- Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-br from-primary-900/90 via-primary-800/85 to-indigo-900/90"></div>
                    
                    <!-- Animated Shapes -->
                    <div class="absolute inset-0 overflow-hidden">
                        <div class="floating-shape shape-1"></div>
                        <div class="floating-shape shape-2"></div>
                        <div class="floating-shape shape-3"></div>
                    </div>
                    
                    <!-- Content -->
                    <div class="relative z-10 flex flex-col items-center justify-center h-full p-10 text-center">
                        <!-- Logo -->
                        <div class="mb-8">
                            <img
                                v-if="logoLight"
                                :src="logoLight"
                                :alt="siteName"
                                class="h-16 w-auto drop-shadow-xl"
                            />
                            <img
                                v-else-if="logoDark"
                                :src="logoDark"
                                :alt="siteName"
                                class="h-16 w-auto drop-shadow-xl"
                            />
                            <div v-else class="flex items-center justify-center w-20 h-20 bg-white/10 backdrop-blur-sm rounded-2xl border border-white/20 shadow-xl">
                                <span class="text-4xl font-bold text-white">{{ siteInitial }}</span>
                            </div>
                        </div>
                        
                        <!-- Title -->
                        <h1 class="mb-2 text-4xl font-bold text-white xl:text-5xl">
                            {{ siteName }}
                        </h1>
                        <p class="text-lg text-white/80">
                            {{ siteTagline }}
                        </p>
                        
                        <!-- Animated Quote Section -->
                        <div class="mt-12 mb-8 min-h-[120px] flex items-center justify-center">
                            <div 
                                class="quote-container transition-all duration-500"
                                :class="quoteVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                            >
                                <div class="relative">
                                    <i class="pi pi-quote-left absolute -top-4 -left-2 text-3xl text-white/20"></i>
                                    <p class="text-xl xl:text-2xl font-light text-white italic px-6">
                                        {{ currentQuote.text }}
                                    </p>
                                    <i class="pi pi-quote-right absolute -bottom-4 -right-2 text-3xl text-white/20"></i>
                                </div>
                                <p class="mt-4 text-sm text-white/60 font-medium">
                                    — {{ currentQuote.author }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Quote Indicators -->
                        <div class="flex gap-2 mb-8">
                            <span 
                                v-for="(_, index) in quotes" 
                                :key="index"
                                class="w-2 h-2 rounded-full transition-all duration-300"
                                :class="currentQuoteIndex === index ? 'bg-white w-6' : 'bg-white/40'"
                            ></span>
                        </div>
                        
                        <!-- Features -->
                        <div class="space-y-3 text-left w-full max-w-xs">
                            <div class="flex items-center gap-3 text-white/90 bg-white/10 backdrop-blur-sm rounded-xl px-4 py-3">
                                <div class="w-8 h-8 flex items-center justify-center bg-green-500/20 rounded-lg">
                                    <i class="pi pi-shield text-green-400"></i>
                                </div>
                                <span class="text-sm">Bank-grade Security</span>
                            </div>
                            <div class="flex items-center gap-3 text-white/90 bg-white/10 backdrop-blur-sm rounded-xl px-4 py-3">
                                <div class="w-8 h-8 flex items-center justify-center bg-blue-500/20 rounded-lg">
                                    <i class="pi pi-bolt text-blue-400"></i>
                                </div>
                                <span class="text-sm">Instant Transfers</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Form Panel -->
            <div class="flex flex-col flex-1 lg:w-3/5 xl:w-7/12">
                
                <!-- Mobile Header (Only visible on mobile) -->
                <div class="lg:hidden py-5 px-6 text-center bg-white/10 backdrop-blur-sm rounded-2xl mb-4">
                    <div class="flex items-center justify-center">
                        <img
                            v-if="logoLight"
                            :src="logoLight"
                            :alt="siteName"
                            class="h-10 w-auto"
                        />
                        <img
                            v-else-if="logoDark"
                            :src="logoDark"
                            :alt="siteName"
                            class="h-10 w-auto"
                        />
                        <div v-else class="flex items-center justify-center w-10 h-10 bg-primary-600 rounded-xl">
                            <span class="text-xl font-bold text-white">{{ siteInitial }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Form Container -->
                <div class="flex-1 flex items-center justify-center p-4 md:p-8 lg:p-10">
                    <div class="w-full max-w-2xl">
                        
                        <!-- Loading Skeleton -->
                        <div v-if="isPageLoading" class="space-y-6">
                            <div>
                                <Skeleton height="2.5rem" class="mb-2" />
                                <Skeleton height="1.25rem" width="10rem" />
                            </div>
                            <Skeleton height="3rem" />
                            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl p-8 space-y-6">
                                <Skeleton height="2rem" width="60%" class="mb-4" />
                                <Skeleton height="3rem" class="mb-4" />
                                <Skeleton height="3rem" class="mb-4" />
                                <Skeleton height="3rem" class="mb-4" />
                                <div class="flex justify-end gap-4">
                                    <Skeleton height="2.5rem" width="6rem" />
                                    <Skeleton height="2.5rem" width="6rem" />
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actual Form -->
                        <div v-else class="animate-fade-in">
                            
                            <!-- Form Header -->
                            <div class="mb-8 text-center lg:text-left">
                                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">
                                    Create Account
                                </h2>
                                <p class="mt-2 text-gray-600 dark:text-gray-400">
                                    Step <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ currentStep + 1 }}</span> of <span class="font-semibold">4</span>
                                </p>
                            </div>
                            
                            <!-- Step Indicators - Mobile Responsive -->
                            <div class="mb-8">
                                <!-- Mobile: Compact step indicator (xs to sm) -->
                                <div class="flex sm:hidden items-center justify-center gap-2">
                                    <template v-for="(step, index) in steps" :key="'mobile-'+index">
                                        <div 
                                            class="w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300"
                                            :class="[
                                                currentStep > index 
                                                    ? 'bg-green-500 text-white' 
                                                    : currentStep === index 
                                                        ? 'bg-indigo-600 text-white ring-2 ring-indigo-300 dark:ring-indigo-700 scale-110' 
                                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400'
                                            ]"
                                        >
                                            <i v-if="currentStep > index" class="pi pi-check text-sm"></i>
                                            <i v-else :class="['pi', step.icon, 'text-sm']"></i>
                                        </div>
                                        <!-- Mobile connector -->
                                        <div 
                                            v-if="index < steps.length - 1"
                                            class="w-6 h-1 rounded-full transition-all duration-300"
                                            :class="[
                                                currentStep > index 
                                                    ? 'bg-green-500' 
                                                    : 'bg-gray-200 dark:bg-gray-700'
                                            ]"
                                        ></div>
                                    </template>
                                </div>
                                
                                <!-- Tablet and Desktop: Full step indicator -->
                                <div class="hidden sm:flex items-center justify-between">
                                    <template v-for="(step, index) in steps" :key="'desktop-'+index">
                                        <!-- Step Item -->
                                        <div class="flex flex-col items-center">
                                            <div 
                                                class="relative w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center font-semibold transition-all duration-300"
                                                :class="[
                                                    currentStep > index 
                                                        ? 'bg-green-500 text-white ring-4 ring-green-100 dark:ring-green-900/50' 
                                                        : currentStep === index 
                                                            ? 'bg-indigo-600 text-white ring-4 ring-indigo-100 dark:ring-indigo-900/50 scale-110' 
                                                            : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400'
                                                ]"
                                            >
                                                <i v-if="currentStep > index" class="pi pi-check text-lg md:text-xl"></i>
                                                <i v-else :class="['pi', step.icon, 'text-lg md:text-xl']"></i>
                                            </div>
                                            <span 
                                                class="mt-2 text-xs md:text-sm font-medium transition-colors duration-300 whitespace-nowrap"
                                                :class="[
                                                    currentStep >= index 
                                                        ? 'text-gray-900 dark:text-white' 
                                                        : 'text-gray-500 dark:text-gray-400'
                                                ]"
                                            >
                                                <span class="hidden md:inline">{{ step.label }}</span>
                                                <span class="md:hidden">{{ step.shortLabel }}</span>
                                            </span>
                                        </div>
                                        
                                        <!-- Connector Line -->
                                        <div 
                                            v-if="index < steps.length - 1"
                                            class="flex-1 h-1 mx-2 md:mx-4 rounded-full transition-all duration-300"
                                            :class="[
                                                currentStep > index 
                                                    ? 'bg-green-500' 
                                                    : 'bg-gray-200 dark:bg-gray-700'
                                            ]"
                                        ></div>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Form Card -->
                            <div class="p-6 md:p-8 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700">
                                <form @submit.prevent="currentStep === 3 ? submitForm() : nextStep()">
                                    
                                    <!-- Step 0: Personal Information -->
                                    <div v-show="currentStep === 0" class="space-y-6 animate-slide-in">
                                        <div>
                                            <h3 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                                Personal Information
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Tell us about yourself
                                            </p>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                            <!-- First Name -->
                                            <div>
                                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    First Name <span class="text-red-500">*</span>
                                                </label>
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path :d="getIconPath('user')" />
                                                        </svg>
                                                    </div>
                                                    <input
                                                        v-model="formData.first_name"
                                                        type="text"
                                                        placeholder="John"
                                                        class="block w-full pl-10 pr-3 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                                        :class="stepErrors.first_name || errors.first_name 
                                                            ? 'border-red-500 bg-red-50 dark:bg-red-900/20' 
                                                            : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 hover:border-indigo-400'"
                                                    />
                                                </div>
                                                <p v-if="stepErrors.first_name || errors.first_name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                                    {{ stepErrors.first_name || errors.first_name }}
                                                </p>
                                            </div>
                                            
                                            <!-- Last Name -->
                                            <div>
                                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Last Name <span class="text-red-500">*</span>
                                                </label>
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path :d="getIconPath('user')" />
                                                        </svg>
                                                    </div>
                                                    <input
                                                        v-model="formData.last_name"
                                                        type="text"
                                                        placeholder="Doe"
                                                        class="block w-full pl-10 pr-3 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                                        :class="stepErrors.last_name || errors.last_name 
                                                            ? 'border-red-500 bg-red-50 dark:bg-red-900/20' 
                                                            : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 hover:border-indigo-400'"
                                                    />
                                                </div>
                                                <p v-if="stepErrors.last_name || errors.last_name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                                    {{ stepErrors.last_name || errors.last_name }}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <!-- Middle Name -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Middle Name <span class="text-sm text-gray-500">(Optional)</span>
                                            </label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path :d="getIconPath('user')" />
                                                    </svg>
                                                </div>
                                                <input
                                                    v-model="formData.middle_name"
                                                    type="text"
                                                    placeholder="Michael"
                                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent hover:border-indigo-400 transition-all duration-200"
                                                />
                                            </div>
                                        </div>
                                        
                                        <!-- Username -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Username <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-400 font-medium">@</span>
                                                </div>
                                                <input
                                                    v-model="formData.username"
                                                    type="text"
                                                    placeholder="johndoe"
                                                    class="block w-full pl-10 pr-3 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                                    :class="stepErrors.username || errors.username 
                                                        ? 'border-red-500 bg-red-50 dark:bg-red-900/20' 
                                                        : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 hover:border-indigo-400'"
                                                />
                                            </div>
                                            <p v-if="stepErrors.username || errors.username" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                                {{ stepErrors.username || errors.username }}
                                            </p>
                                            <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                Minimum 3 characters, letters and numbers only
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Step 1: Contact Information -->
                                    <div v-show="currentStep === 1" class="space-y-6 animate-slide-in">
                                        <div>
                                            <h3 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                                Contact Information
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                How can we reach you?
                                            </p>
                                        </div>
                                        
                                        <!-- Email -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Email Address <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                                <input
                                                    v-model="formData.email"
                                                    type="email"
                                                    placeholder="john@example.com"
                                                    class="block w-full pl-10 pr-3 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                                    :class="stepErrors.email || errors.email 
                                                        ? 'border-red-500 bg-red-50 dark:bg-red-900/20' 
                                                        : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 hover:border-indigo-400'"
                                                />
                                            </div>
                                            <p v-if="stepErrors.email || errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                                {{ stepErrors.email || errors.email }}
                                            </p>
                                        </div>
                                        
                                        <!-- Phone -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Phone Number <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                    </svg>
                                                </div>
                                                <input
                                                    v-model="formData.phone"
                                                    type="tel"
                                                    placeholder="+1 234 567 8900"
                                                    class="block w-full pl-10 pr-3 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                                    :class="stepErrors.phone || errors.phone 
                                                        ? 'border-red-500 bg-red-50 dark:bg-red-900/20' 
                                                        : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 hover:border-indigo-400'"
                                                />
                                            </div>
                                            <p v-if="stepErrors.phone || errors.phone" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                                {{ stepErrors.phone || errors.phone }}
                                            </p>
                                        </div>
                                        
                                        <!-- Address -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Street Address <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                </div>
                                                <input
                                                    v-model="formData.address"
                                                    type="text"
                                                    placeholder="123 Main Street"
                                                    class="block w-full pl-10 pr-3 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                                    :class="stepErrors.address || errors.address 
                                                        ? 'border-red-500 bg-red-50 dark:bg-red-900/20' 
                                                        : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 hover:border-indigo-400'"
                                                />
                                            </div>
                                            <p v-if="stepErrors.address || errors.address" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                                {{ stepErrors.address || errors.address }}
                                            </p>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4">
                                            <!-- City -->
                                            <div>
                                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    City <span class="text-red-500">*</span>
                                                </label>
                                                <input
                                                    v-model="formData.city"
                                                    type="text"
                                                    placeholder="New York"
                                                    class="block w-full px-3 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                                    :class="stepErrors.city || errors.city 
                                                        ? 'border-red-500 bg-red-50 dark:bg-red-900/20' 
                                                        : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 hover:border-indigo-400'"
                                                />
                                                <p v-if="stepErrors.city || errors.city" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                                    {{ stepErrors.city || errors.city }}
                                                </p>
                                            </div>
                                            
                                            <!-- State -->
                                            <div>
                                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    State <span class="text-sm text-gray-500">(Optional)</span>
                                                </label>
                                                <input
                                                    v-model="formData.state"
                                                    type="text"
                                                    placeholder="NY"
                                                    class="block w-full px-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent hover:border-indigo-400 transition-all duration-200"
                                                />
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4">
                                            <!-- Postal Code -->
                                            <div>
                                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Postal Code
                                                </label>
                                                <input
                                                    v-model="formData.postal_code"
                                                    type="text"
                                                    placeholder="10001"
                                                    class="block w-full px-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent hover:border-indigo-400 transition-all duration-200"
                                                />
                                            </div>
                                            
                                            <!-- Country -->
                                            <div>
                                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Country <span class="text-red-500">*</span>
                                                </label>
                                                <select
                                                    v-model="formData.country"
                                                    class="block w-full px-3 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 appearance-none bg-right bg-no-repeat"
                                                    :class="stepErrors.country || errors.country 
                                                        ? 'border-red-500 bg-red-50 dark:bg-red-900/20' 
                                                        : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 hover:border-indigo-400'"
                                                    style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%206l5%205%205-5%202%201-7%207-7-7%202-1z%22%20fill%3D%22%239ca3af%22%2F%3E%3C%2Fsvg%3E'); background-position: right 0.75rem center; background-size: 1.25rem 1.25rem; padding-right: 2.5rem;"
                                                >
                                                    <option value="">Select Country</option>
                                                    <option 
                                                        v-for="country in countryOptions" 
                                                        :key="country.value" 
                                                        :value="country.value"
                                                    >
                                                        {{ country.label }}
                                                    </option>
                                                </select>
                                                <p v-if="stepErrors.country || errors.country" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                                    {{ stepErrors.country || errors.country }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Step 2: Account Setup -->
                                    <div v-show="currentStep === 2" class="space-y-6 animate-slide-in">
                                        <div>
                                            <h3 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                                Account Setup
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Choose your account preferences
                                            </p>
                                        </div>
                                        
                                        <!-- Account Type -->
                                        <div>
                                            <label class="block mb-4 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Account Type <span class="text-red-500">*</span>
                                            </label>
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                                <div
                                                    v-for="type in accountTypeOptions"
                                                    :key="type.value || type.id"
                                                    @click="formData.account_type = type.value || type.id"
                                                    class="relative p-5 border-2 rounded-xl cursor-pointer transition-all duration-300 hover:shadow-lg group"
                                                    :class="[
                                                        formData.account_type === (type.value || type.id)
                                                            ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 shadow-md ring-2 ring-indigo-200 dark:ring-indigo-800'
                                                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 hover:border-indigo-300'
                                                    ]"
                                                >
                                                    <!-- Check mark -->
                                                    <div 
                                                        v-if="formData.account_type === (type.value || type.id)"
                                                        class="absolute top-3 right-3 w-6 h-6 bg-indigo-500 rounded-full flex items-center justify-center animate-scale-in"
                                                    >
                                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path :d="getIconPath('check')" />
                                                        </svg>
                                                    </div>
                                                    
                                                    <div class="flex flex-col items-center text-center">
                                                        <div 
                                                            class="w-14 h-14 rounded-full flex items-center justify-center mb-3 transition-all duration-300"
                                                            :class="[
                                                                formData.account_type === (type.value || type.id)
                                                                    ? 'bg-indigo-500 text-white scale-110'
                                                                    : 'bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-300 group-hover:bg-indigo-100 group-hover:text-indigo-600'
                                                            ]"
                                                        >
                                                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 20 20">
                                                                <path :d="getIconPath(type.icon || 'wallet')" />
                                                            </svg>
                                                        </div>
                                                        <h4 class="font-semibold text-gray-900 dark:text-white mb-1">
                                                            {{ type.label || type.name }}
                                                        </h4>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ type.description || 'Account type' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <p v-if="stepErrors.account_type || errors.account_type" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                                {{ stepErrors.account_type || errors.account_type }}
                                            </p>
                                        </div>
                                        
                                        <!-- Currency -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Preferred Currency
                                            </label>
                                            <select
                                                v-model="formData.currency"
                                                class="block w-full px-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent hover:border-indigo-400 transition-all duration-200 appearance-none bg-right bg-no-repeat"
                                                style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%206l5%205%205-5%202%201-7%207-7-7%202-1z%22%20fill%3D%22%239ca3af%22%2F%3E%3C%2Fsvg%3E'); background-position: right 0.75rem center; background-size: 1.25rem 1.25rem; padding-right: 2.5rem;"
                                            >
                                                <option 
                                                    v-for="currency in currencyOptions" 
                                                    :key="currency.value" 
                                                    :value="currency.value"
                                                >
                                                    {{ currency.label }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- Step 3: Security Setup -->
                                    <div v-show="currentStep === 3" class="space-y-6 animate-slide-in">
                                        <div>
                                            <h3 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                                Security Setup
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Secure your account
                                            </p>
                                        </div>
                                        
                                        <!-- Password -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Password <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <input
                                                    v-model="formData.password"
                                                    :type="showPassword ? 'text' : 'password'"
                                                    placeholder="Create a strong password"
                                                    class="block w-full px-3 pr-10 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                                    :class="stepErrors.password || errors.password 
                                                        ? 'border-red-500 bg-red-50 dark:bg-red-900/20' 
                                                        : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 hover:border-indigo-400'"
                                                />
                                                <button
                                                    type="button"
                                                    @click="showPassword = !showPassword"
                                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                                >
                                                    <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                    </svg>
                                                </button>
                                            </div>
                                            
                                            <!-- Password Strength Meter -->
                                            <div v-if="formData.password" class="mt-2">
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Password Strength:</span>
                                                    <span class="text-xs font-semibold" :class="[
                                                        passwordStrength.score <= 2 ? 'text-red-600' : 
                                                        passwordStrength.score === 3 ? 'text-yellow-600' : 
                                                        passwordStrength.score === 4 ? 'text-blue-600' : 'text-green-600'
                                                    ]">
                                                        {{ passwordStrength.label }}
                                                    </span>
                                                </div>
                                                <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                                    <div 
                                                        class="h-full transition-all duration-300 rounded-full"
                                                        :class="passwordStrength.color"
                                                        :style="{ width: `${passwordStrength.score * 20}%` }"
                                                    ></div>
                                                </div>
                                            </div>
                                            
                                            <p v-if="stepErrors.password || errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                                {{ stepErrors.password || errors.password }}
                                            </p>
                                            <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                At least 8 characters with uppercase, lowercase, and numbers
                                            </p>
                                        </div>
                                        
                                        <!-- Confirm Password -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Confirm Password <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <input
                                                    v-model="formData.password_confirmation"
                                                    :type="showPasswordConfirm ? 'text' : 'password'"
                                                    placeholder="Re-enter your password"
                                                    class="block w-full px-3 pr-10 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                                    :class="stepErrors.password_confirmation || errors.password_confirmation 
                                                        ? 'border-red-500 bg-red-50 dark:bg-red-900/20' 
                                                        : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 hover:border-indigo-400'"
                                                />
                                                <button
                                                    type="button"
                                                    @click="showPasswordConfirm = !showPasswordConfirm"
                                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                                >
                                                    <svg v-if="showPasswordConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <p v-if="stepErrors.password_confirmation || errors.password_confirmation" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                                {{ stepErrors.password_confirmation || errors.password_confirmation }}
                                            </p>
                                        </div>
                                        
                                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6"></div>
                                        
                                        <!-- Transaction PIN -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Transaction PIN (6 digits) <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                    </svg>
                                                </div>
                                                <input
                                                    v-model="formData.transaction_pin"
                                                    type="password"
                                                    inputmode="numeric"
                                                    maxlength="6"
                                                    placeholder="Enter 6-digit PIN"
                                                    @input="formData.transaction_pin = formatPinInput(formData.transaction_pin)"
                                                    class="block w-full pl-10 pr-3 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 tracking-widest text-center font-semibold"
                                                    :class="stepErrors.transaction_pin || errors.transaction_pin 
                                                        ? 'border-red-500 bg-red-50 dark:bg-red-900/20' 
                                                        : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 hover:border-indigo-400'"
                                                />
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Used to authorize transactions</p>
                                            <p v-if="stepErrors.transaction_pin || errors.transaction_pin" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                                {{ stepErrors.transaction_pin || errors.transaction_pin }}
                                            </p>
                                        </div>
                                        
                                        <!-- Confirm PIN -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Confirm PIN <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                    </svg>
                                                </div>
                                                <input
                                                    v-model="formData.transaction_pin_confirmation"
                                                    type="password"
                                                    inputmode="numeric"
                                                    maxlength="6"
                                                    placeholder="Re-enter your PIN"
                                                    @input="formData.transaction_pin_confirmation = formatPinInput(formData.transaction_pin_confirmation)"
                                                    class="block w-full pl-10 pr-3 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 tracking-widest text-center font-semibold"
                                                    :class="stepErrors.transaction_pin_confirmation || errors.transaction_pin_confirmation 
                                                        ? 'border-red-500 bg-red-50 dark:bg-red-900/20' 
                                                        : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 hover:border-indigo-400'"
                                                />
                                            </div>
                                            <p v-if="stepErrors.transaction_pin_confirmation || errors.transaction_pin_confirmation" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                                {{ stepErrors.transaction_pin_confirmation || errors.transaction_pin_confirmation }}
                                            </p>
                                        </div>
                                        
                                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6"></div>
                                        
                                        <!-- Terms & Privacy -->
                                        <div class="space-y-4">
                                            <div class="flex items-start gap-3">
                                                <input
                                                    v-model="formData.agree_terms"
                                                    type="checkbox"
                                                    id="terms"
                                                    class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 focus:ring-2 cursor-pointer"
                                                    :class="stepErrors.agree_terms ? 'border-red-500' : ''"
                                                />
                                                <label for="terms" class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer flex-1">
                                                    I agree to the <button type="button" @click="showTermsModal = true" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">Terms and Conditions</button>
                                                </label>
                                            </div>
                                            <p v-if="stepErrors.agree_terms" class="ml-8 text-sm text-red-600 dark:text-red-400">
                                                {{ stepErrors.agree_terms }}
                                            </p>
                                            
                                            <div class="flex items-start gap-3">
                                                <input
                                                    v-model="formData.agree_privacy"
                                                    type="checkbox"
                                                    id="privacy"
                                                    class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 focus:ring-2 cursor-pointer"
                                                    :class="stepErrors.agree_privacy ? 'border-red-500' : ''"
                                                />
                                                <label for="privacy" class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer flex-1">
                                                    I agree to the <button type="button" @click="showPrivacyModal = true" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">Privacy Policy</button>
                                                </label>
                                            </div>
                                            <p v-if="stepErrors.agree_privacy" class="ml-8 text-sm text-red-600 dark:text-red-400">
                                                {{ stepErrors.agree_privacy }}
                                            </p>
                                        </div>
                                        
                                        <!-- reCAPTCHA -->
                                        <div v-if="recaptcha?.enabled" class="flex justify-center pt-4">
                                            <ReCaptcha
                                                ref="recaptchaRef"
                                                :site-key="recaptcha.siteKey"
                                                :version="recaptcha.version"
                                                theme="light"
                                                action="register"
                                                @verify="(token) => recaptchaToken = token"
                                                @expire="() => recaptchaToken = ''"
                                            />
                                        </div>
                                        <p v-if="errors.recaptcha_token" class="text-sm text-red-600 dark:text-red-400 text-center">
                                            {{ errors.recaptcha_token }}
                                        </p>
                                    </div>
                                    
                                    <!-- Navigation Buttons -->
                                    <div class="flex items-center justify-between gap-4 pt-8 mt-8 border-t border-gray-200 dark:border-gray-700">
                                        <button
                                            v-if="currentStep > 0"
                                            type="button"
                                            @click="prevStep"
                                            :disabled="isProcessing"
                                            class="px-6 py-3 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-semibold hover:bg-gray-50 dark:hover:bg-gray-600 focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center gap-2"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                            </svg>
                                            Back
                                        </button>
                                        <div v-else></div>
                                        
                                        <button
                                            v-if="currentStep < 3"
                                            type="submit"
                                            :disabled="!canProceed"
                                            class="px-8 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center gap-2 shadow-lg hover:shadow-xl"
                                        >
                                            Next
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <button
                                            v-else
                                            type="submit"
                                            :disabled="!canProceed || isProcessing"
                                            class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold hover:from-indigo-700 hover:to-purple-700 focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center gap-2 shadow-lg hover:shadow-xl relative overflow-hidden"
                                        >
                                            <span v-if="isProcessing" class="flex items-center gap-2">
                                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Registering...
                                            </span>
                                            <span v-else class="flex items-center gap-2">
                                                Register
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path :d="getIconPath('check')" />
                                                </svg>
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Sign In Link -->
                            <div class="mt-6 text-center">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Already have an account?
                                    <Link href="/login" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 ml-1">
                                        Sign In
                                    </Link>
                                </p>
                            </div>
                            
                            <!-- Footer -->
                            <div class="mt-8 text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-500">
                                    © {{ new Date().getFullYear() }} {{ copyrightText }}. All Rights Reserved.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </Transition>

    <!-- Terms Modal -->
    <Teleport to="body">
        <div
            v-if="showTermsModal"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fade-in"
            @click.self="showTermsModal = false"
        >
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] flex flex-col animate-scale-in">
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Terms and Conditions</h3>
                    <button
                        @click="showTermsModal = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-6 prose prose-sm dark:prose-invert max-w-none">
                    <p class="text-gray-600 dark:text-gray-400 mb-4"><strong>Effective Date:</strong> January 1, 2026</p>
                    
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">1. Account Terms</h4>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        By opening an account with {{ siteName }}, you agree to maintain accurate and complete information about yourself. 
                        You must be at least 18 years of age to open an account.
                    </p>
                    
                    <!-- Add more terms content here as needed -->
                </div>
                <div class="flex justify-end gap-3 p-6 border-t border-gray-200 dark:border-gray-700">
                    <button
                        @click="showTermsModal = false"
                        class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                    >
                        Close
                    </button>
                    <button
                        @click="formData.agree_terms = true; showTermsModal = false"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path :d="getIconPath('check')" />
                        </svg>
                        I Accept
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
    
    <!-- Privacy Modal -->
    <Teleport to="body">
        <div
            v-if="showPrivacyModal"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fade-in"
            @click.self="showPrivacyModal = false"
        >
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] flex flex-col animate-scale-in">
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Privacy Policy</h3>
                    <button
                        @click="showPrivacyModal = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-6 prose prose-sm dark:prose-invert max-w-none">
                    <p class="text-gray-600 dark:text-gray-400 mb-4"><strong>Effective Date:</strong> January 1, 2026</p>
                    
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">1. Information We Collect</h4>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        We collect information you provide directly to us, including personal identification information.
                    </p>
                    
                    <!-- Add more privacy content here as needed -->
                </div>
                <div class="flex justify-end gap-3 p-6 border-t border-gray-200 dark:border-gray-700">
                    <button
                        @click="showPrivacyModal = false"
                        class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                    >
                        Close
                    </button>
                    <button
                        @click="formData.agree_privacy = true; showPrivacyModal = false"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path :d="getIconPath('check')" />
                        </svg>
                        I Accept
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slide-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes scale-in {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}

.animate-slide-in {
    animation: slide-in 0.4s ease-out;
}

.animate-scale-in {
    animation: scale-in 0.3s ease-out;
}

/* Pulse animation with delays */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.delay-1000 {
    animation-delay: 1s;
}

.delay-2000 {
    animation-delay: 2s;
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
    width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb {
    background: #475569;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}
</style>

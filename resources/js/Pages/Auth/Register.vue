<script setup>
/**
 * Register Page - Multi-Step Registration Form
 * Enhanced with animated gradient, side panel with BG image, animated quotes
 * 4-Step Wizard: Personal → Contact → Account → Security
 */
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import InputMask from 'primevue/inputmask';
import Dropdown from 'primevue/dropdown';
import Checkbox from 'primevue/checkbox';
import Divider from 'primevue/divider';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';

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
    }
});

const toast = useToast();

// Current step (0-3)
const currentStep = ref(0);

// Animated quotes for side panel
const quotes = [
    { text: "Banking made simple, secure, and smart.", author: "Finora Bank" },
    { text: "Your financial future starts here.", author: "Join 10,000+ users" },
    { text: "Experience the next generation of banking.", author: "Digital First" },
    { text: "Where security meets convenience.", author: "Trusted Worldwide" },
];
const currentQuoteIndex = ref(0);
const quoteVisible = ref(true);
let quoteInterval = null;

onMounted(() => {
    quoteInterval = setInterval(() => {
        quoteVisible.value = false;
        setTimeout(() => {
            currentQuoteIndex.value = (currentQuoteIndex.value + 1) % quotes.length;
            quoteVisible.value = true;
        }, 500);
    }, 5000);
});

onUnmounted(() => {
    if (quoteInterval) clearInterval(quoteInterval);
});

const currentQuote = computed(() => quotes[currentQuoteIndex.value]);

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

const isProcessing = ref(false);

// Step configuration
const steps = [
    { label: 'Personal', icon: 'pi pi-user' },
    { label: 'Contact', icon: 'pi pi-map-marker' },
    { label: 'Account', icon: 'pi pi-wallet' },
    { label: 'Security', icon: 'pi pi-shield' }
];

// Currency options (with fallback if prop is empty)
const currencyOptions = computed(() => {
    if (props.currencies && props.currencies.length > 0) {
        return props.currencies;
    }
    // Fallback currencies
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
    // Fallback countries
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
            icon: type.value === 'SAV' ? 'pi-money-bill' : 
                  type.value === 'CHK' ? 'pi-credit-card' : 
                  type.value === 'BUS' ? 'pi-briefcase' : 
                  type.value === 'PRM' ? 'pi-star' : 'pi-wallet',
        }));
    }
    // Fallback account types (using actual database codes)
    return [
        { id: 'SAV', value: 'SAV', name: 'Savings Account', label: 'Savings Account', icon: 'pi-money-bill', description: 'Earn interest on your balance' },
        { id: 'CHK', value: 'CHK', name: 'Checking Account', label: 'Checking Account', icon: 'pi-credit-card', description: 'Daily transactions' },
        { id: 'BUS', value: 'BUS', name: 'Business Account', label: 'Business Account', icon: 'pi-briefcase', description: 'For business needs' }
    ];
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

const submitForm = () => {
    if (!canProceed.value) {
        toast.add({
            severity: 'error',
            summary: 'Validation Error',
            detail: 'Please fill all required fields correctly',
            life: 4000
        });
        return;
    }
    
    isProcessing.value = true;
    
    router.post(route('register'), formData.value, {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Registration Successful',
                detail: 'Welcome to Finora Bank!',
                life: 5000
            });
        },
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Registration Failed',
                detail: errors.email || errors.username || 'Please check the form for errors',
                life: 5000
            });
            
            // Go to step with error
            if (errors.first_name || errors.last_name || errors.username) currentStep.value = 0;
            else if (errors.email || errors.phone || errors.address) currentStep.value = 1;
            else if (errors.account_type) currentStep.value = 2;
            else if (errors.password || errors.transaction_pin) currentStep.value = 3;
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

// Watch for server-side errors
watch(() => props.errors, (newErrors) => {
    if (Object.keys(newErrors).length > 0) {
        const firstError = Object.values(newErrors)[0];
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: firstError,
            life: 4000
        });
    }
}, { deep: true });
</script>

<template>
    <Head title="Create Account" />
    
    <!-- Animated Gradient Background -->
    <div class="min-h-screen animated-gradient">
        <div class="flex min-h-screen p-4 lg:p-6">
            
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
                            <div class="flex items-center justify-center w-20 h-20 bg-white/10 backdrop-blur-sm rounded-2xl border border-white/20 shadow-xl">
                                <span class="text-4xl font-bold text-white">F</span>
                            </div>
                        </div>
                        
                        <!-- Title -->
                        <h1 class="mb-2 text-4xl font-bold text-white xl:text-5xl">
                            Finora Bank
                        </h1>
                        <p class="text-lg text-white/80">
                            Secure Banking Platform
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
                            <div class="flex items-center gap-3 text-white/90 bg-white/10 backdrop-blur-sm rounded-xl px-4 py-3">
                                <div class="w-8 h-8 flex items-center justify-center bg-purple-500/20 rounded-lg">
                                    <i class="pi pi-globe text-purple-400"></i>
                                </div>
                                <span class="text-sm">Global Access</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Form Panel -->
            <div class="flex flex-col flex-1 lg:w-3/5 xl:w-7/12">
                
                <!-- Mobile Header (Only visible on mobile) -->
                <div class="lg:hidden py-5 px-6 text-center bg-white/10 backdrop-blur-sm rounded-2xl mb-4">
                    <div class="flex items-center justify-center gap-3 mb-2">
                        <div class="flex items-center justify-center w-10 h-10 bg-primary-600 rounded-xl">
                            <span class="text-xl font-bold text-white">F</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900 dark:text-white">Finora Bank</span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Secure Banking Platform</p>
                </div>
                
                <!-- Form Container -->
                <div class="flex-1 flex items-center justify-center p-4 md:p-8 lg:p-10">
                    <div class="w-full max-w-2xl">
                        
                        <!-- Form Header -->
                        <div class="mb-8 text-center lg:text-left">
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                                Create Account
                            </h2>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                Step <span class="font-semibold text-primary-600">{{ currentStep + 1 }}</span> of <span class="font-semibold">4</span>
                            </p>
                        </div>
                        
                        <!-- Step Indicators -->
                        <div class="mb-8">
                            <div class="flex items-center">
                                <template v-for="(step, index) in steps" :key="index">
                                    <!-- Step Item -->
                                    <div class="flex items-center">
                                        <div 
                                            class="flex items-center justify-center w-10 h-10 rounded-full font-semibold text-sm transition-all duration-300"
                                            :class="[
                                                currentStep > index 
                                                    ? 'bg-green-500 text-white' 
                                                    : currentStep === index 
                                                        ? 'bg-primary-600 text-white ring-4 ring-primary-200 dark:ring-primary-900' 
                                                        : 'bg-white dark:bg-gray-700 text-gray-500 shadow-sm'
                                            ]"
                                        >
                                            <i v-if="currentStep > index" class="pi pi-check text-sm"></i>
                                            <span v-else>{{ index + 1 }}</span>
                                        </div>
                                        <span 
                                            class="ml-2 text-sm font-medium hidden sm:inline"
                                            :class="[
                                                currentStep >= index 
                                                    ? 'text-gray-900 dark:text-white' 
                                                    : 'text-gray-500 dark:text-gray-400'
                                            ]"
                                        >
                                            {{ step.label }}
                                        </span>
                                    </div>
                                    
                                    <!-- Connector Line -->
                                    <div 
                                        v-if="index < steps.length - 1"
                                        class="flex-1 h-0.5 mx-2 sm:mx-4 transition-all duration-300"
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
                        <div class="p-6 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-xl md:p-8 border border-white/20">
                            <form @submit.prevent="currentStep === 3 ? submitForm() : nextStep()">
                                
                                <!-- Step 0: Personal Information -->
                                <div v-show="currentStep === 0">
                                    <div class="mb-6">
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                            Personal Information
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            Tell us about yourself
                                        </p>
                                    </div>
                                    
                                    <div class="space-y-5">
                                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                            <!-- First Name -->
                                            <div>
                                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    First Name <span class="text-red-500">*</span>
                                                </label>
                                                <IconField>
                                                    <InputIcon class="pi pi-user" />
                                                    <InputText
                                                        v-model="formData.first_name"
                                                        placeholder="John"
                                                        class="w-full"
                                                        :class="{ 'p-invalid': stepErrors.first_name || errors.first_name }"
                                                    />
                                                </IconField>
                                                <small v-if="stepErrors.first_name || errors.first_name" class="text-red-500">
                                                    {{ stepErrors.first_name || errors.first_name }}
                                                </small>
                                            </div>
                                            
                                            <!-- Last Name -->
                                            <div>
                                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Last Name <span class="text-red-500">*</span>
                                                </label>
                                                <IconField>
                                                    <InputIcon class="pi pi-user" />
                                                    <InputText
                                                        v-model="formData.last_name"
                                                        placeholder="Doe"
                                                        class="w-full"
                                                        :class="{ 'p-invalid': stepErrors.last_name || errors.last_name }"
                                                    />
                                                </IconField>
                                                <small v-if="stepErrors.last_name || errors.last_name" class="text-red-500">
                                                    {{ stepErrors.last_name || errors.last_name }}
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <!-- Middle Name -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Middle Name
                                            </label>
                                            <IconField>
                                                <InputIcon class="pi pi-user" />
                                                <InputText
                                                    v-model="formData.middle_name"
                                                    placeholder="(Optional)"
                                                    class="w-full"
                                                />
                                            </IconField>
                                        </div>
                                        
                                        <!-- Username -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Username <span class="text-red-500">*</span>
                                            </label>
                                            <IconField>
                                                <InputIcon class="pi pi-at" />
                                                <InputText
                                                    v-model="formData.username"
                                                    placeholder="johndoe"
                                                    class="w-full"
                                                    :class="{ 'p-invalid': stepErrors.username || errors.username }"
                                                />
                                            </IconField>
                                            <small v-if="stepErrors.username || errors.username" class="text-red-500">
                                                {{ stepErrors.username || errors.username }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Step 1: Contact Information -->
                                <div v-show="currentStep === 1">
                                    <div class="mb-6">
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                            Contact Information
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            How can we reach you?
                                        </p>
                                    </div>
                                    
                                    <div class="space-y-5">
                                        <!-- Email -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Email Address <span class="text-red-500">*</span>
                                            </label>
                                            <IconField>
                                                <InputIcon class="pi pi-envelope" />
                                                <InputText
                                                    v-model="formData.email"
                                                    type="email"
                                                    placeholder="john@example.com"
                                                    class="w-full"
                                                    :class="{ 'p-invalid': stepErrors.email || errors.email }"
                                                />
                                            </IconField>
                                            <small v-if="stepErrors.email || errors.email" class="text-red-500">
                                                {{ stepErrors.email || errors.email }}
                                            </small>
                                        </div>
                                        
                                        <!-- Phone -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Phone Number <span class="text-red-500">*</span>
                                            </label>
                                            <IconField>
                                                <InputIcon class="pi pi-phone" />
                                                <InputText
                                                    v-model="formData.phone"
                                                    type="tel"
                                                    placeholder="+1 234 567 8900"
                                                    class="w-full"
                                                    :class="{ 'p-invalid': stepErrors.phone || errors.phone }"
                                                />
                                            </IconField>
                                            <small v-if="stepErrors.phone || errors.phone" class="text-red-500">
                                                {{ stepErrors.phone || errors.phone }}
                                            </small>
                                        </div>
                                        
                                        <!-- Address -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Street Address <span class="text-red-500">*</span>
                                            </label>
                                            <IconField>
                                                <InputIcon class="pi pi-map-marker" />
                                                <InputText
                                                    v-model="formData.address"
                                                    placeholder="123 Main Street"
                                                    class="w-full"
                                                    :class="{ 'p-invalid': stepErrors.address || errors.address }"
                                                />
                                            </IconField>
                                            <small v-if="stepErrors.address || errors.address" class="text-red-500">
                                                {{ stepErrors.address || errors.address }}
                                            </small>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4">
                                            <!-- City -->
                                            <div>
                                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    City <span class="text-red-500">*</span>
                                                </label>
                                                <IconField>
                                                    <InputIcon class="pi pi-building" />
                                                    <InputText
                                                        v-model="formData.city"
                                                        placeholder="New York"
                                                        class="w-full"
                                                        :class="{ 'p-invalid': stepErrors.city || errors.city }"
                                                    />
                                                </IconField>
                                                <small v-if="stepErrors.city || errors.city" class="text-red-500">
                                                    {{ stepErrors.city || errors.city }}
                                                </small>
                                            </div>
                                            
                                            <!-- State -->
                                            <div>
                                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    State
                                                </label>
                                                <InputText
                                                    v-model="formData.state"
                                                    placeholder="NY"
                                                    class="w-full"
                                                />
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4">
                                            <!-- Postal Code -->
                                            <div>
                                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Postal Code
                                                </label>
                                                <InputText
                                                    v-model="formData.postal_code"
                                                    placeholder="10001"
                                                    class="w-full"
                                                />
                                            </div>
                                            
                                            <!-- Country -->
                                            <div>
                                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Country <span class="text-red-500">*</span>
                                                </label>
                                                <Dropdown
                                                    v-model="formData.country"
                                                    :options="countryOptions"
                                                    optionLabel="label"
                                                    optionValue="value"
                                                    placeholder="Select Country"
                                                    filter
                                                    filterPlaceholder="Search country..."
                                                    class="w-full"
                                                    :class="{ 'p-invalid': stepErrors.country || errors.country }"
                                                />
                                                <small v-if="stepErrors.country || errors.country" class="text-red-500">
                                                    {{ stepErrors.country || errors.country }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Step 2: Account Setup -->
                                <div v-show="currentStep === 2">
                                    <div class="mb-6">
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                            Account Setup
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            Choose your account preferences
                                        </p>
                                    </div>
                                    
                                    <div class="space-y-6">
                                        <!-- Account Type -->
                                        <div>
                                            <label class="block mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Account Type <span class="text-red-500">*</span>
                                            </label>
                                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                                <div
                                                    v-for="type in accountTypeOptions"
                                                    :key="type.value || type.id"
                                                    @click="formData.account_type = type.value || type.id"
                                                    class="relative p-4 border-2 rounded-xl cursor-pointer transition-all duration-200 hover:border-primary-300 hover:shadow-md"
                                                    :class="[
                                                        formData.account_type === (type.value || type.id)
                                                            ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 shadow-md'
                                                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800'
                                                    ]"
                                                >
                                                    <!-- Check mark -->
                                                    <div 
                                                        v-if="formData.account_type === (type.value || type.id)"
                                                        class="absolute top-2 right-2 w-5 h-5 bg-primary-500 rounded-full flex items-center justify-center"
                                                    >
                                                        <i class="pi pi-check text-white text-xs"></i>
                                                    </div>
                                                    
                                                    <div class="flex flex-col items-center text-center">
                                                        <div 
                                                            class="flex items-center justify-center w-12 h-12 mb-3 rounded-full transition-all"
                                                            :class="[
                                                                formData.account_type === (type.value || type.id)
                                                                    ? 'bg-primary-500 text-white'
                                                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-500'
                                                            ]"
                                                        >
                                                            <i :class="'pi ' + (type.icon || 'pi-wallet')" class="text-xl"></i>
                                                        </div>
                                                        <h4 class="font-semibold text-gray-900 dark:text-white">
                                                            {{ type.label || type.name }}
                                                        </h4>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                            {{ type.description || 'Account type' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <small v-if="stepErrors.account_type || errors.account_type" class="text-red-500 mt-2 block">
                                                {{ stepErrors.account_type || errors.account_type }}
                                            </small>
                                        </div>
                                        
                                        <!-- Currency -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Preferred Currency
                                            </label>
                                            <Dropdown
                                                v-model="formData.currency"
                                                :options="currencyOptions"
                                                optionLabel="label"
                                                optionValue="value"
                                                filter
                                                filterPlaceholder="Search currency..."
                                                class="w-full"
                                            />
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Step 3: Security Setup -->
                                <div v-show="currentStep === 3">
                                    <div class="mb-6">
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                            Security Setup
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            Secure your account
                                        </p>
                                    </div>
                                    
                                    <div class="space-y-5">
                                        <!-- Password -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Password <span class="text-red-500">*</span>
                                            </label>
                                            <Password
                                                v-model="formData.password"
                                                placeholder="Create a strong password"
                                                toggleMask
                                                :feedback="true"
                                                class="w-full"
                                                inputClass="w-full"
                                                :class="{ 'p-invalid': stepErrors.password || errors.password }"
                                            />
                                            <small v-if="stepErrors.password || errors.password" class="text-red-500">
                                                {{ stepErrors.password || errors.password }}
                                            </small>
                                        </div>
                                        
                                        <!-- Confirm Password -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Confirm Password <span class="text-red-500">*</span>
                                            </label>
                                            <Password
                                                v-model="formData.password_confirmation"
                                                placeholder="Re-enter your password"
                                                toggleMask
                                                :feedback="false"
                                                class="w-full"
                                                inputClass="w-full"
                                                :class="{ 'p-invalid': stepErrors.password_confirmation || errors.password_confirmation }"
                                            />
                                            <small v-if="stepErrors.password_confirmation || errors.password_confirmation" class="text-red-500">
                                                {{ stepErrors.password_confirmation || errors.password_confirmation }}
                                            </small>
                                        </div>
                                        
                                        <Divider />
                                        
                                        <!-- Transaction PIN -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Transaction PIN (6 digits) <span class="text-red-500">*</span>
                                            </label>
                                            <IconField>
                                                <InputIcon class="pi pi-lock" />
                                                <InputMask
                                                    v-model="formData.transaction_pin"
                                                    mask="999999"
                                                    placeholder="Enter 6-digit PIN"
                                                    class="w-full"
                                                    :class="{ 'p-invalid': stepErrors.transaction_pin || errors.transaction_pin }"
                                                />
                                            </IconField>
                                            <small class="text-gray-500">Used to authorize transactions</small>
                                            <small v-if="stepErrors.transaction_pin || errors.transaction_pin" class="block text-red-500">
                                                {{ stepErrors.transaction_pin || errors.transaction_pin }}
                                            </small>
                                        </div>
                                        
                                        <!-- Confirm PIN -->
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Confirm PIN <span class="text-red-500">*</span>
                                            </label>
                                            <IconField>
                                                <InputIcon class="pi pi-lock" />
                                                <InputMask
                                                    v-model="formData.transaction_pin_confirmation"
                                                    mask="999999"
                                                    placeholder="Re-enter your PIN"
                                                    class="w-full"
                                                    :class="{ 'p-invalid': stepErrors.transaction_pin_confirmation || errors.transaction_pin_confirmation }"
                                                />
                                            </IconField>
                                            <small v-if="stepErrors.transaction_pin_confirmation || errors.transaction_pin_confirmation" class="text-red-500">
                                                {{ stepErrors.transaction_pin_confirmation || errors.transaction_pin_confirmation }}
                                            </small>
                                        </div>
                                        
                                        <Divider />
                                        
                                        <!-- Terms & Privacy -->
                                        <div class="space-y-3">
                                            <div class="flex items-start gap-3">
                                                <Checkbox
                                                    v-model="formData.agree_terms"
                                                    :binary="true"
                                                    inputId="terms"
                                                    :class="{ 'p-invalid': stepErrors.agree_terms }"
                                                />
                                                <label for="terms" class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                                    I agree to the <Link href="/terms" class="text-primary-600 hover:underline">Terms and Conditions</Link>
                                                </label>
                                            </div>
                                            <small v-if="stepErrors.agree_terms" class="text-red-500 block ml-7">
                                                {{ stepErrors.agree_terms }}
                                            </small>
                                            
                                            <div class="flex items-start gap-3">
                                                <Checkbox
                                                    v-model="formData.agree_privacy"
                                                    :binary="true"
                                                    inputId="privacy"
                                                    :class="{ 'p-invalid': stepErrors.agree_privacy }"
                                                />
                                                <label for="privacy" class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                                    I agree to the <Link href="/privacy" class="text-primary-600 hover:underline">Privacy Policy</Link>
                                                </label>
                                            </div>
                                            <small v-if="stepErrors.agree_privacy" class="text-red-500 block ml-7">
                                                {{ stepErrors.agree_privacy }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Navigation Buttons -->
                                <div class="flex items-center justify-between gap-4 pt-6 mt-8 border-t border-gray-200 dark:border-gray-700">
                                    <Button
                                        v-if="currentStep > 0"
                                        type="button"
                                        label="Back"
                                        icon="pi pi-arrow-left"
                                        severity="secondary"
                                        outlined
                                        @click="prevStep"
                                        :disabled="isProcessing"
                                    />
                                    <div v-else></div>
                                    
                                    <Button
                                        v-if="currentStep < 3"
                                        type="submit"
                                        label="Next"
                                        icon="pi pi-arrow-right"
                                        iconPos="right"
                                        :disabled="!canProceed"
                                    />
                                    <Button
                                        v-else
                                        type="submit"
                                        label="Create Account"
                                        icon="pi pi-check"
                                        iconPos="right"
                                        :loading="isProcessing"
                                        :disabled="!canProceed"
                                    />
                                </div>
                            </form>
                        </div>
                        
                        <!-- Sign In Link -->
                        <div class="mt-6 text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Already have an account?
                                <Link href="/login" class="font-semibold text-primary-600 hover:text-primary-700 ml-1">
                                    Sign In
                                </Link>
                            </p>
                        </div>
                        
                        <!-- Footer -->
                        <div class="mt-8 text-center">
                            <p class="text-xs text-gray-500 dark:text-gray-500">
                                © {{ new Date().getFullYear() }} Finora Bank. All Rights Reserved.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Animated Gradient Background */
.animated-gradient {
    background: linear-gradient(-45deg, #e8f4f8, #d4e5f7, #c8d9f0, #e2e8f0, #dbeafe, #e0e7ff);
    background-size: 400% 400%;
    animation: gradientShift 15s ease infinite;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Dark mode gradient */
:root.dark .animated-gradient,
.dark .animated-gradient {
    background: linear-gradient(-45deg, #0f172a, #1e293b, #1e1b4b, #172554, #1e3a5f, #0c1929);
    background-size: 400% 400%;
    animation: gradientShift 15s ease infinite;
}

/* Floating Shapes */
.floating-shape {
    position: absolute;
    border-radius: 50%;
    opacity: 0.15;
}

.shape-1 {
    width: 300px;
    height: 300px;
    background: linear-gradient(135deg, rgba(255,255,255,0.3), rgba(255,255,255,0.05));
    top: 10%;
    left: -5%;
    animation: float1 20s ease-in-out infinite;
}

.shape-2 {
    width: 200px;
    height: 200px;
    background: linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0.05));
    bottom: 20%;
    right: -3%;
    animation: float2 15s ease-in-out infinite;
}

.shape-3 {
    width: 150px;
    height: 150px;
    background: linear-gradient(135deg, rgba(255,255,255,0.25), rgba(255,255,255,0.05));
    top: 50%;
    left: 30%;
    animation: float3 18s ease-in-out infinite;
}

@keyframes float1 {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    25% { transform: translate(20px, 30px) rotate(5deg); }
    50% { transform: translate(-10px, 50px) rotate(-5deg); }
    75% { transform: translate(30px, 20px) rotate(3deg); }
}

@keyframes float2 {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    33% { transform: translate(-30px, -20px) rotate(-5deg); }
    66% { transform: translate(20px, -30px) rotate(5deg); }
}

@keyframes float3 {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(-20px, 20px) scale(1.1); }
}

/* Quote Container */
.quote-container {
    max-width: 350px;
}

/* Password meter styling */
:deep(.p-password-meter) {
    margin-top: 0.5rem;
}

/* Invalid state styling */
:deep(.p-checkbox.p-invalid .p-checkbox-box) {
    border-color: rgb(239 68 68);
}

:deep(.p-inputtext.p-invalid) {
    border-color: rgb(239 68 68);
}

:deep(.p-password.p-invalid .p-password-input) {
    border-color: rgb(239 68 68);
}

/* Input focus states */
:deep(.p-inputtext:focus) {
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
}

/* Dropdown styling */
:deep(.p-dropdown) {
    width: 100%;
}

/* IconField proper spacing - ensures icon doesn't overlap placeholder */
:deep(.p-iconfield) {
    width: 100%;
}

:deep(.p-iconfield .p-inputtext) {
    padding-left: 2.5rem;
}

:deep(.p-iconfield .p-inputicon) {
    left: 0.75rem;
    color: #9ca3af;
}

:deep(.p-iconfield .p-inputmask) {
    padding-left: 2.5rem;
}
</style>

<script setup>
/**
 * Profile Page
 * User profile management with tabs for personal info, security, and settings
 */
import { ref, computed, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Textarea from 'primevue/textarea';
import DatePicker from 'primevue/datepicker';
import Avatar from 'primevue/avatar';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import ToggleSwitch from 'primevue/toggleswitch';
import Divider from 'primevue/divider';
import Badge from 'primevue/badge';
import Message from 'primevue/message';
import Select from 'primevue/select';
import Dialog from 'primevue/dialog';
import Card from 'primevue/card';
import InputNumber from 'primevue/inputnumber';

import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import PinInput from '@/Components/Forms/PinInput.vue';
import { getInitials } from '@/Utils/formatters';

const props = defineProps({
    user: Object,
    activeTab: {
        type: String,
        default: 'personal'
    },
    loginHistory: {
        type: Array,
        default: () => []
    },
    preferences: {
        type: Object,
        default: () => ({})
    },
    notifications: {
        type: Object,
        default: () => ({})
    },
    availableLanguages: {
        type: Array,
        default: () => []
    },
    availableTimezones: {
        type: Array,
        default: () => []
    },
});

const toast = useToast();

// Active tab state
const currentTab = ref(
    props.activeTab === 'security' ? '1' : 
    props.activeTab === 'settings' ? '2' : '0'
);

// Profile form
const profileForm = useForm({
    first_name: props.user.first_name || '',
    last_name: props.user.last_name || '',
    middle_name: props.user.middle_name || '',
    phone_number: props.user.phone_number || '',
    date_of_birth: props.user.date_of_birth ? new Date(props.user.date_of_birth) : null,
    address_line_1: props.user.address_line_1 || '',
    address_line_2: props.user.address_line_2 || '',
    city: props.user.city || '',
    state: props.user.state || '',
    postal_code: props.user.postal_code || '',
    country: props.user.country || '',
});

// Password form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

// PIN form
const pinForm = useForm({
    current_pin: '',
    pin: '',
    pin_confirmation: '',
});

// New PIN form (for users without PIN)
const newPinForm = useForm({
    pin: '',
    pin_confirmation: '',
});

// Settings forms (moved from Settings page)
const preferencesForm = useForm({
    theme: props.preferences?.theme || 'system',
    language: props.preferences?.language || 'en',
    currency_display: props.preferences?.currency_display || 'symbol',
    date_format: props.preferences?.date_format || 'M d, Y',
    time_format: props.preferences?.time_format || '12h',
    timezone: props.preferences?.timezone || 'UTC',
    lockscreen_enabled: props.preferences?.lockscreen_enabled ?? false,
    lockscreen_timeout: props.preferences?.lockscreen_timeout ?? 5,
});

const notificationsForm = useForm({
    email_transactions: props.notifications?.email_transactions ?? true,
    email_security: props.notifications?.email_security ?? true,
    email_marketing: props.notifications?.email_marketing ?? false,
    push_transactions: props.notifications?.push_transactions ?? true,
    push_security: props.notifications?.push_security ?? true,
    sms_transactions: props.notifications?.sms_transactions ?? false,
    sms_security: props.notifications?.sms_security ?? true,
});

// Delete account dialog
const showDeleteDialog = ref(false);
const deleteForm = useForm({
    password: '',
    confirmation: '',
});

// Settings options
const themeOptions = [
    { label: 'Light', value: 'light' },
    { label: 'Dark', value: 'dark' },
    { label: 'System', value: 'system' },
];

const currencyDisplayOptions = [
    { label: 'Symbol ($)', value: 'symbol' },
    { label: 'Code (USD)', value: 'code' },
    { label: 'Name (US Dollar)', value: 'name' },
];

const dateFormatOptions = [
    { label: 'Jan 27, 2026', value: 'M d, Y' },
    { label: '27/01/2026', value: 'd/m/Y' },
    { label: '01/27/2026', value: 'm/d/Y' },
    { label: '2026-01-27', value: 'Y-m-d' },
];

const timeFormatOptions = [
    { label: '12-hour (3:30 PM)', value: '12h' },
    { label: '24-hour (15:30)', value: '24h' },
];

const submitProfile = () => {
    profileForm.put('/profile', {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Profile Updated',
                detail: 'Your profile has been updated successfully.',
                life: 3000,
            });
        },
    });
};

const submitPassword = () => {
    passwordForm.put('/profile/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            toast.add({
                severity: 'success',
                summary: 'Password Updated',
                detail: 'Your password has been changed successfully.',
                life: 3000,
            });
        },
    });
};

const submitPin = () => {
    pinForm.put('/profile/pin', {
        preserveScroll: true,
        onSuccess: () => {
            pinForm.reset();
            toast.add({
                severity: 'success',
                summary: 'PIN Updated',
                detail: 'Your transaction PIN has been changed successfully.',
                life: 3000,
            });
        },
    });
};

const submitNewPin = () => {
    newPinForm.post('/profile/pin', {
        preserveScroll: true,
        onSuccess: () => {
            newPinForm.reset();
            toast.add({
                severity: 'success',
                summary: 'PIN Set',
                detail: 'Your transaction PIN has been set successfully.',
                life: 3000,
            });
        },
    });
};

const onAvatarSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validate file size (2MB max)
        if (file.size > 2000000) {
            toast.add({
                severity: 'error',
                summary: 'File Too Large',
                detail: 'Please select an image smaller than 2MB.',
                life: 5000,
            });
            return;
        }
        
        const formData = new FormData();
        formData.append('avatar', file);
        
        router.post('/profile/avatar', formData, {
            preserveScroll: true,
            onSuccess: () => {
                toast.add({
                    severity: 'success',
                    summary: 'Avatar Updated',
                    detail: 'Your profile photo has been updated.',
                    life: 3000,
                });
            },
            onError: () => {
                toast.add({
                    severity: 'error',
                    summary: 'Upload Failed',
                    detail: 'Failed to upload profile photo. Please try again.',
                    life: 5000,
                });
            },
        });
    }
    // Reset input so the same file can be selected again
    event.target.value = '';
};

const removeAvatar = () => {
    router.delete('/profile/avatar', {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Avatar Removed',
                detail: 'Your profile photo has been removed.',
                life: 3000,
            });
        },
    });
};

// Settings handlers
const submitPreferences = () => {
    preferencesForm.put('/settings/preferences', {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Preferences Saved',
                detail: 'Your preferences have been updated.',
                life: 3000,
            });
        },
    });
};

const submitNotifications = () => {
    notificationsForm.put('/settings/notifications', {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Notifications Updated',
                detail: 'Your notification preferences have been saved.',
                life: 3000,
            });
        },
    });
};

const exportData = () => {
    window.location.href = '/settings/export-data';
};

const confirmDeleteAccount = () => {
    showDeleteDialog.value = true;
};

const deleteAccount = () => {
    deleteForm.delete('/settings/account', {
        onSuccess: () => {
            showDeleteDialog.value = false;
        },
        onError: () => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Failed to delete account. Please check your password and try again.',
                life: 5000,
            });
        },
    });
};

// Handle image load errors
const handleImageError = (event) => {
    event.target.style.display = 'none';
};
</script>

<template>
    <Head title="My Profile" />

    <DashboardLayout>
        <div class="max-w-5xl mx-auto">
            <!-- Page Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Profile</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Manage your account settings and preferences</p>
            </div>

            <!-- Profile Header Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <div class="flex flex-col sm:flex-row items-center gap-6">
                    <!-- Avatar -->
                    <div class="relative">
                        <img
                            v-if="user.avatar_url"
                            :src="user.avatar_url"
                            :alt="user.full_name"
                            class="w-24 h-24 rounded-full object-cover ring-4 ring-indigo-100 dark:ring-indigo-900/50"
                            @error="handleImageError"
                        />
                        <Avatar
                            v-else
                            :label="getInitials(`${user.first_name} ${user.last_name}`)"
                            class="!w-24 !h-24 !text-2xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400"
                            shape="circle"
                        />
                        <!-- Camera Upload Button -->
                        <label class="absolute -bottom-1 -right-1 cursor-pointer">
                            <input
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="onAvatarSelect"
                            />
                            <span class="w-8 h-8 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full flex items-center justify-center shadow-lg transition-colors">
                                <i class="pi pi-camera text-sm"></i>
                            </span>
                        </label>
                    </div>

                    <!-- User Info -->
                    <div class="text-center sm:text-left flex-1">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ user.full_name }}
                        </h2>
                        <p class="text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-3">
                            <Badge 
                                :value="user.is_verified ? 'Verified' : 'Unverified'" 
                                :severity="user.is_verified ? 'success' : 'warn'"
                            />
                            <Badge 
                                :value="`KYC Level ${user.kyc_level}`" 
                                severity="info"
                            />
                            <Badge 
                                v-if="user.two_factor_enabled"
                                value="2FA Enabled" 
                                severity="success"
                            />
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="hidden lg:flex gap-6 text-center">
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ user.created_at }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Member Since</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ user.last_login_at || 'N/A' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Last Login</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <Tabs v-model:value="currentTab">
                    <TabList class="border-b border-gray-200 dark:border-gray-700">
                        <Tab value="0" class="!rounded-none">
                            <i class="pi pi-user mr-2"></i>
                            Personal Info
                        </Tab>
                        <Tab value="1" class="!rounded-none">
                            <i class="pi pi-shield mr-2"></i>
                            Security
                        </Tab>
                        <Tab value="2" class="!rounded-none">
                            <i class="pi pi-cog mr-2"></i>
                            Settings
                        </Tab>
                    </TabList>

                    <TabPanels class="p-6">
                        <!-- Personal Info Tab -->
                        <TabPanel value="0">
                            <!-- Account Information (Read-only) -->
                            <div class="mb-8 p-6 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <i class="pi pi-info-circle text-indigo-600 dark:text-indigo-400"></i>
                                    Account Information
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Username</p>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ user.username }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Email Address</p>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ user.email }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Account Status</p>
                                        <Badge :value="user.is_verified ? 'Verified' : 'Unverified'" :severity="user.is_verified ? 'success' : 'warn'" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">KYC Level</p>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Level {{ user.kyc_level }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Member Since</p>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ user.created_at }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Email Verified</p>
                                        <Badge :value="user.email_verified_at ? 'Yes' : 'No'" :severity="user.email_verified_at ? 'success' : 'danger'" />
                                    </div>
                                </div>
                            </div>

                            <form @submit.prevent="submitProfile" class="space-y-6">
                                <!-- Name Fields -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">First Name *</label>
                                        <InputText 
                                            v-model="profileForm.first_name" 
                                            placeholder="John"
                                            :class="{ 'p-invalid': profileForm.errors.first_name }"
                                        />
                                        <small v-if="profileForm.errors.first_name" class="text-red-500">{{ profileForm.errors.first_name }}</small>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Middle Name</label>
                                        <InputText 
                                            v-model="profileForm.middle_name" 
                                            placeholder="William"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Last Name *</label>
                                        <InputText 
                                            v-model="profileForm.last_name" 
                                            placeholder="Doe"
                                            :class="{ 'p-invalid': profileForm.errors.last_name }"
                                        />
                                        <small v-if="profileForm.errors.last_name" class="text-red-500">{{ profileForm.errors.last_name }}</small>
                                    </div>
                                </div>

                                <!-- Contact Fields -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Phone Number</label>
                                        <InputText 
                                            v-model="profileForm.phone_number" 
                                            placeholder="+1 (555) 123-4567"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Date of Birth</label>
                                        <DatePicker 
                                            v-model="profileForm.date_of_birth"
                                            dateFormat="M dd, yy"
                                            showIcon
                                            :maxDate="new Date()"
                                        />
                                    </div>
                                </div>

                                <Divider />

                                <!-- Address Fields -->
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Address</h3>
                                
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Address Line 1</label>
                                    <InputText 
                                        v-model="profileForm.address_line_1" 
                                        placeholder="123 Main Street"
                                    />
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Address Line 2</label>
                                    <InputText 
                                        v-model="profileForm.address_line_2" 
                                        placeholder="Apt 4B"
                                    />
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">City</label>
                                        <InputText 
                                            v-model="profileForm.city" 
                                            placeholder="New York"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">State</label>
                                        <InputText 
                                            v-model="profileForm.state" 
                                            placeholder="NY"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Postal Code</label>
                                        <InputText 
                                            v-model="profileForm.postal_code" 
                                            placeholder="10001"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
                                        <InputText 
                                            v-model="profileForm.country" 
                                            placeholder="United States"
                                        />
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="flex justify-end pt-4">
                                    <Button 
                                        type="submit" 
                                        label="Save Changes" 
                                        icon="pi pi-check"
                                        :loading="profileForm.processing"
                                    />
                                </div>
                            </form>
                        </TabPanel>

                        <!-- Security Tab -->
                        <TabPanel value="1">
                            <div class="space-y-6">
                                <div class="grid gap-6 lg:grid-cols-2">
                                    <Card class="h-full">
                                        <template #title>
                                            <div class="flex items-center gap-2">
                                                <i class="pi pi-lock text-indigo-500"></i>
                                                <span>Change Password</span>
                                            </div>
                                        </template>
                                        <template #content>
                                            <form @submit.prevent="submitPassword" class="space-y-4">
                                                <div class="flex flex-col gap-2">
                                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Current Password</label>
                                                    <Password 
                                                        v-model="passwordForm.current_password"
                                                        :feedback="false"
                                                        toggleMask
                                                        :class="{ 'p-invalid': passwordForm.errors.current_password }"
                                                    />
                                                    <small v-if="passwordForm.errors.current_password" class="text-red-500">{{ passwordForm.errors.current_password }}</small>
                                                </div>
                                                <div class="flex flex-col gap-2">
                                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
                                                    <Password 
                                                        v-model="passwordForm.password"
                                                        toggleMask
                                                        :class="{ 'p-invalid': passwordForm.errors.password }"
                                                    />
                                                    <small v-if="passwordForm.errors.password" class="text-red-500">{{ passwordForm.errors.password }}</small>
                                                </div>
                                                <div class="flex flex-col gap-2">
                                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Confirm New Password</label>
                                                    <Password 
                                                        v-model="passwordForm.password_confirmation"
                                                        :feedback="false"
                                                        toggleMask
                                                    />
                                                </div>
                                                <Button 
                                                    type="submit" 
                                                    label="Update Password" 
                                                    icon="pi pi-check"
                                                    :loading="passwordForm.processing"
                                                />
                                            </form>
                                        </template>
                                    </Card>

                                    <Card class="h-full">
                                        <template #title>
                                            <div class="flex items-center gap-2">
                                                <i class="pi pi-key text-indigo-500"></i>
                                                <span>Transaction PIN</span>
                                            </div>
                                        </template>
                                        <template #content>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                                Your 4-digit PIN is used to authorize transactions.
                                            </p>

                                            <form v-if="!user.has_transaction_pin" @submit.prevent="submitNewPin" class="space-y-4">
                                                <Message severity="warn" :closable="false">
                                                    You haven't set a transaction PIN yet. Please set one to authorize transactions.
                                                </Message>
                                                <div class="flex flex-col gap-2">
                                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">New PIN (4 digits)</label>
                                                    <PinInput
                                                        v-model="newPinForm.pin"
                                                        :length="4"
                                                        masked
                                                    />
                                                    <small v-if="newPinForm.errors.pin" class="text-red-500">{{ newPinForm.errors.pin }}</small>
                                                </div>
                                                <div class="flex flex-col gap-2">
                                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Confirm PIN</label>
                                                    <PinInput
                                                        v-model="newPinForm.pin_confirmation"
                                                        :length="4"
                                                        masked
                                                    />
                                                </div>
                                                <Button 
                                                    type="submit" 
                                                    label="Set PIN" 
                                                    icon="pi pi-check"
                                                    :loading="newPinForm.processing"
                                                />
                                            </form>

                                            <form v-else @submit.prevent="submitPin" class="space-y-4">
                                                <div class="flex flex-col gap-2">
                                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Current PIN</label>
                                                    <PinInput
                                                        v-model="pinForm.current_pin"
                                                        :length="4"
                                                        masked
                                                    />
                                                    <small v-if="pinForm.errors.current_pin" class="text-red-500">{{ pinForm.errors.current_pin }}</small>
                                                </div>
                                                <div class="flex flex-col gap-2">
                                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">New PIN</label>
                                                    <PinInput
                                                        v-model="pinForm.pin"
                                                        :length="4"
                                                        masked
                                                    />
                                                    <small v-if="pinForm.errors.pin" class="text-red-500">{{ pinForm.errors.pin }}</small>
                                                </div>
                                                <div class="flex flex-col gap-2">
                                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Confirm New PIN</label>
                                                    <PinInput
                                                        v-model="pinForm.pin_confirmation"
                                                        :length="4"
                                                        masked
                                                    />
                                                </div>
                                                <Button 
                                                    type="submit" 
                                                    label="Change PIN" 
                                                    icon="pi pi-check"
                                                    :loading="pinForm.processing"
                                                />
                                            </form>
                                        </template>
                                    </Card>
                                </div>

                                <Card>
                                    <template #title>
                                        <div class="flex items-center gap-2">
                                            <i class="pi pi-history text-indigo-500"></i>
                                            <span>Recent Login Activity</span>
                                        </div>
                                    </template>
                                    <template #content>
                                        <div class="space-y-3">
                                            <div 
                                                v-for="session in loginHistory" 
                                                :key="session.ip"
                                                class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                                            >
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center">
                                                        <i class="pi pi-desktop text-indigo-600 dark:text-indigo-400"></i>
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-gray-900 dark:text-white">{{ session.device }}</p>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ session.ip }} · {{ session.location }}</p>
                                                    </div>
                                                </div>
                                                <Badge v-if="session.is_current" value="Current" severity="success" />
                                            </div>
                                        </div>
                                    </template>
                                </Card>
                            </div>
                        </TabPanel>

                        <!-- Settings Tab -->
                        <TabPanel value="2">
                            <div class="space-y-8">
                                <!-- Appearance & Display -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                        <i class="pi pi-palette mr-2"></i>
                                        Appearance & Display
                                    </h3>

                                    <form @submit.prevent="submitPreferences" class="space-y-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <!-- Theme -->
                                            <div class="flex flex-col gap-2">
                                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Theme</label>
                                                <Select 
                                                    v-model="preferencesForm.theme"
                                                    :options="themeOptions"
                                                    optionLabel="label"
                                                    optionValue="value"
                                                    placeholder="Select theme"
                                                    class="w-full"
                                                />
                                            </div>

                                            <!-- Language -->
                                            <div class="flex flex-col gap-2">
                                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Language</label>
                                                <Select 
                                                    v-model="preferencesForm.language"
                                                    :options="availableLanguages"
                                                    optionLabel="name"
                                                    optionValue="code"
                                                    placeholder="Select language"
                                                    class="w-full"
                                                />
                                            </div>

                                            <!-- Currency Display -->
                                            <div class="flex flex-col gap-2">
                                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Currency Display</label>
                                                <Select 
                                                    v-model="preferencesForm.currency_display"
                                                    :options="currencyDisplayOptions"
                                                    optionLabel="label"
                                                    optionValue="value"
                                                    placeholder="Select format"
                                                    class="w-full"
                                                />
                                            </div>

                                            <!-- Date Format -->
                                            <div class="flex flex-col gap-2">
                                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Date Format</label>
                                                <Select 
                                                    v-model="preferencesForm.date_format"
                                                    :options="dateFormatOptions"
                                                    optionLabel="label"
                                                    optionValue="value"
                                                    placeholder="Select format"
                                                    class="w-full"
                                                />
                                            </div>

                                            <!-- Time Format -->
                                            <div class="flex flex-col gap-2">
                                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Time Format</label>
                                                <Select 
                                                    v-model="preferencesForm.time_format"
                                                    :options="timeFormatOptions"
                                                    optionLabel="label"
                                                    optionValue="value"
                                                    placeholder="Select format"
                                                    class="w-full"
                                                />
                                            </div>

                                            <!-- Timezone -->
                                            <div class="flex flex-col gap-2">
                                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Timezone</label>
                                                <Select 
                                                    v-model="preferencesForm.timezone"
                                                    :options="availableTimezones"
                                                    optionLabel="name"
                                                    optionValue="code"
                                                    placeholder="Select timezone"
                                                    class="w-full"
                                                    filter
                                                />
                                            </div>
                                        </div>

                                        <div class="flex justify-end">
                                            <Button 
                                                type="submit" 
                                                label="Save Preferences" 
                                                icon="pi pi-check"
                                                :loading="preferencesForm.processing"
                                            />
                                        </div>
                                    </form>
                                </div>

                                <Divider />

                                <!-- Notification Preferences -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                        <i class="pi pi-bell mr-2"></i>
                                        Notification Preferences
                                    </h3>

                                    <form @submit.prevent="submitNotifications" class="space-y-6">
                                        <!-- Email Notifications -->
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Email Notifications</h4>
                                            <div class="space-y-4">
                                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                                    <div>
                                                        <p class="font-medium text-gray-900 dark:text-white">Transaction Alerts</p>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">Receive emails for deposits, withdrawals, and transfers</p>
                                                    </div>
                                                    <ToggleSwitch v-model="notificationsForm.email_transactions" />
                                                </div>
                                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                                    <div>
                                                        <p class="font-medium text-gray-900 dark:text-white">Security Alerts</p>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">Get notified about login attempts and password changes</p>
                                                    </div>
                                                    <ToggleSwitch v-model="notificationsForm.email_security" />
                                                </div>
                                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                                    <div>
                                                        <p class="font-medium text-gray-900 dark:text-white">Marketing & Promotions</p>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">Receive offers, news, and product updates</p>
                                                    </div>
                                                    <ToggleSwitch v-model="notificationsForm.email_marketing" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Push Notifications -->
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Push Notifications</h4>
                                            <div class="space-y-4">
                                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                                    <div>
                                                        <p class="font-medium text-gray-900 dark:text-white">Transaction Alerts</p>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">Instant notifications for account activity</p>
                                                    </div>
                                                    <ToggleSwitch v-model="notificationsForm.push_transactions" />
                                                </div>
                                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                                    <div>
                                                        <p class="font-medium text-gray-900 dark:text-white">Security Alerts</p>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">Immediate alerts for suspicious activity</p>
                                                    </div>
                                                    <ToggleSwitch v-model="notificationsForm.push_security" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- SMS Notifications -->
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">SMS Notifications</h4>
                                            <div class="space-y-4">
                                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                                    <div>
                                                        <p class="font-medium text-gray-900 dark:text-white">Transaction Alerts</p>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">Text messages for large transactions</p>
                                                    </div>
                                                    <ToggleSwitch v-model="notificationsForm.sms_transactions" />
                                                </div>
                                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                                    <div>
                                                        <p class="font-medium text-gray-900 dark:text-white">Security Alerts</p>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">OTP codes and security notifications via SMS</p>
                                                    </div>
                                                    <ToggleSwitch v-model="notificationsForm.sms_security" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex justify-end">
                                            <Button 
                                                type="submit" 
                                                label="Save Notification Settings" 
                                                icon="pi pi-check"
                                                :loading="notificationsForm.processing"
                                            />
                                        </div>
                                    </form>
                                </div>

                                <Divider />

                                <!-- Lockscreen & Session Security -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                        <i class="pi pi-shield mr-2"></i>
                                        Lockscreen & Session Security
                                    </h3>

                                    <form @submit.prevent="submitPreferences" class="space-y-6">
                                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                            <div class="flex-1">
                                                <p class="font-medium text-gray-900 dark:text-white">Enable Lockscreen</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Automatically lock your account after a period of inactivity</p>
                                            </div>
                                            <ToggleSwitch v-model="preferencesForm.lockscreen_enabled" />
                                        </div>

                                        <div v-if="preferencesForm.lockscreen_enabled" class="flex flex-col gap-2">
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Lockscreen Timeout (minutes)</label>
                                            <InputNumber 
                                                v-model="preferencesForm.lockscreen_timeout"
                                                :min="1"
                                                :max="60"
                                                showButtons
                                                buttonLayout="horizontal"
                                                class="w-48"
                                            >
                                                <template #incrementbuttonicon>
                                                    <i class="pi pi-plus" />
                                                </template>
                                                <template #decrementbuttonicon>
                                                    <i class="pi pi-minus" />
                                                </template>
                                            </InputNumber>
                                            <small class="text-gray-500 dark:text-gray-400">After {{ preferencesForm.lockscreen_timeout }} minutes of inactivity, you'll need to re-enter your PIN</small>
                                        </div>

                                        <div class="flex justify-end">
                                            <Button 
                                                type="submit" 
                                                label="Save Security Settings" 
                                                icon="pi pi-check"
                                                :loading="preferencesForm.processing"
                                            />
                                        </div>
                                    </form>
                                </div>

                                <Divider />

                                <!-- Data & Privacy -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                        <i class="pi pi-lock mr-2"></i>
                                        Data & Privacy
                                    </h3>

                                    <div class="space-y-4">
                                        <!-- Export Data -->
                                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">Export Your Data</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Download a copy of all your account data in JSON format</p>
                                            </div>
                                            <Button 
                                                label="Export" 
                                                icon="pi pi-download"
                                                severity="secondary"
                                                @click="exportData"
                                            />
                                        </div>

                                        <!-- Delete Account -->
                                        <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <p class="font-medium text-red-700 dark:text-red-400">Delete Account</p>
                                                    <p class="text-sm text-red-600 dark:text-red-300 mt-1">
                                                        Permanently delete your account and all associated data. This action cannot be undone.
                                                    </p>
                                                </div>
                                                <Button 
                                                    label="Delete" 
                                                    icon="pi pi-trash"
                                                    severity="danger"
                                                    @click="confirmDeleteAccount"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </TabPanel>
                    </TabPanels>
                </Tabs>
            </div>
        </div>

        <!-- Delete Account Dialog -->
        <Dialog 
            v-model:visible="showDeleteDialog"
            header="Delete Account"
            :modal="true"
            :closable="true"
            class="w-full max-w-md"
        >
            <div class="space-y-4">
                <Message severity="error" :closable="false">
                    This action is permanent and cannot be undone. All your data will be deleted.
                </Message>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Enter your password</label>
                    <Password 
                        v-model="deleteForm.password"
                        :feedback="false"
                        toggleMask
                        class="w-full"
                    />
                    <small v-if="deleteForm.errors.password" class="text-red-500">{{ deleteForm.errors.password }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Type DELETE to confirm</label>
                    <InputText 
                        v-model="deleteForm.confirmation"
                        placeholder="DELETE"
                        class="w-full"
                    />
                    <small v-if="deleteForm.errors.confirmation" class="text-red-500">{{ deleteForm.errors.confirmation }}</small>
                </div>
            </div>

            <template #footer>
                <Button 
                    label="Cancel" 
                    severity="secondary"
                    @click="showDeleteDialog = false"
                />
                <Button 
                    label="Delete Account" 
                    severity="danger"
                    icon="pi pi-trash"
                    :loading="deleteForm.processing"
                    :disabled="deleteForm.confirmation !== 'DELETE'"
                    @click="deleteAccount"
                />
            </template>
        </Dialog>
    </DashboardLayout>
</template>

<style scoped>
:deep(.p-password) {
    width: 100%;
}

:deep(.p-password-input) {
    width: 100%;
}
</style>

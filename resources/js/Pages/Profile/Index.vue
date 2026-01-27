<script setup>
/**
 * Profile Page
 * User profile management with tabs for personal info, security, and notifications
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
    }
});

const toast = useToast();

// Active tab state
const currentTab = ref(props.activeTab === 'security' ? '1' : '0');

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

// Two Factor state
const twoFactorEnabled = ref(props.user.two_factor_enabled);

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

const toggleTwoFactor = () => {
    router.post('/profile/two-factor', {}, {
        preserveScroll: true,
        onSuccess: () => {
            twoFactorEnabled.value = !twoFactorEnabled.value;
            toast.add({
                severity: 'success',
                summary: 'Two-Factor Authentication',
                detail: twoFactorEnabled.value ? 'Two-factor authentication enabled.' : 'Two-factor authentication disabled.',
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
                    </TabList>

                    <TabPanels class="p-6">
                        <!-- Personal Info Tab -->
                        <TabPanel value="0">
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
                            <div class="space-y-8">
                                <!-- Password Section -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Change Password</h3>
                                    <form @submit.prevent="submitPassword" class="space-y-4 max-w-md">
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
                                            icon="pi pi-lock"
                                            :loading="passwordForm.processing"
                                        />
                                    </form>
                                </div>

                                <Divider />

                                <!-- Transaction PIN Section -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Transaction PIN</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                        Your 4-digit PIN is used to authorize transactions.
                                    </p>

                                    <!-- Set PIN (if no PIN exists) -->
                                    <form v-if="!user.has_transaction_pin" @submit.prevent="submitNewPin" class="space-y-4 max-w-md">
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
                                            icon="pi pi-key"
                                            :loading="newPinForm.processing"
                                        />
                                    </form>

                                    <!-- Change PIN (if PIN exists) -->
                                    <form v-else @submit.prevent="submitPin" class="space-y-4 max-w-md">
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
                                            icon="pi pi-key"
                                            :loading="pinForm.processing"
                                        />
                                    </form>
                                </div>

                                <Divider />

                                <!-- Two-Factor Authentication -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Two-Factor Authentication</h3>
                                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg max-w-md">
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">Email OTP Verification</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Receive a verification code via email for sensitive actions.
                                            </p>
                                        </div>
                                        <ToggleSwitch 
                                            v-model="twoFactorEnabled"
                                            @change="toggleTwoFactor"
                                        />
                                    </div>
                                </div>

                                <Divider />

                                <!-- Login History -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Recent Login Activity</h3>
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
                                </div>
                            </div>
                        </TabPanel>
                    </TabPanels>
                </Tabs>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
/**
 * Settings Page
 * User preferences and app settings
 */
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import { useConfirm } from 'primevue/useconfirm';
import Button from 'primevue/button';
import Select from 'primevue/select';
import ToggleSwitch from 'primevue/toggleswitch';
import Divider from 'primevue/divider';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';
import Card from 'primevue/card';

import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    preferences: Object,
    notifications: Object,
    availableLanguages: Array,
    availableTimezones: Array,
});

const toast = useToast();
const confirm = useConfirm();

// Preferences form
const preferencesForm = useForm({
    theme: props.preferences?.theme || 'system',
    language: props.preferences?.language || 'en',
    currency_display: props.preferences?.currency_display || 'symbol',
    date_format: props.preferences?.date_format || 'M d, Y',
    time_format: props.preferences?.time_format || '12h',
    timezone: props.preferences?.timezone || 'UTC',
});

// Notification preferences
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

// Theme options
const themeOptions = [
    { label: 'Light', value: 'light' },
    { label: 'Dark', value: 'dark' },
    { label: 'System', value: 'system' },
];

// Currency display options
const currencyDisplayOptions = [
    { label: 'Symbol ($)', value: 'symbol' },
    { label: 'Code (USD)', value: 'code' },
    { label: 'Name (US Dollar)', value: 'name' },
];

// Date format options
const dateFormatOptions = [
    { label: 'Jan 27, 2026', value: 'M d, Y' },
    { label: '27/01/2026', value: 'd/m/Y' },
    { label: '01/27/2026', value: 'm/d/Y' },
    { label: '2026-01-27', value: 'Y-m-d' },
];

// Time format options
const timeFormatOptions = [
    { label: '12-hour (3:30 PM)', value: '12h' },
    { label: '24-hour (15:30)', value: '24h' },
];

const submitPreferences = () => {
    preferencesForm.put('/settings/preferences', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Your preferences have been updated.', 'Preferences Saved');
        },
    });
};

const submitNotifications = () => {
    notificationsForm.put('/settings/notifications', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Your notification preferences have been saved.', 'Notifications Updated');
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
            toast.error('Failed to delete account. Please check your password and try again.', 'Error');
        },
    });
};
</script>

<template>
    <Head title="Settings" />

    <DashboardLayout>
        <div class="max-w-4xl mx-auto">
            <!-- Page Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Settings</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Manage your app preferences and notifications</p>
            </div>

            <div class="space-y-6">
                <!-- Appearance & Display -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                        <i class="pi pi-palette mr-2"></i>
                        Appearance & Display
                    </h2>

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

                <!-- Notification Preferences -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                        <i class="pi pi-bell mr-2"></i>
                        Notification Preferences
                    </h2>

                    <form @submit.prevent="submitNotifications" class="space-y-6">
                        <!-- Email Notifications -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Email Notifications</h3>
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

                        <Divider />

                        <!-- Push Notifications -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Push Notifications</h3>
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

                        <Divider />

                        <!-- SMS Notifications -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">SMS Notifications</h3>
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

                <!-- Data & Privacy -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                        <i class="pi pi-lock mr-2"></i>
                        Data & Privacy
                    </h2>

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

                        <Divider />

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

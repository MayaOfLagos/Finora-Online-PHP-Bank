<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import ProgressBar from 'primevue/progressbar';
import Dialog from 'primevue/dialog';
import Password from 'primevue/password';
import Divider from 'primevue/divider';
import Message from 'primevue/message';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
    rewards: { type: Object, default: () => ({ data: [] }) },
    stats: { type: Object, default: () => ({}) },
    bankAccounts: { type: Array, default: () => [] },
});

const page = usePage();
const flash = computed(() => page.props.flash);
const toast = useToast();

// Show flash success message
if (flash.value?.success) {
    toast.success(flash.value.success, 'Success');
}

const form = useForm({
    points: 100,
    redemption_type: 'cash',
    bank_account_id: props.bankAccounts?.[0]?.id ?? null,
    pin: '',
});

const redemptionOptions = [
    { label: 'Cash (Credit to Account)', value: 'cash', icon: 'pi pi-wallet' },
    { label: 'Voucher Code', value: 'voucher', icon: 'pi pi-ticket' },
    { label: 'Discount on Services', value: 'discount', icon: 'pi pi-percentage' },
];

// Modal states
const showConfirmModal = ref(false);
const showPinModal = ref(false);
const isVerifying = ref(false);

const totalEarned = computed(() => props.stats.total_earned ?? 0);
const totalRedeemed = computed(() => props.stats.total_redeemed ?? 0);
const available = computed(() => props.stats.available ?? 0);
const progress = computed(() => (totalEarned.value ? Math.min((available.value / totalEarned.value) * 100, 100) : 0));

// Calculate cash value (100 points = $1)
const cashValue = computed(() => (form.points / 100).toFixed(2));

// Get selected account
const selectedAccount = computed(() => {
    return props.bankAccounts.find(acc => acc.id === form.bank_account_id);
});

// Get selected redemption option
const selectedRedemptionOption = computed(() => {
    return redemptionOptions.find(opt => opt.value === form.redemption_type);
});

// Status badge colors
const statusSeverity = (status) => {
    const map = {
        earned: 'success',
        redeemed: 'info',
        pending: 'warn',
        expired: 'danger',
    };
    return map[status] || 'secondary';
};

// Step 1: Initiate redemption
const initiateRedeem = () => {
    if (form.points < 100) {
        form.setError('points', 'Minimum 100 points required');
        return;
    }
    if (form.points > available.value) {
        form.setError('points', `You only have ${available.value} points available`);
        return;
    }
    if (form.redemption_type === 'cash' && !form.bank_account_id) {
        form.setError('bank_account_id', 'Please select an account for cash redemption');
        return;
    }
    form.clearErrors();
    showConfirmModal.value = true;
};

// Step 2: Proceed to PIN
const proceedToPinVerification = () => {
    showConfirmModal.value = false;
    form.pin = '';
    showPinModal.value = true;
};

// Step 3: Submit with PIN
const submitWithPin = () => {
    if (form.pin.length !== 6) {
        form.setError('pin', 'PIN must be 6 digits');
        return;
    }
    
    isVerifying.value = true;
    form.post('/rewards/redeem', {
        preserveScroll: true,
        onSuccess: () => {
            showPinModal.value = false;
            form.reset('points', 'pin');
            form.points = 100;
        },
        onError: (errors) => {
            if (errors.pin) {
                // Keep PIN modal open for PIN errors
            } else {
                showPinModal.value = false;
            }
        },
        onFinish: () => {
            isVerifying.value = false;
        },
    });
};

const cancelRedeem = () => {
    showConfirmModal.value = false;
    showPinModal.value = false;
    form.pin = '';
};

// Format date
const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric' 
    });
};
</script>

<template>
    <DashboardLayout title="Rewards">
        <div class="space-y-6">
            <div class="space-y-1">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Rewards</h1>
                <p class="text-gray-600 dark:text-gray-400">Earn and redeem points across {{ $page.props.appName || 'Finora' }} experiences.</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="p-4 bg-gradient-to-br from-green-500 to-green-600 rounded-xl text-white">
                    <p class="text-sm opacity-80">Available Points</p>
                    <p class="text-3xl font-bold">{{ available.toLocaleString() }}</p>
                    <p class="text-xs mt-1 opacity-70">≈ ${{ (available / 100).toFixed(2) }} value</p>
                </div>
                <div class="p-4 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl text-white">
                    <p class="text-sm opacity-80">Total Earned</p>
                    <p class="text-3xl font-bold">{{ totalEarned.toLocaleString() }}</p>
                </div>
                <div class="p-4 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl text-white">
                    <p class="text-sm opacity-80">Total Redeemed</p>
                    <p class="text-3xl font-bold">{{ totalRedeemed.toLocaleString() }}</p>
                </div>
                <div class="p-4 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl text-white">
                    <p class="text-sm opacity-80">Pending</p>
                    <p class="text-3xl font-bold">{{ stats.pending ?? 0 }}</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <Card class="lg:col-span-2">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-gift text-primary"></i>
                            Redeem Points
                        </div>
                    </template>
                    <template #content>
                        <Message v-if="available < 100" severity="warn" :closable="false" class="mb-4">
                            You need at least 100 points to redeem. Keep earning!
                        </Message>
                        
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Points to Redeem</label>
                                <InputNumber 
                                    v-model="form.points" 
                                    :min="100" 
                                    :max="available"
                                    :step="50" 
                                    class="w-full mt-1"
                                    :disabled="available < 100"
                                />
                                <p class="text-xs text-gray-500 mt-1">Cash value: ${{ cashValue }}</p>
                                <p v-if="form.errors.points" class="text-sm text-red-500 mt-1">{{ form.errors.points }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Redemption Type</label>
                                <Select 
                                    v-model="form.redemption_type" 
                                    :options="redemptionOptions" 
                                    optionLabel="label" 
                                    optionValue="value" 
                                    class="w-full mt-1"
                                    :disabled="available < 100"
                                >
                                    <template #option="{ option }">
                                        <div class="flex items-center gap-2">
                                            <i :class="option.icon"></i>
                                            <span>{{ option.label }}</span>
                                        </div>
                                    </template>
                                </Select>
                                <p v-if="form.errors.redemption_type" class="text-sm text-red-500 mt-1">{{ form.errors.redemption_type }}</p>
                            </div>
                            
                            <!-- Bank account selection for cash redemption -->
                            <div v-if="form.redemption_type === 'cash'" class="md:col-span-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Credit to Account</label>
                                <Select 
                                    v-model="form.bank_account_id" 
                                    :options="bankAccounts" 
                                    optionLabel="account_name" 
                                    optionValue="id" 
                                    class="w-full mt-1"
                                    placeholder="Select account"
                                    :disabled="available < 100"
                                >
                                    <template #option="{ option }">
                                        <div class="flex justify-between items-center w-full">
                                            <span>{{ option.account_name }}</span>
                                            <span class="text-gray-500 text-sm">****{{ option.account_number?.slice(-4) }}</span>
                                        </div>
                                    </template>
                                    <template #value="{ value }">
                                        <span v-if="selectedAccount">
                                            {{ selectedAccount.account_name }} - ****{{ selectedAccount.account_number?.slice(-4) }}
                                        </span>
                                        <span v-else class="text-gray-400">Select account</span>
                                    </template>
                                </Select>
                                <p v-if="form.errors.bank_account_id" class="text-sm text-red-500 mt-1">{{ form.errors.bank_account_id }}</p>
                            </div>
                            
                            <div class="md:col-span-2 flex justify-end">
                                <Button 
                                    label="Redeem Points" 
                                    icon="pi pi-gift" 
                                    :disabled="available < 100 || form.points > available"
                                    @click="initiateRedeem" 
                                />
                            </div>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #title>Points Progress</template>
                    <template #content>
                        <div class="space-y-4">
                            <div class="text-center">
                                <div class="text-4xl font-bold text-primary">{{ available.toLocaleString() }}</div>
                                <p class="text-sm text-gray-500">Available Points</p>
                            </div>
                            <ProgressBar :value="progress" :showValue="false" class="h-3" />
                            <div class="flex justify-between text-xs text-gray-500">
                                <span>0</span>
                                <span>{{ totalEarned.toLocaleString() }} earned</span>
                            </div>
                            
                            <Divider />
                            
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">100 points</span>
                                    <span class="font-medium">= $1.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Min. redemption</span>
                                    <span class="font-medium">100 points</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <Card>
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-history text-primary"></i>
                        Reward Activity
                    </div>
                </template>
                <template #content>
                    <div v-if="rewards.data.length" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div 
                            v-for="reward in rewards.data" 
                            :key="reward.id" 
                            class="p-4 border rounded-xl dark:border-gray-700 bg-white dark:bg-gray-800 space-y-2"
                        >
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900 dark:text-white truncate">{{ reward.title }}</h3>
                                <Tag :value="reward.status" :severity="statusSeverity(reward.status)" />
                            </div>
                            <div class="flex items-center gap-2">
                                <span 
                                    class="text-lg font-bold"
                                    :class="reward.points >= 0 ? 'text-green-600' : 'text-red-600'"
                                >
                                    {{ reward.points >= 0 ? '+' : '' }}{{ reward.points }} pts
                                </span>
                            </div>
                            <p v-if="reward.description" class="text-sm text-gray-600 dark:text-gray-400">{{ reward.description }}</p>
                            <p class="text-xs text-gray-500">{{ reward.source || reward.type }}</p>
                            <p class="text-xs text-gray-400">{{ formatDate(reward.earned_date || reward.created_at) }}</p>
                        </div>
                    </div>
                    <div v-else class="text-center py-8">
                        <i class="pi pi-gift text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                        <p class="text-gray-600 dark:text-gray-400">No rewards yet. Complete transactions to earn points!</p>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Confirmation Modal -->
        <Dialog 
            v-model:visible="showConfirmModal" 
            modal 
            header="Confirm Redemption" 
            :style="{ width: '450px' }"
            :closable="true"
        >
            <div class="space-y-4">
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                        <i class="pi pi-gift text-3xl text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300">You are about to redeem:</p>
                </div>

                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Points:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ form.points.toLocaleString() }} pts</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Cash Value:</span>
                        <span class="font-bold text-green-600">${{ cashValue }}</span>
                    </div>
                    <Divider />
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Redemption Type:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ selectedRedemptionOption?.label }}</span>
                    </div>
                    <div v-if="form.redemption_type === 'cash' && selectedAccount" class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Credit To:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ selectedAccount.account_name }}</span>
                    </div>
                </div>

                <Message severity="info" :closable="false">
                    <span class="text-sm">You will need to enter your 6-digit PIN to confirm.</span>
                </Message>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button label="Cancel" severity="secondary" outlined @click="cancelRedeem" />
                    <Button label="Continue" icon="pi pi-arrow-right" @click="proceedToPinVerification" />
                </div>
            </template>
        </Dialog>

        <!-- PIN Verification Modal -->
        <Dialog 
            v-model:visible="showPinModal" 
            modal 
            header="Enter Transaction PIN" 
            :style="{ width: '400px' }"
            :closable="!isVerifying"
        >
            <div class="space-y-4">
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                        <i class="pi pi-lock text-3xl text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300">Enter your 6-digit transaction PIN</p>
                </div>

                <div class="flex flex-col items-center gap-2">
                    <Password 
                        v-model="form.pin"
                        placeholder="Enter PIN"
                        :feedback="false"
                        toggleMask
                        inputClass="w-full text-center text-2xl tracking-[0.5em] font-mono"
                        :maxlength="6"
                        :disabled="isVerifying"
                        @keyup.enter="submitWithPin"
                    />
                    <p v-if="form.errors.pin" class="text-sm text-red-500">{{ form.errors.pin }}</p>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button label="Cancel" severity="secondary" outlined :disabled="isVerifying" @click="cancelRedeem" />
                    <Button 
                        label="Confirm & Redeem" 
                        icon="pi pi-check" 
                        :loading="isVerifying"
                        :disabled="form.pin.length !== 6"
                        @click="submitWithPin" 
                    />
                </div>
            </template>
        </Dialog>
    </DashboardLayout>
</template>

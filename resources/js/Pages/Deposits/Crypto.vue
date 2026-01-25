<script setup>
/**
 * Crypto Deposit Page
 * Deposit cryptocurrencies via blockchain transactions
 */
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Message from 'primevue/message';
import Divider from 'primevue/divider';

import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    accounts: {
        type: Array,
        default: () => []
    },
    cryptocurrencies: {
        type: Array,
        default: () => []
    },
    depositLimits: {
        type: Object,
        default: () => ({
            daily: 500000,
            perTransaction: 100000
        })
    },
    todaysTotal: {
        type: Number,
        default: 0
    }
});

const toast = useToast();
const { formatCurrency } = useCurrency();

const form = useForm({
    bank_account_id: null,
    cryptocurrency_id: null,
    crypto_wallet_id: null,
    crypto_amount: null,
    usd_amount: null,
    transaction_hash: ''
});

const selectedCrypto = ref(null);
const selectedWallets = ref([]);
const walletAddresses = ref([]);
const currentStep = ref(0);
const isProcessing = ref(false);
const depositComplete = ref(false);
const showQRCode = ref(false);
const currentExchangeRate = ref(null);
const isConvertingFromUsd = ref(true); // Track which user is editing

const steps = [
    { label: 'Select Account' },
    { label: 'Select Cryptocurrency' },
    { label: 'Wallet Address' },
    { label: 'Enter Amount' },
    { label: 'Transaction Hash' },
    { label: 'Complete' }
];

const remainingDaily = computed(() => {
    return (props.depositLimits.daily - props.todaysTotal) / 100;
});

const maxAmount = computed(() => {
    return Math.min(
        props.depositLimits.perTransaction / 100,
        remainingDaily.value
    );
});

const selectedAccount = computed(() => {
    return props.accounts.find(a => a.id === form.bank_account_id);
});

const selectedCryptoData = computed(() => {
    return props.cryptocurrencies.find(c => c.id === form.cryptocurrency_id);
});

const selectedWalletData = computed(() => {
    return walletAddresses.value.find(w => w.id === form.crypto_wallet_id);
});

// Watch USD amount and auto-convert to crypto
watch(() => form.usd_amount, (newValue) => {
    if (isConvertingFromUsd.value && newValue && currentExchangeRate.value) {
        form.crypto_amount = parseFloat((newValue / currentExchangeRate.value).toFixed(8));
    }
});

// Watch crypto amount and auto-convert to USD
watch(() => form.crypto_amount, (newValue) => {
    if (!isConvertingFromUsd.value && newValue && currentExchangeRate.value) {
        form.usd_amount = parseFloat((newValue * currentExchangeRate.value).toFixed(2));
    }
});

const getCryptoIcon = (symbol) => {
    const icons = {
        'BTC': 'pi-bitcoin',
        'ETH': 'pi-wallet',
        'USDT': 'pi-dollar',
        'USDC': 'pi-dollar',
        'BNB': 'pi-wallet',
        'XRP': 'pi-wallet',
        'ADA': 'pi-wallet',
        'DOGE': 'pi-wallet',
    };
    return icons[symbol] || 'pi-money-bill';
};

const isFormValid = computed(() => {
    return form.bank_account_id &&
        form.cryptocurrency_id &&
        form.crypto_wallet_id &&
        form.crypto_amount &&
        form.usd_amount &&
        form.usd_amount > 0 &&
        form.usd_amount <= maxAmount.value &&
        form.transaction_hash &&
        form.transaction_hash.trim().length > 0;
});

const nextStep = () => {
    if (currentStep.value < 4 && validateStep()) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 0) {
        currentStep.value--;
    }
};

const validateStep = () => {
    if (currentStep.value === 0) {
        return !!form.bank_account_id;
    } else if (currentStep.value === 1) {
        return !!form.cryptocurrency_id;
    } else if (currentStep.value === 2) {
        return !!form.crypto_wallet_id;
    } else if (currentStep.value === 3) {
        return form.crypto_amount && form.usd_amount && form.usd_amount > 0 && form.usd_amount <= maxAmount.value;
    }
    return true;
};

const selectCryptocurrency = async (cryptoId) => {
    form.cryptocurrency_id = cryptoId;
    form.crypto_wallet_id = null;
    form.usd_amount = null;
    form.crypto_amount = null;
    walletAddresses.value = [];

    try {
        const response = await fetch(route('deposits.crypto.wallet', { cryptocurrency: cryptoId }));
        const data = await response.json();
        walletAddresses.value = data.wallets || [];
        selectedCrypto.value = selectedCryptoData.value;
        
        // Set exchange rate from response or props
        currentExchangeRate.value = data.cryptocurrency?.exchange_rate || selectedCryptoData.value?.exchange_rate;
        
        if (!currentExchangeRate.value) {
            toast.add({
                severity: 'warn',
                summary: 'Exchange Rate Unavailable',
                detail: 'Please contact support for current rates',
                life: 4000
            });
        }
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Unable to load wallet addresses',
            life: 3000
        });
    }
};

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text).then(() => {
        toast.add({
            severity: 'success',
            summary: 'Copied',
            detail: 'Wallet address copied to clipboard',
            life: 2000
        });
    });
};

const submitDeposit = () => {
    if (!isFormValid.value) {
        toast.add({
            severity: 'error',
            summary: 'Invalid Crypto Deposit',
            detail: 'Please verify all deposit details',
            life: 3000
        });
        return;
    }

    isProcessing.value = true;

    form.post(route('deposits.crypto.register'), {
        preserveScroll: true,
        onSuccess: (page) => {
            depositComplete.value = true;
            currentStep.value = 5;
            toast.add({
                severity: 'success',
                summary: 'Crypto Deposit Registered',
                detail: 'Your transaction is pending verification',
                life: 5000
            });
        },
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Deposit Failed',
                detail: errors.transaction_hash || errors.usd_amount || 'Unable to register deposit',
                life: 5000
            });
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const startNewDeposit = () => {
    form.reset();
    currentStep.value = 0;
    depositComplete.value = false;
    selectedCrypto.value = null;
    walletAddresses.value = [];
    showQRCode.value = false;
};
</script>

<template>
    <Head title="Crypto Deposit" />

    <DashboardLayout title="Crypto Deposit">
        <div class="max-w-3xl mx-auto">
            <Link
                href="/deposits"
                class="inline-flex items-center gap-2 mb-6 text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            >
                <i class="pi pi-arrow-left"></i>
                Back to Deposits
            </Link>

            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Cryptocurrency Deposit
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Deposit Bitcoin, Ethereum, and other cryptocurrencies
                </p>
            </div>
        </div>

        <!-- Deposit Form -->
        <div class="max-w-3xl mx-auto">
            <!-- Steps -->
            <Card v-if="!depositComplete" class="mb-6">
                <template #content>
                    <div class="flex items-center justify-between overflow-x-auto pb-2">
                        <div
                            v-for="(step, index) in steps"
                            :key="index"
                            class="flex items-center flex-shrink-0"
                            :class="{ 'flex-1': index < steps.length - 1 }"
                        >
                            <div
                                class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold transition-all flex-shrink-0"
                                :class="[
                                    index === currentStep
                                        ? 'bg-orange-500 text-white'
                                        : index < currentStep
                                        ? 'bg-green-500 text-white'
                                        : 'bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-300'
                                ]"
                            >
                                {{ index + 1 }}
                            </div>
                            <div
                                v-if="index < steps.length - 1"
                                class="h-1 mx-2 transition-all flex-1"
                                :class="[
                                    index < currentStep
                                        ? 'bg-green-500'
                                        : 'bg-gray-300 dark:bg-gray-600'
                                ]"
                            ></div>
                        </div>
                    </div>
                    <p class="mt-3 text-sm font-medium text-center text-gray-600 dark:text-gray-400">
                        {{ steps[currentStep].label }}
                    </p>
                </template>
            </Card>

            <!-- Step 0: Select Account -->
            <Card v-if="currentStep === 0 && !depositComplete" class="shadow-lg">
                <template #title>
                    <div class="flex items-center gap-2 text-lg">
                        <i class="pi pi-wallet text-primary-500"></i>
                        Select Deposit Account
                    </div>
                </template>
                <template #content>
                    <div class="space-y-4">
                        <div
                            v-for="account in props.accounts"
                            :key="account.id"
                            @click="form.bank_account_id = account.id"
                            class="p-4 transition-all duration-300 border-2 rounded-lg cursor-pointer"
                            :class="[
                                form.bank_account_id === account.id
                                    ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex items-center justify-center w-10 h-10 rounded-full transition-all"
                                        :class="[
                                            form.bank_account_id === account.id
                                                ? 'bg-green-500 text-white'
                                                : 'bg-gray-100 dark:bg-gray-700 text-gray-500'
                                        ]"
                                    >
                                        <i
                                            class="pi"
                                            :class="[
                                                form.bank_account_id === account.id
                                                    ? 'pi-check'
                                                    : 'pi-wallet'
                                            ]"
                                        ></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ account.account_type?.name }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            ****{{ account.account_number.slice(-4) }}
                                        </p>
                                    </div>
                                </div>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ formatCurrency(account.balance, account.currency) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <Link href="/deposits">
                                <Button label="Cancel" outlined />
                            </Link>
                            <Button
                                label="Continue"
                                icon="pi pi-arrow-right"
                                :disabled="!form.bank_account_id"
                                @click="nextStep"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Step 1: Select Cryptocurrency -->
            <Card v-if="currentStep === 1 && !depositComplete" class="shadow-lg">
                <template #title>
                    <div class="flex items-center gap-2 text-lg">
                        <i class="pi pi-coins text-primary-500"></i>
                        Select Cryptocurrency
                    </div>
                </template>
                <template #content>
                    <div class="space-y-3">
                        <div
                            v-for="crypto in props.cryptocurrencies"
                            :key="crypto.id"
                            @click="selectCryptocurrency(crypto.id)"
                            class="p-4 transition-all duration-300 border-2 rounded-lg cursor-pointer"
                            :class="[
                                form.cryptocurrency_id === crypto.id
                                    ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20'
                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'
                            ]"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex items-center justify-center w-12 h-12 rounded-full transition-all flex-shrink-0"
                                    :class="[
                                        form.cryptocurrency_id === crypto.id
                                            ? 'bg-orange-500 text-white'
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-500'
                                    ]"
                                >
                                    <i class="text-lg pi" :class="getCryptoIcon(crypto.symbol)"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        {{ crypto.name }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ crypto.symbol }} • {{ crypto.network }}
                                    </p>
                                </div>
                                <div
                                    v-if="form.cryptocurrency_id === crypto.id"
                                    class="flex items-center justify-center w-6 h-6 rounded-full bg-orange-500 text-white"
                                >
                                    <i class="pi pi-check text-sm"></i>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <Link href="/deposits">
                                <Button label="Cancel" outlined />
                            </Link>
                            <Button
                                label="Continue"
                                icon="pi pi-arrow-right"
                                :disabled="!form.cryptocurrency_id"
                                @click="nextStep"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Step 2: Wallet Address -->
            <Card v-if="currentStep === 2 && !depositComplete" class="shadow-lg">
                <template #title>
                    <div class="flex items-center gap-2 text-lg">
                        <i class="pi pi-wallet text-primary-500"></i>
                        {{ selectedCrypto?.name }} Wallet Address
                    </div>
                </template>
                <template #content>
                    <div class="space-y-4">
                        <Message severity="info" :closable="false">
                            <p class="text-sm">Select or copy the wallet address to send your {{ selectedCrypto?.symbol }} to this address.</p>
                        </Message>

                        <div class="space-y-3">
                            <div
                                v-for="wallet in walletAddresses"
                                :key="wallet.id"
                                @click="form.crypto_wallet_id = wallet.id"
                                class="p-4 transition-all duration-300 border-2 rounded-lg cursor-pointer"
                                :class="[
                                    form.crypto_wallet_id === wallet.id
                                        ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'
                                ]"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ wallet.label }}
                                        </p>
                                        <p class="mt-2 p-2 rounded bg-gray-100 dark:bg-gray-800 text-xs font-mono text-gray-600 dark:text-gray-300 break-all">
                                            {{ wallet.wallet_address }}
                                        </p>
                                        <Button
                                            label="Copy Address"
                                            icon="pi pi-copy"
                                            text
                                            size="small"
                                            class="mt-2"
                                            @click.stop="copyToClipboard(wallet.wallet_address)"
                                        />
                                    </div>
                                    <div
                                        v-if="form.crypto_wallet_id === wallet.id"
                                        class="flex items-center justify-center w-6 h-6 rounded-full bg-green-500 text-white flex-shrink-0"
                                    >
                                        <i class="pi pi-check text-sm"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between gap-3 pt-4">
                            <Button label="Back" icon="pi pi-arrow-left" outlined @click="prevStep" />
                            <Button
                                label="Continue"
                                icon="pi pi-arrow-right"
                                :disabled="!form.crypto_wallet_id"
                                @click="nextStep"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Step 3: Enter Amount -->
            <Card v-if="currentStep === 3 && !depositComplete" class="shadow-lg">
                <template #title>
                    <div class="flex items-center gap-2 text-lg">
                        <i class="pi pi-dollar text-primary-500"></i>
                        Enter Amount
                    </div>
                </template>
                <template #content>
                    <div class="space-y-4">
                        <!-- Exchange Rate Display -->
                        <Message v-if="currentExchangeRate" severity="info" :closable="false">
                            <p class="text-sm">
                                <strong>Current Rate:</strong> 1 {{ selectedCrypto?.symbol }} = ${{ currentExchangeRate.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                            </p>
                        </Message>
                        <Message v-else severity="warn" :closable="false">
                            <p class="text-sm">Exchange rate unavailable. Please contact support.</p>
                        </Message>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    USD Amount <span class="text-red-500">*</span>
                                </label>
                                <InputNumber
                                    v-model="form.usd_amount"
                                    @focus="isConvertingFromUsd = true"
                                    mode="currency"
                                    currency="USD"
                                    locale="en-US"
                                    placeholder="0.00"
                                    class="w-full"
                                    :min="1"
                                    :max="maxAmount"
                                    :disabled="!currentExchangeRate"
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    Enter USD amount to auto-calculate crypto
                                </p>
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ selectedCrypto?.symbol }} Amount <span class="text-red-500">*</span>
                                </label>
                                <InputNumber
                                    v-model="form.crypto_amount"
                                    @focus="isConvertingFromUsd = false"
                                    placeholder="0.00"
                                    class="w-full"
                                    :min="0.00001"
                                    :max-fraction-digits="8"
                                    :disabled="!currentExchangeRate"
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    Auto-calculated based on USD amount
                                </p>
                            </div>
                        </div>

                        <Divider />

                        <div class="text-xs text-gray-500">
                            Max daily: ${{ (depositLimits.daily / 100).toFixed(2) }} | 
                            Max per tx: ${{ (depositLimits.perTransaction / 100).toFixed(2) }} |
                            Available today: {{ formatCurrency(remainingDaily * 100, 'USD') }}
                        </div>

                        <Message v-if="remainingDaily <= 0" severity="error" :closable="false">
                            <p class="text-sm">Daily crypto deposit limit reached</p>
                        </Message>

                        <Message severity="warning" :closable="false">
                            <p class="text-sm"><strong>Important:</strong> Network fees are not included in this amount. You'll need to pay additional fees to the {{ selectedCrypto?.symbol }} network.</p>
                        </Message>

                        <div class="flex justify-between gap-3 pt-4">
                            <Button label="Back" icon="pi pi-arrow-left" outlined @click="prevStep" />
                            <Button
                                label="Continue"
                                icon="pi pi-arrow-right"
                                :disabled="!validateStep()"
                                @click="nextStep"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Step 4: Transaction Hash -->
            <Card v-if="currentStep === 4 && !depositComplete" class="shadow-lg">
                <template #title>
                    <div class="flex items-center gap-2 text-lg">
                        <i class="pi pi-check text-primary-500"></i>
                        Confirm Transaction
                    </div>
                </template>
                <template #content>
                    <div class="space-y-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Blockchain Transaction Hash <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                v-model="form.transaction_hash"
                                placeholder="0x... or txid"
                                class="w-full font-mono text-xs"
                            />
                            <p class="mt-2 text-xs text-gray-500">
                                Paste the transaction hash from your wallet or block explorer
                            </p>
                        </div>

                        <Divider />

                        <div class="space-y-3 p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Cryptocurrency</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ selectedCrypto?.symbol }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Crypto Amount</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ form.crypto_amount }} {{ selectedCrypto?.symbol }}</span>
                            </div>
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-3 flex justify-between">
                                <span class="text-gray-500">USD Amount</span>
                                <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                    {{ formatCurrency(form.usd_amount * 100, 'USD') }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Wallet</span>
                                <span class="text-sm text-gray-900 dark:text-white font-mono">
                                    ...{{ selectedWalletData?.wallet_address.slice(-8) }}
                                </span>
                            </div>
                        </div>

                        <Message severity="info" :closable="false">
                            <p class="text-sm">Your transaction will be verified by our team. This usually takes 10-30 minutes.</p>
                        </Message>

                        <div class="flex justify-between gap-3 pt-4">
                            <Button label="Back" icon="pi pi-arrow-left" outlined @click="prevStep" />
                            <Button
                                label="Submit Deposit"
                                icon="pi pi-send"
                                :loading="isProcessing"
                                :disabled="!isFormValid"
                                @click="submitDeposit"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Step 5: Complete -->
            <Card v-if="depositComplete" class="shadow-lg">
                <template #content>
                    <div class="py-8 text-center">
                        <div class="flex items-center justify-center w-20 h-20 mx-auto mb-4 rounded-full bg-orange-100 dark:bg-orange-900/30">
                            <i class="text-4xl text-orange-600 pi pi-check dark:text-orange-400"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            Deposit Registered
                        </h3>
                        <p class="mt-2 text-gray-500">
                            Your crypto deposit is pending verification
                        </p>

                        <div class="p-4 mt-6 text-left rounded-lg bg-gray-50 dark:bg-gray-800">
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Cryptocurrency</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ selectedCrypto?.symbol }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Amount</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        {{ form.crypto_amount }} {{ selectedCrypto?.symbol }}
                                    </span>
                                </div>
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-3 flex justify-between">
                                    <span class="text-gray-500">USD Value</span>
                                    <span class="font-bold text-orange-600 dark:text-orange-400">
                                        {{ formatCurrency(form.usd_amount * 100, 'USD') }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Status</span>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200">
                                        Pending Verification
                                    </span>
                                </div>
                            </div>
                        </div>

                        <Message severity="success" :closable="false" class="mt-4">
                            <p class="text-sm">We've received your transaction. Once verified by our team, the funds will be added to your account. You'll receive an email notification.</p>
                        </Message>

                        <div class="flex justify-center gap-3 mt-6">
                            <Button
                                label="New Deposit"
                                icon="pi pi-plus"
                                outlined
                                @click="startNewDeposit"
                            />
                            <Link href="/deposits">
                                <Button label="Back to Deposits" icon="pi pi-arrow-left" />
                            </Link>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </DashboardLayout>
</template>

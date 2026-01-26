<script setup>
/**
 * AccountCard Component
 * Displays a bank account card with balance
 */
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import CurrencyDisplay from '@/Components/Common/CurrencyDisplay.vue';
import { maskAccountNumber } from '@/Utils/formatters';

const props = defineProps({
    account: {
        type: Object,
        required: true,
        // Expected: { id, account_number, account_type, balance, currency, name }
    },
    showActions: {
        type: Boolean,
        default: true,
    },
    compact: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['transfer', 'view']);

const hideBalance = ref(false);

const accountTypeColors = {
    savings: 'from-emerald-500 to-teal-600',
    checking: 'from-indigo-500 to-purple-600',
    business: 'from-amber-500 to-orange-600',
    fixed_deposit: 'from-blue-500 to-cyan-600',
};

const gradientClass = computed(() => {
    return accountTypeColors[props.account.account_type?.toLowerCase()] || accountTypeColors.checking;
});

const accountTypeLabel = computed(() => {
    const types = {
        savings: 'Savings Account',
        checking: 'Checking Account',
        business: 'Business Account',
        fixed_deposit: 'Fixed Deposit',
    };
    return types[props.account.account_type?.toLowerCase()] || 'Account';
});
</script>

<template>
    <div
        :class="[
            'relative overflow-hidden rounded-2xl bg-gradient-to-br text-white shadow-lg transition-transform hover:scale-[1.02]',
            gradientClass,
            compact ? 'p-4' : 'p-6'
        ]"
    >
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <circle cx="80" cy="20" r="40" fill="white" />
                <circle cx="20" cy="80" r="30" fill="white" />
            </svg>
        </div>

        <!-- Content -->
        <div class="relative z-10">
            <!-- Header -->
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-white/80 text-xs font-medium uppercase tracking-wide">
                        {{ accountTypeLabel }}
                    </p>
                    <p v-if="account.name" class="text-white font-semibold mt-0.5">
                        {{ account.name }}
                    </p>
                </div>

                <!-- Hide Balance Toggle -->
                <button
                    @click="hideBalance = !hideBalance"
                    class="p-2 rounded-full hover:bg-white/10 transition-colors"
                    :title="hideBalance ? 'Show balance' : 'Hide balance'"
                >
                    <i :class="hideBalance ? 'pi pi-eye' : 'pi pi-eye-slash'" class="text-sm"></i>
                </button>
            </div>

            <!-- Balance -->
            <div :class="compact ? 'mb-3' : 'mb-6'">
                <p class="text-white/70 text-xs mb-1">Available Balance</p>
                <CurrencyDisplay
                    :amount="account.balance"
                    :currency="account.currency || 'USD'"
                    :hide-balance="hideBalance"
                    :size="compact ? 'lg' : 'xl'"
                    class="text-white"
                />
            </div>

            <!-- Account Number -->
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/70 text-xs">Account Number</p>
                    <p class="font-mono text-sm">
                        {{ maskAccountNumber(account.account_number) }}
                    </p>
                </div>

                <!-- Quick Actions -->
                <div v-if="showActions && !compact" class="flex gap-2">
                    <button
                        @click="emit('transfer', account)"
                        class="px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-xs font-medium transition-colors"
                    >
                        <i class="pi pi-send mr-1"></i>
                        Transfer
                    </button>
                    <Link
                        :href="`/accounts/${account.uuid}`"
                        class="px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-xs font-medium transition-colors"
                    >
                        <i class="pi pi-eye mr-1"></i>
                        View
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

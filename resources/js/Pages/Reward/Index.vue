<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import ProgressBar from 'primevue/progressbar';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    rewards: { type: Object, default: () => ({ data: [] }) },
    stats: { type: Object, default: () => ({}) },
});

const toast = useToast();

const form = useForm({
    points: 100,
    redemption_type: 'cash',
});

const redemptionOptions = [
    { label: 'Cash', value: 'cash' },
    { label: 'Voucher', value: 'voucher' },
    { label: 'Discount', value: 'discount' },
];

const totalEarned = computed(() => props.stats.total_earned ?? 0);
const totalRedeemed = computed(() => props.stats.total_redeemed ?? 0);
const available = computed(() => totalEarned.value - totalRedeemed.value);
const progress = computed(() => (totalEarned.value ? Math.min((available.value / totalEarned.value) * 100, 100) : 0));

const submitRedeem = () => {
    form.post('/rewards/redeem', {
        preserveScroll: true,
        onSuccess: () => toast.add({ severity: 'success', summary: 'Redeemed', detail: 'Redemption submitted.', life: 2500 }),
    });
};
</script>

<template>
    <DashboardLayout title="Rewards">
        <div class="space-y-6">
            <div class="space-y-1">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Rewards</h1>
                <p class="text-gray-600 dark:text-gray-400">Earn and redeem points across Finora experiences.</p>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <Card class="lg:col-span-2">
                    <template #title>Redeem Points</template>
                    <template #content>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="text-sm text-gray-700 dark:text-gray-300">Points</label>
                                <InputNumber v-model="form.points" :min="100" :step="50" class="w-full mt-1" />
                                <p v-if="form.errors.points" class="text-sm text-red-500">{{ form.errors.points }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-700 dark:text-gray-300">Redemption Type</label>
                                <Dropdown v-model="form.redemption_type" :options="redemptionOptions" optionLabel="label" optionValue="value" class="w-full mt-1" />
                                <p v-if="form.errors.redemption_type" class="text-sm text-red-500">{{ form.errors.redemption_type }}</p>
                            </div>
                            <div class="md:col-span-2 flex justify-end">
                                <Button label="Redeem" icon="pi pi-gift" :loading="form.processing" @click="submitRedeem" />
                            </div>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #title>Points</template>
                    <template #content>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Available</span>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ available }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Earned</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ totalEarned }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Redeemed</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ totalRedeemed }}</span>
                            </div>
                            <ProgressBar :value="progress" showValue class="h-2" />
                        </div>
                    </template>
                </Card>
            </div>

            <Card>
                <template #title>Reward Activity</template>
                <template #content>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div v-for="reward in rewards.data" :key="reward.id" class="p-4 border rounded-xl dark:border-gray-700 bg-white dark:bg-gray-800 space-y-2">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ reward.title }}</h3>
                                <Tag :value="reward.status" />
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Points: {{ reward.points }}</p>
                            <p class="text-xs text-gray-500">{{ reward.source || 'Reward activity' }}</p>
                            <p class="text-xs text-gray-500">{{ reward.created_at }}</p>
                        </div>
                    </div>
                    <p v-if="!rewards.data.length" class="text-sm text-gray-600 dark:text-gray-400">No rewards yet.</p>
                </template>
            </Card>
        </div>
    </DashboardLayout>
</template>

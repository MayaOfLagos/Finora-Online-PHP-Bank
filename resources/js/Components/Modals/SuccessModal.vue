<script setup>
/**
 * SuccessModal Component
 * Success state modal with animation
 */
import { ref, watch, onMounted } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Success!',
    },
    message: {
        type: String,
        default: 'Your transaction has been completed successfully.',
    },
    details: {
        type: Array,
        default: () => [],
        // Example: [{ label: 'Amount', value: '$500.00' }, { label: 'Reference', value: 'TXN123456' }]
    },
    primaryAction: {
        type: Object,
        default: () => ({ label: 'Done', href: null }),
    },
    secondaryAction: {
        type: Object,
        default: null,
        // Example: { label: 'Make Another Transfer', href: '/transfers' }
    },
});

const emit = defineEmits(['update:visible', 'primary', 'secondary']);

// Animation state
const showCheck = ref(false);

// Trigger animation when visible
watch(() => props.visible, (visible) => {
    if (visible) {
        showCheck.value = false;
        setTimeout(() => {
            showCheck.value = true;
        }, 100);
    }
});

// Handle primary action
const handlePrimary = () => {
    emit('primary');
    emit('update:visible', false);
};

// Handle secondary action
const handleSecondary = () => {
    emit('secondary');
    emit('update:visible', false);
};
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :closable="false"
        :showHeader="false"
        :style="{ width: '420px' }"
        :pt="{
            root: { class: 'rounded-2xl overflow-hidden' },
            content: { class: 'p-0' },
        }"
        @update:visible="$emit('update:visible', $event)"
    >
        <!-- Success Animation -->
        <div class="bg-gradient-to-b from-green-500 to-green-600 p-8 text-center">
            <div
                :class="[
                    'w-20 h-20 mx-auto bg-white rounded-full flex items-center justify-center',
                    'transition-all duration-500 ease-out',
                    showCheck ? 'scale-100 opacity-100' : 'scale-50 opacity-0'
                ]"
            >
                <svg
                    class="w-10 h-10 text-green-500"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="3"
                        d="M5 13l4 4L19 7"
                        :class="showCheck ? 'animate-draw-check' : ''"
                    />
                </svg>
            </div>

            <h2
                :class="[
                    'text-2xl font-bold text-white mt-4 transition-all duration-500 delay-200',
                    showCheck ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'
                ]"
            >
                {{ title }}
            </h2>
        </div>

        <!-- Content -->
        <div class="p-6 text-center">
            <p class="text-gray-600 mb-6">
                {{ message }}
            </p>

            <!-- Transaction Details -->
            <div v-if="details.length > 0" class="bg-gray-50 rounded-xl p-4 mb-6 text-left">
                <div
                    v-for="(detail, index) in details"
                    :key="index"
                    :class="[
                        'flex justify-between py-2',
                        index < details.length - 1 ? 'border-b border-gray-100' : ''
                    ]"
                >
                    <span class="text-sm text-gray-500">{{ detail.label }}</span>
                    <span class="text-sm font-medium text-gray-900">{{ detail.value }}</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-3">
                <component
                    :is="primaryAction.href ? Link : 'button'"
                    :href="primaryAction.href"
                    class="w-full"
                    @click="!primaryAction.href && handlePrimary()"
                >
                    <Button
                        :label="primaryAction.label"
                        class="w-full"
                        size="large"
                    />
                </component>

                <component
                    v-if="secondaryAction"
                    :is="secondaryAction.href ? Link : 'button'"
                    :href="secondaryAction.href"
                    class="w-full"
                    @click="!secondaryAction.href && handleSecondary()"
                >
                    <Button
                        :label="secondaryAction.label"
                        class="w-full"
                        text
                    />
                </component>
            </div>
        </div>
    </Dialog>
</template>

<style scoped>
@keyframes draw-check {
    0% {
        stroke-dasharray: 100;
        stroke-dashoffset: 100;
    }
    100% {
        stroke-dasharray: 100;
        stroke-dashoffset: 0;
    }
}

.animate-draw-check {
    animation: draw-check 0.5s ease-out forwards;
    animation-delay: 0.3s;
    stroke-dasharray: 100;
    stroke-dashoffset: 100;
}
</style>

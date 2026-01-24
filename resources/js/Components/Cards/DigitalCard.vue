<script setup>
/**
 * DigitalCard Component
 * Display a virtual/physical bank card with card details
 */
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';

const props = defineProps({
    card: {
        type: Object,
        required: true,
    },
    showActions: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['freeze', 'view-details']);

// Card gradient based on type
const cardGradient = computed(() => {
    const gradients = {
        'Platinum': 'from-gray-700 via-gray-600 to-gray-800',
        'Gold': 'from-yellow-600 via-amber-500 to-yellow-700',
        'Business': 'from-slate-700 via-slate-600 to-slate-800',
        'Virtual': 'from-violet-600 via-purple-500 to-indigo-600',
        'default': 'from-indigo-600 via-indigo-500 to-purple-600',
    };

    for (const [key, gradient] of Object.entries(gradients)) {
        if (props.card.card_type?.toLowerCase().includes(key.toLowerCase())) {
            return gradient;
        }
    }

    if (props.card.is_virtual) {
        return gradients['Virtual'];
    }

    return gradients['default'];
});

// Status badge color
const statusColor = computed(() => {
    const colors = {
        active: 'bg-green-500',
        frozen: 'bg-blue-500',
        blocked: 'bg-red-500',
        expired: 'bg-gray-500',
    };
    return colors[props.card.status] || 'bg-gray-500';
});

// Is card usable
const isUsable = computed(() => props.card.status === 'active');
</script>

<template>
    <div class="digital-card">
        <!-- Card Visual -->
        <div
            :class="[
                'relative aspect-[1.586/1] rounded-2xl p-5 sm:p-6 bg-gradient-to-br shadow-lg overflow-hidden',
                cardGradient
            ]"
        >
            <!-- Pattern Overlay -->
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="card-pattern" width="20" height="20" patternUnits="userSpaceOnUse">
                            <circle cx="10" cy="10" r="1" fill="white" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#card-pattern)" />
                </svg>
            </div>

            <!-- Card Content -->
            <div class="relative h-full flex flex-col justify-between">
                <!-- Top Row -->
                <div class="flex items-start justify-between">
                    <!-- Card Type & Status -->
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-white/90 text-xs sm:text-sm font-medium">
                                {{ card.card_type }}
                            </span>
                            <span
                                v-if="card.is_virtual"
                                class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] text-white uppercase tracking-wider"
                            >
                                Virtual
                            </span>
                        </div>
                        <span
                            :class="[
                                'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] text-white',
                                statusColor
                            ]"
                        >
                            <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                            {{ card.status_label }}
                        </span>
                    </div>

                    <!-- Bank Logo -->
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                        <span class="text-white font-bold text-lg sm:text-xl">F</span>
                    </div>
                </div>

                <!-- Chip & Contactless -->
                <div class="flex items-center gap-3 my-2 sm:my-4">
                    <!-- Chip -->
                    <div class="w-10 h-7 sm:w-12 sm:h-9 bg-gradient-to-br from-yellow-300 to-yellow-500 rounded-md">
                        <div class="w-full h-full grid grid-cols-3 gap-0.5 p-1">
                            <div class="bg-yellow-600/30 rounded-sm"></div>
                            <div class="bg-yellow-600/30 rounded-sm"></div>
                            <div class="bg-yellow-600/30 rounded-sm"></div>
                            <div class="bg-yellow-600/30 rounded-sm"></div>
                            <div class="bg-yellow-600/30 rounded-sm"></div>
                            <div class="bg-yellow-600/30 rounded-sm"></div>
                        </div>
                    </div>
                    <!-- Contactless -->
                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white/70" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" opacity="0.3"/>
                        <path d="M7.5 12c0-2.48 2.02-4.5 4.5-4.5v-2c-3.59 0-6.5 2.91-6.5 6.5s2.91 6.5 6.5 6.5v-2c-2.48 0-4.5-2.02-4.5-4.5z"/>
                        <path d="M12 8c2.21 0 4 1.79 4 4s-1.79 4-4 4v2c3.31 0 6-2.69 6-6s-2.69-6-6-6v2z"/>
                    </svg>
                </div>

                <!-- Card Number -->
                <div class="text-white font-mono text-base sm:text-lg tracking-[0.2em] mb-2 sm:mb-4">
                    {{ card.card_number }}
                </div>

                <!-- Bottom Row -->
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-white/60 text-[10px] uppercase tracking-wider mb-0.5">Card Holder</p>
                        <p class="text-white text-xs sm:text-sm font-medium uppercase tracking-wide">
                            {{ card.card_holder_name }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-white/60 text-[10px] uppercase tracking-wider mb-0.5">Expires</p>
                        <p class="text-white text-xs sm:text-sm font-medium">
                            {{ card.expiry || 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Actions -->
        <div v-if="showActions" class="mt-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <Button
                    v-if="isUsable"
                    icon="pi pi-snowflake"
                    label="Freeze"
                    severity="secondary"
                    size="small"
                    text
                    @click="$emit('freeze', card)"
                />
                <Button
                    v-else-if="card.status === 'frozen'"
                    icon="pi pi-sun"
                    label="Unfreeze"
                    severity="info"
                    size="small"
                    text
                    @click="$emit('freeze', card)"
                />
            </div>
            <Link :href="`/cards/${card.uuid}`">
                <Button
                    icon="pi pi-external-link"
                    label="Details"
                    size="small"
                    text
                    @click="$emit('view-details', card)"
                />
            </Link>
        </div>
    </div>
</template>

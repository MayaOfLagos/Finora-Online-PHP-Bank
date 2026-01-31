<script setup>
/**
 * ReferralWelcomeModal Component
 * Displays welcome message with confetti when user registers via referral link
 * Shows inviter info and potential earnings
 */
import { ref, watch, onMounted, onUnmounted, computed } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Avatar from 'primevue/avatar';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    inviter: {
        type: Object,
        default: null,
        // { name: string, avatar?: string, level?: string }
    },
    bonusAmount: {
        type: Number,
        default: 0,
        // Amount in cents
    },
    bonusEnabled: {
        type: Boolean,
        default: true,
    },
    currency: {
        type: String,
        default: 'USD',
    },
});

const emit = defineEmits(['update:visible', 'continue']);

// Animation states
const showContent = ref(false);
const confettiCanvas = ref(null);
let confettiAnimationId = null;

// Format currency
const formattedBonus = computed(() => {
    if (!props.bonusAmount) return '$0.00';
    const amount = props.bonusAmount / 100;
    const formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: props.currency,
    });
    return formatter.format(amount);
});

// Get inviter initials
const inviterInitials = computed(() => {
    if (!props.inviter?.name) return '?';
    return props.inviter.name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
});

// Confetti animation
const createConfetti = () => {
    if (!confettiCanvas.value) return;
    
    const canvas = confettiCanvas.value;
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    
    const particles = [];
    const colors = ['#4F46E5', '#7C3AED', '#EC4899', '#F59E0B', '#10B981', '#3B82F6'];
    
    // Create particles
    for (let i = 0; i < 150; i++) {
        particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height - canvas.height,
            r: Math.random() * 6 + 2,
            d: Math.random() * 150 + 50,
            color: colors[Math.floor(Math.random() * colors.length)],
            tilt: Math.random() * 10 - 5,
            tiltAngle: Math.random() * Math.PI,
            tiltAngleIncrement: Math.random() * 0.1 + 0.05,
            shape: Math.random() > 0.5 ? 'circle' : 'rect',
        });
    }
    
    const draw = () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        particles.forEach((p, i) => {
            ctx.beginPath();
            ctx.fillStyle = p.color;
            
            if (p.shape === 'circle') {
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            } else {
                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.rotate(p.tiltAngle);
                ctx.fillRect(-p.r, -p.r / 2, p.r * 2, p.r);
                ctx.restore();
            }
            
            ctx.fill();
        });
        
        update();
        confettiAnimationId = requestAnimationFrame(draw);
    };
    
    const update = () => {
        particles.forEach((p, i) => {
            p.y += Math.cos(p.d) + 3;
            p.x += Math.sin(0) * 2;
            p.tiltAngle += p.tiltAngleIncrement;
            p.tilt = Math.sin(p.tiltAngle) * 15;
            
            if (p.y > canvas.height) {
                particles[i] = {
                    ...p,
                    x: Math.random() * canvas.width,
                    y: -10,
                };
            }
        });
    };
    
    draw();
    
    // Stop confetti after 4 seconds
    setTimeout(() => {
        if (confettiAnimationId) {
            cancelAnimationFrame(confettiAnimationId);
            if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
        }
    }, 4000);
};

// Watch visibility
watch(() => props.visible, (visible) => {
    if (visible) {
        showContent.value = false;
        setTimeout(() => {
            showContent.value = true;
            setTimeout(createConfetti, 300);
        }, 100);
    } else {
        if (confettiAnimationId) {
            cancelAnimationFrame(confettiAnimationId);
        }
    }
});

// Cleanup
onUnmounted(() => {
    if (confettiAnimationId) {
        cancelAnimationFrame(confettiAnimationId);
    }
});

const handleContinue = () => {
    emit('continue');
    emit('update:visible', false);
};
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :closable="false"
        :showHeader="false"
        :style="{ width: '480px', maxWidth: '95vw' }"
        :pt="{
            root: { class: 'rounded-3xl overflow-hidden' },
            content: { class: 'p-0' },
            mask: { class: 'backdrop-blur-sm' },
        }"
        @update:visible="$emit('update:visible', $event)"
    >
        <!-- Confetti Canvas (absolute positioned) -->
        <canvas 
            ref="confettiCanvas" 
            class="fixed inset-0 pointer-events-none z-50"
            style="width: 100vw; height: 100vh;"
        ></canvas>
        
        <!-- Modal Content -->
        <div class="relative overflow-hidden">
            <!-- Animated Gradient Header -->
            <div class="relative bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 p-8 text-center">
                <!-- Animated rings -->
                <div class="absolute inset-0 overflow-hidden">
                    <div class="absolute -top-10 -left-10 w-40 h-40 bg-white/10 rounded-full animate-pulse"></div>
                    <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-white/10 rounded-full animate-pulse delay-150"></div>
                </div>
                
                <!-- Gift Icon -->
                <div 
                    class="relative z-10 w-20 h-20 mx-auto mb-4 bg-white rounded-2xl shadow-xl flex items-center justify-center transform transition-all duration-500"
                    :class="showContent ? 'scale-100 rotate-0' : 'scale-50 rotate-12'"
                >
                    <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                    </svg>
                </div>
                
                <!-- Congratulations Text -->
                <h2 
                    class="relative z-10 text-2xl md:text-3xl font-bold text-white mb-2 transition-all duration-500"
                    :class="showContent ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                >
                    🎉 Welcome Bonus!
                </h2>
                <p 
                    class="relative z-10 text-white/80 transition-all duration-500 delay-100"
                    :class="showContent ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                >
                    You've been referred by a friend
                </p>
            </div>
            
            <!-- Content Body -->
            <div class="bg-white dark:bg-gray-800 p-8">
                <!-- Inviter Card -->
                <div 
                    class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-2xl mb-6 transition-all duration-500 delay-200"
                    :class="showContent ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                >
                    <!-- Avatar -->
                    <div class="shrink-0">
                        <Avatar
                            v-if="inviter?.avatar"
                            :image="inviter.avatar"
                            shape="circle"
                            size="large"
                            class="ring-4 ring-indigo-100 dark:ring-indigo-900"
                        />
                        <div 
                            v-else 
                            class="w-14 h-14 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center ring-4 ring-indigo-100 dark:ring-indigo-900"
                        >
                            <span class="text-lg font-bold text-white">{{ inviterInitials }}</span>
                        </div>
                    </div>
                    
                    <!-- Inviter Info -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Invited by</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                            {{ inviter?.name || 'A Friend' }}
                        </p>
                        <p v-if="inviter?.level" class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">
                            {{ inviter.level }} Member
                        </p>
                    </div>
                    
                    <!-- Badge -->
                    <div class="shrink-0">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Verified
                        </span>
                    </div>
                </div>
                
                <!-- Bonus Preview (only if enabled) -->
                <div 
                    v-if="bonusEnabled && bonusAmount > 0"
                    class="text-center p-6 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-2xl border border-indigo-100 dark:border-indigo-800 mb-6 transition-all duration-500 delay-300"
                    :class="showContent ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                >
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Your welcome bonus</p>
                    <p class="text-4xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        {{ formattedBonus }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Will be credited after registration
                    </p>
                </div>
                
                <!-- No Bonus Message -->
                <div 
                    v-else
                    class="text-center p-6 bg-gray-50 dark:bg-gray-700/30 rounded-2xl mb-6 transition-all duration-500 delay-300"
                    :class="showContent ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                >
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300">
                        You're joining through a referral link
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Your friend will receive a reward when you sign up!
                    </p>
                </div>
                
                <!-- Benefits List -->
                <div 
                    class="space-y-3 mb-6 transition-all duration-500 delay-400"
                    :class="showContent ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                >
                    <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
                        <div class="w-6 h-6 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <span>Instant account activation</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
                        <div class="w-6 h-6 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <span>Free bank transfers</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
                        <div class="w-6 h-6 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <span>Earn rewards for inviting friends</span>
                    </div>
                </div>
                
                <!-- Continue Button -->
                <Button
                    label="Continue to Registration"
                    icon="pi pi-arrow-right"
                    iconPos="right"
                    class="w-full justify-center text-base py-3 bg-gradient-to-r from-indigo-600 to-purple-600 border-0 hover:from-indigo-700 hover:to-purple-700 transition-all duration-500 delay-500"
                    :class="showContent ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                    @click="handleContinue"
                />
            </div>
        </div>
    </Dialog>
</template>

<style scoped>
.delay-150 {
    animation-delay: 150ms;
}
</style>

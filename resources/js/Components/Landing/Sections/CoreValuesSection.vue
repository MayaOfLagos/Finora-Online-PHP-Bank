<script setup>
import { ref, onMounted } from 'vue';

const isVisible = ref(false);
const sectionRef = ref(null);

const values = [
    {
        title: 'Integrity',
        description: 'We serve with honesty and excellence, ensuring transparency in every interaction.',
        icon: 'shield'
    },
    {
        title: 'Security',
        description: 'Your money and data are protected with bank-grade encryption and 24/7 monitoring.',
        icon: 'lock'
    },
    {
        title: 'Innovation',
        description: 'We seek opportunities for continuous improvement in how we serve you.',
        icon: 'sparkle'
    }
];

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    isVisible.value = true;
                    observer.disconnect();
                }
            });
        },
        { threshold: 0.2 }
    );
    
    if (sectionRef.value) {
        observer.observe(sectionRef.value);
    }
});
</script>

<template>
    <section ref="sectionRef" class="py-20 lg:py-28 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div 
                class="text-center max-w-2xl mx-auto mb-14"
                :class="isVisible ? 'animate-fade-in' : 'opacity-0'"
            >
                <span class="inline-block text-xs font-semibold tracking-widest text-primary-600 uppercase mb-4">
                    Our Core Values
                </span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900">
                    Built on Trust & Excellence
                </h2>
            </div>

            <!-- Values Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-10 lg:gap-12">
                <div 
                    v-for="(value, index) in values"
                    :key="value.title"
                    class="text-center"
                    :class="isVisible ? 'animate-fade-in' : 'opacity-0'"
                    :style="{ animationDelay: `${(index + 1) * 0.15}s` }"
                >
                    <!-- Icon -->
                    <div class="w-20 h-20 sm:w-18 sm:h-18 lg:w-20 lg:h-20 mx-auto mb-6 bg-white rounded-2xl shadow-sm flex items-center justify-center border border-gray-100">
                        <!-- Shield Icon -->
                        <svg v-if="value.icon === 'shield'" class="w-10 h-10 sm:w-9 sm:h-9 lg:w-10 lg:h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <!-- Lock Icon -->
                        <svg v-else-if="value.icon === 'lock'" class="w-10 h-10 sm:w-9 sm:h-9 lg:w-10 lg:h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <!-- Sparkle Icon -->
                        <svg v-else class="w-10 h-10 sm:w-9 sm:h-9 lg:w-10 lg:h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>

                    <!-- Title -->
                    <h3 class="text-xl font-bold text-gray-900 mb-3">
                        {{ value.title }}
                    </h3>

                    <!-- Description -->
                    <p class="text-gray-600 leading-relaxed">
                        {{ value.description }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.8s ease-out forwards;
}
</style>

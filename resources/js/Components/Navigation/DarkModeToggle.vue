<script setup>
/**
 * DarkModeToggle Component
 * Toggle button for switching between light and dark modes
 */
import { useDarkMode } from '@/Composables/useDarkMode';

const { isDark, toggleDarkMode } = useDarkMode();
</script>

<template>
    <button
        @click="toggleDarkMode"
        class="relative p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors"
        v-tooltip.bottom="isDark ? 'Light Mode' : 'Dark Mode'"
    >
        <!-- Sun Icon (visible in dark mode) -->
        <Transition
            enter-active-class="transition-all duration-300"
            enter-from-class="opacity-0 rotate-90 scale-0"
            enter-to-class="opacity-100 rotate-0 scale-100"
            leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 rotate-0 scale-100"
            leave-to-class="opacity-0 -rotate-90 scale-0"
        >
            <i v-if="isDark" class="pi pi-sun text-xl text-amber-400 absolute inset-0 m-auto w-fit h-fit"></i>
        </Transition>

        <!-- Moon Icon (visible in light mode) -->
        <Transition
            enter-active-class="transition-all duration-300"
            enter-from-class="opacity-0 -rotate-90 scale-0"
            enter-to-class="opacity-100 rotate-0 scale-100"
            leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 rotate-0 scale-100"
            leave-to-class="opacity-0 rotate-90 scale-0"
        >
            <i v-if="!isDark" class="pi pi-moon text-xl absolute inset-0 m-auto w-fit h-fit"></i>
        </Transition>

        <!-- Invisible spacer to maintain button size -->
        <i class="pi pi-sun text-xl invisible"></i>
    </button>
</template>

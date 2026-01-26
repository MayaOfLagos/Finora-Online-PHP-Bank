<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <!-- Mobile Sticky Footer - Only visible on mobile -->
    <div class="fixed bottom-0 left-0 right-0 z-50 md:hidden bg-white border-t border-gray-200 shadow-[0_-4px_20px_rgba(0,0,0,0.1)]">
        <div class="flex items-center justify-between gap-3 px-4 py-3 max-w-lg mx-auto">
            <!-- Login Button -->
            <Link 
                href="/login"
                class="flex-1 inline-flex items-center justify-center px-4 py-3 text-sm font-semibold text-primary-700 bg-primary-50 border-2 border-primary-200 rounded-xl hover:bg-primary-100 transition-all duration-300"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Sign In
            </Link>
            
            <!-- Register Button -->
            <Link 
                href="/register"
                class="flex-1 inline-flex items-center justify-center px-4 py-3 text-sm font-semibold text-white bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl hover:from-primary-700 hover:to-primary-800 transition-all duration-300 shadow-lg shadow-primary-500/30"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Open Account
            </Link>
        </div>
        
        <!-- Safe Area for iPhones with home indicator -->
        <div class="h-safe-area-bottom bg-white"></div>
    </div>
</template>

<style scoped>
/* Safe area for iPhone home indicator */
.h-safe-area-bottom {
    height: env(safe-area-inset-bottom, 0);
}
</style>

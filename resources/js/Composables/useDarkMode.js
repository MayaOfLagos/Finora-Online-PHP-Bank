/**
 * useDarkMode Composable
 * Manages dark mode state with localStorage persistence
 */
import { ref, watch, onMounted } from 'vue';

const isDark = ref(false);

export function useDarkMode() {
    /**
     * Initialize dark mode from localStorage or system preference
     */
    const initDarkMode = () => {
        const stored = localStorage.getItem('darkMode');

        if (stored !== null) {
            isDark.value = stored === 'true';
        } else {
            // Check system preference
            isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
        }

        applyDarkMode();
    };

    /**
     * Apply dark mode to the document
     */
    const applyDarkMode = () => {
        if (isDark.value) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    };

    /**
     * Toggle dark mode
     */
    const toggleDarkMode = () => {
        isDark.value = !isDark.value;
        localStorage.setItem('darkMode', isDark.value.toString());
        applyDarkMode();
    };

    /**
     * Set dark mode explicitly
     */
    const setDarkMode = (value) => {
        isDark.value = value;
        localStorage.setItem('darkMode', value.toString());
        applyDarkMode();
    };

    // Watch for system preference changes
    onMounted(() => {
        initDarkMode();

        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        const handleChange = (e) => {
            if (localStorage.getItem('darkMode') === null) {
                isDark.value = e.matches;
                applyDarkMode();
            }
        };

        mediaQuery.addEventListener('change', handleChange);
    });

    return {
        isDark,
        toggleDarkMode,
        setDarkMode,
        initDarkMode,
    };
}

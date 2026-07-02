import { ref, watch, computed } from 'vue'

const STORAGE_KEY = 'theme'
const theme = ref(localStorage.getItem(STORAGE_KEY) || 'system')

function getSystemPreference() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

function applyTheme(value) {
    const resolved = value === 'system' ? getSystemPreference() : value
    const html = document.documentElement

    if (resolved === 'dark') {
        html.classList.add('dark')
    } else {
        html.classList.remove('dark')
    }
}

// Initialize immediately before Vue mounts
applyTheme(theme.value)

// Watch for system preference changes
if (typeof window !== 'undefined') {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    mediaQuery.addEventListener('change', () => {
        if (theme.value === 'system') {
            applyTheme('system')
        }
    })
}

export function useTheme() {
    watch(theme, (value) => {
        localStorage.setItem(STORAGE_KEY, value)
        applyTheme(value)
    }, { immediate: true })

    const resolvedTheme = computed(() => {
        return theme.value === 'system' ? getSystemPreference() : theme.value
    })

    const isDark = computed(() => resolvedTheme.value === 'dark')

    function setTheme(value) {
        theme.value = value
    }

    function cycleTheme() {
        const modes = ['light', 'dark', 'system']
        const currentIndex = modes.indexOf(theme.value)
        const nextIndex = (currentIndex + 1) % modes.length
        setTheme(modes[nextIndex])
    }

    return {
        theme,
        resolvedTheme,
        isDark,
        setTheme,
        cycleTheme,
    }
}

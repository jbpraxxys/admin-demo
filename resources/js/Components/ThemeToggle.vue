<script setup>
import { useTheme } from '@/composables/useTheme'

const { theme, cycleTheme } = useTheme()
</script>

<template>
    <button
        @click="cycleTheme"
        class="inline-flex items-center gap-2 px-3 py-2 text-sm text-foreground-muted hover:text-foreground rounded-lg transition-colors"
        :title="`Theme: ${theme}`"
    >
        <!-- Sun -->
        <svg
            v-if="theme === 'light'"
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
        >
            <circle cx="12" cy="12" r="5" />
            <path stroke-linecap="round" d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
        </svg>
        <!-- Moon -->
        <svg
            v-else-if="theme === 'dark'"
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
        </svg>
        <!-- System / Auto -->
        <svg
            v-else
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
        >
            <rect x="2" y="3" width="20" height="14" rx="2" ry="2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8m-4-4v4" />
        </svg>
        <span class="text-xs capitalize">{{ theme }}</span>
    </button>
</template>

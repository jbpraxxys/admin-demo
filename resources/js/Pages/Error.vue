<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'

const props = defineProps({ status: Number })

const title = computed(() => ({
    503: 'Service Unavailable',
    500: 'Server Error',
    404: 'Page Not Found',
    403: 'Forbidden',
}[props.status] || 'Error'))

const description = computed(() => ({
    503: 'We are doing some maintenance. Please check back soon.',
    500: 'Whoops, something went wrong on our servers.',
    404: 'Sorry, the page you are looking for could not be found.',
    403: 'Sorry, you are forbidden from accessing this page.',
}[props.status] || 'An unexpected error occurred.'))

function refresh() {
    router.visit(window.location.href, { replace: true })
}
</script>

<template>
    <Head :title="title" />
    <GuestLayout>
        <div class="flex flex-col items-center justify-center text-center max-w-md px-6 py-12">
            <!-- Icon -->
            <div class="mb-8">
                <svg
                    v-if="status === 404"
                    class="w-20 h-20 text-foreground-hint"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM13.5 10.5h-6" />
                </svg>
                <svg
                    v-else-if="status === 403"
                    class="w-20 h-20 text-foreground-hint"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
                <svg
                    v-else
                    class="w-20 h-20 text-foreground-hint"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
            </div>

            <!-- Status Code -->
            <div class="text-7xl font-bold text-foreground-hint/20 mb-4">
                {{ status }}
            </div>

            <h1 class="text-3xl font-semibold text-foreground mb-4">{{ title }}</h1>
            <p class="text-base text-foreground-subtle mb-10">{{ description }}</p>

            <div class="flex items-center gap-4">
                <Link
                    href="/dashboard"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-brand-yellow hover:bg-brand-yellow-dark text-black text-sm font-semibold rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Back to Dashboard
                </Link>
                <button
                    @click="refresh"
                    class="px-6 py-3 text-sm text-foreground-subtle hover:text-foreground border border-surface-border hover:border-foreground-hint rounded-lg transition-colors"
                >
                    Refresh Page
                </button>
            </div>
        </div>
    </GuestLayout>
</template>
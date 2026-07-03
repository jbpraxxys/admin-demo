<script setup>
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
    slug: String,
    projectName: String,
})

const form = useForm({
    password: '',
})

function submit() {
    form.post(`/projects/${props.slug}/login`)
}
</script>

<template>
    <Head :title="`Demo Access - ${projectName}`" />
    <div class="min-h-screen flex items-center justify-center bg-surface-page px-4">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl bg-brand-yellow/10 border border-brand-yellow/20 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-brand-yellow" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <h1 class="text-xl font-semibold text-foreground mb-1">Demo Access</h1>
                <p class="text-sm text-foreground-subtle">Enter the demo password to view <strong class="text-foreground">{{ projectName }}</strong>.</p>
            </div>

            <form @submit.prevent="submit" class="bg-surface-card border border-surface-border rounded-xl p-6 shadow-sm">
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-foreground mb-1.5">Demo Password</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="w-full px-3 py-2 bg-surface-page border border-surface-border rounded-lg text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition-colors"
                        placeholder="Enter password"
                        autofocus
                    />
                    <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-500">{{ form.errors.password }}</p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full px-4 py-2.5 bg-brand-yellow hover:bg-brand-yellow-dark text-black text-sm font-semibold rounded-lg transition-colors disabled:opacity-50"
                >
                    {{ form.processing ? 'Verifying...' : 'Access Demo' }}
                </button>
            </form>
        </div>
    </div>
</template>

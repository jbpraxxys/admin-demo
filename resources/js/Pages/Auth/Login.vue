<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

defineProps({ status: String, canResetPassword: Boolean })

const form = useForm({ email: '', password: '', remember: false })

function submit() {
    form.post('/login', { onFinish: () => form.reset('password') })
}
</script>

<template>
    <GuestLayout>
        <Head title="Sign in" />

        <div class="w-full max-w-sm">
            <!-- Logo -->
            <div class="flex justify-center mb-10">
                <img src="/img/praxxys-logo.webp" alt="PRAXXYS" class="h-9 w-auto" />
            </div>

            <div class="bg-surface-card border border-surface-border rounded-xl p-8 shadow-2xl">
                <h1 class="text-lg font-semibold text-white mb-1">Sign in to Demo Platform</h1>
                <p class="text-sm text-gray-500 mb-6">Enter your credentials to continue</p>

                <div v-if="status" class="mb-4 text-sm text-green-400">{{ status }}</div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wide">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            autocomplete="username"
                            required
                            class="w-full bg-surface-page border border-surface-border rounded-lg px-3.5 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow transition-colors"
                            placeholder="you@praxxys.ph"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-400">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wide">Password</label>
                        <input
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="w-full bg-surface-page border border-surface-border rounded-lg px-3.5 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow transition-colors"
                            placeholder="••••••••"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-xs text-red-400">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.remember" type="checkbox" class="rounded border-surface-border bg-surface-page text-brand-yellow focus:ring-brand-yellow" />
                            <span class="text-xs text-gray-500">Remember me</span>
                        </label>
                        <a v-if="canResetPassword" href="/forgot-password" class="text-xs text-brand-yellow hover:text-brand-yellow-dark transition-colors">Forgot password?</a>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-brand-yellow hover:bg-brand-yellow-dark text-black font-semibold text-sm py-2.5 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Sign in
                    </button>
                </form>
            </div>

            <p class="text-center text-xs text-gray-600 mt-6">PRAXXYS Solutions Inc. — Internal Tools</p>
        </div>
    </GuestLayout>
</template>

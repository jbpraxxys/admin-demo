<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'pm',
})

function submit() {
    form.post('/admin/users')
}
</script>

<template>
    <Head title="Add User" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-white">Add User</h1>
        </template>

        <div class="max-w-md">
            <div class="bg-surface-card border border-surface-border rounded-xl p-6 space-y-5">
                <div>
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full bg-surface-page border border-surface-border rounded-lg px-3.5 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow transition-colors"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-400">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full bg-surface-page border border-surface-border rounded-lg px-3.5 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow transition-colors"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-400">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        class="w-full bg-surface-page border border-surface-border rounded-lg px-3.5 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow transition-colors"
                    />
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-400">{{ form.errors.password }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">Confirm Password</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        class="w-full bg-surface-page border border-surface-border rounded-lg px-3.5 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow transition-colors"
                    />
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">Role</label>
                    <select
                        v-model="form.role"
                        class="w-full bg-surface-page border border-surface-border rounded-lg px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow transition-colors"
                    >
                        <option value="pm">PM</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-1">
                    <a href="/admin/users" class="px-4 py-2.5 text-sm text-gray-500 hover:text-white transition-colors">Cancel</a>
                    <button
                        type="button"
                        @click="submit"
                        :disabled="form.processing"
                        class="px-5 py-2.5 bg-brand-yellow hover:bg-brand-yellow-dark text-black text-sm font-semibold rounded-lg transition-colors disabled:opacity-50"
                    >
                        Create User
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

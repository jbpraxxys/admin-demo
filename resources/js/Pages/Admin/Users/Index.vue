<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

defineProps({ users: Array })

function deleteUser(id) {
    if (!confirm('Delete this user?')) return
    router.delete(`/admin/users/${id}`)
}
</script>

<template>
    <Head title="Users" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h1 class="text-lg font-semibold text-white">Users</h1>
                <Link
                    href="/admin/users/create"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-brand-yellow hover:bg-brand-yellow-dark text-black text-sm font-semibold rounded-lg transition-colors"
                >
                    + Add User
                </Link>
            </div>
        </template>

        <div class="max-w-3xl bg-surface-card border border-surface-border rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-border">
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-gray-500 uppercase tracking-wide">Name</th>
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-gray-500 uppercase tracking-wide">Email</th>
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-gray-500 uppercase tracking-wide">Role</th>
                        <th class="px-6 py-3.5"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="u in users"
                        :key="u.id"
                        class="border-b border-surface-border last:border-0 hover:bg-white/[0.02] transition-colors"
                    >
                        <td class="px-6 py-4 font-medium text-white">{{ u.name }}</td>
                        <td class="px-6 py-4 text-gray-400">{{ u.email }}</td>
                        <td class="px-6 py-4">
                            <span :class="u.role === 'admin' ? 'bg-brand-yellow/10 text-brand-yellow border-brand-yellow/20' : 'bg-blue-500/10 text-blue-400 border-blue-500/20'"
                                  class="px-2 py-1 rounded-md text-xs font-medium border">
                                {{ u.role }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click="deleteUser(u.id)" class="text-xs text-red-500 hover:text-red-400 transition-colors">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>

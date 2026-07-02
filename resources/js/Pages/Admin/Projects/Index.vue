<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import CopyButton from '@/Components/CopyButton.vue'
import { Head, router } from '@inertiajs/vue3'

defineProps({ projects: Array })

function deleteProject(id) {
    if (!confirm('Delete this project? This cannot be undone.')) return
    router.delete(`/admin/projects/${id}`)
}
</script>

<template>
    <Head title="All Projects" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-white">All Projects</h1>
        </template>

        <div class="bg-surface-card border border-surface-border rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-border">
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-gray-500 uppercase tracking-wide">Project</th>
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-gray-500 uppercase tracking-wide">Client</th>
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-gray-500 uppercase tracking-wide">PM</th>
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-gray-500 uppercase tracking-wide">Link</th>
                        <th class="px-6 py-3.5"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="p in projects"
                        :key="p.id"
                        class="border-b border-surface-border last:border-0 hover:bg-white/[0.02] transition-colors"
                    >
                        <td class="px-6 py-4">
                            <div class="font-medium text-white">{{ p.name }}</div>
                            <div class="text-xs text-gray-600 font-mono mt-0.5">{{ p.slug }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-400">{{ p.client_name }}</td>
                        <td class="px-6 py-4 text-gray-500 text-xs">{{ p.creator_name }}</td>
                        <td class="px-6 py-4">
                            <span :class="p.status === 'active' ? 'bg-green-500/10 text-green-400 border-green-500/20' : 'bg-gray-500/10 text-gray-500 border-gray-500/20'"
                                  class="px-2 py-1 rounded-md text-xs font-medium border">
                                {{ p.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <CopyButton :text="p.shareable_link" />
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click="deleteProject(p.id)" class="text-xs text-red-500 hover:text-red-400 transition-colors">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>

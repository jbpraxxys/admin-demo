<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

defineProps({ projects: Array })
</script>

<template>
    <Head title="My Projects" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h1 class="text-lg font-semibold text-foreground">My Projects</h1>
                <Link
                    href="/projects/create"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-brand-yellow hover:bg-brand-yellow-dark text-black text-sm font-semibold rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    New Project
                </Link>
            </div>
        </template>

        <!-- Empty state -->
        <div v-if="!projects?.length" class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-16 h-16 rounded-2xl bg-surface-card border border-surface-border flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-foreground-hint" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
            </div>
            <h3 class="text-foreground font-medium mb-1">No projects yet</h3>
            <p class="text-sm text-foreground-subtle mb-6">Create your first project to get started.</p>
            <Link href="/projects/create" class="px-4 py-2 bg-brand-yellow hover:bg-brand-yellow-dark text-black text-sm font-semibold rounded-lg transition-colors">
                Create Project
            </Link>
        </div>

        <!-- Projects table -->
        <div v-else class="bg-surface-card border border-surface-border rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-border">
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-foreground-hint uppercase tracking-wide">Project</th>
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-foreground-hint uppercase tracking-wide">Client</th>
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-foreground-hint uppercase tracking-wide">Status</th>
                        <th class="px-6 py-3.5 text-xs font-medium text-foreground-hint uppercase tracking-wide text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="p in projects"
                        :key="p.id"
                        class="border-b border-surface-border last:border-0 hover:bg-surface-hover transition-colors"
                    >
                        <td class="px-6 py-4">
                            <div class="font-medium text-foreground">{{ p.name }}</div>
                            <div class="text-xs text-foreground-hint font-mono mt-0.5">{{ p.slug }}</div>
                        </td>
                        <td class="px-6 py-4 text-foreground-muted">{{ p.client_name }}</td>
                        <td class="px-6 py-4">
                            <span :class="p.status === 'active' ? 'bg-green-500/10 text-green-600 dark:text-green-400 border-green-500/20' : 'bg-gray-500/10 text-gray-500 border-gray-500/20'"
                                  class="px-2 py-1 rounded-md text-xs font-medium border">
                                {{ p.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <Link
                                    :href="`/projects/${p.slug}`"
                                    class="p-1.5 rounded-md text-foreground-subtle hover:text-brand-yellow hover:bg-brand-yellow/10 transition-colors"
                                    title="Manage"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </Link>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>

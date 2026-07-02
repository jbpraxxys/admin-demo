<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({ projects: Array })

const modal = ref({
    show: false,
    title: '',
    message: '',
    confirmText: 'Confirm',
    variant: 'danger',
    onConfirm: () => {},
})

function openModal(options) {
    modal.value = {
        show: true,
        title: options.title,
        message: options.message,
        confirmText: options.confirmText || 'Confirm',
        variant: options.variant || 'danger',
        onConfirm: options.onConfirm,
    }
}

function closeModal() {
    modal.value.show = false
}

function handleConfirm() {
    modal.value.onConfirm()
    closeModal()
}

function deleteProject(id) {
    openModal({
        title: 'Delete Project',
        message: 'Are you sure you want to delete this project? This action cannot be undone.',
        confirmText: 'Delete',
        variant: 'danger',
        onConfirm: () => router.delete(`/admin/projects/${id}`),
    })
}

function toggleArchive(project) {
    const action = project.status === 'active' ? 'archive' : 'unarchive'
    openModal({
        title: `${action.charAt(0).toUpperCase() + action.slice(1)} Project`,
        message: `Are you sure you want to ${action} this project?`,
        confirmText: action.charAt(0).toUpperCase() + action.slice(1),
        variant: 'primary',
        onConfirm: () => {
            router.patch(`/projects/${project.slug}`, {
                demo_password: project.demo_password,
                status: project.status === 'active' ? 'archived' : 'active',
            })
        },
    })
}

function copy(text) {
    navigator.clipboard.writeText(text)
}
</script>

<template>
    <Head title="All Projects" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-foreground">All Projects</h1>
        </template>

        <div class="bg-surface-card border border-surface-border rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-border">
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-foreground-hint uppercase tracking-wide">Project</th>
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-foreground-hint uppercase tracking-wide">Client</th>
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-foreground-hint uppercase tracking-wide">PM</th>
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
                        <td class="px-6 py-4 text-foreground-subtle text-xs">{{ p.creator_name }}</td>
                        <td class="px-6 py-4">
                            <span :class="p.status === 'active' ? 'bg-green-500/10 text-green-600 dark:text-green-400 border-green-500/20' : 'bg-gray-500/10 text-gray-500 border-gray-500/20'"
                                  class="px-2 py-1 rounded-md text-xs font-medium border">
                                {{ p.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    @click="copy(p.shareable_link)"
                                    class="p-1.5 rounded-md text-foreground-subtle hover:text-brand-yellow hover:bg-brand-yellow/10 transition-colors"
                                    title="Copy link"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                                <button
                                    @click="toggleArchive(p)"
                                    class="p-1.5 rounded-md text-foreground-subtle hover:text-brand-yellow hover:bg-brand-yellow/10 transition-colors"
                                    :title="p.status === 'active' ? 'Archive' : 'Unarchive'"
                                >
                                    <svg v-if="p.status === 'active'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </button>
                                <button
                                    @click="deleteProject(p.id)"
                                    class="p-1.5 rounded-md text-foreground-subtle hover:text-red-500 hover:bg-red-500/10 transition-colors"
                                    title="Delete"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ConfirmModal
            :show="modal.show"
            :title="modal.title"
            :message="modal.message"
            :confirm-text="modal.confirmText"
            :variant="modal.variant"
            @close="closeModal"
            @confirm="handleConfirm"
        />
    </AuthenticatedLayout>
</template>

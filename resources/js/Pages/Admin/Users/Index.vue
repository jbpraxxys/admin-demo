<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({ users: Array })

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

function deleteUser(id) {
    openModal({
        title: 'Delete User',
        message: 'Are you sure you want to delete this user? This action cannot be undone.',
        confirmText: 'Delete',
        variant: 'danger',
        onConfirm: () => router.delete(`/admin/users/${id}`),
    })
}
</script>

<template>
    <Head title="Users" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h1 class="text-lg font-semibold text-foreground">Users</h1>
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
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-foreground-hint uppercase tracking-wide">Name</th>
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-foreground-hint uppercase tracking-wide">Email</th>
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-foreground-hint uppercase tracking-wide">Role</th>
                        <th class="px-6 py-3.5 text-xs font-medium text-foreground-hint uppercase tracking-wide text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="u in users"
                        :key="u.id"
                        class="border-b border-surface-border last:border-0 hover:bg-surface-hover transition-colors"
                    >
                        <td class="px-6 py-4 font-medium text-foreground">{{ u.name }}</td>
                        <td class="px-6 py-4 text-foreground-muted">{{ u.email }}</td>
                        <td class="px-6 py-4">
                            <span :class="u.role === 'admin' ? 'bg-brand-yellow/10 text-brand-yellow border-brand-yellow/20' : 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20'"
                                  class="px-2 py-1 rounded-md text-xs font-medium border">
                                {{ u.role }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end">
                                <button
                                    @click="deleteUser(u.id)"
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

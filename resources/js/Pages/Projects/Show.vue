<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({ project: Object, files: Array })

const uploading = ref(false)
const fileInput = ref(null)
const folderInput = ref(null)

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

function relativePath(file) {
    const rel = file.webkitRelativePath || file.name
    const parts = rel.split('/').filter(Boolean)
    return parts.join('/')
}

function upload(e) {
    const files = e.target.files
    if (!files.length) return

    uploading.value = true
    const formData = new FormData()
    for (const f of files) {
        formData.append('files[]', f)
        formData.append('paths[]', relativePath(f))
    }

    router.post(`/projects/${props.project.slug}/files`, formData, {
        forceFormData: true,
        onFinish: () => { uploading.value = false; e.target.value = '' },
    })
}

function displayPath(path) {
    const prefix = `projects/${props.project.slug}/`
    return path.startsWith(prefix) ? path.slice(prefix.length) : path
}

function copy(path) {
    const fullUrl = `${window.location.origin}/projects/${props.project.slug}/${displayPath(path)}`
    navigator.clipboard.writeText(fullUrl)
}

function formatSize(bytes) {
    if (bytes === 0) return '0 B'
    const units = ['B', 'KB', 'MB', 'GB', 'TB']
    const i = Math.floor(Math.log(bytes) / Math.log(1024))
    const value = bytes / Math.pow(1024, i)
    return `${value.toFixed(2).replace(/\.00$/, '').replace(/0$/, '')} ${units[i]}`
}

function deleteFile(id, name) {
    openModal({
        title: 'Delete File',
        message: `Are you sure you want to delete "${name}"?`,
        confirmText: 'Delete',
        variant: 'danger',
        onConfirm: () => router.delete(`/projects/${props.project.slug}/files/${id}`),
    })
}

function toggleStatus() {
    const newStatus = props.project.status === 'active' ? 'archived' : 'active'
    const action = newStatus === 'archived' ? 'archive' : 'unarchive'
    openModal({
        title: `${action.charAt(0).toUpperCase() + action.slice(1)} Project`,
        message: `Are you sure you want to ${action} this project?`,
        confirmText: action.charAt(0).toUpperCase() + action.slice(1),
        variant: 'primary',
        onConfirm: () => {
            router.patch(`/projects/${props.project.slug}`, {
                demo_password: props.project.demo_password,
                status: newStatus,
            }, {
                onSuccess: () => {
                    if (newStatus === 'archived') {
                        router.visit('/dashboard')
                    }
                },
            })
        },
    })
}
</script>

<template>
    <Head :title="project.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-3">
                    <div>
                        <h1 class="text-lg font-semibold text-foreground">{{ project.name }}</h1>
                        <p class="text-xs text-foreground-subtle mt-0.5">{{ project.client_name }}</p>
                    </div>
                    <span :class="project.status === 'active' ? 'bg-green-500/10 text-green-600 dark:text-green-400 border-green-500/20' : 'bg-gray-500/10 text-gray-500 border-gray-500/20'"
                          class="px-2 py-1 rounded-md text-xs font-medium border">
                        {{ project.status }}
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        @click="toggleStatus"
                        class="inline-flex items-center gap-2 px-3 py-2 bg-surface-card border border-surface-border hover:border-foreground-hint text-foreground text-sm font-medium rounded-lg transition-colors"
                    >
                        <svg v-if="project.status === 'active'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        {{ project.status === 'active' ? 'Archive' : 'Unarchive' }}
                    </button>
                    <button
                        @click="fileInput.click()"
                        :disabled="uploading"
                        class="inline-flex items-center gap-2 px-3 py-2 bg-surface-card border border-surface-border hover:border-brand-yellow text-foreground text-sm font-medium rounded-lg transition-colors disabled:opacity-50"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Upload Files
                    </button>
                    <button
                        @click="folderInput.click()"
                        :disabled="uploading"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-brand-yellow hover:bg-brand-yellow-dark text-black text-sm font-semibold rounded-lg transition-colors disabled:opacity-50"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        {{ uploading ? 'Uploading…' : 'Upload Folder' }}
                    </button>
                    <input ref="fileInput" type="file" multiple class="hidden" @change="upload" />
                    <input ref="folderInput" type="file" webkitdirectory directory multiple class="hidden" @change="upload" />
                </div>
            </div>
        </template>

        <!-- Files -->
        <div v-if="!files?.length" class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-16 h-16 rounded-2xl bg-surface-card border border-surface-border flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-foreground-hint" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
            </div>
            <h3 class="text-foreground font-medium mb-1">No files uploaded</h3>
            <p class="text-sm text-foreground-subtle">Upload loose files or an entire site folder. Folder structure is preserved.</p>
        </div>

        <div v-else class="bg-surface-card border border-surface-border rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-border">
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-foreground-hint uppercase tracking-wide">File</th>
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-foreground-hint uppercase tracking-wide">Size</th>
                        <th class="px-6 py-3.5 text-xs font-medium text-foreground-hint uppercase tracking-wide text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="f in files"
                        :key="f.path"
                        class="border-b border-surface-border last:border-0 hover:bg-surface-hover transition-colors"
                    >
                        <td class="px-6 py-3.5">
                            <a
                                :href="`/projects/${project.slug}/${displayPath(f.path)}`"
                                target="_blank"
                                class="text-brand-yellow hover:text-brand-yellow-dark transition-colors font-mono text-xs"
                            >
                                {{ displayPath(f.path) }}
                            </a>
                        </td>
                        <td class="px-6 py-3.5 text-foreground-subtle text-xs">{{ formatSize(f.size) }}</td>
                        <td class="px-6 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <a
                                    :href="`/projects/${project.slug}/${displayPath(f.path)}`"
                                    target="_blank"
                                    class="p-1.5 rounded-md text-foreground-subtle hover:text-brand-yellow hover:bg-brand-yellow/10 transition-colors"
                                    title="View"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <button
                                    @click="copy(f.path)"
                                    class="p-1.5 rounded-md text-foreground-subtle hover:text-brand-yellow hover:bg-brand-yellow/10 transition-colors"
                                    title="Copy link"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                                <button
                                    @click="deleteFile(f.id, displayPath(f.path))"
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

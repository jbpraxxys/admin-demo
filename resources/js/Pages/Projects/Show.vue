<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import FileTreeItem from '@/Components/FileTreeItem.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({ project: Object, files: Array })

const uploading = ref(false)
const fileInput = ref(null)
const folderInput = ref(null)

// Modal states
const modal = ref({
    show: false,
    title: '',
    message: '',
    confirmText: 'Confirm',
    variant: 'danger',
    onConfirm: () => {},
})

const editModal = ref({
    show: false,
    file: null,
    content: '',
    loading: false,
    saving: false,
})

const renameModal = ref({
    show: false,
    file: null,
    newName: '',
    errors: {},
})

// Batch selection
const selectedIds = ref(new Set())
const expandedFolders = ref(new Set())

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

function batchDelete() {
    const ids = Array.from(selectedIds.value)
    if (!ids.length) return
    
    openModal({
        title: 'Delete Selected Files',
        message: `Are you sure you want to delete ${ids.length} selected file${ids.length > 1 ? 's' : ''}?`,
        confirmText: 'Delete',
        variant: 'danger',
        onConfirm: () => {
            router.delete(`/projects/${props.project.slug}/files`, {
                data: { ids },
            })
            selectedIds.value.clear()
        },
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

// Tree structure
const fileTree = computed(() => {
    const tree = { type: 'folder', name: 'Root', children: [] }
    
    for (const file of props.files || []) {
        const relPath = displayPath(file.path)
        const parts = relPath.split('/').filter(Boolean)
        
        let current = tree
        for (let i = 0; i < parts.length; i++) {
            const part = parts[i]
            const isLast = i === parts.length - 1
            
            if (isLast) {
                current.children.push({
                    type: 'file',
                    name: part,
                    path: relPath,
                    fullPath: file.path,
                    id: file.id,
                    size: file.size,
                })
            } else {
                let folder = current.children.find(c => c.type === 'folder' && c.name === part)
                if (!folder) {
                    folder = {
                        type: 'folder',
                        name: part,
                        path: parts.slice(0, i + 1).join('/'),
                        children: [],
                        expanded: false,
                    }
                    current.children.push(folder)
                }
                current = folder
            }
        }
    }
    
    // Sort: folders first, then files, alphabetically
    function sortChildren(node) {
        node.children.sort((a, b) => {
            if (a.type !== b.type) {
                return a.type === 'folder' ? -1 : 1
            }
            return a.name.localeCompare(b.name)
        })
        node.children.forEach(c => {
            if (c.type === 'folder') sortChildren(c)
        })
    }
    sortChildren(tree)
    
    return tree.children
})

function toggleFolder(path) {
    if (expandedFolders.value.has(path)) {
        expandedFolders.value.delete(path)
    } else {
        expandedFolders.value.add(path)
    }
}

function isExpanded(path) {
    return expandedFolders.value.has(path)
}

function toggleSelect(id) {
    if (selectedIds.value.has(id)) {
        selectedIds.value.delete(id)
    } else {
        selectedIds.value.add(id)
    }
}

function isSelected(id) {
    return selectedIds.value.has(id)
}

function getSelectedCount() {
    return selectedIds.value.size
}

function collectAllFileIds(nodes) {
    const ids = []
    for (const node of nodes) {
        if (node.type === 'file') {
            ids.push(node.id)
        } else if (node.type === 'folder' && isExpanded(node.path)) {
            ids.push(...collectAllFileIds(node.children))
        }
    }
    return ids
}

function selectAllVisible() {
    const allIds = collectAllFileIds(fileTree.value)
    
    if (selectedIds.value.size === allIds.length && allIds.length > 0) {
        selectedIds.value.clear()
    } else {
        selectedIds.value = new Set(allIds)
    }
}

function getAllVisibleIds() {
    return collectAllFileIds(fileTree.value)
}

function allVisibleSelected() {
    const allIds = getAllVisibleIds()
    return allIds.length > 0 && selectedIds.value.size === allIds.length
}

function someVisibleSelected() {
    const allIds = getAllVisibleIds()
    return selectedIds.value.size > 0 && selectedIds.value.size < allIds.length
}

// Edit file
async function openEditModal(fileNode) {
    editModal.value = {
        show: true,
        file: fileNode,
        content: '',
        loading: true,
        saving: false,
    }
    
    try {
        const response = await fetch(`/projects/${props.project.slug}/files/${fileNode.id}/content`)
        const data = await response.json()
        editModal.value.content = data.content
    } catch (e) {
        console.error('Failed to load file content:', e)
    } finally {
        editModal.value.loading = false
    }
}

function closeEditModal() {
    editModal.value.show = false
}

async function saveFileContent() {
    editModal.value.saving = true
    
    try {
        const response = await fetch(`/projects/${props.project.slug}/files/${editModal.value.file.id}/content`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ content: editModal.value.content }),
        })
        
        if (response.ok) {
            closeEditModal()
            router.reload({ only: ['files'] })
        }
    } catch (e) {
        console.error('Failed to save file:', e)
    } finally {
        editModal.value.saving = false
    }
}

// Rename file
function openRenameModal(fileNode) {
    renameModal.value = {
        show: true,
        file: fileNode,
        newName: fileNode.name,
        errors: {},
    }
}

function closeRenameModal() {
    renameModal.value.show = false
}

function submitRename() {
    router.patch(`/projects/${props.project.slug}/files/${renameModal.value.file.id}/rename`, {
        new_name: renameModal.value.newName,
    }, {
        onSuccess: () => closeRenameModal(),
        onError: (errors) => {
            renameModal.value.errors = errors
        },
    })
}

function copy(path) {
    const fullUrl = `${window.location.origin}/projects/${props.project.slug}/${displayPath(path)}`
    navigator.clipboard.writeText(fullUrl)
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
            <!-- Batch actions bar -->
            <div class="flex items-center justify-between px-6 py-3 border-b border-surface-border bg-surface-hover/50">
                <div class="flex items-center gap-3">
                    <input
                        type="checkbox"
                        class="w-4 h-4 rounded border-surface-border text-brand-yellow focus:ring-brand-yellow bg-surface-page"
                        :checked="allVisibleSelected()"
                        :indeterminate="someVisibleSelected()"
                        @change="selectAllVisible"
                    />
                    <span v-if="getSelectedCount() > 0" class="text-sm text-foreground">
                        {{ getSelectedCount() }} selected
                    </span>
                </div>
                <button
                    v-if="getSelectedCount() > 0"
                    @click="batchDelete"
                    class="inline-flex items-center gap-2 px-3 py-1.5 text-sm text-red-500 hover:text-red-600 hover:bg-red-500/10 rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete Selected
                </button>
            </div>

            <!-- File Tree -->
            <div class="divide-y divide-surface-border">
                <template v-for="node in fileTree" :key="node.path">
                    <!-- Folder -->
                    <div v-if="node.type === 'folder'" class="group">
                        <div
                            @click="toggleFolder(node.path)"
                            class="flex items-center gap-3 px-6 py-3 cursor-pointer hover:bg-surface-hover transition-colors"
                        >
                            <svg
                                class="w-4 h-4 text-brand-yellow transition-transform duration-200"
                                :class="isExpanded(node.path) ? 'rotate-90' : ''"
                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                            <svg class="w-5 h-5 text-brand-yellow" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                            <span class="text-sm font-medium text-foreground">{{ node.name }}</span>
                            <span class="text-xs text-foreground-hint">({{ node.children.length }} item{{ node.children.length !== 1 ? 's' : '' }})</span>
                        </div>
                        
                        <!-- Folder Children -->
                        <div v-if="isExpanded(node.path)" class="bg-surface-page/50">
                            <FileTreeItem
                                :nodes="node.children"
                                :project-slug="project.slug"
                                :expanded-folders="expandedFolders"
                                :selected-ids="selectedIds"
                                :level="1"
                                @toggle-folder="toggleFolder"
                                @toggle-select="toggleSelect"
                                @edit="openEditModal"
                                @rename="openRenameModal"
                                @delete="(payload) => deleteFile(payload.id, payload.name)"
                                @copy="copy"
                            />
                        </div>
                    </div>
                    
                    <!-- Root File -->
                    <div
                        v-else
                        class="flex items-center gap-3 px-6 py-3 hover:bg-surface-hover transition-colors group"
                    >
                        <input
                            type="checkbox"
                            class="w-4 h-4 rounded border-surface-border text-brand-yellow focus:ring-brand-yellow bg-surface-page"
                            :checked="isSelected(node.id)"
                            @change="toggleSelect(node.id)"
                            @click.stop
                        />
                        <svg class="w-5 h-5 text-foreground-hint" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <button
                            @click="openEditModal(node)"
                            class="text-sm font-mono text-foreground hover:text-brand-yellow transition-colors flex-1 text-left"
                        >
                            {{ node.name }}
                        </button>
                        <span class="text-xs text-foreground-subtle">{{ formatSize(node.size) }}</span>
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button
                                @click="openEditModal(node)"
                                class="p-1.5 rounded-md text-foreground-subtle hover:text-brand-yellow hover:bg-brand-yellow/10 transition-colors"
                                title="Edit"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                </svg>
                            </button>
                            <button
                                @click="openRenameModal(node)"
                                class="p-1.5 rounded-md text-foreground-subtle hover:text-brand-yellow hover:bg-brand-yellow/10 transition-colors"
                                title="Rename"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                </svg>
                            </button>
                            <a
                                :href="`/projects/${project.slug}/${node.path}`"
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
                                @click="copy(node.path)"
                                class="p-1.5 rounded-md text-foreground-subtle hover:text-brand-yellow hover:bg-brand-yellow/10 transition-colors"
                                title="Copy link"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                            <button
                                @click="deleteFile(node.id, node.name)"
                                class="p-1.5 rounded-md text-foreground-subtle hover:text-red-500 hover:bg-red-500/10 transition-colors"
                                title="Delete"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="editModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="bg-surface-card border border-surface-border rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 border-b border-surface-border">
                    <div>
                        <h3 class="text-sm font-semibold text-foreground">Edit File</h3>
                        <p class="text-xs text-foreground-hint mt-0.5">{{ editModal.file?.path }}</p>
                    </div>
                    <button @click="closeEditModal" class="p-1.5 rounded-md text-foreground-subtle hover:text-foreground transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex-1 p-4 overflow-hidden">
                    <div v-if="editModal.loading" class="flex items-center justify-center py-12">
                        <svg class="w-8 h-8 animate-spin text-brand-yellow" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <textarea
                        v-else
                        v-model="editModal.content"
                        class="w-full h-full min-h-[400px] px-4 py-3 bg-surface-page border border-surface-border rounded-lg text-foreground text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent resize-none"
                        spellcheck="false"
                    ></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-surface-border">
                    <button
                        @click="closeEditModal"
                        class="px-4 py-2 text-sm text-foreground hover:bg-surface-hover rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="saveFileContent"
                        :disabled="editModal.saving || editModal.loading"
                        class="px-4 py-2 bg-brand-yellow hover:bg-brand-yellow-dark text-black text-sm font-semibold rounded-lg transition-colors disabled:opacity-50 inline-flex items-center gap-2"
                    >
                        <svg v-if="editModal.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ editModal.saving ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Rename Modal -->
        <div v-if="renameModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="bg-surface-card border border-surface-border rounded-xl shadow-xl w-full max-w-md">
                <div class="px-6 py-4 border-b border-surface-border">
                    <h3 class="text-sm font-semibold text-foreground">Rename File</h3>
                </div>
                <div class="p-6">
                    <label class="block text-sm font-medium text-foreground mb-2">New name</label>
                    <input
                        v-model="renameModal.newName"
                        type="text"
                        class="w-full px-3 py-2 bg-surface-page border border-surface-border rounded-lg text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent"
                        @keyup.enter="submitRename"
                    />
                    <p v-if="renameModal.errors.new_name" class="mt-1.5 text-xs text-red-500">{{ renameModal.errors.new_name }}</p>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-surface-border">
                    <button
                        @click="closeRenameModal"
                        class="px-4 py-2 text-sm text-foreground hover:bg-surface-hover rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitRename"
                        class="px-4 py-2 bg-brand-yellow hover:bg-brand-yellow-dark text-black text-sm font-semibold rounded-lg transition-colors"
                    >
                        Rename
                    </button>
                </div>
            </div>
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

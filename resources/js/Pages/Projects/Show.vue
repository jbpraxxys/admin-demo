<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import CopyButton from '@/Components/CopyButton.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({ project: Object, files: Array })

const uploading = ref(false)
const fileInput = ref(null)
const folderInput = ref(null)

// Build the relative path for a file. For folder uploads the browser exposes
// webkitRelativePath which includes the selected root folder (e.g.
// "my-site/css/style.css"); we strip that root so files land directly under
// the project (e.g. "css/style.css") and the demo's index.html sits at
// /projects/{slug}/index.html.
function relativePath(file) {
    const rel = file.webkitRelativePath || file.name
    const parts = rel.split('/').filter(Boolean)
    // webkitRelativePath always starts with the chosen folder name; drop it.
    if (file.webkitRelativePath && parts.length > 1) {
        parts.shift()
    }
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

// Display path relative to the project's public folder.
function displayPath(path) {
    const prefix = `projects/${props.project.slug}/`
    return path.startsWith(prefix) ? path.slice(prefix.length) : path
}

function deleteFile(id, name) {
    if (!confirm(`Delete "${name}"?`)) return
    router.delete(`/projects/${props.project.slug}/files/${id}`)
}
</script>

<template>
    <Head :title="project.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div>
                    <h1 class="text-lg font-semibold text-white">{{ project.name }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">{{ project.client_name }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <CopyButton :text="project.shareable_link" />
                    <button
                        @click="fileInput.click()"
                        :disabled="uploading"
                        class="inline-flex items-center gap-2 px-3 py-2 bg-surface-card border border-surface-border hover:border-brand-yellow text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50"
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
                <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
            </div>
            <h3 class="text-white font-medium mb-1">No files uploaded</h3>
            <p class="text-sm text-gray-500">Upload loose files or an entire site folder. Folder structure is preserved.</p>
        </div>

        <div v-else class="bg-surface-card border border-surface-border rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-border">
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-gray-500 uppercase tracking-wide">File</th>
                        <th class="text-left px-6 py-3.5 text-xs font-medium text-gray-500 uppercase tracking-wide">Size</th>
                        <th class="px-6 py-3.5"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="f in files"
                        :key="f.path"
                        class="border-b border-surface-border last:border-0 hover:bg-white/[0.02] transition-colors"
                    >
                        <td class="px-6 py-3.5">
                            <a
                                :href="`/${f.path}`"
                                target="_blank"
                                class="text-brand-yellow hover:text-brand-yellow-dark transition-colors font-mono text-xs"
                            >
                                {{ displayPath(f.path) }}
                            </a>
                        </td>
                        <td class="px-6 py-3.5 text-gray-500 text-xs">{{ f.size }}</td>
                        <td class="px-6 py-3.5 text-right">
                            <button @click="deleteFile(f.id, displayPath(f.path))" class="text-xs text-red-500 hover:text-red-400 transition-colors">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>

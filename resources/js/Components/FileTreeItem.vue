<script setup>
import { computed } from 'vue'

const props = defineProps({
    nodes: { type: Array, required: true },
    projectSlug: { type: String, required: true },
    expandedFolders: { type: Set, required: true },
    selectedIds: { type: Set, required: true },
    level: { type: Number, default: 0 },
})

const emit = defineEmits([
    'toggle-folder',
    'toggle-folder-select',
    'toggle-select',
    'edit',
    'rename',
    'delete',
    'delete-folder',
    'copy',
])

const indent = computed(() => props.level * 24 + 24)

function isExpanded(path) {
    return props.expandedFolders.has(path)
}

function isSelected(id) {
    return props.selectedIds.has(id)
}

function formatSize(bytes) {
    if (bytes === 0) return '0 B'
    const units = ['B', 'KB', 'MB', 'GB', 'TB']
    const i = Math.floor(Math.log(bytes) / Math.log(1024))
    const value = bytes / Math.pow(1024, i)
    return `${value.toFixed(2).replace(/\.00$/, '').replace(/0$/, '')} ${units[i]}`
}

function getAllFileIds(node) {
    const ids = []
    if (node.type === 'file') {
        ids.push(node.id)
    } else if (node.type === 'folder' && node.children) {
        for (const child of node.children) {
            ids.push(...getAllFileIds(child))
        }
    }
    return ids
}

function folderSelectionState(node) {
    const ids = getAllFileIds(node)
    if (ids.length === 0) return 'empty'
    const selectedCount = ids.filter(id => props.selectedIds.has(id)).length
    if (selectedCount === 0) return 'none'
    if (selectedCount === ids.length) return 'all'
    return 'partial'
}

function folderChecked(node) {
    return folderSelectionState(node) === 'all'
}

function folderIndeterminate(node) {
    return folderSelectionState(node) === 'partial'
}
</script>

<template>
    <template v-for="node in nodes" :key="node.path">
        <!-- Folder -->
        <div v-if="node.type === 'folder'" class="group">
            <div
                class="flex items-center gap-3 py-2.5 hover:bg-surface-hover transition-colors"
                :style="{ paddingLeft: indent + 'px' }"
            >
                <input
                    type="checkbox"
                    class="w-4 h-4 rounded border-surface-border text-brand-yellow focus:ring-brand-yellow bg-surface-page"
                    :checked="folderChecked(node)"
                    :indeterminate.prop="folderIndeterminate(node)"
                    @change="$emit('toggle-folder-select', node)"
                    @click.stop
                />
                <div
                    @click="$emit('toggle-folder', node.path)"
                    class="flex items-center gap-3 cursor-pointer flex-1"
                >
                    <svg
                        class="w-4 h-4 text-brand-yellow transition-transform duration-200"
                        :class="isExpanded(node.path) ? 'rotate-90' : ''"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                    <svg class="w-4 h-4 text-brand-yellow" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    <span class="text-sm font-medium text-foreground">{{ node.name }}</span>
                    <span class="text-xs text-foreground-hint">({{ node.children.length }} item{{ node.children.length !== 1 ? 's' : '' }})</span>
                </div>
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button
                        @click="$emit('delete-folder', node)"
                        class="p-1 rounded-md text-foreground-subtle hover:text-red-500 hover:bg-red-500/10 transition-colors"
                        title="Delete folder"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
            <div v-if="isExpanded(node.path)" class="bg-surface-page/30">
                <FileTreeItem
                    :nodes="node.children"
                    :project-slug="projectSlug"
                    :expanded-folders="expandedFolders"
                    :selected-ids="selectedIds"
                    :level="level + 1"
                    @toggle-folder="$emit('toggle-folder', $event)"
                    @toggle-folder-select="$emit('toggle-folder-select', $event)"
                    @toggle-select="$emit('toggle-select', $event)"
                    @edit="$emit('edit', $event)"
                    @rename="$emit('rename', $event)"
                    @delete="$emit('delete', $event)"
                    @delete-folder="$emit('delete-folder', $event)"
                    @copy="$emit('copy', $event)"
                />
            </div>
        </div>
        
        <!-- File -->
        <div
            v-else
            class="flex items-center gap-3 py-2.5 hover:bg-surface-hover transition-colors group"
            :style="{ paddingLeft: indent + 'px' }"
        >
            <input
                type="checkbox"
                class="w-4 h-4 rounded border-surface-border text-brand-yellow focus:ring-brand-yellow bg-surface-page"
                :checked="isSelected(node.id)"
                @change="$emit('toggle-select', node.id)"
                @click.stop
            />
            <svg class="w-4 h-4 text-foreground-hint" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            <button
                @click="$emit('edit', node)"
                class="text-sm font-mono text-foreground hover:text-brand-yellow transition-colors flex-1 text-left"
            >
                {{ node.name }}
            </button>
            <span class="text-xs text-foreground-subtle">{{ formatSize(node.size) }}</span>
            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button
                    @click="$emit('edit', node)"
                    class="p-1 rounded-md text-foreground-subtle hover:text-brand-yellow hover:bg-brand-yellow/10 transition-colors"
                    title="Edit"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                </button>
                <button
                    @click="$emit('rename', node)"
                    class="p-1 rounded-md text-foreground-subtle hover:text-brand-yellow hover:bg-brand-yellow/10 transition-colors"
                    title="Rename"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                    </svg>
                </button>
                <a
                    :href="`/projects/${projectSlug}/${node.path}`"
                    target="_blank"
                    class="p-1 rounded-md text-foreground-subtle hover:text-brand-yellow hover:bg-brand-yellow/10 transition-colors"
                    title="View"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </a>
                <button
                    @click="$emit('copy', node.path)"
                    class="p-1 rounded-md text-foreground-subtle hover:text-brand-yellow hover:bg-brand-yellow/10 transition-colors"
                    title="Copy link"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </button>
                <button
                    @click="$emit('delete', { id: node.id, name: node.name })"
                    class="p-1 rounded-md text-foreground-subtle hover:text-red-500 hover:bg-red-500/10 transition-colors"
                    title="Delete"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </div>
    </template>
</template>

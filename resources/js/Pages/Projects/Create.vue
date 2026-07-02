<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const form = useForm({
    name: '',
    client_name: '',
    slug: '',
    demo_password: '',
    description: '',
    status: 'active',
})

// Auto-suggest a slug from the project name until the user edits it manually.
const slugTouched = ref(false)
watch(() => form.name, (value) => {
    if (slugTouched.value) return
    form.slug = (value || '')
        .toLowerCase()
        .replace(/[^a-z0-9_-]+/g, '-')
        .replace(/^-+|-+$/g, '')
})

function submit() {
    form.post('/projects')
}
</script>

<template>
    <Head title="New Project" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-foreground">New Project</h1>
        </template>

        <div class="max-w-lg">
            <div class="bg-surface-card border border-surface-border rounded-xl p-6 space-y-5">
                <div>
                    <label class="block text-xs font-medium text-foreground-muted uppercase tracking-wide mb-1.5">Project Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full bg-surface-page border border-surface-border rounded-lg px-3.5 py-2.5 text-sm text-foreground placeholder-foreground-hint focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow transition-colors"
                        placeholder="Client Website Redesign"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-500 dark:text-red-400">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-foreground-muted uppercase tracking-wide mb-1.5">Client Name</label>
                    <input
                        v-model="form.client_name"
                        type="text"
                        required
                        class="w-full bg-surface-page border border-surface-border rounded-lg px-3.5 py-2.5 text-sm text-foreground placeholder-foreground-hint focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow transition-colors"
                        placeholder="Acme Corp"
                    />
                    <p v-if="form.errors.client_name" class="mt-1 text-xs text-red-500 dark:text-red-400">{{ form.errors.client_name }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-foreground-muted uppercase tracking-wide mb-1.5">Slug <span class="normal-case text-foreground-hint">(URL identifier)</span></label>
                    <input
                        v-model="form.slug"
                        @input="slugTouched = true"
                        type="text"
                        required
                        pattern="[a-z0-9_-]+"
                        class="w-full bg-surface-page border border-surface-border rounded-lg px-3.5 py-2.5 text-sm text-foreground placeholder-foreground-hint focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow transition-colors"
                        placeholder="my-project"
                    />
                    <p class="mt-1 text-xs text-foreground-hint">Used in the demo URL. Lowercase letters, numbers, hyphens and underscores only.</p>
                    <p v-if="form.errors.slug" class="mt-1 text-xs text-red-500 dark:text-red-400">{{ form.errors.slug }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-foreground-muted uppercase tracking-wide mb-1.5">Demo Password</label>
                    <input
                        v-model="form.demo_password"
                        type="text"
                        required
                        minlength="4"
                        class="w-full bg-surface-page border border-surface-border rounded-lg px-3.5 py-2.5 text-sm text-foreground placeholder-foreground-hint focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow transition-colors"
                        placeholder="Password clients use to view the demo"
                    />
                    <p v-if="form.errors.demo_password" class="mt-1 text-xs text-red-500 dark:text-red-400">{{ form.errors.demo_password }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-foreground-muted uppercase tracking-wide mb-1.5">Description <span class="normal-case text-foreground-hint">(optional)</span></label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="w-full bg-surface-page border border-surface-border rounded-lg px-3.5 py-2.5 text-sm text-foreground placeholder-foreground-hint focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow transition-colors resize-none"
                        placeholder="Brief description of the project..."
                    />
                </div>

                <div>
                    <label class="block text-xs font-medium text-foreground-muted uppercase tracking-wide mb-1.5">Status</label>
                    <select
                        v-model="form.status"
                        class="w-full bg-surface-page border border-surface-border rounded-lg px-3.5 py-2.5 text-sm text-foreground focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow transition-colors"
                    >
                        <option value="active">Active</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-1">
                    <a href="/dashboard" class="px-4 py-2.5 text-sm text-foreground-subtle hover:text-foreground transition-colors">Cancel</a>
                    <button
                        type="button"
                        @click="submit"
                        :disabled="form.processing"
                        class="px-5 py-2.5 bg-brand-yellow hover:bg-brand-yellow-dark text-black text-sm font-semibold rounded-lg transition-colors disabled:opacity-50"
                    >
                        Create Project
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

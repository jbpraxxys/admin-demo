<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth.user)
const isAdmin = computed(() => user.value?.role === 'admin')

const navLinks = computed(() => {
    const links = [
        { href: '/dashboard', label: 'My Projects', icon: 'M3 7h18M3 12h18M3 17h18' },
        { href: '/projects/create', label: 'New Project', icon: 'M12 4v16m8-8H4' },
    ]
    if (isAdmin.value) {
        links.push(
            { href: '/admin/projects', label: 'All Projects', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
            { href: '/admin/users', label: 'Users', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' },
        )
    }
    return links
})

function isActive(href) {
    return page.url.startsWith(href)
}
</script>

<template>
    <div class="flex min-h-screen bg-surface-page text-white">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 w-60 bg-surface-sidebar border-r border-surface-border flex flex-col z-20">
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-surface-border flex-shrink-0">
                <Link href="/dashboard">
                    <img src="/img/praxxys-logo.webp" alt="PRAXXYS" class="h-7 w-auto" />
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <Link
                    v-for="link in navLinks"
                    :key="link.href"
                    :href="link.href"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                        isActive(link.href)
                            ? 'bg-brand-yellow/10 text-brand-yellow'
                            : 'text-gray-400 hover:bg-surface-card hover:text-white'
                    ]"
                >
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="link.icon" />
                    </svg>
                    {{ link.label }}
                </Link>
            </nav>

            <!-- User footer -->
            <div class="border-t border-surface-border px-4 py-4 flex-shrink-0">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full bg-brand-yellow/20 flex items-center justify-center text-brand-yellow text-xs font-bold uppercase flex-shrink-0">
                        {{ user?.name?.charAt(0) }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-sm font-medium text-white truncate">{{ user?.name }}</div>
                        <div class="text-xs text-gray-500 truncate">{{ user?.email }}</div>
                    </div>
                </div>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="w-full text-left text-xs text-gray-500 hover:text-red-400 transition-colors py-1"
                >
                    Sign out
                </Link>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 ml-60 flex flex-col min-h-screen">
            <!-- Page header (optional slot) -->
            <header v-if="$slots.header" class="h-16 border-b border-surface-border flex items-center px-8">
                <slot name="header" />
            </header>

            <!-- Page body -->
            <main class="flex-1 p-8">
                <slot />
            </main>
        </div>
    </div>
</template>

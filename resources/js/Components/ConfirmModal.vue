<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Confirm',
    },
    message: {
        type: String,
        default: '',
    },
    confirmText: {
        type: String,
        default: 'Confirm',
    },
    cancelText: {
        type: String,
        default: 'Cancel',
    },
    variant: {
        type: String,
        default: 'danger',
        validator: (v) => ['danger', 'primary'].includes(v),
    },
});

const emit = defineEmits(['close', 'confirm']);

const confirmClasses = computed(() => {
    if (props.variant === 'danger') {
        return 'bg-red-600 hover:bg-red-500 text-white';
    }
    return 'bg-brand-yellow hover:bg-brand-yellow-dark text-black';
});

function close() {
    emit('close');
}

function confirm() {
    emit('confirm');
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 overflow-y-auto"
                @click.self="close"
            >
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" />

                <!-- Modal -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <Transition
                        enter-active-class="ease-out duration-300"
                        enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                        leave-active-class="ease-in duration-200"
                        leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                        leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <div
                            v-if="show"
                            class="relative w-full max-w-md overflow-hidden rounded-xl bg-surface-card border border-surface-border shadow-2xl"
                        >
                            <!-- Header -->
                            <div class="px-6 py-4 border-b border-surface-border">
                                <h3 class="text-lg font-semibold text-foreground">
                                    {{ title }}
                                </h3>
                            </div>

                            <!-- Body -->
                            <div class="px-6 py-4">
                                <p class="text-sm text-foreground-subtle">
                                    {{ message }}
                                </p>
                            </div>

                            <!-- Footer -->
                            <div class="px-6 py-4 bg-surface-page border-t border-surface-border flex items-center justify-end gap-3">
                                <button
                                    @click="close"
                                    class="px-4 py-2 text-sm text-foreground-subtle hover:text-foreground border border-surface-border hover:border-foreground-hint rounded-lg transition-colors"
                                >
                                    {{ cancelText }}
                                </button>
                                <button
                                    @click="confirm"
                                    :class="confirmClasses"
                                    class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors"
                                >
                                    {{ confirmText }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

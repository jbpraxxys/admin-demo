<script setup>
import { ref } from 'vue'

const props = defineProps({ text: String })
const copied = ref(false)

function copy() {
  navigator.clipboard.writeText(props.text)
  copied.value = true
  setTimeout(() => (copied.value = false), 2000)
}
</script>

<template>
  <button
    @click="copy"
    class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded border border-gray-300 hover:bg-gray-50 transition"
  >
    {{ copied ? '✓ Copied' : 'Copy' }}
  </button>
</template>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="open"
        class="fixed inset-0 z-40 bg-slate-900/35 backdrop-blur-sm"
        @click="close"
      />
    </Transition>

    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="translate-x-0"
      leave-to-class="translate-x-full"
    >
      <div
        v-if="open"
        class="fixed top-0 right-0 h-full z-50 w-full max-w-[520px] bg-surface shadow-xl flex flex-col"
        role="dialog"
        aria-modal="true"
      >
        <div class="flex items-center justify-between px-6 py-4 border-b border-subtle shrink-0">
          <h2 class="text-lg font-semibold text-main">{{ title }}</h2>
          <button @click="close" class="p-1 rounded hover:bg-canvas text-muted hover:text-main">
            <XIcon class="w-5 h-5" />
          </button>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-4">
          <slot />
        </div>

        <div v-if="$slots.footer" class="shrink-0 px-6 py-4 border-t border-subtle bg-canvas">
          <slot name="footer" />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';
import { X as XIcon } from 'lucide-vue-next';

const props = defineProps<{
  open: boolean;
  title?: string;
}>();

const emit = defineEmits<{
  'update:open': [v: boolean];
  close: [];
}>();

const close = () => {
  emit('update:open', false);
  emit('close');
};

const handleKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Escape') close();
};

onMounted(() => document.addEventListener('keydown', handleKeydown));
onUnmounted(() => document.removeEventListener('keydown', handleKeydown));
</script>
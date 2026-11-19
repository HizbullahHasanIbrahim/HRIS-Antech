<template>
  <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center w-full">
    <!-- Search Input with ⌘F / Ctrl+K -->
    <div class="relative flex-1 w-full sm:w-auto">
      <label for="search" class="sr-only">Cari</label>
      <input
        id="search"
        v-model="searchQuery"
        @input="$emit('update:searchQuery', searchQuery)"
        @keydown.meta.f.prevent="focusSearch"
        @keydown.ctrl.f.prevent="focusSearch"
        placeholder="Cari... (⌘F)"
        class="w-full sm:w-64 px-4 py-2 pl-10 pr-4 bg-surface border border-control rounded-lg text-main placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition-shadow"
      />
      <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
      <kbd v-if="!isMac" class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] px-1.5 py-0.5 bg-canvas text-muted rounded border border-control hidden sm:inline-flex" aria-hidden="true">Ctrl+K</kbd>
      <kbd v-else class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] px-1.5 py-0.5 bg-canvas text-muted rounded border border-control hidden sm:inline-flex" aria-hidden="true">⌘K</kbd>
    </div>

    <!-- Filter Button / Dropdown -->
    <slot name="filters" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

interface Props {
  searchQuery: string;
  placeholder?: string;
}

const props = withDefaults(defineProps<Props>(), {
  searchQuery: '',
  placeholder: 'Cari...',
});

const searchQuery = ref(props.searchQuery);
const isMac = ref(false);

function focusSearch() {
  const input = document.getElementById('search') as HTMLInputElement;
  input?.focus();
}

function handleKeyDown(e: KeyboardEvent) {
  if ((e.metaKey || e.ctrlKey) && (e.key === 'f' || e.key === 'k')) {
    e.preventDefault();
    focusSearch();
  }
}

onMounted(() => {
  isMac.value = navigator.platform.toUpperCase().includes('MAC');
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

defineEmits(['update:searchQuery']);
</script>
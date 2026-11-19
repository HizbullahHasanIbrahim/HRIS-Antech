<template>
  <nav class="flex flex-col space-y-1">
    <!-- Header underline per Guidelines v2.0: garis pemisah tipis memanjang -->
    <div class="flex items-center gap-4 border-b border-subtle pb-2">
      <button
        v-for="(tab, i) in tabs"
        :key="i"
        @click="active = tab.id"
        :class="[
          'flex items-center gap-1 px-3 py-2 text-sm font-medium transition-colors',
          active === tab.id
            ? 'text-accent border-b-2 border-accent'
            : 'text-muted hover:text-main',
        ]"
        aria-pressed="active === tab.id"
      >
        <span>{{ tab.label }}</span>
      </button>
      <div class="flex-1" />
    </div>

    <slot name="content" />
  </nav>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface TabItem {
  id: string;
  label: string;
}

interface Props {
  tabs: TabItem[];
  activeTab?: string;
}

const props = withDefaults(defineProps<Props>(), {
  tabs: () => [],
});

const active = computed({
  get() {
    return props.activeTab ?? props.tabs[0]?.id ?? '';
  },
  set(val: string) {
    emit('update:activeTab', val);
  },
});

const emit = defineEmits(['update:activeTab']);
</script>
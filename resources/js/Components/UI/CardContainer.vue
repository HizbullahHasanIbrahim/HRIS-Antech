<template>
  <div class="bg-canvas border border-subtle rounded-xl p-6 shadow-sm">
    <h3 class="text-lg font-semibold text-main mb-4">{{ title }}</h3>

    <div class="bg-surface  rounded-lg border border-control p-4">
      <slot />
    </div>

    <div v-if="description" class="mt-3 text-sm text-muted">
      {{ description }}
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  title: string;
  description?: string;
}

defineProps<Props>();
</script>
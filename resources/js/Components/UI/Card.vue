<template>
  <div class="bg-canvas  rounded-xl shadow-sm border border-subtle overflow-hidden">
    <!-- Header -->
    <div v-if="header" class="px-6 py-4 border-b border-subtle bg-surface ">
      <h3 class="text-lg font-semibold text-main">{{ header.title }}</h3>
      <p v-if="header.description" class="text-sm text-muted mt-1">{{ header.description }}</p>
    </div>

    <!-- Body -->
    <div class="p-6">
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
interface Header {
  title: string;
  description?: string;
}

interface Props {
  header?: Header;
}

defineProps<Props>();
</script>
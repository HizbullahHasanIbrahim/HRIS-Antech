<template>
  <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
    <h2 class="text-lg font-semibold text-main">
      {{ title }}
    </h2>

    <!-- Checklist Items -->
    <div class="flex flex-col gap-3">
      <div
        v-for="item in items"
        :key="item.id"
        class="flex items-start gap-3 p-3 bg-canvas border border-subtle rounded-lg"
      >
        <input
          type="checkbox"
          :id="'item-' + item.id"
          :checked="item.checked"
          :disabled="!item.checkable"
          @change="$emit('check-item', item.id, !item.checked)"
          :class="[
            'mt-0.5 h-4 w-4 rounded border cursor-pointer transition-colors',
            item.checked
              ? 'bg-status-success border-status-success'
              : item.checkable
              ? 'border-control hover:border-accent'
              : 'bg-canvas cursor-not-allowed border-control',
          ]"
        />
        <div class="flex-1">
          <label :for="'item-' + item.id" class="text-sm font-medium text-main">
            {{ item.label }}
          </label>
          <p v-if="item.description" class="text-xs text-muted mt-0.5">
            {{ item.description }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface ChecklistItem {
  id: string;
  label: string;
  description?: string;
  checked: boolean;
  computable: boolean;
}

interface Props {
  title: string;
  items: ChecklistItem[];
}

defineProps<Props>();
defineEmits(['check-item']);
</script>
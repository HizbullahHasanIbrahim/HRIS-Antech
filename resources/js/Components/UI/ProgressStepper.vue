<template>
  <div class="flex items-center w-full overflow-x-auto pb-2">
    <div
      v-for="(step, i) in steps"
      :key="i"
      class="flex items-center shrink-0"
    >
      <!-- Step Circle -->
      <div class="relative flex flex-col items-center">
        <div
          :class="[
            'w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold transition-colors',
            step.status === 'completed'
              ? 'bg-status-success text-white'
              : step.status === 'active'
              ? 'bg-accent text-white ring-2 ring-accent ring-offset-2'
              : 'bg-canvas text-muted border-2 border-subtle',
          ]"
        >
          <svg v-if="step.status === 'completed'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span v-else>{{ i + 1 }}</span>
        </div>
        <span
          :class="[
            'mt-2 text-[10px] font-medium text-center whitespace-nowrap',
            step.status === 'active' ? 'text-accent' : 'text-muted',
          ]"
        >
          {{ step.label }}
        </span>
      </div>

      <!-- Connector Line -->
      <div
        v-if="i < steps.length - 1"
        :class="[
          'w-12 h-0.5 mx-2 transition-colors',
          step.status === 'completed' ? 'bg-status-success' : 'bg-subtle',
        ]"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
interface Step {
  id: string;
  label: string;
  status: 'completed' | 'active' | 'pending';
}

interface Props {
  steps: Step[];
}

defineProps<Props>();
</script>
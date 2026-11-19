<template>
  <Suspense>
    <template #loading>
      <div class="flex items-center justify-center p-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-accent" />
      </div>
    </template>

    <component
      :is="AsyncChart"
      :options="chartOptions"
      :theme="appliedTheme"
      @theme-changed="onThemeChange"
    />
  </Suspense>
</template>

<script setup lang="ts">
import { defineAsyncComponent, computed, ref } from 'vue';
import { useTheme } from '../Composables/useTheme';

interface Props {
  type: string;
  series: Array<{ name: string; data: number[] }>;
  options?: Record<string, unknown>;
}

const props = withDefaults(defineProps<Props>(), {
  series: () => [],
  options: () => ({}),
});

const { isDark, appliedTheme } = useTheme();
const AsyncChart = defineAsyncComponent(() =>
  import('./ApexChart.vue').catch(() => {
    // Fallback jika komponen gagal dimuat
    return { template: '<div class="p-4 text-center text-muted">Gagal memuat grafik</div>' };
  })
);

const chartOptions = computed(() => ({
  responsive: true,
  animation: {
    animate: isDark.value ? false : true,
  },
  ...props.options,
}));

function onThemeChange() {
  // Trigger re-render atau update chart
  forceUpdate.value = !forceUpdate.value;
}

const forceUpdate = ref(false);
</script>
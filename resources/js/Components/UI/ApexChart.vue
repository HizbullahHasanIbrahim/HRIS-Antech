<template>
  <VueApexCharts
    :options="computedOptions"
    :series="series"
    :type="type"
    :height="height"
  />
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { useTheme } from '../Composables/useTheme';

interface Props {
  type: string;
  series: Array<{ name: string; data: number[] }>;
  options?: Record<string, unknown>;
  theme?: string;
  height?: number | string;
  instantAnimation?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  series: () => [],
  options: () => ({}),
  height: 350,
  instantAnimation: true,
});

const { isDark, appliedTheme } = useTheme();
const chartRef = ref<InstanceType<typeof ApexCharts>>(null);
let chartInstance: ApexCharts | null = null;

const computedOptions = computed(() => {
  return {
    theme: {
      mode: isDark.value ? 'dark' : 'light',
      palette: 'palette1',
      theme: {
        neutral: {
          base: isDark.value ? '#475569' : '#F1F5F9',
          primary: isDark.value ? '#94A3B8' : '#64748B',
        },
      },
    },
    chart: {
      background: isDark.value ? '#121218' : '#FFFFFF',
      foreColor: isDark.value ? '#F1F5F9' : '#1E293B',
      animations: {
        enabled: !props.instantAnimation,
      },
    },
    ...props.options,
    responsive: [
      {
        breakpoint: 480,
        options: {
          chart: {
            width: '100%',
          },
          legend: {
            position: 'bottom',
          },
        },
      },
    ],
  };
});

watch(isDark, () => {
  if (chartInstance) {
    chartInstance.updateOptions(computedOptions.value);
  }
});

watch(appliedTheme, () => {
  if (chartInstance) {
    chartInstance.updateOptions(computedOptions.value);
  }
});

function onMounted() {
  // Chart instance will be created by vue3-apexcharts
}

onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy();
  }
});
</script>
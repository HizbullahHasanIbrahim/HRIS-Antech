<template>
  <span :class="pillClasses">
    <slot name="icon">
      <svg v-if="icon && iconName" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
        <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z" />
      </svg>
    </slot>
    <span :class="labelClasses">{{ label }}</span>
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  category: 'DOSEN' | 'TENDIK' | 'Kontrak' | 'Studi S3' | string;
  size?: 'sm' | 'md' | 'lg';
  icon?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  icon: false,
});

const colorMap = {
  DOSEN: { bg: 'bg-category-dosen-bg', text: 'text-category-dosen' },
  TENDIK: { bg: 'bg-category-tendik-bg', text: 'text-category-tendik' },
  Kontrak: { bg: 'bg-category-kontrak-bg', text: 'text-category-kontrak' },
  'Studi S3': { bg: 'bg-category-studis3-bg', text: 'text-category-studis3' },
};

const sizeMap = {
  sm: 'px-2.5 py-0.5 text-xs',
  md: 'px-3 py-1 text-xs',
  lg: 'px-4 py-1.5 text-sm',
};

const iconName = computed(() => {
  switch (props.category) {
    case 'DOSEN': return 'M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z';
    case 'TENDIK': return 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 a9 9 0 0118 0z';
    case 'Kontrak': return 'M9 12l2 2 4-4M9 12l2-2 4 4';
    case 'Studi S3': return 'M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z';
    default: return '';
  }
});

const pillClasses = computed(() => {
  const style = colorMap[props.category as keyof typeof colorMap] || colorMap.Kontrak;
  return `inline-flex items-center gap-1 ${style.bg} ${style.text} ${sizeMap[props.size]} rounded-full`;
});

const label = computed(() => props.category);

const labelClasses = computed(() => 'font-medium');
</script>
<template>
  <div class="flex items-center gap-2 font-mono text-sm">
    <span v-if="masked" class="tracking-wider">{{ maskedNumber }}</span>
    <span v-else class="tracking-wider">{{ fullNumber }}</span>
    <button
      v-if="canReveal"
      @click="masked = !masked"
      class="text-xs text-accent hover:underline focus:outline-none"
    >
      {{ masked ? 'Lihat' : 'Sembunyikan' }}
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

interface Props {
  number: string | null;
  canReveal?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  number: null,
  canReveal: false,
});

const masked = ref(true);

const fullNumber = computed(() => props.number || '-');
const maskedNumber = computed(() => {
  if (!props.number) return '-';
  const len = props.number.length;
  if (len <= 4) return props.number;
  return '*'.repeat(len - 4) + props.number.slice(-4);
});
</script>
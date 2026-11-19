<template>
  <button
    :type="type"
    :class="buttonClasses"
    :disabled="disabled || loading"
    :aria-busy="loading"
  >
    <slot v-if="loading" class="flex items-center justify-center">
      <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 1 1 10 10A8 8 0 014 12z" />
      </svg>
      Memproses...
    </slot>
    <slot v-else />
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';
interface Props {
  type?: 'button' | 'submit' | 'reset';
  variant?: 'primary' | 'secondary' | 'danger' | 'ghost';
  size?: 'sm' | 'md' | 'lg';
  disabled?: boolean;
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  type: 'button',
  variant: 'primary',
  size: 'md',
  disabled: false,
  loading: false,
});

const buttonClasses = computed(() => {
  const base = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

  const variants = {
    primary: 'bg-brand-primary text-white hover:bg-brand-primary-dark focus:ring-brand-primary',
    secondary: 'bg-white text-main border border-control hover:bg-surface focus:ring-brand-primary',
    danger: 'bg-status-danger text-white hover:bg-status-danger/90 focus:ring-status-danger',
    ghost: 'text-main hover:bg-surface focus:ring-brand-primary',
  };

  const sizes = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2 text-base',
    lg: 'px-6 py-3 text-lg',
  };

  return `${base} ${variants[props.variant]} ${sizes[props.size]}`;
});
</script>
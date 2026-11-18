<template>
  <div class="fixed top-4 right-4 z-50 flex flex-col gap-2 w-80 pointer-events-none">
    <TransitionGroup
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 translate-x-full"
      enter-to-class="opacity-100 translate-x-0"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 translate-x-0"
      leave-to-class="opacity-0 translate-x-full"
      move-class="transition duration-200"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-lg shadow-lg border text-sm"
        :class="toastClasses(toast.type)"
        role="alert"
      >
        <component :is="toastIcon(toast.type)" class="w-5 h-5 mt-0.5 shrink-0" />
        <span class="flex-1">{{ toast.message }}</span>
        <button @click="dismiss(toast.id)" class="shrink-0 opacity-70 hover:opacity-100">
          <XIcon class="w-4 h-4" />
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
  CheckCircle2 as CheckCircle2Icon,
  XCircle as XCircleIcon,
  AlertTriangle as AlertTriangleIcon,
  Info as InfoIcon,
  X as XIcon,
} from 'lucide-vue-next';

type ToastType = 'success' | 'error' | 'warning' | 'info';

interface Toast {
  id: number;
  type: ToastType;
  message: string;
}

let _id = 0;
const toasts = ref<Toast[]>([]);
const page = usePage();

const add = (type: ToastType, message: string) => {
  const id = ++_id;
  toasts.value.push({ id, type, message });
  const ttl = type === 'success' ? 4000 : 7000;
  setTimeout(() => dismiss(id), ttl);
};

const dismiss = (id: number) => {
  const idx = toasts.value.findIndex(t => t.id === id);
  if (idx !== -1) toasts.value.splice(idx, 1);
};

watch(
  () => page.props?.flash as Record<string, string> | undefined,
  (flash) => {
    if (!flash) return;
    if (flash.success) add('success', flash.success);
    if (flash.error) add('error', flash.error);
    if (flash.warning) add('warning', flash.warning);
    if (flash.info) add('info', flash.info);
  },
  { deep: true },
);

const toastClasses = (type: ToastType) => ({
  success: 'bg-status-success-bg border-status-success text-status-success',
  error:   'bg-status-danger-bg border-status-danger text-status-danger-text',
  warning: 'bg-status-warning-bg border-status-warning text-status-warning',
  info:    'bg-status-info-bg border-status-info text-status-info',
}[type]);

const toastIcon = (type: ToastType) => ({
  success: CheckCircle2Icon,
  error:   XCircleIcon,
  warning: AlertTriangleIcon,
  info:    InfoIcon,
}[type]);

defineExpose({ add, dismiss });
</script>
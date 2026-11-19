<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" />
        <div class="relative w-full max-w-sm bg-surface rounded-xl shadow-lg border border-subtle p-6">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-status-warning-bg rounded-full flex items-center justify-center shrink-0">
              <AlertTriangleIcon class="w-5 h-5 text-status-warning" />
            </div>
            <div>
              <h2 class="text-base font-semibold text-main">Konflik Data Terdeteksi</h2>
              <p class="text-sm text-muted">Versi #{{ expectedVersion }}</p>
            </div>
          </div>

          <p class="text-sm text-main mb-5">
            Pengajuan ini baru saja diproses oleh pengguna lain. Data yang Anda lihat sudah tidak aktual.
          </p>

          <div class="flex gap-3">
            <button
              type="button"
              @click="$emit('update:open', false)"
              class="flex-1 px-4 py-2 text-sm font-medium text-main border border-control rounded-lg hover:bg-canvas"
            >
              Tutup
            </button>
            <button
              type="button"
              @click="reload"
              class="flex-1 px-4 py-2 text-sm font-medium text-white bg-brand-primary rounded-lg hover:bg-brand-primary-dark flex items-center justify-center gap-2"
            >
              <RefreshCwIcon class="w-4 h-4" />
              Muat Ulang Data
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { AlertTriangle as AlertTriangleIcon, RefreshCw as RefreshCwIcon } from 'lucide-vue-next';

const props = defineProps<{
  open: boolean;
  expectedVersion?: number;
  only?: string[];
}>();

const emit = defineEmits<{
  'update:open': [v: boolean];
  reloaded: [];
}>();

const reload = () => {
  router.reload({ only: props.only });
  emit('reloaded');
  emit('update:open', false);
};
</script>
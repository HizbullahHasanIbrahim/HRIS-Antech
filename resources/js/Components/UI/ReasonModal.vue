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
      <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
      >
        <div
          class="relative w-full max-w-md bg-surface rounded-xl shadow-lg border border-subtle p-6"
          @click.stop
        >
          <!-- Header -->
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-status-warning-bg rounded-full flex items-center justify-center shrink-0">
              <AlertTriangleIcon class="w-5 h-5 text-status-warning" />
            </div>
            <div>
              <h2 class="text-base font-semibold text-main">{{ title }}</h2>
              <p v-if="description" class="text-sm text-muted">{{ description }}</p>
            </div>
          </div>

          <!-- Reason textarea -->
          <label for="reason-input" class="sr-only">Alasan</label>
          <textarea
            id="reason-input"
            v-model="reason"
            :placeholder="placeholder"
            rows="4"
            class="w-full bg-canvas border border-control rounded-lg p-3 text-main resize-none focus:outline-none focus:ring-2 focus:ring-brand-primary/50 focus:border-brand-primary"
          ></textarea>
          <p v-if="reason.length > 0 && reason.length < 5" class="text-xs text-status-danger mt-1">
            Alasan minimal 5 karakter.
          </p>

          <!-- Footer -->
          <div class="flex gap-3 mt-5">
            <button
              type="button"
              @click="close"
              class="flex-1 px-4 py-2 text-sm font-medium text-main border border-control rounded-lg hover:bg-canvas transition-colors"
            >
              Batal
            </button>
            <button
              type="button"
              :disabled="!canConfirm"
              @click="confirm"
              class="flex-1 px-4 py-2 text-sm font-medium text-white bg-status-danger rounded-lg hover:bg-status-danger/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
                <span v-if="loading" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              {{ confirmLabel }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { AlertTriangle as AlertTriangleIcon } from 'lucide-vue-next';

const props = withDefaults(defineProps<{
  open: boolean;
  title?: string;
  description?: string;
  placeholder?: string;
  confirmLabel?: string;
  loading?: boolean;
}>(), {
  title: 'Konfirmasi',
  description: '',
  placeholder: 'Tuliskan alasan penolakan/pembatalan minimal 5 karakter...',
  confirmLabel: 'Konfirmasi',
  loading: false,
});

const emit = defineEmits<{
  'update:open': [v: boolean];
  confirm: [reason: string];
}>();

const reason = ref<string>('');

const canConfirm = computed(() => reason.value.trim().length >= 5 && !props.loading);

watch(() => props.open, (open) => {
  if (!open) {
    reason.value = '';
  }
});

const close = () => {
  reason.value = '';
  emit('update:open', false);
};

const confirm = () => {
  if (!canConfirm.value) return;
  emit('confirm', reason.value.trim());
};
</script>
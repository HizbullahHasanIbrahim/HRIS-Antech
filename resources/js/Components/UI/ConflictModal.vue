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
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="close" />

        <div class="relative w-full max-w-md bg-white rounded-xl shadow-lg border border-subtle p-6">
          <div class="flex items-start justify-between mb-4">
            <div>
              <h2 class="text-lg font-semibold text-main">{{ title }}</h2>
              <p class="text-sm text-muted mt-1">{{ description }}</p>
            </div>
            <button @click="close" class="p-1 rounded hover:bg-canvas text-muted hover:text-main ml-4">
              <XIcon class="w-5 h-5" />
            </button>
          </div>

          <div class="mb-2">
            <label class="block text-sm font-medium text-main mb-1">
              Alasan <span class="text-status-danger">*</span>
            </label>
            <textarea
              v-model="reason"
              :placeholder="placeholder"
              rows="4"
              class="w-full px-3 py-2 border border-control rounded-lg text-sm text-main focus:ring-2 focus:ring-brand-primary focus:border-brand-primary resize-none"
            />
            <div class="flex justify-between mt-1">
              <p v-if="reason.length > 0 && reason.length < minLength" class="text-xs text-status-danger">
                Minimal {{ minLength }} karakter
              </p>
              <p class="text-xs text-muted ml-auto">{{ reason.length }} / {{ maxLength }}</p>
            </div>
          </div>

          <div class="flex gap-3 mt-6 justify-end">
            <button
              type="button"
              @click="close"
              class="px-4 py-2 text-sm font-medium text-main border border-control rounded-lg hover:bg-canvas"
            >
              Batal
            </button>
            <button
              type="button"
              @click="confirm"
              :disabled="!isValid || loading"
              class="px-4 py-2 text-sm font-medium text-white bg-status-danger rounded-lg hover:bg-status-danger-text disabled:opacity-50 flex items-center gap-2"
            >
              <span v-if="loading" class="animate-spin w-4 h-4 rounded-full border-2 border-white border-t-transparent" />
              {{ confirmLabel }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { X as XIcon } from 'lucide-vue-next';

const props = withDefaults(defineProps<{
  open: boolean;
  title?: string;
  description?: string;
  placeholder?: string;
  confirmLabel?: string;
  minLength?: number;
  maxLength?: number;
  loading?: boolean;
}>(), {
  title: 'Konfirmasi Alasan',
  description: 'Berikan alasan yang jelas untuk tindakan ini.',
  placeholder: 'Tuliskan alasan...',
  confirmLabel: 'Konfirmasi',
  minLength: 10,
  maxLength: 500,
  loading: false,
});

const emit = defineEmits<{
  'update:open': [v: boolean];
  confirm: [reason: string];
  close: [];
}>();

const reason = ref('');

watch(() => props.open, (v) => { if (!v) reason.value = ''; });

const isValid = computed(() => reason.value.trim().length >= props.minLength);

const close = () => {
  emit('update:open', false);
  emit('close');
};

const confirm = () => {
  if (!isValid.value) return;
  emit('confirm', reason.value.trim());
};
</script>
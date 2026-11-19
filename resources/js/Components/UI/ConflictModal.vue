<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-surface rounded-xl border border-subtle p-6 max-w-md w-full">
      <div class="text-center">
        <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-warning/10 flex items-center justify-center">
          <ExclamationTriangleIcon class="w-6 h-6 text-status-warning" />
        </div>
        <h3 class="text-lg font-semibold text-main mb-2">Konflik Data</h3>
        <p class="text-sm text-muted mb-6">
          Data telah diperbarui oleh pengguna lain. Silakan muat ulang halaman untuk mendapatkan versi terbaru.
        </p>
        <button
          @click="reload"
          class="w-full bg-brand-primary text-white py-2 px-4 rounded-lg font-medium hover:bg-brand-primary-dark"
        >
          Muat Ulang Halaman
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { AlertTriangle as ExclamationTriangleIcon } from 'lucide-vue-next';
import { defineProps } from 'vue';

const props = defineProps<{ open: boolean }>();

const reload = () => {
  window.location.reload();
};
</script>
<template>
  <span :class="badgeClasses">
    <LockIcon v-if="isFinal" class="inline-block w-3 h-3 shrink-0" />
    <span>{{ label }}</span>
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Lock as LockIcon } from 'lucide-vue-next';

interface Props {
  status: string;
  finalStatuses?: string[];
}

const props = withDefaults(defineProps<Props>(), {
  finalStatuses: () => ['disetujui', 'ditolak', 'dibatalkan', 'final', 'selesai'],
});

const isFinal = computed(() => props.finalStatuses.includes(props.status?.toLowerCase() || ''));

const statusGroups: Record<string, string[]> = {
  success: ['disetujui', 'diterima', 'lolos', 'final', 'aktif', 'lulus', 'selesai', 'dibuka'],
  warning: ['diajukan', 'proses', 'menunggu', 'diproses', 'berjalan'],
  info:    ['draf', 'diajukan', 'direncanakan', 'dijadwalkan'],
  danger:  ['ditolak', 'gugur', 'tidak_lulus', 'berhenti'],
  neutral: ['dibatalkan', 'ditutup', 'nonaktif', 'pensiun', 'batal', 'belum'],
};

const badgeClasses = computed(() => {
  const s = props.status?.toLowerCase() || '';
  let group = 'neutral';
  for (const [key, statuses] of Object.entries(statusGroups)) {
    if (statuses.includes(s)) {
      group = key;
      break;
    }
  }

  const styles = {
    success: 'bg-status-success-bg text-status-success',
    warning: 'bg-status-warning-bg text-status-warning',
    info:    'bg-status-info-bg text-status-info',
    danger:  'bg-status-danger-bg text-status-danger-text',
    neutral: 'bg-neutral-bg text-neutral',
  }[group];

  return `inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold ${styles}`;
});

const label = computed(() => {
  if (!props.status) return '-';
  const formatted = props.status.replace(/_/g, ' ');
  return formatted.charAt(0).toUpperCase() + formatted.slice(1);
});
</script>
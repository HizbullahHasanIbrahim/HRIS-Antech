<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-main">Dashboard</h1>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-surface rounded-xl p-5 border border-subtle hover:shadow-sm transition-shadow">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-brand-primary/10 rounded-xl flex items-center justify-center text-brand-primary">
            <UsersIcon class="w-6 h-6" />
          </div>
          <div>
            <div class="text-sm text-muted">Total Pegawai</div>
            <div class="text-2xl font-semibold text-main">{{ stats.sdm?.total_pegawai ?? 0 }}</div>
          </div>
        </div>
      </div>
      <div class="bg-surface rounded-xl p-5 border border-subtle hover:shadow-sm transition-shadow">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-status-warning/10 rounded-xl flex items-center justify-center text-status-warning">
            <ClockIcon class="w-6 h-6" />
          </div>
          <div>
            <div class="text-sm text-muted">Menunggu Persetujuan (Cuti)</div>
            <div class="text-2xl font-semibold text-main">{{ stats.cuti?.pending ?? 0 }}</div>
          </div>
        </div>
      </div>
      <div class="bg-surface rounded-xl p-5 border border-subtle hover:shadow-sm transition-shadow">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-status-success/10 rounded-xl flex items-center justify-center text-status-success">
            <CheckCircle2Icon class="w-6 h-6" />
          </div>
          <div>
            <div class="text-sm text-muted">Disetujui Bulan Ini</div>
            <div class="text-2xl font-semibold text-main">{{ stats.cuti?.approved_this_month ?? 0 }}</div>
          </div>
        </div>
      </div>
      <div class="bg-surface rounded-xl p-5 border border-subtle hover:shadow-sm transition-shadow">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-status-info/10 rounded-xl flex items-center justify-center text-status-info">
            <CalendarClockIcon class="w-6 h-6" />
          </div>
          <div>
            <div class="text-sm text-muted">Pensiun Mendatang (12 bln)</div>
            <div class="text-2xl font-semibold text-main">{{ stats.pensiun?.upcoming_12_months ?? 0 }}</div>
          </div>
        </div>
      </div>
    </div>

    <Card :header="{ title: 'Aktivitas Terakhir' }">
      <template #default>
        <div class="space-y-5" v-if="recentActivities && recentActivities.length">
          <div class="flex items-start gap-3" v-for="activity in recentActivities" :key="activity.id">
            <div class="w-2.5 h-2.5 mt-1.5 bg-status-info rounded-full flex-shrink-0" aria-hidden="true"></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm text-main font-medium">{{ activity.action }}</p>
              <p class="text-xs text-muted mt-0.5">{{ activity.actor_name }} • {{ activity.time }}</p>
            </div>
          </div>
        </div>
        <p v-else class="text-sm text-muted text-center py-4">Belum ada aktivitas terbaru.</p>
      </template>
    </Card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <Card :header="{ title: 'Statistik Cuti & Lembur' }">
        <template #default>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-sm text-muted">Total Cuti Bulan Ini</span>
              <span class="text-lg font-semibold text-main">{{ stats.cuti?.total_this_month ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-muted">Total Lembur Bulan Ini</span>
              <span class="text-lg font-semibold text-main">{{ stats.lembur?.total_this_month ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-muted">Total Jam Lembur</span>
              <span class="text-lg font-semibold text-main">{{ stats.lembur?.total_hours ?? 0 }} Jam</span>
            </div>
          </div>
        </template>
      </Card>

      <Card :header="{ title: 'Statistik Kesejahteraan' }">
        <template #default>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-sm text-muted">BPJS Kesehatan Aktif</span>
              <span class="text-lg font-semibold text-main">{{ stats.welfare?.bpjs_kesehatan_aktif ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-muted">BPJS Ketenagakerjaan Aktif</span>
              <span class="text-lg font-semibold text-main">{{ stats.welfare?.bpjs_ketenagakerjaan_aktif ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-muted">Tunjangan Diajukan Bulan Ini</span>
              <span class="text-lg font-semibold text-main">{{ stats.welfare?.allowances_this_month ?? 0 }}</span>
            </div>
          </div>
        </template>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Card from '@/Components/UI/Card.vue';
import { Users as UsersIcon, Clock as ClockIcon, CheckCircle2 as CheckCircle2Icon, CalendarClock as CalendarClockIcon } from 'lucide-vue-next';

interface Props {
  stats: any;
  recentActivities: any[];
}

const props = defineProps<Props>();

const stats = ref(props.stats);
const recentActivities = ref(props.recentActivities);

onMounted(() => {
  stats.value = props.stats;
  recentActivities.value = props.recentActivities;
});
</script>
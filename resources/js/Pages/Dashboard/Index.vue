<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-main">Dashboard</h1>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-surface rounded-xl p-5 border border-subtle hover:shadow-sm transition-shadow">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-brand-primary/10 rounded-xl flex items-center justify-center text-brand-primary">
            <span class="font-bold text-lg">P</span>
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
            <span class="font-bold text-lg">M</span>
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
            <span class="font-bold text-lg">A</span>
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
            <span class="font-bold text-lg">K</span>
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
          <div class="flex items-start gap-3" v-for="activity in recentActivities" :key="activity.action">
            <div class="w-2.5 h-2.5 mt-1.5 bg-status-info rounded-full flex-shrink-0" aria-hidden="true"></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm text-main font-medium">{{ activity.action }}</p>
              <p class="text-xs text-muted mt-0.5">{{ activity.actor_name }} • {{ activity.time }}</p>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-muted">Belum ada aktivitas terbaru</div>
      </template>
    </Card>
  </div>
</template>

<script setup lang="ts">
import Card from '@/Components/UI/Card.vue';

interface Props {
  stats?: Record<string, any>;
  recentActivities?: Array<{
    action: string;
    actor_name: string;
    time: string;
  }>;
}

const props = withDefaults(defineProps<Props>(), {
  stats: () => ({}),
  recentActivities: () => [],
});
</script>
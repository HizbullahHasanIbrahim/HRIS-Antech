<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-main">Dashboard</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-surface rounded-lg p-4 border border-subtle">
        <div class="flex items-center">
          <div class="w-8 h-8 bg-brand-primary/10 rounded-lg flex items-center justify-center mr-3">
            <span class="text-brand-primary font-bold">P</span>
          </div>
          <div>
            <div class="text-sm text-muted">Total Pegawai</div>
            <div class="text-xl font-semibold text-main">{{ stats.total_employees || 245 }}</div>
          </div>
        </div>
      </div>
      <div class="bg-surface rounded-lg p-4 border border-subtle">
        <div class="flex items-center">
          <div class="w-8 h-8 bg-status-warning/10 rounded-lg flex items-center justify-center mr-3">
            <span class="text-status-warning font-bold">M</span>
          </div>
          <div>
            <div class="text-sm text-muted">Menunggu Persetujuan</div>
            <div class="text-xl font-semibold text-main">{{ stats.pending_approvals || 18 }}</div>
          </div>
        </div>
      </div>
      <div class="bg-surface rounded-lg p-4 border border-subtle">
        <div class="flex items-center">
          <div class="w-8 h-8 bg-status-success/10 rounded-lg flex items-center justify-center mr-3">
            <span class="text-status-success font-bold">A</span>
          </div>
          <div>
            <div class="text-sm text-muted">Disetujui Bulan Ini</div>
            <div class="text-xl font-semibold text-main">{{ stats.approved_this_month || 156 }}</div>
          </div>
        </div>
      </div>
      <div class="bg-surface rounded-lg p-4 border border-subtle">
        <div class="flex items-center">
          <div class="w-8 h-8 bg-status-info/10 rounded-lg flex items-center justify-center mr-3">
            <span class="text-status-info font-bold">K</span>
          </div>
          <div>
            <div class="text-sm text-muted">Target Kinerja</div>
            <div class="text-xl font-semibold text-main">{{ stats.performance_target || 85 }}%</div>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-surface rounded-lg border border-subtle p-4">
      <h2 class="text-lg font-semibold mb-4">Aktivitas Terakhir</h2>
      <div class="space-y-3">
        <div class="flex items-center space-x-3">
          <div class="w-2 h-2 bg-status-warning rounded-full"></div>
          <div class="flex-1">
            <span class="text-sm text-main">Agus Saputra diajukan cuti tahunan</span>
            <div class="text-xs text-muted">2 menit yang lalu</div>
          </div>
        </div>
        <div class="flex items-center space-x-3">
          <div class="w-2 h-2 bg-status-success rounded-full"></div>
          <div class="flex-1">
            <span class="text-sm text-main">Rina Wijaya disetujui lembur</span>
            <div class="text-xs text-muted">15 menit yang lalu</div>
          </div>
        </div>
        <div class="flex items-center space-x-3">
          <div class="w-2 h-2 bg-status-danger rounded-full"></div>
          <div class="flex-1">
            <span class="text-sm text-main">Budi Santoso ditolak kenaikan pangkat</span>
            <div class="text-xs text-muted">1 jam yang lalu</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  stats?: {
    total_employees: number;
    pending_approvals: number;
    approved_this_month: number;
    performance_target: number;
  };
}

const props = withDefaults(defineProps<Props>(), {
  stats: () => ({}),
});
</script>
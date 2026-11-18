<template>
  <div class="space-y-8">
    <h1 class="text-3xl font-bold text-main">Laporan Eksekutif</h1>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
      <div class="bg-surface rounded-xl p-5 border border-subtle shadow-sm">
        <p class="text-sm text-muted mb-1">Total Pegawai</p>
        <p class="text-3xl font-extrabold text-brand-primary">{{ kpi.totalEmployees }}</p>
      </div>
      <div class="bg-surface rounded-xl p-5 border border-subtle shadow-sm">
        <p class="text-sm text-muted mb-1">Total Dosen Aktif</p>
        <p class="text-3xl font-extrabold text-main">{{ kpi.totalDosen }}</p>
      </div>
      <div class="bg-surface rounded-xl p-5 border border-subtle shadow-sm">
        <p class="text-sm text-muted mb-1">Total Tendik Aktif</p>
        <p class="text-3xl font-extrabold text-main">{{ kpi.totalTendik }}</p>
      </div>
      <div class="bg-surface rounded-xl p-5 border border-subtle shadow-sm">
        <p class="text-sm text-muted mb-1">Pensiun Tahun Ini</p>
        <p class="text-3xl font-extrabold text-danger">{{ kpi.pensiunThisYear }}</p>
      </div>
      <div class="bg-surface rounded-xl p-5 border border-subtle shadow-sm">
        <p class="text-sm text-muted mb-1">Cuti/Lembur Bulan Ini</p>
        <p class="text-3xl font-extrabold text-status-warning">{{ kpi.leaveRequestsThisMonth }}</p>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-surface p-6 rounded-xl border border-subtle">
        <h3 class="text-xl font-semibold text-main mb-4">Distribusi Jabatan Akademik Dosen</h3>
        <div v-if="charts.dosenRankDistribution.length > 0" class="h-80">
          <VueApexCharts ref="dosenChart" type="bar" height="100%" :options="dosenChartOptions" :series="dosenChartSeries" />
        </div>
        <p v-else class="text-muted">Data dosen rank kosong.</p>
      </div>
      <div class="bg-surface p-6 rounded-xl border border-subtle">
        <h3 class="text-xl font-semibold text-main mb-4">Distribusi Pangkat Tendik</h3>
        <div v-if="charts.tendikRankDistribution.length > 0" class="h-80">
          <VueApexCharts ref="tendikChart" type="bar" height="100%" :options="tendikChartOptions" :series="tendikChartSeries" />
        </div>
        <p v-else class="text-muted">Data tendik rank kosong.</p>
      </div>
    </div>

    <!-- Compliance & TABS Section -->
    <div class="bg-surface p-6 rounded-xl border border-subtle">
      <h2 class="text-xl font-semibold text-main mb-6">Laporan Spesifik (RPT-01 s/d RPT-06)</h2>
      
      <!-- Compliance Summary -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <div class="p-4 bg-info/10 border border-info rounded-lg">
          <p class="text-sm text-muted">BKD Dosen ({{ compliance.bkd.period }})</p>
          <div class="flex justify-between text-sm mt-1">
            <span>Diajukan: {{ compliance.bkd.submitted }}</span>
            <span>Final: {{ compliance.bkd.final }}</span>
          </div>
          <p class="text-xl font-bold mt-2 text-brand-primary">Kepatuhan: {{ compliance.bkd.complianceRate }}%</p>
          <p class="text-sm text-muted">Finalisasi: {{ compliance.bkd.finalRate }}%</p>
        </div>
        <div class="p-4 bg-warning/10 border border-warning rounded-lg">
          <p class="text-sm text-muted">SKP Pegawai</p>
          <div class="flex justify-between text-sm mt-1">
            <span>Disetujui: {{ compliance.skp.approved }}</span>
            <span>Total Aktif: {{ compliance.skp.total }}</span>
          </div>
          <p class="text-xl font-bold mt-2 text-status-warning">Kepatuhan: {{ compliance.skp.complianceRate }}%</p>
          <p class="text-sm text-muted">Disetujui: {{ compliance.skp.approvedRate }}%</p>
        </div>
      </div>

      <!-- Tabbed Views -->
      <div class="space-y-6">
        <div class="flex border-b border-subtle overflow-x-auto">
          <button 
            v-for="key in reportKeys" 
            :key="key" 
            @click="activeTab = key"
            :class="[
              'pb-3 border-b-2 transition-colors whitespace-nowrap px-4 text-sm font-medium',
              activeTab === key
                ? 'border-brand-primary text-brand-primary font-semibold'
                : 'border-transparent text-muted hover:text-main'
            ]"
          >
            {{ reportLabels[key].label }} (RPT-{{ reportLabels[key].rpt }})
          </button>
        </div>

        <!-- Tab Content -->
        <div :key="activeTab" class="p-4 bg-canvas rounded-b-xl border border-t-0 border-subtle">
          <div v-if="activeTab === 'sdm'" class="space-y-4">
            <h4 class="text-lg font-semibold text-main">Ringkasan SDM</h4>
            <div class="flex gap-3">
              <AppButton variant="primary" type="button" @click="downloadCsv('sdm')">
                Unduh CSV
              </AppButton>
              <p class="text-sm text-muted">Filter: Unit {{ filters.unit_id ? units.find(u => u.id === filters.unit_id)?.name : 'Semua Unit' }} | Periode: {{ filters.periode }}</p>
            </div>
          </div>
          <div v-else-if="activeTab === 'cuti-lembur'" class="space-y-4">
            <h4 class="text-lg font-semibold text-main">Cuti & Lembur</h4>
            <div class="flex gap-3">
              <AppButton variant="primary" type="button" @click="downloadCsv('cuti-lembur')">
                Unduh CSV
              </AppButton>
            </div>
          </div>
          <div v-else-if="activeTab === 'karir-dosen'" class="space-y-4">
            <h4 class="text-lg font-semibold text-main">Karir Dosen</h4>
            <div class="flex gap-3">
              <AppButton variant="primary" type="button" @click="downloadCsv('karir-dosen')">
                Unduh CSV
              </AppButton>
            </div>
          </div>
          <div v-else-if="activeTab === 'pangkat-tendik'" class="space-y-4">
            <h4 class="text-lg font-semibold text-main">Pangkat Tendik</h4>
            <div class="flex gap-3">
              <AppButton variant="primary" type="button" @click="downloadCsv('pangkat-tendik')">
                Unduh CSV
              </AppButton>
            </div>
          </div>
          <div v-else-if="activeTab === 'bkd-lkd'" class="space-y-4">
            <h4 class="text-lg font-semibold text-main">BKD / LKD</h4>
            <div class="flex gap-3">
              <AppButton variant="primary" type="button" @click="downloadCsv('bkd-lkd')">
                Unduh CSV
              </AppButton>
            </div>
          </div>
          <div v-else-if="activeTab === 'skp'" class="space-y-4">
            <h4 class="text-lg font-semibold text-main">SKP</h4>
            <div class="flex gap-3">
              <AppButton variant="primary" type="button" @click="downloadCsv('skp')">
                Unduh CSV
              </AppButton>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import AppButton from '@/Components/UI/AppButton.vue';

// --- PROPS ---
const props = defineProps<{
  kpi: {
    totalEmployees: number;
    totalDosen: number;
    totalTendik: number;
    pensiunThisYear: number;
    leaveRequestsThisMonth: number;
    leaveDaysApproved: number;
    overtimeHoursApproved: number;
    allowanceRequestsThisMonth: number;
  };
  charts: {
    dosenRankDistribution: Array<{ rank: string; count: number }>;
    tendikRankDistribution: Array<{ rank: string; count: number }>;
  };
  compliance: {
    bkd: {
      submitted: number;
      final: number;
      total: number;
      complianceRate: number;
      finalRate: number;
      period: string;
    };
    skp: {
      submitted: number;
      approved: number;
      total: number;
      complianceRate: number;
      approvedRate: number;
    };
  };
  units: { id: number; name: string; unit_type: string }[];
  filters: { unit_id: number | null; periode: string };
  exportUrls: Record<string, string>;
}>();

// --- STATE & CONSTANTS ---
const reportKeys = ['sdm', 'cuti-lembur', 'karir-dosen', 'pangkat-tendik', 'bkd-lkd', 'skp'];
const reportLabels: Record<string, { label: string; rpt: string }> = {
  'sdm': { label: 'Ringkasan SDM', rpt: '01' },
  'cuti-lembur': { label: 'Cuti & Lembur', rpt: '02' },
  'karir-dosen': { label: 'Karir Dosen', rpt: '03' },
  'pangkat-tendik': { label: 'Pangkat Tendik', rpt: '04' },
  'bkd-lkd': { label: 'BKD / LKD', rpt: '05' },
  'skp': { label: 'SKP', rpt: '06' },
};
const activeTab = ref('sdm');

// --- CHART SETUP ---
// Base options for ApexCharts
const baseChartOptions = {
  chart: {
    type: 'bar',
    height: '100%',
    toolbar: { show: false },
  },
  xaxis: {
    categories: [], // Populated by data
  },
  yaxis: {
    labels: { formatter: (val: number) => val.toFixed(0) },
  },
  plotOptions: {
    bar: {
      borderRadius: 4,
      horizontal: false,
    },
  },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth' },
  grid: {
    borderColor: '#e2e8f0', // border-subtle
    row: { colors: ['transparent', '#f7fafc'] }, // bg-canvas
  },
  colors: ['#E67E22'], // Orange #E67E22
  tooltip: {
    y: {
      formatter: (val: number) => `${val} Pegawai`
    }
  }
};

// Dosen Rank Chart
const dosenChartSeries = computed(() => props.charts.dosenRankDistribution.map(d => d.count));
const dosenChartOptions = computed(() => ({
  ...baseChartOptions,
  xaxis: { categories: props.charts.dosenRankDistribution.map(d => d.rank) },
  title: { text: 'Jumlah Dosen per Jabatan Akademik', align: 'left' }
}));

// Tendik Rank Chart
const tendikChartSeries = computed(() => props.charts.tendikRankDistribution.map(d => d.count));
const tendikChartOptions = computed(() => ({
  ...baseChartOptions,
  xaxis: { categories: props.charts.tendikRankDistribution.map(d => d.rank) },
  title: { text: 'Jumlah Tendik per Pangkat/Golongan', align: 'left' }
}));


// --- METHODS ---
const downloadCsv = (type: string) => {
  window.open(props.exportUrls[type], '_blank');
};

// Expose charts to template
const dosenChart = ref<VueApexCharts | null>(null);
const tendikChart = ref<VueApexCharts | null>(null);
</script>
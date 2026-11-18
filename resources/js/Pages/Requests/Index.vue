<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-main">Pengajuan Kepegawaian</h1>
        <p class="text-sm text-muted">Pengelolaan cuti, lembur, dan tunjangan pegawai.</p>
      </div>
      <button
        type="button"
        @click="openCreateModal"
        class="inline-flex items-center gap-2 px-4 py-2 bg-brand-primary text-white text-sm font-semibold rounded-lg hover:bg-brand-primary-dark transition-colors"
      >
        <PlusIcon class="w-4 h-4" />
        Ajukan Baru
      </button>
    </div>

    <!-- Modul Tabs -->
    <div class="border-b border-subtle flex gap-6 text-sm font-medium">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        @click="activeTab = tab.id"
        :class="[
          'pb-3 border-b-2 transition-colors flex items-center gap-2',
          activeTab === tab.id
            ? 'border-brand-primary text-brand-primary font-semibold'
            : 'border-transparent text-muted hover:text-main'
        ]"
      >
        <component :is="tab.icon" class="w-4 h-4" />
        {{ tab.label }}
        <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-mono">
          {{ counts[tab.id] || 0 }}
        </span>
      </button>
    </div>

    <!-- Data Table -->
    <DataTable
      :columns="currentColumns"
      :rows="currentItems"
      :loading="loading"
      searchable
      :search-placeholder="`Cari data ${activeTab}...`"
      @search="handleSearch"
    >
      <template #status="{ row }">
        <StatusBadge :status="row.status" />
      </template>

      <template #actions="{ row }">
        <div class="flex items-center justify-end gap-2">
          <!-- Action: Approve -->
          <button
            v-if="canApprove(row)"
            @click="approve(row)"
            title="Setujui"
            class="p-1.5 text-status-success hover:bg-status-success-bg rounded"
          >
            <CheckCircleIcon class="w-4 h-4" />
          </button>

          <!-- Action: Reject (Requires Reason RULE-12) -->
          <button
            v-if="canReject(row)"
            @click="openRejectModal(row)"
            title="Tolak"
            class="p-1.5 text-status-danger hover:bg-status-danger-bg rounded"
          >
            <XCircleIcon class="w-4 h-4" />
          </button>

          <!-- Action: Cancel (Requires Reason RULE-12) -->
          <button
            v-if="canCancel(row)"
            @click="openCancelModal(row)"
            title="Batalkan"
            class="p-1.5 text-muted hover:text-main hover:bg-canvas rounded"
          >
            <BanIcon class="w-4 h-4" />
          </button>
        </div>
      </template>
    </DataTable>

    <!-- Modal Form Pengajuan Baru -->
    <Teleport to="body">
      <div v-if="createModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="createModalOpen = false" />
        <div class="relative w-full max-w-lg bg-white rounded-xl shadow-lg border border-subtle p-6 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-main">Form Pengajuan Baru</h2>
            <button @click="createModalOpen = false" class="text-muted hover:text-main">
              <XIcon class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="submitCreate" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-main mb-1">Kategori Pengajuan</label>
              <select v-model="formType" class="w-full border border-control rounded-lg p-2 text-sm bg-white">
                <option value="leave">Cuti Pegawai</option>
                <option value="overtime">Lembur Kerja</option>
                <option value="allowance">Tunjangan Fungsional</option>
              </select>
            </div>

            <!-- Form Cuti -->
            <div v-if="formType === 'leave'" class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-main mb-1">Jenis Cuti</label>
                <select v-model="leaveForm.leave_type_id" required class="w-full border border-control rounded-lg p-2 text-sm bg-white">
                  <option value="" disabled>Pilih jenis cuti</option>
                  <option v-for="t in leaveTypes" :key="t.id" :value="t.id">
                    {{ t.name }} (Kuota: {{ t.annual_quota }} hari)
                  </option>
                </select>
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm font-medium text-main mb-1">Mulai</label>
                  <input type="date" v-model="leaveForm.start_date" required class="w-full border border-control rounded-lg p-2 text-sm" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-main mb-1">Selesai</label>
                  <input type="date" v-model="leaveForm.end_date" required class="w-full border border-control rounded-lg p-2 text-sm" />
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-main mb-1">Alasan</label>
                <textarea v-model="leaveForm.reason" required rows="2" class="w-full border border-control rounded-lg p-2 text-sm resize-none" placeholder="Tuliskan keterangan..." />
              </div>
              <div>
                <label class="block text-sm font-medium text-main mb-1">Atasan Penyetuju (ID Pegawai)</label>
                <input type="number" v-model="leaveForm.approver_id" required class="w-full border border-control rounded-lg p-2 text-sm" placeholder="Contoh: 1" />
              </div>
            </div>

            <!-- Form Lembur -->
            <div v-if="formType === 'overtime'" class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-main mb-1">Tanggal Lembur</label>
                <input type="date" v-model="overtimeForm.work_date" required class="w-full border border-control rounded-lg p-2 text-sm" />
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm font-medium text-main mb-1">Mulai (Jam)</label>
                  <input type="time" v-model="overtimeForm.start_time" required class="w-full border border-control rounded-lg p-2 text-sm" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-main mb-1">Selesai (Jam)</label>
                  <input type="time" v-model="overtimeForm.end_time" required class="w-full border border-control rounded-lg p-2 text-sm" />
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-main mb-1">Uraian Tugas Lembur</label>
                <textarea v-model="overtimeForm.reason" required rows="2" class="w-full border border-control rounded-lg p-2 text-sm resize-none" placeholder="Tuliskan uraian pekerjaan lembur..." />
              </div>
              <div>
                <label class="block text-sm font-medium text-main mb-1">Atasan Penyetuju (ID Pegawai)</label>
                <input type="number" v-model="overtimeForm.approver_id" required class="w-full border border-control rounded-lg p-2 text-sm" placeholder="Contoh: 1" />
              </div>
            </div>

            <!-- Form Tunjangan -->
            <div v-if="formType === 'allowance'" class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-main mb-1">ID Tipe Tunjangan</label>
                <input type="number" v-model="allowanceForm.allowance_type_id" required class="w-full border border-control rounded-lg p-2 text-sm" placeholder="ID Tipe Tunjangan" />
              </div>
              <div>
                <label class="block text-sm font-medium text-main mb-1">Label Periode</label>
                <input type="text" v-model="allowanceForm.period_label" required class="w-full border border-control rounded-lg p-2 text-sm" placeholder="Contoh: 2026-03" />
              </div>
              <div>
                <label class="block text-sm font-medium text-main mb-1">Nominal (Rp)</label>
                <input type="number" v-model="allowanceForm.amount" required class="w-full border border-control rounded-lg p-2 text-sm" placeholder="Contoh: 1500000" />
              </div>
              <div>
                <label class="block text-sm font-medium text-main mb-1">Atasan Penyetuju (ID Pegawai)</label>
                <input type="number" v-model="allowanceForm.approver_id" required class="w-full border border-control rounded-lg p-2 text-sm" placeholder="Contoh: 1" />
              </div>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-subtle">
              <button
                type="button"
                @click="createModalOpen = false"
                class="px-4 py-2 border border-control rounded-lg text-sm text-main hover:bg-canvas"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="submitting"
                class="px-4 py-2 bg-brand-primary text-white rounded-lg text-sm font-semibold hover:bg-brand-primary-dark disabled:opacity-50"
              >
                Kirim
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Reason Modal (RULE-12) -->
    <ReasonModal
      v-model:open="reasonModalOpen"
      :title="modalAction === 'reject' ? 'Tolak Pengajuan' : 'Batalkan Pengajuan'"
      :description="`Berikan alasan ${modalAction === 'reject' ? 'penolakan' : 'pembatalan'} pengajuan ini.`"
      :confirm-label="modalAction === 'reject' ? 'Tolak' : 'Batalkan'"
      :loading="submitting"
      @confirm="handleConfirmReason"
    />

    <!-- Conflict Modal HTTP 409 (RULE-17, EX-04) -->
    <ConflictModal
      v-model:open="conflictModalOpen"
      :expected-version="selectedItem?.version"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import {
  CalendarDays as CalendarDaysIcon,
  Clock as ClockIcon,
  Coins as CoinsIcon,
  Plus as PlusIcon,
  CheckCircle as CheckCircleIcon,
  XCircle as XCircleIcon,
  Ban as BanIcon,
  X as XIcon,
} from 'lucide-vue-next';
import DataTable from '../../Components/UI/DataTable.vue';
import StatusBadge from '../../Components/UI/StatusBadge.vue';
import ReasonModal from '../../Components/UI/ReasonModal.vue';
import ConflictModal from '../../Components/UI/ConflictModal.vue';

interface LeaveRequestItem {
  id: number;
  employee_id: number;
  leave_type?: { name: string };
  start_date: string;
  end_date: string;
  reason: string;
  status: string;
  version: number;
}

const props = withDefaults(defineProps<{
  leaveRequests?: LeaveRequestItem[];
  overtimeRequests?: any[];
  allowanceRequests?: any[];
  leaveTypes?: any[];
  authUserId?: number;
  isApprover?: boolean;
}>(), {
  leaveRequests: () => [],
  overtimeRequests: () => [],
  allowanceRequests: () => [],
  leaveTypes: () => [],
  authUserId: 0,
  isApprover: false,
});

const activeTab = ref<'leave' | 'overtime' | 'allowance'>('leave');
const loading = ref(false);
const submitting = ref(false);
const createModalOpen = ref(false);
const reasonModalOpen = ref(false);
const conflictModalOpen = ref(false);
const modalAction = ref<'reject' | 'cancel'>('reject');
const selectedItem = ref<any>(null);

const formType = ref<'leave' | 'overtime' | 'allowance'>('leave');

const leaveForm = ref({
  leave_type_id: '',
  start_date: '',
  end_date: '',
  reason: '',
  approver_id: '',
});

const overtimeForm = ref({
  work_date: '',
  start_time: '',
  end_time: '',
  reason: '',
  approver_id: '',
});

const allowanceForm = ref({
  allowance_type_id: '',
  period_label: '',
  amount: '',
  approver_id: '',
});

const tabs = [
  { id: 'leave', label: 'Cuti', icon: CalendarDaysIcon },
  { id: 'overtime', label: 'Lembur', icon: ClockIcon },
  { id: 'allowance', label: 'Tunjangan', icon: CoinsIcon },
];

const counts = computed(() => ({
  leave: props.leaveRequests.length,
  overtime: props.overtimeRequests.length,
  allowance: props.allowanceRequests.length,
}));

const currentItems = computed(() => {
  if (activeTab.value === 'leave') return props.leaveRequests;
  if (activeTab.value === 'overtime') return props.overtimeRequests;
  return props.allowanceRequests;
});

const currentColumns = computed(() => {
  if (activeTab.value === 'leave') {
    return [
      { key: 'leave_type', label: 'Jenis Cuti', render: (_: any, r: any) => r.leave_type?.name || '-' },
      { key: 'start_date', label: 'Mulai' },
      { key: 'end_date', label: 'Selesai' },
      { key: 'reason', label: 'Alasan' },
      { key: 'status', label: 'Status' },
    ];
  }
  if (activeTab.value === 'overtime') {
    return [
      { key: 'work_date', label: 'Tanggal Lembur' },
      { key: 'start_time', label: 'Mulai' },
      { key: 'end_time', label: 'Selesai' },
      { key: 'reason', label: 'Uraian' },
      { key: 'status', label: 'Status' },
    ];
  }
  return [
    { key: 'period_label', label: 'Periode' },
    { key: 'amount', label: 'Nominal', render: (val: any) => val ? `Rp ${Number(val).toLocaleString('id-ID')}` : '-' },
    { key: 'status', label: 'Status' },
  ];
});

const canApprove = (row: any) => {
  return props.isApprover && row.status === 'diajukan';
};

const canReject = (row: any) => {
  return props.isApprover && row.status === 'diajukan';
};

const canCancel = (row: any) => {
  return row.status === 'diajukan';
};

const openCreateModal = () => {
  createModalOpen.value = true;
};

const openRejectModal = (row: any) => {
  selectedItem.value = row;
  modalAction.value = 'reject';
  reasonModalOpen.value = true;
};

const openCancelModal = (row: any) => {
  selectedItem.value = row;
  modalAction.value = 'cancel';
  reasonModalOpen.value = true;
};

const approve = (row: any) => {
  submitting.value = true;
  const prefix = activeTab.value === 'leave' ? 'leave-requests' : (activeTab.value === 'overtime' ? 'overtime-requests' : 'allowances');
  
  router.post(`/api/v1/${prefix}/${row.id}/approve`, {
    version: row.version,
  }, {
    preserveScroll: true,
    onError: (errors: any) => {
      if (errors?.code === 'CONCURRENCY_CONFLICT') {
        selectedItem.value = row;
        conflictModalOpen.value = true;
      }
    },
    onFinish: () => {
      submitting.value = false;
    },
  });
};

const handleConfirmReason = (reason: string) => {
  if (!selectedItem.value) return;
  submitting.value = true;
  const prefix = activeTab.value === 'leave' ? 'leave-requests' : (activeTab.value === 'overtime' ? 'overtime-requests' : 'allowances');
  const endpoint = `/api/v1/${prefix}/${selectedItem.value.id}/${modalAction.value}`;
  
  router.post(endpoint, {
    reason,
    decision_note: reason,
    version: selectedItem.value.version,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      reasonModalOpen.value = false;
    },
    onError: (errors: any) => {
      if (errors?.code === 'CONCURRENCY_CONFLICT') {
        conflictModalOpen.value = true;
      }
    },
    onFinish: () => {
      submitting.value = false;
    },
  });
};

const submitCreate = () => {
  submitting.value = true;
  let endpoint = '/api/v1/leave-requests';
  let payload: any = leaveForm.value;

  if (formType.value === 'overtime') {
    endpoint = '/api/v1/overtime-requests';
    payload = overtimeForm.value;
  } else if (formType.value === 'allowance') {
    endpoint = '/api/v1/allowances';
    payload = allowanceForm.value;
  }

  router.post(endpoint, payload, {
    preserveScroll: true,
    onSuccess: () => {
      createModalOpen.value = false;
    },
    onFinish: () => {
      submitting.value = false;
    },
  });
};

const handleSearch = (term: string) => {
  router.get(window.location.pathname, { search: term }, { preserveState: true, replace: true });
};
</script>

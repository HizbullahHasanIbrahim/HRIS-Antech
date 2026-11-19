<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-main">Kinerja Dosen & Tendik</h1>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-subtle flex gap-6 text-sm font-medium overflow-x-auto">
      <button
        @click="activeTab = 'bkd'"
        :class="[
          'pb-3 border-b-2 transition-colors whitespace-nowrap',
          activeTab === 'bkd'
            ? 'border-brand-primary text-brand-primary font-semibold'
            : 'border-transparent text-muted hover:text-main'
        ]"
      >
        BKD (Beban Kerja Dosen)
      </button>
      <button
        @click="activeTab = 'lkd'"
        :class="[
          'pb-3 border-b-2 transition-colors whitespace-nowrap',
          activeTab === 'lkd'
            ? 'border-brand-primary text-brand-primary font-semibold'
            : 'border-transparent text-muted hover:text-main'
        ]"
      >
        LKD (Laporan Kinerja Dosen)
      </button>
      <button
        @click="activeTab = 'skp'"
        :class="[
          'pb-3 border-b-2 transition-colors whitespace-nowrap',
          activeTab === 'skp'
            ? 'border-brand-primary text-brand-primary font-semibold'
            : 'border-transparent text-muted hover:text-main'
        ]"
      >
        SKP / Sasaran Kinerja
      </button>
    </div>

    <!-- BKD Tab -->
    <div v-if="activeTab === 'bkd'">
      <DataTable
        :columns="bkdColumns"
        :rows="bkdData"
        :loading="loading"
        searchable
        search-placeholder="Cari BKD pegawai..."
        :pagination="bkdPagination"
        @search="handleSearch"
      >
        <template #employee="{ row }">
          <span class="font-medium text-main">{{ row.employee?.full_name || '-' }}</span>
        </template>
        <template #academic_year="{ row }">
          <span>{{ row.academic_year }} - Semester {{ row.semester }}</span>
        </template>
        <template #total_credit="{ row }">
          <span class="font-semibold text-main">{{ (Number(row.teaching_credit) + Number(row.research_credit) + Number(row.service_credit) + Number(row.support_credit)).toFixed(2) }} SKS</span>
        </template>
        <template #status="{ row }">
          <StatusBadge :status="row.status" />
        </template>
        <template #actions="{ row }">
          <ActionButtons
            :item="row"
            :is-admin-or-head="props.isAdminOrHead"
            item-type="workload"
            @reload="reloadData"
            @open-reason-modal="openReasonModal"
            @conflict="openConflictModal"
          />
        </template>
      </DataTable>
    </div>

    <!-- LKD Tab -->
    <div v-if="activeTab === 'lkd'">
      <DataTable
        :columns="lkdColumns"
        :rows="lkdData"
        :loading="loading"
        searchable
        search-placeholder="Cari LKD pegawai..."
        :pagination="lkdPagination"
        @search="handleSearch"
      >
        <template #employee="{ row }">
          <span class="font-medium text-main">{{ row.employee?.full_name || '-' }}</span>
        </template>
        <template #academic_year="{ row }">
          <span>{{ row.academic_year }} - Semester {{ row.semester }}</span>
        </template>
        <template #total_credit="{ row }">
          <span class="font-semibold text-main">{{ (Number(row.teaching_credit) + Number(row.research_credit) + Number(row.service_credit) + Number(row.support_credit)).toFixed(2) }} SKS</span>
        </template>
        <template #status="{ row }">
          <StatusBadge :status="row.status" />
        </template>
        <template #actions="{ row }">
          <ActionButtons
            :item="row"
            :is-admin-or-head="props.isAdminOrHead"
            item-type="workload"
            @reload="reloadData"
            @open-reason-modal="openReasonModal"
            @conflict="openConflictModal"
          />
        </template>
      </DataTable>
    </div>

    <!-- SKP Tab -->
    <div v-if="activeTab === 'skp'">
      <DataTable
        :columns="skpColumns"
        :rows="skpData"
        :loading="loading"
        searchable
        search-placeholder="Cari SKP pegawai..."
        :pagination="skpPagination"
        @search="handleSearch"
      >
        <template #employee="{ row }">
          <span class="font-medium text-main">{{ row.employee?.full_name || '-' }}</span>
        </template>
        <template #period="{ row }">
          <span>{{ row.period_label }} ({{ row.period_start }} s.d. {{ row.period_end }})</span>
        </template>
        <template #final_score="{ row }">
          <span class="font-semibold text-main">{{ row.final_score ?? '-' }}</span>
        </template>
        <template #rating="{ row }">
          <span>{{ row.rating ?? '-' }}</span>
        </template>
        <template #status="{ row }">
          <StatusBadge :status="row.status" />
        </template>
        <template #actions="{ row }">
          <ActionButtons
            :item="row"
            :is-admin-or-head="props.isAdminOrHead"
            item-type="skp"
            @reload="reloadData"
            @open-reason-modal="openReasonModal"
            @conflict="openConflictModal"
          />
        </template>
      </DataTable>
    </div>

    <!-- Reason Modal -->
    <ReasonModal
      v-model:open="reasonModalOpen"
      :title="reasonModalTitle"
      :description="reasonModalDescription"
      :placeholder="reasonModalPlaceholder"
      :confirm-label="reasonModalConfirmLabel"
      :loading="reasonModalLoading"
      @confirm="handleReasonConfirm"
      @close="closeReasonModal"
    />

    <!-- Conflict Modal -->
    <ConflictModal
      v-model:open="conflictModalOpen"
      :expected-version="conflictExpectedVersion"
      :only="conflictReloadOnly"
      @reloaded="onConflictReload"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import DataTable from '../../Components/UI/DataTable.vue'
import StatusBadge from '../../Components/UI/StatusBadge.vue'
import ReasonModal from '../../Components/UI/ReasonModal.vue'
import ConflictModal from '../../Components/UI/ConflictModal.vue'
import ActionButtons from './Components/ActionButtons.vue'

const props = defineProps<{
  bkdReports: any
  lkdReports: any
  skpTargets: any
  isAdminOrHead: boolean
  scopeEmployeeId: number | null
}>()

const activeTab = ref<'bkd' | 'lkd' | 'skp'>('bkd')
const loading = ref(false)

// BKD
const bkdData = computed(() => props.bkdReports?.data || props.bkdReports || [])
const bkdPagination = computed(() => props.bkdReports)

// LKD
const lkdData = computed(() => props.lkdReports?.data || props.lkdReports || [])
const lkdPagination = computed(() => props.lkdReports)

// SKP
const skpData = computed(() => props.skpTargets?.data || props.skpTargets || [])
const skpPagination = computed(() => props.skpTargets)

const bkdColumns = [
  { key: 'employee', label: 'Nama Pegawai' },
  { key: 'academic_year', label: 'Tahun Akademik' },
  { key: 'total_credit', label: 'Total SKS' },
  { key: 'status', label: 'Status' },
]

const lkdColumns = [
  { key: 'employee', label: 'Nama Pegawai' },
  { key: 'academic_year', label: 'Tahun Akademik' },
  { key: 'total_credit', label: 'Total SKS' },
  { key: 'status', label: 'Status' },
]

const skpColumns = [
  { key: 'employee', label: 'Nama Pegawai' },
  { key: 'period', label: 'Periode' },
  { key: 'final_score', label: 'Nilai Akhir' },
  { key: 'rating', label: 'Rating' },
  { key: 'status', label: 'Status' },
]

const handleSearch = (term: string) => {
  loading.value = true
  router.get(window.location.pathname, { search: term }, {
    preserveState: true,
    replace: true,
    onFinish: () => {
      loading.value = false
    }
  })
}

watch(() => [props.bkdReports, props.lkdReports, props.skpTargets], () => {
  loading.value = false
}, { deep: true })

// Reason Modal state
const reasonModalOpen = ref(false)
const reasonModalTitle = ref('')
const reasonModalDescription = ref('')
const reasonModalPlaceholder = ref('')
const reasonModalConfirmLabel = ref('')
const reasonModalLoading = ref(false)
const pendingAction = ref<{
  type: 'reject' | 'cancel' | 'revise'
  item: any
  itemType: 'workload' | 'skp' | 'academic' | 'staff'
  endpoint: string
  version: number
} | null>(null)

const openReasonModal = (action: typeof pendingAction.value) => {
  pendingAction.value = action
  if (action) {
    const labels: Record<string, string> = {
      reject: 'Tolak',
      cancel: 'Batalkan',
      revise: 'Revisi',
    }
    const verb = labels[action.type] ?? 'Proses'
    reasonModalTitle.value = `${verb} Pengajuan`
    reasonModalDescription.value = `Berikan alasan untuk ${verb.toLowerCase()} pengajuan ini.`
    reasonModalPlaceholder.value = `Tuliskan alasan ${verb.toLowerCase()}...`
    reasonModalConfirmLabel.value = verb
  }
  reasonModalOpen.value = true
  reasonModalLoading.value = false
}

const closeReasonModal = () => {
  reasonModalOpen.value = false
  pendingAction.value = null
}

const handleReasonConfirm = (reason: string) => {
  if (!pendingAction.value) return

  reasonModalLoading.value = true
  const { type, item, itemType, endpoint, version } = pendingAction.value

  const data: Record<string, any> = {}

  // For revise, need the credit fields and revision_reason
  if (type === 'revise') {
    data.teaching = item.teaching_credit
    data.research = item.research_credit
    data.service = item.service_credit
    data.support = item.support_credit
    data.revision_reason = reason
  } else {
    // For reject/cancel
    data.decision_note = reason
  }

  router.post(endpoint, data, {
    preserveState: true,
    onSuccess: () => {
      reasonModalOpen.value = false
      pendingAction.value = null
      reloadData()
    },
    onError: (errors) => {
      // Check for 409 conflict
      if (errors.status === 409) {
        reasonModalOpen.value = false
        openConflictModal(version)
      }
      reasonModalLoading.value = false
    },
    onFinish: () => {
      reasonModalLoading.value = false
    },
  })
}

// Conflict Modal state
const conflictModalOpen = ref(false)
const conflictExpectedVersion = ref(0)
const conflictReloadOnly = ref<string[]>([])

const openConflictModal = (version: number) => {
  conflictExpectedVersion.value = version
  conflictReloadOnly.value = ['bkdReports', 'lkdReports', 'skpTargets']
  conflictModalOpen.value = true
}

const onConflictReload = () => {
  conflictModalOpen.value = false
}

// Reload data after action
const reloadData = () => {
  router.reload({ only: ['bkdReports', 'lkdReports', 'skpTargets'] })
}
</script>
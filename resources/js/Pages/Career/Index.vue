<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-main">Karir & Kepangkatan</h1>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-subtle flex gap-6 text-sm font-medium overflow-x-auto">
      <button
        @click="activeTab = 'academic'"
        :class="[
          'pb-3 border-b-2 transition-colors whitespace-nowrap',
          activeTab === 'academic'
            ? 'border-brand-primary text-brand-primary font-semibold'
            : 'border-transparent text-muted hover:text-main'
        ]"
      >
        Jabatan Fungsional Dosen
      </button>
      <button
        @click="activeTab = 'staff'"
        :class="[
          'pb-3 border-b-2 transition-colors whitespace-nowrap',
          activeTab === 'staff'
            ? 'border-brand-primary text-brand-primary font-semibold'
            : 'border-transparent text-muted hover:text-main'
        ]"
      >
        Kepangkatan Tendik
      </button>
    </div>

    <!-- Academic Ranks Tab -->
    <div v-if="activeTab === 'academic'">
      <DataTable
        :columns="academicColumns"
        :rows="academicData"
        :loading="loading"
        searchable
        search-placeholder="Cari riwayat jabatan dosen..."
        :pagination="academicPagination"
        @search="handleSearch"
      >
        <template #employee="{ row }">
          <span class="font-medium text-main">{{ row.employee?.full_name || '-' }}</span>
        </template>
        <template #rank_name="{ row }">
          <span class="font-semibold text-main">{{ row.rank_name }}</span>
        </template>
        <template #credit_points="{ row }">
          <span>{{ row.credit_points }} Kum</span>
        </template>
        <template #sk_number="{ row }">
          <span class="font-mono text-xs">{{ row.sk_number || '-' }}</span>
        </template>
        <template #effective_date="{ row }">
          <span>{{ row.effective_date }}</span>
        </template>
        <template #status="{ row }">
          <StatusBadge :status="row.status" />
        </template>
        <template #actions="{ row }">
          <ActionButtons
            :item="row"
            :is-admin-or-head="props.isAdminOrHead"
            item-type="academic"
            @reload="reloadData"
            @open-reason-modal="openReasonModal"
            @conflict="openConflictModal"
          />
        </template>
      </DataTable>
    </div>

    <!-- Staff Ranks Tab -->
    <div v-if="activeTab === 'staff'">
      <DataTable
        :columns="staffColumns"
        :rows="staffData"
        :loading="loading"
        searchable
        search-placeholder="Cari riwayat kepangkatan tendik..."
        :pagination="staffPagination"
        @search="handleSearch"
      >
        <template #employee="{ row }">
          <span class="font-medium text-main">{{ row.employee?.full_name || '-' }}</span>
        </template>
        <template #rank_grade="{ row }">
          <span class="font-semibold text-main">{{ row.rank_grade }}</span>
        </template>
        <template #functional_position="{ row }">
          <span>{{ row.functional_position || '-' }}</span>
        </template>
        <template #sk_number="{ row }">
          <span class="font-mono text-xs">{{ row.sk_number || '-' }}</span>
        </template>
        <template #effective_date="{ row }">
          <span>{{ row.effective_date }}</span>
        </template>
        <template #status="{ row }">
          <StatusBadge :status="row.status" />
        </template>
        <template #actions="{ row }">
          <ActionButtons
            :item="row"
            :is-admin-or-head="props.isAdminOrHead"
            item-type="staff"
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
import ActionButtons from '../Performance/Components/ActionButtons.vue'

const props = defineProps<{
  academicRanks: any
  staffRanks: any
  isAdminOrHead: boolean
  scopeEmployeeId: number | null
}>()

const activeTab = ref<'academic' | 'staff'>('academic')
const loading = ref(false)

const academicData = computed(() => props.academicRanks?.data || props.academicRanks || [])
const academicPagination = computed(() => props.academicRanks)

const staffData = computed(() => props.staffRanks?.data || props.staffRanks || [])
const staffPagination = computed(() => props.staffRanks)

const academicColumns = [
  { key: 'employee', label: 'Nama Pegawai' },
  { key: 'rank_name', label: 'Jabatan Fungsional' },
  { key: 'credit_points', label: 'Angka Kredit' },
  { key: 'sk_number', label: 'No. SK' },
  { key: 'effective_date', label: 'Tanggal Berlaku' },
  { key: 'status', label: 'Status' },
]

const staffColumns = [
  { key: 'employee', label: 'Nama Pegawai' },
  { key: 'rank_grade', label: 'Golongan/Ruang' },
  { key: 'functional_position', label: 'Jabatan Tendik' },
  { key: 'sk_number', label: 'No. SK' },
  { key: 'effective_date', label: 'Tanggal Berlaku' },
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

watch(() => [props.academicRanks, props.staffRanks], () => {
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

  const data: Record<string, any> = {
    decision_note: reason,
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
  conflictReloadOnly.value = ['academicRanks', 'staffRanks']
  conflictModalOpen.value = true
}

const onConflictReload = () => {
  conflictModalOpen.value = false
}

// Reload data after action
const reloadData = () => {
  router.reload({ only: ['academicRanks', 'staffRanks'] })
}
</script>
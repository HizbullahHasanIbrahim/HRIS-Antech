<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-main">Organisasi & Pegawai</h1>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-subtle flex gap-6 text-sm font-medium">
      <button
        @click="activeTab = 'departments'"
        :class="[
          'pb-3 border-b-2 transition-colors',
          activeTab === 'departments'
            ? 'border-brand-primary text-brand-primary font-semibold'
            : 'border-transparent text-muted hover:text-main'
        ]"
      >
        Daftar Departemen
      </button>
      <button
        @click="activeTab = 'employees'"
        :class="[
          'pb-3 border-b-2 transition-colors',
          activeTab === 'employees'
            ? 'border-brand-primary text-brand-primary font-semibold'
            : 'border-transparent text-muted hover:text-main'
        ]"
      >
        Data Pegawai
      </button>
    </div>

    <!-- Departments Tab -->
    <div v-if="activeTab === 'departments'">
      <DataTable
        :columns="departmentColumns"
        :rows="departmentsData"
        :loading="loading"
        searchable
        search-placeholder="Cari departemen..."
        :pagination="departmentsPagination"
        @search="handleSearch"
      >
        <template #name="{ row }">
          <span class="font-medium text-main">{{ row.name }}</span>
        </template>
        <template #unit_type="{ row }">
          <span class="capitalize">{{ row.unit_type }}</span>
        </template>
        <template #is_active="{ row }">
          <StatusBadge :status="row.is_active ? 'aktif' : 'nonaktif'" />
        </template>
      </DataTable>
    </div>

    <!-- Employees Tab -->
    <div v-if="activeTab === 'employees'">
      <DataTable
        :columns="employeeColumns"
        :rows="employeesData"
        :loading="loading"
        searchable
        search-placeholder="Cari pegawai..."
        :pagination="employeesPagination"
        @search="handleSearch"
      >
        <template #full_name="{ row }">
          <button @click="openDetail(row)" class="font-medium text-brand-primary hover:underline">
            {{ row.full_name }}
          </button>
        </template>
        <template #nip="{ row }">
          <span class="font-mono text-sm">{{ row.nip }}</span>
        </template>
        <template #category="{ row }">
          <span>{{ row.category }}</span>
        </template>
        <template #unit="{ row }">
          <span>{{ row.unit?.name || row.department?.name || '-' }}</span>
        </template>
        <template #status="{ row }">
          <StatusBadge :status="row.employment_status" />
        </template>
        <template #actions="{ row }">
          <AppButton variant="ghost" size="sm" @click="openDetail(row)">Detail</AppButton>
        </template>
      </DataTable>
    </div>

    <!-- Employee Detail SideDrawer -->
    <SideDrawer :open="drawerOpen" title="Detail Pegawai" @close="closeDetail" @update:open="drawerOpen = $event">
      <div v-if="selectedEmployee" class="space-y-4">
        <div class="flex items-center gap-4">
          <div class="w-16 h-16 bg-brand-primary/10 rounded-full flex items-center justify-center text-brand-primary font-bold text-xl">
            {{ selectedEmployee.full_name?.charAt(0) || '?' }}
          </div>
          <div>
            <h3 class="text-lg font-semibold text-main">{{ selectedEmployee.full_name }}</h3>
            <p class="text-sm text-muted">{{ selectedEmployee.nip }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <span class="text-muted block text-xs">Kategori</span>
            <span class="font-medium text-main">{{ selectedEmployee.category }}</span>
          </div>
          <div>
            <span class="text-muted block text-xs">Status</span>
            <StatusBadge :status="selectedEmployee.employment_status" />
          </div>
          <div>
            <span class="text-muted block text-xs">Unit Kerja</span>
            <span class="font-medium text-main">{{ selectedEmployee.unit?.name || selectedEmployee.department?.name || '-' }}</span>
          </div>
          <div>
            <span class="text-muted block text-xs">Pendidikan Tertinggi</span>
            <span class="font-medium text-main">{{ selectedEmployee.highest_education }}</span>
          </div>
          <div>
            <span class="text-muted block text-xs">Tanggal Mulai Kerja</span>
            <span class="font-medium text-main">{{ selectedEmployee.join_date }}</span>
          </div>
          <div>
            <span class="text-muted block text-xs">Atasan Langsung</span>
            <span class="font-medium text-main">{{ selectedEmployee.supervisor?.full_name || '-' }}</span>
          </div>
        </div>
      </div>
    </SideDrawer>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import DataTable from '../../Components/UI/DataTable.vue'
import StatusBadge from '../../Components/UI/StatusBadge.vue'
import SideDrawer from '../../Components/UI/SideDrawer.vue'
import AppButton from '../../Components/UI/AppButton.vue'

const props = defineProps<{
  departments: any
  employees: any
  unitFilter: number | null
  isAdminOrHead: boolean
}>()

const activeTab = ref<'departments' | 'employees'>('departments')
const loading = ref(false)
const drawerOpen = ref(false)
const selectedEmployee = ref<any>(null)

const departmentsData = computed(() => props.departments?.data || props.departments || [])
const departmentsPagination = computed(() => props.departments)

const employeesData = computed(() => props.employees?.data || props.employees || [])
const employeesPagination = computed(() => props.employees)

const departmentColumns = [
  { key: 'name', label: 'Nama Departemen' },
  { key: 'unit_type', label: 'Tipe Unit' },
  { key: 'is_active', label: 'Status' },
]

const employeeColumns = [
  { key: 'full_name', label: 'Nama Lengkap' },
  { key: 'nip', label: 'NIP' },
  { key: 'category', label: 'Kategori' },
  { key: 'unit', label: 'Unit Kerja' },
  { key: 'highest_education', label: 'Pendidikan' },
  { key: 'employment_status', label: 'Status' },
  { key: 'actions', label: 'Aksi' },
]

const openDetail = (employee: any) => {
  selectedEmployee.value = employee
  drawerOpen.value = true
}

const closeDetail = () => {
  drawerOpen.value = false
  selectedEmployee.value = null
}

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

watch(() => [props.departments, props.employees], () => {
  loading.value = false
}, { deep: true })
</script>
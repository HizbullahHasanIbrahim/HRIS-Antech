<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-main">Tracker Pensiun</h1>
      <AppButton variant="primary" @click="showCreateModal = true">
        <template #default>Ajukan Pensiun</template>
      </AppButton>
    </div>

    <Card>
      <template #header>
        <h2 class="text-lg font-semibold text-main">Daftar Calon Pensiun</h2>
        <p class="text-sm text-muted">Manajemen proses pensiun pegawai UAD</p>
      </template>
      <template #default>
        <div class="space-y-4">
          <div
            v-for="ret in retirements.data"
            :key="ret.id"
            class="flex items-center justify-between p-4 bg-canvas rounded-lg"
          >
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 bg-accent/10 text-accent rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
              </div>
              <div>
                <h3 class="font-medium text-main">{{ ret.employee }}</h3>
                <p class="text-sm text-muted">NIP: {{ ret.nip }} • {{ ret.department }}</p>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <StatusBadge :status="ret.status" />
              <div class="text-sm text-muted">{{ ret.planned_date || '-' }}</div>
              <span class="text-xs text-muted">{{ ret.sk_number || '-' }}</span>
            </div>
          </div>

          <div v-if="!retirements.data.length" class="text-center py-8 text-muted">Belum ada data pensiun</div>
        </div>

        <!-- Pagination -->
        <div v-if="retirements.last_page > 1" class="flex justify-center mt-4 gap-2">
          <Link
            v-for="link in retirements.links"
            :key="link.label"
            :href="link.url || '#'"
            class="px-3 py-1 rounded-md text-sm"
            :class="link.active ? 'bg-accent text-white' : 'bg-canvas text-main hover:bg-surface'"
            v-html="link.label"
          />
        </div>
      </template>
    </Card>

    <!-- Create Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-surface rounded-lg p-6 w-full max-w-md">
        <h2 class="text-xl font-bold mb-4">Usulan Pensiun</h2>
        <form @submit.prevent="submitCreate">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">Pegawai</label>
              <select v-model="createForm.employee_id" class="w-full border border-control rounded-md p-2 bg-canvas text-main" required>
                <option value="" disabled>Pilih Pegawai</option>
                <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.full_name }} ({{ emp.nip }})</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Usia Pensiun</label>
              <input v-model="createForm.retirement_age" type="number" class="w-full border border-control rounded-md p-2 bg-canvas text-main" min="50" max="75" placeholder="57" required />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Tanggal Perencanaan</label>
              <input v-model="createForm.planned_date" type="date" class="w-full border border-control rounded-md p-2 bg-canvas text-main" required />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Catatan</label>
              <textarea v-model="createForm.notes" class="w-full border border-control rounded-md p-2 bg-canvas text-main" rows="3" placeholder="Catatan tambahan..."></textarea>
            </div>
            <div class="flex justify-end gap-2">
              <AppButton type="button" variant="secondary" @click="showCreateModal = false">Batal</AppButton>
              <AppButton type="submit" variant="primary" :disabled="submitting">Ajukan</AppButton>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppButton from '@/Components/UI/AppButton.vue';
import Card from '@/Components/UI/Card.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';

interface Props {
  retirements: {
    data: Array<{
      id: number;
      employee: string;
      nip: string;
      department: string;
      retirement_age: number;
      planned_date: string | null;
      proposed_at: string | null;
      status: string;
      sk_number: string | null;
      notes: string | null;
    }>;
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  isAdminOrHead: boolean;
  employees: Array<{ id: number; full_name: string; nip: string }>;
}

const props = defineProps<Props>();
const retirements = ref(props.retirements);
const showCreateModal = ref(false);
const submitting = ref(false);
const employees = ref(props.employees);

const createForm = ref({
  employee_id: '',
  retirement_age: 57,
  planned_date: new Date().toISOString().split('T')[0],
  notes: '',
});

const submitCreate = () => {
  submitting.value = true;
  router.post('/retirement', createForm.value, {
    onSuccess: () => {
      showCreateModal.value = false;
      router.reload();
    },
    onFinish: () => {
      submitting.value = false;
    },
  });
};
</script>
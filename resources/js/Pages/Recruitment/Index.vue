<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-main">Lowongan Kerja</h1>
      <AppButton variant="primary" @click="showCreateModal = true">
        <template #default>Buat Lowongan</template>
      </AppButton>
    </div>

    <Card>
      <template #header>
        <h2 class="text-lg font-semibold text-main">Lowongan Aktif</h2>
        <p class="text-sm text-muted">Daftar semua lowongan yang sedang dibuka</p>
      </template>
      <template #default>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" v-if="vacancies.data.length">
          <div
            v-for="vacancy in vacancies.data"
            :key="vacancy.id"
            class="bg-surface border border-subtle rounded-lg p-4 hover:border-accent transition-all cursor-pointer"
          >
            <div class="flex justify-between items-start mb-2">
              <h3 class="font-semibold text-main">{{ vacancy.title }}</h3>
              <StatusBadge :status="vacancy.status" />
            </div>
            <p class="text-sm text-muted mb-3">{{ vacancy.description }}</p>
            <div class="flex flex-wrap gap-2 mb-3">
              <span class="text-xs bg-canvas px-2 py-1 rounded text-muted">{{ vacancy.department }}</span>
              <span class="text-xs bg-canvas px-2 py-1 rounded text-muted">{{ vacancy.type }}</span>
            </div>
            <div class="flex justify-between text-xs text-muted">
              <span>Terbuka: {{ vacancy.openDate }}</span>
              <span>Deadline: {{ vacancy.deadline }}</span>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-muted">Belum ada lowongan aktif</div>

        <!-- Pagination -->
        <div v-if="vacancies.last_page > 1" class="flex justify-center mt-4 gap-2">
          <Link
            v-for="link in vacancies.links"
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
      <div class="bg-surface rounded-lg p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-4">Buat Lowongan Baru</h2>
        <form @submit.prevent="submitCreate">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">Judul</label>
              <input v-model="createForm.title" type="text" class="w-full border border-border rounded-md p-2 bg-canvas text-main" required />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Deskripsi</label>
              <textarea v-model="createForm.description" class="w-full border border-border rounded-md p-2 bg-canvas text-main" rows="4" required></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Kategori</label>
              <select v-model="createForm.category" class="w-full border border-border rounded-md p-2 bg-canvas text-main" required>
                <option value="penegatan">Penegatan (Dosen/Tendik Tetap)</option>
                <option value="tenaga_kontrak">Tenaga Kontrak</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Unit Kerja</label>
              <select v-model="createForm.unit_id" class="w-full border border-border rounded-md p-2 bg-canvas text-main" required>
                <option value="" disabled>Pilih Unit</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Kuota</label>
              <input v-model="createForm.quota" type="number" class="w-full border border-border rounded-md p-2 bg-canvas text-main" min="1" required />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Tanggal Buka</label>
                <input v-model="createForm.open_date" type="date" class="w-full border border-border rounded-md p-2 bg-canvas text-main" required />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Tanggal Tutup</label>
                <input v-model="createForm.close_date" type="date" class="w-full border border-border rounded-md p-2 bg-canvas text-main" required />
              </div>
            </div>
            <div class="flex justify-end gap-2">
              <AppButton type="button" variant="outline" @click="showCreateModal = false">Batal</AppButton>
              <AppButton type="submit" variant="primary" :disabled="submitting">Simpan</AppButton>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppButton from '@/Components/UI/AppButton.vue';
import Card from '@/Components/UI/Card.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';

interface Props {
  vacancies: {
    data: Array<{
      id: number;
      title: string;
      description: string;
      status: string;
      department: string;
      type: string;
      openDate: string;
      deadline: string;
      applications: number;
    }>;
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  applicants: Array<{ id: number; full_name: string; email: string; nip: string }>;
  isAdminOrHead: boolean;
  scopeEmployeeId: number | null;
}

const props = defineProps<Props>();
const vacancies = ref(props.vacancies);
const showCreateModal = ref(false);
const submitting = ref(false);
const departments = ref([
  { id: 1, name: 'Biro Sumber Daya Manusia' },
  { id: 2, name: 'Fakultas Teknologi Industri' },
  { id: 3, name: 'Fakultas Keguruan' },
]);

const createForm = ref({
  title: '',
  description: '',
  category: 'penegatan',
  unit_id: '',
  quota: 1,
  open_date: new Date().toISOString().split('T')[0],
  close_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
});

const submitCreate = () => {
  submitting.value = true;
  router.post('/recruitment', createForm.value, {
    onSuccess: () => {
      showCreateModal.value = false;
      createForm.value = {
        title: '',
        description: '',
        category: 'penegatan',
        unit_id: '',
        quota: 1,
        open_date: new Date().toISOString().split('T')[0],
        close_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
      };
    },
    onFinish: () => {
      submitting.value = false;
    },
  });
};
</script>
<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
      <div>
        <h1 class="text-2xl font-bold text-main">Sertifikasi Dosen</h1>
        <p class="text-sm text-muted mt-1">Kelola sertifikasi keahlian dosen</p>
      </div>
      <AppButton variant="primary" @click="openCreateModal">
        <template #default>Tambah Sertifikasi</template>
      </AppButton>
    </div>

    <Card :header="{ title: 'Daftar Sertifikasi', description: 'Manajemen sertifikasi keahlian dosen' }">
      <template #default>
        <div class="space-y-3" v-if="certifications.data.length">
          <div
            v-for="cert in certifications.data"
            :key="cert.id"
            class="flex items-center justify-between p-4 bg-canvas rounded-xl hover:bg-surface transition-colors"
          >
            <div class="flex items-center gap-4 min-w-0">
              <div class="w-11 h-11 bg-brand-primary/10 text-brand-primary rounded-xl flex items-center justify-center flex-shrink-0">
                <Award class="w-5 h-5" aria-hidden="true" />
              </div>
              <div class="min-w-0">
                <h3 class="font-medium text-main truncate">{{ cert.employee }}</h3>
                <p class="text-sm text-muted truncate">{{ cert.name }} • {{ cert.year }}</p>
                <p class="text-xs text-muted truncate">{{ cert.number }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
              <StatusBadge :status="cert.status" />
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <div class="mx-auto w-16 h-16 bg-canvas rounded-full flex items-center justify-center mb-4">
            <Award class="w-8 h-8 text-muted" aria-hidden="true" />
          </div>
          <h3 class="text-lg font-medium text-main mb-1">Belum ada sertifikasi</h3>
          <p class="text-sm text-muted mb-6">Tambah sertifikasi dosen pertama</p>
          <AppButton variant="primary" @click="openCreateModal" class="w-full sm:w-auto">
            Tambah Sertifikasi Pertama
          </AppButton>
        </div>

        <!-- Pagination -->
        <nav v-if="certifications.last_page > 1" class="mt-6" aria-label="Navigasi halaman sertifikasi">
          <div class="flex justify-center gap-1">
            <Link
              v-for="link in certifications.links"
              :key="link.label"
              :href="link.url || '#'"
              :class="[
                'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
                link.active
                  ? 'bg-brand-primary text-white'
                  : 'text-main hover:bg-surface focus:outline-none focus:ring-2 focus:ring-brand-primary'
              ]"
              :aria-current="link.active ? 'page' : undefined"
              :aria-label="link.label === 'Previous' ? 'Halaman sebelumnya' : link.label === 'Next' ? 'Halaman selanjutnya' : `Halaman ${link.label}`"
              v-html="link.label"
            />
          </div>
        </nav>
      </template>
    </Card>

    <!-- Create Modal -->
    <Teleport to="body">
      <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="closeCreateModal" role="dialog" aria-modal="true" aria-labelledby="create-cert-modal-title">
        <div class="bg-surface rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between px-6 py-4 border-b border-subtle sticky top-0 bg-surface z-10">
            <h2 id="create-cert-modal-title" class="text-xl font-bold text-main">Tambah Sertifikasi</h2>
            <button type="button" @click="closeCreateModal" class="text-muted hover:text-main transition-colors p-1 rounded-lg hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-brand-primary" aria-label="Tutup modal">
              <X class="w-5 h-5" aria-hidden="true" />
            </button>
          </div>
          <form @submit.prevent="submitCreate">
            <div class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-medium mb-1">Pegawai <span class="text-status-danger" aria-hidden="true">*</span></label>
                <select v-model="createForm.employee_id" class="w-full border border-control rounded-md p-2 bg-canvas text-main" required>
                  <option value="" disabled>Pilih Pegawai</option>
                  <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.full_name }} ({{ emp.nip }})</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Jenis Sertifikasi <span class="text-status-danger" aria-hidden="true">*</span></label>
                <input v-model="createForm.cert_type" type="text" class="w-full border border-control rounded-md p-2 bg-canvas text-main" required />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Nomor Sertifikasi <span class="text-status-danger" aria-hidden="true">*</span></label>
                <input v-model="createForm.cert_number" type="text" class="w-full border border-control rounded-md p-2 bg-canvas text-main" required />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Level <span class="text-status-danger" aria-hidden="true">*</span></label>
                <select v-model="createForm.cert_level" class="w-full border border-control rounded-md p-2 bg-canvas text-main" required>
                  <option value="" disabled>Pilih Level</option>
                  <option value="Sertifikasi">Sertifikasi</option>
                  <option value="Sertifikat">Sertifikat</option>
                  <option value="Akreditasi">Akreditasi</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Tanggal Diajukan <span class="text-status-danger" aria-hidden="true">*</span></label>
                <input v-model="createForm.submitted_date" type="date" class="w-full border border-control rounded-md p-2 bg-canvas text-main" required />
              </div>
              <div class="flex justify-end gap-3 pt-4">
                <AppButton variant="secondary" @click="closeCreateModal">Batal</AppButton>
                <AppButton variant="primary" :loading="submitting" @click.prevent="submitCreate">Simpan</AppButton>
              </div>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Award, X } from 'lucide-vue-next';
import AppButton from '@/Components/UI/AppButton.vue';
import Card from '@/Components/UI/Card.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';

interface Props {
  certifications: {
    data: Array<{
      id: number;
      employee: string;
      name: string;
      number: string | null;
      year: string;
      status: string;
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
const certifications = ref(props.certifications);
const showCreateModal = ref(false);
const submitting = ref(false);
const employees = ref(props.employees);

const createForm = ref({
  employee_id: '',
  cert_type: '',
  cert_number: '',
  cert_level: 'Sertifikasi',
  submitted_date: new Date().toISOString().split('T')[0],
});

const openCreateModal = () => {
  showCreateModal.value = true;
  document.body.style.overflow = 'hidden';
};

const closeCreateModal = () => {
  showCreateModal.value = false;
  document.body.style.overflow = '';
};

const submitCreate = () => {
  submitting.value = true;
  router.post('/career/certifications', createForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      closeCreateModal();
      router.reload();
    },
    onError: () => {},
    onFinish: () => {
      submitting.value = false;
    },
  });
};
</script>
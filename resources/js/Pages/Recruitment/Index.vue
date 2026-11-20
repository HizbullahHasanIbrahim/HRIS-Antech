<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
      <div>
        <h1 class="text-2xl font-bold text-main">Lowongan Kerja</h1>
        <p class="text-sm text-muted mt-1">Kelola lowongan pekerjaan dan pelamar</p>
      </div>
      <AppButton variant="primary" @click="openCreateModal">
        <template #default>Buat Lowongan</template>
      </AppButton>
    </div>

    <Card :header="{ title: 'Lowongan Aktif', description: 'Daftar semua lowongan yang sedang dibuka' }">
      <template #default>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" v-if="vacancies.data.length">
          <article
            v-for="vacancy in vacancies.data"
            :key="vacancy.id"
            class="bg-surface border border-subtle rounded-xl p-5 hover:border-accent hover:shadow-md transition-all duration-200"
          >
            <div class="flex justify-between items-start mb-3">
              <h3 class="font-semibold text-main line-clamp-1">{{ vacancy.title }}</h3>
              <StatusBadge :status="vacancy.status" />
            </div>
            <p class="text-sm text-muted mb-4 line-clamp-2">{{ vacancy.description }}</p>
            <div class="flex flex-wrap gap-2 mb-4">
              <span class="inline-flex items-center gap-1 text-xs bg-canvas px-2.5 py-1 rounded text-muted">
                <Building2 class="w-3 h-3" aria-hidden="true" />
                {{ vacancy.department }}
              </span>
              <span class="inline-flex items-center gap-1 text-xs bg-canvas px-2.5 py-1 rounded text-muted">
                <Briefcase class="w-3 h-3" aria-hidden="true" />
                {{ vacancy.type }}
              </span>
            </div>
            <div class="flex flex-col sm:flex-row justify-between gap-2 text-xs text-muted">
              <span class="flex items-center gap-1">
                <Calendar class="w-3 h-3" aria-hidden="true" />
                Terbuka: {{ vacancy.openDate }}
              </span>
              <span class="flex items-center gap-1">
                <Clock class="w-3 h-3" aria-hidden="true" />
                Deadline: {{ vacancy.deadline }}
              </span>
            </div>
            <div class="mt-4 pt-4 border-t border-subtle">
              <span class="text-xs text-muted">{{ vacancy.applications }} pelamar</span>
            </div>
          </article>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <div class="mx-auto w-16 h-16 bg-canvas rounded-full flex items-center justify-center mb-4">
            <Briefcase class="w-8 h-8 text-muted" aria-hidden="true" />
          </div>
          <h3 class="text-lg font-medium text-main mb-1">Belum ada lowongan aktif</h3>
          <p class="text-sm text-muted mb-6">Mulai buat lowongan pekerjaan pertama Anda</p>
          <AppButton variant="primary" @click="openCreateModal" class="w-full sm:w-auto">
            Buat Lowongan Pertama
          </AppButton>
        </div>

        <!-- Pagination -->
        <nav v-if="vacancies.last_page > 1" class="mt-6" aria-label="Navigasi halaman lowongan">
          <div class="flex justify-center gap-1">
            <Link
              v-for="link in vacancies.links"
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
      <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="closeCreateModal" role="dialog" aria-modal="true" aria-labelledby="create-modal-title">
        <div class="bg-surface rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between px-6 py-4 border-b border-subtle sticky top-0 bg-surface z-10">
            <h2 id="create-modal-title" class="text-xl font-bold text-main">Buat Lowongan Baru</h2>
            <button type="button" @click="closeCreateModal" class="text-muted hover:text-main transition-colors p-1 rounded-lg hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-brand-primary" aria-label="Tutup modal">
              <X class="w-5 h-5" aria-hidden="true" />
            </button>
          </div>
          <form @submit.prevent="submitCreate" class="p-6 space-y-5">
            <div>
              <label for="title" class="block text-sm font-medium text-main mb-1.5">Judul <span class="text-status-danger" aria-hidden="true">*</span></label>
              <input id="title" v-model="createForm.title" type="text" class="w-full border border-border rounded-lg px-4 py-2.5 bg-canvas text-main placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent" required aria-required="true" autocomplete="off" />
            </div>
            <div>
              <label for="description" class="block text-sm font-medium text-main mb-1.5">Deskripsi <span class="text-status-danger" aria-hidden="true">*</span></label>
              <textarea id="description" v-model="createForm.description" class="w-full border border-border rounded-lg px-4 py-2.5 bg-canvas text-main placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent" rows="4" required aria-required="true"></textarea>
            </div>
            <div>
              <label for="category" class="block text-sm font-medium text-main mb-1.5">Kategori <span class="text-status-danger" aria-hidden="true">*</span></label>
              <select id="category" v-model="createForm.category" class="w-full border border-border rounded-lg px-4 py-2.5 bg-canvas text-main focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent" required aria-required="true">
                <option value="" disabled>Pilih Kategori</option>
                <option value="penegatan">Penegatan (Dosen/Tendik Tetap)</option>
                <option value="tenaga_kontrak">Tenaga Kontrak</option>
              </select>
            </div>
            <div>
              <label for="unit_id" class="block text-sm font-medium text-main mb-1.5">Unit Kerja <span class="text-status-danger" aria-hidden="true">*</span></label>
              <select id="unit_id" v-model="createForm.unit_id" class="w-full border border-border rounded-lg px-4 py-2.5 bg-canvas text-main focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent" required aria-required="true">
                <option value="" disabled>Pilih Unit</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
              </select>
            </div>
            <div>
              <label for="quota" class="block text-sm font-medium text-main mb-1.5">Kuota <span class="text-status-danger" aria-hidden="true">*</span></label>
              <input id="quota" v-model="createForm.quota" type="number" class="w-full border border-border rounded-lg px-4 py-2.5 bg-canvas text-main focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent" min="1" required aria-required="true" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              <div>
                <label for="open_date" class="block text-sm font-medium text-main mb-1.5">Tanggal Buka <span class="text-status-danger" aria-hidden="true">*</span></label>
                <input id="open_date" v-model="createForm.open_date" type="date" class="w-full border border-border rounded-lg px-4 py-2.5 bg-canvas text-main focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent" required aria-required="true" />
              </div>
              <div>
                <label for="close_date" class="block text-sm font-medium text-main mb-1.5">Tanggal Tutup <span class="text-status-danger" aria-hidden="true">*</span></label>
                <input id="close_date" v-model="createForm.close_date" type="date" class="w-full border border-border rounded-lg px-4 py-2.5 bg-canvas text-main focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent" required aria-required="true" />
              </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-subtle">
              <AppButton type="button" variant="secondary" @click="closeCreateModal" :disabled="submitting">Batal</AppButton>
              <AppButton type="submit" variant="primary" :loading="submitting">Simpan</AppButton>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Building2, Briefcase, Calendar, Clock, X } from 'lucide-vue-next';
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
  departments: Array<{ id: number; name: string }>;
}

const props = defineProps<Props>();
const vacancies = ref(props.vacancies);
const showCreateModal = ref(false);
const submitting = ref(false);
const departments = ref(props.departments);

const createForm = ref({
  title: '',
  description: '',
  category: 'dosen',
  unit_id: '',
  quota: 1,
  open_date: new Date().toISOString().split('T')[0],
  close_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
});

const openCreateModal = () => {
  showCreateModal.value = true;
  // Focus trap will be handled by browser for modal
  document.body.style.overflow = 'hidden';
};

const closeCreateModal = () => {
  showCreateModal.value = false;
  document.body.style.overflow = '';
};

const submitCreate = () => {
  submitting.value = true;
  router.post('/recruitment', createForm.value, {
    onSuccess: () => {
      closeCreateModal();
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
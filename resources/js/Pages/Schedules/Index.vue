<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
      <div>
        <h1 class="text-2xl font-bold text-main">Jadwal Kerja</h1>
        <p class="text-sm text-muted mt-1">Kelola jadwal kerja harian dan mingguan</p>
      </div>
      <AppButton variant="primary" @click="openCreateModal">
        <template #default>Buat Jadwal</template>
      </AppButton>
    </div>

    <Card :header="{ title: 'Jadwal Kerja Pegawai', description: 'Manajemen jadwal kerja harian dan mingguan' }">
      <template #default>
        <div class="space-y-4" v-if="schedules.data.length">
          <div
            v-for="schedule in schedules.data"
            :key="schedule.id"
            class="bg-canvas rounded-xl border border-subtle p-5 hover:bg-surface hover:border-accent transition-all duration-200"
          >
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-main">{{ schedule.unit_name }}</h3>
              <StatusBadge :status="schedule.is_published ? 'disetujui' : 'draf'" />
            </div>
            
            <div class="flex items-center gap-2 text-sm text-muted mb-4">
              <span>Berlaku: {{ schedule.valid_from }}</span>
              <span v-if="schedule.valid_until">s/d {{ schedule.valid_until }}</span>
              <span v-else class="text-status-success">Tidak terbatas</span>
            </div>

            <!-- Jadwal Hari Kerja -->
            <div v-if="schedule.days.length">
              <h4 class="font-medium text-main mb-3">Jadwal Hari Kerja</h4>
              <div class="grid grid-cols-7 gap-1 mb-3">
                <div
                  v-for="day in schedule.days"
                  :key="day.day_name"
                  class="bg-surface rounded-lg h-14 flex items-center justify-center text-xs"
                >
                  <span class="text-main font-medium">{{ day.day_name }}</span>
                </div>
              </div>
              <div class="space-y-1">
                <p v-for="day in schedule.days" :key="'time-' + day.day_name" class="text-sm text-muted flex items-center gap-2">
                  <span class="w-10 font-medium text-main">{{ day.day_name }}</span>
                  <span class="text-muted">:</span>
                  <span>{{ day.start_time || '---' }} - {{ day.end_time || '---' }}</span>
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <div class="mx-auto w-16 h-16 bg-canvas rounded-full flex items-center justify-center mb-4">
            <Calendar class="w-8 h-8 text-muted" aria-hidden="true" />
          </div>
          <h3 class="text-lg font-medium text-main mb-1">Belum ada jadwal kerja</h3>
          <p class="text-sm text-muted mb-6">Buat jadwal kerja pertama untuk unit</p>
          <AppButton variant="primary" @click="openCreateModal" class="w-full sm:w-auto">
            Buat Jadwal Pertama
          </AppButton>
        </div>

        <!-- Pagination -->
        <nav v-if="schedules.last_page > 1" class="mt-6" aria-label="Navigasi halaman jadwal">
          <div class="flex justify-center gap-1">
            <Link
              v-for="link in schedules.links"
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
      <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="closeCreateModal" role="dialog" aria-modal="true" aria-labelledby="create-schedule-modal-title">
        <div class="bg-surface rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between px-6 py-4 border-b border-subtle sticky top-0 bg-surface z-10">
            <h2 id="create-schedule-modal-title" class="text-xl font-bold text-main">Buat Jadwal Kerja</h2>
            <button type="button" @click="closeCreateModal" class="text-muted hover:text-main transition-colors p-1 rounded-lg hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-brand-primary" aria-label="Tutup modal">
              <X class="w-5 h-5" aria-hidden="true" />
            </button>
          </div>
          <form @submit.prevent="submitCreate">
            <div class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-medium mb-1">Unit <span class="text-status-danger" aria-hidden="true">*</span></label>
                <select v-model="createForm.unit_id" class="w-full border border-border rounded-md p-2 bg-canvas text-main" required>
                  <option value="" disabled>Pilih Unit</option>
                  <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Kategori <span class="text-status-danger" aria-hidden="true">*</span></label>
                <select v-model="createForm.category" class="w-full border border-border rounded-md p-2 bg-canvas text-main" required>
                  <option value="dosen">Dosen</option>
                  <option value="tendik">Tenaga Kependidikan</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Tanggal Mulai <span class="text-status-danger" aria-hidden="true">*</span></label>
                <input v-model="createForm.valid_from" type="date" class="w-full border border-border rounded-md p-2 bg-canvas text-main" required />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Tanggal Berakhir</label>
                <input v-model="createForm.valid_until" type="date" class="w-full border border-border rounded-md p-2 bg-canvas text-main" />
              </div>
              <div class="flex flex-wrap gap-2 mb-4">
                <div v-for="d in workWeekDays" :key="d.day" class="flex items-center gap-2 bg-canvas border border-border rounded-lg p-3">
                  <span class="w-12 font-medium text-main">{{ d.day }}</span>
                  <input v-model="d.start_time" type="time" class="border border-border rounded px-2 py-1 bg-canvas text-main" required />
                  <span class="text-muted">s/d</span>
                  <input v-model="d.end_time" type="time" class="border border-border rounded px-2 py-1 bg-canvas text-main" required />
                </div>
              </div>
              <div class="flex items-center gap-2">
                <input type="checkbox" id="is_published" v-model="createForm.is_published" class="h-4 w-4 rounded border-border" />
                <label for="is_published" class="text-sm text-main">Publikasi jadwal</label>
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
import { X } from 'lucide-vue-next';
import AppButton from '@/Components/UI/AppButton.vue';
import Card from '@/Components/UI/Card.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';

interface Props {
  schedules: {
    data: Array<{
      id: number;
      unit_name: string;
      category: string;
      valid_from: string;
      valid_until: string | null;
      is_published: boolean;
      days: Array<{
        day_name: string;
        start_time: string | null;
        end_time: string | null;
      }>;
    }>;
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  isAdminOrHead: boolean;
  departments: Array<{ id: number; name: string }>;
}

const props = defineProps<Props>();
const schedules = ref(props.schedules);
const showCreateModal = ref(false);
const submitting = ref(false);
const departments = ref(props.departments);
const workWeekDays = ref([
  { day: 'Senin', day_of_week: 1, start_time: '08:00', end_time: '17:00' },
  { day: 'Selasa', day_of_week: 2, start_time: '08:00', end_time: '17:00' },
  { day: 'Rabu', day_of_week: 3, start_time: '08:00', end_time: '17:00' },
  { day: 'Kamis', day_of_week: 4, start_time: '08:00', end_time: '17:00' },
  { day: 'Jumat', day_of_week: 5, start_time: '08:00', end_time: '17:00' },
  { day: 'Sabtu', day_of_week: 6, start_time: '08:00', end_time: '17:00' },
  { day: 'Minggu', day_of_week: 7, start_time: '00:00', end_time: '00:00' },
]);

const createForm = ref({
  unit_id: '',
  category: 'dosen',
  valid_from: new Date().toISOString().split('T')[0],
  valid_until: '',
  is_published: true,
  days: workWeekDays.value.map(d => ({
    day_of_week: d.day_of_week,
    start_time: d.start_time,
    end_time: d.end_time,
  }))
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
  router.post('/schedules', createForm.value, {
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
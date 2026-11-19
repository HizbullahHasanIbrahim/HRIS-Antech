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

    <!-- Create Modal placeholder -->
    <Teleport to="body">
      <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="closeCreateModal" role="dialog" aria-modal="true" aria-labelledby="create-schedule-modal-title">
        <div class="bg-surface rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between px-6 py-4 border-b border-subtle sticky top-0 bg-surface z-10">
            <h2 id="create-schedule-modal-title" class="text-xl font-bold text-main">Buat Jadwal Kerja</h2>
            <button type="button" @click="closeCreateModal" class="text-muted hover:text-main transition-colors p-1 rounded-lg hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-brand-primary" aria-label="Tutup modal">
              <X class="w-5 h-5" aria-hidden="true" />
            </button>
          </div>
          <div class="p-6 text-center text-muted">
            Form buat jadwal kerja akan diimplementasikan nanti.
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Calendar, X } from 'lucide-vue-next';
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
}

const props = defineProps<Props>();
const schedules = ref(props.schedules);
const showCreateModal = ref(false);

const openCreateModal = () => {
  showCreateModal.value = true;
  document.body.style.overflow = 'hidden';
};

const closeCreateModal = () => {
  showCreateModal.value = false;
  document.body.style.overflow = '';
};
</script>
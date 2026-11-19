<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-main">Jadwal Kerja</h1>
      <AppButton variant="primary">
        <template #default>Baru</template>
      </AppButton>
    </div>

    <Card>
      <template #header>
        <h2 class="text-lg font-semibold text-main">Jadwal Kerja Pegawai</h2>
        <p class="text-sm text-muted">Manajemen jadwal kerja harian dan mingguan</p>
      </template>
      <template #default>
        <div class="space-y-4">
          <div v-if="schedules.data.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="schedule in schedules.data"
              :key="schedule.id"
              class="bg-canvas rounded-lg border border-subtle p-4"
            >
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-main">{{ schedule.unit_name }}</h3>
                <StatusBadge :status="schedule.is_published ? 'disetujui' : 'draft'" />
              </div>

              <!-- Jadwal Hari Kerja -->
              <div v-if="schedule.days.length">
                <h4 class="font-bold text-main mb-3">Jadwal Hari Kerja</h4>
                <div class="grid grid-cols-7 gap-1 mb-3">
                  <div
                    v-for="day in schedule.days"
                    :key="day.day_name"
                    class="bg-surface rounded-lg h-14 flex items-center justify-center text-xs"
                  >
                    <span class="text-main font-medium">{{ day.day_name }}</span>
                  </div>
                </div>
                <p v-for="day in schedule.days" :key="'time-' + day.day_name" class="text-sm text-muted">
                  {{ day.day_name }}: {{ day.start_time || '---' }} - {{ day.end_time || '---' }}
                </p>
              </div>
            </div>
          </div>

          <div v-else class="text-center py-8 text-muted">Belum ada jadwal kerja</div>
        </div>

        <!-- Pagination -->
        <div v-if="schedules.last_page > 1" class="flex justify-center mt-4 gap-2">
          <Link
            v-for="link in schedules.links"
            :key="link.label"
            :href="link.url || '#'"
            class="px-3 py-1 rounded-md text-sm"
            :class="link.active ? 'bg-accent text-white' : 'bg-canvas text-main hover:bg-surface'"
            v-html="link.label"
          />
        </div>
      </template>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
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
</script>
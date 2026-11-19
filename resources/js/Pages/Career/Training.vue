<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-main">Pelatihan & Diklat</h1>
      <AppButton variant="primary">
        <template #default>Tambah Pelatihan</template>
      </AppButton>
    </div>

    <Card>
      <template #header>
        <h2 class="text-lg font-semibold text-main">Daftar Pelatihan</h2>
        <p class="text-sm text-muted">Manajemen pelatihan dan diklat pegawai</p>
      </template>
      <template #default>
        <div class="space-y-4">
          <div
            v-for="training in trainings.data"
            :key="training.id"
            class="flex items-center justify-between p-4 bg-canvas rounded-lg"
          >
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 bg-accent/10 text-accent rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div>
                <h3 class="font-medium text-main">{{ training.name }}</h3>
                <p class="text-sm text-muted">{{ training.employee }} • {{ training.provider }}</p>
                <p class="text-xs text-muted">{{ training.date }} • {{ training.duration }}</p>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <StatusBadge :status="training.status" />
            </div>
          </div>

          <div v-if="!trainings.data.length" class="text-center py-8 text-muted">Belum ada data pelatihan</div>
        </div>

        <!-- Pagination -->
        <div v-if="trainings.last_page > 1" class="flex justify-center mt-4 gap-2">
          <Link
            v-for="link in trainings.links"
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
  trainings: {
    data: Array<{
      id: number;
      employee: string;
      name: string;
      provider: string;
      date: string;
      duration: string;
      status: string;
    }>;
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  isAdminOrHead: boolean;
}

const props = defineProps<Props>();
const trainings = ref(props.trainings);
</script>
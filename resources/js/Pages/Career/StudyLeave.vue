<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-main">Studi Lanjut S3</h1>
      <AppButton variant="primary">
        <template #default>Ajukan</template>
      </AppButton>
    </div>

    <Card>
      <template #header>
        <h2 class="text-lg font-semibold text-main">Pengajuan Studi Lanjut</h2>
        <p class="text-sm text-muted">Persyaratan dan status pengajuan studi lanjut S3</p>
      </template>
      <template #default>
        <div class="space-y-4">
          <div
            v-for="study in studies.data"
            :key="study.id"
            class="flex items-center justify-between p-4 bg-canvas rounded-lg"
          >
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 bg-accent/10 text-accent rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <h3 class="font-medium text-main">{{ study.candidate }}</h3>
                <p class="text-sm text-muted">{{ study.program }}</p>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <div class="text-sm text-muted">{{ study.scholarship || '-' }}</div>
              <StatusBadge :status="study.status" />
              <div v-if="study.reason" class="text-xs text-muted">{{ study.reason }}</div>
            </div>
          </div>

          <div v-if="!studies.data.length" class="text-center py-8 text-muted">Belum ada data studi lanjut</div>
        </div>

        <!-- Pagination -->
        <div v-if="studies.last_page > 1" class="flex justify-center mt-4 gap-2">
          <Link
            v-for="link in studies.links"
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
  studies: {
    data: Array<{
      id: number;
      candidate: string;
      program: string;
      status: string;
      reason: string | null;
      scholarship: string | null;
    }>;
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  isAdminOrHead: boolean;
}

const props = defineProps<Props>();
const studies = ref(props.studies);
</script>
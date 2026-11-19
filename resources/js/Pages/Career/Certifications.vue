<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-main">Sertifikasi Dosen</h1>
      <AppButton variant="primary">
        <template #default>Tambah Sertifikasi</template>
      </AppButton>
    </div>

    <Card>
      <template #header>
        <h2 class="text-lg font-semibold text-main">Daftar Sertifikasi</h2>
        <p class="text-sm text-muted">Manajemen sertifikasi keahlian dosen</p>
      </template>
      <template #default>
        <div class="space-y-4">
          <div
            v-for="cert in certifications.data"
            :key="cert.id"
            class="flex items-center justify-between p-4 bg-canvas rounded-lg"
          >
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 bg-accent/10 text-accent rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <h3 class="font-medium text-main">{{ cert.employee }}</h3>
                <p class="text-sm text-muted">{{ cert.name }} • {{ cert.year }}</p>
                <p class="text-xs text-muted">{{ cert.number }}</p>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <StatusBadge :status="cert.status" />
            </div>
          </div>

          <div v-if="!certifications.data.length" class="text-center py-8 text-muted">Belum ada data sertifikasi</div>
        </div>

        <!-- Pagination -->
        <div v-if="certifications.last_page > 1" class="flex justify-center mt-4 gap-2">
          <Link
            v-for="link in certifications.links"
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
}

const props = defineProps<Props>();
const certifications = ref(props.certifications);
</script>
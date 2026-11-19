<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-main">Activity Logs</h1>
      <SearchFilterBar v-model:searchQuery="searchQuery" placeholder="Cari aktivitas..." />
    </div>

    <Card>
      <template #header>
        <h2 class="text-lg font-semibold text-main">Log Aktivitas Sistem</h2>
        <p class="text-sm text-muted">Riwayat semua aktivitas pengguna dalam sistem</p>
      </template>
      <template #default>
        <div class="space-y-4">
          <div v-for="log in logs.data" :key="log.id" class="flex items-center justify-between p-4 bg-canvas rounded-lg">
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 bg-accent/10 text-accent rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <h3 class="font-medium text-main">{{ log.action }}</h3>
                <p class="text-sm text-muted">{{ log.actor_name }} • {{ log.time }}</p>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <StatusBadge :status="log.new_status || 'info'" />
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="logs.last_page > 1" class="flex justify-center mt-4 gap-2">
          <Link
            v-for="link in logs.links"
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
import SearchFilterBar from '@/Components/UI/SearchFilterBar.vue';
import Card from '@/Components/UI/Card.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';

interface Props {
  logs: {
    data: Array<{
      id: number;
      action: string;
      actor_name: string;
      actor_email: string | null;
      entity_type: string | null;
      entity_id: number | null;
      old_status: string | null;
      new_status: string | null;
      reason: string | null;
      details: Record<string, unknown> | null;
      ip_address: string | null;
      time: string | null;
    }>;
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  actors: Array<{ id: number; name: string }>;
}

const props = defineProps<Props>();
const logs = ref(props.logs);
const searchQuery = ref('');
</script>
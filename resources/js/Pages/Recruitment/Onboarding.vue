<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-main">Onboarding Penggawai</h1>
      <AppButton variant="primary" @click="showCreateModal = true">
        <template #default>Baru</template>
      </AppButton>
    </div>

    <Card>
      <template #header>
        <h2 class="text-lg font-semibold text-main">Daftar Penggawai Baru</h2>
        <p class="text-sm text-muted">Manajemen proses onboarding</p>
      </template>
      <template #default>
        <div class="space-y-4">
          <div
            v-for="user in users.data"
            :key="user.id"
            class="flex items-center justify-between p-4 bg-canvas rounded-lg"
          >
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-surface rounded-full flex items-center justify-center">
                <span class="text-sm font-medium text-main">{{ user.name.charAt(0) }}</span>
              </div>
              <div>
                <h3 class="font-medium text-main">{{ user.name }}</h3>
                <p class="text-sm text-muted">{{ user.email }}</p>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <StatusBadge :status="user.status" />
              <span class="text-sm text-muted">{{ user.statusDate }}</span>
            </div>
          </div>
        </div>

        <div v-if="!users.data.length" class="text-center py-8 text-muted">Belum ada pegawai baru</div>

        <!-- Pagination -->
        <div v-if="users.last_page > 1" class="flex justify-center mt-4 gap-2">
          <Link
            v-for="link in users.links"
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
        <h2 class="text-xl font-bold mb-4">Tambah Penggawai Baru</h2>
        <form @submit.prevent="submitCreate">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">Nama Lengkap</label>
              <input v-model="createForm.full_name" type="text" class="w-full border border-border rounded-md p-2 bg-canvas text-main" required />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Email</label>
              <input v-model="createForm.email" type="email" class="w-full border border-border rounded-md p-2 bg-canvas text-main" required />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">NIP</label>
              <input v-model="createForm.nip" type="text" class="w-full border border-border rounded-md p-2 bg-canvas text-main" required />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Tanggal Mulai Kerja</label>
              <input v-model="createForm.start_date" type="date" class="w-full border border-border rounded-md p-2 bg-canvas text-main" required />
            </div>
            <div class="flex justify-end gap-2">
              <AppButton type="button" variant="outline" @click="showCreateModal = false">Batal</AppButton>
              <AppButton type="submit" variant="primary" :disabled="submitting">Ajukan</AppButton>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppButton from '@/Components/UI/AppButton.vue';
import Card from '@/Components/UI/Card.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';

interface Props {
  users: {
    data: Array<{
      id: number;
      name: string;
      email: string;
      status: string;
      statusDate: string;
    }>;
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
}

const props = defineProps<Props>();
const users = ref(props.users);
const showCreateModal = ref(false);
const submitting = ref(false);

const createForm = ref({
  full_name: '',
  email: '',
  nip: '',
  start_date: new Date().toISOString().split('T')[0],
});

const submitCreate = () => {
  submitting.value = true;
  router.post('/recruitment/onboarding', createForm.value, {
    onSuccess: () => {
      showCreateModal.value = false;
      router.reload();
    },
    onFinish: () => {
      submitting.value = false;
    },
  });
};
</script>
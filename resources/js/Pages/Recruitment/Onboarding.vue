<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
      <div>
        <h1 class="text-2xl font-bold text-main">Onboarding Pegawai</h1>
        <p class="text-sm text-muted mt-1">Kelola proses onboarding pegawai baru</p>
      </div>
      <AppButton variant="primary" @click="openCreateModal">
        <template #default>Tambah Pegawai</template>
      </AppButton>
    </div>

    <Card :header="{ title: 'Daftar Pegawai Baru', description: 'Kelola proses onboarding pegawai baru' }">
      <template #default>
        <div class="space-y-3" v-if="users.data.length">
          <div
            v-for="user in users.data"
            :key="user.id"
            class="flex items-center justify-between p-4 bg-canvas rounded-xl hover:bg-surface transition-colors"
          >
            <div class="flex items-center gap-4 min-w-0">
              <div class="w-11 h-11 bg-surface rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-base font-medium text-main">{{ user.name.charAt(0).toUpperCase() }}</span>
              </div>
              <div class="min-w-0">
                <h3 class="font-medium text-main truncate">{{ user.name }}</h3>
                <p class="text-sm text-muted truncate">{{ user.email }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
              <StatusBadge :status="user.status" />
              <span class="inline-flex items-center gap-1 text-xs text-muted">
                <Calendar class="w-3 h-3" aria-hidden="true" />
                {{ user.statusDate }}
              </span>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <div class="mx-auto w-16 h-16 bg-canvas rounded-full flex items-center justify-center mb-4">
            <UserPlus class="w-8 h-8 text-muted" aria-hidden="true" />
          </div>
          <h3 class="text-lg font-medium text-main mb-1">Belum ada pegawai baru</h3>
          <p class="text-sm text-muted mb-6">Mulai tambah pegawai untuk memulai onboarding</p>
          <AppButton variant="primary" @click="openCreateModal" class="w-full sm:w-auto">
            Tambah Pegawai Pertama
          </AppButton>
        </div>

        <!-- Pagination -->
        <nav v-if="users.last_page > 1" class="mt-6" aria-label="Navigasi halaman onboarding">
          <div class="flex justify-center gap-1">
            <Link
              v-for="link in users.links"
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
      <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="closeCreateModal" role="dialog" aria-modal="true" aria-labelledby="create-onboarding-modal-title">
        <div class="bg-surface rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between px-6 py-4 border-b border-subtle sticky top-0 bg-surface z-10">
            <h2 id="create-onboarding-modal-title" class="text-xl font-bold text-main">Tambah Pegawai Baru</h2>
            <button type="button" @click="closeCreateModal" class="text-muted hover:text-main transition-colors p-1 rounded-lg hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-brand-primary" aria-label="Tutup modal">
              <X class="w-5 h-5" aria-hidden="true" />
            </button>
          </div>
          <form @submit.prevent="submitCreate" class="p-6 space-y-5">
            <div>
              <label for="onboard-full-name" class="block text-sm font-medium text-main mb-1.5">Nama Lengkap <span class="text-status-danger" aria-hidden="true">*</span></label>
              <input id="onboard-full-name" v-model="createForm.full_name" type="text" class="w-full border border-border rounded-lg px-4 py-2.5 bg-canvas text-main placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent" required aria-required="true" autocomplete="name" />
            </div>
            <div>
              <label for="onboard-email" class="block text-sm font-medium text-main mb-1.5">Email <span class="text-status-danger" aria-hidden="true">*</span></label>
              <input id="onboard-email" v-model="createForm.email" type="email" class="w-full border border-border rounded-lg px-4 py-2.5 bg-canvas text-main placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent" required aria-required="true" autocomplete="email" />
            </div>
            <div>
              <label for="onboard-nip" class="block text-sm font-medium text-main mb-1.5">NIP <span class="text-status-danger" aria-hidden="true">*</span></label>
              <input id="onboard-nip" v-model="createForm.nip" type="text" class="w-full border border-border rounded-lg px-4 py-2.5 bg-canvas text-main placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent" required aria-required="true" autocomplete="off" />
            </div>
            <div>
              <label for="onboard-start-date" class="block text-sm font-medium text-main mb-1.5">Tanggal Mulai Kerja <span class="text-status-danger" aria-hidden="true">*</span></label>
              <input id="onboard-start-date" v-model="createForm.start_date" type="date" class="w-full border border-border rounded-lg px-4 py-2.5 bg-canvas text-main focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent" required aria-required="true" />
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-subtle">
              <AppButton type="button" variant="secondary" @click="closeCreateModal" :disabled="submitting">Batal</AppButton>
              <AppButton type="submit" variant="primary" :loading="submitting">Ajukan</AppButton>
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
import { Calendar, UserPlus, X } from 'lucide-vue-next';
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
  router.post('/recruitment/onboarding', createForm.value, {
    onSuccess: () => {
      closeCreateModal();
      router.reload();
    },
    onFinish: () => {
      submitting.value = false;
    },
  });
};
</script>
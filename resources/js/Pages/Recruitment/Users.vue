<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-main">Pendaftaran Pengguna</h1>
      <AppButton variant="primary" @click="showCreateModal = true">
        <template #default>Tambah Pengguna</template>
      </AppButton>
    </div>

    <Card>
      <template #header>
        <h2 class="text-lg font-semibold text-main">Daftar Pengguna</h2>
        <p class="text-sm text-muted">Manajemen akun pengguna dan akses sistem</p>
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
              <span class="text-sm text-muted">{{ user.role }}</span>
              <span class="text-sm text-muted">{{ user.department }}</span>
              <StatusBadge :status="user.status" />
            </div>
          </div>
        </div>

        <div v-if="!users.data.length" class="text-center py-8 text-muted">Belum ada pengguna</div>

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
        <h2 class="text-xl font-bold mb-4">Tambah Pengguna Baru</h2>
        <form @submit.prevent="submitCreate">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">Nama</label>
              <input v-model="createForm.name" type="text" class="w-full border border-border rounded-md p-2 bg-canvas text-main" required />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Email</label>
              <input v-model="createForm.email" type="email" class="w-full border border-border rounded-md p-2 bg-canvas text-main" required />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Role</label>
              <select v-model="createForm.role" class="w-full border border-border rounded-md p-2 bg-canvas text-main" required>
                <option value="" disabled>Pilih Role</option>
                <option value="dosen">Dosen</option>
                <option value="tendik">Tendik</option>
                <option value="admin-bsdm">Admin BSDM</option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Unit Kerja</label>
                <select v-model="createForm.unit_id" class="w-full border border-border rounded-md p-2 bg-canvas text-main" required>
                  <option value="" disabled>Pilih Unit</option>
                  <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input v-model="createForm.password" type="password" class="w-full border border-border rounded-md p-2 bg-canvas text-main" required />
              </div>
            </div>
            <div class="flex justify-end gap-2">
              <AppButton type="button" variant="outline" @click="showCreateModal = false">Batal</AppButton>
              <AppButton type="submit" variant="primary" :disabled="submitting">Simpan</AppButton>
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
      role: string;
      department: string;
      status: string;
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
const departments = ref([
  { id: 1, name: 'Biro Sumber Daya Manusia' },
  { id: 2, name: 'Fakultas Teknologi Industri' },
  { id: 3, name: 'Fakultas Keguruan' },
]);

const createForm = ref({
  name: '',
  email: '',
  role: 'dosen',
  unit_id: '',
  password: '',
});

const submitCreate = () => {
  submitting.value = true;
  router.post('/recruitment/users', createForm.value, {
    onSuccess: () => {
      showCreateModal.value = false;
      createForm.value = {
        name: '',
        email: '',
        role: 'dosen',
        unit_id: '',
        password: '',
      };
    },
    onFinish: () => {
      submitting.value = false;
    },
  });
};
</script>
<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-main">BPJS Kesehatan & Ketenagakerjaan</h1>
      <AppButton variant="primary" @click="showRegisterModal = true">
        <template #default>Daftar BPJS</template>
      </AppButton>
    </div>

    <Card>
      <template #header>
        <h2 class="text-lg font-semibold text-main">Daftar Keanggotaan BPJS</h2>
        <p class="text-sm text-muted">Manajemen keanggotaan BPJS Kesehatan dan Ketenagakerjaan</p>
      </template>
      <template #default>
        <div class="space-y-4">
          <div
            v-for="bpjs in memberships.data"
            :key="bpjs.id"
            class="flex items-center justify-between p-4 bg-canvas rounded-lg"
          >
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 bg-accent/10 text-accent rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <div>
                <h3 class="font-medium text-main">{{ bpjs.employee?.full_name || 'Unknown' }}</h3>
                <p class="text-sm text-muted">{{ bpjs.bpjs_type === 'kesehatan' ? 'BPJS Kesehatan' : 'BPJS Ketenagakerjaan' }}</p>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <BpjsMaskedField :number="bpjs.masked_number || bpjs.membership_number" :can-reveal="false" />
              <StatusBadge :status="bpjs.status" />
              <AppButton
                v-if="bpjs.status === 'aktif'"
                variant="outline"
                size="sm"
                @click="cancelBpjs(bpjs.id)"
              >
                Batalkan
              </AppButton>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="memberships.last_page > 1" class="flex justify-center mt-4 gap-2">
          <Link
            v-for="link in memberships.links"
            :key="link.label"
            :href="link.url || '#'"
            class="px-3 py-1 rounded-md text-sm"
            :class="link.active ? 'bg-accent text-white' : 'bg-canvas text-main hover:bg-surface'"
            v-html="link.label"
          />
        </div>
      </template>
    </Card>

    <!-- Register Modal -->
    <div v-if="showRegisterModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-surface rounded-lg p-6 w-full max-w-md">
        <h2 class="text-xl font-bold mb-4">Daftar BPJS</h2>
        <form @submit.prevent="submitRegister">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">Tipe BPJS</label>
              <select v-model="registerForm.bpjs_type" class="w-full border border-border rounded-md p-2 bg-canvas text-main">
                <option value="kesehatan">BPJS Kesehatan</option>
                <option value="ketenagakerjaan">BPJS Ketenagakerjaan</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Nomor Keanggotaan</label>
              <input v-model="registerForm.membership_number" type="text" class="w-full border border-border rounded-md p-2 bg-canvas text-main" required />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Tanggal Pendaftaran</label>
              <input v-model="registerForm.registered_date" type="date" class="w-full border border-border rounded-md p-2 bg-canvas text-main" required />
            </div>
            <div class="flex justify-end gap-2">
              <AppButton type="button" variant="outline" @click="showRegisterModal = false">Batal</AppButton>
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
import { router, Link } from '@inertiajs/vue3';
import AppButton from '@/Components/UI/AppButton.vue';
import Card from '@/Components/UI/Card.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';
import BpjsMaskedField from '@/Components/UI/BpjsMaskedField.vue';

interface Props {
  memberships: {
    data: Array<{
      id: number;
      employee_id: number;
      employee?: { full_name: string; nip: string };
      bpjs_type: string;
      membership_number: string;
      masked_number?: string;
      registered_date: string;
      status: string;
      archived_at: string | null;
    }>;
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  bpjsTypes: string[];
}

const props = defineProps<Props>();
const memberships = ref(props.memberships);
const showRegisterModal = ref(false);
const submitting = ref(false);

const registerForm = ref({
  bpjs_type: 'kesehatan',
  membership_number: '',
  registered_date: new Date().toISOString().split('T')[0],
});

const submitRegister = () => {
  submitting.value = true;
  router.post(route('welfare.bpjs.store'), registerForm.value, {
    onSuccess: () => {
      showRegisterModal.value = false;
      registerForm.value = {
        bpjs_type: 'kesehatan',
        membership_number: '',
        registered_date: new Date().toISOString().split('T')[0],
      };
    },
    onFinish: () => {
      submitting.value = false;
    },
  });
};

const cancelBpjs = (id: number) => {
  if (!confirm('Yakin ingin membatalkan keanggotaan BPJS ini?')) return;
  router.post(route('welfare.bpjs.cancel', id));
};
</script>
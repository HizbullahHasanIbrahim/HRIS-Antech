<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
      <div>
        <h1 class="text-2xl font-bold text-main">BPJS Kesehatan & Ketenagakerjaan</h1>
        <p class="text-sm text-muted mt-1">Kelola keanggotaan BPJS Kesehatan dan Ketenagakerjaan</p>
      </div>
      <AppButton variant="primary" @click="openRegisterModal">
        <template #default>Daftar BPJS</template>
      </AppButton>
    </div>

    <Card :header="{ title: 'Daftar Keanggotaan BPJS', description: 'Manajemen keanggotaan BPJS Kesehatan dan Ketenagakerjaan' }">
      <template #default>
        <div class="space-y-3" v-if="memberships.data.length">
          <div
            v-for="bpjs in memberships.data"
            :key="bpjs.id"
            class="flex items-center justify-between p-4 bg-canvas rounded-xl hover:bg-surface transition-colors"
          >
            <div class="flex items-center gap-4 min-w-0">
              <div class="w-11 h-11 bg-brand-primary/10 text-brand-primary rounded-xl flex items-center justify-center flex-shrink-0">
                <Shield class="w-5 h-5" aria-hidden="true" />
              </div>
              <div class="min-w-0">
                <h3 class="font-medium text-main truncate">{{ bpjs.employee?.full_name || 'Unknown' }}</h3>
                <p class="text-sm text-muted truncate">{{ bpjs.bpjs_type === 'kesehatan' ? 'BPJS Kesehatan' : 'BPJS Ketenagakerjaan' }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
              <BpjsMaskedField :number="bpjs.masked_number || bpjs.membership_number" :can-reveal="false" />
              <StatusBadge :status="bpjs.status" />
              <AppButton
                v-if="bpjs.status === 'aktif'"
                variant="ghost"
                size="sm"
                @click="cancelBpjs(bpjs.id)"
              >
                Batalkan
              </AppButton>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <div class="mx-auto w-16 h-16 bg-canvas rounded-full flex items-center justify-center mb-4">
            <Shield class="w-8 h-8 text-muted" aria-hidden="true" />
          </div>
          <h3 class="text-lg font-medium text-main mb-1">Belum ada keanggotaan BPJS</h3>
          <p class="text-sm text-muted mb-6">Daftarkan keanggotaan BPJS pertama</p>
          <AppButton variant="primary" @click="openRegisterModal" class="w-full sm:w-auto">
            Daftar BPJS Pertama
          </AppButton>
        </div>

        <!-- Pagination -->
        <nav v-if="memberships.last_page > 1" class="mt-6" aria-label="Navigasi halaman BPJS">
          <div class="flex justify-center gap-1">
            <Link
              v-for="link in memberships.links"
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

    <!-- Register Modal -->
    <Teleport to="body">
      <div v-if="showRegisterModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="closeRegisterModal" role="dialog" aria-modal="true" aria-labelledby="register-bpjs-modal-title">
        <div class="bg-surface rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between px-6 py-4 border-b border-subtle sticky top-0 bg-surface z-10">
            <h2 id="register-bpjs-modal-title" class="text-xl font-bold text-main">Daftar BPJS</h2>
            <button type="button" @click="closeRegisterModal" class="text-muted hover:text-main transition-colors p-1 rounded-lg hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-brand-primary" aria-label="Tutup modal">
              <X class="w-5 h-5" aria-hidden="true" />
            </button>
          </div>
          <form @submit.prevent="submitRegister" class="p-6 space-y-5">
            <div>
              <label for="bpjs-type" class="block text-sm font-medium text-main mb-1.5">Tipe BPJS <span class="text-status-danger" aria-hidden="true">*</span></label>
              <select id="bpjs-type" v-model="registerForm.bpjs_type" class="w-full border border-border rounded-lg px-4 py-2.5 bg-canvas text-main focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent" required aria-required="true">
                <option value="kesehatan">BPJS Kesehatan</option>
                <option value="ketenagakerjaan">BPJS Ketenagakerjaan</option>
              </select>
            </div>
            <div>
              <label for="bpjs-number" class="block text-sm font-medium text-main mb-1.5">Nomor Keanggotaan <span class="text-status-danger" aria-hidden="true">*</span></label>
              <input id="bpjs-number" v-model="registerForm.membership_number" type="text" class="w-full border border-border rounded-lg px-4 py-2.5 bg-canvas text-main placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent" required aria-required="true" autocomplete="off" />
            </div>
            <div>
              <label for="bpjs-date" class="block text-sm font-medium text-main mb-1.5">Tanggal Pendaftaran <span class="text-status-danger" aria-hidden="true">*</span></label>
              <input id="bpjs-date" v-model="registerForm.registered_date" type="date" class="w-full border border-border rounded-lg px-4 py-2.5 bg-canvas text-main focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent" required aria-required="true" />
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-subtle">
              <AppButton type="button" variant="secondary" @click="closeRegisterModal" :disabled="submitting">Batal</AppButton>
              <AppButton type="submit" variant="primary" :loading="submitting">Simpan</AppButton>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Shield, X } from 'lucide-vue-next';
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

const openRegisterModal = () => {
  showRegisterModal.value = true;
  document.body.style.overflow = 'hidden';
};

const closeRegisterModal = () => {
  showRegisterModal.value = false;
  document.body.style.overflow = '';
};

const submitRegister = () => {
  submitting.value = true;
  router.post(route('welfare.bpjs.store'), registerForm.value, {
    onSuccess: () => {
      closeRegisterModal();
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
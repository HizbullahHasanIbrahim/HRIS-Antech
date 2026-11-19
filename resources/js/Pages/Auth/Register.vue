<template>
  <div class="min-h-screen bg-canvas flex items-center justify-center p-4">
    <div class="w-full max-w-lg bg-surface rounded-2xl border border-subtle p-8 shadow-xl">
      <div class="text-center mb-6">
        <Link href="/" class="inline-block mb-2">
          <div class="w-10 h-10 bg-accent rounded-xl flex items-center justify-center text-white font-bold mx-auto text-base shadow-md">A</div>
        </Link>
        <h1 class="text-2xl font-bold text-main">Pendaftaran & Aktivasi Akun</h1>
        <p class="text-muted text-sm mt-1">Biro Sumber Daya Manusia - UAD</p>
      </div>

      <!-- Segmented Control -->
      <div class="flex p-1 bg-canvas rounded-xl border border-subtle mb-6">
        <button
          @click="tab = 'pelamar'"
          :class="[
            'flex-1 py-2 text-sm font-semibold rounded-lg transition-all',
            tab === 'pelamar'
              ? 'bg-surface text-accent shadow-sm'
              : 'text-muted hover:text-main'
          ]"
        >
          Daftar Pelamar Baru
        </button>
        <button
          @click="tab = 'aktivasi'"
          :class="[
            'flex-1 py-2 text-sm font-semibold rounded-lg transition-all',
            tab === 'aktivasi'
              ? 'bg-surface text-accent shadow-sm'
              : 'text-muted hover:text-main'
          ]"
        >
          Aktivasi Pegawai Baru
        </button>
      </div>

      <!-- Tab 1: Pelamar Baru -->
      <form v-if="tab === 'pelamar'" @submit.prevent="submitPelamar">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-main mb-1">Nama Lengkap & Gelar</label>
            <input v-model="pelamarForm.full_name" type="text" required class="w-full px-4 py-2 border border-control rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent" placeholder="Cth: Dr. Ahmad Alfan, M.Kom." />
          </div>
          <div>
            <label class="block text-sm font-medium text-main mb-1">Email Aktif</label>
            <input v-model="pelamarForm.email" type="email" required class="w-full px-4 py-2 border border-control rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent" placeholder="email@uad.ac.id" />
          </div>
          <div>
            <label class="block text-sm font-medium text-main mb-1">Nomor Telepon / WhatsApp</label>
            <input v-model="pelamarForm.phone" type="text" required class="w-full px-4 py-2 border border-control rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent" placeholder="08123456789" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-main mb-1">Pendidikan Tertinggi</label>
              <select v-model="pelamarForm.highest_education" class="w-full px-4 py-2 border border-control rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent bg-surface">
                <option value="S1">S1</option>
                <option value="S2">S2</option>
                <option value="S3">S3</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-main mb-1">Kategori</label>
              <select v-model="pelamarForm.category" class="w-full px-4 py-2 border border-control rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent bg-surface">
                <option value="dosen">Dosen</option>
                <option value="tendik">Tendik</option>
              </select>
            </div>
          </div>
          <button type="submit" :disabled="pelamarForm.processing" class="w-full bg-accent hover:bg-accent-hover text-white py-3 rounded-lg font-semibold transition-all">
            Kirim Pendaftaran Pelamar
          </button>
        </div>
      </form>

      <!-- Tab 2: Aktivasi Pegawai Baru -->
      <form v-else @submit.prevent="submitAktivasi">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-main mb-1">NIP / Nomor Induk Pegawai</label>
            <input v-model="aktivasiForm.nip" type="text" required class="w-full px-4 py-2 border border-control rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent" placeholder="19900101..." />
          </div>
          <div>
            <label class="block text-sm font-medium text-main mb-1">Username Akun Baru</label>
            <input v-model="aktivasiForm.username" type="text" required class="w-full px-4 py-2 border border-control rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent" placeholder="username.pegawai" />
          </div>
          <div>
            <label class="block text-sm font-medium text-main mb-1">Password Baru</label>
            <input v-model="aktivasiForm.password" type="password" required class="w-full px-4 py-2 border border-control rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent" placeholder="Minimal 8 karakter" />
          </div>
          <button type="submit" :disabled="aktivasiForm.processing" class="w-full bg-accent hover:bg-accent-hover text-white py-3 rounded-lg font-semibold transition-all">
            Aktivasi Akun Sekarang
          </button>
        </div>
      </form>

      <div class="mt-6 text-center text-sm text-muted">
        Sudah punya akun? <Link href="/login" class="text-accent font-semibold hover:underline">Masuk disini</Link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';

const tab = ref<'pelamar' | 'aktivasi'>('pelamar');

const pelamarForm = useForm({
  full_name: '',
  email: '',
  phone: '',
  highest_education: 'S2',
  category: 'dosen',
});

const aktivasiForm = useForm({
  nip: '',
  username: '',
  password: '',
});

const submitPelamar = () => {
  pelamarForm.post('/api/v1/applicants', {
    onSuccess: () => {
      alert('Pendaftaran berhasil dikirim!');
    },
  });
};

const submitAktivasi = () => {
  aktivasiForm.post('/register', {
    onSuccess: () => {
      // Activated & redirected
    },
  });
};
</script>
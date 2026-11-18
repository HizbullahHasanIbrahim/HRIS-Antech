<template>
  <div class="min-h-screen bg-canvas flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-surface rounded-xl border border-subtle p-8">
      <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-main">Antech Async HRIS</h1>
        <p class="text-muted mt-1">Biro Sumber Daya Manusia - UAD</p>
      </div>

      <form @submit.prevent="login">
        <div class="mb-4">
          <label for="username" class="block text-sm font-medium text-main mb-1">
            Username <span class="text-status-danger">*</span>
          </label>
          <input
            id="username"
            v-model="form.username"
            type="text"
            required
            class="w-full px-4 py-2 border border-control rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
            placeholder="Masukkan username"
          />
        </div>

        <div class="mb-6">
          <label for="password" class="block text-sm font-medium text-main mb-1">
            Password <span class="text-status-danger">*</span>
          </label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            class="w-full px-4 py-2 border border-control rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
            placeholder="Masukkan password"
          />
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-brand-primary text-white py-2 px-4 rounded-lg font-medium hover:bg-brand-primary-dark focus:ring-2 focus:ring-brand-primary focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="form.processing" class="flex items-center justify-center">
            <span class="animate-spin mr-2">⏳</span>
            Memproses...
          </span>
          <span v-else>Masuk</span>
        </button>

        <p v-if="form.errors.general" class="text-status-danger text-sm mt-4 text-center">{{ form.errors.general }}</p>
      </form>

      <div class="mt-6 p-4 bg-brand-primary/5 rounded-lg text-sm text-brand-primary">
        <strong>Demo Accounts:</strong>
        <ul class="mt-2 space-y-1 text-xs">
          <li>admin.bsdm / password123</li>
          <li>kepala.biro / password123</li>
          <li>kepala.bagian / password123</li>
          <li>dosen.demo / password123</li>
          <li>tendik.demo / password123</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  username: '',
  password: '',
});

const login = () => {
  form.post(route('login'), {
    onSuccess: () => {
      // Toast handled by ToastContainer
    },
  });
};
</script>
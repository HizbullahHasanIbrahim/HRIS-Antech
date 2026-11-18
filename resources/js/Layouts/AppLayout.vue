<template>
  <div class="min-h-screen bg-canvas flex flex-col">
    <!-- Topbar -->
    <header class="h-16 bg-surface border-b border-subtle fixed top-0 inset-x-0 z-30 flex items-center px-4 lg:px-6 justify-between">
      <!-- Left side: Hamburger + Search -->
      <div class="flex items-center gap-3">
        <button
          @click="sidebarOpen = !sidebarOpen"
          class="lg:hidden p-2 rounded-lg hover:bg-canvas text-muted hover:text-main"
          aria-label="Toggle menu"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <a href="/" class="flex items-center gap-2">
          <div class="w-8 h-8 bg-brand-primary rounded-lg flex items-center justify-center text-white font-bold text-sm">
            A
          </div>
          <span class="font-semibold text-main text-base hidden sm:inline">Antech Async HRIS</span>
        </a>
      </div>

      <!-- Right side: Unit scope + User menu -->
      <div class="flex items-center gap-3">
        <!-- Unit Scope Badge (RULE-15) -->
        <div v-if="unitScope" class="hidden md:flex items-center gap-1.5 px-3 py-1 bg-brand-primary/10 rounded-full text-xs font-medium text-brand-primary">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          <span>{{ unitScope }}</span>
        </div>

        <!-- User Dropdown -->
        <div class="relative">
          <button
            @click="userMenuOpen = !userMenuOpen"
            class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-canvas transition-colors"
          >
            <div class="w-8 h-8 rounded-full bg-brand-primary text-white flex items-center justify-center font-semibold text-xs">
              {{ initials }}
            </div>
            <div class="hidden sm:block text-left text-xs leading-tight">
              <div class="font-semibold text-main">{{ user?.employee?.full_name || user?.username || 'User' }}</div>
              <div class="text-muted">{{ roleName }}</div>
            </div>
            <svg class="w-4 h-4 text-muted hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Dropdown menu -->
          <div
            v-if="userMenuOpen"
            class="absolute right-0 mt-2 w-48 bg-surface rounded-lg shadow-lg border border-subtle py-1 z-40 text-sm"
          >
            <div class="px-4 py-2 border-b border-subtle sm:hidden">
              <div class="font-medium text-main">{{ user?.employee?.full_name || user?.username }}</div>
              <div class="text-xs text-muted">{{ roleName }}</div>
            </div>
            <button
              @click="logout"
              class="w-full text-left px-4 py-2 text-status-danger hover:bg-status-danger-bg flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              Keluar
            </button>
          </div>
        </div>
      </div>
    </header>

    <div class="flex pt-16 min-h-screen">
      <!-- Mobile sidebar backdrop -->
      <div
        v-if="sidebarOpen"
        class="fixed inset-0 z-20 bg-slate-900/40 backdrop-blur-sm lg:hidden"
        @click="sidebarOpen = false"
      />

      <!-- Sidebar -->
      <aside
        :class="[
          'fixed lg:sticky top-16 left-0 h-[calc(100vh-4rem)] bg-brand-primary text-white z-20 transition-all duration-200 flex flex-col',
          sidebarOpen ? 'translate-x-0 w-64' : '-translate-x-full lg:translate-x-0 lg:w-64'
        ]"
      >
        <nav class="flex-1 overflow-y-auto p-3 space-y-4 text-sm">
          <!-- Main modules -->
          <div>
            <div class="px-3 mb-1 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Modul Utama</div>
            <div class="space-y-0.5">
              <a
                href="/dashboard"
                class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-brand-primary-dark transition-colors"
                :class="{ 'bg-brand-primary-dark text-brand-accent': $page.url === '/dashboard' || $page.url === '/' }"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard</span>
              </a>

              <!-- Pengajuan (Leave, Overtime, Allowance) -->
              <a
                href="/requests"
                class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-brand-primary-dark transition-colors"
                :class="{ 'bg-brand-primary-dark text-brand-accent': $page.url.startsWith('/requests') }"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span>Pengajuan</span>
              </a>
            </div>
          </div>

          <!-- Organisasi & Jadwal -->
          <div>
            <div class="px-3 mb-1 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Organisasi</div>
            <div class="space-y-0.5">
              <a
                href="/organization"
                class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-brand-primary-dark transition-colors"
                :class="{ 'bg-brand-primary-dark text-brand-accent': $page.url.startsWith('/organization') }"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>Organisasi & Pegawai</span>
              </a>
            </div>
          </div>

          <!-- Kinerja & Karir -->
          <div>
            <div class="px-3 mb-1 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Kinerja & Karir</div>
            <div class="space-y-0.5">
              <a
                href="/performance"
                class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-brand-primary-dark transition-colors"
                :class="{ 'bg-brand-primary-dark text-brand-accent': $page.url.startsWith('/performance') }"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Kinerja (BKD & SKP)</span>
              </a>
              <a
                href="/career"
                class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-brand-primary-dark transition-colors"
                :class="{ 'bg-brand-primary-dark text-brand-accent': $page.url.startsWith('/career') }"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Karir & Pengembangan</span>
              </a>
            </div>
          </div>

          <!-- Laporan Eksekutif -->
          <div>
            <div class="px-3 mb-1 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Eksekutif</div>
            <div class="space-y-0.5">
              <a
                href="/reports"
                class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-brand-primary-dark transition-colors"
                :class="{ 'bg-brand-primary-dark text-brand-accent': $page.url.startsWith('/reports') }"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Laporan Eksekutif</span>
              </a>
            </div>
          </div>
        </nav>

        <!-- Footer -->
        <div class="p-3 border-t border-slate-700 text-xs text-slate-400">
          <div>BSDM UAD &copy; 2026</div>
        </div>
      </aside>

      <!-- Main content -->
      <main class="flex-1 p-4 lg:p-6 overflow-y-auto">
        <slot />
      </main>
    </div>

    <!-- Toast Notifications Host -->
    <ToastContainer />
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import ToastContainer from '../Components/UI/ToastContainer.vue';

const page = usePage();
const sidebarOpen = ref(false);
const userMenuOpen = ref(false);

const user = computed(() => (page.props as any).auth?.user);
const roleName = computed(() => {
  const roles = (page.props as any).auth?.roles || [];
  return roles[0]?.name || 'Pegawai';
});

const unitScope = computed(() => {
  return (page.props as any).auth?.unit?.name || null;
});

const initials = computed(() => {
  const name = user.value?.employee?.full_name || user.value?.username || 'U';
  return name.substring(0, 2).toUpperCase();
});

const logout = () => {
  router.post('/logout');
};
</script>
<template>
  <LandingLayout>
    <Head title="Manajemen SDM Masa Depan" />

    <!-- Hero Section -->
    <section class="pt-32 pb-20 overflow-hidden">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center">
          <div class="text-center lg:text-left space-y-8">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-accent-soft text-accent text-xs font-bold tracking-wider uppercase animate-fade-in">
              <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-accent"></span>
              </span>
              Arsitektur HRIS Terpadu BSDM UAD
            </div>
            <h1 class="text-5xl lg:text-7xl font-extrabold text-main leading-[1.1] tracking-tight">
              Manajemen SDM <br/>
              <span class="text-transparent bg-clip-text bg-gradient-to-r from-accent to-accent-alt">Lebih Presisi.</span>
            </h1>
            <p class="text-xl text-muted max-w-2xl mx-auto lg:mx-0 leading-relaxed">
              Platform kepegawaian modern yang dirancang untuk efisiensi, akurasi, dan transparansi di lingkungan Universitas Ahmad Dahlan.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
              <Link href="/register" class="w-full sm:w-auto bg-accent hover:bg-accent-hover text-white px-8 py-4 rounded-full font-bold text-lg transition-all shadow-lg shadow-accent/25 hover:scale-105 active:scale-95">
                Mulai Bergabung
              </Link>
              <Link href="/login" class="w-full sm:w-auto bg-surface hover:bg-canvas text-main border border-subtle px-8 py-4 rounded-full font-bold text-lg transition-all hover:border-accent/30">
                Portal Pegawai
              </Link>
            </div>
          </div>

          <!-- Mockup Preview -->
          <div class="mt-16 lg:mt-0 relative group">
            <div class="absolute -inset-4 bg-gradient-to-tr from-accent/20 to-accent-alt/20 rounded-[2.5rem] blur-2xl group-hover:blur-3xl transition-all"></div>
            <div class="relative bg-surface border border-subtle rounded-[2rem] shadow-2xl overflow-hidden aspect-[4/3] flex flex-col">
              <!-- Browser Header -->
              <div class="h-10 bg-canvas border-b border-subtle flex items-center px-4 gap-1.5">
                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                <div class="w-3 h-3 rounded-full bg-green-400"></div>
              </div>
              <!-- Mockup Content -->
              <div class="flex-1 p-6 space-y-6">
                <div class="grid grid-cols-3 gap-4">
                  <div class="h-24 bg-canvas rounded-xl border border-subtle animate-pulse"></div>
                  <div class="h-24 bg-canvas rounded-xl border border-subtle animate-pulse delay-75"></div>
                  <div class="h-24 bg-canvas rounded-xl border border-subtle animate-pulse delay-150"></div>
                </div>
                <div class="h-48 bg-canvas rounded-xl border border-subtle flex items-end p-4 gap-2">
                  <div class="flex-1 bg-accent/40 h-[40%] rounded-t-lg"></div>
                  <div class="flex-1 bg-accent/60 h-[70%] rounded-t-lg"></div>
                  <div class="flex-1 bg-accent h-[90%] rounded-t-lg"></div>
                  <div class="flex-1 bg-accent/50 h-[50%] rounded-t-lg"></div>
                  <div class="flex-1 bg-accent/80 h-[80%] rounded-t-lg"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Bento Features Grid -->
    <section id="features" class="py-24 bg-surface">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 space-y-4">
          <h2 class="text-3xl font-bold text-main">Satu Sistem, Beragam Solusi</h2>
          <p class="text-muted">Dirancang khusus untuk memenuhi kompleksitas tata kelola SDM di UAD.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 auto-rows-[240px]">
          <!-- Feature 1: Recruitment -->
          <div class="md:col-span-2 bg-canvas border border-subtle rounded-[1.5rem] p-8 flex flex-col justify-between group hover:border-accent/50 transition-all">
            <div class="space-y-4">
              <div class="w-12 h-12 bg-accent rounded-xl flex items-center justify-center text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
              </div>
              <h3 class="text-2xl font-bold text-main">Rekrutmen & Onboarding</h3>
              <p class="text-muted">Proses seleksi transparan, terintegrasi, dan otomatisasi data pegawai baru.</p>
            </div>
            <div class="flex justify-end opacity-0 group-hover:opacity-100 transition-opacity">
              <div class="w-10 h-10 rounded-full bg-accent flex items-center justify-center text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
              </div>
            </div>
          </div>

          <!-- Feature 2: Attendance -->
          <div class="bg-canvas border border-subtle rounded-[1.5rem] p-8 flex flex-col gap-4 hover:border-accent/50 transition-all">
            <div class="w-10 h-10 bg-accent/10 text-accent rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="font-bold text-main">Presensi & Cuti</h3>
            <p class="text-sm text-muted">Pengajuan cuti, lembur, dan manajemen jadwal kerja real-time.</p>
          </div>

          <!-- Feature 3: Performance -->
          <div class="bg-canvas border border-subtle rounded-[1.5rem] p-8 flex flex-col gap-4 hover:border-accent/50 transition-all">
            <div class="w-10 h-10 bg-accent/10 text-accent rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <h3 class="font-bold text-main">BKD & SKP</h3>
            <p class="text-sm text-muted">Laporan kinerja Dosen & Tendik yang tervalidasi dan terukur.</p>
          </div>

          <!-- Feature 4: Career -->
          <div class="lg:col-span-2 bg-canvas border border-subtle rounded-[1.5rem] p-8 flex flex-col justify-between hover:border-accent/50 transition-all">
             <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-accent-soft text-accent rounded-xl flex items-center justify-center">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div>
                  <h3 class="text-xl font-bold text-main">Pengembangan Karir</h3>
                  <p class="text-sm text-muted">Tracking jabatan fungsional, kepangkatan, dan studi lanjut.</p>
                </div>
             </div>
             <div class="mt-4 grid grid-cols-2 gap-4">
                <div class="h-20 bg-surface rounded-xl border border-subtle p-3 flex flex-col justify-center">
                  <span class="text-[10px] text-muted uppercase font-bold tracking-widest">Dosen</span>
                  <span class="text-lg font-bold text-main">Jabatan Fungsional</span>
                </div>
                <div class="h-20 bg-surface rounded-xl border border-subtle p-3 flex flex-col justify-center">
                  <span class="text-[10px] text-muted uppercase font-bold tracking-widest">Tendik</span>
                  <span class="text-lg font-bold text-main">Kepangkatan</span>
                </div>
              </div>
          </div>

          <!-- Feature 5: Reports -->
          <div class="md:col-span-2 bg-gradient-to-br from-accent to-accent-alt rounded-[1.5rem] p-8 text-white flex flex-col justify-center gap-4">
            <h3 class="text-3xl font-bold">Laporan Eksekutif</h3>
            <p class="text-white/80 max-w-md">Data analitik komprehensif untuk pengambilan keputusan strategis oleh pimpinan universitas.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Vacancies Section -->
    <section id="vacancies" class="py-24 bg-canvas">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 space-y-4">
          <h2 class="text-3xl font-bold text-main">Lowongan Tersedia</h2>
          <p class="text-muted">Bergabunglah dengan tim terbaik di Universitas Ahmad Dahlan.</p>
        </div>
        <div class="bg-surface border border-subtle rounded-[1.5rem] p-8 text-center">
          <div class="w-16 h-16 mx-auto mb-6 bg-accent/10 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <h3 class="text-2xl font-bold text-main mb-2">Tidak Ada Lowongan Saat Ini</h3>
          <p class="text-muted mb-6 max-w-xl mx-auto">Silakan cek kembali kemudian atau hubungi BSDM UAD untuk informasi lebih lanjut.</p>
          <Link href="/register" class="inline-flex items-center gap-2 bg-accent hover:bg-accent-hover text-white px-6 py-3 rounded-full font-bold transition-all">
            Daftar & Dapatkan Notifikasi
          </Link>
        </div>
      </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-24 bg-surface">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 space-y-4">
          <h2 class="text-3xl font-bold text-main">Tentang Antech Async HRIS</h2>
          <p class="text-muted">Platform kepegawaian generasi baru untuk BSDM UAD.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
          <div class="text-center space-y-4">
            <div class="w-14 h-14 mx-auto bg-accent rounded-xl flex items-center justify-center text-white">
              <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
              </svg>
            </div>
            <h3 class="text-xl font-bold text-main">Terintegrasi</h3>
            <p class="text-muted">Satu platform untuk rekrutmen, presensi, kinerja, karir, kesejahteraan, dan pensiun.</p>
          </div>
          <div class="text-center space-y-4">
            <div class="w-14 h-14 mx-auto bg-accent rounded-xl flex items-center justify-center text-white">
              <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <h3 class="text-xl font-bold text-main">Real-Time</h3>
            <p class="text-muted">Data live, dashboard interaktif, dan laporan eksekutif untuk pengambilan keputusan cepat.</p>
          </div>
          <div class="text-center space-y-4">
            <div class="w-14 h-14 mx-auto bg-accent rounded-xl flex items-center justify-center text-white">
              <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
            </div>
            <h3 class="text-xl font-bold text-main">Aman & Terpercaya</h3>
            <p class="text-muted">RBAC berbasis unit, audit trail lengkap, dan enkripsi data sensitif (BPJS, gaji).</p>
          </div>
        </div>
      </div>
    </section>
  </LandingLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import LandingLayout from '@/Layouts/LandingLayout.vue';

defineOptions({ layout: LandingLayout });
</script>

<style scoped>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
  animation: fade-in 0.8s ease-out forwards;
}
</style>
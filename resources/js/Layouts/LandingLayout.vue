<template>
  <div class="min-h-screen bg-canvas selection:bg-accent/30">
    <!-- Landing Navbar: Translucent sticky ala Apple -->
    <nav class="fixed top-0 inset-x-0 z-50 bg-canvas/80 backdrop-blur-xl border-b border-subtle">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-accent rounded-lg flex items-center justify-center text-white font-bold text-sm">A</div>
          <span class="font-bold text-main tracking-tight">Antech Async HRIS</span>
        </div>
        
        <div class="hidden md:flex items-center gap-8 text-sm font-medium text-muted">
          <a href="#features" class="hover:text-accent transition-colors">Fitur</a>
          <a href="#vacancies" class="hover:text-accent transition-colors">Lowongan</a>
          <a href="#about" class="hover:text-accent transition-colors">Tentang</a>
        </div>

        <div class="flex items-center gap-4">
          <Link href="/login" class="text-sm font-semibold text-main hover:text-accent">Masuk</Link>
          <Link href="/register" class="bg-accent hover:bg-accent-hover text-white px-4 py-2 rounded-full text-sm font-semibold transition-all">Daftar Sekarang</Link>
        </div>
      </div>
    </nav>

    <main>
      <slot />
    </main>

    <footer class="bg-surface border-t border-subtle py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
          <div class="col-span-2">
            <div class="flex items-center gap-2 mb-4">
              <div class="w-8 h-8 bg-accent rounded-lg flex items-center justify-center text-white font-bold text-sm">A</div>
              <span class="font-bold text-main">Antech Async HRIS</span>
            </div>
            <p class="text-muted text-sm max-w-xs">Sistem Informasi Kepegawaian & Manajemen SDM Terpadu Biro Sumber Daya Manusia (BSDM) Universitas Ahmad Dahlan.</p>
          </div>
          <div>
            <h4 class="font-bold text-main mb-4 text-sm">Tautan Cepat</h4>
            <ul class="space-y-2 text-sm text-muted">
              <li><a href="#" class="hover:text-accent">Beranda</a></li>
              <li><a href="#features" class="hover:text-accent">Fitur</a></li>
              <li><a href="/login" class="hover:text-accent">Portal Pegawai</a></li>
            </ul>
          </div>
          <div>
            <h4 class="font-bold text-main mb-4 text-sm">Kontak</h4>
            <p class="text-sm text-muted">Kampus 4 UAD, Yogyakarta<br>Email: bsdm@uad.ac.id</p>
          </div>
        </div>
        <div class="pt-8 border-t border-subtle flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-muted">
          <p>&copy; 2026 BSDM UAD. All rights reserved.</p>
          <div class="flex gap-6">
            <a href="#" class="hover:text-accent">Privacy Policy</a>
            <a href="#" class="hover:text-accent">Terms of Service</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>
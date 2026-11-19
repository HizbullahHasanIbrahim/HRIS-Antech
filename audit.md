# 📄 LAPORAN AUDIT KHUSUS & RENCANA PERBAIKAN HRIS (v2.0 UI DESIGN SYSTEM & PRD/SDD ALIGNMENT)

| Metadata | Detail |
|---|---|
| **Nama Sistem** | Antech Async - Human Resource Information System (HRIS) BSDM UAD |
| **Tanggal Audit** | 19 November 2026 |
| **Acuan Dokumen** | `PRD v1.1`, `SDD v1.0`, `ui_design_system_guidelines_antech_async_hris.md (v2.0)` |
| **Fokus Utama** | **Audit Kepatuhan, Testing, Debugging, Quality Assurance (QA), & Rencana Perbaikan** |
| **Status Sistem** | Backend Core (100% Passed 37 Tests), Frontend v1 Base Ready, Design System v2.0 Gap Analysis |

---

## 📑 DAFTAR ISI

1. [Evaluasi Dokumen UI Design System v2.0](#1-evaluasi-dokumen-ui-design-system-v20)
2. [Hasil Audit Status Sedia Ada (Existing Codebase Audit)](#2-hasil-audit-status-sedia-ada-existing-codebase-audit)
   - 2.1 Audit Frontend & UI Components
   - 2.2 Audit Backend & Security Boundary
   - 2.3 Audit Testing & Coverage
   - 2.4 Audit QA & Performance (Bundle & Anti-FOUC)
3. [Daftar Kesenjangan Utama (Gap Analysis: Guidelines v2.0 vs Codebase)](#3-daftar-kesenjangan-utama-gap-analysis-guidelines-v20-vs-codebase)
4. [Rencana Perbaikan Terstruktur & Komprehensif (Action Plan & Roadmap)](#4-rencana-perbaikan-terstruktur--komprehensif-action-plan--roadmap)
   - Fase 1: Fondasi Theme (Dark Mode), Anti-FOUC & Shared Props
   - Fase 2: Pustaka Komponen UI Apple-Inspired & Refactoring AppLayout
   - Fase 3: Landing Page (`/`) & Flow Autentikasi Terpisah (`/login` & `/register`)
   - Fase 4: Pelengkapan Modul UI yang Masih Absen (P-01 s.d. P-16)
   - Fase 5: Penguatan Testing, Debugging & QA Automation
5. [Strategi Debugging, Matriks Pengujian & QA Checklist](#5-strategi-debugging-matriks-pengujian--qa-checklist)

---

## 1. Evaluasi Dokumen UI Design System v2.0

### 🟢 Kelebihan Utama Guidelines v2.0:
1. **Arsitektur Visual Apple-Inspired & Dual Theme (Light & Dark):** Pedoman warna (`--color-accent` Electric Violet `#6D28D9` / `#8B5CF6`, `--bg-canvas`, `--bg-surface`, `--bg-elevated`) sangat tajam dan presisi.
2. **Alur Autentikasi & Landing Page Publik yang Jelas:** Memisahkan halaman publik (`/`), Login (`/login`), dan Register (`/register` dengan Segmented Control: *Daftar Pelamar Baru* vs *Aktivasi Akun Pegawai*). Ini menjaga kepatuhan `RULE-02` dan `RULE-15`.
3. **Standar Komponen Lengkap:** Menyediakan spesifikasi untuk `EmployeeDirectoryCard`, `CategoryPill`, `SubTabs`, `SearchFilterBar` dengan shortcut `⌘F` / `Ctrl+K`, `ProgressStepper`, `BpjsMaskedField`, dan `RetirementChecklist`.
4. **Respon Cepat Skenario Pengecualian (`EX-01` s.d. `EX-12`):** Pemetaan jelas antara kondisi error bisnis backend dengan feedback visual frontend (misal `ConflictModal` untuk `HTTP 409` versi mismatch `RULE-17`).

---

## 2. Hasil Audit Status Sedia Ada (Existing Codebase Audit)

### 2.1 Audit Frontend & UI Components
* **Status Teruji:** 11 Halaman dasar terintegrasi (`Login`, `Dashboard`, `Requests`, `Organization`, `Performance`, `Career`, `Reports`).
* **Kepatuhan Rute:** 100% form submission sudah beralih dari `/api/v1/*` (Bearer token mismatch) ke Web Action Routes (`routes/web.php`) menggunakan `Inertia.post()` dengan Cookie Auth.
* **Temuan Kesenjangan UI:**
  - ❌ Komponen Dark Mode (`ThemeToggle`, `useTheme.ts`, CSS custom properties `:root` / `.dark`) **belum diimplementasikan**.
  - ❌ Anti-FOUC script belum dipasang di `resources/views/app.blade.php`.
  - ❌ Landing Page (`resources/js/Pages/Landing/Index.vue`) & `LandingLayout.vue` **belum ada**.
  - ❌ Register Page terpisah (`resources/js/Pages/Auth/Register.vue`) **belum ada**.
  - ❌ Komponen UI baru guidelines v2.0 (`EmployeeDirectoryCard`, `CategoryPill`, `SubTabs`, `SearchFilterBar`, `ProgressStepper`, `BpjsMaskedField`, `RetirementChecklist`, `DrillDownChart`) **belum dibuat**.

### 2.2 Audit Backend & Security Boundary
* **Status Teruji:** 100% 30 Migration (`0001` s.d. `0030`), Triggers MySQL 8.4, SoftDeletes (`RULE-13`), Activity Logs Append-Only (`RULE-14`), Unit Scoping (`RULE-15`), dan Optimistic Locking Version (`RULE-17`) sudah berjalan di level database & service Laravel.
* **Temuan Kesenjangan Backend:**
  - ⚠️ Middleware `HandleInertiaRequests` baru melempar `auth.user`, `auth.roles`, `auth.unit`. Belum dilengkapi dengan `auth.unit_scope` detail dan `auth.permissions` (`view_sensitive_welfare`, `manage_recruitment`, dll.) sesuai kontrak JSON Guidelines 1.4.

### 2.3 Audit Testing & Coverage
* **Status Test Suite:** **37 Passing Tests (204 Assertions)** di PHPUnit/Pest.
* **Temuan Kesenjangan Testing:**
  - ⚠️ Belum ada Feature Test khusus untuk alur `/register` (Registrasi Pelamar vs Aktivasi Pegawai).
  - ⚠️ Belum ada Test untuk pengunduhan berkas privat (`cv_path`, `certificate_path`) melalui `target="_blank"`.
  - ⚠️ Belum ada pengujian visual/E2E untuk pemutaran tema Dark Mode & pintasan `⌘F` / `Ctrl+K`.

### 2.4 Audit QA & Performance (Bundle & Anti-FOUC)
* **Kondisi Build:** `npm run build` sukses, namun Vite mengeluarkan peringatan chunk > 500kB:
  - `apexcharts.ssr.esm-*.js` (956 kB)
  - `app-*.js` (1.25 MB)
* **Temuan QA:** ApexCharts di `Reports/Index.vue` diimpor secara sinkron, memperlambat FCP/LCP awal. Perlu diubah menggunakan `defineAsyncComponent`.

---

## 3. Daftar Kesenjangan Utama (Gap Analysis: Guidelines v2.0 vs Codebase)

| Komponen / Fitur Guidelines v2.0 | Status di Codebase | Dampak Terhadap QA & System | Tindakan Perbaikan |
|---|---|---|---|
| **Dual Theme (Light & Dark Mode)** | ❌ Belum Ada | Pengguna tidak dapat berpindah mode; antarmuka silau/tidak konsisten | Buat `useTheme.ts`, `ThemeToggle.vue`, dan CSS variable di `app.css` |
| **Anti-FOUC Script di Head** | ❌ Belum Ada | Kedipan putih (*Flash of Unstyled Content*) saat refresh di Dark Mode | Pasang script IIFE di `resources/views/app.blade.php` |
| **Landing Page Publik (`/`)** | ❌ Belum Ada | Akses `/` langsung meredirect ke `/login` tanpa halaman pengenalan | Buat `LandingLayout.vue` & `Pages/Landing/Index.vue` |
| **Register Terpisah (`/register`)** | ❌ Belum Ada | Calon pelamar & aktivasi pegawai baru tidak memiliki antarmuka pendaftaran | Buat `Pages/Auth/Register.vue` (Tab Pelamar & Tab Aktivasi NIP) |
| **Contract Shared Props Inertia** | ⚠️ Sebagian (50%) | Permissions sensitif belum terkirim halus ke frontend | Update `HandleInertiaRequests.php` & `hris.d.ts` |
| **Pustaka Komponen v2.0** | ❌ Belum Ada | Tampilan direktori pegawai masih berupa tabel biasa tanpa kartu 3-kolom | Scaffold komponen `UI/*` & `Domain/*` sesuai spec v2.0 |
| **Lazy Loading ApexCharts** | ⚠️ Synchronous | Bundle size JS mencapai > 2.2 MB (mengganggu LCP/FCP) | Gunakan `defineAsyncComponent(() => import('vue3-apexcharts'))` |
| **Halaman Modul Terpisah (P-01 s.d. P-16)** | ⚠️ Baru 5 Modul Utama | 8 domain bisnis (Rekrutmen, BPJS, Pensiun, Diklat, dll) belum ada halaman UI | Scaffold Halaman Vue di `resources/js/Pages/` |

---

## 4. Rencana Perbaikan Terstruktur & Komprehensif (Action Plan & Roadmap)

Rencana perbaikan dibagi menjadi **5 Fase Eksekusi** yang dapat dijalankan secara bertahap dan terukur:

```text
+-----------------------------------------------------------------------------------+
| FASE 1: FONDASI THEME & INERTIA CONTRACT                                          |
| - Setup CSS Variables (Electric Violet & Dual-Theme), useTheme, Anti-FOUC Script  |
| - Update HandleInertiaRequests.php (Unit Scope & Permissions breakdown)           |
+-----------------------------------------+-----------------------------------------+
                                          |
                                          v
+-----------------------------------------------------------------------------------+
| FASE 2: PUSTAKA KOMPONEN UI v2.0 & REFACTORING APPLAYOUT                          |
| - Buat ThemeToggle, CategoryPill, SubTabs, SearchFilterBar (⌘F), ProgressStepper |
| - Update AppLayout (Collapsible Sidebar 252px/72px, Light/Dark styling)           |
+-----------------------------------------+-----------------------------------------+
                                          |
                                          v
+-----------------------------------------------------------------------------------+
| FASE 3: LANDING PAGE & AUTH FLOW TERPISAH                                         |
| - LandingLayout & Pages/Landing/Index.vue (Hero, Pain Point Contrast, Bento Cards)|
| - AuthLayout & Pages/Auth/Register.vue (Segmented Control: Pelamar vs Aktivasi)  |
+-----------------------------------------+-----------------------------------------+
                                          |
                                          v
+-----------------------------------------------------------------------------------+
| FASE 4: PELENGKAPAN MODUL UI PRD (P-01 s.d. P-16)                                 |
| - Recruitment/ (Vacancies, Selection Stages), Schedules/, Welfare/Bpjs/          |
| - Retirements/ (Checklist EX-09), Audit/ActivityLogs/ (JSON Diff Drawer)          |
+-----------------------------------------+-----------------------------------------+
                                          |
                                          v
+-----------------------------------------------------------------------------------+
| FASE 5: QA AUTOMATION, BUNDLE OPTIMIZATION & TESTING                              |
| - Code-splitting ApexCharts (defineAsyncComponent)                                |
| - Tambah Feature Tests (Register, File Download, Optimistic Locking 409)          |
+-----------------------------------------------------------------------------------+
```

---

### Detail Eksekusi per Fase:

### 🔹 FASE 1: Fondasi Theme (Dark Mode), Anti-FOUC & Shared Props
1. **CSS Variables (`resources/css/app.css`):**
   - Mendefinisikan `:root` (Light Mode) dan `.dark` (Dark Mode) untuk seluruh token warna (`--color-accent: #6D28D9`, `--bg-canvas: #F5F5F7` / `#09090D`, `--bg-surface`, `--border-subtle`, dll).
2. **Anti-FOUC Script (`resources/views/app.blade.php`):**
   - Menambahkan skrip IIFE di `<head>` untuk membaca `localStorage.getItem('hris-theme')` atau `prefers-color-scheme`.
3. **Composable Theme (`resources/js/Composables/useTheme.ts`):**
   - Mengelola state `theme` (`'light'` | `'dark'` | `'system'`) dan menyinkronkan class `.dark` pada `document.documentElement`.
4. **Update `HandleInertiaRequests.php` & `hris.d.ts`:**
   - Memperluas payload `auth` dengan `unit_scope` (is_global, unit_ids, label) dan `permissions` lengkap.

### 🔹 FASE 2: Pustaka Komponen UI Apple-Inspired & Refactoring AppLayout
1. **Scaffold Komponen UI (`resources/js/Components/UI/`):**
   - `ThemeToggle.vue`: Switcher mode terang/gelap/sistem dengan ikon Sun/Moon/Monitor.
   - `CategoryPill.vue`: Pill `DOSEN` (ungu lembut), `TENDIK` (biru lembut).
   - `SubTabs.vue`: Navigasi tab underline tipis dengan aksen violet.
   - `SearchFilterBar.vue`: Input pencarian dengan shortcut `⌘F` / `Ctrl+K` dan tombol filter.
   - `ProgressStepper.vue`: Visualisasi tahap seleksi rekrutmen & 4 jenjang fungsional Dosen.
   - `BpjsMaskedField.vue`: Penampilan nomor BPJS ter-masking (`********1234`).
   - `RetirementChecklist.vue`: Checklist validasi 7 pengajuan aktif untuk pensiun (`EX-09`).
   - `DrillDownChart.vue`: Async wrapper ApexCharts yang reaktif terhadap perpindahan tema.
2. **Refactoring `AppLayout.vue`:**
   - Menambahkan kemampuan collapsible sidebar (`252px` ke `72px`), penyempurnaan styling light/dark, dan indikator *Unit Scope* di topbar.

### 🔹 FASE 3: Landing Page Publik (`/`) & Flow Autentikasi Terpisah (`/login` & `/register`)
1. **Landing Page (`resources/js/Pages/Landing/Index.vue` & `LandingLayout.vue`):**
   - Translucent Sticky Navbar (`backdrop-blur-xl`).
   - Hero Section 2-kolom dengan pratinjau antarmuka macOS-style.
   - Seksi perbandingan *Cara Lama vs Dengan Antech Async*.
   - Bento Feature Cards (6 modul utama) & Seksi Lowongan Terbuka.
2. **Halaman Register Terpisah (`resources/js/Pages/Auth/Register.vue` & `AuthLayout.vue`):**
   - Segmented Control 2 Tab: *Daftar Pelamar Baru* (`P-01`/`P-02`) vs *Aktivasi Akun Pegawai* (NIP + Email verifikasi).

### 🔹 FASE 4: Pelengkapan Modul UI yang Masih Absen (P-01 s.d. P-16)
1. **Recruitment (`resources/js/Pages/Recruitment/`):** Halaman Lowongan, Pelamar, Tahap Seleksi (Nilai AIK `RULE-01`), dan Tombol Konversi Pegawai (`RULE-02`).
2. **Schedules (`resources/js/Pages/Schedules/`):** Matrix jadwal kerja 7 hari & versi baru.
3. **Welfare/Bpjs (`resources/js/Pages/Welfare/Bpjs/`):** Pendaftaran & monitoring BPJS terproteksi (`RULE-16`).
4. **Retirements (`resources/js/Pages/Retirements/`):** Tracker Pensiun & `RetirementChecklist` (`EX-09`).
5. **Audit/ActivityLogs (`resources/js/Pages/Audit/ActivityLogs/`):** Read-only log viewer & Side-Drawer JSON Diff.

### 🔹 FASE 5: Penguatan Testing, Debugging & QA Automation
1. **Bundle Optimization:** Mengganti impor langsung `VueApexCharts` dengan `defineAsyncComponent` untuk menurunkan ukuran chunk JS di bawah 500kB.
2. **Penambahan Test Cases Baru:**
   - Test Registrasi Pelamar & Aktivasi Pegawai (`tests/Feature/AuthRegisterTest.php`).
   - Test Pengunduhan Berkas Privat (`tests/Feature/PrivateFileDownloadTest.php`).
   - Test E2E/Flow untuk Optimistic Locking (`HTTP 409`) dan Unit Scoping (`RULE-15`).

---

## 5. Strategi Debugging, Matriks Pengujian & QA Checklist

### 🛠️ 5.1 Strategi Debugging Kunci
1. **Handling Multi-Guard Auth Mismatch (Web Session vs API Token):**
   - *Prinsip:* Seluruh submission dari form Vue Inertia **WAJIB** dikirim ke Web Routes (`routes/web.php`) yang ditangani oleh Web Action Controllers dan mengembalikan `back()->with(...)`. Jangan mengirim dari Inertia ke `/api/v1/*`.
2. **Debugging Optimistic Locking (`HTTP 409` / `RULE-17`):**
   - Bila data diubah aktor lain secara bersamaan, backend melempar `ConcurrencyConflictException` (`409`). Frontend menangkapnya via callback `onError` atau flash payload dan menampilkan `ConflictModal` untuk memuat ulang data.
3. **Debugging Activity Log Immutability (`RULE-13` & `RULE-14`):**
   - Setiap upaya `UPDATE` atau `DELETE` pada tabel `activity_logs` akan digagalkan oleh MySQL Trigger dengan `SQLSTATE 45000`. Pengujian harus memverifikasi bahwa log tidak dapat diubah bahkan oleh `DB::statement()`.

---

### 🧪 5.2 Matriks Pengujian QA (Testing Matrix)

| Modul / Fitur | Jenis Pengujian | Target / Ekspektasi | File Test Terkait |
|---|---|---|---|
| **Auth & Registrasi** | Feature Test | Login sukses, Registrasi Pelamar sukses, Aktivasi NIP valid | `tests/Feature/AuthRegisterTest.php` |
| **Cuti, Lembur & Tunjangan** | Feature Test | Anti self-approval (`RULE-14`), Saldo berkurang, Tanggal bentrok ditolak | `tests/Feature/LeaveRequestServiceTest.php` |
| **Kinerja BKD & SKP** | Feature Test | Auto-sum SKS, Immutability status `final`, Revisi berversi | `tests/Feature/WebPerformanceCareerFlowTest.php` |
| **Unit Scoping (RBAC)** | Feature Test | User non-BSDM hanya melihat data unit kerjanya (`RULE-15`) | `tests/Feature/WebReportsFlowTest.php` |
| **Activity Logs & Triggers** | Feature Test | Prevent update/delete pada `activity_logs`, Catat `ACCESS_DENIED` | `tests/Feature/DatabaseSchemaTest.php` |
| **File Download Privat** | Feature Test | File CV/Sertifikat hanya bisa diunduh oleh user berhak | `tests/Feature/VerificationFaseSatuSampaiTigaTest.php` |
| **Optimistic Locking** | Integration Test | HTTP 409 dilempar saat `version` mismatch | `tests/Feature/PhaseTwoFeaturesTest.php` |

---

### 📋 5.3 Checklist Kualitas (QA Final Sign-Off Checklist)

- [ ] **100% Test Passing:** Seluruh unit & feature tests di `php artisan test` lulus tanpa error.
- [ ] **Zero Code Formatting Issue:** `./vendor/bin/pint --test` bersih dari kesalahan linting PHP.
- [ ] **Build Frontend Clean:** `npm run build` sukses tanpa warning chunk masif (>500kB) setelah async component applied.
- [ ] **Dual Theme Anti-FOUC:** Halaman di-refresh dalam mode `dark` tanpa kedipan latar putih.
- [ ] **Keyboard Accessibility:** Shortcut `⌘F` / `Ctrl+K` dapat memfokuskan input pencarian di seluruh modul.
- [ ] **Data Immutability Guard:** Ikon gembok dan status *read-only* tampil pada rekaman berstatus final (`disetujui`, `ditolak`, `dibatalkan`, `final`).
- [ ] **No Hardcoded URLs:** Tidak ada endpoint `/api/v1/` yang dipanggil langsung dari komponen Vue Inertia.

---

*Laporan audit dan rencana perbaikan ini disusun secara terstruktur untuk memastikan Antech Async HRIS BSDM UAD memiliki kualitas enterprise, aman, performan, dan sepenuhnya patuh terhadap spesifikasi PRD v1.1, SDD v1.0, serta UI Design System Guidelines v2.0.*

# UI DESIGN SYSTEM & FRONTEND GUIDELINES

## Antech Async - Human Resource Information System (HRIS)

| Metadata | Detail |
|---|---|
| Nama Sistem | Antech Async - Sistem Informasi Kepegawaian dan Manajemen Sumber Daya Manusia (HRIS) |
| Organisasi / Domain | Biro Sumber Daya Manusia (BSDM), Universitas Ahmad Dahlan |
| Versi Dokumen | 2.0 (Apple-Inspired Minimalist, Light/Dark Mode, Landing & Separate Auth Flow) |
| Status | Draft - Pendamping `PRD v1.1` & `SDD v1.0` |
| Tanggal | 19 November 2026 |
| Stack Frontend | **Inertia.js (v1/v2) + Vue 3 atau React + Tailwind CSS** |
| Ikon & Grafik | Lucide / Heroicons (stroke `1.5px`–`1.75px`) & ApexCharts (`vue3-apexcharts` / `react-apexcharts`) |
| Tema Visual | **Dual Theme: Light Mode & Dark Mode** (`light` / `dark` / `system`) |
| Backend & Database | Laravel 11.x (PHP 8.3+) & MySQL 8.4 LTS (InnoDB) |

---

## 1. Filosofi Desain, Alur Pengguna & Arsitektur Frontend

Antarmuka Antech Async HRIS memadukan **estetika minimalis presisi ala Apple** (ruang bernapas lega, tipografi tajam, sudut membulat konsisten, bayangan lembut, dan *frosted glass* halus), **tata letak operasional modern** (sidebar terang/gelap yang bersih, tab navigasi sekunder, dan tampilan *Directory Card Grid* + *Table* + *Org Chart*), serta **alur masuk produk yang lengkap** mulai dari halaman publik hingga dashboard internal.

### 1.1 Alur Perjalanan Pengguna (*End-to-End User Flow*)

```text
+----------------------------------+
|     1. LANDING PAGE (Publik)     |  Route: /
|  - Hero & Ringkasan HRIS         |  Layout: LandingLayout (Translucent Glass Navbar)
|  - Cara Lama vs Antech Async     |  Aksi: [Masuk Portal] | [Daftar / Lowongan] | [Toggle Tema]
|  - Modul Utama & Lowongan Dibuka |
+----------------+-----------------+
                 |
        +--------+--------+
        |                 |
        v                 v
+---------------+  +----------------------------------+
| 2A. LOGIN     |  | 2B. REGISTER (Terpisah)          |  Routes: /login & /register
| Route: /login |  | Route: /register                 |  Layout: AuthLayout (Split / Frosted Card)
| Pegawai,      |  | - Registrasi Pelamar Eksternal   |  Patuh RULE-02 & RULE-15:
| Atasan, BSDM, |  |   (P-01/P-02 Lowongan Dosen/     |  Pendaftaran mandiri hanya membuat akun
| Pimpinan,     |  |   Tendik) atau Aktivasi Akun     |  pelamar / aktivasi NIP terverifikasi
| Admin Sistem  |  |   Pegawai Terdaftar              |
+-------+-------+  +----------------+-----------------+
        |                           |
        +-------------+-------------+
                      | Authenticated Session
                      v
+-----------------------------------------------------+
|           3. HRIS DASHBOARD & MODUL INTI            |  Route: /dashboard, /employees, dst.
|  Layout: AppLayout (Persistent Shell + Light/Dark)  |  RBAC + Unit Scope + Live Filter
|  12 Menu Navigasi & 17 Proses Bisnis BSDM UAD       |
+-----------------------------------------------------+
```

### 1.2 Prinsip Utama Antarmuka
1. **Clarity & Breathing Room (Apple HIG Inspired):** Kurangi garis kasar dan warna berat. Gunakan perbedaan *surface luminance*, border *hairline* tipis, dan ruang kosong terukur agar data kepegawaian yang padat tetap terasa ringan dibaca.
2. **Dual-Mode First (Light & Dark Mode):** Setiap komponen, badge status, *glass panel*, hingga grafik ApexCharts dirancang serentak untuk mode terang dan gelap tanpa warna *hardcoded*.
3. **Kejelasan Status & *Immutability*:** Status final (`disetujui`, `ditolak`, `dibatalkan`, `final`, `selesai`) selalu menampilkan ikon gembok kecil sebagai penanda bahwa data terkunci (`RULE-11`, `EX-05`).
4. **Transparansi Cakupan Akses (*Unit Scope*):** Indikator cakupan unit kerja aktif tampil jelas di header aplikasi agar pengguna sadar data yang difilter RBAC (`RULE-15`).
5. **Konvensi Waktu & Angka:** Timestamp UTC dari MySQL 8.4 dirender ke `Asia/Jakarta` (WIB); angka tabel (NIP, SKS, skor SKP, nominal) memakai `tabular-nums` dan format `id-ID`.

### 1.3 Struktur Direktori Frontend (`resources/js/`)

```text
resources/js/
├── app.ts / app.js                    # Bootstrap Inertia.js + Theme initializer
├── Layouts/
│   ├── LandingLayout.(vue|tsx)        # Translucent Sticky Navbar + Footer Publik
│   ├── AuthLayout.(vue|tsx)           # Layout Terpisah Login & Register (Clean Minimalist)
│   └── AppLayout.(vue|tsx)            # Persistent App Shell (Collapsible Sidebar + Topbar + Toast)
├── Components/
│   ├── UI/
│   │   ├── ThemeToggle.(vue|tsx)      # Switcher Light / Dark / System (Sun/Moon/Monitor)
│   │   ├── AppButton.(vue|tsx)        # Primary Violet, Secondary Glass, Danger, Ghost + Spinner
│   │   ├── StatusBadge.(vue|tsx)      # Dot-pill badge (• Active) + ikon gembok status final
│   │   ├── CategoryPill.(vue|tsx)     # Pill kategori (DOSEN, TENDIK, Kontrak, Studi S3)
│   │   ├── SubTabs.(vue|tsx)          # Underline tabs (Employee List | Directory | ORG Chart)
│   │   ├── SearchFilterBar.(vue|tsx)  # Input cari + shortcut badge (⌘F / Ctrl+K) + tombol Filter
│   │   ├── DataTable.(vue|tsx)        # Clean table, skeleton loader, empty state, pagination
│   │   ├── SideDrawer.(vue|tsx)       # Slide-over panel kanan (520px) + frosted backdrop
│   │   ├── ReasonModal.(vue|tsx)      # Modal wajib alasan (RULE-12) untuk tolak/batal/revisi
│   │   ├── ConflictModal.(vue|tsx)    # Modal konflik HTTP 409 Optimistic Locking (EX-04)
│   │   ├── ProgressStepper.(vue|tsx)  # Visualisasi tahapan seleksi & jenjang jabatan Dosen
│   │   └── ToastContainer.(vue|tsx)   # Floating pill toast kanan atas dari Inertia flash
│   └── Domain/
│       ├── EmployeeDirectoryCard.(vue|tsx) # Kartu pegawai 3-kolom (avatar, status, ⋮ menu, metadata)
│       ├── LeaveQuotaCards.(vue|tsx)
│       ├── BpjsMaskedField.(vue|tsx)
│       ├── RetirementChecklist.(vue|tsx)
│       └── DrillDownChart.(vue|tsx)   # Wrapper ApexCharts adaptif Light/Dark + klik filter
├── Composables/ (atau Hooks/)
│   ├── useTheme.(ts|js)               # State Light/Dark/System + sinkronisasi localStorage & ApexCharts
│   ├── useLiveFilter.(ts|js)          # Debounce 300ms + router.get partial reload
│   ├── usePermission.(ts|js)          # Helper cek role, izin sensitif, & unit_scope
│   └── useToast.(ts|js)               # Sinkronisasi page.props.flash ke antrean toast
├── Types/
│   └── hris.d.ts                      # Interface TypeScript 29 entitas & shared props
└── Pages/
    ├── Landing/Index.(vue|tsx)        # Landing Page publik HRIS BSDM UAD
    ├── Auth/
    │   ├── Login.(vue|tsx)            # Halaman Login terpisah
    │   └── Register.(vue|tsx)         # Halaman Register Pelamar / Aktivasi Pegawai terpisah
    ├── Dashboard/Index.(vue|tsx)
    ├── Organization/                  # Tabs: Employee List | Directory (Card Grid) | ORG Chart
    ├── Recruitment/                   # Vacancies, Applicants, Selection Stages, Onboarding
    ├── Requests/                      # Leave, Overtime, Allowance
    ├── Schedules/                     # Work Schedules & Weekly Matrix
    ├── Career/                        # Academic Ranks, Staff Ranks, Study Leaves, Certifications, Trainings
    ├── Performance/                   # Workload (BKD/LKD) & SKP
    ├── Welfare/Bpjs/                  # Kepesertaan BPJS
    ├── Retirements/                   # Monitoring, Checklist EX-09, SK Pensiun
    ├── Reports/                       # RPT-01 s.d. RPT-06
    ├── Admin/UsersRoles/              # Manajemen User, Role, & Unit Scope
    └── Audit/ActivityLogs/            # Daftar & Side-Drawer JSON Diff (Read-Only)
```

### 1.4 Kontrak Shared Props Inertia (`HandleInertiaRequests`)

```json
{
  "auth": {
    "user": {
      "id": 12,
      "username": "ahmad.alfan",
      "employee_id": 45,
      "full_name": "Dr. Ahmad Alfan, M.Kom.",
      "avatar_url": null,
      "category": "DOSEN",
      "highest_education": "S3",
      "unit_name": "Program Studi Sistem Informasi"
    },
    "roles": ["atasan_langsung", "pegawai"],
    "unit_scope": {
      "is_global": false,
      "unit_ids": [7],
      "label": "Prodi Sistem Informasi"
    },
    "permissions": {
      "view_sensitive_welfare": false,
      "manage_recruitment": false,
      "manage_performance": false,
      "manage_users": false,
      "aggregate_reports_only": false
    }
  },
  "flash": {
    "success": null,
    "error": null,
    "warning": null,
    "conflict": null
  }
}
```

---

## 2. Design Tokens (Apple-Inspired + Dual Theme Light & Dark Mode)

Seluruh warna didefinisikan menggunakan **CSS Custom Properties** pada `:root` (Light Mode) dan `.dark` (Dark Mode), lalu dipetakan ke konfigurasi Tailwind CSS (`@theme` di Tailwind v4 atau `tailwind.config.js`). Aksen utama mengadopsi warna **Electric Violet** modern yang tajam dari referensi dashboard, dipadukan dengan permukaan monokrom bersih khas Apple.

### 2.1 Palet Warna Dual-Mode (Light & Dark)

| Kategori | CSS Variable | Light Mode (`:root`) | Dark Mode (`.dark`) | Penggunaan Utama |
|---|---|---|---|---|
| **Brand Primary** | `--color-accent` | `#6D28D9` | `#8B5CF6` | Primary Button (`+ Add New Employee`), indikator tab aktif, highlight landing |
| **Brand Hover** | `--color-accent-hover` | `#5B21B6` | `#7C3AED` | Hover & pressed state tombol utama |
| **Brand Soft Surface** | `--color-accent-soft` | `#F5F3FF` | `rgba(139, 92, 246, 0.16)` | Latar item sidebar aktif, pill kategori `DOSEN`, highlight perbandingan |
| **Brand Secondary Accent** | `--color-accent-alt` | `#D946EF` | `#E879F9` | Aksen gradien halus pada logo & ilustrasi hero landing page |
| **App Canvas** | `--bg-canvas` | `#F5F5F7` | `#09090D` | Latar belakang utama halaman & area konten di luar kartu |
| **Primary Surface** | `--bg-surface` | `#FFFFFF` | `#121218` | Latar Sidebar, Card pegawai, Table, Modal, dan Side-Drawer |
| **Elevated Surface** | `--bg-elevated` | `#FBFBFD` | `#1A1A24` | Input pencarian, header tabel, kartu ringkasan, popover dropdown |
| **Glass Surface** | `--bg-glass` | `rgba(255, 255, 255, 0.78)` | `rgba(18, 18, 24, 0.78)` | Navbar landing page, topbar sticky, backdrop modal (`backdrop-blur-xl`) |
| **Hairline Border** | `--border-subtle` | `#E5E7EB` | `rgba(255, 255, 255, 0.08)` | Garis tepi Sidebar, Card, pemisah baris tabel |
| **Control Border** | `--border-control` | `#D1D5DB` | `rgba(255, 255, 255, 0.15)` | Border input form, tombol Secondary, dan tombol Filter |
| **Text Primary** | `--text-primary` | `#111827` | `#F5F5F7` | Judul halaman, nama pegawai, angka KPI, konten utama |
| **Text Secondary** | `--text-secondary` | `#6B7280` | `#9CA3AF` | Jabatan/unit subtitle, label metadata, menu sidebar nonaktif |
| **Text Muted** | `--text-muted` | `#9CA3AF` | `#6B7280` | Section label (`MAIN MENU`, `ORGANISATION`), placeholder, shortcut `⌘F` |
| **Success** | `--status-success-fg` / `bg` | `#059669` / `#ECFDF5` | `#34D399` / `rgba(16, 185, 129, 0.14)` | Status `aktif`, `disetujui`, `diterima`, `lolos`, `final`, `selesai` |
| **Warning** | `--status-warning-fg` / `bg` | `#B45309` / `#FFFBEB` | `#FBBF24` / `rgba(245, 158, 11, 0.14)` | Status `diajukan`, `proses`, `menunggu`, `diproses`, `berjalan` |
| **Danger** | `--status-danger-fg` / `bg` | `#E11D48` / `#FFF1F2` | `#FB7185` / `rgba(244, 63, 94, 0.14)` | Danger button, `ditolak`, `gugur`, `tidak_lulus`, error inline |
| **Info** | `--status-info-fg` / `bg` | `#2563EB` / `#EFF6FF` | `#60A5FA` / `rgba(59, 130, 246, 0.14)` | Status `draf`, `diusulkan`, `direncanakan`, `dijadwalkan`, pill `TENDIK` |
| **Neutral / Archive** | `--status-neutral-fg` / `bg` | `#4B5563` / `#F3F4F6` | `#9CA3AF` / `rgba(156, 163, 175, 0.14)` | Status `dibatalkan`, `ditutup`, `nonaktif`, `pensiun`, `batal` |

### 2.2 Arsitektur Dark Mode & Light Mode (`useTheme`)

1. **Tiga Mode Pilihan:** `Light`, `Dark`, dan `System` (mengikuti `prefers-color-scheme` OS pengguna).
2. **Anti-FOUC (Bebas Kedipan Putih saat Refresh):** Skrip sinkron singkat wajib diletakkan di dalam `<head>` pada `resources/views/app.blade.php` sebelum aset Vite dimuat:
   ```html
   <script>
     (function () {
       const saved = localStorage.getItem('hris-theme') || 'system';
       const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
       if (saved === 'dark' || (saved === 'system' && prefersDark)) {
         document.documentElement.classList.add('dark');
       }
     })();
   </script>
   ```
3. **Sinkronisasi Grafik ApexCharts:** Komponen `DrillDownChart` membaca nilai reaktif `isDark` dari `useTheme()` untuk mengganti `theme.mode` (`'light'` / `'dark'`), warna label sumbu (`--text-secondary`), warna garis grid (`--border-subtle`), serta gaya tooltip secara instan tanpa *reload*.
4. **Lokasi Tombol Switcher (`ThemeToggle`):** Tersedia di tiga titik konsisten: pojok kanan Navbar Landing Page, pojok kanan atas halaman Login/Register, dan di Topbar / footer profil Sidebar Dashboard.

### 2.3 Tipografi (Apple Precision Hierarchy)

* **Font Utama (Display & UI):** `-apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Inter', 'Segoe UI', sans-serif`.
* **Font Monospace (Identitas & Audit):** `'SF Mono', 'JetBrains Mono', ui-monospace, monospace` — untuk NIP, NIDN, nomor SK, nomor BPJS, kode tunjangan, shortcut `⌘F`, dan diff JSON `activity_logs`.
* **Tracking Halus:** Judul besar (`Hero` dan `Page Title`) menggunakan *tight letter-spacing* (`-0.025em`) khas Apple agar terasa padat, modern, dan elegan.

| Level | Ukuran / Line-Height | Weight | Letter Spacing | Penggunaan |
|---|---:|---:|---:|---|
| Hero Display (Landing) | `48px / 54px` (`32px` mobile) | `700` | `-0.03em` | Headline utama Landing Page |
| Section Display (Landing) | `32px / 40px` | `600` | `-0.025em` | Judul bagian masalah/solusi & modul |
| App Page Title | `22px / 30px` | `600` | `-0.02em` | Judul halaman dashboard (mis. `Pegawai`, `Pengajuan Cuti`) |
| Section / Drawer Title | `17px / 24px` | `600` | `-0.015em` | Judul *Side-Drawer*, *Modal*, dan kartu grafik |
| Card Title / Name | `15px / 21px` | `600` | `-0.01em` | Nama pegawai di *Directory Card*, judul KPI Card |
| Body & Table Cell | `14px / 20px` | `400` | `0em` | Isi tabel, deskripsi modul, paragraf |
| Label & Metadata | `12.5px / 18px` | `500` | `0em` | Label input, baris metadata kartu (`Unit Kerja`, `Email`) |
| Sidebar Group / Pill | `11px / 16px` | `600` | `+0.06em` | Header grup sidebar (`MAIN MENU`, `ORGANISASI`) & status pill |

### 2.4 Spacing, Radius & Elevasi (Apple-Grade Geometry)

* **Skala Spasi (basis `4px`):** `8px` jarak ikon–teks, `16px` gap antar kartu directory, `20px` padding dalam kartu, `24px`–`32px` padding area konten utama, `80px`–`112px` jarak vertikal antar seksi Landing Page.
* **Radius Sudut Membulat Halus:**
  * `10px`: Button, Input pencarian, Menu item sidebar.
  * `12px`: Popover dropdown (`Details`, `Edit`, `Nonaktifkan`), Toast notification.
  * `16px`: Employee Directory Card, KPI Card, Table Container, Modal.
  * `20px`–`24px`: Hero Mockup Frame & Bento Feature Card di Landing Page.
  * `9999px` (Full Pill): Status Badge (`• Aktif`), Category Pill (`DOSEN`, `TENDIK`), Avatar.
* **Elevasi & Glassmorphism:**
  * **Light Mode Card:** Border `1px solid var(--border-subtle)` + bayangan sangat halus `0 1px 2px rgba(16, 24, 40, 0.04)`. Hover kartu naik halus `0 8px 24px -6px rgba(16, 24, 40, 0.08)`.
  * **Dark Mode Card:** Permukaan `#121218` dengan border *hairline* `rgba(255, 255, 255, 0.08)` dan *top inner highlight* `inset 0 1px 0 rgba(255, 255, 255, 0.04)`.
  * **Floating Layer (Popover, Modal, Glass Navbar):** `backdrop-filter: blur(20px) saturate(180%)` + bayangan melayang terdistribusi lembut.

---

## 3. Spesifikasi Landing Page Publik (`/`)

Struktur Landing Page mengadaptasi alur visual referensi kedua (*Hero Split + Ringkasan Sistem + Perbandingan Cara Lama vs Sistem Baru + Grid Modul Operasional*), namun diwarnai dan ditata ulang menggunakan **Design Token Apple-Minimalist + Electric Violet** yang mendukung Light & Dark Mode.

### 3.1 Arsitektur Seksi Landing Page

1. **Sticky Translucent Navbar (`height: 68px`):**
   * **Kiri:** Logo ikon gradien violet–magenta + wordmark **Antech Async** (subtitle tipis `HRIS BSDM UAD`).
   * **Tengah:** Tautan jangkar mulus: `Fitur Utama`, `Cara Kerja`, `Modul & Laporan`, `Lowongan Terbuka`.
   * **Kanan:** `ThemeToggle` (Light/Dark), tombol Ghost `Daftar` (`/register`), dan tombol Primary Pill `Masuk Portal` (`/login`).

2. **Hero Section (2-Kolom Split + Subtle Radial Glow):**
   * **Kolom Kiri:**
     * *Eyebrow Pill:* `• ARSITEKTUR HRIS TERPADU BSDM UAD` (latar `--color-accent-soft`, teks `--color-accent`).
     * *Headline (`48px` Bold):* **"Transformasi Manajemen** `<span class="text-accent">SDM & Karir Akademik</span>` **Modern."**
     * *Subheadline:* "Sistem informasi kepegawaian terpusat untuk mengelola siklus hidup Dosen dan Tendik Universitas Ahmad Dahlan — mulai dari rekrutmen, cuti, BKD/LKD, SKP, hingga masa pensiun dengan jejak audit transparan."
     * *CTA Pair:* Primary Button `Masuk ke Portal HRIS →` (`/login`) dan Secondary Glass Button `Daftar Pelamar / Lihat Lowongan` (`/register`).
     * *Compact Summary Card (`Ringkasan Sistem`):* Kartu kaca membulat (`16px`) menampilkan 4 baris metrik ringkas:
       * `Proses Bisnis Terintegrasi` — **17 Proses (`P-01` s.d. `P-17`)**
       * `Kategori Pegawai` — **Dosen & Tendik**
       * `Kontrol Akses` — **RBAC + Cakupan Unit**
       * `Integritas Audit` — **Append-Only Log & Locking**
   * **Kolom Kanan (Apple-Style Floating Product Preview):**
     * Bingkai jendela bergaya macOS (3 titik kontrol halus di kiri atas) yang menampilkan pratinjau interaktif antarmuka **Employee Directory & Dashboard Antech Async** (otomatis berubah tampilan antara Light Preview dan Dark Preview mengikuti `ThemeToggle`), dilengkapi *floating mini-card* di sudut kiri bawah (misal indikator `✓ 99.8% Pengajuan Tercatat & Terverifikasi`).

3. **Seksi Perbandingan Masalah vs Solusi (*Pain Point Contrast*):**
   * *Judul:* **"Kalau ini masih terasa familiar, tim BSDM butuh sistem yang saling terhubung."**
   * *Grid 2 Kolom Sejajar (`rounded-2xl`, padding `28px`):*
     * **Kartu Kiri — `CARA LAMA` (Permukaan Netral `--bg-elevated`):** Merangkum `BP-01` s.d. `BP-06` (berkas pegawai tersebar, persetujuan cuti/lembur manual tanpa jejak, jenjang karir Dosen/Tendik sulit dipantau lintas bidang, rekap laporan pimpinan lambat).
     * **Kartu Kanan — `DENGAN ANTECH ASYNC` (Aksen Gradien Violet `#6D28D9` $\rightarrow$ `#7C3AED` dengan teks putih bersih):** Merangkum `BO-01` s.d. `BO-04` (data induk & riwayat penempatan satu pintu, workflow anti-konflik dengan *optimistic locking*, validasi BKD/SKP terstruktur, serta 6 laporan agregat pimpinan *real-time*).

4. **Seksi Modul Operasional Utama (6 Bento Feature Cards, Grid 3 $\times$ 2):**
   * Masing-masing kartu memiliki ikon *squircle* (`44px`, radius `12px`) bernuansa pastel/transparan sesuai kategori:
     1. **Organisasi & Direktori Pegawai:** Data induk Dosen/Tendik, *Directory Grid*, *Org Chart*, dan riwayat penempatan.
     2. **Rekrutmen & Onboarding:** Publikasi lowongan, seleksi bertahap + nilai AIK, hingga konversi otomatis pegawai aktif.
     3. **Cuti, Lembur & Jadwal Kerja:** Validasi saldo & overlap *real-time*, persetujuan berjenjang, dan versi jadwal kerja.
     4. **Karir, Studi S3 & Pelatihan:** Percepatan jabatan fungsional Dosen, kepangkatan Tendik, sertifikasi, dan diklat.
     5. **Evaluasi Kinerja (BKD & SKP):** Laporan BKD/LKD berversi dengan revisi tercatat serta penilaian SKP berbobot $100\%$.
     6. **Kesejahteraan, Pensiun & Laporan:** Kepesertaan BPJS terproteksi, pengajuan tunjangan, *scheduled* pensiun, dan 6 laporan SDM.

5. **Seksi Lowongan Terbuka & CTA Penutup:**
   * Menampilkan hingga 3 kartu lowongan berstatus `dibuka` (data publik dari tabel `vacancies`: judul, unit, kategori `DOSEN`/`TENDIK`, tanggal tutup) dengan tombol `Lamar Sekarang` yang mengarahkan ke `/register`.
   * Footer minimalis: Identitas BSDM Universitas Ahmad Dahlan, tautan cepat, dan status hak cipta proyek Antech Async.

---

## 4. Spesifikasi Halaman Autentikasi Terpisah (`/login` & `/register`)

Halaman autentikasi menggunakan `AuthLayout` terpisah dari `LandingLayout` dan `AppLayout` agar pengalaman masuk terasa fokus, cepat, dan aman.

### 4.1 Tata Letak `AuthLayout` (Minimalist Apple Card / Subtle Split)
* **Top Utility Bar Minimal:** Tombol kembali `← Kembali ke Beranda` (`/`) di kiri atas dan `ThemeToggle` di kanan atas.
* **Kontainer Tengah (Desktop):** Kartu tunggal presisi selebar `420px` (atau *split screen* elegan 55%/45% pada layar lebar di mana sisi kiri menampilkan panel visual *brand showcase* tenang dan sisi kanan menampilkan formulir bersih) di atas latar `--bg-canvas` dengan pendaran radial violet sangat samar (`opacity: 0.08`).

### 4.2 Halaman Login (`/login`)
* **Target Pengguna:** Seluruh aktor internal (Pegawai Dosen/Tendik, Atasan Langsung, Staf/Kepala Bidang BSDM, Kepala Biro, Wakil Rektor, Admin Sistem) dan Pelamar terdaftar.
* **Elemen Formulir:**
  * Logo & judul **"Masuk ke Antech Async"** + subjudul *"Gunakan username atau email institusi Anda."*
  * Input `Username / Email` (autofocus) dan `Kata Sandi` (dilengkapi tombol ikon mata *show/hide*).
  * Baris utilitas: Checkbox `Ingat saya` dan tautan `Lupa kata sandi?` (arahan kontak Admin Sistem BSDM).
  * Tombol Primary full-width `Masuk` dengan animasi spinner `form.processing`.
  * Footer tautan: *"Calon pelamar atau pegawai baru?"* $\rightarrow$ `Daftar / Aktivasi Akun` (`/register`).
* **Penanganan Error:** Error kredensial atau akun nonaktif (`users.is_active = 0`) tampil sebagai *inline alert* merah lembut di atas tombol tanpa membocorkan detail keamanan sensitif. Setelah sukses, Laravel mengarahkan otomatis ke `/dashboard` (atau portal lamaran bagi role pelamar).

### 4.3 Halaman Register Terpisah (`/register`)
Agar fitur registrasi tetap mematuhi integritas data `RULE-02` dan `RULE-15` (pegawai aktif hanya dibuat lewat onboarding/BSDM, bukan mendaftar sembarangan sebagai Admin/Pegawai):
* **Segmented Control ala Apple di Bagian Atas Kartu** (2 tab rapi dalam pil tunggal):
  1. **Tab 1 — Daftar Pelamar Baru (Default):**
     * Membuat akun pelamar eksternal (`users` + profil `applicants`) untuk melamar lowongan Dosen/Tendik yang dibuka (`P-01`/`P-02`).
     * Field: Nama Lengkap, Email (validasi unik `EX-10`), Nomor Telepon, Tanggal Lahir, Pendidikan Terakhir (`S1`/`S2`/`S3`/`D3`/`SMA`), Password & Konfirmasi Password.
  2. **Tab 2 — Aktivasi Akun Pegawai:**
     * Khusus Dosen/Tendik yang datanya sudah tercatat di tabel `employees` oleh BSDM tetapi belum memiliki akun login (`users.employee_id IS NULL`).
     * Field: NIP, Email Kerja (harus cocok dengan record `employees`), Username pilihan, dan Password baru. Sistem memverifikasi kecocokan NIP + Email sebelum mengaitkan akun ke role dasar `pegawai`.
* Footer tautan: *"Sudah memiliki akun?"* $\rightarrow$ `Masuk di sini` (`/login`).

---

## 5. App Shell, Navigasi & Gaya Visual Dashboard (Referensi Gambar 1)

Tata letak area terautentikasi mengadopsi desain bersih dari referensi pertama (`Nexora`) yang disesuaikan dengan 12 menu HRIS BSDM UAD.

### 5.1 Blueprint App Shell (Desktop $\ge 1280\text{px}$)

```text
+-------------------------+-----------------------------------------------------------------+
| [Logo] Antech Async [|<]|  Pegawai (Page Title)          [ThemeToggle] [+ Tambah Pegawai] |
|-------------------------|-----------------------------------------------------------------|
| MAIN MENU               |  Daftar Pegawai  |  Direktori (Active)  |  Struktur Organisasi  |
|   Dashboard             |  ----------------===========================------------------- |
|   Inbox Persetujuan (4) |                                                                 |
|   Laporan & Analitik    |  [ Q  Cari nama, NIP, atau unit...         ⌘F ]  [ ≡ Filter ]   |
|                         |                                                                 |
| ORGANISASI & SDM        |  +-------------------+ +-------------------+ +----------------+ |
| | Pegawai (Active)      |  | • Aktif         ⋮ | | • Aktif         ⋮ | | • Aktif      ⋮ | |
|   Jadwal Kerja          |  |     (Avatar)      | |     (Avatar)      | |    (Avatar)    | |
|   Cuti & Lembur         |  | Dr. Ahmad Alfan   | | Kartika Pricilla  | | M. Zaki Raiz   | |
|   Karir & Pelatihan     |  | Lektor | Prodi SI | | Laboran | FTI     | | Asisten Ahli   | |
|   Kinerja (BKD & SKP)   |  |     [ DOSEN ]     | |    [ TENDIK ]     | |   [ DOSEN ]    | |
|   Rekrutmen             |  |-------------------| |-------------------| |----------------| |
|   BPJS & Tunjangan      |  | Unit    Prodi SI  | | Unit    Lab FTI   | | Unit   Prodi TI| |
|   Pensiun               |  | NIP     198501... | | NIP     199604... | | NIP    199208..| |
|                         |  | Email   ahmad@... | | Email   kartik... | | Email  zaki@...| |
|                         |  +-------------------+ +-------------------+ +----------------+ |
|-------------------------|                                                                 |
|   Manajemen User & Role |                                      +------------------------+ |
|   Activity Log (Audit)  |                                      | Side-Drawer (520px)    | |
|   Pengaturan            |                                      | Detail / Riwayat / Log | |
|-------------------------|                                      +------------------------+ |
| (Av) Dr. Ahmad Alfan    |                                                                 |
|      Cakupan: Prodi SI  |                                                                 |
+-------------------------+-----------------------------------------------------------------+
```

### 5.2 Karakteristik Sidebar Minimalis (Clean Light & Obsidian Dark)
* **Lebar:** `252px` (terbuka) dengan tombol ciutkan (`[|<]`) di sebelah logo untuk mengubahnya menjadi *icon rail* `72px` dengan animasi transisi `200ms cubic-bezier(0.2, 0, 0, 1)`.
* **Permukaan Sidebar:** Putih bersih (`#FFFFFF`) di Light Mode dan `#121218` di Dark Mode, dipisahkan oleh 1px *hairline border* (`--border-subtle`).
* **Grup Menu Terstruktur:**
  * `MAIN MENU`: **Dashboard**, **Inbox Persetujuan** (antrean cepat pengajuan `diajukan` dengan badge angka), **Laporan & Analitik** (`RPT-01` s.d. `RPT-06`).
  * `ORGANISASI & SDM`: **Pegawai** (mencakup *List*, *Directory*, *Org Chart*), **Jadwal Kerja**, **Cuti & Lembur**, **Karir & Pengembangan** (Jabatan Dosen, Pangkat Tendik, Studi S3, Sertifikasi, Pelatihan), **Kinerja** (BKD/LKD & SKP), **Rekrutmen**, **Kesejahteraan** (BPJS & Tunjangan), **Pensiun**.
  * `SISTEM (Bawah Sidebar)`: **User & Role**, **Activity Log**, serta **Profil Pengguna Aktif** di paling bawah (Avatar bulat + Nama + Indikator *Unit Scope* aktif + menu Logout).
* **Gaya Item Aktif (Ciri Khas Gambar 1):** Latar lembut `--color-accent-soft` (radius `10px`), bilah vertikal kecil (`3px x 20px`, radius penuh) berwarna `--color-accent` di sisi kiri item, serta ikon & teks tebal berwarna `--color-accent`.

### 5.3 Pola Header Halaman, Sub-Tabs & Search Bar
1. **Header Konten:** Judul halaman tegas di kiri (`22px` Semibold), dipadukan `ThemeToggle` + tombol aksi utama berwarna Violet (`+ Tambah Pegawai` / `+ Ajukan Cuti`) di kanan.
2. **Underline Sub-Tabs (`SubTabs`):** Berada tepat di bawah header dengan garis pemisah tipis memanjang. Tab aktif memiliki teks `--color-accent` dan garis bawah `2px solid var(--color-accent)` (contoh pada modul Pegawai: `Daftar Pegawai` | `Direktori` | `Struktur Organisasi`; pada modul Pengajuan: `Cuti` | `Lembur` | `Tunjangan`; pada modul Kinerja: `BKD / LKD` | `SKP`).
3. **Search & Filter Bar (`SearchFilterBar`):**
   * Kotak pencarian lebar penuh dengan ikon kaca pembesar di kiri dan lencana pintasan keyboard `⌘F` (atau `Ctrl+K` di Windows/Linux) di kanan.
   * Tombol `Filter` di sisi kanan membuka popover/bar filter dinamis (Unit Kerja, Kategori `DOSEN`/`TENDIK`, Status Kepegawaian, Periode) tanpa memenuhi layar.

### 5.4 Breakpoint Responsivitas
* **Desktop ($\ge 1280\text{px}$):** Sidebar penuh `252px`, *Directory Card Grid* 3 kolom, KPI Grid 3–4 kolom.
* **Tablet (`768px` – `1279px`):** Sidebar otomatis menciut menjadi *icon rail* `72px`, *Directory Card Grid* 2 kolom, KPI Grid 2 kolom.
* **Mobile ($< 768\text{px}$):** Sidebar berubah menjadi *off-canvas drawer* via tombol burger di kiri atas, *Directory Grid* 1 kolom, dan tabel mendukung scroll horizontal dengan kolom identitas tetap *sticky*.

---

## 6. Pustaka Komponen UI Inti & Pemetaan Status

### 6.1 Pemetaan Status Badge (`StatusBadge`)

Mengadopsi gaya **dot-pill badge** (`• Aktif`, `• Nonaktif`) yang bersih dari referensi pertama:

| Kelompok Warna | Token | Daftar Status Modul |
|---|---|---|
| **Hijau** (Positif / Sah) | `--status-success` | `disetujui`, `diterima`, `lolos`, `final`, `aktif`, `lulus`, `selesai`, `dibuka` |
| **Kuning** (Menunggu Aksi) | `--status-warning` | `diajukan`, `proses`, `menunggu`, `diproses`, `berjalan` |
| **Biru** (Inisiasi / Terjadwal) | `--status-info` | `draf`, `diusulkan`, `direncanakan`, `dijadwalkan` |
| **Merah** (Ditolak / Gugur) | `--status-danger` | `ditolak`, `gugur`, `tidak_lulus`, `berhenti` |
| **Abu-abu** (Batal / Arsip) | `--status-neutral` | `dibatalkan`, `ditutup`, `nonaktif`, `pensiun`, `batal`, `belum` |

> **Penanda Status Terminal (`RULE-11` & `EX-05`):** Status final (`disetujui`, `ditolak`, `dibatalkan`, `final`, `selesai`) mengganti titik dot dengan ikon gembok kecil (`12px`) pada halaman detail untuk menandakan bahwa data bersifat *immutable*.

### 6.2 Komponen Utama
* **Employee Directory Card (`EmployeeDirectoryCard` — Referensi Gambar 1):**
  * Header kartu: `StatusBadge` (`• Aktif`) di kiri, tombol menu tiga titik (`⋮`) di kanan yang memunculkan floating popover (`Lihat Detail`, `Ubah Penempatan`, `Nonaktifkan` — **bukan** *Delete* permanen demi mematuhi `RULE-13`).
  * Tengah: Avatar bulat `64px` (foto profil atau inisial monokrom elegan), Nama Lengkap (`15px` Semibold), subjudul `Jabatan / Pendidikan | Unit Kerja`, dan `CategoryPill` (`DOSEN` ungu lembut, `TENDIK` biru lembut).
  * Bawah: Garis pemisah *hairline* diikuti 3 baris metadata sejajar kiri–kanan (`Unit Kerja`, `NIP / NIDN`, `Email Kerja`).
* **Buttons (`AppButton`):**
  * *Primary:* Latar `--color-accent`, teks putih, radius `10px`, spinner otomatis saat `form.processing`.
  * *Secondary:* Latar `--bg-surface`, border `--border-control`, teks `--text-primary`.
  * *Danger:* Latar `--status-danger-fg`, teks putih — khusus Tolak, Batalkan, dan Nonaktifkan.
* **Data Table (`DataTable`):**
  * Header *sticky* berlatar `--bg-elevated`, baris bersih dengan hover halus, *skeleton loader* saat Inertia *partial reload*, *empty state* ilustratif, dan paginasi `<Link preserve-scroll preserve-state>`.
* **Modal Validasi Alasan (`ReasonModal` — `RULE-12`):**
  * Wajib muncul saat aksi **Tolak**, **Batalkan**, atau **Buat Revisi BKD**. Memuat `textarea` minimal 10 karakter + penghitung karakter + hidden `version`.
* **Side-Drawer (`SideDrawer`):**
  * Panel melayang kanan `520px` dengan `backdrop-blur-sm` untuk pratinjau profil pegawai, riwayat `employee_assignments`, persetujuan cepat, dan rincian JSON `activity_logs`.
* **Progress Stepper (`ProgressStepper`):**
  * Untuk `selection_stages` (Rekrutmen) dan 4 jenjang jabatan fungsional Dosen (`Asisten Ahli` $\rightarrow$ `Lektor` $\rightarrow$ `Lektor Kepala` $\rightarrow$ `Guru Besar`). Tahap selesai hijau, aktif berbingkai violet dengan `animate-pulse`, gugur merah, mendatang abu-abu.
* **Unduh Berkas Privat (`cv_path`, `certificate_path`):**
  * Wajib menggunakan tag HTML standar `<a href="..." target="_blank">` (bukan `<Link>` Inertia) agar file stream dari `storage/app/private` diunduh dengan benar oleh browser.

---

## 7. Pola Interaksi Real-Time Berbasis Inertia.js

### 7.1 Live Filter & Keyboard Shortcut (`⌘F` / `Ctrl+K`)
* Menekan `⌘F` atau `Ctrl+K` langsung memfokuskan kotak `SearchFilterBar`.
* Perubahan input pencarian (`debounce 300ms`) dan dropdown filter mengeksekusi **Partial Reload** Inertia:

```ts
router.get(
  route('employees.index'),
  { search, unit_id, category, status, view }, // view: 'list' | 'directory' | 'org-chart'
  {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    only: ['employees', 'filters'],
  }
);
```

### 7.2 Optimistic Locking & Penanganan Konflik `HTTP 409` (`RULE-17`, `EX-04`)
* Seluruh formulir persetujuan mengirimkan `version` melalui `useForm`.
* Bila backend mengembalikan konflik versi, **`ConflictModal`** muncul otomatis dengan tombol **Muat Ulang Data Terbaru** (`router.reload({ only: ['request', 'items'] })`).

### 7.3 Drill-Down Graph (ApexCharts Dual-Mode)
* Mengklik segmen batang/donut pada grafik Dashboard atau halaman Laporan (`RPT-01` s.d. `RPT-06`) memicu `router.get()` parsial untuk menyaring tabel rincian di bawah grafik dan menampilkan *filter chip* aktif yang bisa dihapus dengan sekali klik.

### 7.4 Persistent Layout & Deferred Props (`NFR-04` $\le 2$ Detik)
* Seluruh halaman dashboard memakai `AppLayout` sebagai *Persistent Layout* sehingga state Sidebar (terbuka/ciut), tema aktif, dan antrean Toast tetap utuh antar perpindahan halaman.
* Grafik agregat berat dimuat menggunakan *Deferred Props* (Inertia v2) agar halaman utama tampil di bawah 2 detik.

---

## 8. Matriks Visibilitas Menu & Blueprint Modul Utama

### 8.1 Matriks Visibilitas Menu Berdasarkan Role

Penyembunyian menu di frontend hanya bersifat kenyamanan UX; otorisasi aktual ditegakkan penuh di Laravel Policy & query scope (`PRD Bagian 3.3`).

| Menu Navigasi | Pegawai | Atasan Langsung | Staf Bidang BSDM | Kepala Biro / Warek | Admin Sistem |
|---|---|---|---|---|---|
| 1. Dashboard | Ringkasan Pribadi | Antrean Unit | Antrean Bidang | Agregat Eksekutif | Kesehatan Sistem |
| 2. Inbox Persetujuan | Pantau Pengajuan | Approve Unit | Proses Bidang | Monitoring | — |
| 3. Pegawai (List / Directory / Org Chart) | Profil Sendiri | Bawahan Unit | Sesuai Bidang | Lintas Unit | Kelola Unit |
| 4. Rekrutmen | — | — | Bidang Seleksi | Monitoring | — |
| 5. Cuti & Lembur | Ajukan & Pantau | Approve Unit | Kelola & Rekap | Monitoring | — |
| 6. Jadwal Kerja | Lihat Jadwal | Lihat Unit | Kelola & Publish | Monitoring | — |
| 7. Karir & Pengembangan | Riwayat & Ajukan | Lihat Unit | Seleksi / Diklat | Monitoring | — |
| 8. Kinerja (BKD & SKP) | Isi & Revisi | Validasi Unit | Evaluasi Kinerja | Monitoring | — |
| 9. Kesejahteraan (BPJS & Tunjangan) | Data & Ajukan | Approve Unit | Izin Khusus BSDM | Agregat (*Masked*) | — |
| 10. Pensiun | Status Sendiri | Info Unit | Kelola Pensiun | Monitoring | — |
| 11. Laporan (`RPT-01` s.d. `RPT-06`) | — | Rekap Unit | Laporan Bidang | `RPT-01` s.d. `RPT-06` | — |
| 12. User & Role + Activity Log | Log Sendiri | Log Unit | Log Bidang | Audit Lintas Unit | Penuh |

### 8.2 Ringkasan Blueprint Layar per Modul (17 Proses Bisnis)

1. **Dashboard (`P-17`):** 3 KPI Card minimalis di atas, 2 grafik *Drill-Down* ApexCharts adaptif tema di tengah (`RPT-06` & `RPT-04`), dan tabel ringkas transaksi aktif di bawah dengan aksi cepat `SideDrawer`.
2. **Organisasi & Pegawai (`PRD-FR-01`, `PRD-FR-02`):** 3 *Sub-Tabs* ala referensi pertama:
   * `Daftar Pegawai` (*DataTable* padat).
   * `Direktori` (Grid 3-kolom `EmployeeDirectoryCard` lengkap dengan avatar, badge `• Aktif`, pil `DOSEN`/`TENDIK`, dan popover `⋮`).
   * `Struktur Organisasi` (*Interactive Org Chart* hierarki `departments.parent_id`).
   * Klik kartu/baris membuka halaman/drawer detail dengan tab *Biodata*, *Timeline Penempatan (`employee_assignments`)*, *Karir & Kinerja*, serta *BPJS*.
3. **Rekrutmen & Onboarding (`P-01` s.d. `P-03`):** Lowongan (`draf`/`dibuka`/`ditutup`), `ProgressStepper` seleksi + nilai AIK (`RULE-01`), dan tombol **Konversi ke Pegawai Aktif** khusus kandidat `diterima` (`RULE-02`).
4. **Cuti, Lembur & Tunjangan (`P-04`, `P-05`, `P-15`):** `LeaveQuotaCards` di atas form cuti, kalkulasi otomatis `total_days` / `total_hours` + peringatan *inline* `EX-01`/`EX-02`/`EX-07`, validasi syarat S3 pada Tunjangan Doktor (`RULE-09`), serta penyembunyian mutlak tombol approve jika `auth.user.employee_id === item.employee_id` (`RULE-14`).
5. **Jadwal Kerja (`P-06`):** Matrix 7 hari (`day_of_week` unik) dan mekanisme **Buat Versi Baru** agar jadwal historis tidak tertimpa.
6. **Karir & Pengembangan (`P-07` s.d. `P-11`):** `ProgressStepper` 4 jenjang Dosen dengan *warning banner* & toggle **Jalur Percepatan** bila melompati jenjang (`RULE-05`, `EX-06`), timeline kepangkatan Tendik (`RULE-06`), studi lanjut S3, sertifikasi, dan pelatihan termasuk label `pra_pensiun`.
7. **Kinerja: BKD/LKD & SKP (`P-12`, `P-13`):** Auto-sum `total_credit` BKD, kunci *read-only* pada status `final` dengan pemilih versi (`v1`, `v2`) + `ReasonModal` revisi (`RULE-07`), serta form butir SKP dinamis dengan indikator progress bar **Total Bobot = $100\%$** (`RULE-08`).
8. **BPJS & Pensiun (`P-14`, `P-16`):** `BpjsMaskedField` (`********1234`) dengan proteksi 1 BPJS aktif per jenis (`RULE-16`), serta modal penyelesaian pensiun dengan `RetirementChecklist` otomatis atas 7 tabel pengajuan aktif (`EX-09`).
9. **Laporan & Activity Log (`P-17`):** 6 halaman laporan seragam dengan *Drill-Down Chart* dan pembatasan tampilan agregat bagi Wakil Rektor; halaman `Activity Log` murni *read-only* dengan garis tepi merah pada `ACCESS_DENIED` dan *Side-Drawer* JSON diff.

---

## 9. Pemetaan Umpan Balik UI untuk Skenario Pengecualian (`EX-01` s.d. `EX-12`)

| ID Exception | Kondisi | Umpan Balik UI (Light & Dark Mode) | Aksi Pemulihan Pengguna |
|---|---|---|---|
| `EX-01` | Saldo cuti tidak mencukupi | Alert inline merah lembut di bawah tanggal berisi angka sisa saldo | Mengurangi hari atau mengganti jenis cuti |
| `EX-02` / `EX-07` | Tanggal cuti / jam lembur bentrok | Border merah + tautan rentang tanggal/jam yang bentrok | Memilih ulang tanggal atau jam valid |
| `EX-03` | Percobaan *self-approval* | Tombol approve tidak dirender; akses langsung menampilkan halaman `403` | Kembali ke Dashboard (`ACCESS_DENIED` tercatat) |
| `EX-04` | Konflik konkurensi (`version` beda) | `ConflictModal` melayang (`HTTP 409` / flash conflict) | Klik **Muat Ulang Data Terbaru** (`router.reload()`) |
| `EX-05` | Status final terkunci | Ikon gembok pada `StatusBadge`; tombol edit disembunyikan | Klik **Ajukan Ulang** atau **Ajukan Revisi** (BKD) |
| `EX-06` | Lompatan jenjang jabatan Dosen | Banner peringatan kuning pada form jabatan fungsional | Aktifkan *toggle* **Jalur Percepatan** (`is_accelerated`) |
| `EX-09` | Pensiun tertahan pengajuan aktif | `RetirementChecklist` merah berisi tautan ke pengajuan terkait | Menyelesaikan/menutup pengajuan aktif dahulu |
| `EX-10` / `EX-11` | Email pelamar / No. BPJS duplikat | `form.errors` inline tepat di bawah kolom terkait | Membuka rekaman yang sudah terdaftar |

---

## 10. Aksesibilitas (WCAG 2.1 AA) & Target Performa Frontend

* **Kontras Dual-Mode:** Rasio kontras teks utama minimal $7:1$ dan teks sekunder minimal $4.5:1$ baik di Light Mode (`#111827` di atas `#FFFFFF`) maupun Dark Mode (`#F5F5F7` di atas `#121218`).
* **Navigasi Keyboard & Fokus:** Mendukung `⌘F`/`Ctrl+K` (fokus pencarian), `Tab`, `Enter`, dan `Escape` (tutup modal/drawer). Cincin fokus memakai `focus-visible:ring-2 focus-visible:ring-[var(--color-accent)] focus-visible:ring-offset-2`.
* **Kinerja Antarmuka (`NFR-04` $\le 2$ Detik):** *Code splitting* per halaman via Vite, debounce pencarian `300ms`, paginasi server-side `15`/`25` baris, *partial reload* (`only: [...]`), serta *anti-FOUC* inline script untuk tema.

---

## 11. Riwayat Perubahan Dokumen

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0 | 17 November 2026 | Dokumen spesifikasi UI awal untuk stack Inertia.js + Vue 3 / React + Tailwind CSS (palet Deep Navy & Orange). |
| 2.0 | 19 November 2026 | Redesain menyeluruh ke **Apple-Inspired Minimalist Design System** mengacu pada tata letak Dashboard modern (light/dark sidebar, Electric Violet accent `#6D28D9`/`#8B5CF6`, *Employee Directory Card Grid* + *List* + *Org Chart*, `⌘F` search bar), penambahan **Landing Page publik (`/`)**, pemisahan halaman **Login (`/login`) & Register (`/register`)**, serta arsitektur penuh **Light Mode & Dark Mode** (`useTheme` + anti-FOUC + sinkronisasi ApexCharts). |
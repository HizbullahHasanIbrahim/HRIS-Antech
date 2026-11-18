# UI DESIGN SYSTEM & FRONTEND GUIDELINES

## Antech Async - Human Resource Information System (HRIS)

| Metadata | Detail |
|---|---|
| Nama Sistem | Antech Async - Sistem Informasi Kepegawaian dan Manajemen Sumber Daya Manusia (HRIS) |
| Organisasi / Domain | Biro Sumber Daya Manusia (BSDM), Universitas Ahmad Dahlan |
| Versi Dokumen | 1.0 |
| Status | Draft - Pendamping `PRD v1.1` & `SDD v1.0` |
| Tanggal | 17 November 2026 |
| Stack Frontend | **Inertia.js (v1/v2) + Vue 3 atau React + Tailwind CSS** |
| Ikon & Grafik | Lucide / Heroicons (stroke 1.75px) & ApexCharts (`vue3-apexcharts` / `react-apexcharts`) |

---

## 1. Prinsip Desain & Arsitektur Frontend (Inertia.js)

Antarmuka Antech Async HRIS dirancang sebagai **Modern Monolith SPA** menggunakan Inertia.js. Seluruh navigasi, penyaringan data, validasi formulir, dan pembaruan status pengajuan berjalan mulus tanpa *full page reload* dan tanpa perlu membangun lapisan REST API terpisah.

### 1.1 Prinsip Utama Antarmuka
1. **Kejelasan Status & *Immutability*:** Status workflow setiap modul terlihat seketika lewat warna semantik dan penanda gembok untuk status final (`RULE-11`, `EX-05`).
2. **Transparansi Cakupan Akses (*Unit Scope*):** Pengguna selalu melihat indikator cakupan data aktifnya di bilah atas (misalnya unit kerja tertentu atau lintas unit) agar selaras dengan RBAC (`RULE-15`).
3. **Privasi Data Sensitif *by Default*:** Nomor kepesertaan BPJS (`membership_number`) dan alamat domisili tersamarkan secara bawaan di tampilan daftar.
4. **Pencegahan Kesalahan Dini:** Validasi interaktif di klien (sisa kuota cuti, durasi lembur, total bobot SKP $= 100\%$) berjalan berdampingan dengan validasi ketat *FormRequest* dan MySQL 8.4 di server.
5. **Konvensi Waktu & Angka:** Seluruh timestamp UTC dari MySQL 8.4 diformat ke zona waktu `Asia/Jakarta` (WIB) pada sisi klien, sedangkan seluruh angka tabel (NIP, nominal tunjangan, skor SKP, beban SKS) wajib memakai kelas `tabular-nums` dan format lokal `id-ID`.

### 1.2 Struktur Direktori Frontend (`resources/js/`)

Struktur ini berlaku konsisten baik tim memilih **Vue 3 (`.vue` + Composition API)** maupun **React (`.tsx` / `.jsx`)**:

```text
resources/js/
├── app.ts / app.js                    # Bootstrap Inertia.js + Persistent Layout resolver
├── Layouts/
│   ├── AppLayout.(vue|tsx)            # Persistent App Shell (Sidebar kiri + Header + Toast Host)
│   └── GuestLayout.(vue|tsx)          # Layout Login & Portal Pelamar Eksternal
├── Components/
│   ├── UI/
│   │   ├── AppButton.(vue|tsx)        # Primary, Secondary, Danger, Ghost + Spinner state
│   │   ├── StatusBadge.(vue|tsx)      # Pemetaan warna status lintas modul + ikon gembok final
│   │   ├── DataTable.(vue|tsx)        # Sticky header, skeleton loader, pagination Inertia
│   │   ├── SideDrawer.(vue|tsx)       # Slide-over panel kanan (520px) untuk detail & audit log
│   │   ├── ReasonModal.(vue|tsx)      # Modal wajib alasan (RULE-12) untuk tolak/batal/revisi
│   │   ├── ConflictModal.(vue|tsx)    # Modal peringatan HTTP 409 Optimistic Locking (EX-04)
│   │   ├── ProgressStepper.(vue|tsx)  # Visualisasi tahapan seleksi & jenjang jabatan Dosen
│   │   └── ToastContainer.(vue|tsx)   # Notifikasi toast kanan atas dari Inertia flash props
│   └── Domain/
│       ├── EmployeeSearchBar.(vue|tsx)
│       ├── LeaveQuotaCards.(vue|tsx)
│       ├── BpjsMaskedField.(vue|tsx)
│       ├── RetirementChecklist.(vue|tsx)
│       └── DrillDownChart.(vue|tsx)   # Wrapper ApexCharts dengan event klik filter
├── Composables/ (atau Hooks/)
│   ├── useLiveFilter.(ts|js)          # Debounce 300ms + router.get partial reload
│   ├── usePermission.(ts|js)          # Helper cek role, izin sensitif, & unit_scope
│   └── useToast.(ts|js)               # Sinkronisasi page.props.flash ke antrean toast
├── Types/
│   └── hris.d.ts                      # Interface TypeScript untuk 29 entitas & shared props
└── Pages/
    ├── Dashboard/Index.(vue|tsx)
    ├── Organization/                  # Departments (Tree/Table) & Employees (List/Detail Tabs)
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


## 2. Design Tokens

### 2.1 Palet Warna

| Kategori | Token CSS / Tailwind | Hex | Penggunaan Utama |
|---|---|---|---|
| Brand Primary | `brand-navy` | `#2C3E50` | Sidebar kiri, header aktif, dan *Primary Button* |
| Brand Hover | `brand-navy-dark` | `#1E2B38` | Status hover/fokus tombol utama & latar item sidebar aktif |
| Brand Accent | `brand-orange` | `#E67E22` | Aksen UAD, indikator menu aktif, *focus ring*, dan highlight grafik |
| Accent Text | `brand-orange-text` | `#B45309` | Teks kecil/label bernuansa oranye di atas putih (memenuhi kontras WCAG AA) |
| Canvas | `bg-canvas` | `#F8FAFC` | Latar belakang utama kanvas halaman |
| Surface | `bg-surface` | `#FFFFFF` | Latar belakang *Card*, *Table*, *Modal*, dan *Side-Drawer* |
| Border Default | `border-subtle` | `#E2E8F0` | Garis tepi *Card* (1px), pemisah baris tabel, dan divider |
| Border Control | `border-control` | `#CBD5E1` | Garis tepi *Secondary Button* dan input formulir |
| Text Primary | `text-main` | `#1E293B` | Judul halaman, isi sel tabel, dan teks utama |
| Text Secondary | `text-muted` | `#64748B` | Label formulir, metadata waktu, dan teks bantuan |
| Semantic Success | `status-success` | `#166534` | Teks badge disetujui/aktif/lulus (latar `#DCFCE7`) |
| Semantic Warning | `status-warning` | `#854D0E` | Teks badge diajukan/proses/menunggu (latar `#FEF9C3`) |
| Semantic Danger | `status-danger` | `#E11D48` | *Danger Button*, error validasi inline, badge ditolak (latar `#FFE4E6`, teks `#BE123C`) |
| Semantic Info | `status-info` | `#1D4ED8` | Teks badge draf/diusulkan/dijadwalkan (latar `#DBEAFE`) |

> **Catatan Kontras:** Warna `#E67E22` digunakan untuk aksen garis, ikon, atau teks besar di atas `#2C3E50`. Untuk teks kecil ($\le 14\text{px}$) di atas latar terang (`#FFFFFF` / `#F8FAFC`), selalu gunakan `#B45309` agar rasio kontras $\ge 4.5:1$.

### 2.2 Tipografi

* **Font Utama (UI & Prose):** `'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif`.
* **Font Monospace (Identitas & Audit):** `'JetBrains Mono', ui-monospace, SFMono-Regular, monospace` — dipakai untuk NIP, NIDN, nomor SK, nomor BPJS, kode tunjangan, dan blok JSON `activity_logs.details`.

| Level | Ukuran / Line-Height | Weight | Penggunaan |
|---|---:|---:|---|
| Page Title | `24px / 32px` | `600` | Judul halaman utama di atas konten |
| Section Header | `18px / 28px` | `600` | Judul subbagian, judul *Side-Drawer* & *Modal* |
| Card / Widget Title | `15px / 22px` | `600` | Judul KPI Card, judul grafik, dan panel formulir |
| Body & Table Cell | `14px / 20px` | `400` | Teks konten utama dan isi baris tabel |
| Form Label | `13px / 18px` | `500` | Label input formulir |
| Badge & Table Header | `12px / 16px` | `600` | Header kolom tabel (`uppercase tracking-wider`) dan status badge |

### 2.3 Spacing, Radius & Elevasi

* **Skala Spasi (kelipatan `4px`):** `8px` jarak ikon–teks, `16px` jarak antar kolom formulir, `20px` padding dalam *Card*, `24px` jarak antar blok konten utama.
* **Radius Sudut:** `6px` (*Button*, *Input*), `8px` (*Card*, *Table Container*), `12px` (*Modal*), `9999px` (*Status Badge* pill).
* **Elevasi:** *Card* dan *Table* menggunakan desain datar (*flat*) dengan `border: 1px solid #E2E8F0` tanpa bayangan tebal. Bayangan (`shadow-lg`) hanya diterapkan pada lapisan melayang (*Dropdown*, *Toast*, *Modal*, *Side-Drawer*).

---

## 3. Pemetaan Status Badge per Konteks Modul

Karena setiap modul memiliki konteks status sendiri (`PRD Bagian 7.1`), komponen `<StatusBadge status="..." />` memetakan seluruh nilai status secara konsisten:

| Kelompok Warna | Latar / Teks | Daftar Status |
|---|---|---|
| **Hijau** (Positif / Sah) | `#DCFCE7` / `#166534` | `disetujui`, `diterima`, `lolos`, `final`, `aktif`, `lulus`, `selesai`, `dibuka` |
| **Kuning** (Dalam Proses) | `#FEF9C3` / `#854D0E` | `diajukan`, `proses`, `menunggu`, `diproses`, `berjalan` |
| **Biru** (Inisiasi / Terjadwal) | `#DBEAFE` / `#1D4ED8` | `draf`, `diusulkan`, `direncanakan`, `dijadwalkan` |
| **Merah** (Ditolak / Gugur) | `#FFE4E6` / `#BE123C` | `ditolak`, `gugur`, `tidak_lulus`, `berhenti` |
| **Abu-abu** (Batal / Arsip) | `#F1F5F9` / `#475569` | `dibatalkan`, `ditutup`, `nonaktif`, `pensiun`, `batal`, `belum` |

> **Penanda Status Terminal (`RULE-11` & `EX-05`):** Status final (`disetujui`, `ditolak`, `dibatalkan`, `final`, `selesai`) wajib menampilkan ikon gembok kecil (`12px`) di sisi kiri teks badge pada halaman detail sebagai penanda visual bahwa rekaman terkunci dan tidak dapat diedit ulang.

---

## 4. Pustaka Komponen UI Inti

### 4.1 Buttons (`AppButton`)
* **Primary:** Latar `#2C3E50`, hover `#1E2B38`, teks `#FFFFFF`. Hanya boleh ada satu *Primary Button* di tiap area aksi.
* **Secondary:** Latar `#FFFFFF` / transparan, border `#CBD5E1`, teks `#1E293B`, hover `#F8FAFC`.
* **Danger:** Latar `#E11D48`, hover `#BE123C`, teks `#FFFFFF` — khusus penolakan, pembatalan, dan penonaktifan.
* **Integrasi Inertia `useForm`:** Properti `:loading="form.processing"` (Vue) atau `loading={form.processing}` (React) otomatis menonaktifkan tombol dan menampilkan spinner `16px` untuk mencegah *double-submit*.

### 4.2 Form Controls & Upload Berkas Privat
* Label selalu diletakkan di atas input dengan tanda `*` merah (`#E11D48`) untuk bidang wajib.
* **Inline Error Feedback:** Terikat langsung ke `form.errors.<field>` dari Inertia. Saat pengguna mulai memperbaiki input, jalankan `form.clearErrors('<field>')` agar pesan merah langsung hilang.
* **Unduh Berkas Privat (`cv_path`, `certificate_path`):** Tautan unduh **wajib menggunakan tag HTML standar `<a href="..." target="_blank">`** dan **BUKAN** komponen `<Link>` milik Inertia, agar browser menangani respons *file stream* dari `storage/app/private` tanpa error parsing JSON Inertia.

### 4.3 Data Table (`DataTable`)
* Header tabel bersifat *sticky* berlatar `#F8FAFC` dengan teks `#64748B`.
* Baris tabel bergaris tipis `#E2E8F0` dengan selang-seling halus (`odd:bg-white even:bg-slate-50/50`), tinggi minimum `48px`, dan highlight hover `#F1F5F9`.
* Menyediakan 3 *state* eksplisit:
  1. **Loading Skeleton:** Muncul saat *partial reload* Inertia sedang berjalan pada tabel.
  2. **Empty State:** Ikon netral, pesan kontekstual ("Belum ada pengajuan cuti pada periode ini"), dan tombol CTA bila role memiliki izin tambah.
  3. **Pagination:** Menggunakan komponen `<Link preserve-scroll preserve-state>` dari Laravel paginator (`links`).

### 4.4 Modal Validasi Alasan (`ReasonModal` - `RULE-12`)
* Dipicu setiap kali pengguna mengklik **Tolak**, **Batalkan**, atau **Buat Revisi BKD**.
* Memuat `textarea` wajib diisi (minimal 10 karakter), penghitung karakter, dan tombol konfirmasi yang tetap `disabled` sampai syarat terpenuhi.
* Mengirimkan payload beserta `version` saat ini melalui `form.patch()` / `form.post()`.

### 4.5 Side-Drawer (`SideDrawer`)
* Panel geser dari sisi kanan selebar `520px` (desktop) hingga `100%` (mobile) dengan *backdrop* `bg-slate-900/35`.
* Digunakan untuk pratinjau cepat profil pegawai, riwayat penempatan (`employee_assignments`), detail pengajuan, dan rincian JSON `activity_logs` tanpa meninggalkan halaman daftar maupun mereset filter.
* Menutup otomatis saat tombol `Escape` ditekan atau klik di luar panel.

### 4.6 Progress Stepper (`ProgressStepper`)
* Digunakan pada alur **Tahapan Seleksi Rekrutmen (`selection_stages`)** dan **Jenjang Jabatan Fungsional Dosen (`Asisten Ahli` $\rightarrow$ `Lektor` $\rightarrow$ `Lektor Kepala` $\rightarrow$ `Guru Besar`)**.
* Indikator visual:
  * **Selesai (`lolos` / tercapai):** Lingkaran hijau `#DCFCE7` + ikon centang `#166534`.
  * **Sedang Berjalan (`menunggu` / `diajukan`):** Lingkaran berbingkai oranye `#E67E22` dengan animasi denyut halus (`animate-pulse`).
  * **Gugur / Ditolak:** Lingkaran merah `#FFE4E6` + ikon silang `#E11D48`.
  * **Belum Dimulai:** Lingkaran abu-abu `#E2E8F0`.

### 4.7 Toast Notification (`ToastContainer`)
* Dipasang satu kali di dalam *Persistent Layout* (`AppLayout`) di pojok kanan atas (`top-4 right-4 z-50`).
* Mendengarkan perubahan `page.props.flash` setiap kali respons Inertia tiba:
  * `flash.success`: Auto-dismiss setelah `4000ms`.
  * `flash.error` / `flash.warning`: Tetap tampil hingga ditutup manual atau maksimal `7000ms`.

---

## 5. Pola Interaksi Real-Time Berbasis Inertia.js

### 5.1 Live Filter & Pencarian Tanpa Reload Penuh
Pencarian nama pegawai (`FULLTEXT` index) dan filter dropdown (Unit, Kategori, Status, Periode) menerapkan *debounce* `300ms` lalu memanggil **Partial Reload** Inertia agar hanya prop tabel dan filter yang dikirim ulang oleh Laravel:

```ts
// Pola referensi Inertia.js (berlaku untuk Vue 3 watch / React useEffect)
router.get(
  route('employees.index'),
  { search, unit_id, category, status },
  {
    preserveState: true,
    preserveScroll: true,
    replace: true,               // Tidak memenuhi riwayat tombol Back browser
    only: ['employees', 'filters'], // Partial reload: header, sidebar, & opsi unit tidak diambil ulang
  }
);
```

### 5.2 Optimistic Locking & Penanganan Konflik `HTTP 409` (`RULE-17`, `EX-04`)
* Setiap formulir persetujuan (`leave_requests`, `overtime_requests`, `allowance_requests`, `academic_rank_histories`, `staff_rank_histories`, `performance_targets`, `retirements`) wajib menyertakan `version` di dalam objek `useForm({ version: item.version, decision_note: '' })`.
* Bila dua approver menekan tombol setuju secara bersamaan, backend mendeteksi `affected_rows === 0` dan mengembalikan flash `conflict` (atau status konflik).
* Frontend otomatis membuka **`ConflictModal`** berwarna kuning: *"Pengajuan ini baru saja diproses oleh pengguna lain."* disertai tombol **Muat Ulang Data Terbaru** yang menjalankan `router.reload({ only: ['request', 'items'] })`.

### 5.3 Drill-Down Graph (ApexCharts + Inertia Partial Reload)
Pada Dashboard dan halaman Laporan (`RPT-01` s.d. `RPT-06`), event `dataPointSelection` pada ApexCharts disambungkan ke `router.get()` dengan `preserveScroll: true`:
* Mengklik batang unit atau potongan kategori pada grafik komposisi SDM (`RPT-06`) langsung memfilter tabel rincian di bawah grafik sesuai `unit_id` dan `category` yang diklik.
* Muncul *active filter chip* di atas tabel (misalnya `Filter Grafik: Fakultas Teknologi Industri ×`) agar pengguna dapat mereset filter dengan sekali klik.

### 5.4 Persistent Layout & Deferred Loading (`NFR-04` $\le 2$ Detik)
* Seluruh halaman terautentikasi menetapkan `AppLayout` sebagai *Persistent Layout* Inertia agar posisi scroll Sidebar, status menu dropdown yang terbuka, dan antrean Toast tidak ter-reset saat berpindah halaman.
* Widget grafik agregat yang berat pada Dashboard menggunakan **Deferred Props** (Inertia v2) atau *lazy partial reload* setelah kerangka halaman tampil, sehingga *First Contentful Paint* tetap jauh di bawah batas $\le 2$ detik.

---

## 6. Layout, Navigasi & Matriks Visibilitas Role

### 6.1 App Shell Blueprint (Desktop $\ge 1280\text{px}$)

```text
+-----------------------+---------------------------------------------------------------+
|  ANTECH ASYNC HRIS    |  [Cari Pegawai / NIP... (Ctrl+K)]     [Cakupan: Unit/Global]  |
|  BSDM UAD             |                                        [Notif] [Avatar/Role]  |
+-----------------------+---------------------------------------------------------------+
|  [Sidebar #2C3E50]    |  Breadcrumb: Pengajuan > Cuti                [+ Ajukan Cuti]  |
|  > Dashboard          |---------------------------------------------------------------|
|    Organisasi & Peg.  |  +----------------+  +----------------+  +----------------+   |
|    Rekrutmen          |  | KPI Card 1     |  | KPI Card 2     |  | KPI Card 3     |   |
|    Pengajuan (v)      |  +----------------+  +----------------+  +----------------+   |
|      - Cuti           |                                                               |
|      - Lembur         |  +---------------------------------------------------------+  |
|      - Tunjangan      |  | Live Filter: [Cari...] [Unit v] [Kategori v] [Status v] |  |
|    Jadwal Kerja       |  +---------------------------------------------------------+  |
|    Karir & Pengemb.   |  | Data Table / Drill-Down Chart (ApexCharts)              |  |
|    Kinerja (v)        |  |                                                         |  |
|      - BKD/LKD        |  |  [Baris data...]          [StatusBadge]   [Detail >]    |  |
|      - SKP            |  +---------------------------------------------------------+  |
|    Kesejahteraan BPJS |                                     +---------------------+   |
|    Pensiun            |                                     | Side-Drawer (520px) |   |
|    Laporan            |                                     | Detail & Audit Log  |   |
|  -------------------- |                                     +---------------------+   |
|    User & Role        |                                                               |
|    Activity Log       |                                                               |
+-----------------------+---------------------------------------------------------------+
```

### 6.2 Breakpoint Responsivitas
* **Desktop ($\ge 1280\text{px}$):** Sidebar kiri terbuka tetap (`260px`), menu aktif berlatar `#1E2B38` dengan border kiri `3px solid #E67E22`, grid KPI 3–4 kolom.
* **Tablet (`768px` – `1279px`):** Sidebar dapat diciutkan menjadi *icon rail* (`72px`) dengan tooltip hover, grid KPI 2 kolom.
* **Mobile ($< 768\text{px}$):** Sidebar terlipat menjadi menu burger di kiri atas (*off-canvas drawer*), grid KPI 1 kolom, dan tabel mendukung scroll horizontal dengan kolom identitas (Nama/NIP) tetap *sticky* di kiri.

### 6.3 Matriks Visibilitas 12 Menu Navigasi per Role

Penyembunyian menu di frontend hanya bersifat kenyamanan UX; penegakan akses utama tetap berada pada Laravel Policy & query scope (`PRD Bagian 3.3`).

| Menu Navigasi | Pegawai | Atasan Langsung | Staf Bidang BSDM | Kepala Biro / Warek | Admin Sistem |
|---|---|---|---|---|---|
| 1. Dashboard | Ringkasan Pribadi | Antrean Unit | Antrean Bidang | Agregat Eksekutif | Kesehatan Sistem |
| 2. Organisasi & Pegawai | Profil Sendiri | Bawahan Unit | Sesuai Bidang | Lintas Unit | Kelola Unit |
| 3. Rekrutmen | — | — | Bidang Seleksi | Monitoring | — |
| 4. Pengajuan (Cuti/Lembur/Tunjangan) | Ajukan & Pantau | Approve Unit | Kelola & Rekap | Monitoring | — |
| 5. Jadwal Kerja | Lihat Jadwal | Lihat Unit | Kelola & Publish | Monitoring | — |
| 6. Karir & Pengembangan | Riwayat & Ajukan | Lihat Unit | Seleksi / Diklat | Monitoring | — |
| 7. Kinerja (BKD & SKP) | Isi & Revisi | Validasi Unit | Evaluasi Kinerja | Monitoring | — |
| 8. Kesejahteraan (BPJS) | Data Sendiri | — | Izin Khusus BSDM | Agregat (*Masked*) | — |
| 9. Pensiun | Status Sendiri | Info Unit | Kelola Pensiun | Monitoring | — |
| 10. Laporan (`RPT-01` s.d. `RPT-06`) | — | Rekap Unit | Laporan Bidang | `RPT-01` s.d. `RPT-06` | — |
| 11. Manajemen User & Role | — | — | — | — | Penuh |
| 12. Activity Log | Log Sendiri | Log Unit | Log Bidang | Audit Lintas Unit | Audit Sistem |

---

## 7. Blueprint Layar per Modul (17 Proses Bisnis)

### 7.1 Dashboard & Analitik (`P-17`)
* **Atas:** Grid 3 KPI Card adaptif sesuai role (misal: Total Pegawai Aktif Dosen/Tendik, Menunggu Persetujuan, Mendekati Pensiun; untuk Pegawai: Sisa Cuti Tahunan, Status BKD Semester Aktif, Status SKP Berjalan).
* **Tengah:** Dua grafik ApexCharts interaktif (*Drill-Down*): Komposisi Pegawai per Unit/Kategori (`RPT-06`) dan Rekap Cuti/Lembur Bulanan (`RPT-04`).
* **Bawah:** Tabel ringkas 10 transaksi terbaru berstatus `diajukan` dengan tombol **Tinjau** yang membuka `SideDrawer`.

### 7.2 Organisasi & Data Pegawai (`PRD-FR-01`, `PRD-FR-02`)
* **Struktur Unit (`departments`):** Switcher tampilan antara **Tree View** hierarki (`biro` $\rightarrow$ `bidang` $\rightarrow$ `urusan` $\rightarrow$ `unit`) dan **Table View**.
* **Daftar Pegawai:** Live search (Nama/NIP) + filter Unit, Kategori (`DOSEN`/`TENDIK`), dan Status (`aktif`/`nonaktif`/`pensiun`).
* **Detail Pegawai (Tabbed Page):**
  1. *Biodata & Penempatan Aktif* (NIP, NIDN, pendidikan terakhir, atasan langsung).
  2. *Riwayat Penempatan* (`employee_assignments` dalam format timeline `effective_from` – `effective_until`).
  3. *Karir, Pelatihan & Kinerja* (ringkasan jabatan, studi S3, sertifikasi, BKD, SKP).
  4. *Kesejahteraan BPJS* (dengan tombol *Reveal* nomor kepesertaan khusus pengguna berizin).

### 7.3 Rekrutmen & Onboarding (`P-01` s.d. `P-03`)
* **Lowongan (`vacancies`):** Tabel/card lowongan beserta kuota, jumlah pelamar, periode buka–tutup, dan tombol publikasi (`draf` $\rightarrow$ `dibuka`).
* **Seleksi Pelamar (`applications` & `selection_stages`):** Menampilkan `ProgressStepper` tahap seleksi + input nilai tahap & `aik_score`. Tombol lanjut tahap terkunci otomatis bila tahap sebelumnya belum berstatus `lolos` (`RULE-01`).
* **Onboarding:** Tombol **Konversi ke Pegawai Aktif** hanya aktif bila `application.status === 'diterima'` dan belum memiliki relasi `employees.application_id` (`RULE-02`).

### 7.4 Pengajuan Cuti, Lembur & Tunjangan (`P-04`, `P-05`, `P-15`)
* **Cuti:** Header formulir menampilkan `LeaveQuotaCards` (Kuota, Terpakai, Sisa). Pemilihan `start_date` dan `end_date` langsung menghitung `total_days` di klien dan menampilkan peringatan inline bila melebihi sisa kuota (`EX-01`).
* **Lembur:** Input `work_date`, `start_time`, `end_time` otomatis menghitung `total_hours` dan memblokir pengiriman klien jika `end_time <= start_time` (`EX-07`).
* **Tunjangan:** Opsi jenis tunjangan yang memiliki `requires_doctorate = true` dinonaktifkan dengan tooltip informatif bila `auth.user.highest_education !== 'S3'` (`RULE-09`).
* **Guard Self-Approval (`RULE-14`):** Komponen tombol **Setujui** / **Tolak** tidak pernah dirender apabila `auth.user.employee_id === item.employee_id`.

### 7.5 Jadwal Kerja (`P-06`)
* **Weekly Schedule Matrix:** Grid 7 baris (Senin–Minggu) untuk mengisi `start_time` dan `end_time` sekaligus mencegah pemilihan `day_of_week` ganda.
* **Versi Historis:** Jadwal yang sudah `is_published = true` tidak diubah menimpa histori; aksi perubahan disediakan lewat tombol **Buat Versi Baru** yang menutup `valid_until` jadwal lama.

### 7.6 Karir & Pengembangan (`P-07` s.d. `P-11`)
* **Jabatan Fungsional Dosen:** Menampilkan `ProgressStepper` 4 jenjang (`Asisten Ahli`, `Lektor`, `Lektor Kepala`, `Guru Besar`). Jika pilihan jenjang tujuan melompati urutan, form memunculkan *warning banner* dan mewajibkan pengaktifan opsi **Jalur Percepatan (`is_accelerated`)** (`RULE-05`, `EX-06`).
* **Kepangkatan Tendik:** Timeline pangkat/golongan dan jabatan fungsional dengan panel ringkasan masa kerja dan nilai SKP periode terakhir (`RULE-06`).
* **Studi Lanjut S3, Sertifikasi & Pelatihan:** Tabel dengan badge status beasiswa, tautan unduh sertifikat privat (`<a>`), serta label khusus untuk pelatihan `pra_pensiun` yang tertaut ke `retirement_id`.

### 7.7 Evaluasi Kinerja: BKD/LKD & SKP (`P-12`, `P-13`)
* **BKD/LKD (`workload_reports`):** Empat input SKS (*computed property* menjumlahkan `total_credit` secara real-time di klien, serasi dengan *generated column* MySQL). Untuk laporan berstatus `final`, form terkunci (*read-only*) dan menyediakan pemilih versi (`v1`, `v2 [Berlaku]`) serta tombol **Ajukan Revisi** yang membuka `ReasonModal` (`revision_reason` wajib, `RULE-07`).
* **SKP (`performance_targets` & `performance_items`):** Tabel baris dinamis (tambah/hapus butir target) dilengkapi *progress bar* **Total Bobot**: berwarna merah bila $\sum \text{weight} \ne 100\%$ dan hijau bila tepat $100\%$. Kartu `final_score` dan `rating` hanya menampilkan hasil setelah status `disetujui` (`RULE-08`).

### 7.8 Kesejahteraan (BPJS) & Pensiun (`P-14`, `P-16`)
* **BPJS (`bpjs_memberships`):** Komponen `BpjsMaskedField` menyamarkan `membership_number` menjadi `********1234`. Penambahan kepesertaan `aktif` untuk jenis BPJS yang sudah aktif diblokir di UI dengan panduan menonaktifkan/mengarsipkan data lama terlebih dahulu (`RULE-16`).
* **Pensiun (`retirements`):** Daftar pegawai usulan *scheduled job* dengan hitung mundur `planned_date` dan status pelatihan pra-pensiun. Modal penyelesaian menampilkan `RetirementChecklist` yang memeriksa 7 tabel pengajuan aktif; tombol **Selesaikan Pensiun** terkunci bila masih ada pengajuan berstatus `diajukan` atau `draf` (`EX-09`).

### 7.9 Laporan Lintas Bidang & Activity Log (`P-17`)
* **Laporan (`RPT-01` s.d. `RPT-06`):** Bilah filter seragam (Periode, Unit, Kategori, Status), grafik *Drill-Down* ApexCharts di atas, tabel agregat di bawah, serta tombol ekspor CSV/PDF via tautan unduh non-Inertia. Bila `auth.permissions.aggregate_reports_only === true` (Wakil Rektor), rincian data individu disembunyikan.
* **Activity Log (`activity_logs`):** Tabel *read-only* murni tanpa tombol edit/hapus (`RULE-13`). Baris `ACCESS_DENIED` diberi aksen garis kiri merah `#E11D48`. Mengklik baris membuka `SideDrawer` berisi aktor, IP address, status lama $\rightarrow$ baru, alasan, dan tampilan *diff* JSON `details`.

---

## 8. Pemetaan Umpan Balik UI untuk Skenario Pengecualian (`EX-01` s.d. `EX-12`)

| ID Exception | Kondisi | Umpan Balik UI pada Inertia.js | Aksi Pemulihan Pengguna |
|---|---|---|---|
| `EX-01` | Saldo cuti tidak mencukupi | Alert merah inline di bawah pemilih tanggal berisi sisa saldo | Mengurangi jumlah hari atau mengganti jenis cuti |
| `EX-02` / `EX-07` | Tanggal cuti / jam lembur bentrok | Border input merah + informasi tanggal/jam bentrok | Memilih ulang tanggal atau rentang jam |
| `EX-03` | Percobaan *self-approval* | Tombol approve tidak dirender; akses paksa menampilkan halaman Inertia `403` | Kembali ke Dashboard (sistem mencatat `ACCESS_DENIED`) |
| `EX-04` | Konflik konkurensi (`version` beda) | `ConflictModal` kuning (`HTTP 409` / flash conflict) | Klik **Muat Ulang Data Terbaru** (`router.reload()`) |
| `EX-05` | Status final tidak boleh diubah | Ikon gembok pada `StatusBadge`; tombol edit disembunyikan | Klik **Ajukan Ulang** atau **Buat Revisi** (BKD) |
| `EX-06` | Lompatan jenjang jabatan Dosen | Banner kuning pada form jabatan fungsional | Aktifkan *toggle* **Jalur Percepatan** (`is_accelerated`) |
| `EX-09` | Pensiun tertahan pengajuan aktif | `RetirementChecklist` merah berisi tautan ke pengajuan terkait | Menyelesaikan/menutup pengajuan aktif terlebih dahulu |
| `EX-10` / `EX-11` | Email pelamar / No. BPJS duplikat | `form.errors` inline tepat di bawah kolom terkait | Mengklik tautan menuju data yang sudah terdaftar |

---

## 9. Aksesibilitas (WCAG 2.1 AA) & Target Performa Frontend

* **Navigasi Keyboard & Fokus:** Seluruh modal, drawer, dan dropdown mendukung `Tab`, `Shift+Tab`, `Enter`, dan `Escape`. Fokus elemen interaktif wajib memakai `focus-visible:ring-2 focus-visible:ring-[#E67E22] focus-visible:ring-offset-2`.
* **ARIA Attributes:** Seluruh tombol berbasis ikon menyertakan `aria-label`; kontainer toast dan pesan error inline memakai `aria-live="polite"`; modal/drawer menerapkan *focus trap* dan `role="dialog"`.
* **Kinerja Antarmuka (`NFR-04` $\le 2$ Detik):**
  * *Code splitting* otomatis per halaman melalui Vite + Inertia (`import.meta.glob('./Pages/**')`) sehingga bundle awal tetap kecil.
  * *Debounce* `300ms` pada pencarian live dan paginasi server-side `15` / `25` baris per halaman.
  * *Partial reload* (`only: [...]`) pada filter tabel dan *drill-down* grafik agar payload JSON tetap ringan.

---

## 10. Riwayat Perubahan Dokumen

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0 | 17 November 2026 | Dokumen spesifikasi UI terpisah dibuat untuk stack **Inertia.js + Vue 3 / React + Tailwind CSS + ApexCharts**, menggantikan Bagian 11 pada `SDD.md`. |
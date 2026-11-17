# PRODUCT REQUIREMENTS DOCUMENT (PRD)

## Antech Async - Human Resource Information System (HRIS)

| Metadata | Detail |
|---|---|
| Nama Produk | Antech Async - Sistem Informasi Kepegawaian dan Manajemen Sumber Daya Manusia (HRIS) |
| Organisasi / Domain | Biro Sumber Daya Manusia (BSDM), Universitas Ahmad Dahlan |
| Versi PRD | 1.1 (Hasil Audit & Penyelarasan MySQL 8.4 LTS) |
| Status | Draft - diturunkan dari BRD v1.1 |
| Tanggal sumber BRD | 7 September 2026 |
| Tanggal PRD | 17 November 2026 |
| Tim | Antech Async |
| Sumber utama | Business Requirements Document (BRD) Antech Async v1.1 |
| Backend yang direncanakan | Laravel |
| Database | **MySQL saja** |
| Database engine yang direkomendasikan | MySQL 8.4 LTS, InnoDB |
| Dokumen lanjutan | ERD (DBML), normalisasi, logical schema, migration, seed data, indexing, dan test plan |


> **Keputusan teknologi:** Seluruh rancangan database, migration, query, constraint, trigger, indexing, dan pengujian pada PRD ini ditujukan khusus untuk **MySQL 8.4 LTS (InnoDB)**. PostgreSQL tidak digunakan sebagai target implementasi. Penyesuaian teknis dari rancangan PostgreSQL pada BRD dijelaskan secara rinci pada Bagian 10.

---

## 1. Pendahuluan

### 1.1 Tujuan Dokumen

PRD ini menerjemahkan kebutuhan bisnis dalam BRD Antech Async v1.1 menjadi kebutuhan produk dan acuan teknis awal untuk pengembangan HRIS BSDM UAD. Dokumen menjelaskan ruang lingkup produk, aktor, modul, alur kerja, functional requirement, aturan bisnis, kebutuhan data, standar database MySQL 8.4, kebutuhan non-fungsional, keamanan, laporan, serta kriteria penerimaan.

PRD menjadi penghubung antara BRD dengan tahap perancangan ERD (DBML), normalisasi, logical schema MySQL, Laravel migration, implementasi fitur, dan pengujian.

### 1.2 Latar Belakang

BSDM UAD mengelola dua kategori pegawai yang memiliki jalur karir berbeda:

- **Dosen**, dengan jalur jabatan fungsional akademik (Asisten Ahli hingga Guru Besar).
- **Tenaga Kependidikan (Tendik)**, dengan jalur kepangkatan dan jabatan fungsional tendik (pustakawan, laboran, programer, dll.).

Layanan BSDM meliputi rekrutmen, onboarding, cuti, lembur, pengaturan hari dan waktu kerja, pengembangan karir, studi lanjut, sertifikasi, pelatihan, BKD/LKD, SKP, BPJS, tunjangan, hingga pensiun, yang dijalankan oleh tiga bidang: **Seleksi & Pengembangan Karir**, **Pendidikan & Pelatihan**, serta **Evaluasi Kinerja**. Proses yang tersebar pada berkas dan layanan terpisah menyulitkan pencarian data, pemantauan lintas bidang, pelacakan riwayat, serta penyusunan laporan.

HRIS Antech Async dikembangkan untuk memusatkan data dan proses tersebut dalam satu sistem yang terstruktur, memiliki alur persetujuan terstandar, serta menyimpan jejak audit yang tidak dapat dimanipulasi.

### 1.3 Permasalahan Bisnis

| ID | Permasalahan | Dampak |
|---|---|---|
| BP-01 | Data pegawai, rekrutmen, dan onboarding tersebar di berkas terpisah dan sulit dicari secara terpusat. | Data tidak konsisten, proses seleksi dan penerimaan sulit didokumentasikan. |
| BP-02 | Pengajuan cuti, lembur, dan tunjangan belum memakai alur persetujuan yang terstandar dan tercatat. | Persetujuan lambat, hak pegawai tidak jelas, dan riwayat sulit diverifikasi. |
| BP-03 | Perjalanan karir Dosen dan Tendik (jabatan fungsional, kepangkatan, studi lanjut, pelatihan) sulit dipantau lintas bidang. | Jenjang karir dan percepatan kualifikasi tidak terpantau. |
| BP-04 | Evaluasi kinerja (BKD/LKD dan SKP) belum terdokumentasi secara terstruktur. | Beban dan kinerja pegawai sulit dievaluasi sesuai regulasi. |
| BP-05 | Kepesertaan BPJS dan proses pensiun belum terkelola dan terjadwal dalam satu sistem. | Hak jaminan sosial dan transisi pensiun berisiko tidak tertib. |
| BP-06 | Pimpinan sulit memperoleh gambaran kondisi SDM lintas bidang. | Keputusan tidak berbasis data. |

### 1.4 Tujuan Produk dan Pemetaan ke Business Objectives (BO)

| ID BO (BRD) | Business Objective | Tujuan Produk yang Diturunkan |
|---|---|---|
| **BO-01** | Menyediakan pencatatan data pegawai (Dosen dan Tendik) beserta proses rekrutmen dan onboarding secara terpusat. | 1. Menyediakan data induk pegawai Dosen dan Tendik beserta struktur organisasi dan penempatannya secara terpusat.<br>2. Mendokumentasikan lowongan, seleksi pelamar (termasuk nilai AIK), dan proses onboarding kandidat diterima menjadi pegawai aktif. |
| **BO-02** | Memudahkan pengajuan dan persetujuan cuti, lembur, tunjangan, dan kenaikan jabatan/pangkat dengan alur yang tercatat. | 3. Menyediakan pengajuan serta persetujuan cuti, lembur, jadwal kerja, tunjangan, dan kenaikan jabatan/pangkat dengan state machine yang konsisten dan bebas pemrosesan ganda. |
| **BO-03** | Menyediakan riwayat karir, kinerja, pelatihan, dan kesejahteraan pegawai untuk kebutuhan evaluasi dan audit. | 4. Memelihara riwayat jabatan akademik, kepangkatan tendik, pelatihan, sertifikasi, studi lanjut S3, BKD/LKD, dan SKP.<br>5. Mendokumentasikan kepesertaan BPJS serta jadwal dan proses pensiun.<br>6. Memastikan data historis dan aktivitas penting tercatat secara *append-only* pada audit log. |
| **BO-04** | Menyediakan laporan SDM lintas bidang untuk pengambilan keputusan pimpinan. | 7. Menyediakan dashboard dan 6 laporan SDM lintas bidang yang dapat difilter per unit dan periode sesuai cakupan hak akses. |

### 1.5 Prinsip Produk

- **Centralized data:** data pegawai dan transaksi disimpan dalam satu database MySQL 8.4.
- **Role-based access:** hak akses mengikuti peran dan cakupan unit (`unit_id`).
- **Traceable workflow:** setiap pengajuan dan keputusan memiliki status serta riwayat perubahan yang utuh.
- **Historical record:** perubahan data penting tidak menimpa atau menghilangkan riwayat sebelumnya (*soft delete* dan versi baru).
- **Data integrity:** relasi foreign key, unique constraint, generated column, trigger, transaksi InnoDB, dan validasi aplikasi mencegah data tidak konsisten.
- **Modular implementation:** modul dikembangkan berdasarkan prioritas *Must* dan *Should* pada BRD.

---

## 2. Ruang Lingkup Produk

### 2.1 In Scope

Produk mencakup 17 proses bisnis berikut:

| ID | Proses | Hasil yang diharapkan |
|---|---|---|
| P-01 | Posting lowongan Dosen/Tendik | Lowongan berdasarkan kebutuhan SDM unit tercatat dan dapat dipublikasikan. |
| P-02 | Seleksi dan rekrutmen | Data pelamar, tahapan seleksi, penilaian (termasuk AIK), dan hasil diterima/ditolak tercatat. |
| P-03 | Onboarding pegawai baru | Kandidat berstatus diterima dapat dibuat menjadi profil pegawai aktif. |
| P-04 | Pengajuan cuti pegawai | Pengajuan cuti, pemotongan saldo, dan keputusan atasan tercatat. |
| P-05 | Pengajuan lembur | Jam lembur, durasi, dan keputusan atasan tercatat. |
| P-06 | Pengaturan hari dan waktu kerja | Jadwal kerja dapat ditetapkan, dipublikasikan, dan memiliki versi riwayat. |
| P-07 | Percepatan jabatan fungsional Dosen | Pengajuan dan riwayat kenaikan jabatan Asisten Ahli hingga Guru Besar tercatat. |
| P-08 | Kepangkatan dan jabatan fungsional Tendik | Pengajuan dan riwayat kenaikan pangkat/jabatan pustakawan, laboran, programer, dll. tercatat. |
| P-09 | Studi lanjut Dosen (S3) | Pemetaan dosen studi lanjut S3 dan pendampingan beasiswa tercatat. |
| P-10 | Sertifikasi Dosen | Proses dan hasil sertifikasi Dosen beserta berkasnya tercatat. |
| P-11 | Pelatihan Dosen dan Tendik | Riwayat pelatihan (termasuk pra-pensiun, IT, dan bahasa asing) serta sertifikat tercatat. |
| P-12 | Pengisian BKD/LKD | Beban kerja Dosen per semester dicatat, divalidasi atasan, dan mendukung versi revisi. |
| P-13 | Pengisian SKP | Target, realisasi, bobot, dan skor kinerja pegawai dicatat per periode evaluasi. |
| P-14 | Pengelolaan BPJS | Kepesertaan BPJS Kesehatan dan Ketenagakerjaan dicatat dengan kontrol keunikan aktif. |
| P-15 | Pengajuan tunjangan | Pengajuan tunjangan kesejahteraan/doktor dan keputusan persetujuannya tercatat. |
| P-16 | Pengelolaan pensiun | Pegawai mendekati usia pensiun dapat diusulkan terjadwal, dipantau, dan diproses. |
| P-17 | Monitoring dan pelaporan SDM | Laporan SDM lintas bidang dapat disajikan dan difilter berdasarkan unit serta periode. |

### 2.2 Out of Scope untuk Versi Ini

Hal-hal berikut tidak menjadi fitur inti versi ini karena BRD tidak menetapkannya sebagai kebutuhan yang disetujui atau masih menunggu keputusan:

1. **Payroll/penggajian dan perhitungan gaji.** BRD menyatakan fokus versi ini adalah pencatatan data kepegawaian. Status payroll menunggu keputusan melalui Q-04.
2. Integrasi aktif dengan API BPJS, pangkalan data dosen nasional, sistem keuangan/penggajian, layanan email, atau SSO kampus. Integrasi ini baru berupa potensi integrasi pada BRD (Q-08).
3. Penentuan kebijakan final jenis/jatah cuti, usia pensiun, pejabat persetujuan jalur percepatan, SLA persetujuan, dan retensi data sebelum dikonfirmasi oleh BSDM.
4. Penggunaan data pegawai nyata pada simulasi akademik. Data dan volume awal merupakan asumsi dan seluruh pengujian wajib memakai data sintetis.

### 2.3 Prioritas Implementasi

- **Must:** BR-01, BR-02, BR-04, BR-05, BR-07, BR-08, BR-09, BR-11.
- **Should:** BR-03, BR-06, BR-10, BR-12.

Urutan sprint dan pembagian deliverable ditentukan tim dengan mendahulukan seluruh kebutuhan *Must*.

---

## 3. Pengguna, Stakeholder, dan Hak Akses

### 3.1 Stakeholder

| Stakeholder | Peran dalam BRD/Produk | Kebutuhan Utama |
|---|---|---|
| Wakil Rektor Bidang SDM | Reviewer / decision maker | Menyetujui kebijakan SDM tingkat universitas dan memantau laporan agregat. |
| Kepala Biro SDM (BSDM) | Owner / primary actor | Mengoordinasikan seluruh bidang SDM dan memantau laporan lintas bidang. |
| Kepala Bidang Seleksi & Pengembangan Karir | Primary actor | Mengelola rekrutmen, jabatan fungsional Dosen, kepangkatan dan jabatan Tendik. |
| Kepala Bidang Pendidikan & Pelatihan | Primary actor | Mengelola studi lanjut S3, pelatihan, dan sertifikasi Dosen/Tendik. |
| Kepala Bidang Evaluasi Kinerja | Primary actor | Mengelola SKP, BKD/LKD, dan pengembangan informasi evaluasi kinerja. |
| Kepala Urusan & Staf BSDM | Support / operational actor | Menjalankan proses administratif harian sesuai bidang masing-masing. |
| Dosen | Primary actor (subject) | Melihat data sendiri; mengajukan cuti, lembur, BKD/LKD, jabatan fungsional, studi lanjut, sertifikasi, dan tunjangan. |
| Tenaga Kependidikan (Tendik) | Primary actor (subject) | Melihat data sendiri; mengajukan cuti, lembur, kepangkatan, jabatan fungsional tendik, dan tunjangan. |
| Atasan langsung | Approver | Menyetujui atau menolak pengajuan serta memvalidasi BKD/LKD dan SKP bawahan pada unitnya. |
| Pelamar (calon Dosen/Tendik) | External participant | Melamar lowongan Dosen/Tendik yang sedang dibuka. |
| Admin Sistem | Support | Mengelola akun pengguna, penetapan role, dan konfigurasi teknis sistem. |

### 3.2 Matriks Hak Akses

| Peran | Akses Utama | Batasan |
|---|---|---|
| Pegawai (Dosen/Tendik) | Melihat profil dan riwayat sendiri; membuat dan memantau pengajuan milik sendiri. | Tidak dapat melihat data pegawai lain atau menyetujui pengajuannya sendiri (`RULE-14`). |
| Atasan langsung | Melihat data bawahan dalam cakupan unit; menyetujui pengajuan; memvalidasi BKD/LKD dan SKP untuk unit sendiri. | Tidak dapat memproses pengajuan sendiri; cakupan unit mengikuti penugasan aktif. |
| Staf/Kepala Bidang Seleksi | Lowongan, pelamar, seleksi, onboarding, jabatan fungsional Dosen, dan kepangkatan Tendik. | Akses dibatasi ke fungsi dan data bidang Seleksi & Pengembangan Karir. |
| Staf/Kepala Bidang Diklat | Studi lanjut, sertifikasi, dan pelatihan. | Akses dibatasi ke fungsi dan data bidang Pendidikan & Pelatihan. |
| Staf/Kepala Bidang Evaluasi Kinerja | SKP, BKD/LKD, dan laporan kinerja. | Akses dibatasi ke fungsi dan data bidang Evaluasi Kinerja. |
| Kepala Biro SDM | Monitoring dan laporan lintas bidang; kewenangan koordinasi sesuai kebijakan. | Data sensitif tetap mengikuti pemeriksaan otorisasi khusus. |
| Wakil Rektor Bidang SDM | Monitoring dan laporan tingkat universitas (tampilan agregat). | Hanya melihat agregat; tidak otomatis memperoleh akses detail data sensitif per individu. |
| Admin Sistem | Akun, role, dan konfigurasi sistem. | Tidak otomatis mendapat hak membaca atau mengubah seluruh data bisnis/kepegawaian. |

**Catatan otorisasi:** Kebijakan mengenai siapa yang boleh memberikan/menaikkan role menjadi Admin Sistem atau role bisnis tingkat tinggi harus dikunci bersama pemilik sistem sebelum implementasi. Setiap pemberian atau pencabutan role wajib tercatat dalam `activity_logs`.

### 3.3 Model Role dan Cakupan Unit

- Tabel `roles` menyimpan daftar role.
- Tabel `user_roles` menghubungkan akun (`users`) dengan role (`roles`) dan dapat membatasi akses pada `unit_id` tertentu.
- Role dengan `unit_id` bernilai `NULL` digunakan untuk cakupan lintas unit sesuai konfigurasi kebijakan akses.
- Otorisasi wajib memeriksa role sekaligus kepemilikan baris data / cakupan unit di sisi server (Policy & Query Scope); menyembunyikan menu pada UI saja tidak cukup.
- Data BPJS (`membership_number`), nilai tunjangan, alamat domisili, dan informasi pribadi harus mempunyai pemeriksaan izin tambahan (`RULE-15`).

---

## 4. Gambaran Sistem dan Modul Produk

### 4.1 Modul Utama

| Modul | Fungsi | Proses Terkait | Entitas Inti |
|---|---|---|---|
| Dashboard & Organisasi | Ringkasan SDM, struktur unit, data pegawai, dan riwayat penempatan. | P-17 | `departments`, `employees`, `employee_assignments` |
| Manajemen Pengguna & Akses | Login, manajemen akun, role, dan cakupan unit. | Lintas proses | `users`, `roles`, `user_roles` |
| Rekrutmen & Onboarding | Lowongan, data pelamar, tahapan seleksi, dan pembentukan pegawai baru. | P-01 s.d. P-03 | `vacancies`, `applicants`, `applications`, `selection_stages`, `employees` |
| Cuti | Jenis cuti, saldo tahunan, pengajuan, persetujuan, dan riwayat cuti. | P-04 | `leave_types`, `leave_balances`, `leave_requests` |
| Lembur | Pengajuan jam lembur, validasi durasi, persetujuan, dan rekap. | P-05 | `overtime_requests` |
| Jadwal Kerja | Penyusunan, publikasi, dan versi historis jadwal kerja per unit/kategori. | P-06 | `work_schedules`, `work_schedule_days` |
| Karir Dosen & Tendik | Jabatan fungsional Dosen, kepangkatan Tendik, studi lanjut, sertifikasi, dan pelatihan. | P-07 s.d. P-11 | `academic_rank_histories`, `staff_rank_histories`, `study_leaves`, `certifications`, `trainings` |
| Kinerja | Laporan BKD/LKD per semester dan evaluasi SKP per periode. | P-12, P-13 | `workload_reports`, `performance_targets`, `performance_items` |
| Kesejahteraan | Kepesertaan BPJS dan pengajuan tunjangan. | P-14, P-15 | `bpjs_memberships`, `allowance_types`, `allowance_requests` |
| Pensiun | Usulan terjadwal, pemrosesan SK pensiun, dan penautan pelatihan pra-pensiun. | P-16 | `retirements`, `trainings` |
| Audit & Pelaporan | Jejak aktivitas (*append-only*) dan laporan SDM lintas bidang. | P-17 | `activity_logs` dan seluruh entitas agregat |

### 4.2 Navigasi Produk yang Diharapkan

1. Dashboard.
2. Data Organisasi dan Pegawai.
3. Rekrutmen & Onboarding.
4. Pengajuan: Cuti, Lembur, dan Tunjangan.
5. Jadwal Kerja.
6. Karir dan Pengembangan (Jabatan Akademik, Pangkat Tendik, Studi Lanjut, Sertifikasi, Pelatihan).
7. Kinerja: BKD/LKD dan SKP.
8. Kesejahteraan: BPJS.
9. Pensiun.
10. Laporan SDM.
11. Manajemen Pengguna dan Role.
12. Activity Log.

---

## 5. Alur Kerja Produk

### 5.1 Rekrutmen dan Onboarding (P-01 s.d. P-03)

1. Staf bidang Seleksi membuat lowongan dengan unit pemilik, kategori (`DOSEN`/`TENDIK`), kualifikasi minimum, kuota, tanggal buka, dan tanggal tutup ($\text{close\_date} \ge \text{open\_date}$).
2. Lowongan disimpan sebagai `draf`, kemudian dipublikasikan menjadi `dibuka`.
3. Pelamar mengisi biodata (`applicants`) dan mengirim pendaftaran (`applications`) pada lowongan yang dibuka.
4. Sistem mencegah email pelamar duplikat dan mencegah pelamar mendaftar lebih dari satu kali pada lowongan yang sama (`RULE-01`, `EX-10`).
5. Petugas mencatat tahapan seleksi (`selection_stages`), urutan tahap, nilai tahap, nilai AIK pada pendaftaran, catatan, dan penilai.
6. Pelamar hanya dapat diproses ke tahap berikutnya bila memenuhi kualifikasi minimum dan kriteria penilaian (`RULE-01`).
7. Keputusan akhir pendaftaran ditetapkan menjadi `diterima` atau `ditolak`.
8. Profil pegawai aktif (`employees`) hanya dapat dibuat dari pendaftaran berstatus `diterima` (`RULE-02`) dan hanya boleh menghasilkan satu record pegawai per pendaftaran.
9. Saat onboarding dibuat, sistem juga mencatat baris awal pada `employee_assignments` serta membuat akun `users` bila diperlukan.

### 5.2 Pengajuan Cuti (P-04)

1. Pegawai memilih jenis cuti, tanggal mulai, tanggal selesai ($\text{end\_date} \ge \text{start\_date}$), dan alasan.
2. Sistem memvalidasi ketersediaan saldo cuti (`leave_balances`) serta memastikan tanggal tidak bertumpuk dengan cuti lain milik pegawai tersebut yang berstatus `disetujui` (atau sedang `diajukan`).
3. Bila saldo kurang (`EX-01`) atau tanggal bentrok (`EX-02`), sistem menolak pengajuan dan menampilkan informasi detailnya.
4. Pengajuan disimpan dengan status `diajukan` kepada atasan langsung / pejabat berwenang (`approver_id` $\ne$ `employee_id`).
5. Approver menyetujui (`disetujui`) atau menolak (`ditolak`). Alasan keputusan (`decision_note`) wajib diisi saat penolakan atau pembatalan (`RULE-12`).
6. Jika disetujui, penambahan `used_days` pada `leave_balances`, perubahan status `leave_requests`, dan pencatatan `activity_logs` dieksekusi dalam satu transaksi database dengan penguncian baris (*row locking*).
7. Pemohon dapat membatalkan (`dibatalkan`) selama status masih `diajukan`. Status final tidak dapat dibuka kembali (`RULE-11`, `EX-05`).

### 5.3 Pengajuan Lembur (P-05)

1. Pegawai mengisi tanggal kerja (`work_date`), jam mulai (`start_time`), jam selesai (`end_time`), durasi jam (`total_hours`), dan alasan pekerjaan lembur.
2. Sistem memvalidasi bahwa lembur berada pada hari kalender yang sama dengan $\text{end\_time} > \text{start\_time}$ (`RULE-04`) dan tidak bertumpuk dengan jam lembur lain pada tanggal yang sama (`EX-07`).
3. Pengajuan dikirim kepada approver (`approver_id` $\ne$ `employee_id`).
4. Approver menyetujui atau menolak dengan alasan tercatat.
5. Hanya pengajuan berstatus `disetujui` yang dihitung dalam rekap kompensasi lembur (`RULE-04`).

### 5.4 Jadwal Kerja (P-06)

1. Petugas membuat jadwal kerja (`work_schedules`) berdasarkan nama, unit sasaran (opsional), kategori pegawai (opsional), dan tanggal mulai berlaku (`valid_from`).
2. Petugas mengisi rincian hari kerja (`work_schedule_days`) dari $1$ (Senin) sampai $7$ (Minggu) beserta jam masuk dan jam pulang ($\text{end\_time} > \text{start\_time}$).
3. Sistem memastikan satu hari (`day_of_week`) tidak didaftarkan dua kali pada jadwal yang sama.
4. Jadwal dipublikasikan (`is_published = 1`, `published_at` terisi).
5. Jadwal lama dipertahankan sebagai versi historis (`RULE-13`); perubahan jadwal kerja dilakukan dengan menutup masa berlaku jadwal lama (`valid_until`) dan membuat jadwal baru.

### 5.5 Riwayat Karir Dosen (P-07)

1. Pengajuan kenaikan jabatan fungsional Dosen dicatat pada `academic_rank_histories` beserta jenjang tujuan (`Asisten Ahli`, `Lektor`, `Lektor Kepala`, `Guru Besar`), angka kredit, dan penanda jalur percepatan (`is_accelerated`).
2. Kenaikan normal wajib mengikuti urutan jenjang secara berurutan. Lompatan jenjang ditolak kecuali melalui jalur percepatan (`is_accelerated = 1`) dengan persetujuan pejabat berwenang (`RULE-05`, `EX-06`).
3. Saat disetujui, nomor SK, tanggal SK, dan tanggal efektif (`effective_date`) dicatat. Riwayat jabatan terdahulu tetap dipertahankan.

### 5.6 Riwayat Pangkat/Jabatan Tendik (P-08)

1. Pengajuan kenaikan pangkat/golongan (`rank_grade`) dan/atau jabatan fungsional Tendik (`functional_position`) dicatat pada `staff_rank_histories`.
2. Sistem dan petugas memverifikasi pemenuhan syarat BSDM (seperti masa kerja dan hasil SKP sesuai `RULE-06`).
3. Keputusan, approver, tanggal efektif, nomor SK, dan tanggal SK disimpan tanpa menghapus riwayat kepangkatan sebelumnya.

### 5.7 Studi Lanjut, Sertifikasi, dan Pelatihan (P-09 s.d. P-11)

- **Studi Lanjut (`study_leaves`):** Mencatat Dosen yang menempuh studi lanjut (default `S3`), universitas, program studi, tanggal mulai, target selesai, tanggal selesai aktual, status studi, nama/status beasiswa, dan catatan pendampingan.
- **Sertifikasi (`certifications`):** Mencatat pengajuan dan hasil sertifikasi Dosen, nomor sertifikat (unik bila terbit), tanggal pengajuan, tanggal hasil, status (`diajukan`, `proses`, `lulus`, `tidak_lulus`), dan path berkas.
- **Pelatihan (`trainings`):** Mencatat riwayat pelatihan Dosen/Tendik (`pra_pensiun`, `it`, `bahasa_asing`, `lainnya`), penyelenggara, tanggal mulai/selesai, durasi jam, status, sertifikat, serta relasi opsional ke `retirements` untuk pelatihan pra-pensiun.

### 5.8 BKD/LKD (P-12)

1. Dosen mengisi beban kerja semester (`workload_reports`) yang mencakup SKS pengajaran, penelitian, pengabdian, dan penunjang. Total beban (`total_credit`) dihitung otomatis dari keempat komponen tersebut.
2. Laporan diajukan (`diajukan`) dan divalidasi oleh atasan (`validator_id` $\ne$ `employee_id`) hingga berstatus `final`.
3. Setelah berstatus `final`, laporan tidak boleh diedit langsung. Jika ada koreksi, sistem membuat baris baru dengan `version = version + 1`, mewajibkan pengisian `revision_reason`, mengatur baris baru menjadi `is_current = 1`, dan mengubah versi sebelumnya menjadi `is_current = 0` dalam satu transaksi (`RULE-07`).

### 5.9 SKP (P-13)

1. Setiap pegawai memiliki satu dokumen SKP aktif (`performance_targets`) per periode evaluasi (`period_label`).
2. Di dalam SKP terdapat rincian butir target (`performance_items`) dengan satuan ukur, nilai target, realisasi, bobot (`weight`), dan skor butir (`score`). Total bobot seluruh butir pada satu SKP harus bernilai $100\%$ saat diajukan.
3. Penilai (`appraiser_id` $\ne$ `employee_id`) memeriksa realisasi dan memberikan persetujuan (`disetujui`) atau penolakan (`ditolak`).
4. Skor akhir (`final_score`) dan predikat (`rating`) hanya dihitung dan ditetapkan setelah penilai menyetujui realisasi (`RULE-08`). Jika SKP ditolak, pegawai dapat membuat pengajuan ulang untuk periode tersebut sesuai `RULE-11`.

### 5.10 BPJS (P-14)

1. BSDM mengelola data kepesertaan `kesehatan` dan `ketenagakerjaan` pada `bpjs_memberships`.
2. Nomor kepesertaan (`membership_number`) bersifat unik dan sensitif.
3. Satu pegawai hanya boleh memiliki paling banyak satu kepesertaan berstatus `aktif` untuk setiap jenis BPJS (`RULE-16`). Kepesertaan lama yang tidak berlaku diubah menjadi `nonaktif` dan dicatat waktu arsipnya (`archived_at`).

### 5.11 Pengajuan Tunjangan (P-15)

1. Pegawai mengajukan tunjangan (`allowance_requests`) berdasarkan jenis tunjangan (`allowance_types`) dan periode (`period_label`).
2. Bila jenis tunjangan mensyaratkan gelar doktor (`requires_doctorate = 1`), sistem memvalidasi bahwa `employees.highest_education = 'S3'` (atau memiliki data kelulusan S3 yang sah) sebelum mengizinkan pengajuan (`RULE-09`).
3. Sistem mencegah pengajuan aktif ganda untuk pegawai, jenis tunjangan, dan periode yang sama (`RULE-09`), namun tetap mengizinkan pengajuan ulang apabila pengajuan sebelumnya berstatus `dibatalkan` atau `ditolak` (`RULE-11`).

### 5.12 Pensiun (P-16)

1. Scheduled job memeriksa pegawai aktif yang mendekati usia pensiun berdasarkan `birth_date` dan kebijakan usia pensiun (`RULE-10`).
2. Sistem membuat usulan pensiun (`retirements`) berstatus `diusulkan` beserta jadwal `planned_date`.
3. BSDM memproses transisi pensiun (`diproses`), menjadwalkan pelatihan pra-pensiun (`trainings.retirement_id`), dan mencatat nomor serta tanggal SK pensiun.
4. Sebelum status pensiun diselesaikan (`selesai`) dan status pegawai diubah menjadi `pensiun`/`nonaktif`, seluruh pengajuan aktif milik pegawai tersebut harus diselesaikan atau ditutup terlebih dahulu (`EX-09`).

### 5.13 Monitoring dan Pelaporan (P-17)

Sistem menyediakan dashboard dan 6 laporan utama lintas bidang dengan filter unit, kategori pegawai, status, dan periode yang menghormati batasan role pengguna.

---

## 6. Functional Requirements

### 6.1 Kebutuhan Fungsional Utama (Dari BRD)

| ID | Functional Requirement | Trace ke BR | Prioritas |
|---|---|---|---|
| FR-01 | Sistem dapat mencatat lowongan dan seleksi kandidat Dosen/Tendik dengan status lolos/gugur. | BR-01 | Must |
| FR-02 | Sistem dapat mencatat pengajuan cuti dengan jenis, tanggal, alasan, dan persetujuan atasan. | BR-02 | Must |
| FR-03 | Sistem dapat mencatat pengajuan lembur dengan jam mulai/selesai dan persetujuan atasan. | BR-03 | Should |
| FR-04 | Sistem dapat mencatat pengajuan dan riwayat jabatan fungsional Dosen per jenjang. | BR-04 | Must |
| FR-05 | Sistem dapat mencatat pengajuan dan riwayat kepangkatan/jabatan fungsional Tendik. | BR-05 | Must |
| FR-06 | Sistem dapat mencatat pemetaan studi lanjut S3 dan status pendampingan beasiswa. | BR-06 | Should |
| FR-07 | Sistem dapat mencatat BKD/LKD per semester dengan validasi atasan. | BR-07 | Must |
| FR-08 | Sistem dapat mencatat SKP dengan target dan realisasi untuk Dosen dan Tendik. | BR-08 | Must |
| FR-09 | Sistem dapat mencatat status kepesertaan BPJS per pegawai. | BR-09 | Must |
| FR-10 | Sistem dapat mencatat pengajuan tunjangan beserta status persetujuannya. | BR-10 | Should |
| FR-11 | Sistem dapat mencatat proses pensiun termasuk jadwal pelatihan pra-pensiun. | BR-11 | Must |
| FR-12 | Sistem dapat menyajikan laporan SDM yang dapat difilter per bidang dan periode. | BR-12 | Should |

### 6.2 Functional Requirements Tambahan untuk Implementasi Teknis

| ID | Requirement | Modul | Prioritas |
|---|---|---|---|
| PRD-FR-01 | Sistem menyediakan pencarian cepat (termasuk *full-text search* nama pegawai), filter, pengurutan, dan detail data pegawai sesuai hak akses. | Pegawai | Must |
| PRD-FR-02 | Sistem menyimpan riwayat penempatan unit, atasan langsung, dan kategori pegawai berdasarkan tanggal efektif mulai dan akhir (`effective_from`, `effective_until`). | Organisasi/Pegawai | Must |
| PRD-FR-03 | Sistem mendukung state machine pengajuan (`diajukan`, `disetujui`, `ditolak`, `dibatalkan`) dan mengizinkan pengajuan ulang sebagai baris baru setelah ditolak/dibatalkan. | Workflow | Must |
| PRD-FR-04 | Sistem mencatat status lama, status baru, actor, waktu, IP address, dan alasan untuk setiap perubahan status ke tabel `activity_logs`. | Audit | Must |
| PRD-FR-05 | Sistem mencegah pemohon menyetujui atau memvalidasi pengajuannya sendiri baik di level aplikasi maupun database. | Workflow/RBAC | Must |
| PRD-FR-06 | Sistem menggunakan transaksi InnoDB dan *pessimistic locking* untuk keputusan pengajuan, validasi overlap tanggal/jam, serta pembaruan saldo cuti. | Workflow | Must |
| PRD-FR-07 | Sistem menggunakan kolom `version` (*optimistic locking*) pada seluruh tabel pengajuan dan persetujuan yang berpotensi diproses bersamaan. | Workflow | Must |
| PRD-FR-08 | Sistem menerapkan *soft delete* (`deleted_at`) dan status nonaktif/arsip sehingga tidak ada penghapusan permanen atas data historis kepegawaian. | Data Management | Must |
| PRD-FR-09 | Sistem memfasilitasi revisi BKD/LKD yang sudah `final` dengan membuat versi baru (`version + 1`), mewajibkan alasan revisi, dan memindahkan penanda `is_current`. | Kinerja | Must |
| PRD-FR-10 | Sistem menyajikan 6 laporan SDM (`RPT-01` s.d. `RPT-06`) dengan batasan tampilan agregat bagi Wakil Rektor dan perlindungan kolom sensitif. | Reporting | Should |
| PRD-FR-11 | Sistem mencatat setiap percobaan akses atau aksi yang ditolak karena tidak berwenang (`ACCESS_DENIED`) ke dalam `activity_logs`. | Security/Audit | Must |
| PRD-FR-12 | Sistem menyediakan *scheduled job* untuk mendeteksi pegawai yang mendekati usia pensiun dan membuat usulan otomatis tanpa duplikasi. | Pensiun | Must |
| PRD-FR-13 | Sistem dapat membuat, memublikasikan, dan mempertahankan versi historis jadwal kerja berdasarkan unit, kategori, dan masa berlaku. | Jadwal Kerja | Must |
| PRD-FR-14 | Sistem memvalidasi kelengkapan pengajuan aktif pegawai sebelum mengizinkan penyelesaian proses pensiun (`EX-09`). | Pensiun | Must |
| PRD-FR-15 | Sistem dapat mencatat proses dan hasil sertifikasi Dosen beserta nomor unik dan lokasi file sertifikat. | Sertifikasi | Should |
| PRD-FR-16 | Sistem dapat mencatat pelatihan Dosen/Tendik, durasi jam, sertifikat, dan menautkannya ke proses pensiun untuk pelatihan pra-pensiun. | Pelatihan | Must |

---

## 7. Business Rules

Business rule berikut wajib ditegakkan melalui kombinasi validasi aplikasi Laravel, constraint MySQL 8.4, trigger, transaksi InnoDB, dan otorisasi.

| ID | Aturan Bisnis (BRD) | Lapisan Penegakan di MySQL 8.4 & Aplikasi |
|---|---|---|
| RULE-01 | Pelamar hanya dapat diproses ke tahap berikutnya bila memenuhi kualifikasi minimum lowongan dan kriteria penilaian (termasuk nilai AIK). Satu pelamar tidak boleh mendaftar dua kali pada lowongan yang sama. | Validasi service aplikasi + `UNIQUE (vacancy_id, applicant_id)` pada `applications` + `UNIQUE (email)` pada `applicants`. |
| RULE-02 | Pegawai aktif hanya dapat dibuat dari kandidat berstatus diterima. Setiap pegawai memiliki satu kategori (`DOSEN` atau `TENDIK`) dan ID/NIP yang unik. | Validasi onboarding (cek `applications.status = 'diterima'`) + `UNIQUE (nip)`, `UNIQUE (application_id)`, dan `CHECK (category IN ('DOSEN','TENDIK'))` pada `employees`. |
| RULE-03 | Pengajuan cuti hanya dapat disetujui bila saldo cuti mencukupi dan tanggalnya tidak bertumpuk dengan cuti disetujui milik pegawai yang sama. | Transaksi InnoDB + *pessimistic lock* pada `employees` dan `leave_balances` + query cek overlap tanggal + `CHECK (used_days <= quota_days)`. |
| RULE-04 | Pengajuan lembur harus memiliki jam selesai setelah jam mulai dan disetujui atasan. Hanya lembur yang disetujui dihitung untuk kompensasi. | `CHECK (end_time > start_time)` pada `overtime_requests` + *pessimistic lock* cek overlap jam lembur + filter `status = 'disetujui'` pada query laporan. |
| RULE-05 | Kenaikan jabatan fungsional Dosen mengikuti urutan jenjang. Loncat jenjang hanya melalui jalur percepatan dengan persetujuan yang tercatat. Riwayat jabatan lama tidak dihapus. | Validasi urutan jenjang di aplikasi + pengecekan `is_accelerated = 1` + penyimpanan historis di `academic_rank_histories`. |
| RULE-06 | Kenaikan pangkat/jabatan Tendik hanya diproses bila syarat BSDM terpenuhi (mis. masa kerja dan hasil SKP). Riwayat sebelumnya tetap disimpan. | Validasi syarat masa kerja & SKP di aplikasi + penyimpanan historis di `staff_rank_histories`. |
| RULE-07 | Setiap Dosen memiliki satu BKD/LKD aktif per semester. BKD/LKD final setelah divalidasi atasan; perubahan setelah final hanya lewat revisi sebagai versi baru dengan alasan. | `UNIQUE (employee_id, academic_year, semester, version)` + *virtual generated column* untuk `UNIQUE` satu baris `is_current = 1` per semester + wajib `revision_reason` bila `version > 1`. |
| RULE-08 | Setiap pegawai memiliki satu SKP aktif per periode evaluasi. Skor akhir dihitung setelah penilai menyetujui realisasi. | *Conditional unique constraint* via *virtual generated column* pada `performance_targets` (mengizinkan buat baru jika status sebelumnya `ditolak`) + kalkulasi `final_score` saat `status = 'disetujui'`. |
| RULE-09 | Tunjangan hanya dapat diajukan pegawai yang memenuhi syarat jenis tunjangannya (mis. tunjangan doktor mensyaratkan ijazah S3 tercatat) dan tidak boleh ganda untuk periode yang sama. | Validasi syarat S3 di aplikasi + *conditional unique constraint* via *virtual generated column* pada `allowance_requests(employee_id, allowance_type_id, period_label)` untuk status aktif. |
| RULE-10 | Proses pensiun diusulkan sistem saat pegawai mendekati usia pensiun. Setelah pensiun, status pegawai nonaktif/pensiun dan data historis dipertahankan. | Scheduled job Laravel + *conditional unique constraint* pada `retirements` + pengecekan `EX-09` sebelum update `employees.employment_status`. |
| RULE-11 | Setiap pengajuan mengikuti alur status tetap: `diajukan` lalu `disetujui` atau `ditolak`. Pemohon boleh membatalkan (`dibatalkan`) selama belum disetujui. Status final tidak dikembalikan; koreksi lewat pembatalan atau pengajuan ulang sebagai riwayat baru. | State machine di aplikasi + *conditional unique index* di MySQL agar baris berstatus `dibatalkan`/`ditolak` tidak memblokir insert pengajuan ulang. |
| RULE-12 | Setiap perubahan status menyimpan status lama, status baru, actor, waktu, dan alasan (wajib untuk penolakan dan pembatalan). | Insert wajib ke `activity_logs` dalam transaksi yang sama + validasi `decision_note` / `reason` wajib diisi saat status `ditolak` atau `dibatalkan`. |
| RULE-13 | Data kepegawaian tidak dihapus permanen; data yang tidak digunakan dinonaktifkan agar jejak audit tetap ada. | Kolom `deleted_at` (Laravel `SoftDeletes`), kolom `is_active` / `employment_status`, serta `ON DELETE RESTRICT` pada seluruh Foreign Key. |
| RULE-14 | Pemohon tidak boleh menyetujui pengajuannya sendiri; persetujuan hanya oleh atasan langsung atau role berwenang. | Laravel Policy + MySQL Trigger (`BEFORE INSERT` & `BEFORE UPDATE`) yang menolak `employee_id = approver_id` (serta `validator_id`, `appraiser_id`, `supervisor_id`). |
| RULE-15 | Akses data mengikuti role: pegawai hanya melihat data miliknya, staf bidang hanya data sesuai bidangnya, dan data sensitif (BPJS, tunjangan, data pribadi) dibatasi. | Middleware & Policy RBAC + query scoping per `unit_id` + pembatasan atribut sensitif pada API/View dan akun DB. |
| RULE-16 | Satu pegawai memiliki paling banyak satu kepesertaan aktif per jenis BPJS, dan nomor kepesertaan unik. | `UNIQUE (membership_number)` + *virtual generated column* `active_employee_id` dengan `UNIQUE (active_employee_id, bpjs_type)`. |
| RULE-17 | Dua pengguna yang memproses pengajuan yang sama hampir bersamaan tidak boleh menghasilkan status ganda atau saldo yang salah. | Transaksi InnoDB + *optimistic locking* (`WHERE version = :current_version`) pada semua tabel persetujuan + `SELECT ... FOR UPDATE` pada saldo cuti. |

---

## 8. Skenario Pengecualian dan Penanganan (Exceptions)

| ID | Kondisi Pengecualian | Penanganan Sistem yang Diharapkan |
|---|---|---|
| EX-01 | Pengajuan cuti melebihi saldo. | Sistem menolak pengajuan dan menampilkan sisa saldo cuti saat ini (`RULE-03`). |
| EX-02 | Pengajuan cuti bertumpuk dengan cuti disetujui lainnya. | Sistem mengunci baris pegawai, menolak pengajuan, dan menunjukkan rentang tanggal cuti yang bentrok (`RULE-03`). |
| EX-03 | Pemohon mencoba menyetujui pengajuannya sendiri. | Sistem menolak aksi (baik di Policy maupun Trigger DB) dan mencatat percobaan pada `activity_logs` dengan `action = 'ACCESS_DENIED'` (`RULE-14`, `RULE-12`). |
| EX-04 | Dua approver memproses pengajuan yang sama hampir bersamaan. | Hanya satu transaksi yang berhasil memperbarui baris dan menaikkan `version`; transaksi kedua gagal karena `version` tidak cocok, melakukan rollback, dan menampilkan pesan konflik beserta status terbaru (`RULE-17`). |
| EX-05 | Atasan salah menyetujui atau menolak pengajuan. | Status final (`disetujui`/`ditolak`) tidak dapat diedit kembali; koreksi dilakukan lewat pembatalan (sesuai prosedur) atau pengajuan ulang sebagai baris riwayat baru (`RULE-11`, `RULE-12`). |
| EX-06 | Dosen mengajukan kenaikan jabatan melompati jenjang. | Sistem menolak pengajuan, kecuali ditandai sebagai jalur percepatan (`is_accelerated = 1`) dengan persetujuan pejabat berwenang yang tercatat (`RULE-05`). |
| EX-07 | Pengajuan lembur dengan jam selesai lebih awal/sama dengan jam mulai atau waktu bertumpuk. | Sistem menolak input karena melanggar `CHECK (end_time > start_time)` atau terdeteksi overlap jam lembur pada tanggal yang sama (`RULE-04`). |
| EX-08 | Pegawai pindah unit atau berpindah kategori (Tendik menjadi Dosen). | Baris penempatan lama di `employee_assignments` ditutup (`effective_until` diisi), baris penempatan baru dibuat mulai `effective_from`, dan kondisi terkini di `employees` diperbarui (`RULE-02`, `RULE-13`; perpindahan kategori menunggu Q-05). |
| EX-09 | Pegawai memasuki masa pensiun saat masih ada pengajuan aktif. | Sistem memblokir penyelesaian pensiun (`status = 'selesai'`) dan menampilkan daftar pengajuan berstatus `diajukan`/`proses` agar diselesaikan atau ditutup terlebih dahulu (`RULE-10`, `RULE-11`). |
| EX-10 | Pelamar mendaftar ganda atau data pelamar duplikat. | Sistem menolak pendaftaran kedua berdasarkan `UNIQUE (email)` atau `UNIQUE (vacancy_id, applicant_id)` dan mengarahkan ke pendaftaran yang sudah ada (`RULE-01`). |
| EX-11 | Nomor BPJS duplikat atau data tidak lengkap. | Sistem menolak penyimpanan karena `UNIQUE (membership_number)` atau validasi atribut wajib, lalu menandai data pegawai untuk dilengkapi (`RULE-16`). |
| EX-12 | Pegawai berhenti/mengundurkan diri sebelum usia pensiun. | Status `employees.employment_status` diubah menjadi `nonaktif`, `end_date` diisi, penempatan aktif ditutup, dan seluruh data historis dipertahankan (`RULE-13`). |

---

## 9. Data, Retensi Objek Bisnis, dan Rancangan Database

### 9.1 Prinsip Skema Data

1. Nama tabel dan kolom menggunakan `snake_case` berbahasa Inggris; nama tabel menggunakan bentuk jamak.
2. Primary key menggunakan `BIGINT UNSIGNED AUTO_INCREMENT`.
3. Foreign key menggunakan tipe `BIGINT UNSIGNED` yang persis sama dengan primary key referensinya dan wajib memakai `ON DELETE RESTRICT` secara default.
4. Setiap tabel bisnis memiliki kolom teknis `created_at DATETIME NOT NULL`, `updated_at DATETIME NOT NULL`, dan `deleted_at DATETIME NULL` (untuk *soft delete* sesuai `RULE-13`), kecuali `activity_logs` yang hanya memiliki `created_at DATETIME NOT NULL` karena bersifat *append-only*.
5. Nilai uang dan angka presisi menggunakan `DECIMAL(p,s)`, bukan `FLOAT` atau `DOUBLE`.
6. Berkas dokumen (CV, sertifikat) disimpan di storage privat aplikasi; database hanya menyimpan lokasi path (`VARCHAR(255)`).
7. Pelaku transaksi mengikuti konvensi BRD: `approver_id`, `appraiser_id`, `validator_id`, dan `supervisor_id` merujuk ke `employees.id`; sedangkan `created_by`, `assessed_by`, `processed_by`, dan `actor_id` merujuk ke `users.id`.

### 9.2 Pemetaan 20 Business Objects ke 29 Entitas & Kebijakan Retensi BRD

> **Catatan Penamaan Tabel Unit:** Pada BRD Bagian 9.2 tabel organisasi dinamai `departments`, sedangkan pada Bagian 9.4.1 tertulis `unit`. PRD ini menetapkan **`departments`** sebagai nama tabel fisik kanonis di MySQL dan mempertahankan nama foreign key `unit_id` (merujuk ke `departments.id`) agar selaras dengan seluruh atribut di BRD.

| No | Business Object (BRD) | Tabel MySQL (29 Entitas) | Kebijakan Retensi (BRD Bagian 9) |
|---:|---|---|---|
| 1 | Department (Unit) | `departments` | **Permanen** (tidak dihapus, hanya dinonaktifkan via `is_active`). |
| 2 | Employee (Pegawai) | `employees`, `employee_assignments` | **Permanen, dinonaktifkan** (`employment_status` menjadi `nonaktif`/`pensiun`). |
| 3 | User & Role | `users`, `roles`, `user_roles` | **Dinonaktifkan** (`users.is_active = 0`). |
| 4 | Vacancy (Lowongan) | `vacancies` | **Diusulkan 5 tahun** (menunggu konfirmasi Q-03). |
| 5 | Applicant & Selection | `applicants`, `applications`, `selection_stages` | **Pelamar ditolak: diusulkan 2 tahun** (menunggu konfirmasi Q-09); pelamar diterima: permanen bersama riwayat pegawai. |
| 6 | Leave Type & Balance | `leave_types`, `leave_balances` | **Permanen**. |
| 7 | Leave Request | `leave_requests` | **Permanen**. |
| 8 | Overtime Request | `overtime_requests` | **Diusulkan minimal 5 tahun** (menunggu konfirmasi Q-03). |
| 9 | Work Schedule | `work_schedules`, `work_schedule_days` | **Permanen (versi historis)**. |
| 10 | Academic Rank History | `academic_rank_histories` | **Permanen**. |
| 11 | Staff Rank History | `staff_rank_histories` | **Permanen**. |
| 12 | Study Leave & Scholarship | `study_leaves` | **Permanen**. |
| 13 | Certification | `certifications` | **Permanen**. |
| 14 | Training | `trainings` | **Permanen**. |
| 15 | Workload Report (BKD/LKD) | `workload_reports` | **Permanen** (seluruh versi disimpan). |
| 16 | Performance Target (SKP) | `performance_targets`, `performance_items` | **Permanen**. |
| 17 | BPJS Membership | `bpjs_memberships` | **Permanen, diarsipkan** (`archived_at` diisi saat `nonaktif`). |
| 18 | Allowance Request | `allowance_types`, `allowance_requests` | **Permanen**. |
| 19 | Retirement | `retirements` | **Permanen**. |
| 20 | Status History / Activity Log | `activity_logs` | **Permanen (append-only)** (kebijakan pengarsipan log menunggu Q-03). |

### 9.3 Data Dictionary Lengkap (29 Tabel MySQL 8.4)

Seluruh tabel di bawah (nomor 1 s.d. 28) secara implisit memiliki kolom `created_at DATETIME NOT NULL`, `updated_at DATETIME NOT NULL`, dan `deleted_at DATETIME NULL`. Tabel nomor 29 (`activity_logs`) hanya memiliki `created_at DATETIME NOT NULL`.

#### Kelompok A: Organisasi, Pegawai, dan Akses

**1. `departments`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `parent_id` | BIGINT UNSIGNED | Yes | NULL | FK ke `departments.id`; mendukung struktur hierarki unit induk-anak pada BRD 9.3. |
| `name` | VARCHAR(150) | No | - | Nama unit/bidang. |
| `unit_type` | VARCHAR(20) | No | - | `CHECK (unit_type IN ('biro', 'bidang', 'urusan', 'unit'))`. |
| `is_active` | TINYINT(1) | No | 1 | `1` = aktif, `0` = nonaktif (`RULE-13`). |

**2. `employees`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `nip` | VARCHAR(30) | No | - | `UNIQUE`; ID/NIP unik pegawai (`RULE-02`). |
| `nidn` | VARCHAR(20) | Yes | NULL | `UNIQUE`; khusus Dosen (nullable untuk Tendik). |
| `full_name` | VARCHAR(150) | No | - | Nama lengkap; didukung `FULLTEXT` index untuk pencarian cepat. |
| `category` | VARCHAR(10) | No | - | `CHECK (category IN ('DOSEN', 'TENDIK'))` (`RULE-02`). |
| `gender` | CHAR(1) | No | - | `CHECK (gender IN ('L', 'P'))`. |
| `birth_place` | VARCHAR(100) | No | - | Tempat lahir. |
| `birth_date` | DATE | No | - | Tanggal lahir; dasar penghitungan usia pensiun. |
| `email` | VARCHAR(150) | No | - | `UNIQUE`; email kerja pegawai. |
| `phone` | VARCHAR(20) | Yes | NULL | Nomor telepon. |
| `address` | TEXT | Yes | NULL | Alamat domisili (data sensitif). |
| `highest_education` | VARCHAR(10) | No | - | Pendidikan terakhir, mis. `S1`, `S2`, `S3` (syarat `RULE-09`). |
| `unit_id` | BIGINT UNSIGNED | No | - | FK ke `departments.id`; unit kerja saat ini. |
| `supervisor_id` | BIGINT UNSIGNED | Yes | NULL | FK ke `employees.id`; atasan langsung (`supervisor_id <> id`). |
| `application_id` | BIGINT UNSIGNED | Yes | NULL | FK ke `applications.id`; `UNIQUE` (1 pendaftaran diterima = maks 1 pegawai). |
| `employment_status` | VARCHAR(10) | No | `'aktif'` | `CHECK (employment_status IN ('aktif', 'nonaktif', 'pensiun'))`. |
| `join_date` | DATE | No | - | Tanggal mulai bekerja. |
| `end_date` | DATE | Yes | NULL | Tanggal berhenti/pensiun (`CHECK (end_date IS NULL OR end_date >= join_date)`). |

**3. `employee_assignments`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `employee_id` | BIGINT UNSIGNED | No | - | FK ke `employees.id`. |
| `unit_id` | BIGINT UNSIGNED | No | - | FK ke `departments.id`; unit pada masa penempatan. |
| `supervisor_id` | BIGINT UNSIGNED | Yes | NULL | FK ke `employees.id`; atasan pada masa penempatan (`supervisor_id <> employee_id`). |
| `category` | VARCHAR(10) | No | - | `CHECK (category IN ('DOSEN', 'TENDIK'))`. |
| `effective_from` | DATE | No | - | Tanggal mulai berlakunya penempatan/kategori (`EX-08`). |
| `effective_until` | DATE | Yes | NULL | Tanggal berakhir (`NULL` bila masih aktif; `effective_until >= effective_from`). |

**4. `users`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `employee_id` | BIGINT UNSIGNED | Yes | NULL | FK ke `employees.id`; `UNIQUE` (1 pegawai maks 1 akun; `NULL` untuk admin sistem). |
| `username` | VARCHAR(50) | No | - | `UNIQUE`; nama login. |
| `password_hash` | VARCHAR(255) | No | - | Hash kata sandi (bcrypt/argon2id). |
| `is_active` | TINYINT(1) | No | 1 | Default `1`; akun dinonaktifkan, tidak dihapus. |
| `last_login_at` | DATETIME | Yes | NULL | Waktu login terakhir. |

**5. `roles`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `name` | VARCHAR(100) | No | - | `UNIQUE`; nama role. |
| `description` | TEXT | Yes | NULL | Cakupan akses role. |

**6. `user_roles`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `user_id` | BIGINT UNSIGNED | No | - | FK ke `users.id`. |
| `role_id` | BIGINT UNSIGNED | No | - | FK ke `roles.id`. |
| `unit_id` | BIGINT UNSIGNED | Yes | NULL | FK ke `departments.id`; `NULL` bila berlaku lintas unit. |

> **Unique Constraint `user_roles`:** Menggunakan functional index `UNIQUE KEY uq_user_role_unit (user_id, role_id, (IFNULL(unit_id, 0)))` agar penugasan role lintas unit (`unit_id IS NULL`) tidak dapat diduplikasi.

#### Kelompok B: Rekrutmen dan Onboarding

**7. `vacancies`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `unit_id` | BIGINT UNSIGNED | No | - | FK ke `departments.id`; unit pemilik kebutuhan. |
| `title` | VARCHAR(150) | No | - | Judul lowongan. |
| `category` | VARCHAR(10) | No | - | `CHECK (category IN ('DOSEN', 'TENDIK'))`. |
| `description` | TEXT | Yes | NULL | Uraian posisi. |
| `min_qualification` | TEXT | No | - | Kualifikasi minimum (`RULE-01`). |
| `quota` | SMALLINT UNSIGNED | No | - | Jumlah formasi (`CHECK (quota > 0)`). |
| `open_date` | DATE | No | - | Tanggal buka pendaftaran. |
| `close_date` | DATE | No | - | Tanggal tutup (`CHECK (close_date >= open_date)`). |
| `status` | VARCHAR(10) | No | `'draf'` | `CHECK (status IN ('draf', 'dibuka', 'ditutup'))`. |
| `created_by` | BIGINT UNSIGNED | No | - | FK ke `users.id`; pembuat lowongan. |

**8. `applicants`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `full_name` | VARCHAR(150) | No | - | Nama lengkap pelamar. |
| `email` | VARCHAR(150) | No | - | `UNIQUE`; mencegah data pelamar ganda (`EX-10`). |
| `phone` | VARCHAR(20) | No | - | Nomor telepon. |
| `birth_date` | DATE | No | - | Tanggal lahir. |
| `highest_education` | VARCHAR(10) | No | - | Pendidikan terakhir. |
| `cv_path` | VARCHAR(255) | Yes | NULL | Lokasi berkas CV di storage privat. |

**9. `applications`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `vacancy_id` | BIGINT UNSIGNED | No | - | FK ke `vacancies.id`. |
| `applicant_id` | BIGINT UNSIGNED | No | - | FK ke `applicants.id`. |
| `applied_at` | DATETIME | No | - | Waktu mendaftar. |
| `aik_score` | DECIMAL(5,2) | Yes | NULL | Nilai AIK (`CHECK (aik_score IS NULL OR (aik_score >= 0 AND aik_score <= 100))`). |
| `status` | VARCHAR(10) | No | `'diajukan'` | `CHECK (status IN ('diajukan', 'proses', 'diterima', 'ditolak'))`. |
| `decided_at` | DATETIME | Yes | NULL | Waktu keputusan akhir. |

> **Unique Constraint `applications`:** `UNIQUE KEY uq_application_vacancy_applicant (vacancy_id, applicant_id)` (`RULE-01`).

**10. `selection_stages`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `application_id` | BIGINT UNSIGNED | No | - | FK ke `applications.id`. |
| `stage_name` | VARCHAR(50) | No | - | Nama tahap, mis. administrasi, tes, wawancara. |
| `stage_order` | SMALLINT UNSIGNED | No | - | Urutan tahap (`CHECK (stage_order > 0)`). |
| `score` | DECIMAL(5,2) | Yes | NULL | Nilai tahap (`CHECK (score IS NULL OR (score >= 0 AND score <= 100))`). |
| `result` | VARCHAR(10) | No | `'menunggu'` | `CHECK (result IN ('menunggu', 'lolos', 'gugur'))`. |
| `assessed_by` | BIGINT UNSIGNED | Yes | NULL | FK ke `users.id`; penilai. |
| `assessed_at` | DATETIME | Yes | NULL | Waktu penilaian. |
| `notes` | TEXT | Yes | NULL | Catatan penilai. |

> **Unique Constraint `selection_stages`:** `UNIQUE KEY uq_stage_application_order (application_id, stage_order)`.

#### Kelompok C: Cuti, Lembur, dan Jadwal Kerja

**11. `leave_types`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `type` | VARCHAR(20) | No | - | `UNIQUE`; kode jenis cuti. |
| `name` | VARCHAR(100) | No | - | Nama jenis cuti. |
| `annual_quota` | SMALLINT UNSIGNED | No | - | Jatah hari dasar per periode saldo (menunggu Q-01). |
| `applies_to` | VARCHAR(10) | No | `'SEMUA'` | `CHECK (applies_to IN ('SEMUA', 'DOSEN', 'TENDIK'))`. |
| `is_active` | TINYINT(1) | No | 1 | Default `1`. |

**12. `leave_balances`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `employee_id` | BIGINT UNSIGNED | No | - | FK ke `employees.id`. |
| `leave_type_id` | BIGINT UNSIGNED | No | - | FK ke `leave_types.id`. |
| `year` | SMALLINT UNSIGNED | No | - | Tahun saldo (kalender/akademik menunggu Q-01). |
| `quota_days` | SMALLINT UNSIGNED | No | - | Jatah hari cuti. |
| `used_days` | SMALLINT UNSIGNED | No | 0 | Hari terpakai; `CHECK (used_days <= quota_days)` (`RULE-03`). |
| `version` | INT UNSIGNED | No | 1 | Penomoran versi untuk *optimistic locking* (`RULE-17`). |

> **Unique Constraint `leave_balances`:** `UNIQUE KEY uq_leave_balance_emp_type_year (employee_id, leave_type_id, year)`.

**13. `leave_requests`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `employee_id` | BIGINT UNSIGNED | No | - | FK ke `employees.id`; pemohon. |
| `leave_type_id` | BIGINT UNSIGNED | No | - | FK ke `leave_types.id`. |
| `start_date` | DATE | No | - | Tanggal mulai cuti. |
| `end_date` | DATE | No | - | Tanggal selesai (`CHECK (end_date >= start_date)`). |
| `total_days` | SMALLINT UNSIGNED | No | - | Jumlah hari cuti (`CHECK (total_days > 0)`). |
| `reason` | TEXT | No | - | Alasan pengajuan cuti. |
| `status` | VARCHAR(12) | No | `'diajukan'` | `CHECK (status IN ('diajukan', 'disetujui', 'ditolak', 'dibatalkan'))`. |
| `approver_id` | BIGINT UNSIGNED | No | - | FK ke `employees.id`; atasan berwenang (`approver_id <> employee_id`). |
| `submitted_at` | DATETIME | No | - | Waktu pengajuan. |
| `decided_at` | DATETIME | Yes | NULL | Waktu keputusan. |
| `decision_note` | TEXT | Yes | NULL | Wajib diisi bila `status IN ('ditolak', 'dibatalkan')` (`RULE-12`). |
| `version` | INT UNSIGNED | No | 1 | Penomoran versi untuk *optimistic locking* (`RULE-17`). |

**14. `overtime_requests`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `employee_id` | BIGINT UNSIGNED | No | - | FK ke `employees.id`; pemohon. |
| `work_date` | DATE | No | - | Tanggal lembur (hari kalender yang sama). |
| `start_time` | TIME | No | - | Jam mulai lembur. |
| `end_time` | TIME | No | - | Jam selesai (`CHECK (end_time > start_time)` sesuai `RULE-04`). |
| `total_hours` | DECIMAL(4,2) | No | - | Durasi lembur dalam jam (`CHECK (total_hours > 0)`). |
| `reason` | TEXT | No | - | Uraian pekerjaan lembur. |
| `status` | VARCHAR(12) | No | `'diajukan'` | `CHECK (status IN ('diajukan', 'disetujui', 'ditolak', 'dibatalkan'))`. |
| `approver_id` | BIGINT UNSIGNED | No | - | FK ke `employees.id`; atasan berwenang (`approver_id <> employee_id`). |
| `submitted_at` | DATETIME | No | - | Waktu pengajuan. |
| `decided_at` | DATETIME | Yes | NULL | Waktu keputusan. |
| `decision_note` | TEXT | Yes | NULL | Wajib diisi bila `status IN ('ditolak', 'dibatalkan')`. |
| `version` | INT UNSIGNED | No | 1 | Penomoran versi untuk *optimistic locking* (`RULE-17`). |

**15. `work_schedules`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `name` | VARCHAR(100) | No | - | Nama jadwal kerja. |
| `unit_id` | BIGINT UNSIGNED | Yes | NULL | FK ke `departments.id`; `NULL` bila berlaku umum. |
| `category` | VARCHAR(10) | Yes | NULL | `CHECK (category IS NULL OR category IN ('DOSEN', 'TENDIK'))`. |
| `valid_from` | DATE | No | - | Tanggal mulai berlaku. |
| `valid_until` | DATE | Yes | NULL | Tanggal akhir berlaku (`CHECK (valid_until IS NULL OR valid_until >= valid_from)`). |
| `is_published` | TINYINT(1) | No | 0 | Default `0`. |
| `published_at` | DATETIME | Yes | NULL | Waktu dipublikasikan. |
| `created_by` | BIGINT UNSIGNED | No | - | FK ke `users.id`; pembuat jadwal. |

**16. `work_schedule_days`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `schedule_id` | BIGINT UNSIGNED | No | - | FK ke `work_schedules.id`. |
| `day_of_week` | TINYINT UNSIGNED | No | - | `CHECK (day_of_week BETWEEN 1 AND 7)` ($1=\text{Senin} \dots 7=\text{Minggu}$). |
| `start_time` | TIME | No | - | Jam masuk. |
| `end_time` | TIME | No | - | Jam pulang pada hari yang sama (`CHECK (end_time > start_time)`). |

> **Unique Constraint `work_schedule_days`:** `UNIQUE KEY uq_schedule_day (schedule_id, day_of_week)`.

#### Kelompok D: Karir dan Pengembangan

**17. `academic_rank_histories`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `employee_id` | BIGINT UNSIGNED | No | - | FK ke `employees.id`; harus kategori `DOSEN`. |
| `rank_name` | VARCHAR(30) | No | - | `CHECK (rank_name IN ('Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Guru Besar'))`. |
| `credit_points` | DECIMAL(8,2) | Yes | NULL | Angka kredit kumulatif. |
| `is_accelerated` | TINYINT(1) | No | 0 | `1` bila melalui jalur percepatan (`RULE-05`). |
| `status` | VARCHAR(12) | No | `'diajukan'` | `CHECK (status IN ('diajukan', 'disetujui', 'ditolak', 'dibatalkan'))`. |
| `submitted_at` | DATETIME | No | - | Waktu pengajuan. |
| `approver_id` | BIGINT UNSIGNED | Yes | NULL | FK ke `employees.id`; pemberi persetujuan (menunggu Q-06; `approver_id <> employee_id`). |
| `decided_at` | DATETIME | Yes | NULL | Waktu keputusan. |
| `effective_date` | DATE | Yes | NULL | Tanggal berlaku jabatan. |
| `sk_number` | VARCHAR(100) | Yes | NULL | Nomor SK jabatan fungsional. |
| `sk_date` | DATE | Yes | NULL | Tanggal SK. |
| `decision_note` | TEXT | Yes | NULL | Wajib diisi bila `status IN ('ditolak', 'dibatalkan')`. |
| `version` | INT UNSIGNED | No | 1 | Penomoran versi untuk *optimistic locking* (`RULE-17`). |

**18. `staff_rank_histories`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `employee_id` | BIGINT UNSIGNED | No | - | FK ke `employees.id`; harus kategori `TENDIK`. |
| `rank_grade` | VARCHAR(20) | No | - | Pangkat/golongan. |
| `functional_position` | VARCHAR(100) | Yes | NULL | Jabatan fungsional tendik (pustakawan, laboran, programer, dll.). |
| `status` | VARCHAR(12) | No | `'diajukan'` | `CHECK (status IN ('diajukan', 'disetujui', 'ditolak', 'dibatalkan'))`. |
| `submitted_at` | DATETIME | No | - | Waktu pengajuan. |
| `approver_id` | BIGINT UNSIGNED | Yes | NULL | FK ke `employees.id`; pejabat berwenang (`approver_id <> employee_id`). |
| `decided_at` | DATETIME | Yes | NULL | Waktu keputusan. |
| `effective_date` | DATE | Yes | NULL | Tanggal berlaku kepangkatan/jabatan. |
| `sk_number` | VARCHAR(100) | Yes | NULL | Nomor SK. |
| `sk_date` | DATE | Yes | NULL | Tanggal SK. |
| `decision_note` | TEXT | Yes | NULL | Wajib diisi bila `status IN ('ditolak', 'dibatalkan')`. |
| `version` | INT UNSIGNED | No | 1 | Penomoran versi untuk *optimistic locking* (`RULE-17`). |

**19. `study_leaves`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `employee_id` | BIGINT UNSIGNED | No | - | FK ke `employees.id`; Dosen. |
| `degree_level` | VARCHAR(5) | No | `'S3'` | Jenjang studi lanjut; default `'S3'`. |
| `university` | VARCHAR(150) | No | - | Perguruan tinggi tujuan. |
| `study_program` | VARCHAR(150) | No | - | Program studi. |
| `start_date` | DATE | No | - | Tanggal mulai studi. |
| `expected_end_date` | DATE | Yes | NULL | Perkiraan selesai (`expected_end_date >= start_date`). |
| `actual_end_date` | DATE | Yes | NULL | Tanggal selesai sebenarnya (`actual_end_date >= start_date`). |
| `status` | VARCHAR(15) | No | `'direncanakan'` | `CHECK (status IN ('direncanakan', 'berjalan', 'selesai', 'berhenti'))`. |
| `scholarship_name` | VARCHAR(150) | Yes | NULL | Nama beasiswa. |
| `scholarship_status` | VARCHAR(10) | Yes | `'belum'` | `CHECK (scholarship_status IS NULL OR scholarship_status IN ('belum', 'proses', 'disetujui', 'ditolak'))`. |
| `mentoring_notes` | TEXT | Yes | NULL | Catatan pendampingan beasiswa. |

**20. `certifications`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `employee_id` | BIGINT UNSIGNED | No | - | FK ke `employees.id`; Dosen. |
| `cert_type` | VARCHAR(50) | No | - | Jenis sertifikasi. |
| `cert_number` | VARCHAR(50) | Yes | NULL | `UNIQUE`; nomor sertifikat (nullable saat masih proses). |
| `submitted_date` | DATE | No | - | Tanggal pengajuan. |
| `result_date` | DATE | Yes | NULL | Tanggal hasil (`CHECK (result_date IS NULL OR result_date >= submitted_date)`). |
| `status` | VARCHAR(12) | No | `'diajukan'` | `CHECK (status IN ('diajukan', 'proses', 'lulus', 'tidak_lulus'))`. |
| `certificate_path` | VARCHAR(255) | Yes | NULL | Lokasi berkas sertifikat di storage privat. |

**21. `trainings`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `employee_id` | BIGINT UNSIGNED | No | - | FK ke `employees.id`; peserta pelatihan. |
| `retirement_id` | BIGINT UNSIGNED | Yes | NULL | FK ke `retirements.id`; diisi bila pelatihan pra-pensiun terkait proses pensiun. |
| `title` | VARCHAR(200) | No | - | Judul pelatihan. |
| `training_type` | VARCHAR(15) | No | - | `CHECK (training_type IN ('pra_pensiun', 'it', 'bahasa_asing', 'lainnya'))`. |
| `organizer` | VARCHAR(150) | Yes | NULL | Penyelenggara pelatihan. |
| `start_date` | DATE | No | - | Tanggal mulai. |
| `end_date` | DATE | No | - | Tanggal selesai (`CHECK (end_date >= start_date)`). |
| `duration_hours` | SMALLINT UNSIGNED | Yes | NULL | Durasi dalam jam (`CHECK (duration_hours IS NULL OR duration_hours > 0)`). |
| `status` | VARCHAR(12) | No | `'dijadwalkan'` | `CHECK (status IN ('dijadwalkan', 'selesai', 'batal'))`. |
| `certificate_number` | VARCHAR(100) | Yes | NULL | Nomor sertifikat pelatihan. |
| `certificate_path` | VARCHAR(255) | Yes | NULL | Lokasi berkas sertifikat di storage privat. |

#### Kelompok E: Kinerja

**22. `workload_reports`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `employee_id` | BIGINT UNSIGNED | No | - | FK ke `employees.id`; Dosen. |
| `academic_year` | VARCHAR(9) | No | - | Tahun akademik, mis. `2026/2027`. |
| `semester` | VARCHAR(6) | No | - | `CHECK (semester IN ('ganjil', 'genap'))`. |
| `teaching_credit` | DECIMAL(5,2) | No | 0.00 | Beban pendidikan/pengajaran ($\ge 0$). |
| `research_credit` | DECIMAL(5,2) | No | 0.00 | Beban penelitian ($\ge 0$). |
| `service_credit` | DECIMAL(5,2) | No | 0.00 | Beban pengabdian ($\ge 0$). |
| `support_credit` | DECIMAL(5,2) | No | 0.00 | Beban penunjang ($\ge 0$). |
| `total_credit` | DECIMAL(6,2) | No | - | `GENERATED ALWAYS AS (teaching_credit + research_credit + service_credit + support_credit) STORED` (atau via `CHECK` penjumlahan). |
| `status` | VARCHAR(10) | No | `'draf'` | `CHECK (status IN ('draf', 'diajukan', 'final'))`. |
| `validator_id` | BIGINT UNSIGNED | Yes | NULL | FK ke `employees.id`; atasan validator (`validator_id <> employee_id`). |
| `validated_at` | DATETIME | Yes | NULL | Waktu validasi. |
| `version` | SMALLINT UNSIGNED | No | 1 | Versi laporan (`RULE-07`). |
| `is_current` | TINYINT(1) | No | 1 | Penanda versi yang berlaku saat ini (`1` = berlaku, `0` = historis). |
| `revision_reason` | TEXT | Yes | NULL | Wajib diisi bila `version > 1` (`CHECK (version = 1 OR revision_reason IS NOT NULL)`). |

> **Unique Constraint `workload_reports`:** `UNIQUE KEY uq_workload_version (employee_id, academic_year, semester, version)` serta *virtual generated column* `current_employee_id` untuk menjamin hanya ada satu baris `is_current = 1` yang belum di-*soft-delete* per Dosen per semester (lihat Bagian 10.3).

**23. `performance_targets`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `employee_id` | BIGINT UNSIGNED | No | - | FK ke `employees.id`; Dosen/Tendik. |
| `period_label` | VARCHAR(20) | No | - | Label periode evaluasi. |
| `period_start` | DATE | No | - | Awal periode. |
| `period_end` | DATE | No | - | Akhir periode (`CHECK (period_end >= period_start)`). |
| `appraiser_id` | BIGINT UNSIGNED | Yes | NULL | FK ke `employees.id`; atasan penilai (`appraiser_id <> employee_id`). |
| `status` | VARCHAR(10) | No | `'draf'` | `CHECK (status IN ('draf', 'diajukan', 'disetujui', 'ditolak'))`. |
| `final_score` | DECIMAL(5,2) | Yes | NULL | Skor akhir; hanya dihitung setelah `status = 'disetujui'` (`RULE-08`). |
| `rating` | VARCHAR(30) | Yes | NULL | Predikat kinerja. |
| `approved_at` | DATETIME | Yes | NULL | Waktu persetujuan penilai. |
| `version` | INT UNSIGNED | No | 1 | Penomoran versi untuk *optimistic locking* (`RULE-17`). |

> **Unique Constraint `performance_targets`:** Menggunakan *virtual generated column* agar satu pegawai hanya memiliki satu SKP aktif (`status <> 'ditolak'` dan `deleted_at IS NULL`) per `period_label`, sehingga bila SKP ditolak tetap dapat diajukan ulang sebagai baris baru (`RULE-08`, `RULE-11`).

**24. `performance_items`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `target_id` | BIGINT UNSIGNED | No | - | FK ke `performance_targets.id`. |
| `description` | TEXT | No | - | Uraian butir kinerja. |
| `unit` | VARCHAR(30) | No | - | Satuan ukur target. |
| `target_value` | DECIMAL(10,2) | No | - | Nilai target (`CHECK (target_value >= 0)`). |
| `realization_value` | DECIMAL(10,2) | Yes | NULL | Nilai realisasi (`CHECK (realization_value IS NULL OR realization_value >= 0)`). |
| `weight` | DECIMAL(5,2) | No | - | Bobot butir (`CHECK (weight > 0 AND weight <= 100)`). |
| `score` | DECIMAL(5,2) | Yes | NULL | Nilai capaian butir. |

#### Kelompok F: Kesejahteraan dan Pensiun

**25. `bpjs_memberships`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `employee_id` | BIGINT UNSIGNED | No | - | FK ke `employees.id`. |
| `bpjs_type` | VARCHAR(20) | No | - | `CHECK (bpjs_type IN ('kesehatan', 'ketenagakerjaan'))`. |
| `membership_number` | VARCHAR(30) | No | - | `UNIQUE`; nomor kepesertaan BPJS (data sensitif, sesuai BRD 9.4.25). |
| `registered_date` | DATE | No | - | Tanggal terdaftar. |
| `status` | VARCHAR(10) | No | `'aktif'` | `CHECK (status IN ('aktif', 'nonaktif'))`. |
| `end_date` | DATE | Yes | NULL | Tanggal berakhir (`CHECK (end_date IS NULL OR end_date >= registered_date)`). |
| `archived_at` | DATETIME | Yes | NULL | Waktu diarsipkan saat berubah menjadi nonaktif. |

> **Unique Constraint `bpjs_memberships`:** `UNIQUE (membership_number)` serta *virtual generated column* `active_employee_id` untuk menjamin maksimal satu kepesertaan `aktif` per `(employee_id, bpjs_type)` (`RULE-16`, lihat Bagian 10.3).

**26. `allowance_types`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `code` | VARCHAR(20) | No | - | `UNIQUE`; kode jenis tunjangan. |
| `name` | VARCHAR(100) | No | - | Nama tunjangan (mis. tunjangan kesejahteraan, tunjangan doktor). |
| `requires_doctorate` | TINYINT(1) | No | 0 | `1` bila mensyaratkan pendidikan S3 tercatat (`RULE-09`). |
| `period_type` | VARCHAR(10) | No | - | `CHECK (period_type IN ('bulanan', 'tahunan', 'sekali'))`. |
| `description` | TEXT | Yes | NULL | Ketentuan tunjangan. |
| `is_active` | TINYINT(1) | No | 1 | Default `1`. |

**27. `allowance_requests`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `employee_id` | BIGINT UNSIGNED | No | - | FK ke `employees.id`; pemohon. |
| `allowance_type_id` | BIGINT UNSIGNED | No | - | FK ke `allowance_types.id`. |
| `period_label` | VARCHAR(20) | No | - | Periode tunjangan (mis. `2026-11` atau `2026`). |
| `amount` | DECIMAL(14,2) | Yes | NULL | Nilai pengajuan (`CHECK (amount IS NULL OR amount >= 0)`); bukan perhitungan payroll (Q-04). |
| `reason` | TEXT | Yes | NULL | Alasan pengajuan. |
| `status` | VARCHAR(12) | No | `'diajukan'` | `CHECK (status IN ('diajukan', 'disetujui', 'ditolak', 'dibatalkan'))`. |
| `approver_id` | BIGINT UNSIGNED | No | - | FK ke `employees.id`; pejabat berwenang (`approver_id <> employee_id`). |
| `submitted_at` | DATETIME | No | - | Waktu pengajuan. |
| `decided_at` | DATETIME | Yes | NULL | Waktu keputusan. |
| `decision_note` | TEXT | Yes | NULL | Wajib diisi bila `status IN ('ditolak', 'dibatalkan')`. |
| `version` | INT UNSIGNED | No | 1 | Penomoran versi untuk *optimistic locking* (`RULE-17`). |

> **Unique Constraint `allowance_requests`:** Menggunakan *virtual generated column* `active_allowance_key` agar keunikan `(employee_id, allowance_type_id, period_label)` hanya mengunci baris yang berstatus `diajukan` atau `disetujui` dan belum di-*soft-delete* (`RULE-09` & `RULE-11`).

**28. `retirements`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `employee_id` | BIGINT UNSIGNED | No | - | FK ke `employees.id`. |
| `retirement_age` | SMALLINT UNSIGNED | No | - | Usia pensiun yang dipakai (menunggu Q-07). |
| `planned_date` | DATE | No | - | Tanggal pensiun terjadwal. |
| `proposed_at` | DATETIME | No | - | Waktu diusulkan oleh sistem (*scheduled job*, `RULE-10`). |
| `status` | VARCHAR(12) | No | `'diusulkan'` | `CHECK (status IN ('diusulkan', 'diproses', 'selesai', 'dibatalkan'))`. |
| `processed_by` | BIGINT UNSIGNED | Yes | NULL | FK ke `users.id`; petugas pemroses. |
| `sk_number` | VARCHAR(100) | Yes | NULL | Nomor SK pensiun. |
| `sk_date` | DATE | Yes | NULL | Tanggal SK pensiun. |
| `completed_at` | DATETIME | Yes | NULL | Waktu proses selesai. |
| `notes` | TEXT | Yes | NULL | Catatan. |
| `version` | INT UNSIGNED | No | 1 | Penomoran versi untuk *optimistic locking* (`RULE-17`). |

> **Unique Constraint `retirements`:** Menggunakan *virtual generated column* `active_retirement_emp_id` agar satu pegawai hanya memiliki satu proses pensiun aktif (`status <> 'dibatalkan'` dan `deleted_at IS NULL`). Jika usulan pensiun pernah dibatalkan karena koreksi tanggal lahir/jabatan Guru Besar, sistem tetap dapat membuat usulan baru di masa depan.

#### Kelompok G: Audit

**29. `activity_logs`**

| Kolom | Tipe MySQL | Null | Default | Keterangan & Constraint |
|---|---|---:|---|---|
| `id` | BIGINT UNSIGNED | No | AUTO_INC | Primary key. |
| `actor_id` | BIGINT UNSIGNED | Yes | NULL | FK ke `users.id`; `NULL` bila dijalankan otomatis oleh sistem (*scheduled job*). |
| `action` | VARCHAR(30) | No | - | Mis. `SUBMIT`, `APPROVE`, `REJECT`, `CANCEL`, `UPDATE`, `ACCESS_DENIED`. |
| `entity_type` | VARCHAR(50) | No | - | Nama tabel/entitas yang terdampak. |
| `entity_id` | BIGINT UNSIGNED | Yes | NULL | ID baris terdampak (polymorphic). |
| `old_status` | VARCHAR(30) | Yes | NULL | Status lama sebelum perubahan. |
| `new_status` | VARCHAR(30) | Yes | NULL | Status baru setelah perubahan. |
| `reason` | TEXT | Yes | NULL | Wajib diisi untuk penolakan dan pembatalan (`RULE-12`). |
| `details` | JSON | Yes | NULL | Payload JSON untuk rincian perubahan data sensitif (BPJS, tunjangan, data pribadi). |
| `ip_address` | VARCHAR(45) | Yes | NULL | Alamat IPv4/IPv6 pelaku. |
| `created_at` | DATETIME | No | CURRENT_TIMESTAMP | Waktu kejadian; tabel ini **tidak** memiliki `updated_at` maupun `deleted_at`. |

### 9.4 Relasi Inti Antar Entitas

- `departments` 1:N `departments` (melalui `parent_id`).
- `departments` 1:N `employees`, `employee_assignments`, `user_roles`, `vacancies`, `work_schedules`.
- `employees` 1:N `employees` (melalui `supervisor_id`).
- `employees` 1:1 (opsional) `users` (melalui `users.employee_id`).
- `applications` 1:1 (opsional) `employees` (melalui `employees.application_id`).
- `users` M:N `roles` melalui `user_roles` (dengan atribut cakupan `unit_id`).
- `vacancies` 1:N `applications`; `applicants` 1:N `applications`; `applications` 1:N `selection_stages`.
- `employees` 1:N `employee_assignments`, `leave_balances`, `leave_requests`, `overtime_requests`, `academic_rank_histories`, `staff_rank_histories`, `study_leaves`, `certifications`, `trainings`, `workload_reports`, `performance_targets`, `bpjs_memberships`, `allowance_requests`, `retirements`.
- `work_schedules` 1:N `work_schedule_days`.
- `performance_targets` 1:N `performance_items`.
- `retirements` 1:N `trainings` (opsional melalui `trainings.retirement_id`).
- `activity_logs` mencatat perubahan seluruh entitas melalui referensi polymorphic `(entity_type, entity_id)` dan berelasi ke `users` melalui `actor_id`.

### 9.5 Strategi Constraint dan Indexing MySQL 8.4

#### A. Ringkasan Unique Constraint

| Tabel | Jenis Unique | Implementasi Kolom / Expression di MySQL 8.4 |
|---|---|---|
| `employees` | Hard Unique | `UNIQUE (nip)`, `UNIQUE (nidn)`, `UNIQUE (email)`, `UNIQUE (application_id)` |
| `users` | Hard Unique | `UNIQUE (username)`, `UNIQUE (employee_id)` |
| `roles` | Hard Unique | `UNIQUE (name)` |
| `user_roles` | Functional Unique | `UNIQUE (user_id, role_id, (IFNULL(unit_id, 0)))` |
| `applicants` | Hard Unique | `UNIQUE (email)` |
| `applications` | Hard Unique | `UNIQUE (vacancy_id, applicant_id)` |
| `selection_stages` | Hard Unique | `UNIQUE (application_id, stage_order)` |
| `leave_types` | Hard Unique | `UNIQUE (type)` |
| `leave_balances` | Hard Unique | `UNIQUE (employee_id, leave_type_id, year)` |
| `work_schedule_days` | Hard Unique | `UNIQUE (schedule_id, day_of_week)` |
| `certifications` | Hard Unique | `UNIQUE (cert_number)` |
| `workload_reports` | Hard + Conditional | `UNIQUE (employee_id, academic_year, semester, version)` + `UNIQUE (current_employee_id, academic_year, semester)` |
| `performance_targets` | Conditional Unique | `UNIQUE (active_target_key)` (hanya bila `status <> 'ditolak' AND deleted_at IS NULL`) |
| `bpjs_memberships` | Hard + Conditional | `UNIQUE (membership_number)` + `UNIQUE (active_employee_id, bpjs_type)` |
| `allowance_types` | Hard Unique | `UNIQUE (code)` |
| `allowance_requests` | Conditional Unique | `UNIQUE (active_allowance_key)` (hanya bila `status NOT IN ('ditolak','dibatalkan') AND deleted_at IS NULL`) |
| `retirements` | Conditional Unique | `UNIQUE (active_retirement_emp_id)` (hanya bila `status <> 'dibatalkan' AND deleted_at IS NULL`) |

#### B. Daftar Index Pencarian dan Pelaporan Minimum

- **Pencarian Pegawai (`NFR-04` < 2 detik):**
  - `FULLTEXT INDEX ft_employees_full_name (full_name)` untuk pencarian kata/potongan nama dengan `MATCH(full_name) AGAINST(...)`.
  - `INDEX idx_employees_unit_cat_status (unit_id, category, employment_status, deleted_at)` untuk filter daftar pegawai dan laporan `RPT-06`.
  - `INDEX idx_employees_supervisor (supervisor_id, employment_status)`.
  - `INDEX idx_employees_birth_date (employment_status, category, birth_date)` untuk *scheduled job* pendeteksi usia pensiun (`RPT-05`).
- **Rekrutmen (`RPT-01`):**
  - `INDEX idx_vacancies_status_dates (status, category, unit_id, open_date, close_date)`.
  - `INDEX idx_applications_vacancy_status (vacancy_id, status, applied_at)`.
- **Cuti & Lembur (`RPT-04` & Cek Overlap):**
  - `INDEX idx_leave_req_emp_dates (employee_id, status, start_date, end_date)`.
  - `INDEX idx_leave_req_approver (approver_id, status, submitted_at)`.
  - `INDEX idx_overtime_emp_date (employee_id, work_date, status, start_time, end_time)`.
  - `INDEX idx_overtime_approver (approver_id, status, submitted_at)`.
- **Karir, Pelatihan, & Kinerja (`RPT-02`, `RPT-03`, `RPT-06`):**
  - `INDEX idx_acad_rank_emp_status (employee_id, status, effective_date)`.
  - `INDEX idx_staff_rank_emp_status (employee_id, status, effective_date)`.
  - `INDEX idx_trainings_emp_type_dates (employee_id, training_type, status, start_date)`.
  - `INDEX idx_workload_emp_sem_curr (employee_id, academic_year, semester, is_current)`.
  - `INDEX idx_perf_targets_period_status (period_label, status, employee_id)`.
- **Audit Log:**
  - `INDEX idx_activity_entity_time (entity_type, entity_id, created_at)`.
  - `INDEX idx_activity_actor_time (actor_id, created_at)`.
  - `INDEX idx_activity_action_time (action, created_at)`.

---

## 10. Standar Implementasi Database MySQL 8.4 LTS

### 10.1 Konfigurasi Platform Database

| Parameter | Standar Implementasi |
|---|---|
| DBMS & Versi | **MySQL 8.4 LTS** (hanya MySQL). |
| Storage Engine | **InnoDB** pada seluruh tabel (mendukung transaksi ACID, foreign key, dan *row-level locking*). |
| Character Set & Collation | `utf8mb4` dengan collation `utf8mb4_0900_ai_ci`. |
| SQL Mode Wajib | `STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION`. |
| Isolation Level | `REPEATABLE READ` (default InnoDB), dikombinasikan dengan `SELECT ... FOR UPDATE` pada transaksi kritis. |
| Timezone | Koneksi database diatur ke UTC (`+00:00`); konversi tampilan ke `Asia/Jakarta` (WIB) dilakukan di level aplikasi. |

### 10.2 Konversi Tipe Data PostgreSQL (BRD) ke MySQL 8.4 (PRD)

| Tipe pada BRD | Tipe MySQL pada PRD | Justifikasi Teknis MySQL 8.4 |
|---|---|---|
| `bigint` | `BIGINT UNSIGNED` | Digunakan untuk semua PK dan FK agar rentang ID positif lebih luas dan tipe antar tabel konsisten. |
| `integer` | `INT UNSIGNED` | Digunakan untuk counter `version` (*optimistic locking*) dengan nilai awal `DEFAULT 1`. |
| `smallint` | `SMALLINT UNSIGNED` | Digunakan untuk kuota, tahun, urutan tahap, durasi jam, dan usia pensiun yang selalu bernilai $\ge 0$. |
| `varchar(n)` / `char(n)` / `text` | `VARCHAR(n)` / `CHAR(n)` / `TEXT` | Dipertahankan sesuai panjang karakter BRD. |
| `boolean` | `TINYINT(1)` | Representasi standar boolean di MySQL dan Eloquent Laravel (`0` = false, `1` = true). |
| `date` / `time` | `DATE` / `TIME` | Digunakan untuk tanggal murni dan jam kerja/lembur dalam satu hari kalender. |
| `timestamp` | **`DATETIME`** | **Penting:** Tipe `TIMESTAMP` di MySQL memiliki batas *Year 2038 Problem* (`2038-01-19 03:14:07 UTC`). Seluruh kolom waktu wajib menggunakan `DATETIME` (mendukung hingga tahun `9999`) agar aman untuk proyeksi pensiun dan retensi jangka panjang. |
| `numeric(p,s)` | `DECIMAL(p,s)` | Menjaga presisi eksak untuk skor, bobot, SKS, durasi lembur, dan nominal tunjangan. |
| `jsonb` | `JSON` | MySQL 8.4 menyimpan tipe `JSON` dalam format biner internal yang teroptimasi untuk pembacaan cepat. |

### 10.3 Adaptasi Fitur Khusus PostgreSQL ke MySQL 8.4

#### 1. Simulasi Partial Unique Index dengan Virtual Generated Column (Memperhitungkan Soft Delete & Status Final)

MySQL tidak memiliki sintaks `CREATE UNIQUE INDEX ... WHERE ...` seperti PostgreSQL. Sebagai gantinya, MySQL 8.4 mendukung pembuatan `UNIQUE KEY` di atas **`VIRTUAL GENERATED COLUMN`**. Karena `UNIQUE` di MySQL mengizinkan banyak baris bernilai `NULL`, ekspresi `CASE WHEN ... THEN ... ELSE NULL END` akan mengunci baris yang memenuhi syarat saja dan mengabaikan baris historis/dibatalkan/terhapus:

```sql
-- 1. bpjs_memberships: Maksimal 1 BPJS aktif per pegawai & jenis (RULE-16)
active_employee_id BIGINT UNSIGNED GENERATED ALWAYS AS (
    CASE WHEN status = 'aktif' AND deleted_at IS NULL THEN employee_id ELSE NULL END
) VIRTUAL,
UNIQUE KEY uq_bpjs_active_employee_type (active_employee_id, bpjs_type)

-- 2. workload_reports: Maksimal 1 laporan BKD berstatus current per Dosen & semester (RULE-07)
current_employee_id BIGINT UNSIGNED GENERATED ALWAYS AS (
    CASE WHEN is_current = 1 AND deleted_at IS NULL THEN employee_id ELSE NULL END
) VIRTUAL,
UNIQUE KEY uq_workload_current (current_employee_id, academic_year, semester)

-- 3. allowance_requests: Mencegah pengajuan ganda, namun mengizinkan ajukan ulang jika ditolak/dibatalkan (RULE-09 & RULE-11)
active_allowance_key VARCHAR(64) GENERATED ALWAYS AS (
    CASE
        WHEN status NOT IN ('ditolak', 'dibatalkan') AND deleted_at IS NULL
        THEN CONCAT(employee_id, ':', allowance_type_id, ':', period_label)
        ELSE NULL
    END
) VIRTUAL,
UNIQUE KEY uq_allowance_active_period (active_allowance_key)

-- 4. performance_targets: 1 SKP aktif per periode, boleh buat baru jika ditolak (RULE-08 & RULE-11)
active_target_key VARCHAR(45) GENERATED ALWAYS AS (
    CASE
        WHEN status <> 'ditolak' AND deleted_at IS NULL
        THEN CONCAT(employee_id, ':', period_label)
        ELSE NULL
    END
) VIRTUAL,
UNIQUE KEY uq_perf_target_active_period (active_target_key)

-- 5. retirements: 1 proses pensiun aktif per pegawai (RULE-10)
active_retirement_emp_id BIGINT UNSIGNED GENERATED ALWAYS AS (
    CASE WHEN status <> 'dibatalkan' AND deleted_at IS NULL THEN employee_id ELSE NULL END
) VIRTUAL,
UNIQUE KEY uq_retirement_active_employee (active_retirement_emp_id)
```

#### 2. Penanganan `NULL` pada Unique Key `user_roles` dengan Functional Index

Untuk mencegah seorang pengguna diberi role lintas-unit (`unit_id IS NULL`) yang sama lebih dari satu kali:

```sql
UNIQUE KEY uq_user_role_unit (user_id, role_id, (IFNULL(unit_id, 0)), (IFNULL(deleted_at, '1000-01-01 00:00:00')))
```

#### 3. Penegakan `RULE-14` (Pemohon $\ne$ Approver) melalui Trigger MySQL

Di MySQL 8.4, penggunaan `CHECK (employee_id <> approver_id)` pada kolom yang menjadi bagian dari `FOREIGN KEY` dapat memicu pembatasan engine InnoDB (Error 3823). Oleh karena itu, selain divalidasi di Laravel Policy/FormRequest, larangan menyetujui data sendiri ditegakkan di database menggunakan **Trigger `BEFORE INSERT` dan `BEFORE UPDATE`** dengan `SIGNAL SQLSTATE '45000'`:

- Pada `leave_requests`, `overtime_requests`, `allowance_requests`, `academic_rank_histories`, dan `staff_rank_histories`: tolak jika `NEW.approver_id IS NOT NULL AND NEW.employee_id = NEW.approver_id`.
- Pada `workload_reports`: tolak jika `NEW.validator_id IS NOT NULL AND NEW.employee_id = NEW.validator_id`.
- Pada `performance_targets`: tolak jika `NEW.appraiser_id IS NOT NULL AND NEW.employee_id = NEW.appraiser_id`.
- Pada `employees` (`id = supervisor_id`) dan `employee_assignments` (`employee_id = supervisor_id`): tolak jika pegawai menjadi atasan langsung bagi dirinya sendiri.

### 10.4 Transaksi, Pessimistic Locking, dan Optimistic Locking

1. **Parent-Row Mutex untuk Mencegah Overlap Tanggal/Jam (`EX-02`, `EX-07`, `EX-08`):**
   Di bawah isolation level `REPEATABLE READ`, query `SELECT COUNT(*)` biasa untuk mengecek apakah ada cuti atau lembur yang bertumpuk tidak mengunci celah bagi baris baru yang sedang di-insert secara paralel (*phantom read / write skew*). Oleh sebab itu, setiap transaksi pengajuan/persetujuan cuti, lembur, maupun penempatan wajib mengunci baris induk pegawai terlebih dahulu:
   ```sql
   SELECT id FROM employees WHERE id = :employee_id FOR UPDATE;
   ```
   Setelah baris `employees` terkunci, barulah aplikasi memeriksa overlap tanggal di `leave_requests` atau jam di `overtime_requests`.
2. **Row Locking Saldo Cuti (`RULE-03`):**
   Saat menyetujui cuti, kunci baris saldo dengan `SELECT id, quota_days, used_days, version FROM leave_balances WHERE employee_id = ? AND leave_type_id = ? AND year = ? FOR UPDATE`, pastikan `used_days + total_days <= quota_days`, lalu update saldo dan status cuti secara atomik.
3. **Optimistic Locking (`RULE-17` & `EX-04`):**
   Setiap form/request persetujuan mengirimkan nomor `version` yang terakhir dibaca. Query update wajib menyertakan klausa versi:
   ```sql
   UPDATE leave_requests
   SET status = :new_status, decided_at = NOW(), decision_note = :note, version = version + 1
   WHERE id = :id AND version = :current_version AND status = 'diajukan';
   ```
   Jika `affected_rows == 0`, transaksi wajib di-*rollback* dan sistem mengembalikan HTTP `409 Conflict` beserta data terbaru.

### 10.5 Urutan Eksekusi Migration Laravel

Agar pembuatan Foreign Key tidak gagal karena ketergantungan melingkar atau urutan tabel:

1. Buat tabel master/organisasi: `departments`, `roles`, `leave_types`, `allowance_types`.
2. Buat tabel rekrutmen: `applicants`, `vacancies` (tanpa FK `created_by` dulu, atau buat `users` lebih dulu tanpa FK `employee_id`), `applications`.
3. Buat tabel `employees` (mengacu ke `departments` dan `applications`), lalu `employee_assignments`.
4. Buat tabel `users` (mengacu ke `employees`) dan `user_roles`, lalu tambahkan FK `vacancies.created_by` dan `selection_stages`.
5. Buat tabel `retirements` **sebelum** tabel `trainings` agar FK `trainings.retirement_id -> retirements.id` dapat langsung dipasang.
6. Buat seluruh tabel transaksi cuti, lembur, jadwal kerja, karir, kinerja, BPJS, tunjangan, dan terakhir `activity_logs`.

---

## 11. Reporting dan Dashboard

| ID | Nama Laporan | Tujuan & Isi (BRD 12.1) | Filter Minimum |
|---|---|---|---|
| RPT-01 | Recruitment Dashboard | Melihat jumlah lowongan, pelamar, dan kandidat diterima per periode. | Periode, kategori (`DOSEN`/`TENDIK`), unit, status lowongan. |
| RPT-02 | SKP Report | Melihat hasil SKP per unit dan periode evaluasi. | Unit, periode (`period_label`), kategori, status SKP. |
| RPT-03 | Training, Certification & Study Report | Melihat pelatihan, sertifikasi, dan studi lanjut pegawai. | Unit, kategori, jenis kegiatan, status, rentang periode. |
| RPT-04 | Leave & Overtime Recap | Melihat rekap pengajuan cuti dan jam lembur disetujui per unit dan periode. | Unit, kategori, jenis cuti, status, rentang tanggal. |
| RPT-05 | Upcoming Retirement Report | Melihat daftar pegawai yang mendekati usia pensiun beserta status prosesnya. | Unit, kategori, rentang tanggal rencana pensiun, status pensiun. |
| RPT-06 | Workforce Composition Report | Melihat komposisi Dosen/Tendik per jabatan fungsional, pangkat, pendidikan, dan unit. | Unit, kategori, status pegawai, periode efektif. |

---

## 12. Audit, Keamanan, dan Privasi

### 12.1 Kebutuhan Audit (BRD 12.2)

1. Mencatat siapa yang mengajukan dan siapa yang menyetujui/menolak setiap pengajuan beserta stempel waktunya.
2. Mencatat `old_status` dan `new_status` pada setiap perubahan status, termasuk alasan penolakan atau pembatalan.
3. Mencatat riwayat jabatan fungsional dan kepangkatan beserta nomor dan tanggal SK.
4. Mencatat perubahan data sensitif (BPJS, tunjangan, data pribadi) beserta pelakunya di dalam kolom JSON `activity_logs.details` (tanpa menyimpan password/token).
5. Mencatat setiap percobaan akses atau aksi yang ditolak karena tidak berwenang (`ACCESS_DENIED`).
6. Memastikan `activity_logs` bersifat *append-only* (tidak dapat diubah atau dihapus oleh pengguna biasa).

### 12.2 Keamanan Aplikasi dan Database

- Terapkan RBAC pada route, controller/service, policy, dan query scope (`unit_id`).
- Lakukan *masking* pada nomor BPJS (`membership_number`) dan alamat domisili saat ditampilkan di layar daftar umum; tampilkan penuh hanya bagi role yang memiliki izin eksplisit.
- Simpan berkas CV dan sertifikat pada disk privat Laravel (`storage/app/private`) dan sajikan melalui endpoint terautentikasi yang memeriksa Policy.
- Gunakan akun user MySQL aplikasi yang tidak memiliki privilege `DROP DATABASE` atau `TRUNCATE` pada environment produksi, serta batasi operasi `UPDATE`/`DELETE` pada tabel `activity_logs`.

---

## 13. Non-Functional Requirements & Asumsi Volume Data

### 13.1 Daftar Non-Functional Requirements

| ID | Kategori | Requirement | Target / Kriteria Verifikasi |
|---|---|---|---|
| NFR-01 | Security | Akses sistem menggunakan role-based access control (RBAC) dan pembatasan unit. | Seluruh endpoint teruji menolak akses lintas unit/role yang tidak sah. |
| NFR-02 | Auditability | Aktivitas penting dan perubahan status tercatat secara utuh. | 100% transisi status dan `ACCESS_DENIED` menghasilkan baris di `activity_logs`. |
| NFR-03 | Integrity | Data konsisten dan bebas pemrosesan ganda saat diakses serentak. | Uji konkurensi membuktikan hanya 1 approval berhasil dan saldo tidak pernah minus. |
| NFR-04 | Performance | Pencarian pegawai dan daftar pengajuan tampil dalam **$\le 2$ detik** pada dataset uji. | Diukur pada dataset benchmark (lebih besar dari estimasi 5 tahun) dan diverifikasi dengan `EXPLAIN`. |
| NFR-05 | Backup & Recovery | Backup database MySQL dilakukan harian dan pemulihan diuji berkala. | Skrip `mysqldump` / backup otomatis dan prosedur *restore* ke DB uji berjalan sukses. |
| NFR-06 | Scalability | Sistem mampu menangani pertumbuhan data tahunan $+5\%$ selama 5 tahun+. | Struktur index dan query agregat laporan tetap responsif pada proyeksi volume 5 tahun. |
| NFR-07 | Maintainability | Skema database dikelola sepenuhnya melalui Laravel Migration, DBML, dan Seeder. | Database kosong dapat dibangun ulang dan diisi data sintetis dengan satu rangkaian perintah. |
| NFR-08 | Data Retention | Data historis dipertahankan sesuai kebijakan retensi per objek bisnis. | Soft delete, kolom `archived_at`, dan filter retensi mendukung usulan 2 tahun (pelamar ditolak) & 5 tahun (lowongan/lembur). |

### 13.2 Asumsi Volume dan Pertumbuhan Data (BRD Bagian 10)

Untuk objek stok (`Employee`, `BPJS Membership`), angka 1/3/5 tahun adalah jumlah baris pada akhir tahun. Untuk objek transaksi, angka 1/3/5 tahun adalah jumlah baris kumulatif dengan asumsi pertumbuhan $+5\%$ per tahun:

| Business Object | Volume Awal / Dasar Asumsi | Pertumbuhan | 1 Tahun | 3 Tahun | 5 Tahun |
|---|---|---:|---:|---:|---:|
| Employee (stok) | $1.300$ ($800$ Dosen + $500$ Tendik) | $+5\%$/tahun | $1.365$ | $1.505$ | $1.659$ |
| BPJS Membership (stok) | $2.600$ ($2$ jenis $\times 1.300$ pegawai) | $+5\%$/tahun | $2.730$ | $3.010$ | $3.318$ |
| Vacancy | $40$/tahun | $+5\%$/tahun | $\approx 40$ | $\approx 130$ | $\approx 220$ |
| Applicant & Selection | $2.000$/tahun ($40$ lowongan $\times 50$ pelamar) | $+5\%$/tahun | $\approx 2.000$ | $\approx 6.300$ | $\approx 11.100$ |
| Leave Request | $3.900$/tahun ($1.300$ pegawai $\times 3$ pengajuan) | $+5\%$/tahun | $\approx 3.900$ | $\approx 12.300$ | $\approx 21.500$ |
| Overtime Request | $12.000$/tahun ($500$ Tendik $\times 24$ pengajuan) | $+5\%$/tahun | $\approx 12.000$ | $\approx 37.800$ | $\approx 66.300$ |
| Rank History (Dosen + Tendik) | $195$/tahun ($15\%$ pegawai naik jabatan/pangkat) | $+5\%$/tahun | $\approx 200$ | $\approx 610$ | $\approx 1.100$ |
| Workload Report (BKD/LKD) | $1.600$/tahun ($800$ Dosen $\times 2$ semester) | $+5\%$/tahun | $\approx 1.600$ | $\approx 5.000$ | $\approx 8.800$ |
| Performance Target (SKP) | $2.600$/tahun ($1.300$ pegawai $\times 2$ periode) | $+5\%$/tahun | $\approx 2.600$ | $\approx 8.200$ | $\approx 14.400$ |
| Training & Certification | $2.600$/tahun ($1.300$ pegawai $\times 2$ kegiatan) | $+5\%$/tahun | $\approx 2.600$ | $\approx 8.200$ | $\approx 14.400$ |
| Allowance Request | $1.300$/tahun ($1.300$ pegawai $\times 1$ pengajuan) | $+5\%$/tahun | $\approx 1.300$ | $\approx 4.100$ | $\approx 7.200$ |
| Retirement | $30$/tahun ($30$ pegawai/tahun) | $+5\%$/tahun | $\approx 30$ | $\approx 90$ | $\approx 170$ |
| Status History / Activity Log | $70.800$/tahun ($23.600$ pengajuan $\times 3$ transisi) | $+5\%$/tahun | $\approx 70.800$ | $\approx 223.200$ | $\approx 391.200$ |

---

## 14. Potensi Integrasi Eksternal

Sesuai BRD Bagian 13.3 dan Q-08, integrasi berikut dicatat sebagai kandidat pengembangan masa depan (di luar kewajiban rilis pertama):

1. **BPJS:** Sinkronisasi dan validasi status kepesertaan.
2. **Pangkalan Data Dosen Nasional (PDDIKTI/SISTER):** Sinkronisasi jabatan fungsional, BKD, dan sertifikasi Dosen.
3. **Sistem Penggajian/Keuangan Kampus:** Pertukaran data tunjangan dan kompensasi lembur yang telah disetujui.
4. **Layanan Email/Notifikasi:** Pengingat persetujuan tertunda dan notifikasi persiapan masa pensiun.
5. **Authentication/SSO Kampus:** Login tunggal dan pemetaan role civitas akademika.

---

## 15. Asumsi dan Batasan

### 15.1 Asumsi

1. Jumlah pegawai awal diasumsikan $800$ Dosen dan $500$ Tendik dengan pertumbuhan $+5\%$ per tahun.
2. Setiap pegawai memiliki NIP/ID unik dan satu atasan langsung (`supervisor_id`) sebagai approver, kecuali pimpinan puncak.
3. Kebijakan cuti, lembur, jabatan fungsional, dan pensiun UAD tersedia untuk dikonfirmasi ke BSDM.
4. Data uji digenerate secara sintetis dalam jumlah lebih besar dari estimasi untuk latihan evaluasi performa query dan indexing.
5. Fokus versi ini adalah pencatatan data kepegawaian; perhitungan gaji tidak termasuk kecuali diputuskan melalui Q-04.

### 15.2 Batasan

1. Proyek merupakan simulasi akademik; data pegawai nyata tidak digunakan.
2. BRD disusun dari informasi publik BSDM UAD (struktur organisasi dan layanan), bukan dari dokumen internal tertutup.
3. Implementasi aplikasi menggunakan **Laravel**, pemodelan ERD menggunakan **DBML**, database menggunakan **MySQL 8.4 LTS**, dan repositori pusat menggunakan **GitHub**.
4. Sistem wajib menerapkan pembagian akses berbasis role dan mempertahankan riwayat untuk audit.
5. Pengerjaan mengikuti urutan: **BRD $\rightarrow$ ERD $\rightarrow$ Normalisasi $\rightarrow$ Logical Schema MySQL $\rightarrow$ Migration & Seed Data $\rightarrow$ SQL Query & Indexing $\rightarrow$ Benchmark & Optimization $\rightarrow$ Testing & Verification**.

---

## 16. Open Questions dan Keputusan Skema yang Harus Dikonfirmasi

| ID | Pertanyaan Terbuka (BRD Bagian 16) | Dampak pada Skema Database & Aturan Aplikasi |
|---|---|---|
| Q-01 | Apa saja jenis cuti dan jatahnya untuk Dosen dan Tendik, dan apakah saldo dihitung per tahun kalender atau tahun akademik? | Nilai seed `leave_types.annual_quota`, format `leave_balances.year`, dan jadwal reset saldo tahunan. |
| Q-02 | Apakah ada batas waktu persetujuan dan siapa pengganti approver bila atasan berhalangan? | Kebutuhan aturan delegasi/eskalasi approver pada workflow persetujuan. |
| Q-03 | Berapa lama masing-masing jenis data dan audit log wajib disimpan sebelum diarsipkan? | Konfirmasi atas usulan retensi 5 tahun (`vacancies`, `overtime_requests`) dan kebijakan partisi/arsip `activity_logs`. |
| Q-04 | Apakah modul penggajian (payroll) masuk scope, mengingat studi kasus HRIM di kelas mencakup penggajian? | Jika tidak, cukup mencatat `allowance_requests.amount` dan jam lembur disetujui tanpa tabel slip gaji. |
| Q-05 | Apakah pegawai boleh berpindah kategori (Tendik menjadi Dosen) dan bagaimana riwayatnya diperlakukan? | Validasi perubahan `employees.category` dan pencatatan riwayatnya pada `employee_assignments.category`. |
| Q-06 | Siapa pemberi persetujuan pada jalur percepatan jabatan fungsional Dosen? | Aturan otorisasi untuk pengisian `academic_rank_histories.approver_id` ketika `is_accelerated = 1`. |
| Q-07 | Berapa usia pensiun untuk Dosen (termasuk Guru Besar) dan Tendik di UAD? | Parameter usia pada *scheduled job* pensiun dan pengisian `retirements.retirement_age` (mis. Tendik 56/58 thn, Dosen 65 thn, Guru Besar 70 thn). |
| Q-08 | Sistem eksternal mana yang perlu terhubung (BPJS, pangkalan data dosen nasional, penggajian, email)? | Kebutuhan endpoint integrasi atau ekspor data. |
| Q-09 | Berapa lama data pelamar yang ditolak boleh disimpan? | Konfirmasi atas usulan retensi 2 tahun untuk pembersihan/pengarsipan `applicants`, `applications`, dan file CV yang ditolak. |

---

## 17. Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Kebijakan aktual berbeda dari asumsi BRD. | Perubahan business rule dan skema setelah ERD dibuat. | Konfirmasi Q-01, Q-05, dan Q-07 sebelum mengunci ERD final dan gunakan tabel parameter/referensi yang fleksibel. |
| Akses data sensitif terlalu luas. | Kebocoran nomor BPJS/data pribadi dan perubahan tanpa kewenangan. | Terapkan RBAC (`RULE-15`), *masking* atribut sensitif, dan pencatatan `ACCESS_DENIED` di `activity_logs`. |
| Scope 17 proses terlalu luas. | Tidak semua proses selesai dalam waktu proyek. | Kerjakan requirement berprioritas **Must** terlebih dahulu sebelum **Should**. |
| Estimasi volume meleset. | Hasil benchmark kurang representatif. | Validasi ulang asumsi volume pada Bagian 13.2 dan siapkan seeder dengan pengali volume yang dapat dikonfigurasi. |
| Riwayat kepegawaian terhapus. | Audit trail hilang. | Terapkan `SoftDeletes`, `ON DELETE RESTRICT`, dan proteksi tabel `activity_logs` (`RULE-13`). |
| Konflik pemrosesan bersamaan (*race condition*). | Status ganda, saldo cuti minus, atau jadwal bentrok. | Gunakan transaksi InnoDB, *pessimistic lock* pada baris `employees`/`leave_balances`, dan *optimistic locking* (`version`). |

---

## 18. Acceptance Criteria dan Skenario Pengujian

### 18.1 Rekrutmen & Onboarding

- **Given** lowongan berstatus `dibuka`, **when** pelamar mengirim lamaran pertama kali, **then** sistem menyimpan `applications` dengan status `diajukan`.
- **Given** pelamar sudah terdaftar pada lowongan yang sama, **when** pelamar mengirim lamaran kedua, **then** MySQL/aplikasi menolak duplikasi (`uq_application_vacancy_applicant`).
- **Given** pendaftaran belum berstatus `diterima`, **when** petugas mencoba membuat data `employees` dari pendaftaran tersebut, **then** sistem menolak proses onboarding (`RULE-02`).

### 18.2 Cuti, Lembur, dan Persetujuan

- **Given** saldo cuti mencukupi dan tanggal tidak bentrok, **when** pengajuan cuti disetujui atasan, **then** `leave_balances.used_days` bertambah, status menjadi `disetujui`, dan `activity_logs` tercatat dalam satu transaksi.
- **Given** dua approver menekan tombol setuju/tolak pada pengajuan yang sama secara bersamaan, **when** transaksi diproses, **then** hanya satu transaksi yang berhasil menaikkan `version` dan transaksi kedua ditolak dengan status konflik (`EX-04`).
- **Given** pengajuan tunjangan atau SKP pernah berstatus `ditolak` atau `dibatalkan`, **when** pegawai mengajukan ulang untuk periode yang sama, **then** sistem mengizinkan penyimpanan baris baru tanpa terhalang *unique constraint* (`RULE-11`).
- **Given** pemohon mencoba menyetujui pengajuannya sendiri, **when** request dikirim, **then** sistem/trigger menolak aksi dan mencatat `ACCESS_DENIED` pada `activity_logs` (`EX-03`).

### 18.3 Karir, BKD/LKD, dan SKP

- **Given** Dosen mengajukan kenaikan jabatan melompati jenjang tanpa `is_accelerated = 1`, **when** disimpan, **then** sistem menolak pengajuan (`EX-06`).
- **Given** laporan BKD/LKD sudah berstatus `final`, **when** Dosen mengajukan revisi dengan alasan, **then** sistem membuat baris baru dengan `version + 1` dan `is_current = 1`, serta mengubah baris lama menjadi `is_current = 0` (`RULE-07`).
- **Given** komponen SKS pada BKD/LKD diubah, **when** baris disimpan, **then** `total_credit` selalu sama persis dengan penjumlahan keempat komponen SKS.

### 18.4 BPJS, Tunjangan, dan Pensiun

- **Given** pegawai sudah memiliki BPJS Kesehatan berstatus `aktif`, **when** ditambahkan BPJS Kesehatan baru berstatus `aktif`, **then** MySQL menolak insert (`uq_bpjs_active_employee_type`), namun mengizinkan bila data lama diubah menjadi `nonaktif` terlebih dahulu (`RULE-16`).
- **Given** pegawai masih memiliki pengajuan cuti/lembur/tunjangan berstatus `diajukan`, **when** petugas mencoba menyelesaikan status pensiun (`selesai`), **then** sistem memblokir penyelesaian pensiun hingga seluruh pengajuan aktif diselesaikan (`EX-09`).

### 18.5 Performa, Keamanan, dan Backup

- Pencarian pegawai berdasarkan nama (`FULLTEXT`) atau filter unit/kategori serta pemuatan daftar pengajuan selesai dalam waktu **$\le 2$ detik** pada dataset uji.
- Prosedur backup harian dan restore ke database MySQL 8.4 pengujian terbukti berjalan tanpa kehilangan integritas foreign key.

---

## 19. Traceability Matrix

| Business Problem | Business Objective | BR | FR | Proses | Entitas Utama MySQL | Verifikasi |
|---|---|---|---|---|---|---|
| BP-01 | BO-01 | BR-01 | FR-01 | P-01 s.d. P-03 | `departments`, `vacancies`, `applicants`, `applications`, `selection_stages`, `employees`, `employee_assignments` | Recruitment & Onboarding Test |
| BP-02 | BO-02 | BR-02, BR-03, BR-10 | FR-02, FR-03, FR-10 | P-04 s.d. P-06, P-15 | `leave_types`, `leave_balances`, `leave_requests`, `overtime_requests`, `work_schedules`, `work_schedule_days`, `allowance_types`, `allowance_requests`, `activity_logs` | Approval Workflow & Concurrency Test |
| BP-03 | BO-02, BO-03 | BR-04, BR-05, BR-06 | FR-04 s.d. FR-06 | P-07 s.d. P-11 | `academic_rank_histories`, `staff_rank_histories`, `study_leaves`, `certifications`, `trainings` | Career History & Acceleration Test |
| BP-04 | BO-03 | BR-07, BR-08 | FR-07, FR-08 | P-12, P-13 | `workload_reports`, `performance_targets`, `performance_items` | Performance Evaluation & Versioning Test |
| BP-05 | BO-03 | BR-09, BR-11 | FR-09, FR-11 | P-14, P-16 | `bpjs_memberships`, `retirements`, `trainings` | Benefit & Retirement Lifecycle Test |
| BP-06 | BO-04 | BR-12 | FR-12 | P-17 | Seluruh entitas agregat, `users`, `roles`, `user_roles`, `activity_logs` | Report, RBAC & Audit Test |

---

## 20. Definition of Done (DoD)

Deliverable proyek dinyatakan selesai apabila:

1. Masalah bisnis (`BP-01` s.d. `BP-06`) dan tujuan bisnis (`BO-01` s.d. `BO-04`) terdefinisi dan tertelusur hingga ke tabel database.
2. Stakeholder, actor, dan matriks RBAC beserta cakupan unit telah diimplementasikan.
3. Seluruh 17 proses normal dan 12 skenario pengecualian (`EX-01` s.d. `EX-12`) telah ditangani.
4. Seluruh 17 aturan bisnis (`RULE-01` s.d. `RULE-17`) telah ditegakkan pada lapisan aplikasi dan MySQL 8.4.
5. Diagram ERD (DBML) untuk 29 entitas telah dinormalisasi dan konsisten dengan Data Dictionary.
6. Seluruh Laravel migration dapat dijalankan dari database MySQL 8.4 kosong tanpa error urutan Foreign Key.
7. *Virtual generated column*, *functional unique index*, `CHECK` constraint, `FULLTEXT` index, dan Trigger MySQL (`RULE-14`) berfungsi sesuai spesifikasi.
8. Skenario konkurensi (*optimistic locking* & *pessimistic locking*) dan *append-only* `activity_logs` lulus uji otomatis/manual.
9. Benchmark pencarian dan laporan memenuhi target performa ($\le 2$ detik) menggunakan data sintetis.
10. Prosedur backup dan restore database telah diuji dan didokumentasikan.

---

## 21. Tahapan Lanjutan: Dari PRD ke Implementasi Database MySQL

Mengikuti alur pada BRD Bagian 20 yang telah diadaptasi ke MySQL 8.4:

1. **Business Requirements & PRD $\rightarrow$ Conceptual Model / ERD (DBML):** Menyusun kode DBML lengkap 29 tabel beserta relasinya.
2. **ERD $\rightarrow$ Normalization:** Memverifikasi bentuk normal (3NF/BCNF) dan mengunci kamus data.
3. **Normalized Model $\rightarrow$ Logical Schema MySQL 8.4:** Menetapkan DDL MySQL 8.4 termasuk *generated columns*, *functional indexes*, `CHECK` constraints, dan triggers.
4. **Logical Schema $\rightarrow$ Migration & Seed Data:** Menerjemahkan skema ke Laravel Migration dan membuat Seeder sintetis skala besar.
5. **SQL Query $\rightarrow$ Indexing:** Menyusun query untuk 6 laporan (`RPT-01` s.d. `RPT-06`) dan memasang index yang optimal.
6. **Benchmark $\rightarrow$ Optimization:** Menjalankan `EXPLAIN ANALYZE` pada MySQL 8.4 dan mengoptimalkan query hingga di bawah 2 detik.
7. **Testing $\rightarrow$ Verification terhadap BRD & PRD:** Menguji seluruh business rules, skenario exception, RBAC, serta backup/restore.

---

## 22. Referensi

- **Business Requirements Document (BRD) - Antech Async**, Versi 1.1, Status Draft, 7 September 2026.
- [MySQL 8.4 Reference Manual - CREATE TABLE & CHECK Constraints](https://dev.mysql.com/doc/refman/8.4/en/create-table.html)
- [MySQL 8.4 Reference Manual - Generated Columns](https://dev.mysql.com/doc/refman/8.4/en/create-table-generated-columns.html)
- [MySQL 8.4 Reference Manual - Functional Key Parts](https://dev.mysql.com/doc/refman/8.4/en/create-index.html#create-index-functional-key-parts)
- [MySQL 8.4 Reference Manual - InnoDB Locking & Transaction Model](https://dev.mysql.com/doc/refman/8.4/en/innodb-locking-transaction-model.html)

---

## 23. Riwayat Perubahan Dokumen

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0 | 17 November 2026 | PRD pertama diturunkan dari BRD Antech Async v1.1; target database ditetapkan MySQL saja. |
| 1.1 | 17 November 2026 | Revisi hasil audit: penambahan tabel tim & peran dari BRD, pemetaan `BO-01` s.d. `BO-04`, pencantuman angka usulan retensi BRD, penyelarasan `membership_number`, penambahan kolom `version` pada seluruh tabel persetujuan, penyempurnaan *virtual generated columns* dengan pengecekan `deleted_at IS NULL` & status pembatalan/penolakan (`RULE-11`), penambahan *functional unique index* pada `user_roles`, strategi Trigger MySQL untuk `RULE-14`, *pessimistic lock* induk pegawai untuk pencegahan overlap (`EX-02`/`EX-07`), serta penambahan `FULLTEXT` index pencarian nama pegawai. |
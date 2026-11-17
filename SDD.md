# SYSTEM DESIGN DOCUMENT (SDD)

## Antech Async - Human Resource Information System (HRIS)

| Metadata | Detail |
|---|---|
| Nama Sistem | Antech Async - Sistem Informasi Kepegawaian dan Manajemen Sumber Daya Manusia (HRIS) |
| Organisasi / Domain | Biro Sumber Daya Manusia (BSDM), Universitas Ahmad Dahlan |
| Versi SDD | 1.0 |
| Status | Draft - Diturunkan dari BRD v1.1 & PRD v1.1 |
| Tanggal SDD | 17 November 2026 |
| Framework Backend | Laravel 11.x (PHP 8.3+) |
| Database Target | **MySQL 8.4 LTS (Storage Engine: InnoDB)** |
| Pemodelan Skema | DBML (Database Markup Language) & Laravel Migrations |
| Repositori | GitHub |

---

## 1. Pendahuluan

### 1.1 Tujuan Dokumen
System Design Document (SDD) ini menjelaskan **bagaimana** sistem HRIS Antech Async dirancang dan dibangun secara teknis untuk memenuhi seluruh kebutuhan dalam `BRD v1.1` dan `PRD v1.1`. Dokumen ini menjadi panduan langsung bagi tim pengembang dalam menyusun kode DBML, menulis Laravel Migration, membangun *Service Layer*, memasang *Trigger* dan *Constraint* di MySQL 8.4, menulis *Query* pelaporan, serta menjalankan pengujian performa.

### 1.2 Ruang Lingkup Teknis
1. Arsitektur Aplikasi Berlapis (*Layered Architecture*) berbasis Laravel.
2. Pemodelan Data Konseptual, Logis, dan Analisis Normalisasi ($1\text{NF} \rightarrow 2\text{NF} \rightarrow 3\text{NF}/\text{BCNF}$) untuk 29 entitas.
3. Rancangan Skema Fisik MySQL 8.4 LTS (InnoDB), mencakup *Virtual Generated Columns*, *Functional Unique Indexes*, *Check Constraints*, dan *Database Triggers*.
4. Rancangan Urutan *Migration* Laravel (29 tabel) yang bebas konflik *Foreign Key*.
5. Rancangan *State Machine*, *Pessimistic Locking*, dan *Optimistic Locking* untuk menangani konkurensi (`RULE-17`).
6. Rancangan Keamanan Berbasis Peran (RBAC), *Unit Scoping*, *Data Masking*, dan *Append-Only Audit Log*.
7. Rancangan Query SQL untuk 6 Laporan Lintas Bidang (`RPT-01` s.d. `RPT-06`).
8. Strategi *Indexing*, *Synthetic Data Seeding*, *Benchmarking*, serta *Backup & Recovery*.

---

## 2. Arsitektur Sistem

### 2.1 Diagram Arsitektur Berlapis (Layered Architecture)

Sistem menggunakan pola **Service-Oriented Monolith** di atas kerangka kerja Laravel dengan pemisahan tanggung jawab yang tegas antara lapisan presentasi, otorisasi, logika bisnis, dan persistensi data MySQL 8.4:

```
+-----------------------------------------------------------------------------------+
|                              CLIENT / BROWSER LAYER                               |
|        (Pegawai, Atasan, Staf Bidang BSDM, Kepala Biro, Warek, Pelamar)           |
+-----------------------------------------+-----------------------------------------+
                                          | HTTPS
                                          v
+-----------------------------------------------------------------------------------+
|                        HTTP & ROUTING LAYER (LARAVEL)                             |
|   [Auth Middleware] ---> [RBAC & Unit Scope Middleware] ---> [Rate Limiter]       |
+-----------------------------------------+-----------------------------------------+
                                          |
                                          v
+-----------------------------------------------------------------------------------+
|                     CONTROLLER, VALIDATION & POLICY LAYER                         |
|  +-----------------------+  +------------------------+  +----------------------+  |
|  | FormRequest Validator |  | Authorization Policies |  | Data Masking/DTO     |  |
|  | (Validasi Input Awal) |  | (Cek Role & Unit Scope)|  | (Proteksi No. BPJS)  |  |
|  +-----------------------+  +------------------------+  +----------------------+  |
+-----------------------------------------+-----------------------------------------+
                                          |
                                          v
+-----------------------------------------------------------------------------------+
|                       DOMAIN SERVICE & WORKFLOW LAYER                             |
|  +-----------------------+  +------------------------+  +----------------------+  |
|  | Workflow StateMachine |  | Concurrency Manager    |  | AuditLogger Service  |  |
|  | (Transisi Status Sah) |  | (FOR UPDATE & Version) |  | (Append ActivityLog) |  |
|  +-----------------------+  +------------------------+  +----------------------+  |
|  +-----------------------+  +------------------------+  +----------------------+  |
|  | Leave & Overtime Svc  |  | Career & BKD/SKP Svc   |  | Retirement Scheduler |  |
|  +-----------------------+  +------------------------+  +----------------------+  |
+--------------------+-----------------------------------------+--------------------+
                     |                                         |
                     v Eloquent / PDO (TCP/IP)                 v Filesystem API
+----------------------------------------------+  +---------------------------------+
|         DATABASE LAYER: MySQL 8.4 LTS        |  |     PRIVATE STORAGE LAYER       |
|  - Storage Engine : InnoDB (ACID)            |  |  - Disk: storage/app/private    |
|  - Charset/Collate: utf8mb4_0900_ai_ci       |  |  - CV Pelamar (/cv/*)           |
|  - Virtual Generated Columns (Partial UK)    |  |  - Sertifikat (/certificates/*) |
|  - Functional Indexes & FULLTEXT Index       |  |  - Akses via Signed/Auth Route  |
|  - Triggers (RULE-13 Append-Only & RULE-14)  |  +---------------------------------+
+----------------------------------------------+
```

### 2.2 Struktur Direktori Modul Aplikasi (Laravel)

Untuk menjaga *maintainability* (`NFR-07`) atas 17 proses bisnis, kode aplikasi diorganisasikan berdasarkan domain modul:

```text
app/
├── Console/Commands/
│   ├── ProposeUpcomingRetirementsCommand.php   # Scheduled Job Pensiun (RULE-10)
│   └── ArchiveExpiredRecruitmentDataCommand.php # Retensi Data Pelamar (Q-09)
├── Enums/                                      # Enum Status per Konteks Modul
├── Exceptions/
│   ├── ConcurrencyConflictException.php        # HTTP 409 untuk EX-04 (RULE-17)
│   ├── BusinessRuleViolationException.php      # HTTP 422 untuk RULE-01 s.d. RULE-16
│   └── UnauthorizedActionException.php         # HTTP 403 + Log ACCESS_DENIED (EX-03)
├── Http/
│   ├── Controllers/
│   ├── Middleware/EnforceUnitScopeMiddleware.php
│   └── Requests/
├── Models/                                     # 29 Model Eloquent + Scopes
├── Policies/                                   # Otorisasi per Entitas & Unit
└── Services/
    ├── Audit/ActivityLogService.php
    ├── Organization/EmployeeAssignmentService.php
    ├── Recruitment/OnboardingService.php
    ├── Attendance/LeaveRequestService.php
    ├── Attendance/OvertimeRequestService.php
    ├── Career/AcademicRankService.php
    ├── Career/StaffRankService.php
    ├── Performance/WorkloadReportService.php
    ├── Performance/SkpEvaluationService.php
    ├── Welfare/BpjsService.php
    ├── Welfare/AllowanceRequestService.php
    ├── Retirement/RetirementProcessService.php
    └── Reporting/HrisReportQueryService.php
```

---

## 3. Perancangan Basis Data: Konseptual, Normalisasi & DBML

### 3.1 Pengelompokan 29 Entitas dalam 7 Domain

| Kelompok Domain | Daftar Tabel (29 Entitas) | Jumlah |
|---|---|---:|
| **A. Organisasi, Pegawai & Akses** | `departments`, `employees`, `employee_assignments`, `users`, `roles`, `user_roles` | 6 |
| **B. Rekrutmen & Onboarding** | `vacancies`, `applicants`, `applications`, `selection_stages` | 4 |
| **C. Cuti, Lembur & Jadwal Kerja** | `leave_types`, `leave_balances`, `leave_requests`, `overtime_requests`, `work_schedules`, `work_schedule_days` | 6 |
| **D. Karir & Pengembangan** | `academic_rank_histories`, `staff_rank_histories`, `study_leaves`, `certifications`, `trainings` | 5 |
| **E. Evaluasi Kinerja** | `workload_reports`, `performance_targets`, `performance_items` | 3 |
| **F. Kesejahteraan & Pensiun** | `bpjs_memberships`, `allowance_types`, `allowance_requests`, `retirements` | 4 |
| **G. Audit Trail** | `activity_logs` | 1 |

### 3.2 Analisis Normalisasi ($1\text{NF} \rightarrow 2\text{NF} \rightarrow 3\text{NF}/\text{BCNF}$)

Sesuai tahapan pada BRD Bagian 20 (`ERD -> Normalization -> Logical Schema`), seluruh 29 tabel telah dinormalisasi hingga mencapai **Third Normal Form (3NF)** dan **Boyce-Codd Normal Form (BCNF)**:

1. **First Normal Form (1NF - Atomisitas & Penghilangan Kelompok Berulang):**
   * Rincian hari kerja dalam satu jadwal dipisahkan dari `work_schedules` ke tabel anak `work_schedule_days` (satu baris per hari `day_of_week`).
   * Tahapan seleksi pelamar dipisahkan dari `applications` ke tabel `selection_stages`.
   * Butir target kinerja dipisahkan dari `performance_targets` ke tabel `performance_items`.
   * Kolom `activity_logs.details` menggunakan tipe `JSON` khusus untuk *snapshot* perubahan audit (atribut lama vs baru) yang sifatnya skema-fleksibel dan tidak digunakan sebagai kunci relasi.
2. **Second Normal Form (2NF - Penghilangan Ketergantungan Fungsional Parsial):**
   * Seluruh tabel menggunakan *surrogate primary key* tunggal `id BIGINT UNSIGNED AUTO_INCREMENT`, serta memiliki *natural/composite candidate key* yang utuh.
   * Pada proses rekrutmen, biodata pelamar (`full_name`, `email`, `phone`, `birth_date`, `cv_path`) dipisahkan ke tabel `applicants`, sedangkan data pendaftaran per lowongan (`applied_at`, `aik_score`, `status`) berada di `applications(vacancy_id, applicant_id)`. Dengan demikian, biodata pelamar tidak bergantung parsial pada `vacancy_id`.
   * Atribut jenis cuti (`name`, `annual_quota`, `applies_to`) dipisahkan ke `leave_types`, sehingga `leave_balances(employee_id, leave_type_id, year)` hanya menyimpan kuota dan pemakaian tahun berjalan.
3. **Third Normal Form (3NF / BCNF - Penghilangan Ketergantungan Transitif):**
   * Atribut unit kerja (`name`, `unit_type`, `parent_id`) berada di `departments`, bukan disalin ke `employees` atau `vacancies`.
   * Atribut jenis tunjangan (`name`, `requires_doctorate`, `period_type`) berada di `allowance_types`, bukan di `allowance_requests`.
4. **Justifikasi Denormalisasi Terkendali (*Controlled & Derived Attributes*):**
   * **Penempatan Terkini vs Riwayat (`employees` vs `employee_assignments`):** Kolom `unit_id`, `supervisor_id`, dan `category` pada `employees` menyimpan kondisi aktif saat ini untuk mempercepat query otorisasi RBAC dan filter pegawai ($\le 2\text{ detik}$), sedangkan `employee_assignments` menyimpan riwayat lengkap berdasarkan `effective_from` dan `effective_until`. Sinkronisasi keduanya dijaga secara transaksional di `EmployeeAssignmentService`.
   * **Total Beban SKS (`workload_reports.total_credit`):** Didefinisikan sebagai `GENERATED ALWAYS AS (teaching_credit + research_credit + service_credit + support_credit) STORED` di MySQL 8.4. Secara matematis ini menghilangkan anomali pembaruan (*update anomaly*) karena MySQL menghitung nilainya secara otomatis setiap kali komponen SKS berubah.
   * **Total Hari Cuti (`leave_requests.total_days`) & Skor Akhir SKP (`performance_targets.final_score`):** Disimpan sebagai nilai tetap saat transaksi diajukan/disetujui agar perubahan kalender libur atau rumus di masa depan tidak mengubah ketetapan historis yang sudah disahkan.

### 3.3 Spesifikasi DBML (Database Markup Language)

Berikut adalah definisi DBML kanonis untuk 29 entitas sesuai batasan `PRD Bagian 15.2.3`:

```dbml
Project antech_async_hris {
  database_type: 'MySQL'
  Note: 'HRIS BSDM Universitas Ahmad Dahlan - MySQL 8.4 LTS (InnoDB)'
}

// ==========================================
// A. ORGANISASI, PEGAWAI, DAN AKSES
// ==========================================
Table departments {
  id bigint [pk, increment]
  parent_id bigint [ref: > departments.id, null]
  name varchar(150) [not null]
  unit_type varchar(20) [not null, note: 'biro, bidang, urusan, unit']
  is_active boolean [not null, default: true]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

Table employees {
  id bigint [pk, increment]
  nip varchar(30) [not null, unique]
  nidn varchar(20) [unique, null]
  full_name varchar(150) [not null]
  category varchar(10) [not null, note: 'DOSEN, TENDIK']
  gender char(1) [not null, note: 'L, P']
  birth_place varchar(100) [not null]
  birth_date date [not null]
  email varchar(150) [not null, unique]
  phone varchar(20) [null]
  address text [null]
  highest_education varchar(10) [not null]
  unit_id bigint [not null, ref: > departments.id]
  supervisor_id bigint [null, ref: > employees.id]
  application_id bigint [unique, null, ref: - applications.id]
  employment_status varchar(10) [not null, default: 'aktif', note: 'aktif, nonaktif, pensiun']
  join_date date [not null]
  end_date date [null]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

Table employee_assignments {
  id bigint [pk, increment]
  employee_id bigint [not null, ref: > employees.id]
  unit_id bigint [not null, ref: > departments.id]
  supervisor_id bigint [null, ref: > employees.id]
  category varchar(10) [not null, note: 'DOSEN, TENDIK']
  effective_from date [not null]
  effective_until date [null]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

Table users {
  id bigint [pk, increment]
  employee_id bigint [unique, null, ref: - employees.id]
  username varchar(50) [not null, unique]
  password_hash varchar(255) [not null]
  is_active boolean [not null, default: true]
  last_login_at datetime [null]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

Table roles {
  id bigint [pk, increment]
  name varchar(100) [not null, unique]
  description text [null]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

Table user_roles {
  id bigint [pk, increment]
  user_id bigint [not null, ref: > users.id]
  role_id bigint [not null, ref: > roles.id]
  unit_id bigint [null, ref: > departments.id]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

// ==========================================
// B. REKRUTMEN DAN ONBOARDING
// ==========================================
Table vacancies {
  id bigint [pk, increment]
  unit_id bigint [not null, ref: > departments.id]
  title varchar(150) [not null]
  category varchar(10) [not null, note: 'DOSEN, TENDIK']
  description text [null]
  min_qualification text [not null]
  quota smallint [not null]
  open_date date [not null]
  close_date date [not null]
  status varchar(10) [not null, default: 'draf', note: 'draf, dibuka, ditutup']
  created_by bigint [not null, ref: > users.id]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

Table applicants {
  id bigint [pk, increment]
  full_name varchar(150) [not null]
  email varchar(150) [not null, unique]
  phone varchar(20) [not null]
  birth_date date [not null]
  highest_education varchar(10) [not null]
  cv_path varchar(255) [null]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

Table applications {
  id bigint [pk, increment]
  vacancy_id bigint [not null, ref: > vacancies.id]
  applicant_id bigint [not null, ref: > applicants.id]
  applied_at datetime [not null]
  aik_score decimal(5,2) [null]
  status varchar(10) [not null, default: 'diajukan', note: 'diajukan, proses, diterima, ditolak']
  decided_at datetime [null]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]

  Indexes {
    (vacancy_id, applicant_id) [unique]
  }
}

Table selection_stages {
  id bigint [pk, increment]
  application_id bigint [not null, ref: > applications.id]
  stage_name varchar(50) [not null]
  stage_order smallint [not null]
  score decimal(5,2) [null]
  result varchar(10) [not null, default: 'menunggu', note: 'menunggu, lolos, gugur']
  assessed_by bigint [null, ref: > users.id]
  assessed_at datetime [null]
  notes text [null]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]

  Indexes {
    (application_id, stage_order) [unique]
  }
}

// ==========================================
// C. CUTI, LEMBUR, DAN JADWAL KERJA
// ==========================================
Table leave_types {
  id bigint [pk, increment]
  type varchar(20) [not null, unique]
  name varchar(100) [not null]
  annual_quota smallint [not null]
  applies_to varchar(10) [not null, default: 'SEMUA', note: 'SEMUA, DOSEN, TENDIK']
  is_active boolean [not null, default: true]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

Table leave_balances {
  id bigint [pk, increment]
  employee_id bigint [not null, ref: > employees.id]
  leave_type_id bigint [not null, ref: > leave_types.id]
  year smallint [not null]
  quota_days smallint [not null]
  used_days smallint [not null, default: 0]
  version int [not null, default: 1]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]

  Indexes {
    (employee_id, leave_type_id, year) [unique]
  }
}

Table leave_requests {
  id bigint [pk, increment]
  employee_id bigint [not null, ref: > employees.id]
  leave_type_id bigint [not null, ref: > leave_types.id]
  start_date date [not null]
  end_date date [not null]
  total_days smallint [not null]
  reason text [not null]
  status varchar(12) [not null, default: 'diajukan']
  approver_id bigint [not null, ref: > employees.id]
  submitted_at datetime [not null]
  decided_at datetime [null]
  decision_note text [null]
  version int [not null, default: 1]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

Table overtime_requests {
  id bigint [pk, increment]
  employee_id bigint [not null, ref: > employees.id]
  work_date date [not null]
  start_time time [not null]
  end_time time [not null]
  total_hours decimal(4,2) [not null]
  reason text [not null]
  status varchar(12) [not null, default: 'diajukan']
  approver_id bigint [not null, ref: > employees.id]
  submitted_at datetime [not null]
  decided_at datetime [null]
  decision_note text [null]
  version int [not null, default: 1]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

Table work_schedules {
  id bigint [pk, increment]
  name varchar(100) [not null]
  unit_id bigint [null, ref: > departments.id]
  category varchar(10) [null]
  valid_from date [not null]
  valid_until date [null]
  is_published boolean [not null, default: false]
  published_at datetime [null]
  created_by bigint [not null, ref: > users.id]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

Table work_schedule_days {
  id bigint [pk, increment]
  schedule_id bigint [not null, ref: > work_schedules.id]
  day_of_week tinyint [not null, note: '1=Senin .. 7=Minggu']
  start_time time [not null]
  end_time time [not null]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]

  Indexes {
    (schedule_id, day_of_week) [unique]
  }
}

// ==========================================
// D. KARIR DAN PENGEMBANGAN
// ==========================================
Table academic_rank_histories {
  id bigint [pk, increment]
  employee_id bigint [not null, ref: > employees.id]
  rank_name varchar(30) [not null, note: 'Asisten Ahli, Lektor, Lektor Kepala, Guru Besar']
  credit_points decimal(8,2) [null]
  is_accelerated boolean [not null, default: false]
  status varchar(12) [not null, default: 'diajukan']
  submitted_at datetime [not null]
  approver_id bigint [null, ref: > employees.id]
  decided_at datetime [null]
  effective_date date [null]
  sk_number varchar(100) [null]
  sk_date date [null]
  decision_note text [null]
  version int [not null, default: 1]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

Table staff_rank_histories {
  id bigint [pk, increment]
  employee_id bigint [not null, ref: > employees.id]
  rank_grade varchar(20) [not null]
  functional_position varchar(100) [null]
  status varchar(12) [not null, default: 'diajukan']
  submitted_at datetime [not null]
  approver_id bigint [null, ref: > employees.id]
  decided_at datetime [null]
  effective_date date [null]
  sk_number varchar(100) [null]
  sk_date date [null]
  decision_note text [null]
  version int [not null, default: 1]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

Table study_leaves {
  id bigint [pk, increment]
  employee_id bigint [not null, ref: > employees.id]
  degree_level varchar(5) [not null, default: 'S3']
  university varchar(150) [not null]
  study_program varchar(150) [not null]
  start_date date [not null]
  expected_end_date date [null]
  actual_end_date date [null]
  status varchar(15) [not null, default: 'direncanakan']
  scholarship_name varchar(150) [null]
  scholarship_status varchar(10) [null, default: 'belum']
  mentoring_notes text [null]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

Table certifications {
  id bigint [pk, increment]
  employee_id bigint [not null, ref: > employees.id]
  cert_type varchar(50) [not null]
  cert_number varchar(50) [unique, null]
  submitted_date date [not null]
  result_date date [null]
  status varchar(12) [not null, default: 'diajukan']
  certificate_path varchar(255) [null]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

Table trainings {
  id bigint [pk, increment]
  employee_id bigint [not null, ref: > employees.id]
  retirement_id bigint [null, ref: > retirements.id]
  title varchar(200) [not null]
  training_type varchar(15) [not null]
  organizer varchar(150) [null]
  start_date date [not null]
  end_date date [not null]
  duration_hours smallint [null]
  status varchar(12) [not null, default: 'dijadwalkan']
  certificate_number varchar(100) [null]
  certificate_path varchar(255) [null]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

// ==========================================
// E. KINERJA
// ==========================================
Table workload_reports {
  id bigint [pk, increment]
  employee_id bigint [not null, ref: > employees.id]
  academic_year varchar(9) [not null]
  semester varchar(6) [not null]
  teaching_credit decimal(5,2) [not null, default: 0]
  research_credit decimal(5,2) [not null, default: 0]
  service_credit decimal(5,2) [not null, default: 0]
  support_credit decimal(5,2) [not null, default: 0]
  total_credit decimal(6,2) [not null, note: 'GENERATED STORED']
  status varchar(10) [not null, default: 'draf']
  validator_id bigint [null, ref: > employees.id]
  validated_at datetime [null]
  version smallint [not null, default: 1]
  is_current boolean [not null, default: true]
  revision_reason text [null]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]

  Indexes {
    (employee_id, academic_year, semester, version) [unique]
  }
}

Table performance_targets {
  id bigint [pk, increment]
  employee_id bigint [not null, ref: > employees.id]
  period_label varchar(20) [not null]
  period_start date [not null]
  period_end date [not null]
  appraiser_id bigint [null, ref: > employees.id]
  status varchar(10) [not null, default: 'draf']
  final_score decimal(5,2) [null]
  rating varchar(30) [null]
  approved_at datetime [null]
  version int [not null, default: 1]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

Table performance_items {
  id bigint [pk, increment]
  target_id bigint [not null, ref: > performance_targets.id]
  description text [not null]
  unit varchar(30) [not null]
  target_value decimal(10,2) [not null]
  realization_value decimal(10,2) [null]
  weight decimal(5,2) [not null]
  score decimal(5,2) [null]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

// ==========================================
// F. KESEJAHTERAAN DAN PENSIUN
// ==========================================
Table bpjs_memberships {
  id bigint [pk, increment]
  employee_id bigint [not null, ref: > employees.id]
  bpjs_type varchar(20) [not null]
  membership_number varchar(30) [not null, unique]
  registered_date date [not null]
  status varchar(10) [not null, default: 'aktif']
  end_date date [null]
  archived_at datetime [null]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

Table allowance_types {
  id bigint [pk, increment]
  code varchar(20) [not null, unique]
  name varchar(100) [not null]
  requires_doctorate boolean [not null, default: false]
  period_type varchar(10) [not null]
  description text [null]
  is_active boolean [not null, default: true]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

Table allowance_requests {
  id bigint [pk, increment]
  employee_id bigint [not null, ref: > employees.id]
  allowance_type_id bigint [not null, ref: > allowance_types.id]
  period_label varchar(20) [not null]
  amount decimal(14,2) [null]
  reason text [null]
  status varchar(12) [not null, default: 'diajukan']
  approver_id bigint [not null, ref: > employees.id]
  submitted_at datetime [not null]
  decided_at datetime [null]
  decision_note text [null]
  version int [not null, default: 1]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

Table retirements {
  id bigint [pk, increment]
  employee_id bigint [not null, ref: > employees.id]
  retirement_age smallint [not null]
  planned_date date [not null]
  proposed_at datetime [not null]
  status varchar(12) [not null, default: 'diusulkan']
  processed_by bigint [null, ref: > users.id]
  sk_number varchar(100) [null]
  sk_date date [null]
  completed_at datetime [null]
  notes text [null]
  version int [not null, default: 1]
  created_at datetime [not null]
  updated_at datetime [not null]
  deleted_at datetime [null]
}

// ==========================================
// G. AUDIT
// ==========================================
Table activity_logs {
  id bigint [pk, increment]
  actor_id bigint [null, ref: > users.id]
  action varchar(30) [not null]
  entity_type varchar(50) [not null]
  entity_id bigint [null]
  old_status varchar(30) [null]
  new_status varchar(30) [null]
  reason text [null]
  details json [null]
  ip_address varchar(45) [null]
  created_at datetime [not null]
}
```

---

## 4. Rancangan Fisik Basis Data MySQL 8.4 LTS (InnoDB)

### 4.1 Urutan Eksekusi Migration Laravel (Bebas Konflik Foreign Key)

Terdapat ketergantungan melingkar antara `users` $\leftrightarrow$ `employees` $\leftrightarrow$ `applications` $\leftrightarrow$ `vacancies` $\leftrightarrow$ `users` serta referensi silang `trainings` $\rightarrow$ `retirements`. Agar `php artisan migrate:fresh` berjalan mulus dalam satu eksekusi, urutan pembuatan file migration dikunci sebagai berikut:

| Urutan | Nama File Migration | Tabel yang Dibuat / Dimodifikasi | Ketergantungan FK yang Dipasang |
|---:|---|---|---|
| 01 | `0001_create_departments_table.php` | `departments` | `parent_id` $\rightarrow$ `departments.id` |
| 02 | `0002_create_roles_table.php` | `roles` | - |
| 03 | `0003_create_leave_types_table.php` | `leave_types` | - |
| 04 | `0004_create_allowance_types_table.php` | `allowance_types` | - |
| 05 | `0005_create_applicants_table.php` | `applicants` | - |
| 06 | `0006_create_users_table.php` | `users` | Dibuat dengan kolom `employee_id` **tanpa** constraint FK terlebih dahulu. |
| 07 | `0007_create_vacancies_table.php` | `vacancies` | `unit_id` $\rightarrow$ `departments.id`, `created_by` $\rightarrow$ `users.id` |
| 08 | `0008_create_applications_table.php` | `applications` | `vacancy_id` $\rightarrow$ `vacancies.id`, `applicant_id` $\rightarrow$ `applicants.id` |
| 09 | `0009_create_selection_stages_table.php` | `selection_stages` | `application_id` $\rightarrow$ `applications.id`, `assessed_by` $\rightarrow$ `users.id` |
| 10 | `0010_create_employees_table.php` | `employees` + Alter `users` | `unit_id`, `supervisor_id`, `application_id`; lalu pasang FK `users.employee_id` $\rightarrow$ `employees.id`. |
| 11 | `0011_create_employee_assignments_table.php` | `employee_assignments` | `employee_id`, `unit_id`, `supervisor_id` |
| 12 | `0012_create_user_roles_table.php` | `user_roles` | `user_id`, `role_id`, `unit_id` + Functional Unique Index |
| 13 | `0013_create_leave_balances_table.php` | `leave_balances` | `employee_id`, `leave_type_id` |
| 14 | `0014_create_leave_requests_table.php` | `leave_requests` | `employee_id`, `leave_type_id`, `approver_id` |
| 15 | `0015_create_overtime_requests_table.php` | `overtime_requests` | `employee_id`, `approver_id` |
| 16 | `0016_create_work_schedules_table.php` | `work_schedules` | `unit_id`, `created_by` |
| 17 | `0017_create_work_schedule_days_table.php` | `work_schedule_days` | `schedule_id` |
| 18 | `0018_create_academic_rank_histories_table.php` | `academic_rank_histories` | `employee_id`, `approver_id` |
| 19 | `0019_create_staff_rank_histories_table.php` | `staff_rank_histories` | `employee_id`, `approver_id` |
| 20 | `0020_create_study_leaves_table.php` | `study_leaves` | `employee_id` |
| 21 | `0021_create_certifications_table.php` | `certifications` | `employee_id` |
| 22 | `0022_create_retirements_table.php` | `retirements` | `employee_id`, `processed_by` (dibuat sebelum `trainings`) |
| 23 | `0023_create_trainings_table.php` | `trainings` | `employee_id`, `retirement_id` $\rightarrow$ `retirements.id` |
| 24 | `0024_create_workload_reports_table.php` | `workload_reports` | `employee_id`, `validator_id` |
| 25 | `0025_create_performance_targets_table.php` | `performance_targets` | `employee_id`, `appraiser_id` |
| 26 | `0026_create_performance_items_table.php` | `performance_items` | `target_id` $\rightarrow$ `performance_targets.id` |
| 27 | `0027_create_bpjs_memberships_table.php` | `bpjs_memberships` | `employee_id` |
| 28 | `0028_create_allowance_requests_table.php` | `allowance_requests` | `employee_id`, `allowance_type_id`, `approver_id` |
| 29 | `0029_create_activity_logs_table.php` | `activity_logs` | `actor_id` $\rightarrow$ `users.id` |
| 30 | `0030_create_database_triggers.php` | Triggers MySQL 8.4 | Trigger `RULE-13` (Append-Only Audit) & `RULE-14` (Self-Approval Guard) |

### 4.2 Spesifikasi Trigger MySQL 8.4 (`RULE-13` dan `RULE-14`)

Karena MySQL 8.4 InnoDB melarang `CHECK` constraint merujuk pada kolom yang memiliki `FOREIGN KEY` (`Error 3823`), penegakan `RULE-14` (larangan menyetujui/menilai diri sendiri) dan `RULE-13` (proteksi *append-only* `activity_logs`) diimplementasikan dengan Trigger pada migration ke-30:

```sql
-- =====================================================================
-- 1. PROTEKSI APPEND-ONLY PADA ACTIVITY_LOGS (RULE-12 & RULE-13)
-- =====================================================================
CREATE TRIGGER trg_activity_logs_prevent_update
BEFORE UPDATE ON activity_logs
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'RULE-13 VIOLATION: Tabel activity_logs bersifat append-only dan tidak dapat diubah.';
END;

CREATE TRIGGER trg_activity_logs_prevent_delete
BEFORE DELETE ON activity_logs
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'RULE-13 VIOLATION: Tabel activity_logs bersifat append-only dan tidak dapat dihapus.';
END;

-- =====================================================================
-- 2. PENCEGAHAN SELF-SUPERVISION & SELF-APPROVAL (RULE-14)
-- =====================================================================
-- Pola Trigger untuk leave_requests (diaplikasikan identik pada overtime_requests,
-- allowance_requests, academic_rank_histories, dan staff_rank_histories)
CREATE TRIGGER trg_leave_requests_self_approval_ins
BEFORE INSERT ON leave_requests
FOR EACH ROW
BEGIN
    IF NEW.approver_id IS NOT NULL AND NEW.employee_id = NEW.approver_id THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'RULE-14 VIOLATION: Pemohon tidak boleh menjadi approver bagi pengajuannya sendiri.';
    END IF;
END;

CREATE TRIGGER trg_leave_requests_self_approval_upd
BEFORE UPDATE ON leave_requests
FOR EACH ROW
BEGIN
    IF NEW.approver_id IS NOT NULL AND NEW.employee_id = NEW.approver_id THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'RULE-14 VIOLATION: Pemohon tidak boleh menjadi approver bagi pengajuannya sendiri.';
    END IF;
END;

-- Pola Trigger untuk workload_reports (validator_id <> employee_id)
CREATE TRIGGER trg_workload_reports_self_validation_ins
BEFORE INSERT ON workload_reports
FOR EACH ROW
BEGIN
    IF NEW.validator_id IS NOT NULL AND NEW.employee_id = NEW.validator_id THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'RULE-14 VIOLATION: Dosen tidak boleh memvalidasi BKD/LKD miliknya sendiri.';
    END IF;
END;

-- Pola Trigger untuk performance_targets (appraiser_id <> employee_id)
CREATE TRIGGER trg_perf_targets_self_appraisal_ins
BEFORE INSERT ON performance_targets
FOR EACH ROW
BEGIN
    IF NEW.appraiser_id IS NOT NULL AND NEW.employee_id = NEW.appraiser_id THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'RULE-14 VIOLATION: Pegawai tidak boleh menjadi penilai SKP miliknya sendiri.';
    END IF;
END;
```

---

## 5. Rancangan Logika Domain, State Machine & Konkurensi

### 5.1 Spesifikasi State Machine per Modul (`RULE-11`)

Seluruh perubahan status dikendalikan oleh kelas *State Machine* di lapisan Service. Transisi di luar panah di bawah ini akan melempar `BusinessRuleViolationException`:

1. **Pengajuan Umum (`leave_requests`, `overtime_requests`, `allowance_requests`, `academic_rank_histories`, `staff_rank_histories`):**
   * $\text{diajukan} \xrightarrow{\text{approve() oleh Approver}} \text{disetujui}$ *(Terminal)*
   * $\text{diajukan} \xrightarrow{\text{reject(decision\_note) oleh Approver}} \text{ditolak}$ *(Terminal - wajib alasan)*
   * $\text{diajukan} \xrightarrow{\text{cancel(decision\_note) oleh Pemohon}} \text{dibatalkan}$ *(Terminal - wajib alasan)*
2. **Lowongan & Pendaftaran (`vacancies` & `applications`):**
   * Lowongan: $\text{draf} \rightarrow \text{dibuka} \rightarrow \text{ditutup}$ *(Terminal)*
   * Pendaftaran: $\text{diajukan} \rightarrow \text{proses} \rightarrow \{\text{diterima} \mid \text{ditolak}\}$ *(Terminal)*
3. **Beban Kerja Dosen (`workload_reports` - `RULE-07`):**
   * $\text{draf} \rightarrow \text{diajukan} \xrightarrow{\text{validate()}} \text{final}$ *(Immutable)*
   * Jika baris `final` direvisi: baris lama diubah menjadi `is_current = 0`, lalu sistem melakukan `INSERT` baris baru dengan `version = old.version + 1`, `is_current = 1`, `status = 'draf'` (atau `'diajukan'`), dan `revision_reason` wajib diisi.
4. **Sasaran Kinerja Pegawai (`performance_targets` - `RULE-08`):**
   * $\text{draf} \rightarrow \text{diajukan} \rightarrow \{\text{disetujui} \mid \text{ditolak}\}$
   * Kalkulasi skor akhir saat transisi ke `disetujui`:
     $$\text{score}_i = \min\left(\frac{\text{realization\_value}_i}{\text{target\_value}_i} \times 100, 100\right)$$
     $$\text{final\_score} = \sum_{i=1}^{n} \left(\text{score}_i \times \frac{\text{weight}_i}{100}\right), \quad \text{dengan syarat } \sum_{i=1}^{n} \text{weight}_i = 100$$
5. **Proses Pensiun (`retirements` - `RULE-10` & `EX-09`):**
   * $\text{diusulkan} \rightarrow \text{diproses} \rightarrow \{\text{selesai} \mid \text{dibatalkan}\}$

### 5.2 Desain Transaksi & Konkurensi: Persetujuan Cuti (`RULE-03`, `RULE-14`, `RULE-17`, `EX-01` s.d. `EX-04`)

Berikut adalah rancangan alur *Pessimistic Mutex* + *Optimistic Locking* pada `LeaveRequestService::approve()`:

```php
public function approve(int $leaveRequestId, int $expectedVersion, User $actor, ?string $note = null): LeaveRequest
{
    return DB::transaction(function () use ($leaveRequestId, $expectedVersion, $actor, $note) {
        $leaveRequest = LeaveRequest::findOrFail($leaveRequestId);

        // 1. Pencegahan Self-Approval (RULE-14 & EX-03)
        if ($actor->employee_id === $leaveRequest->employee_id) {
            $this->auditLog->logAccessDenied($actor, 'leave_requests', $leaveRequest->id, 'Percobaan menyetujui cuti sendiri.');
            throw new UnauthorizedActionException('Pemohon tidak dapat menyetujui pengajuannya sendiri.');
        }

        // 2. Pessimistic Lock pada baris Induk Pegawai sebagai Mutex anti-Phantom Read (EX-02)
        DB::table('employees')->where('id', $leaveRequest->employee_id)->lockForUpdate()->first();

        // 3. Cek Overlap Tanggal dengan Cuti lain yang sudah Disetujui (RULE-03 & EX-02)
        $hasOverlap = LeaveRequest::where('employee_id', $leaveRequest->employee_id)
            ->where('id', '<>', $leaveRequest->id)
            ->where('status', 'disetujui')
            ->where('start_date', '<=', $leaveRequest->end_date)
            ->where('end_date', '>=', $leaveRequest->start_date)
            ->exists();

        if ($hasOverlap) {
            throw new BusinessRuleViolationException('EX-02: Tanggal cuti bertumpuk dengan cuti lain yang telah disetujui.');
        }

        // 4. Pessimistic Lock pada Saldo Cuti (RULE-03 & EX-01)
        $year = (int) Carbon::parse($leaveRequest->start_date)->format('Y');
        $balance = LeaveBalance::where('employee_id', $leaveRequest->employee_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('year', $year)
            ->lockForUpdate()
            ->firstOrFail();

        $remainingQuota = $balance->quota_days - $balance->used_days;
        if ($leaveRequest->total_days > $remainingQuota) {
            throw new BusinessRuleViolationException("EX-01: Saldo cuti tidak mencukupi. Sisa saldo: {$remainingQuota} hari.");
        }

        // 5. Optimistic Locking Update pada Pengajuan Cuti (RULE-17 & EX-04)
        $updatedRows = LeaveRequest::where('id', $leaveRequest->id)
            ->where('version', $expectedVersion)
            ->where('status', 'diajukan')
            ->update([
                'status'        => 'disetujui',
                'decided_at'    => now(),
                'decision_note' => $note,
                'version'       => $expectedVersion + 1,
                'updated_at'    => now(),
            ]);

        if ($updatedRows === 0) {
            throw new ConcurrencyConflictException('EX-04: Pengajuan telah diproses oleh pengguna lain. Silakan muat ulang data.');
        }

        // 6. Pemotongan Saldo Cuti dengan Increment Version
        $balance->increment('used_days', $leaveRequest->total_days, [
            'version' => $balance->version + 1,
        ]);

        // 7. Catat Activity Log dalam Transaksi yang Sama (RULE-12)
        $this->auditLog->record(
            actor: $actor,
            action: 'APPROVE',
            entityType: 'leave_requests',
            entityId: $leaveRequest->id,
            oldStatus: 'diajukan',
            newStatus: 'disetujui',
            reason: $note
        );

        return $leaveRequest->fresh();
    });
}
```

### 5.3 Desain Guard Penyelesaian Pensiun (`RULE-10` & `EX-09`)

Sebelum status pensiun diubah menjadi `selesai` dan `employees.employment_status` diubah menjadi `pensiun`, `RetirementProcessService::complete()` wajib memastikan tidak ada transaksi gantung:

```php
public function assertNoActiveRequests(int $employeeId): void
{
    $pendingCount = 0
        + LeaveRequest::where('employee_id', $employeeId)->where('status', 'diajukan')->count()
        + OvertimeRequest::where('employee_id', $employeeId)->where('status', 'diajukan')->count()
        + AllowanceRequest::where('employee_id', $employeeId)->where('status', 'diajukan')->count()
        + AcademicRankHistory::where('employee_id', $employeeId)->where('status', 'diajukan')->count()
        + StaffRankHistory::where('employee_id', $employeeId)->where('status', 'diajukan')->count()
        + WorkloadReport::where('employee_id', $employeeId)->where('is_current', true)->whereIn('status', ['draf', 'diajukan'])->count()
        + PerformanceTarget::where('employee_id', $employeeId)->whereIn('status', ['draf', 'diajukan'])->count();

    if ($pendingCount > 0) {
        throw new BusinessRuleViolationException(
            "EX-09: Pegawai masih memiliki {$pendingCount} pengajuan/evaluasi aktif yang belum diselesaikan."
        );
    }
}
```

---

## 6. Rancangan Otorisasi (RBAC), Unit Scoping & Privasi Data

### 6.1 Resolusi Cakupan Unit (`user_roles.unit_id`)

Setiap pengguna dapat memiliki satu atau lebih baris pada `user_roles`. Fungsi *helper* otorisasi pada model `User` menentukan cakupan unit kerja yang dapat diakses:

* Jika pengguna memiliki role dengan `unit_id IS NULL` (misalnya Kepala Biro SDM, Staf BSDM sesuai bidangnya, atau Wakil Rektor untuk laporan agregat), maka cakupan unit bersifat **lintas unit (global)** untuk modul yang diizinkan bagi role tersebut.
* Jika pengguna memiliki role dengan `unit_id = X` (misalnya Atasan Langsung / pimpinan unit), maka Eloquent menerapkan filter:
  ```sql
  WHERE employees.unit_id IN (:allowed_unit_ids) OR employees.supervisor_id = :current_employee_id
  ```
* Setiap penolakan otorisasi di dalam Laravel `Policy` atau `Middleware` ditangkap oleh *Exception Handler* untuk menulis baris `ACCESS_DENIED` ke `activity_logs` melalui koneksi/transaksi mandiri (agar tidak ikut ter-*rollback* bersama transaksi bisnis yang gagal).

### 6.2 Masking Data Sensitif (`RULE-15`)

Pada *Resource/Transformer* API maupun *Blade View*, atribut sensitif dilindungi secara default:
* `bpjs_memberships.membership_number`: Ditampilkan sebagai `********1234` (hanya 4 digit terakhir) kecuali pengguna memiliki izin `view-sensitive-welfare` (Petugas BSDM pengelola BPJS atau pemilik data sendiri).
* `employees.address` dan `employees.phone`: Disembunyikan dari direktori rekan kerja lintas unit.
* Wakil Rektor Bidang SDM hanya menerima respons agregat (`COUNT`, `AVG`, `SUM`) pada modul laporan tanpa daftar rincian NIK/nomor BPJS individu.

---

## 7. Rancangan SQL Query untuk 6 Laporan Utama (`RPT-01` s.d. `RPT-06`)

Berikut adalah rancangan query SQL MySQL 8.4 yang dioptimalkan menggunakan *Composite Index* yang telah didefinisikan pada PRD:

### 7.1 `RPT-01`: Recruitment Dashboard
Menampilkan jumlah lowongan, total pelamar, pelamar dalam proses, dan kandidat diterima per unit dan kategori:

```sql
SELECT 
    d.name AS unit_name,
    v.category,
    COUNT(DISTINCT v.id) AS total_vacancies,
    COUNT(a.id) AS total_applications,
    SUM(CASE WHEN a.status = 'proses' THEN 1 ELSE 0 END) AS in_progress_count,
    SUM(CASE WHEN a.status = 'diterima' THEN 1 ELSE 0 END) AS accepted_count,
    SUM(CASE WHEN a.status = 'ditolak' THEN 1 ELSE 0 END) AS rejected_count
FROM vacancies v
INNER JOIN departments d ON d.id = v.unit_id
LEFT JOIN applications a ON a.vacancy_id = v.id AND a.deleted_at IS NULL
WHERE v.deleted_at IS NULL
  AND v.open_date >= :period_start
  AND v.close_date <= :period_end
  AND (:unit_id IS NULL OR v.unit_id = :unit_id)
  AND (:category IS NULL OR v.category = :category)
GROUP BY d.id, d.name, v.category
ORDER BY d.name ASC, v.category ASC;
```

### 7.2 `RPT-02`: SKP Report
Menampilkan rekapitulasi capaian SKP pegawai per unit dan periode evaluasi:

```sql
SELECT 
    d.name AS unit_name,
    e.category,
    pt.period_label,
    COUNT(pt.id) AS total_skp,
    SUM(CASE WHEN pt.status = 'disetujui' THEN 1 ELSE 0 END) AS approved_skp,
    ROUND(AVG(CASE WHEN pt.status = 'disetujui' THEN pt.final_score END), 2) AS avg_final_score,
    MIN(CASE WHEN pt.status = 'disetujui' THEN pt.final_score END) AS min_final_score,
    MAX(CASE WHEN pt.status = 'disetujui' THEN pt.final_score END) AS max_final_score
FROM performance_targets pt
INNER JOIN employees e ON e.id = pt.employee_id AND e.deleted_at IS NULL
INNER JOIN departments d ON d.id = e.unit_id
WHERE pt.deleted_at IS NULL
  AND pt.period_label = :period_label
  AND (:unit_id IS NULL OR e.unit_id = :unit_id)
  AND (:category IS NULL OR e.category = :category)
GROUP BY d.id, d.name, e.category, pt.period_label
ORDER BY avg_final_score DESC;
```

### 7.3 `RPT-03`: Training, Certification & Study Report
Menampilkan ringkasan partisipasi pelatihan, kelulusan sertifikasi, dan studi lanjut S3 per unit kerja:

```sql
SELECT 
    d.name AS unit_name,
    e.category,
    COUNT(DISTINCT e.id) AS active_employees,
    COUNT(DISTINCT CASE WHEN t.status = 'selesai' THEN t.id END) AS completed_trainings,
    COUNT(DISTINCT CASE WHEN c.status = 'lulus' THEN c.id END) AS passed_certifications,
    COUNT(DISTINCT CASE WHEN sl.status IN ('berjalan', 'selesai') THEN sl.id END) AS s3_study_leaves
FROM employees e
INNER JOIN departments d ON d.id = e.unit_id
LEFT JOIN trainings t 
    ON t.employee_id = e.id AND t.deleted_at IS NULL 
    AND t.start_date BETWEEN :period_start AND :period_end
LEFT JOIN certifications c 
    ON c.employee_id = e.id AND c.deleted_at IS NULL 
    AND c.submitted_date BETWEEN :period_start AND :period_end
LEFT JOIN study_leaves sl 
    ON sl.employee_id = e.id AND sl.deleted_at IS NULL 
    AND sl.start_date <= :period_end
WHERE e.deleted_at IS NULL
  AND e.employment_status = 'aktif'
  AND (:unit_id IS NULL OR e.unit_id = :unit_id)
GROUP BY d.id, d.name, e.category
ORDER BY d.name ASC;
```

### 7.4 `RPT-04`: Leave & Overtime Recap
Menampilkan rekap hari cuti disetujui dan total jam lembur disetujui per unit dan periode:

```sql
SELECT 
    d.name AS unit_name,
    e.category,
    COALESCE(lv.approved_leave_requests, 0) AS approved_leave_requests,
    COALESCE(lv.total_leave_days, 0) AS total_leave_days,
    COALESCE(ot.approved_overtime_requests, 0) AS approved_overtime_requests,
    COALESCE(ot.total_overtime_hours, 0.00) AS total_overtime_hours
FROM departments d
INNER JOIN employees e ON e.unit_id = d.id AND e.deleted_at IS NULL
LEFT JOIN (
    SELECT employee_id, COUNT(id) AS approved_leave_requests, SUM(total_days) AS total_leave_days
    FROM leave_requests
    WHERE status = 'disetujui' AND deleted_at IS NULL
      AND start_date >= :period_start AND end_date <= :period_end
    GROUP BY employee_id
) lv ON lv.employee_id = e.id
LEFT JOIN (
    SELECT employee_id, COUNT(id) AS approved_overtime_requests, SUM(total_hours) AS total_overtime_hours
    FROM overtime_requests
    WHERE status = 'disetujui' AND deleted_at IS NULL
      AND work_date BETWEEN :period_start AND :period_end
    GROUP BY employee_id
) ot ON ot.employee_id = e.id
WHERE d.deleted_at IS NULL
  AND (:unit_id IS NULL OR d.id = :unit_id)
GROUP BY d.id, d.name, e.category, lv.approved_leave_requests, lv.total_leave_days, ot.approved_overtime_requests, ot.total_overtime_hours
ORDER BY d.name ASC;
```

### 7.5 `RPT-05`: Upcoming Retirement Report
Menampilkan pegawai yang mendekati masa pensiun beserta status kesiapan pelatihan pra-pensiunnya:

```sql
SELECT 
    e.nip,
    e.full_name,
    e.category,
    d.name AS unit_name,
    r.retirement_age,
    r.planned_date,
    r.status AS retirement_status,
    COUNT(t.id) AS pre_retirement_trainings_count
FROM retirements r
INNER JOIN employees e ON e.id = r.employee_id AND e.deleted_at IS NULL
INNER JOIN departments d ON d.id = e.unit_id
LEFT JOIN trainings t 
    ON t.retirement_id = r.id 
    AND t.training_type = 'pra_pensiun' 
    AND t.deleted_at IS NULL
WHERE r.deleted_at IS NULL
  AND r.planned_date BETWEEN :start_date AND :end_date
  AND (:unit_id IS NULL OR e.unit_id = :unit_id)
  AND (:status IS NULL OR r.status = :status)
GROUP BY r.id, e.nip, e.full_name, e.category, d.name, r.retirement_age, r.planned_date, r.status
ORDER BY r.planned_date ASC;
```

### 7.6 `RPT-06`: Workforce Composition Report
Menampilkan komposisi pegawai aktif per unit, kategori, pendidikan terakhir, serta jabatan fungsional/kepangkatan terakhir yang telah disetujui:

```sql
SELECT 
    d.name AS unit_name,
    e.category,
    e.highest_education,
    COALESCE(arh.rank_name, srh.rank_grade, 'Belum Ditentukan') AS current_rank_or_grade,
    COUNT(e.id) AS employee_count
FROM employees e
INNER JOIN departments d ON d.id = e.unit_id
LEFT JOIN academic_rank_histories arh ON arh.id = (
    SELECT id FROM academic_rank_histories a2
    WHERE a2.employee_id = e.id AND a2.status = 'disetujui' AND a2.deleted_at IS NULL
    ORDER BY a2.effective_date DESC, a2.id DESC LIMIT 1
)
LEFT JOIN staff_rank_histories srh ON srh.id = (
    SELECT id FROM staff_rank_histories s2
    WHERE s2.employee_id = e.id AND s2.status = 'disetujui' AND s2.deleted_at IS NULL
    ORDER BY s2.effective_date DESC, s2.id DESC LIMIT 1
)
WHERE e.deleted_at IS NULL
  AND e.employment_status = 'aktif'
  AND (:unit_id IS NULL OR e.unit_id = :unit_id)
  AND (:category IS NULL OR e.category = :category)
GROUP BY d.id, d.name, e.category, e.highest_education, current_rank_or_grade
ORDER BY d.name ASC, e.category ASC, employee_count DESC;
```

---

## 8. Strategi Seeding Data Sintetis, Indexing & Benchmark (`NFR-04`)

### 8.1 Rancangan Synthetic Data Seeder (Skala 5 Tahun+)
Sesuai batasan BRD Bagian 14.d ("Data uji digenerate dalam jumlah lebih besar dari estimasi untuk latihan evaluasi performa query dan indexing"), `DatabaseSeeder` dirancang menggunakan *batch insert* (`DB::table(...)->insert($chunks)`) dengan profil volume uji benchmark minimal setara atau melebihi proyeksi kumulatif 5 tahun:

| Tabel | Proyeksi 5 Tahun (BRD) | Target Baris Seeder Benchmark | Metode Generasi di Laravel |
|---|---:|---:|---|
| `departments` | $\approx 35$ | $50$ | Hierarki Biro $\rightarrow$ Bidang $\rightarrow$ Urusan $\rightarrow$ Fakultas/Prodi. |
| `employees` | $1.659$ | $2.500$ | $1.500$ Dosen + $1.000$ Tendik (data Faker Indonesia sintetis). |
| `vacancies` & `applications` | $220$ & $11.100$ | $300$ & $15.000$ | Distribusi status lolos/gugur + tahapan seleksi (`45.000` baris `selection_stages`). |
| `leave_requests` | $21.500$ | $30.000$ | Rentang tanggal tidak tumpang tindih untuk status `disetujui`. |
| `overtime_requests` | $66.300$ | $80.000$ | Mayoritas pada Tendik dengan `end_time > start_time`. |
| `workload_reports` | $8.800$ | $12.000$ | $10$ semester $\times$ Dosen, termasuk sampel revisi `version = 2`. |
| `performance_targets` & `items` | $14.400$ | $20.000$ & $80.000$ | $4$ butir `performance_items` per dokumen SKP. |
| `activity_logs` | $391.200$ | $500.000$ | *Chunked bulk insert* ($5.000$ baris per batch) agar seeding selesai $< 60\text{ detik}$. |

### 8.2 Metodologi Verifikasi Performa (`EXPLAIN ANALYZE`)
Performance Engineer menjalankan protokol uji berikut pada MySQL 8.4 sebelum rilis:
1. Menjalankan `ANALYZE TABLE` pada seluruh 29 tabel setelah proses *seeding* selesai untuk memperbarui statistik kardinalitas index InnoDB.
2. Mengeksekusi `EXPLAIN ANALYZE` pada query pencarian pegawai, daftar pengajuan per atasan, dan ke-6 query laporan (`RPT-01` s.d. `RPT-06`).
3. **Kriteria Lulus:**
   * Waktu eksekusi aktual (`actual time`) untuk pencarian pegawai dan daftar pengajuan $\le 200\text{ ms}$ di sisi database (sehingga total *response time* HTTP tetap jauh di bawah batas $\le 2.000\text{ ms}$ pada `NFR-04`).
   * Tidak ada *Full Table Scan* (`type: ALL`) pada tabel besar (`activity_logs`, `overtime_requests`, `leave_requests`, `applications`, `employees`).

---

## 9. Rancangan Backup, Recovery, dan Retensi Data (`NFR-05` & `NFR-08`)

### 9.1 Prosedur Backup Harian MySQL 8.4
Backup harian dijalankan melalui *cron job* menggunakan utilitas `mysqldump` dengan flag konsistensi transaksi InnoDB tanpa mengunci tabel produksi:

```bash
mysqldump \
  --host=127.0.0.1 --port=3306 --user=hris_backup \
  --single-transaction \
  --routines \
  --triggers \
  --events \
  --default-character-set=utf8mb4 \
  antech_async_hris | gzip > /var/backups/mysql/hris_$(date +\%F_\%H\%M).sql.gz
```

* **Uji Pemulihan (*Restore Drill*):** Dilakukan secara berkala ke database terpisah `antech_async_hris_restore_test` lalu diverifikasi jumlah baris dan keutuhan *Trigger* serta *Foreign Key*-nya.

### 9.2 Arsitektur Retensi & Pengarsipan Otomatis
Sesuai tabel retensi BRD Bagian 9 (dan menunggu penetapan final Q-03 & Q-09):
* **Pelamar Ditolak (Usulan 2 Tahun):** Command terjadwal `hris:archive-rejected-applicants` menandai (*soft delete*) baris `applications` yang berstatus `ditolak` dengan `decided_at < NOW() - INTERVAL 2 YEAR` beserta berkas CV fisiknya apabila pelamar tersebut tidak memiliki pendaftaran lain yang berstatus `diterima`.
* **Lowongan & Lembur (Usulan 5 Tahun):** Data tetap dipertahankan secara historis di tabel utama dengan memanfaatkan index partisi/tanggal agar tidak membebani query tahun berjalan.
* **Kepesertaan BPJS Nonaktif:** Saat status kepesertaan diubah menjadi `nonaktif`, sistem otomatis mengisi `end_date` dan `archived_at = NOW()`, sehingga *virtual generated column* `active_employee_id` bernilai `NULL` dan membuka ruang bagi nomor kepesertaan baru bila diperlukan.

---

## 10. Riwayat Perubahan Dokumen

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0 | 17 November 2026 | Dokumen SDD pertama disusun berdasarkan BRD v1.1 dan PRD v1.1 untuk target implementasi Laravel & MySQL 8.4 LTS (InnoDB). |
---

## 11. Design System & UI Guidelines

Sistem menggunakan kerangka visual dengan kontras jelas dan umpan balik langsung berbasis Livewire atau Alpine.js.

### 11.1 Tipografi & Warna

- **Font:** Inter atau System UI Sans-serif.
- **Brand Primary:** `#2C3E50` (Deep Navy) untuk Sidebar, Header, dan Primary Button.
- **Brand Secondary:** `#E67E22` (UAD Orange) untuk aksen dan indikator khusus.
- **Background:** `#F8FAFC` (Off-white).
- **Teks:** `#1E293B` (Slate 800) untuk konten utama, `#64748B` (Slate 500) untuk label pendukung.

### 11.2 Komponen UI

- **Button Primary:** `#2C3E50` dengan teks putih.
- **Button Secondary:** Border `#CBD5E1` tanpa latar.
- **Button Danger:** `#E11D48` (Soft Red) untuk pembatalan dan penolakan.
- **Card:** Border `#E2E8F0`, sudut 8px, tanpa bayangan tebal.
- **Table:** Baris selang-seling bergaris tipis. Status pengajuan memakai badge (misal: Disetujui = latar `#DCFCE7`, teks `#166534`).

### 11.3 Layout & Navigasi

- **Sidebar:** Tetap di sisi kiri dengan ikon Heroicons atau Lucide. Pada layar seluler, sidebar terlipat menjadi menu burger.
- **Dashboard:** Grid 3 kolom untuk ringkasan angka di bagian atas, diikuti tabel transaksi aktif di bawahnya.

### 11.4 Pola Interaksi Real-Time

- **Live Filter:** Pencarian nama dan filter unit memperbarui tabel tanpa muat ulang halaman (debounce 300ms).
- **Progress Stepper:** Visualisasi alur rekrutmen dan tahapan jabatan. Tahap selesai berwarna hijau, tahap aktif berkedip halus, tahap berikutnya berwarna abu-abu.
- **Side-Drawer:** Detail riwayat pegawai dan log aktivitas muncul dari panel kanan tanpa berpindah dari daftar utama.
- **Modal Validation:** Transisi status `ditolak` atau `dibatalkan` mewajibkan pengisian alasan pada pop-up modal sebelum data terkirim.
- **Inline Feedback:** Form menampilkan pesan kesalahan langsung di bawah bidang input saat pengetikan, tombol simpan menampilkan animasi spinner, dan hasil aksi muncul lewat notifikasi *toast* di kanan atas.
- **Drill-Down Graph:** Grafik ApexCharts di dashboard dapat diklik untuk memfilter tabel rincian di bawahnya.

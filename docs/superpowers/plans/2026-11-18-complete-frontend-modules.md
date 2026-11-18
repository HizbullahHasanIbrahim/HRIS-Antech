# Full-Stack Frontend Modules (Organization, Performance & Career, Reports) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement interactive Inertia.js v2 + Vue 3 pages and Web controllers for Organization, Performance & Career, and Executive Reports (RPT-01 to RPT-06) with charts and CSV exports.

**Architecture:** Thin Web Controllers in `app/Http/Controllers/Web/` query Eloquent models with RBAC & Unit Scoping (`RULE-15`), returning typed Inertia props to Vue 3 pages (`resources/js/Pages/`). State mutations leverage existing API endpoints with optimistic locking (`RULE-17`) and `ReasonModal` (`RULE-12`).

**Tech Stack:** Laravel 13, Inertia.js v2, Vue 3 Composition API (`<script setup lang="ts">`), Tailwind CSS v4, Lucide icons, ApexCharts (`vue3-apexcharts`).

**Spec:** `PRD.md` (v1.1) and `SDD.md` (v1.0), `ui_design_system_guidelines_antech_async_hris.md`

## Global Constraints
- Strictly MySQL 8.4 LTS InnoDB compatibility
- No hard deletion of business entities (`SoftDeletes` / `RULE-13`)
- Terminal state immutability (`RULE-11`)
- Unit scoping enforcement via `RULE-15`
- Reason required for rejection/cancellation (`RULE-12`)
- Optimistic locking version check (`RULE-17` & `EX-04`)
- Design palette: Navy `#2C3E50`, Orange `#E67E22`, semantic status indicators, `tabular-nums` for metrics

## Review Focus
1. Unit scope leakage: Department or employee data from other units visible to non-admin unit managers (`RULE-15`).
2. Concurrent status transition conflicts: Handling HTTP 409 `CONCURRENCY_CONFLICT` gracefully via `ConflictModal`.
3. BKD/LKD revision workflow: Terminal reports require new version and non-empty `revision_reason` (`RULE-07`).
4. CSV export authentication: Export routes require authenticated session with valid role.
5. Missing employee profile: Handling users without an attached `employee` record without 500 crashes.

---

### Task 1: Module 2 - Organisasi & Pegawai Web Controller & Pages

**Files:**
- Create: `app/Http/Controllers/Web/OrganizationWebController.php`
- Modify: `routes/web.php`
- Modify: `resources/js/Pages/Organization/Index.vue`
- Create: `tests/Feature/WebOrganizationFlowTest.php`

**Interfaces:**
- Consumes: `Department`, `Employee` models, `api/v1/departments`, `api/v1/employees`
- Produces: Inertia render for `Organization/Index` with `departments`, `employees`, `unitFilter`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/WebOrganizationFlowTest.php`:
```php
public function test_authenticated_user_can_view_organization_page(): void
{
    $unit = Department::first() ?? Department::factory()->create();
    $employee = Employee::factory()->create(['unit_id' => $unit->id]);
    $user = User::factory()->create(['employee_id' => $employee->id, 'is_active' => true]);

    $response = $this->actingAs($user)->get('/organization');
    $response->assertStatus(200);
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/WebOrganizationFlowTest.php`

- [ ] **Step 3: Implement `OrganizationWebController` and update `routes/web.php`**

Controller fetches active departments and employees (scoped if not Admin BSDM / Kepala Biro), passing pagination and filters to Inertia.

- [ ] **Step 4: Update `resources/js/Pages/Organization/Index.vue`**

Implement tabs for "Daftar Departemen" and "Data Pegawai", DataTable with search, status filters, and SideDrawer for employee detail view.

- [ ] **Step 5: Run tests and build**

Run: `php artisan test tests/Feature/WebOrganizationFlowTest.php && npm run build`

---

### Task 2: Module 3 - Karir & Kinerja Web Controller & Pages

**Files:**
- Create: `app/Http/Controllers/Web/PerformanceWebController.php`
- Create: `app/Http/Controllers/Web/CareerWebController.php`
- Modify: `routes/web.php`
- Modify: `resources/js/Pages/Performance/Index.vue`
- Modify: `resources/js/Pages/Career/Index.vue`
- Create: `tests/Feature/WebPerformanceCareerFlowTest.php`

**Interfaces:**
- Consumes: `WorkloadReport`, `PerformanceTarget`, `AcademicRankHistory`, `StaffRankHistory`
- Produces: Inertia render for `Performance/Index` and `Career/Index` with reports, targets, and rank histories

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/WebPerformanceCareerFlowTest.php` testing access to `/performance` and `/career`.

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/WebPerformanceCareerFlowTest.php`

- [ ] **Step 3: Implement `PerformanceWebController` and `CareerWebController`**

Query BKD/LKD workload reports and SKP targets in `PerformanceWebController`. Query academic and staff rank histories in `CareerWebController`.

- [ ] **Step 4: Implement Vue pages `Performance/Index.vue` and `Career/Index.vue`**

Add status badges, approval/revision actions, credit totals (`RULE-07`), and rank progression tables.

- [ ] **Step 5: Run tests and build**

Run: `php artisan test tests/Feature/WebPerformanceCareerFlowTest.php && npm run build`

---

### Task 3: Module 4 - Laporan Eksekutif Web Controller & Dashboard

**Files:**
- Create: `app/Http/Controllers/Web/ReportWebController.php`
- Modify: `routes/web.php`
- Modify: `resources/js/Pages/Reports/Index.vue`
- Create: `tests/Feature/WebReportsFlowTest.php`

**Interfaces:**
- Consumes: `ReportingService`, RPT-01 to RPT-06 data methods
- Produces: Inertia render for `Reports/Index` with KPI metrics, summary distributions, and direct CSV download URLs

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/WebReportsFlowTest.php` checking `/reports` view and access to export CSV triggers.

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/WebReportsFlowTest.php`

- [ ] **Step 3: Implement `ReportWebController`**

Aggregate counts: total employees by category, leave & overtime monthly figures, rank distribution, and BKD compliance rates.

- [ ] **Step 4: Update `resources/js/Pages/Reports/Index.vue`**

Add ApexCharts / responsive SVG KPI visualizers, metric cards, tabbed views for RPT-01 through RPT-06, and direct "Unduh CSV" buttons linking to `/api/v1/reports/export/{type}`.

- [ ] **Step 5: Run tests, build, and Pint**

Run: `php artisan test && npm run build && ./vendor/bin/pint --dirty`

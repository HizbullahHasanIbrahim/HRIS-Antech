<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>

---

# Project-Specific Guidelines: Antech Async HRIS (BSDM UAD)

> **IMPORTANT FOR AI AGENTS:** Do NOT remove or overwrite this section when running `php artisan boost:install`. Everything below `</laravel-boost-guidelines>` defines the mandatory domain, architecture, and MySQL 8.4 LTS database rules for **Antech Async - Human Resource Information System (HRIS)** for Biro Sumber Daya Manusia (BSDM), Universitas Ahmad Dahlan.

## 1. Single Source of Truth (Mandatory Reading)

Before generating or modifying any migration, Eloquent model, service, form request, policy, controller, seeder, or test, you **MUST** read and strictly align with:
1. **`PRD.md` (v1.1):** Defines the 17 Business Processes (`P-01` to `P-17`), 12 Core Functional Requirements (`FR-01` to `FR-12`), 16 Technical Requirements (`PRD-FR-01` to `PRD-FR-16`), 17 Business Rules (`RULE-01` to `RULE-17`), 12 Exception Scenarios (`EX-01` to `EX-12`), 6 Reports (`RPT-01` to `RPT-06`), and the 29-entity Data Dictionary.
2. **`SDD.md` (v1.0):** Defines the Layered Architecture, 3NF/BCNF Normalization, Canonical DBML, 30-file Laravel Migration execution sequence, MySQL 8.4 Triggers, State Machine transitions, Pessimistic + Optimistic Locking patterns, RBAC Unit Scoping, and optimized Report SQL queries.

---

## 2. Database & Schema Rules (Strictly MySQL 8.4 LTS InnoDB)

- **DBMS Target:** Strictly **MySQL 8.4 LTS** with storage engine **InnoDB**, charset `utf8mb4`, and collation `utf8mb4_0900_ai_ci`.
- **Forbidden PostgreSQL Syntax:** NEVER use `JSONB`, PostgreSQL partial index syntax (`CREATE UNIQUE INDEX ... WHERE ...`), `SERIAL`, `ILIKE`, or PostgreSQL-specific operators.
- **SQL Mode:** Always assume strict mode (`STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION`).
- **Primary & Foreign Keys:**
  - All primary keys (`id`) and foreign keys MUST use `BIGINT UNSIGNED` (`$table->id()` and `$table->foreignId()`).
  - Default foreign key referential action MUST be `ON DELETE RESTRICT` to protect historical records (`RULE-13`). Never use `ON DELETE CASCADE` on employee or transactional history tables.
  - Canonical table name for organizational units is `departments`, while foreign keys referencing it are named `unit_id` (`->constrained('departments')->restrictOnDelete()`).
- **Actor Reference Convention:**
  - `approver_id`, `appraiser_id`, `validator_id`, and `supervisor_id` reference `employees.id`.
  - `created_by`, `assessed_by`, `processed_by`, and `actor_id` reference `users.id`.
- **Date & Time Types:**
  - Use `DATE` for calendar dates, `TIME` for same-day schedules/overtime, and `DATETIME` for timestamps (stored in UTC, displayed in `Asia/Jakarta`).
  - Do NOT use MySQL `TIMESTAMP` for business/historical columns to avoid the Year 2038 limit (`planned_date`, `completed_at`, long-term retention).
- **Status, Categories & Numbers:**
  - Use `VARCHAR(n)` paired with application Enums/FormRequests and MySQL `CHECK` constraints for status/category columns (avoid native MySQL `ENUM` columns).
  - Use `TINYINT(1)` (`$table->boolean()`) for booleans and `DECIMAL(p,s)` for credits, scores, hours, and currency amounts (never `FLOAT` or `DOUBLE`).
- **Timestamps & Soft Deletes (`RULE-13`):**
  - All 28 business tables (`departments` through `retirements`) MUST have `created_at DATETIME`, `updated_at DATETIME`, and `deleted_at DATETIME NULL` (`SoftDeletes` trait). Hard deleting business records is strictly prohibited.
  - **Exception (`activity_logs`):** Table #29 `activity_logs` is strictly **append-only**. It MUST ONLY have `created_at DATETIME NOT NULL` (NO `updated_at`, NO `deleted_at`).

---

## 3. MySQL 8.4 Virtual Generated Columns, Functional Indexes & Triggers

Because MySQL does not support PostgreSQL-style `WHERE` clauses on unique indexes, enforce conditional uniqueness and integrity using these exact MySQL 8.4 patterns:

1. **Conditional Unique Constraints via `VIRTUAL` Generated Columns (Must Check `deleted_at IS NULL`):**
   - **`bpjs_memberships` (`RULE-16`):** Column name MUST be `membership_number VARCHAR(30) UNIQUE`. Enforce max 1 active BPJS per employee & type via:
     `active_employee_id BIGINT UNSIGNED GENERATED ALWAYS AS (CASE WHEN status = 'aktif' AND deleted_at IS NULL THEN employee_id ELSE NULL END) VIRTUAL` + `UNIQUE(active_employee_id, bpjs_type)`.
   - **`workload_reports` (`RULE-07`):**
     - `total_credit` MUST be `DECIMAL(6,2) GENERATED ALWAYS AS (teaching_credit + research_credit + service_credit + support_credit) STORED`.
     - Enforce 1 current report per semester via `current_employee_id BIGINT UNSIGNED GENERATED ALWAYS AS (CASE WHEN is_current = 1 AND deleted_at IS NULL THEN employee_id ELSE NULL END) VIRTUAL` + `UNIQUE(current_employee_id, academic_year, semester)`, alongside `UNIQUE(employee_id, academic_year, semester, version)`.
   - **`allowance_requests` (`RULE-09` & `RULE-11`):** Allow re-submission if a prior request was `ditolak` or `dibatalkan`:
     `active_allowance_key VARCHAR(64) GENERATED ALWAYS AS (CASE WHEN status NOT IN ('ditolak', 'dibatalkan') AND deleted_at IS NULL THEN CONCAT(employee_id, ':', allowance_type_id, ':', period_label) ELSE NULL END) VIRTUAL` + `UNIQUE(active_allowance_key)`.
   - **`performance_targets` (`RULE-08` & `RULE-11`):** Allow re-submission if `ditolak`:
     `active_target_key VARCHAR(45) GENERATED ALWAYS AS (CASE WHEN status <> 'ditolak' AND deleted_at IS NULL THEN CONCAT(employee_id, ':', period_label) ELSE NULL END) VIRTUAL` + `UNIQUE(active_target_key)`.
   - **`retirements` (`RULE-10`):** Allow re-proposing if a prior proposal was `dibatalkan`:
     `active_retirement_emp_id BIGINT UNSIGNED GENERATED ALWAYS AS (CASE WHEN status <> 'dibatalkan' AND deleted_at IS NULL THEN employee_id ELSE NULL END) VIRTUAL` + `UNIQUE(active_retirement_emp_id)`.
2. **Functional Unique Index on `user_roles`:**
   - Prevent duplicate global role assignments (`unit_id IS NULL`) using:
     `UNIQUE KEY uq_user_role_unit (user_id, role_id, (IFNULL(unit_id, 0)), (IFNULL(deleted_at, '1000-01-01 00:00:00')))`.
3. **Full-Text Search Index (`PRD-FR-01` & `NFR-04`):**
   - Always include `FULLTEXT INDEX ft_employees_full_name (full_name)` on `employees` to support sub-2-second employee name searches.
4. **Database Triggers for `RULE-13` & `RULE-14`:**
   - InnoDB forbids `CHECK (employee_id <> approver_id)` on columns backed by foreign keys (MySQL Error 3823). Always enforce self-approval/self-supervision prevention (`employee_id <> approver_id`, `validator_id`, `appraiser_id`, `supervisor_id`) and `activity_logs` append-only immutability via MySQL `BEFORE INSERT`, `BEFORE UPDATE`, and `BEFORE DELETE` triggers using `SIGNAL SQLSTATE '45000'`.

---

## 4. Exact 30-Step Migration Execution Sequence

To avoid circular foreign key errors (`users` <-> `employees` <-> `applications` <-> `vacancies` and `trainings` -> `retirements`), ALWAYS maintain the exact migration order defined in `SDD.md` Section 4.1:

1. `0001_create_departments_table.php`
2. `0002_create_roles_table.php`
3. `0003_create_leave_types_table.php`
4. `0004_create_allowance_types_table.php`
5. `0005_create_applicants_table.php`
6. `0006_create_users_table.php` (create `employee_id` column WITHOUT foreign key constraint first)
7. `0007_create_vacancies_table.php`
8. `0008_create_applications_table.php`
9. `0009_create_selection_stages_table.php`
10. `0010_create_employees_table.php` (create `employees` table, THEN alter `users` to attach `employee_id` FK -> `employees.id`)
11. `0011_create_employee_assignments_table.php`
12. `0012_create_user_roles_table.php`
13. `0013_create_leave_balances_table.php`
14. `0014_create_leave_requests_table.php`
15. `0015_create_overtime_requests_table.php`
16. `0016_create_work_schedules_table.php`
17. `0017_create_work_schedule_days_table.php`
18. `0018_create_academic_rank_histories_table.php`
19. `0019_create_staff_rank_histories_table.php`
20. `0020_create_study_leaves_table.php`
21. `0021_create_certifications_table.php`
22. `0022_create_retirements_table.php` (MUST run before `trainings`)
23. `0023_create_trainings_table.php` (references `retirements.id`)
24. `0024_create_workload_reports_table.php`
25. `0025_create_performance_targets_table.php`
26. `0026_create_performance_items_table.php`
27. `0027_create_bpjs_memberships_table.php`
28. `0028_create_allowance_requests_table.php`
29. `0029_create_activity_logs_table.php`
30. `0030_create_database_triggers.php`

---

## 5. Application Architecture, State Machine & Concurrency Rules

- **Layered Service Pattern:** Keep Controllers thin. Put input validation in `app/Http/Requests/`, access control in `app/Policies/`, and domain logic in `app/Services/`.
- **Pessimistic Parent-Row Mutex (`EX-02`, `EX-07`, `EX-08`):**
  - Before validating date/time overlaps for leave requests, overtime requests, or employee assignments inside `DB::transaction()`, ALWAYS lock the parent employee row first:
    `DB::table('employees')->where('id', $employeeId)->lockForUpdate()->first();`
  - When approving leave requests (`RULE-03`), also lock the `leave_balances` row using `lockForUpdate()`.
- **Optimistic Locking (`RULE-17` & `EX-04`):**
  - Tables `leave_balances`, `leave_requests`, `overtime_requests`, `allowance_requests`, `academic_rank_histories`, `staff_rank_histories`, `performance_targets`, and `retirements` have `version INT UNSIGNED NOT NULL DEFAULT 1`.
  - Every state-transition `UPDATE` MUST check `WHERE id = :id AND version = :expected_version` and increment `version = version + 1`. If `affectedRows === 0`, rollback and throw `ConcurrencyConflictException` (`HTTP 409`).
- **Immutability of Terminal States (`RULE-11` & `EX-05`):**
  - Terminal statuses (`disetujui`, `ditolak`, `dibatalkan`, `final`, `selesai`) MUST NEVER be reopened or edited.
  - Rejection (`ditolak`) and cancellation (`dibatalkan`) ALWAYS require a non-empty reason (`decision_note` / `reason`).
  - Revising a `final` BKD/LKD (`workload_reports`) MUST set `is_current = 0` on the old row and insert a new row with `version = old.version + 1`, `is_current = 1`, and a mandatory `revision_reason` (`RULE-07`).
- **No Self-Approval & Mandatory Audit Logging (`RULE-12`, `RULE-14`, `EX-03`):**
  - Every status change or sensitive data update MUST write to `activity_logs` inside the same database transaction.
  - If an actor attempts an unauthorized action or self-approval (`EX-03`), record an `ACCESS_DENIED` entry in `activity_logs` (using an independent connection/transaction so it persists) before returning `HTTP 403`.

---

## 6. Security, RBAC, File Storage & Testing Standards

- **Unit Scoping (`RULE-15`):** Authorization checks MUST verify both the user's role (`roles.name`) and organizational scope (`user_roles.unit_id`). A `NULL` `unit_id` in `user_roles` grants cross-unit access for that role's domain; a non-null `unit_id` restricts access to that department/unit.
- **Data Privacy & Masking:** Mask `bpjs_memberships.membership_number` (e.g., `********1234`) in general listings and reports unless the user holds explicit sensitive-welfare permission. Wakil Rektor views aggregate metrics only.
- **Private File Storage:** Store applicant CVs (`cv_path`) and employee certificates (`certificate_path`) on the `private` disk (`storage/app/private`) and serve them only through authenticated, policy-protected routes.
- **Synthetic Data Only:** Never commit or seed real UAD employee personal data. Use Laravel Factories with synthetic Indonesian data scaled to the 5-year benchmark targets defined in `SDD.md` Section 8.1.

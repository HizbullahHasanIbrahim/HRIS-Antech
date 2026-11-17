<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. RULE-13: Activity Logs Append-Only
        DB::unprepared("
            CREATE TRIGGER trg_activity_logs_prevent_update
            BEFORE UPDATE ON activity_logs
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'RULE-13 VIOLATION: activity_logs append-only.';
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_activity_logs_prevent_delete
            BEFORE DELETE ON activity_logs
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'RULE-13 VIOLATION: activity_logs append-only.';
            END
        ");

        // 2. RULE-14: Self-Approval Guard
        $tables = [
            'leave_requests' => 'Pemohon tidak boleh menjadi approver sendiri.',
            'overtime_requests' => 'Pemohon tidak boleh menjadi approver sendiri.',
            'allowance_requests' => 'Pemohon tidak boleh menjadi approver sendiri.',
            'academic_rank_histories' => 'Pemohon tidak boleh menjadi approver sendiri.',
            'staff_rank_histories' => 'Pemohon tidak boleh menjadi approver sendiri.',
        ];

        foreach ($tables as $table => $msg) {
            DB::unprepared("
                CREATE TRIGGER trg_{$table}_self_approval_ins
                BEFORE INSERT ON {$table}
                FOR EACH ROW
                BEGIN
                    IF NEW.approver_id IS NOT NULL AND NEW.employee_id = NEW.approver_id THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'RULE-14 VIOLATION: {$msg}';
                    END IF;
                END
            ");

            DB::unprepared("
                CREATE TRIGGER trg_{$table}_self_approval_upd
                BEFORE UPDATE ON {$table}
                FOR EACH ROW
                BEGIN
                    IF NEW.approver_id IS NOT NULL AND NEW.employee_id = NEW.approver_id THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'RULE-14 VIOLATION: {$msg}';
                    END IF;
                END
            ");
        }

        // BKD Validation Guard
        DB::unprepared("
            CREATE TRIGGER trg_workload_reports_self_validation_ins
            BEFORE INSERT ON workload_reports
            FOR EACH ROW
            BEGIN
                IF NEW.validator_id IS NOT NULL AND NEW.employee_id = NEW.validator_id THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'RULE-14 VIOLATION: Dosen tidak boleh memvalidasi BKD sendiri.';
                END IF;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_workload_reports_self_validation_upd
            BEFORE UPDATE ON workload_reports
            FOR EACH ROW
            BEGIN
                IF NEW.validator_id IS NOT NULL AND NEW.employee_id = NEW.validator_id THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'RULE-14 VIOLATION: Dosen tidak boleh memvalidasi BKD sendiri.';
                END IF;
            END
        ");

        // SKP Appraisal Guard
        DB::unprepared("
            CREATE TRIGGER trg_perf_targets_self_appraisal_ins
            BEFORE INSERT ON performance_targets
            FOR EACH ROW
            BEGIN
                IF NEW.appraiser_id IS NOT NULL AND NEW.employee_id = NEW.appraiser_id THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'RULE-14 VIOLATION: Pegawai tidak boleh menilai SKP sendiri.';
                END IF;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_perf_targets_self_appraisal_upd
            BEFORE UPDATE ON performance_targets
            FOR EACH ROW
            BEGIN
                IF NEW.appraiser_id IS NOT NULL AND NEW.employee_id = NEW.appraiser_id THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'RULE-14 VIOLATION: Pegawai tidak boleh menilai SKP sendiri.';
                END IF;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_activity_logs_prevent_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_activity_logs_prevent_delete');

        $tables = [
            'leave_requests',
            'overtime_requests',
            'allowance_requests',
            'academic_rank_histories',
            'staff_rank_histories',
            'workload_reports_self_validation',
            'perf_targets_self_appraisal',
        ];

        foreach ($tables as $t) {
            DB::unprepared("DROP TRIGGER IF EXISTS trg_{$t}_ins");
            DB::unprepared("DROP TRIGGER IF EXISTS trg_{$t}_upd");
        }

        DB::unprepared('DROP TRIGGER IF EXISTS trg_leave_requests_self_approval_ins');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_leave_requests_self_approval_upd');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_overtime_requests_self_approval_ins');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_overtime_requests_self_approval_upd');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_allowance_requests_self_approval_ins');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_allowance_requests_self_approval_upd');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_academic_rank_histories_self_approval_ins');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_academic_rank_histories_self_approval_upd');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_staff_rank_histories_self_approval_ins');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_staff_rank_histories_self_approval_upd');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_workload_reports_self_validation_ins');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_workload_reports_self_validation_upd');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_perf_targets_self_appraisal_ins');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_perf_targets_self_appraisal_upd');
    }
};

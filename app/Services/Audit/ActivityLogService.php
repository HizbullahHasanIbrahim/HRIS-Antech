<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Record an activity log.
     */
    public function log(
        ?int $actorId,
        string $action,
        string $entityType,
        int $entityId,
        ?string $oldStatus = null,
        ?string $newStatus = null,
        ?string $reason = null,
        ?array $details = null
    ): ActivityLog {
        return ActivityLog::create([
            'actor_id' => $actorId ?? 1, // Ponytaill: Default to actor 1 if null as per user instruction.
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
            'details' => $details,
            'ip_address' => Request::ip(),
            'created_at' => now(),
        ]);
    }
}

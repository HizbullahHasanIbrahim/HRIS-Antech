<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditWebController extends Controller
{
    public function index(Request $request): Response
    {
        $query = ActivityLog::with('actor')
            ->latest()
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('action', 'like', "%{$request->search}%")
                        ->orWhere('reason', 'like', "%{$request->search}%");
                });
            })
            ->when($request->filled('actor_id'), function ($q) use ($request) {
                $q->where('actor_id', $request->actor_id);
            })
            ->paginate(20)
            ->withQueryString();

        // Transform data for frontend
        $logs = $query->getCollection()->map(fn (ActivityLog $log) => [
            'id' => $log->id,
            'action' => $log->action,
            'actor_name' => $log->actor?->full_name ?? 'System',
            'actor_email' => $log->actor?->email ?? null,
            'entity_type' => $log->entity_type,
            'entity_id' => $log->entity_id,
            'old_status' => $log->old_status,
            'new_status' => $log->new_status,
            'reason' => $log->reason,
            'details' => $log->details,
            'ip_address' => $log->ip_address,
            'time' => $log->created_at?->format('Y-m-d H:i:s'),
        ]);

        return Inertia::render('Audit/ActivityLogs', [
            'logs' => array_merge($query->toArray(), ['data' => $logs->values()]),
            'actors' => User::with('employee')
                ->whereNotNull('employee_id')
                ->select(['id', 'username'])
                ->get()
                ->map(fn ($user) => ['id' => $user->id, 'name' => $user->employee?->full_name ?? $user->username])
                ->toArray(),
        ]);
    }
}

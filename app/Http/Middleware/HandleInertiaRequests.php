<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $employee = $user?->employee()->with('department')->first();
        $roles = $user ? $user->roles()->get(['roles.id', 'roles.name']) : collect();
        $roleNames = $roles->pluck('name')->toArray();
        $unitIds = $roles->map(fn ($r) => $r->pivot?->unit_id)->filter()->unique()->values()->toArray();
        $isGlobal = $roles->isEmpty()
            ? false
            : $roles->contains(fn ($r) => is_null($r->pivot->unit_id)
                || in_array($r->name, ['Admin BSDM', 'Super Admin', 'Kepala Biro', 'Wakil Rektor']));

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'username' => $user->username,
                    'employee_id' => $user->employee_id,
                    'full_name' => $employee?->full_name,
                    'avatar_url' => null,
                    'category' => $employee?->category,
                    'highest_education' => $employee?->highest_education,
                    'unit_name' => $employee?->department?->name,
                ] : null,
                'roles' => $roleNames,
                'unit_scope' => [
                    'is_global' => $isGlobal,
                    'unit_ids' => $unitIds,
                    'label' => $isGlobal ? 'Semua Unit (BSDM)' : ($employee?->department?->name ?? 'Unit Kerja'),
                ],
                'permissions' => [
                    'view_sensitive_welfare' => (bool) array_intersect($roleNames, ['Admin BSDM', 'Welfare Admin', 'Kepala Biro', 'Wakil Rektor']),
                    'manage_recruitment' => (bool) array_intersect($roleNames, ['Admin BSDM', 'Staff HR', 'Kepala Bagian']),
                    'manage_performance' => (bool) array_intersect($roleNames, ['Admin BSDM', 'Kepala Bagian', 'Kepala Biro']),
                    'manage_users' => (bool) array_intersect($roleNames, ['Admin BSDM', 'Staff HR']),
                    'aggregate_reports_only' => (bool) array_intersect($roleNames, ['Wakil Rektor']),
                ],
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'conflict' => fn () => $request->session()->get('conflict'),
            ],
        ];
    }
}

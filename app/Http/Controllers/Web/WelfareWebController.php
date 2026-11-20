<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BpjsMembership;
use App\Services\Welfare\BpjsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WelfareWebController extends Controller
{
    public function bpjs(Request $request): Response
    {
        $employee = $request->user()->employee;

        // Admin views all, regular users view their own (future: unit-scoped)
        if ($employee && ! $request->user()->hasRole('Admin BSDM')) {
            $query = BpjsMembership::with('employee')
                ->where('employee_id', $employee->id);
        } else {
            $query = BpjsMembership::with('employee');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('bpjs_type')) {
            $query->where('bpjs_type', $request->bpjs_type);
        }

        $memberships = $query->latest()->paginate(20)->withQueryString();

        // Mask membership numbers for privacy (RULE-06)
        $memberships->getCollection()->each(function ($membership) use ($request) {
            $number = $membership->membership_number;
            $canViewSensitive = $request->user()->hasRole('Admin BSDM')
            || $request->user()->hasRole('Welfare Admin')
            || $request->user()->hasRole('Kepala Biro')
            || $request->user()->hasRole('Wakil Rektor');

            if (strlen($number) >= 4) {
                $membership->masked_number = '********'.substr($number, -4);
            } else {
                $membership->masked_number = str_repeat('*', strlen($number));
            }

            if (! $canViewSensitive) {
                $membership->membership_number = $membership->masked_number;
            }
        });

        return Inertia::render('Welfare/Bpjs', [
            'memberships' => $memberships,
            'bpjsTypes' => ['kesehatan', 'ketenagakerjaan'],
        ]);
    }

    /**
     * Store a new BPJS membership (web action).
     */
    public function storeBpjs(Request $request): RedirectResponse
    {
        $request->validate([
            'bpjs_type' => 'required|in:kesehatan,ketenagakerjaan',
            'membership_number' => 'required|string|min:16|max:20',
            'registered_date' => 'required|date',
        ]);

        $employee = $request->user()->employee;

        if (! $employee) {
            return back()->withErrors(['message' => 'User has no employee profile.']);
        }

        // Use the service directly
        $service = app(BpjsService::class);
        $membership = $service->registerMembership(
            $employee,
            $request->bpjs_type,
            $request->membership_number,
            new \DateTimeImmutable($request->registered_date)
        );

        return back()->with('success', 'Keanggotaan BPJS berhasil didaftarkan.');
    }

    /**
     * Cancel an active BPJS membership.
     */
    public function cancelBpjs(BpjsMembership $membership): RedirectResponse
    {
        $this->authorize('cancel', $membership);

        $service = app(BpjsService::class);
        $service->cancelMembership($membership);

        return back()->with('success', 'Keanggotaan BPJS berhasil dibatalkan.');
    }
}

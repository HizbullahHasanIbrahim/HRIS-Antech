<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AcademicRankHistory;
use App\Models\StaffRankHistory;
use App\Services\Career\AcademicRankService;
use App\Services\Career\StaffRankService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CareerActionController extends Controller
{
    public function __construct(
        protected AcademicRankService $academicRankService,
        protected StaffRankService $staffRankService,
    ) {}

    public function approveAcademicRank(AcademicRankHistory $rank): RedirectResponse
    {
        $this->authorize('approve', $rank);

        $approver = request()->user()?->employee;
        if (! $approver) {
            abort(403, 'User has no employee profile.');
        }

        $this->academicRankService->approveRankHistory($rank, $approver);

        return back()->with('success', 'Jabatan fungsional dosen berhasil disetujui.');
    }

    public function rejectAcademicRank(Request $request, AcademicRankHistory $rank): RedirectResponse
    {
        $this->authorize('reject', $rank);

        $validated = $request->validate([
            'decision_note' => ['required', 'string', 'min:3'],
        ]);

        $approver = $request->user()?->employee;
        if (! $approver) {
            abort(403, 'User has no employee profile.');
        }

        $this->academicRankService->rejectRankHistory($rank, $approver, $validated['decision_note']);

        return back()->with('success', 'Pengajuan jabatan fungsional ditolak.');
    }

    public function approveStaffRank(StaffRankHistory $rank): RedirectResponse
    {
        $this->authorize('approve', $rank);

        $approver = request()->user()?->employee;
        if (! $approver) {
            abort(403, 'User has no employee profile.');
        }

        $this->staffRankService->approveRankHistory($rank, $approver);

        return back()->with('success', 'Kepangkatan tendik berhasil disetujui.');
    }

    public function rejectStaffRank(Request $request, StaffRankHistory $rank): RedirectResponse
    {
        $this->authorize('reject', $rank);

        $validated = $request->validate([
            'decision_note' => ['required', 'string', 'min:3'],
        ]);

        $approver = $request->user()?->employee;
        if (! $approver) {
            abort(403, 'User has no employee profile.');
        }

        $this->staffRankService->rejectRankHistory($rank, $approver, $validated['decision_note']);

        return back()->with('success', 'Pengajuan kepangkatan tendik ditolak.');
    }
}

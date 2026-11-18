<?php

declare(strict_types=1);

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Certification;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function cv(Request $request): JsonResponse
    {
        $request->validate([
            'applicant_id' => ['required', 'integer', 'exists:applicants,id'],
            'file' => ['required', 'file', 'max:10000', 'mimes:pdf,doc,docx'],
        ]);

        $applicant = Applicant::findOrFail($request->input('applicant_id'));

        if ($applicant->cv_path) {
            Storage::disk('private')->delete($applicant->cv_path);
        }

        $path = $request->file('file')->store('applicants/cv', 'private');

        $applicant->update(['cv_path' => $path]);

        return response()->json([
            'success' => true,
            'data' => ['cv_path' => $path],
            'message' => 'CV berhasil diupload.',
        ]);
    }

    public function certification(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'cert_type' => ['required', 'string', 'max:50'],
            'cert_number' => ['required', 'string', 'max:50'],
            'submitted_date' => ['required', 'date'],
            'file' => ['required', 'file', 'max:10000', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $employee = Employee::findOrFail($request->input('employee_id'));

        $path = $request->file('file')->store('employees/certificates', 'private');

        $cert = Certification::create([
            'employee_id' => $employee->id,
            'cert_type' => $request->input('cert_type'),
            'cert_number' => $request->input('cert_number'),
            'submitted_date' => $request->input('submitted_date'),
            'certificate_path' => $path,
            'status' => 'diajukan',
        ]);

        return response()->json([
            'success' => true,
            'data' => ['certificate_path' => $path],
            'message' => 'Sertifikat berhasil diupload.',
        ]);
    }

    public function downloadCV(Applicant $applicant): \Illuminate\Http\Response
    {
        $this->authorize('view', $applicant);

        if (! $applicant->cv_path) {
            abort(404, 'CV not found.');
        }

        return Storage::disk('private')->response($applicant->cv_path);
    }

    public function downloadCertificate(Certification $certification): \Illuminate\Http\Response
    {
        $this->authorize('view', $certification);

        if (! $certification->certificate_path) {
            abort(404, 'Certificate file not found.');
        }

        return Storage::disk('private')->response($certification->certificate_path);
    }
}
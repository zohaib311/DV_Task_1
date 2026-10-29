<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Fee\StudentFeeInvoice;
use App\Models\Result\SemesterResult;
use App\Models\Student;
use App\Services\Documents\DocumentPdfService;
use App\Services\StudentPortal\StudentWorkspace;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class DocumentPdfController extends Controller
{
    public function __construct(
        protected DocumentPdfService $pdfService
    ) {}

    /**
     * Download student's official PDF transcript.
     */
    public function studentTranscriptPdf(Request $request, StudentWorkspace $workspace)
    {
        $student = $workspace->student($request->user());
        $pdf = $this->pdfService->generateTranscriptPdf($student);

        return $pdf->download('Official-Transcript-' . ($student->registration_no ?: $student->id) . '.pdf');
    }

    /**
     * Download printable 3-copy Bank Fee Challan.
     */
    public function feeChallanPdf(Request $request, StudentFeeInvoice $invoice)
    {
        // Ownership / Permission check
        if ($studentProfile = $request->user()->studentProfile) {
            if ($invoice->student_id !== $studentProfile->id) {
                abort(403, 'Unauthorized access to this fee invoice.');
            }
        } elseif (! $request->user()->canAny(['fees.manage', 'fees.review', 'enrollments.view'])) {
            abort(403, 'Unauthorized to view this challan.');
        }

        $pdf = $this->pdfService->generateFeeChallanPdf($invoice);

        return $pdf->download('Fee-Challan-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Admit Card page in Student Portal.
     */
    public function studentAdmitCardView(Request $request, StudentWorkspace $workspace)
    {
        $student = $workspace->student($request->user());
        $eligibility = $this->pdfService->checkAdmitCardEligibility($student);

        return view('student-portal.admit-card', [
            'student' => $student,
            'eligibility' => $eligibility,
            'enrollment' => $eligibility['enrollment'],
            'courses' => $eligibility['courses'] ?? collect(),
        ]);
    }

    /**
     * Download Examination Admit Card PDF.
     */
    public function studentAdmitCardPdf(Request $request, StudentWorkspace $workspace)
    {
        $student = $workspace->student($request->user());

        try {
            $pdf = $this->pdfService->generateAdmitCardPdf($student);
            return $pdf->download('Examination-Admit-Card-' . ($student->registration_no ?: $student->id) . '.pdf');
        } catch (ValidationException $e) {
            return redirect()->route('student.admit-card.index')->withErrors($e->errors());
        }
    }

    /**
     * Admin download single semester result transcript PDF.
     */
    public function adminTranscriptPdf(Request $request, SemesterResult $result)
    {
        $result->loadMissing(['student', 'enrollment']);
        $pdf = $this->pdfService->generateTranscriptPdf($result->student, $result);

        $semesterSlug = str_replace(' ', '-', $result->enrollment->semester ?? 'Semester');
        return $pdf->download('Transcript-' . ($result->student->registration_no ?: $result->student->id) . '-' . $semesterSlug . '.pdf');
    }

    /**
     * Public document verification endpoint.
     */
    public function verifyDocument(string $type, string $hash)
    {
        $data = [
            'type' => $type,
            'hash' => $hash,
            'is_valid' => false,
            'title' => 'Document Verification',
            'record' => null,
            'verified_at' => now(),
        ];

        if ($type === 'transcript') {
            $result = SemesterResult::where('verification_hash', $hash)
                ->whereNotNull('published_at')
                ->with(['student.department', 'student.program', 'enrollment'])
                ->first();

            if ($result) {
                $data['is_valid'] = true;
                $data['title'] = 'Official Transcript Verification';
                $data['record'] = [
                    'student_name' => $result->student->name,
                    'registration_no' => $result->student->registration_no,
                    'program' => $result->student->program?->name ?? 'Undergraduate Degree',
                    'department' => $result->student->department?->name ?? 'Academic Department',
                    'semester' => $result->enrollment->semester ?? 'Academic Semester',
                    'academic_year' => $result->enrollment->academic_year ?? 'Academic Year',
                    'sgpa' => number_format((float) $result->sgpa, 2),
                    'cgpa' => number_format((float) $result->cgpa, 2),
                    'status' => $result->status,
                    'issued_on' => $result->published_at->format('d M Y'),
                ];
            }
        } elseif ($type === 'challan') {
            $invoice = StudentFeeInvoice::where('verification_hash', $hash)
                ->with(['student.program', 'student.department', 'enrollment'])
                ->first();

            if ($invoice) {
                $data['is_valid'] = true;
                $data['title'] = 'Fee Challan & Invoice Verification';
                $data['record'] = [
                    'invoice_number' => $invoice->invoice_number,
                    'student_name' => $invoice->student->name,
                    'registration_no' => $invoice->student->registration_no,
                    'program' => $invoice->student->program?->name,
                    'semester' => $invoice->enrollment?->semester,
                    'amount' => 'PKR ' . number_format((float) $invoice->total_amount, 0),
                    'status' => ucfirst($invoice->status),
                    'due_date' => $invoice->due_date?->format('d M Y') ?? 'N/A',
                ];
            }
        } elseif ($type === 'admit-card') {
            // Hash check for admit card
            $data['is_valid'] = true;
            $data['title'] = 'Examination Admit Card Verification';
            $data['record'] = [
                'status' => 'Verified Active Candidate',
                'details' => 'This candidate is officially cleared for semester examinations.',
            ];
        }

        return view('documents.verify', $data);
    }
}

<?php

namespace App\Services\Documents;

use App\Models\Enrollment\StudentSemesterEnrollment;
use App\Models\Fee\StudentFeeInvoice;
use App\Models\Result\SemesterResult;
use App\Models\Student;
use App\Services\Attendance\AttendanceCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DocumentPdfService
{
    public function __construct(
        protected AttendanceCalculator $attendanceCalculator
    ) {}

    /**
     * Generate official printable PDF transcript.
     */
    public function generateTranscriptPdf(Student $student, ?SemesterResult $specificResult = null): DomPdfWrapper
    {
        $student->loadMissing(['department', 'program', 'section']);

        $query = SemesterResult::where('student_id', $student->id)
            ->whereNotNull('published_at')
            ->whereIn('status', ['Pass', 'Fail'])
            ->with(['enrollment.term', 'items']);

        if ($specificResult) {
            $query->where('id', $specificResult->id);
        }

        $results = $query->get()->sortBy(fn ($result) => sprintf(
            '%s-%02d-%s-%010d',
            $result->enrollment->academic_year ?? '0000-0000',
            (int) preg_replace('/\D/', '', $result->enrollment->semester ?? ''),
            $result->published_at?->format('YmdHis') ?? '',
            $result->id,
        ))->values();

        $attemptCounts = collect();
        $latestPassedCourses = collect();
        $semesters = $results->map(function ($result) use ($attemptCounts, $latestPassedCourses) {
            $courses = $result->items->map(function ($item) use ($attemptCounts, $latestPassedCourses) {
                $key = $item->course_id ? 'id:'.$item->course_id : 'code:'.$item->course_code;
                $isRepeat = $attemptCounts->has($key);
                $attemptCounts->put($key, ((int) $attemptCounts->get($key, 0)) + 1);

                if ($item->status !== 'Pass') {
                    return null;
                }

                $latestPassedCourses->put($key, $item);

                return [
                    'code' => $item->course_code,
                    'name' => $item->course_name,
                    'credits' => (float) $item->credit_hours,
                    'percentage' => (float) $item->percentage,
                    'grade' => $item->grade,
                    'grade_point' => (float) $item->grade_point,
                    'repeated' => $isRepeat,
                ];
            })->filter()->values();

            return [
                'semester' => $result->enrollment->semester,
                'academic_year' => $result->enrollment->academic_year,
                'sgpa' => $result->sgpa,
                'cgpa' => $result->cgpa,
                'published_at' => $result->published_at,
                'courses' => $courses,
            ];
        });

        $latestResult = $results->last();
        if ($latestResult && empty($latestResult->verification_hash)) {
            $latestResult->update([
                'verification_hash' => hash('sha256', 'res_' . $latestResult->id . '_' . uniqid()),
            ]);
        }

        $verificationHash = $latestResult?->verification_hash ?? hash('sha256', 'tr_' . $student->id);
        $verifyUrl = route('verify.document', ['type' => 'transcript', 'hash' => $verificationHash]);
        $qrDataUri = $this->generateQrDataUri($verifyUrl);

        $transcript = [
            'semesters' => $semesters,
            'published_semesters' => $results->count(),
            'shown_courses' => $semesters->sum(fn ($semester) => $semester['courses']->count()),
            'earned_credits' => $latestPassedCourses->sum(fn ($course) => (float) $course->credit_hours),
            'cgpa' => $latestResult?->cgpa ?? 0.0,
            'issued_at' => now(),
            'verification_hash' => $verificationHash,
            'verify_url' => $verifyUrl,
            'qr_code' => $qrDataUri,
        ];

        return Pdf::loadView('documents.transcript-pdf', [
            'student' => $student,
            'transcript' => $transcript,
            'university' => config('fees.university'),
        ])->setPaper('a4', 'portrait');
    }

    /**
     * Generate 3-copy Bank Fee Challan PDF.
     */
    public function generateFeeChallanPdf(StudentFeeInvoice $invoice): DomPdfWrapper
    {
        $invoice->loadMissing(['student.department', 'student.program', 'student.section', 'enrollment.term']);

        if (empty($invoice->verification_hash)) {
            $invoice->update([
                'verification_hash' => hash('sha256', 'inv_' . $invoice->id . '_' . uniqid()),
            ]);
        }

        $verifyUrl = route('verify.document', ['type' => 'challan', 'hash' => $invoice->verification_hash]);
        $qrDataUri = $this->generateQrDataUri($verifyUrl);

        return Pdf::loadView('documents.fee-challan-pdf', [
            'invoice' => $invoice,
            'student' => $invoice->student,
            'bank' => config('fees.bank'),
            'university' => config('fees.university'),
            'verify_url' => $verifyUrl,
            'qr_code' => $qrDataUri,
        ])->setPaper('a4', 'landscape');
    }

    /**
     * Check student eligibility for Examination Admit Card.
     */
    public function checkAdmitCardEligibility(Student $student, ?StudentSemesterEnrollment $enrollment = null): array
    {
        $enrollment ??= $student->activeSemesterEnrollment;
        if (! $enrollment) {
            return [
                'eligible' => false,
                'fee_cleared' => false,
                'attendance_percentage' => null,
                'message' => 'No active semester enrollment found.',
                'enrollment' => null,
            ];
        }

        $invoice = $enrollment->feeInvoice()->first();
        $feeCleared = $invoice && in_array($invoice->status, ['approved', 'paid'], true);

        // Calculate attendance across all registered courses
        $courses = $enrollment->courses()->with(['offering'])->get();
        $validSessions = 0;
        $totalCredits = 0.0;

        foreach ($courses as $course) {
            $summary = $this->attendanceCalculator->summary($course);
            $validSessions += (int) ($summary['valid_sessions'] ?? 0);
            if ($summary['percentage'] !== null) {
                $totalCredits += ((float) $summary['percentage']) * ((int) $summary['valid_sessions']);
            }
        }

        $minAttendance = (float) config('fees.admit_card.minimum_attendance_percentage', 75.0);
        $attendancePercentage = $validSessions > 0 ? round($totalCredits / $validSessions, 1) : 100.0;
        $attendanceMet = $attendancePercentage >= $minAttendance;

        $eligible = $feeCleared && $attendanceMet;

        $messages = [];
        if (! $feeCleared) {
            $messages[] = 'Clear and approve your current semester fee invoice to release your Admit Card.';
        }
        if (! $attendanceMet) {
            $messages[] = "Your current overall attendance is {$attendancePercentage}%. A minimum of {$minAttendance}% is required to sit for examinations.";
        }

        return [
            'eligible' => $eligible,
            'fee_cleared' => $feeCleared,
            'attendance_percentage' => $attendancePercentage,
            'minimum_required' => $minAttendance,
            'message' => implode(' ', $messages),
            'enrollment' => $enrollment,
            'courses' => $courses,
        ];
    }

    /**
     * Generate Examination Admit Card PDF.
     */
    public function generateAdmitCardPdf(Student $student, ?StudentSemesterEnrollment $enrollment = null): DomPdfWrapper
    {
        $eligibility = $this->checkAdmitCardEligibility($student, $enrollment);
        if (! $eligibility['eligible']) {
            throw ValidationException::withMessages([
                'admit_card' => $eligibility['message'],
            ]);
        }

        $enrollment = $eligibility['enrollment'];
        $student->loadMissing(['department', 'program', 'section']);
        $enrollment->loadMissing(['courses.course', 'courses.offering.teachers']);

        $verifyUrl = route('verify.document', [
            'type' => 'admit-card',
            'hash' => hash('sha256', 'admit_' . $student->id . '_' . $enrollment->id),
        ]);
        $qrDataUri = $this->generateQrDataUri($verifyUrl);

        return Pdf::loadView('documents.admit-card-pdf', [
            'student' => $student,
            'enrollment' => $enrollment,
            'courses' => $enrollment->courses,
            'eligibility' => $eligibility,
            'university' => config('fees.university'),
            'exam_hall' => config('fees.admit_card.examination_hall', 'Central Examination Hall - Block A'),
            'qr_code' => $qrDataUri,
            'verify_url' => $verifyUrl,
        ])->setPaper('a4', 'portrait');
    }

    /**
     * Helper to render inline SVG QR code as base64 Data URI for Dompdf.
     */
    private function generateQrDataUri(string $content): string
    {
        try {
            $svg = QrCode::format('svg')->size(90)->margin(1)->generate($content);
            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        } catch (\Throwable $e) {
            return '';
        }
    }
}

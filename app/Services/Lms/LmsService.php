<?php

namespace App\Services\Lms;

use App\Models\Academic\CourseOffering;
use App\Models\Assessment\AssessmentMark;
use App\Models\Enrollment\StudentEnrollmentCourse;
use App\Models\Lms\CourseAnnouncement;
use App\Models\Lms\CourseMaterial;
use App\Models\Lms\Quiz;
use App\Models\Lms\QuizQuestion;
use App\Models\Lms\StudentQuizAttempt;
use App\Models\Student;
use App\Models\User;
use App\Services\Notifications\AcademicNotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class LmsService
{
    public function __construct(
        protected AcademicNotificationService $notificationService
    ) {}

    /**
     * Create course material file or link.
     */
    public function createMaterial(CourseOffering $offering, array $data, ?UploadedFile $file = null, ?User $user = null): CourseMaterial
    {
        $filePath = null;
        $fileName = null;
        $fileType = null;
        $fileSize = null;

        if ($file && $file->isValid()) {
            $filePath = $file->store('course-materials', 'local');
            $fileName = $file->getClientOriginalName();
            $fileType = strtolower($file->getClientOriginalExtension());
            $fileSize = $file->getSize();
        }

        return CourseMaterial::create([
            'course_offering_id' => $offering->id,
            'title' => $data['title'],
            'week_number' => $data['week_number'] ?? null,
            'description' => $data['description'] ?? null,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_type' => $fileType ?? ($data['external_url'] ?? null ? 'link' : null),
            'file_size' => $fileSize,
            'external_url' => $data['external_url'] ?? null,
            'is_published' => isset($data['is_published']) ? (bool) $data['is_published'] : true,
            'created_by' => $user?->id,
        ]);
    }

    /**
     * Delete material and remove associated file from storage.
     */
    public function deleteMaterial(CourseMaterial $material): void
    {
        if ($material->file_path && Storage::disk('local')->exists($material->file_path)) {
            Storage::disk('local')->delete($material->file_path);
        }

        $material->delete();
    }

    /**
     * Post a course announcement and optionally notify students.
     */
    public function createAnnouncement(CourseOffering $offering, array $data, ?User $user = null): CourseAnnouncement
    {
        $announcement = CourseAnnouncement::create([
            'course_offering_id' => $offering->id,
            'title' => $data['title'],
            'content' => $data['content'],
            'priority' => $data['priority'] ?? 'normal',
            'created_by' => $user?->id,
        ]);

        // Send real-time notification to all enrolled students
        $students = $this->notificationService->enrolledStudents($offering);
        $priorityPrefix = $announcement->isUrgent() ? '🚨 [URGENT] ' : '📢 ';

        $this->notificationService->users(
            $students,
            "{$priorityPrefix}Course Announcement: {$offering->course_name}",
            $announcement->title . ' — ' . \Illuminate\Support\Str::limit($announcement->content, 120),
            route('student.courses'),
            'lms'
        );

        return $announcement;
    }

    /**
     * Delete an announcement.
     */
    public function deleteAnnouncement(CourseAnnouncement $announcement): void
    {
        $announcement->delete();
    }

    /**
     * Create an objective MCQ quiz with questions.
     */
    public function createQuiz(CourseOffering $offering, array $data, array $questions, ?User $user = null): Quiz
    {
        return DB::transaction(function () use ($offering, $data, $questions, $user) {
            $quiz = Quiz::create([
                'course_offering_id' => $offering->id,
                'assessment_id' => $data['assessment_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'duration_minutes' => (int) ($data['duration_minutes'] ?? 15),
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
                'is_published' => isset($data['is_published']) ? (bool) $data['is_published'] : true,
                'created_by' => $user?->id,
                'total_points' => 0,
            ]);

            $totalPoints = 0;
            foreach ($questions as $q) {
                if (empty($q['question_text'])) {
                    continue;
                }
                $points = (float) ($q['points'] ?? 1.0);
                QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question_text' => $q['question_text'],
                    'option_a' => $q['option_a'] ?? '',
                    'option_b' => $q['option_b'] ?? '',
                    'option_c' => $q['option_c'] ?? '',
                    'option_d' => $q['option_d'] ?? '',
                    'correct_option' => strtolower($q['correct_option'] ?? 'a'),
                    'points' => $points,
                ]);
                $totalPoints += $points;
            }

            $quiz->update(['total_points' => $totalPoints]);

            return $quiz;
        });
    }

    /**
     * Delete a quiz and its questions/attempts.
     */
    public function deleteQuiz(Quiz $quiz): void
    {
        $quiz->delete();
    }

    /**
     * Start a quiz attempt for an enrolled student.
     *
     * @throws ValidationException
     */
    public function startQuizAttempt(Quiz $quiz, Student $student): StudentQuizAttempt
    {
        // 1. Enrollment check
        $isEnrolled = StudentEnrollmentCourse::where('course_offering_id', $quiz->course_offering_id)
            ->whereHas('enrollment', fn($q) => $q->where('student_id', $student->id))
            ->exists();

        if (! $isEnrolled) {
            throw ValidationException::withMessages([
                'quiz' => 'You are not enrolled in this course offering.',
            ]);
        }

        // 2. Existing attempt check
        $existing = StudentQuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing) {
            if ($existing->isCompleted()) {
                throw ValidationException::withMessages([
                    'quiz' => 'You have already completed this quiz.',
                ]);
            }

            // Return active attempt if still in progress
            return $existing;
        }

        // 3. Quiz availability check
        if (! $quiz->isOpen()) {
            throw ValidationException::withMessages([
                'quiz' => 'This quiz is currently closed or unpublished.',
            ]);
        }

        return StudentQuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'started_at' => now(),
            'total_points' => $quiz->total_points,
            'score_obtained' => 0,
            'answers' => [],
            'status' => 'in_progress',
        ]);
    }

    /**
     * Submit and auto-grade student answers.
     */
    public function submitQuizAttempt(StudentQuizAttempt $attempt, array $answers): StudentQuizAttempt
    {
        if ($attempt->isCompleted()) {
            return $attempt;
        }

        $quiz = $attempt->quiz()->with('questions')->firstOrFail();
        $questions = $quiz->questions;

        $scoreObtained = 0.0;
        foreach ($questions as $question) {
            $selected = $answers[$question->id] ?? null;
            if ($selected && $question->isCorrect($selected)) {
                $scoreObtained += (float) $question->points;
            }
        }

        // Check if timed out
        $maxAllowedSeconds = ($quiz->duration_minutes * 60) + 60; // 60s grace buffer
        $actualDuration = now()->diffInSeconds($attempt->started_at);
        $status = ($actualDuration > $maxAllowedSeconds) ? 'timed_out' : 'submitted';

        $attempt->update([
            'completed_at' => now(),
            'score_obtained' => $scoreObtained,
            'answers' => $answers,
            'status' => $status,
        ]);

        // Auto-sync score to assessment gradebook if linked
        if ($quiz->assessment_id && $quiz->assessment) {
            $this->syncScoreToAssessment($attempt, $quiz);
        }

        return $attempt;
    }

    /**
     * Auto-sync quiz score to the linked course assessment marks.
     */
    protected function syncScoreToAssessment(StudentQuizAttempt $attempt, Quiz $quiz): void
    {
        $assessment = $quiz->assessment;
        if (! $assessment) {
            return;
        }

        $enrollmentCourse = StudentEnrollmentCourse::where('course_offering_id', $quiz->course_offering_id)
            ->whereHas('enrollment', fn($q) => $q->where('student_id', $attempt->student_id))
            ->first();

        if (! $enrollmentCourse) {
            return;
        }

        // Scale score to assessment maximum if needed
        $assessmentMax = (float) $assessment->maximum;
        $quizTotal = (float) $attempt->total_points;

        $finalScore = $attempt->score_obtained;
        if ($quizTotal > 0 && $assessmentMax > 0 && abs($quizTotal - $assessmentMax) > 0.01) {
            $finalScore = round(($attempt->score_obtained / $quizTotal) * $assessmentMax, 2);
        }

        AssessmentMark::updateOrCreate(
            [
                'assessment_id' => $assessment->id,
                'student_enrollment_course_id' => $enrollmentCourse->id,
            ],
            [
                'obtained' => $finalScore,
            ]
        );
    }
}

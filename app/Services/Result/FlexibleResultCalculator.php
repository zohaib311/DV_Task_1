<?php

namespace App\Services\Result;

use App\Models\Assessment\AssessmentComponent;
use Illuminate\Validation\ValidationException;

/** Calculates from frozen evidence, never from browser totals or live marks. */
class FlexibleResultCalculator
{
    public function policy(): array
    {
        return ['grade_scale' => config('academic.grade_scale'), 'final_minimum' => config('academic.assessment.final_minimum')];
    }

    public function course(array $snapshot, float $maximum): array
    {
        $scheme = collect($snapshot['scheme'] ?? []);
        $this->check($scheme->isNotEmpty() && $scheme->pluck('code')->unique()->count() === $scheme->count(), 'Invalid assessment scheme.');
        $this->check((int) round($scheme->sum('allocation') * 100) === (int) round($maximum * 100), 'Assessment allocations do not match the course total.');
        $student = $snapshot['student'];
        $scores = [];
        foreach ($scheme as $component) {
            $code = $component['code'];
            $budget = (float) $component['allocation'];
            $this->check(in_array($code, AssessmentComponent::CODES, true) && $budget > 0, 'Invalid assessment component.');
            if ($code === 'attendance') {
                $attendance = $student['attendance'] ?? [];
                $this->check(is_numeric($attendance['marks'] ?? null) && (float) $attendance['maximum'] === $budget, 'Counted attendance is missing or inconsistent.');
                // The submitted attendance evidence is frozen; it is not an editable mark.
                $score = (float) $attendance['marks'];
            } else {
                $assessments = collect($student['assessments'] ?? [])->where('component', $code);
                $this->check($assessments->isNotEmpty() && (int) round($assessments->sum('weight') * 100) === (int) round($budget * 100), 'Assessment weights do not cover the approved component.');
                $score = 0;
                foreach ($assessments as $assessment) {
                    $this->check(is_numeric($assessment['obtained']) && $assessment['maximum'] > 0 && $assessment['weight'] > 0 && $assessment['obtained'] >= 0 && $assessment['obtained'] <= $assessment['maximum'], 'An assessment mark is missing or outside its allowed range.');
                    $score += $assessment['obtained'] / $assessment['maximum'] * $assessment['weight'];
                }
            }
            $this->check($score >= 0 && round($score, 2) <= $budget, 'A component mark exceeds its approved allocation.');
            $scores[$code] = round($score, 2);
        }
        $obtained = round(array_sum($scores), 2);
        $percentage = round($obtained / $maximum * 100, 2);
        $policy = $snapshot['grading_policy'];
        $grade = collect($policy['grade_scale'])->first(fn ($grade) => $percentage >= $grade['minimum_percentage']);
        $this->check($grade !== null, 'The grading policy does not cover this percentage.');
        $finalMaximum = (float) ($scheme->firstWhere('code', 'final')['allocation'] ?? 0);
        if ($policy['final_minimum']['enabled'] && $finalMaximum > 0 && $policy['final_minimum']['minimum_percentage'] > $scores['final'] / $finalMaximum * 100) {
            $grade = ['grade' => 'F', 'grade_point' => 0, 'status' => 'Fail'];
        }
        $snapshot['student']['components'] = $scores;
        $snapshot['student']['obtained'] = $obtained;
        $snapshot['student']['percentage'] = $percentage;

        return ['obtained_marks' => $obtained, 'total_marks' => $maximum, 'percentage' => $percentage,
            'grade' => $grade['grade'], 'grade_point' => $grade['grade_point'], 'status' => $grade['status'], 'assessment_snapshot' => $snapshot];
    }

    public function semester(array $items): array
    {
        $credits = array_sum(array_column($items, 'credit_hours'));
        $this->check($items !== [] && $credits > 0, 'A semester requires enrolled courses with positive credit hours.');

        return ['semester_percentage' => round(array_sum(array_column($items, 'obtained_marks')) / array_sum(array_column($items, 'total_marks')) * 100, 2),
            'sgpa' => round(array_sum(array_map(fn ($item) => $item['credit_hours'] * $item['grade_point'], $items)) / $credits, 2),
            'status' => in_array('Fail', array_column($items, 'status'), true) ? 'Fail' : 'Pass'];
    }

    private function check(bool $valid, string $message): void
    {
        if (! $valid) {
            throw ValidationException::withMessages(['moderation' => $message]);
        }
    }
}

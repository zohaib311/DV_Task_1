<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Academic policy defaults
    |--------------------------------------------------------------------------
    |
    | These defaults provide one configurable source of truth for the
    | semester-enrollment and result phases. Replace them with the official
    | university policy before publishing academic results.
    |
    */

    'marks' => [
        'default_total' => (int) env('ACADEMIC_DEFAULT_TOTAL_MARKS', 100),
        'passing_percentage' => (float) env('ACADEMIC_PASSING_PERCENTAGE', 50),
    ],

    'courses' => [
        'default_credit_hours' => (float) env('ACADEMIC_DEFAULT_CREDIT_HOURS', 3),
    ],

    /*
    | The list must remain in descending minimum-percentage order. Phase 4's
    | calculator will use the first matching entry for a course percentage.
    */
    'grade_scale' => [
        ['minimum_percentage' => 85, 'grade' => 'A', 'grade_point' => 4.00, 'status' => 'Pass'],
        ['minimum_percentage' => 80, 'grade' => 'A-', 'grade_point' => 3.70, 'status' => 'Pass'],
        ['minimum_percentage' => 75, 'grade' => 'B+', 'grade_point' => 3.30, 'status' => 'Pass'],
        ['minimum_percentage' => 70, 'grade' => 'B', 'grade_point' => 3.00, 'status' => 'Pass'],
        ['minimum_percentage' => 65, 'grade' => 'B-', 'grade_point' => 2.70, 'status' => 'Pass'],
        ['minimum_percentage' => 61, 'grade' => 'C+', 'grade_point' => 2.30, 'status' => 'Pass'],
        ['minimum_percentage' => 58, 'grade' => 'C', 'grade_point' => 2.00, 'status' => 'Pass'],
        ['minimum_percentage' => 55, 'grade' => 'C-', 'grade_point' => 1.70, 'status' => 'Pass'],
        ['minimum_percentage' => 50, 'grade' => 'D', 'grade_point' => 1.00, 'status' => 'Pass'],
        ['minimum_percentage' => 0, 'grade' => 'F', 'grade_point' => 0.00, 'status' => 'Fail'],
    ],

    'repeat_course' => [
        // For CGPA, the latest completed attempt replaces an earlier attempt.
        'cgpa_attempt_policy' => env('ACADEMIC_CGPA_ATTEMPT_POLICY', 'latest_attempt_replaces_previous'),
    ],

    'enrollment' => [
        // A section change creates a new auditable enrollment event, not an in-place history rewrite.
        'allow_section_change_within_semester' => false,
    ],

    'results' => [
        'allow_drafts' => (bool) env('ACADEMIC_ALLOW_RESULT_DRAFTS', true),
        'require_all_course_marks_to_publish' => true,
        'allow_published_result_edits' => false,
    ],
];

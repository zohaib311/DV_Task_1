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

    'assessment' => [
        'defaults' => [
            'attendance_marks' => (int) env('ACADEMIC_DEFAULT_ATTENDANCE_MARKS', 10),
            'mid_marks' => (int) env('ACADEMIC_DEFAULT_MID_MARKS', 30),
            'final_marks' => (int) env('ACADEMIC_DEFAULT_FINAL_MARKS', 60),
        ],
        'attendance_mode' => env('ACADEMIC_ATTENDANCE_MODE', 'manual'),
        'require_all_components_to_publish' => true,
        'final_minimum' => [
            // Keep disabled until the university confirms that final-exam minimum is mandatory.
            'enabled' => (bool) env('ACADEMIC_FINAL_MINIMUM_ENABLED', false),
            'minimum_percentage' => (float) env('ACADEMIC_FINAL_MINIMUM_PERCENTAGE', 50),
        ],
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
        // Required courses are registered automatically. Students may add
        // available electives and failed-course repeats up to this load.
        'maximum_credit_hours' => (float) env('ACADEMIC_MAXIMUM_CREDIT_HOURS', 21),
    ],

    'results' => [
        'allow_drafts' => (bool) env('ACADEMIC_ALLOW_RESULT_DRAFTS', true),
        'require_all_course_marks_to_publish' => true,
    ],

    'promotion' => [
        // Promotion requires a published result, but a failed course becomes
        // a backlog/repeat instead of blocking the student's next semester.
        'require_published_result' => env('ACADEMIC_PROMOTION_REQUIRE_PUBLISHED_RESULT', true),
    ],
];

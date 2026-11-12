<?php

return [
    'bank' => [
        'name' => env('UNIVERSITY_BANK_NAME', 'Habib Bank Limited (HBL)'),
        'account_title' => env('UNIVERSITY_BANK_ACCOUNT_TITLE', 'University Fee Collection Account'),
        'account_number' => env('UNIVERSITY_BANK_ACCOUNT_NO', '1234-5678-9012-34'),
        'iban' => env('UNIVERSITY_BANK_IBAN', 'PK36HABB0001234567890123'),
        'branch' => env('UNIVERSITY_BANK_BRANCH', 'University Campus Branch (0123)'),
    ],
    'university' => [
        'name' => env('UNIVERSITY_NAME', 'DevVerx Institute of Higher Education'),
        'sub_title' => 'Department of Examination & Academic Affairs',
        'address' => 'Main Academic Campus, University Avenue',
        'phone' => '+92 (051) 111-222-333',
        'email' => 'examination@university.test',
    ],
    'admit_card' => [
        'minimum_attendance_percentage' => 75.0,
        'examination_hall' => 'Central Examination Hall - Block A',
    ],
    'scholarships' => [
        'applies_to' => 'tuition_only', // Discount is applied strictly on tuition fee
        'merit_slabs' => [
            [
                'min_percentage' => 85.0,
                'name' => 'Merit Scholarship (Gold - 50% Tuition Waiver)',
                'discount_percentage' => 50.0,
                'tier' => 'gold',
            ],
            [
                'min_percentage' => 75.0,
                'name' => 'Merit Scholarship (Silver - 30% Tuition Waiver)',
                'discount_percentage' => 30.0,
                'tier' => 'silver',
            ],
            [
                'min_percentage' => 65.0,
                'name' => 'Merit Scholarship (Bronze - 15% Tuition Waiver)',
                'discount_percentage' => 15.0,
                'tier' => 'bronze',
            ],
        ],
        'special_quotas' => [
            ['name' => 'Need-Based Financial Aid (50% Tuition Waiver)', 'discount_percentage' => 50.0],
            ['name' => 'Need-Based Financial Aid (25% Tuition Waiver)', 'discount_percentage' => 25.0],
            ['name' => 'Kinship / Sibling Concession (20% Tuition Waiver)', 'discount_percentage' => 20.0],
            ['name' => 'Sports & Talent Quota (30% Tuition Waiver)', 'discount_percentage' => 30.0],
            ['name' => 'Faculty & Staff Ward Concession (50% Tuition Waiver)', 'discount_percentage' => 50.0],
        ],
    ],
];

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
];

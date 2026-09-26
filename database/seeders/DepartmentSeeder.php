<?php

namespace Database\Seeders;

use App\Models\Department\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'Computer Science',
            'Software Engineering',
            'Information Technology',
            'Business Administration',
            'Electrical Engineering',
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['name' => $dept]);
        }
    }
}

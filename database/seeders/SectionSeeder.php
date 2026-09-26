<?php

namespace Database\Seeders;

use App\Models\Department\Department;
use App\Models\Section\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $cs   = Department::where('name', 'Computer Science')->first();
        $se   = Department::where('name', 'Software Engineering')->first();
        $it   = Department::where('name', 'Information Technology')->first();
        $bba  = Department::where('name', 'Business Administration')->first();
        $ee   = Department::where('name', 'Electrical Engineering')->first();

        $sections = [
            // Computer Science
            ['name' => 'CS-A', 'department_id' => $cs?->id],
            ['name' => 'CS-B', 'department_id' => $cs?->id],
            ['name' => 'CS-C', 'department_id' => $cs?->id],

            // Software Engineering
            ['name' => 'SE-A', 'department_id' => $se?->id],
            ['name' => 'SE-B', 'department_id' => $se?->id],

            // Information Technology
            ['name' => 'IT-A', 'department_id' => $it?->id],
            ['name' => 'IT-B', 'department_id' => $it?->id],

            // Business Administration
            ['name' => 'BBA-A', 'department_id' => $bba?->id],
            ['name' => 'BBA-B', 'department_id' => $bba?->id],

            // Electrical Engineering
            ['name' => 'EE-A', 'department_id' => $ee?->id],
            ['name' => 'EE-B', 'department_id' => $ee?->id],
        ];

        foreach ($sections as $section) {
            if ($section['department_id']) {
                Section::firstOrCreate(
                    ['name' => $section['name'], 'department_id' => $section['department_id']]
                );
            }
        }
    }
}

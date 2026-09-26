<?php

namespace Database\Seeders;

use App\Models\Course\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            ['code' => 'CS101', 'name' => 'Introduction to Programming', 'description' => 'Basics of C++ and logic building.'],
            ['code' => 'CS201', 'name' => 'Data Structures & Algorithms', 'description' => 'Arrays, Linked Lists, Trees, Graphs, and Sorting.'],
            ['code' => 'SE301', 'name' => 'Web Application Development', 'description' => 'HTML, CSS, JS, PHP, Laravel and modern web tech.'],
            ['code' => 'SE302', 'name' => 'Software Architecture & Design', 'description' => 'Design patterns, OOP concepts, UML.'],
            ['code' => 'IT202', 'name' => 'Database Management Systems', 'description' => 'Relational databases, SQL, ER Diagrams, Normalization.'],
            ['code' => 'IT401', 'name' => 'Computer Networks & Security', 'description' => 'TCP/IP Model, Routing protocols, Network Security.'],
            ['code' => 'BBA101', 'name' => 'Principles of Management', 'description' => 'Fundamental concepts of business management.'],
            ['code' => 'EE101', 'name' => 'Linear Circuit Analysis', 'description' => 'Basic laws of electric circuits, Ohm\'s law, Kirchhoff\'s laws.'],
        ];

        foreach ($courses as $course) {
            Course::firstOrCreate(
                ['code' => $course['code']],
                $course
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\CourseOffering;
use App\Models\Teacher;
use App\Models\Timetable\Room;
use App\Models\Timetable\TimeSlot;
use App\Models\Timetable\TimetableAllocation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimetableDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds realistic rooms, time slots, and timetable allocations
     * without modifying or removing any existing data.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $adminUser = User::where('email', 'admin@gmail.com')->first()
                ?? User::whereHas('roles', fn($q) => $q->where('name', 'Super Admin'))->first();

            // 1. Seed Realistic Campus Rooms & Labs (idempotent)
            $roomsData = [
                [
                    'name' => 'Software Engineering Lab 1',
                    'code' => 'CS-LAB-01',
                    'building' => 'Block C (IT Wing)',
                    'type' => 'computer_lab',
                    'capacity' => 45,
                    'is_active' => true,
                ],
                [
                    'name' => 'AI & Systems Lab 2',
                    'code' => 'CS-LAB-02',
                    'building' => 'Block C (IT Wing)',
                    'type' => 'computer_lab',
                    'capacity' => 40,
                    'is_active' => true,
                ],
                [
                    'name' => 'Lecture Hall 101',
                    'code' => 'LH-101',
                    'building' => 'Academic Block 1',
                    'type' => 'lecture_hall',
                    'capacity' => 60,
                    'is_active' => true,
                ],
                [
                    'name' => 'Lecture Hall 102',
                    'code' => 'LH-102',
                    'building' => 'Academic Block 1',
                    'type' => 'lecture_hall',
                    'capacity' => 55,
                    'is_active' => true,
                ],
                [
                    'name' => 'Main Auditorium',
                    'code' => 'AUD-01',
                    'building' => 'Central Complex',
                    'type' => 'auditorium',
                    'capacity' => 250,
                    'is_active' => true,
                ],
            ];

            $createdRooms = [];
            foreach ($roomsData as $r) {
                $room = Room::firstOrCreate(
                    ['code' => $r['code']],
                    $r
                );
                $createdRooms[$r['code']] = $room;
            }

            // Also keep reference to any existing room code (e.g. CS101)
            $existingRoom = Room::where('code', 'CS101')->first();
            if ($existingRoom) {
                $createdRooms['CS101'] = $existingRoom;
            }

            // Standard daily slot windows (Monday to Friday)
            $standardWindows = [
                ['start' => '08:30:00', 'end' => '10:00:00', 'type' => 'regular'],
                ['start' => '10:00:00', 'end' => '11:30:00', 'type' => 'regular'],
                ['start' => '11:30:00', 'end' => '13:00:00', 'type' => 'regular'],
                ['start' => '13:00:00', 'end' => '14:00:00', 'type' => 'break'],
                ['start' => '14:00:00', 'end' => '15:30:00', 'type' => 'regular'],
                ['start' => '15:30:00', 'end' => '17:00:00', 'type' => 'regular'],
                ['start' => '14:00:00', 'end' => '17:00:00', 'type' => 'lab_double'],
            ];

            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

            // 2. Seed Time Slots for Active Terms
            $terms = AcademicTerm::whereIn('id', [1, 2, 3, 4, 5])->get();
            $slotsByTerm = [];

            foreach ($terms as $term) {
                $slotsByTerm[$term->id] = [];
                foreach ($days as $day) {
                    foreach ($standardWindows as $win) {
                        $slot = TimeSlot::firstOrCreate(
                            [
                                'academic_term_id' => $term->id,
                                'day_of_week' => $day,
                                'start_time' => $win['start'],
                                'end_time' => $win['end'],
                            ],
                            [
                                'slot_type' => $win['type'],
                            ]
                        );
                        $key = "{$day}_{$win['start']}_{$win['end']}";
                        $slotsByTerm[$term->id][$key] = $slot;
                    }
                }
            }

            // 3. Seed Timetable Allocations for Term 1 (DEMO 9C Teaching Term)
            // Available Offerings in Term 1:
            // 1: Demo Programming (DEMO-CS101) - Teacher Ayesha
            // 2: Demo Mathematics (DEMO-CS102) - Teacher Hamza
            // 3: Demo Computing Lab (DEMO-CS103) - Teacher Ayesha & Hamza
            // 14: Intro To Mathematics (MTH101) - Teacher Ayesha
            // 15: Computer Networks & Security (IT401) - Teacher Hamza
            if (isset($slotsByTerm[1])) {
                $t1Offerings = CourseOffering::where('academic_term_id', 1)->with('teachers')->get()->keyBy('id');
                $ayesha = Teacher::where('email', 'demo.teacher1@example.test')->first();
                $hamza = Teacher::where('email', 'demo.teacher2@example.test')->first();

                if ($ayesha && $hamza) {
                    $term1Plan = [
                        // Monday
                        [
                            'offering_id' => 1,
                            'room' => 'CS-LAB-01',
                            'day' => 'Monday',
                            'start' => '08:30:00',
                            'end' => '10:00:00',
                            'teacher_id' => $ayesha->id,
                            'notes' => 'Weekly Lab Lecture - Section DEMO-A',
                        ],
                        [
                            'offering_id' => 2,
                            'room' => 'LH-101',
                            'day' => 'Monday',
                            'start' => '10:00:00',
                            'end' => '11:30:00',
                            'teacher_id' => $hamza->id,
                            'notes' => 'Theory Class - Section DEMO-A',
                        ],
                        [
                            'offering_id' => 15,
                            'room' => 'CS-LAB-02',
                            'day' => 'Monday',
                            'start' => '11:30:00',
                            'end' => '13:00:00',
                            'teacher_id' => $hamza->id,
                            'notes' => 'Network Architecture - Section IT-A',
                        ],

                        // Tuesday
                        [
                            'offering_id' => 14,
                            'room' => 'LH-101',
                            'day' => 'Tuesday',
                            'start' => '08:30:00',
                            'end' => '10:00:00',
                            'teacher_id' => $ayesha->id,
                            'notes' => 'Calculus & Algebra - Section IT-A',
                        ],
                        [
                            'offering_id' => 1,
                            'room' => 'CS-LAB-01',
                            'day' => 'Tuesday',
                            'start' => '10:00:00',
                            'end' => '11:30:00',
                            'teacher_id' => $ayesha->id,
                            'notes' => 'Coding Exercises - Section DEMO-A',
                        ],
                        [
                            'offering_id' => 2,
                            'room' => 'LH-102',
                            'day' => 'Tuesday',
                            'start' => '11:30:00',
                            'end' => '13:00:00',
                            'teacher_id' => $hamza->id,
                            'notes' => 'Problem Solving Session - Section DEMO-A',
                        ],

                        // Wednesday
                        [
                            'offering_id' => 15,
                            'room' => 'CS-LAB-02',
                            'day' => 'Wednesday',
                            'start' => '10:00:00',
                            'end' => '11:30:00',
                            'teacher_id' => $hamza->id,
                            'notes' => 'Security Protocols - Section IT-A',
                        ],
                        [
                            'offering_id' => 3,
                            'room' => 'CS-LAB-01',
                            'day' => 'Wednesday',
                            'start' => '14:00:00',
                            'end' => '15:30:00',
                            'teacher_id' => $ayesha->id,
                            'notes' => 'Computing Practical Lab Part 1 - Section DEMO-A',
                        ],
                        [
                            'offering_id' => 3,
                            'room' => 'CS-LAB-01',
                            'day' => 'Wednesday',
                            'start' => '15:30:00',
                            'end' => '17:00:00',
                            'teacher_id' => $hamza->id,
                            'notes' => 'Computing Practical Lab Part 2 - Section DEMO-A',
                        ],

                        // Thursday
                        [
                            'offering_id' => 2,
                            'room' => 'LH-101',
                            'day' => 'Thursday',
                            'start' => '08:30:00',
                            'end' => '10:00:00',
                            'teacher_id' => $hamza->id,
                            'notes' => 'Differential Equations - Section DEMO-A',
                        ],
                        [
                            'offering_id' => 14,
                            'room' => 'LH-102',
                            'day' => 'Thursday',
                            'start' => '10:00:00',
                            'end' => '11:30:00',
                            'teacher_id' => $ayesha->id,
                            'notes' => 'Analytical Geometry - Section IT-A',
                        ],

                        // Friday
                        [
                            'offering_id' => 1,
                            'room' => 'CS-LAB-01',
                            'day' => 'Friday',
                            'start' => '08:30:00',
                            'end' => '10:00:00',
                            'teacher_id' => $ayesha->id,
                            'notes' => 'Project Code Review - Section DEMO-A',
                        ],
                        [
                            'offering_id' => 15,
                            'room' => 'CS-LAB-02',
                            'day' => 'Friday',
                            'start' => '10:00:00',
                            'end' => '11:30:00',
                            'teacher_id' => $hamza->id,
                            'notes' => 'Packet Tracing Lab - Section IT-A',
                        ],
                    ];

                    foreach ($term1Plan as $item) {
                        if (! isset($t1Offerings[$item['offering_id']])) {
                            continue;
                        }
                        $roomObj = $createdRooms[$item['room']] ?? null;
                        $slotKey = "{$item['day']}_{$item['start']}_{$item['end']}";
                        $slotObj = $slotsByTerm[1][$slotKey] ?? null;

                        if ($roomObj && $slotObj) {
                            TimetableAllocation::firstOrCreate(
                                [
                                    'room_id' => $roomObj->id,
                                    'time_slot_id' => $slotObj->id,
                                ],
                                [
                                    'course_offering_id' => $item['offering_id'],
                                    'teacher_id' => $item['teacher_id'],
                                    'notes' => $item['notes'],
                                    'created_by' => $adminUser?->id,
                                ]
                            );
                        }
                    }
                }
            }

            // 4. Seed Timetable Allocations for Term 5 (Fall-2026-T-3 - Student Dawood's Term)
            // Available Offerings in Term 5:
            // 26: Principles of Management (BBA101) - Teacher Hamza (ID: 7)
            // 27: Practical Lab (DEMO-CS103) - Teacher Ayesha (ID: 6)
            // 28: Intro To Mathematics (MTH101) - Teacher Ayesha (ID: 6)
            if (isset($slotsByTerm[5])) {
                $t5Offerings = CourseOffering::where('academic_term_id', 5)->with('teachers')->get()->keyBy('id');
                $ayesha = Teacher::where('email', 'demo.teacher1@example.test')->first();
                $hamza = Teacher::where('email', 'demo.teacher2@example.test')->first();

                if ($ayesha && $hamza) {
                    $term5Plan = [
                        // Monday
                        [
                            'offering_id' => 28,
                            'room' => 'LH-101',
                            'day' => 'Monday',
                            'start' => '08:30:00',
                            'end' => '10:00:00',
                            'teacher_id' => $ayesha->id,
                            'notes' => 'Mathematics Lecture 1 - Section IT-A',
                        ],
                        [
                            'offering_id' => 26,
                            'room' => 'LH-102',
                            'day' => 'Monday',
                            'start' => '10:00:00',
                            'end' => '11:30:00',
                            'teacher_id' => $hamza->id,
                            'notes' => 'Principles of Management - Section IT-A',
                        ],

                        // Tuesday
                        [
                            'offering_id' => 28,
                            'room' => 'LH-101',
                            'day' => 'Tuesday',
                            'start' => '11:30:00',
                            'end' => '13:00:00',
                            'teacher_id' => $ayesha->id,
                            'notes' => 'Mathematics Tutorial - Section IT-A',
                        ],

                        // Wednesday (Practical Lab Afternoon Session)
                        [
                            'offering_id' => 27,
                            'room' => 'CS-LAB-01',
                            'day' => 'Wednesday',
                            'start' => '14:00:00',
                            'end' => '15:30:00',
                            'teacher_id' => $ayesha->id,
                            'notes' => 'Practical Lab Session Part 1 - Section IT-A',
                        ],
                        [
                            'offering_id' => 27,
                            'room' => 'CS-LAB-01',
                            'day' => 'Wednesday',
                            'start' => '15:30:00',
                            'end' => '17:00:00',
                            'teacher_id' => $ayesha->id,
                            'notes' => 'Practical Lab Session Part 2 - Section IT-A',
                        ],

                        // Thursday
                        [
                            'offering_id' => 26,
                            'room' => 'LH-102',
                            'day' => 'Thursday',
                            'start' => '10:00:00',
                            'end' => '11:30:00',
                            'teacher_id' => $hamza->id,
                            'notes' => 'Strategic Management & Case Studies - Section IT-A',
                        ],

                        // Friday
                        [
                            'offering_id' => 28,
                            'room' => 'LH-101',
                            'day' => 'Friday',
                            'start' => '08:30:00',
                            'end' => '10:00:00',
                            'teacher_id' => $ayesha->id,
                            'notes' => 'Weekly Quiz & Review - Section IT-A',
                        ],
                        [
                            'offering_id' => 26,
                            'room' => 'LH-102',
                            'day' => 'Friday',
                            'start' => '10:00:00',
                            'end' => '11:30:00',
                            'teacher_id' => $hamza->id,
                            'notes' => 'Management Presentation - Section IT-A',
                        ],
                    ];

                    foreach ($term5Plan as $item) {
                        if (! isset($t5Offerings[$item['offering_id']])) {
                            continue;
                        }
                        $roomObj = $createdRooms[$item['room']] ?? null;
                        $slotKey = "{$item['day']}_{$item['start']}_{$item['end']}";
                        $slotObj = $slotsByTerm[5][$slotKey] ?? null;

                        if ($roomObj && $slotObj) {
                            TimetableAllocation::firstOrCreate(
                                [
                                    'room_id' => $roomObj->id,
                                    'time_slot_id' => $slotObj->id,
                                ],
                                [
                                    'course_offering_id' => $item['offering_id'],
                                    'teacher_id' => $item['teacher_id'],
                                    'notes' => $item['notes'],
                                    'created_by' => $adminUser?->id,
                                ]
                            );
                        }
                    }
                }
            }
        });
    }
}

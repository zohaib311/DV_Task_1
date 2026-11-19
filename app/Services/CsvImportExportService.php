<?php

namespace App\Services;

use App\Models\Academic\Program;
use App\Models\Department\Department;
use App\Models\Section\Section;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvImportExportService
{
    /**
     * Stream a complete CSV export of all students with UTF-8 BOM.
     */
    public function exportStudents(): StreamedResponse
    {
        $filename = 'students_export_' . date('Y_m_d_His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // CSV Header Row
            fputcsv($handle, [
                'Registration No',
                'Full Name',
                'Email Address',
                'Phone Number',
                'CNIC / Form-B',
                'Department',
                'Degree Program',
                'Section',
                'Semester',
                'Admission Type',
                'Scholarship Name',
                'Scholarship Percentage',
                'Scholarship Status',
                'Intermediate Degree',
                'Intermediate Board',
                'Intermediate Roll No',
                'Intermediate Total Marks',
                'Intermediate Obtained Marks',
                'Intermediate Percentage',
                'Portal Account Linked',
                'Portal Account Email',
                'Admission Date',
            ]);

            Student::with(['department', 'program', 'section', 'user'])
                ->orderBy('id')
                ->chunk(200, function ($students) use ($handle) {
                    foreach ($students as $student) {
                        fputcsv($handle, [
                            $student->registration_no ?? 'N/A',
                            $student->name,
                            $student->email,
                            $student->phone,
                            $student->cnic ?? '',
                            $student->department->name ?? '',
                            $student->program->name ?? '',
                            $student->section->name ?? '',
                            $student->semester ?? '1',
                            $student->admission_type ?? 'regular',
                            $student->scholarship_name ?? '',
                            $student->scholarship_percentage ? number_format((float) $student->scholarship_percentage, 0) : '0',
                            $student->scholarship_status ?? 'none',
                            $student->inter_degree ?? '',
                            $student->inter_board ?? '',
                            $student->inter_roll_no ?? '',
                            $student->inter_total_marks ?? '',
                            $student->inter_obtained_marks ?? '',
                            $student->inter_percentage ? number_format((float) $student->inter_percentage, 2) : '',
                            $student->user ? 'Yes' : 'No',
                            $student->user?->email ?? '',
                            $student->created_at ? $student->created_at->format('Y-m-d H:i:s') : '',
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    /**
     * Download a clean sample CSV template for students import.
     */
    public function downloadStudentsTemplate(): StreamedResponse
    {
        $filename = 'students_sample_template.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            // Header columns
            fputcsv($handle, [
                'name',
                'email',
                'phone',
                'cnic',
                'department',
                'program',
                'section',
                'semester',
                'admission_type',
                'scholarship_name',
                'scholarship_percentage',
                'inter_degree',
                'inter_board',
                'inter_roll_no',
                'inter_total_marks',
                'inter_obtained_marks',
            ]);

            // Sample row 1
            fputcsv($handle, [
                'Muhammad Ali',
                'ali.student@example.com',
                '03001234567',
                '35202-1234567-1',
                'Computer Section (IT)',
                'BS Computer Science',
                'A',
                '1',
                'regular',
                'Merit Scholarship',
                '50',
                'FSc Pre-Engineering',
                'BISE Lahore',
                '109283',
                '1100',
                '950',
            ]);

            // Sample row 2
            fputcsv($handle, [
                'Fatima Zahra',
                'fatima.student@example.com',
                '03119876543',
                '35201-7654321-2',
                'Information Technology',
                'BS Information Technology',
                'B',
                '1',
                'regular',
                '',
                '0',
                'ICS',
                'BISE Rawalpindi',
                '847291',
                '1100',
                '910',
            ]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    /**
     * Safely and gracefully import students from a CSV file.
     */
    public function importStudents(UploadedFile $file): array
    {
        $filePath = $file->getRealPath();
        $handle = fopen($filePath, 'r');

        if (! $handle) {
            return [
                'success' => false,
                'message' => 'Unable to read the uploaded CSV file.',
                'imported_count' => 0,
                'skipped_count' => 0,
                'errors' => ['File read failure'],
            ];
        }

        // Cache departments, programs, and sections
        $departments = Department::all();
        $programs = Program::all();
        $sections = Section::all();
        $defaultDept = $departments->first();
        $defaultSection = $sections->first();

        $rowNumber = 0;
        $headers = [];
        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 4096, ',')) !== false) {
            $rowNumber++;

            // Handle UTF-8 BOM on first column of the first row
            if ($rowNumber === 1) {
                if (isset($row[0])) {
                    $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
                }
                $headers = array_map([$this, 'normalizeHeaderKey'], $row);
                continue;
            }

            // Skip empty rows
            if (! array_filter($row, fn ($val) => trim((string) $val) !== '')) {
                continue;
            }

            // Map row data using normalized headers
            $data = [];
            foreach ($headers as $index => $headerKey) {
                if ($headerKey !== '' && isset($row[$index])) {
                    $data[$headerKey] = trim((string) $row[$index]);
                }
            }

            // 1. Validate Name
            $name = $data['name'] ?? $data['full_name'] ?? $data['student_name'] ?? null;
            if (empty($name)) {
                $skippedCount++;
                $errors[] = "Row {$rowNumber}: Skipped because Student Name is missing.";
                continue;
            }

            // 2. Validate & Sanitize Email
            $email = $data['email'] ?? $data['email_address'] ?? null;
            if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skippedCount++;
                $errors[] = "Row {$rowNumber} ({$name}): Skipped because email address is missing or invalid.";
                continue;
            }

            // Check duplicate email
            if (Student::where('email', $email)->exists()) {
                $skippedCount++;
                $errors[] = "Row {$rowNumber} ({$name}): Skipped because email '{$email}' already exists.";
                continue;
            }

            // 3. Phone (Sanitize to digits, fallback if missing)
            $phoneRaw = $data['phone'] ?? $data['phone_number'] ?? $data['mobile'] ?? '';
            $phone = preg_replace('/\D/', '', (string) $phoneRaw);
            if (strlen($phone) < 10) {
                $phone = '0300' . str_pad((string) (Student::count() + $importedCount + 1), 7, '0', STR_PAD_LEFT);
            } elseif (strlen($phone) > 11) {
                $phone = substr($phone, 0, 11);
            }

            // 4. Resolve Department
            $deptInput = $data['department'] ?? $data['dept'] ?? $data['department_name'] ?? null;
            $deptId = null;
            if ($deptInput) {
                $deptId = $this->resolveDepartmentId($deptInput, $departments);
            }
            if (! $deptId && $defaultDept) {
                $deptId = $defaultDept->id;
            }

            // 5. Resolve Program
            $progInput = $data['program'] ?? $data['degree_program'] ?? $data['program_name'] ?? null;
            $progId = null;
            if ($progInput) {
                $progId = $this->resolveProgramId($progInput, $programs, $deptId);
            }

            // 6. Resolve Section
            $secInput = $data['section'] ?? $data['section_name'] ?? null;
            $secId = null;
            if ($secInput) {
                $secId = $this->resolveSectionId($secInput, $sections, $deptId);
            }
            if (! $secId && $defaultSection) {
                $secId = $defaultSection->id;
            }

            // 7. Academic fields
            $semester = isset($data['semester']) && is_numeric($data['semester']) ? (int) $data['semester'] : 1;
            $admissionType = $data['admission_type'] ?? 'regular';
            if (! in_array($admissionType, ['regular', 'lateral_adp', 'migration'], true)) {
                $admissionType = 'regular';
            }

            // 8. Scholarship fields
            $scholarshipName = ! empty($data['scholarship_name']) ? $data['scholarship_name'] : null;
            $scholarshipPct = isset($data['scholarship_percentage']) && is_numeric($data['scholarship_percentage']) ? (float) $data['scholarship_percentage'] : 0.0;
            $scholarshipStatus = $scholarshipPct > 0 ? 'active' : 'none';

            // 9. Registration No
            $regNo = $data['registration_no'] ?? $data['reg_no'] ?? null;
            if ($regNo && Student::where('registration_no', $regNo)->exists()) {
                $regNo = null; // Let model booted auto-generate unique number
            }

            // 10. Intermediate academics
            $interDegree = $data['inter_degree'] ?? $data['intermediate_degree'] ?? null;
            $interBoard = $data['inter_board'] ?? $data['board'] ?? null;
            $interRollNo = $data['inter_roll_no'] ?? $data['roll_no'] ?? null;
            $interTotal = isset($data['inter_total_marks']) && is_numeric($data['inter_total_marks']) ? (float) $data['inter_total_marks'] : null;
            $interObtained = isset($data['inter_obtained_marks']) && is_numeric($data['inter_obtained_marks']) ? (float) $data['inter_obtained_marks'] : null;
            $interPct = null;
            if ($interTotal && $interObtained && $interTotal > 0) {
                $interPct = round(($interObtained / $interTotal) * 100, 2);
            }

            try {
                DB::transaction(function () use (
                    $name, $email, $phone, $data, $deptId, $progId, $secId, $semester,
                    $admissionType, $scholarshipName, $scholarshipPct, $scholarshipStatus,
                    $regNo, $interDegree, $interBoard, $interRollNo, $interTotal, $interObtained, $interPct
                ) {
                    $student = new Student();
                    if ($regNo) {
                        $student->registration_no = $regNo;
                    }
                    $student->name = $name;
                    $student->email = $email;
                    $student->phone = $phone;
                    $student->cnic = $data['cnic'] ?? null;
                    $student->department_id = $deptId;
                    $student->program_id = $progId;
                    $student->section_id = $secId;
                    $student->semester = $semester;
                    $student->admission_type = $admissionType;
                    $student->scholarship_name = $scholarshipName;
                    $student->scholarship_percentage = $scholarshipPct;
                    $student->scholarship_status = $scholarshipStatus;
                    $student->inter_degree = $interDegree;
                    $student->inter_board = $interBoard;
                    $student->inter_roll_no = $interRollNo;
                    $student->inter_total_marks = $interTotal;
                    $student->inter_obtained_marks = $interObtained;
                    $student->inter_percentage = $interPct;
                    $student->image = 'default-user.png';
                    $student->save();
                });

                $importedCount++;
            } catch (\Throwable $e) {
                $skippedCount++;
                $errors[] = "Row {$rowNumber} ({$name}): Skipped due to database error: " . $e->getMessage();
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'imported_count' => $importedCount,
            'skipped_count' => $skippedCount,
            'errors' => $errors,
            'message' => "Successfully imported {$importedCount} students." . ($skippedCount > 0 ? " ({$skippedCount} rows skipped)." : ''),
        ];
    }

    /**
     * Stream a complete CSV export of all teachers with UTF-8 BOM.
     */
    public function exportTeachers(): StreamedResponse
    {
        $filename = 'teachers_export_' . date('Y_m_d_His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            // CSV Header Row
            fputcsv($handle, [
                'Teacher ID',
                'Full Name',
                'Email Address',
                'Phone Number',
                'Specialization / Course',
                'Department(s)',
                'Portal Account Linked',
                'Portal Account Email',
                'Assigned Classes Count',
                'Joined Date',
            ]);

            Teacher::with(['user', 'offerings.department'])
                ->orderBy('id')
                ->chunk(200, function ($teachers) use ($handle) {
                    foreach ($teachers as $teacher) {
                        $depts = $teacher->offerings->pluck('department.name')->unique()->filter()->implode(', ');
                        fputcsv($handle, [
                            $teacher->id,
                            $teacher->name,
                            $teacher->email,
                            $teacher->phone,
                            $teacher->course ?? 'General Faculty',
                            $depts ?: 'Not Assigned',
                            $teacher->user ? 'Yes' : 'No',
                            $teacher->user?->email ?? '',
                            $teacher->offerings->count(),
                            $teacher->created_at ? $teacher->created_at->format('Y-m-d H:i:s') : '',
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    /**
     * Download a clean sample CSV template for teachers import.
     */
    public function downloadTeachersTemplate(): StreamedResponse
    {
        $filename = 'teachers_sample_template.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'name',
                'email',
                'phone',
                'course',
                'department',
            ]);

            fputcsv($handle, [
                'Dr. Ayesha Malik',
                'ayesha.malik@example.com',
                '03111234567',
                'Computer Science & Software Systems',
                'Computer Section (IT)',
            ]);

            fputcsv($handle, [
                'Prof. Tariq Mehmood',
                'tariq.mehmood@example.com',
                '03217654321',
                'Database Systems & AI',
                'Information Technology',
            ]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    /**
     * Safely and gracefully import teachers from a CSV file.
     */
    public function importTeachers(UploadedFile $file): array
    {
        $filePath = $file->getRealPath();
        $handle = fopen($filePath, 'r');

        if (! $handle) {
            return [
                'success' => false,
                'message' => 'Unable to read the uploaded CSV file.',
                'imported_count' => 0,
                'skipped_count' => 0,
                'errors' => ['File read failure'],
            ];
        }

        $rowNumber = 0;
        $headers = [];
        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 4096, ',')) !== false) {
            $rowNumber++;

            if ($rowNumber === 1) {
                if (isset($row[0])) {
                    $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
                }
                $headers = array_map([$this, 'normalizeHeaderKey'], $row);
                continue;
            }

            if (! array_filter($row, fn ($val) => trim((string) $val) !== '')) {
                continue;
            }

            $data = [];
            foreach ($headers as $index => $headerKey) {
                if ($headerKey !== '' && isset($row[$index])) {
                    $data[$headerKey] = trim((string) $row[$index]);
                }
            }

            // 1. Validate Name
            $name = $data['name'] ?? $data['full_name'] ?? $data['teacher_name'] ?? null;
            if (empty($name)) {
                $skippedCount++;
                $errors[] = "Row {$rowNumber}: Skipped because Teacher Name is missing.";
                continue;
            }

            // 2. Validate & Sanitize Email
            $email = $data['email'] ?? $data['email_address'] ?? null;
            if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skippedCount++;
                $errors[] = "Row {$rowNumber} ({$name}): Skipped because email address is missing or invalid.";
                continue;
            }

            if (Teacher::where('email', $email)->exists()) {
                $skippedCount++;
                $errors[] = "Row {$rowNumber} ({$name}): Skipped because email '{$email}' already exists.";
                continue;
            }

            // 3. Phone (Sanitize to 11 digits, fallback if missing)
            $phoneRaw = $data['phone'] ?? $data['phone_number'] ?? $data['mobile'] ?? '';
            $phone = preg_replace('/\D/', '', (string) $phoneRaw);
            if (strlen($phone) < 10) {
                $phone = '0311' . str_pad((string) (Teacher::count() + $importedCount + 1), 7, '0', STR_PAD_LEFT);
            } elseif (strlen($phone) > 11) {
                $phone = substr($phone, 0, 11);
            }

            // 4. Course / Specialization
            $course = $data['course'] ?? $data['specialization'] ?? $data['subject'] ?? 'General Faculty';

            try {
                Teacher::create([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'course' => $course,
                    'image' => 'default-user.png',
                ]);

                $importedCount++;
            } catch (\Throwable $e) {
                $skippedCount++;
                $errors[] = "Row {$rowNumber} ({$name}): Skipped due to database error: " . $e->getMessage();
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'imported_count' => $importedCount,
            'skipped_count' => $skippedCount,
            'errors' => $errors,
            'message' => "Successfully imported {$importedCount} teachers." . ($skippedCount > 0 ? " ({$skippedCount} rows skipped)." : ''),
        ];
    }

    /**
     * Normalize header strings (e.g. "Full Name" -> "name", "Phone Number" -> "phone").
     */
    private function normalizeHeaderKey(string $key): string
    {
        $clean = strtolower(trim($key));
        $clean = preg_replace('/[^a-z0-9]+/', '_', $clean);
        $clean = trim($clean, '_');

        // Aliases mapping
        return match ($clean) {
            'full_name', 'student_name', 'teacher_name' => 'name',
            'email_address', 'mail' => 'email',
            'phone_number', 'mobile', 'mobile_number', 'contact' => 'phone',
            'cnic_number', 'b_form', 'form_b', 'cnic_form_b' => 'cnic',
            'reg_no', 'registration_number', 'registration' => 'registration_no',
            'dept', 'department_name' => 'department',
            'degree_program', 'program_name' => 'program',
            'section_name', 'sec' => 'section',
            'specialization', 'subject' => 'course',
            'scholarship', 'scholarship_type' => 'scholarship_name',
            'scholarship_pct', 'waiver_percentage' => 'scholarship_percentage',
            'inter_tot_marks', 'total_intermediate_marks' => 'inter_total_marks',
            'inter_obt_marks', 'obtained_intermediate_marks' => 'inter_obtained_marks',
            default => $clean,
        };
    }

    /**
     * Resolve department ID from user input (ID or case-insensitive name).
     */
    private function resolveDepartmentId(string $input, $departments): ?int
    {
        $inputTrimmed = trim($input);
        if (is_numeric($inputTrimmed)) {
            $match = $departments->firstWhere('id', (int) $inputTrimmed);
            if ($match) {
                return $match->id;
            }
        }

        $inputLower = strtolower($inputTrimmed);
        // Exact name match
        $dept = $departments->first(fn ($d) => strtolower($d->name) === $inputLower);
        if ($dept) {
            return $dept->id;
        }

        // Substring / partial match
        $dept = $departments->first(fn ($d) => str_contains(strtolower($d->name), $inputLower) || str_contains($inputLower, strtolower($d->name)));
        return $dept?->id;
    }

    /**
     * Resolve program ID from user input (ID, code, or case-insensitive name).
     */
    private function resolveProgramId(string $input, $programs, ?int $deptId): ?int
    {
        $inputTrimmed = trim($input);
        if (is_numeric($inputTrimmed)) {
            $match = $programs->firstWhere('id', (int) $inputTrimmed);
            if ($match) {
                return $match->id;
            }
        }

        $inputLower = strtolower($inputTrimmed);
        // Exact name or code match
        $prog = $programs->first(fn ($p) => strtolower($p->name) === $inputLower || strtolower($p->code ?? '') === $inputLower);
        if ($prog) {
            return $prog->id;
        }

        // Substring match
        $prog = $programs->first(fn ($p) => str_contains(strtolower($p->name), $inputLower) || str_contains($inputLower, strtolower($p->name)));
        return $prog?->id;
    }

    /**
     * Resolve section ID from user input (ID or case-insensitive name).
     */
    private function resolveSectionId(string $input, $sections, ?int $deptId): ?int
    {
        $inputTrimmed = trim($input);
        if (is_numeric($inputTrimmed)) {
            $match = $sections->firstWhere('id', (int) $inputTrimmed);
            if ($match) {
                return $match->id;
            }
        }

        $inputLower = strtolower($inputTrimmed);
        // First try matching section in the student's department
        if ($deptId) {
            $deptSections = $sections->where('department_id', $deptId);
            $sec = $deptSections->first(fn ($s) => strtolower($s->name) === $inputLower || strtolower('Section ' . $s->name) === $inputLower);
            if ($sec) {
                return $sec->id;
            }
        }

        // Otherwise match any section
        $sec = $sections->first(fn ($s) => strtolower($s->name) === $inputLower || strtolower('Section ' . $s->name) === $inputLower);
        return $sec?->id;
    }
}

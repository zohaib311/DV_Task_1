<?php

namespace App\Http\Controllers\StudentPortal;

use App\Http\Controllers\Controller;
use App\Models\Leave\StudentLeaveRequest;
use App\Services\Leave\StudentLeaveService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class LeaveController extends Controller
{
    public function index(Request $request, StudentLeaveService $leaves)
    {
        $courses = $leaves->studentCourses($request->user())->get();
        $requests = $leaves->requestsForStudent($request->user())->paginate(12);

        return view('student-portal.leaves.index', compact('courses', 'requests'));
    }

    public function store(Request $request, StudentLeaveService $leaves)
    {
        $data = $request->validate([
            'student_enrollment_course_id' => ['required', 'integer'],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
            'leave_type' => ['required', Rule::in(StudentLeaveRequest::TYPES)],
            'reason' => ['required', 'string', 'min:5', 'max:1500'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ]);
        try {
            $leaves->apply($request->user(), $data, $request->file('attachment'));
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors(['leave_request' => 'We could not send your leave request right now. Please try again, or contact the academic office if the problem continues.']);
        }

        return redirect()->route('student.leaves.index')->with('success', 'Leave request sent to the assigned course teacher.');
    }

    public function cancel(Request $request, int $leave, StudentLeaveService $leaves)
    {
        try {
            $leaves->cancel($request->user(), $leave);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['leave_request' => 'We could not cancel this leave request. Please refresh the page and try again.']);
        }

        return back()->with('success', 'Pending leave request cancelled.');
    }

    public function attachment(Request $request, int $leave, StudentLeaveService $leaves)
    {
        return $leaves->downloadForStudent($request->user(), $leave);
    }
}

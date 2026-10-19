<?php

namespace App\Http\Controllers\Teaching;

use App\Http\Controllers\Controller;
use App\Services\Leave\StudentLeaveService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class LeaveController extends Controller
{
    public function index(Request $request, StudentLeaveService $leaves)
    {
        $status = $request->string('status')->toString();
        $requests = $leaves->requestsForTeacher($request->user())
            ->when(in_array($status, ['pending', 'approved', 'rejected', 'cancelled'], true), fn ($query) => $query->where('status', $status))
            ->paginate(15)->withQueryString();

        return view('teaching.leaves.index', compact('requests', 'status'));
    }

    public function show(Request $request, int $leave, StudentLeaveService $leaves)
    {
        $leave = $leaves->requestForTeacher($request->user(), $leave);

        return view('teaching.leaves.show', compact('leave'));
    }

    public function review(Request $request, int $leave, StudentLeaveService $leaves)
    {
        $data = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'teacher_remark' => ['nullable', 'string', 'max:1500'],
        ]);
        try {
            $leaves->review($request->user(), $leave, $data['decision'], $data['teacher_remark'] ?? null);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors(['leave_request' => 'The leave request could not be updated. Please try again, or refresh the page to check its current status.']);
        }

        return redirect()->route('teaching.leaves.show', $leave)->with('success', 'Leave request '.($data['decision'] === 'approved' ? 'approved' : 'rejected').' and the student has been notified.');
    }

    public function attachment(Request $request, int $leave, StudentLeaveService $leaves)
    {
        return $leaves->downloadForTeacher($request->user(), $leave);
    }
}

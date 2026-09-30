@extends('teaching.layout')
@section('heading', 'Class attendance')
@section('description', 'Choose an assigned class to manage sessions and review attendance.')
@section('teaching-content')
    <div class="table-responsive">
        <table class="table academic-table align-middle">
            <thead><tr><th>Course</th><th>Term / Section</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                @forelse($offerings as $offering)
                    <tr>
                        <td><strong>{{ $offering->course_name }}</strong><small>{{ $offering->course_code }}</small></td>
                        <td>{{ $offering->term->name }}<small>Section {{ $offering->section->name }} · {{ $offering->semester }}</small></td>
                        <td>@include('teaching.partials.status', ['status' => $offering->status])</td>
                        <td><a href="{{ route('teaching.attendance.offering', $offering) }}">Open attendance <i class="bi bi-arrow-right" aria-hidden="true"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="academic-empty">No assigned classes. Ask your administrator to assign a course offering.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $offerings->links('pagination::bootstrap-5') }}
@endsection

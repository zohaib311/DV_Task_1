@extends('teaching.layout')
@section('heading', 'Teacher account setup required')
@section('description', 'Your teaching workspace needs a linked teacher profile.')
@section('teaching-content')
    <div class="academic-note" role="alert">
        <strong>Your account is not linked to a teacher profile.</strong>
        <p class="mb-0 mt-2">Please ask an administrator to link your login account in the Teachers section. Once your profile and course assignments are ready, your classes will appear here.</p>
    </div>
    <p class="teaching-footnote">No student or course records are accessible until your teacher profile is linked.</p>
@endsection

<fieldset class="attendance-status-options">
    <legend class="visually-hidden">Attendance for {{ $record->enrollmentCourse->enrollment->student->name }} — choose one status</legend>
    @foreach (['present', 'absent', 'late', 'excused'] as $status)
        <label class="attendance-status-option attendance-status-{{ $status }}">
            <input type="radio" name="records[{{ $index }}][status]" value="{{ $status }}"
                @checked(old('records.'.$index.'.status', $record->status) === $status) required>
            <span class="attendance-status-label">
                <span class="attendance-status-indicator" aria-hidden="true"></span>
                {{ ucfirst($status) }}
            </span>
        </label>
    @endforeach
</fieldset>

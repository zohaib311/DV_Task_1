<div class="row g-3">
    <div class="col-md-6"><label class="form-label" for="academic_year_id">Academic year</label>
        <select class="form-select" id="academic_year_id" name="academic_year_id" required>
            <option value="">Select academic year</option>
            @foreach ($years as $year)<option value="{{ $year->id }}" @selected(old('academic_year_id', $term->academic_year_id ?? null) == $year->id)>{{ $year->name }} ({{ $year->starts_on->format('d M Y') }} – {{ $year->ends_on->format('d M Y') }})</option>@endforeach
        </select>
    </div>
    <div class="col-md-6"><label class="form-label" for="term_name">Term name</label><input class="form-control" id="term_name" name="name" value="{{ old('name', $term->name ?? '') }}" placeholder="e.g. Fall 2026" maxlength="100" required></div>
    <div class="col-md-4"><label class="form-label" for="term_start">Starts on</label><input class="form-control" id="term_start" name="starts_on" type="date" value="{{ old('starts_on', isset($term) ? $term->starts_on->toDateString() : '') }}" required></div>
    <div class="col-md-4"><label class="form-label" for="term_end">Ends on</label><input class="form-control" id="term_end" name="ends_on" type="date" value="{{ old('ends_on', isset($term) ? $term->ends_on->toDateString() : '') }}" required></div>
    <div class="col-md-4"><label class="form-label" for="term_status">Status</label><select class="form-select" id="term_status" name="status">@foreach (isset($term) ? ['planned', 'active', 'closed'] : ['planned', 'active'] as $status)<option value="{{ $status }}" @selected(old('status', $term->status ?? 'planned') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
</div>

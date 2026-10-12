<span @class([
    'academic-badge',
    'is-active' => $status === 'active',
    'is-complete' => in_array($status, ['completed', 'reviewed'], true),
    'is-pending' => in_array($status, ['planned', 'marks_submitted'], true),
    'is-cancelled' => in_array($status, ['cancelled', 'inactive'], true),
])>{{ ucfirst(str_replace('_', ' ', $status)) }}</span>

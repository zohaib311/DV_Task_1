@extends('welcome')

@section('title', 'Website Prospectus Inquiries')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/admissions.css') }}?v={{ filemtime(public_path('css/admissions.css')) }}">
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

    <!-- Header Section -->
    <div class="admission-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-white-50" style="font-size: 0.85rem;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboardView') }}" class="text-white-50 text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admissions.admin.index') }}" class="text-white-50 text-decoration-none">Admissions</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Prospectus Inquiries</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-1 text-white d-flex align-items-center gap-2">
                <i class="bi bi-chat-left-dots-fill"></i>
                Website Prospectus Inquiries
            </h2>
            <p class="mb-0 text-white-50" style="font-size: 0.95rem;">
                Prospective students who reached out through the university homepage inquiry form for program details and prospectus kits.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admissions.admin.index') }}" class="btn btn-light btn-sm fw-semibold shadow-sm text-primary">
                <i class="bi bi-mortarboard me-1"></i> Applications Hub
            </a>
            <a href="{{ url('/#inquiry') }}" target="_blank" class="btn btn-outline-light btn-sm fw-semibold">
                <i class="bi bi-box-arrow-up-right me-1"></i> View Website Form
            </a>
        </div>
    </div>

    <!-- Session Feedback Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill fs-5 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Inquiries Table Card -->
    <div class="admission-table-card">
        <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <span>Inquiry Submissions</span>
                <span class="badge bg-light text-dark border">{{ $inquiries->total() }} records</span>
            </h5>
            <small class="text-muted">Showing page {{ $inquiries->currentPage() }} of {{ $inquiries->lastPage() }}</small>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Prospective Student</th>
                        <th>Contact</th>
                        <th>Program Interest</th>
                        <th style="min-width: 250px;">Message / Query</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th class="text-end" style="min-width: 140px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inquiries as $index => $inq)
                        <tr>
                            <td class="text-muted fw-semibold">{{ $inquiries->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $inq->name }}</div>
                            </td>
                            <td>
                                <div><a href="mailto:{{ $inq->email }}" class="text-decoration-none text-dark small"><i class="bi bi-envelope me-1 text-primary"></i>{{ $inq->email }}</a></div>
                                @if($inq->phone)
                                    <div class="text-muted small"><i class="bi bi-telephone me-1 text-success"></i>{{ $inq->phone }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $inq->program_interest ?? 'General Information' }}</span>
                            </td>
                            <td>
                                <div class="text-dark small" style="white-space: pre-wrap; max-height: 80px; overflow-y: auto;">{{ $inq->message }}</div>
                                @if($inq->admin_notes)
                                    <div class="text-muted small mt-1"><strong>Note:</strong> {{ $inq->admin_notes }}</div>
                                @endif
                            </td>
                            <td>
                                @if($inq->status === 'new')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-asterisk me-1"></i>New</span>
                                @elseif($inq->status === 'contacted')
                                    <span class="badge bg-info text-dark"><i class="bi bi-telephone-outbound me-1"></i>Contacted</span>
                                @else
                                    <span class="badge bg-secondary"><i class="bi bi-check2-all me-1"></i>Closed</span>
                                @endif
                            </td>
                            <td class="text-muted small">
                                <div>{{ $inq->created_at->format('d M Y') }}</div>
                                <div>{{ $inq->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#inquiryStatusModal{{ $inq->id }}">
                                    <i class="bi bi-pencil-square"></i> Update
                                </button>
                            </td>
                        </tr>

                        <!-- Modal for Updating Inquiry Status -->
                        <div class="modal fade" id="inquiryStatusModal{{ $inq->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow">
                                    <form method="POST" action="{{ route('admissions.admin.inquiry-status', $inq->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <div class="modal-header bg-light border-bottom">
                                            <h5 class="modal-title fw-bold">Update Follow-up Status</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <div class="fw-bold">{{ $inq->name }}</div>
                                                <div class="text-muted small">{{ $inq->email }} | {{ $inq->phone }}</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Follow-up Status</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="new" {{ $inq->status === 'new' ? 'selected' : '' }}>New (Uncontacted)</option>
                                                    <option value="contacted" {{ $inq->status === 'contacted' ? 'selected' : '' }}>Contacted / Prospectus Sent</option>
                                                    <option value="closed" {{ $inq->status === 'closed' ? 'selected' : '' }}>Closed / Converted to Applicant</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Counselor Notes / Remarks</label>
                                                <textarea name="admin_notes" class="form-control" rows="3" placeholder="Called applicant, explained BS Computer Science eligibility...">{{ $inq->admin_notes }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light border-top">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary btn-sm fw-semibold">Save Status</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-chat-square-dots fs-1 d-block mb-2 text-secondary"></i>
                                    <h5>No inquiries received yet</h5>
                                    <p class="small mb-0">Website homepage contact submissions will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inquiries->hasPages())
            <div class="p-3 bg-white border-top d-flex justify-content-center">
                {{ $inquiries->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
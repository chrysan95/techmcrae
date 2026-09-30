@extends('layouts.app')

@section('title', 'Leave Requests - Tech McRae')

@section('content')
{{-- <script src="{{ asset('script.js') }}"></script>
@yield('scripts') --}}
<style>
/* ======================= LEAVE REQUEST MODALS =======================  */
.lr-modal-overlay {
    position: fixed; 
    inset: 0; 
    z-index: 10000;
    background: rgba(21, 26, 45, 0.45);
    display: none; 
    align-items: center; 
    justify-content: center;
    padding: 20px;
}

.lr-modal-overlay.open { 
    display: flex; 
}

/* ---- Detail card ---- */
.lr-detail-card {
    background: var(--beige);
    border-radius: 22px;
    width: 100%; 
    max-width: 760px;
    max-height: 90vh; 
    overflow-y: auto;
    padding: 32px 38px;
    box-shadow: 0 20px 60px rgba(21, 26, 45, 0.25);
}

.lr-detail-header { 
    display: flex; 
    justify-content: space-between; 
    align-items: flex-start; 
}

.lr-detail-titlewrap { 
    display: flex; 
    gap: 16px; 
    align-items: center; 
}

.lr-detail-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    background: var(--yellow);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.lr-detail-icon .material-symbols-rounded { 
    color: var(--white); 
    font-size: 30px; 
}

.lr-detail-title { 
    font-family: 'Redotic', sans-serif; 
    font-size: 2.2rem; 
    color: var(--darkblue); 
    line-height: 1; 
}

.lr-detail-sub { 
    display: flex; 
    align-items: center; 
    gap: 10px; 
    margin-top: 6px;
    font-family: 'Redotic', sans-serif; 
    color: rgba(21, 26, 45, 0.5); 
    font-size: 1.05rem; 
    letter-spacing: 1px; 
}

.lr-close { 
    background: none;
    border: none;
    cursor: pointer;
    color: var(--darkblue); 
}

.lr-close .material-symbols-rounded { 
    font-size: 30px; 
}

.lr-detail-grid { 
    display: grid; 
    grid-template-columns: 1fr 1fr; 
    gap: 20px; 
    margin-top: 20px; 
}

.lr-right { 
    text-align: right; 
}

.lr-field-label { 
    font-family: 'Poppins', sans-serif; 
    color: rgba(21, 26, 45, 0.45); 
    font-size: 0.95rem; 
    margin-bottom: 6px; 
}

.lr-name { 
    font-family: 'Redotic', sans-serif; 
    font-size: 2.4rem; 
    color: var(--darkblue); 
    line-height: 1; 
}

.lr-dept { 
    font-family: 'Redotic', sans-serif; 
    font-size: 2.4rem; 
    line-height: 1; 
}

.lr-divider { 
    border: none; 
    border-top: 1px solid rgba(21, 26, 45, 0.15); 
    margin: 22px 0; 
}

.lr-daterange { 
    font-family: 'Redotic', sans-serif; 
    font-size: 1.9rem; 
    color: var(--darkblue); 
    line-height: 1.15; 
}

.lr-days { 
    font-family: 'Redotic', sans-serif; 
    font-size: 4rem; 
    color: var(--red); 
    line-height: 1; 
}

.lr-reason-block { 
    margin-top: 20px; 
}

.lr-reason { 
    font-family: 'Redotic', sans-serif; 
    font-style: italic; 
    font-size: 1.5rem; 
    color: var(--darkblue); 
}

.lr-type { 
    font-family: 'Redotic', sans-serif; 
    font-size: 2.2rem; 
    line-height: 1; 
}

.lr-status { 
    font-family: 'Redotic', sans-serif; 
    font-size: 2.2rem; 
    line-height: 1; 
}

.lr-notes-block { 
    margin-top: 22px; 
}

.lr-notes { 
    font-family: 'Redotic', sans-serif; 
    font-style: italic; 
    font-size: 1.5rem; 
    color: var(--darkblue); 
    line-height: 1.3; 
}

.lr-detail-actions { 
    display: flex; 
    gap: 16px; 
    margin-top: 26px; 
}

.lr-btn { 
    display: inline-flex; 
    align-items: center; 
    justify-content: center; 
    gap: 8px; 
    cursor: pointer; 
    border: none; 
}

.lr-detail-actions .lr-btn { 
    flex: 1; 
    padding: 16px; 
    border-radius: 14px;
    font-family: 'Redotic', sans-serif; 
    font-size: 1.6rem; 
}

.lr-btn-approve { 
    background: var(--green); 
    color: var(--darkgreen); 
}

.lr-btn-reject { 
    background: var(--red); 
    color: var(--white); 
}

/* ---- Confirm card ---- */
.lr-confirm-card {
    background: var(--beige); 
    border-radius: 20px; 
    padding: 30px;
    width: 100%; 
    max-width: 480px; 
    border: 2px solid var(--green); 
    text-align: center;
}

.lr-confirm-card.reject { 
    border-color: var(--red); 
}

.lr-confirm-icon { 
    width: 74px;
    height: 74px;
    border-radius: 50%;
    background: var(--green);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px; 
}

.lr-confirm-card.reject .lr-confirm-icon { 
    background: var(--red); 
}

.lr-confirm-icon .material-symbols-rounded { 
    color: var(--white); 
    font-size: 42px; 
    font-weight: 700; 
}

.lr-confirm-title { 
    font-family: 'Redotic', sans-serif; 
    font-size: 2.2rem; 
    color: var(--darkgreen); 
    margin-bottom: 16px; 
    font-weight: 400; 
}

.lr-confirm-card.reject .lr-confirm-title { 
    color: var(--red); 
}

.lr-note-input { 
    width: 100%; 
    min-height: 90px; 
    resize: vertical; 
    border: 2px solid var(--green);
    border-radius: 14px; 
    padding: 14px; 
    font-family: 'Poppins', sans-serif; 
    font-size: 1rem;
    color: var(--darkblue); 
    background: transparent; 
    outline: none; 
    margin-bottom: 18px; 
}

.lr-confirm-card.reject .lr-note-input { 
    border-color: var(--red); 
}

.lr-note-input::placeholder { 
    color: rgba(21, 26, 45, 0.4); 
    font-weight: 600; 
}

.lr-confirm-actions { 
    display: flex; 
    gap: 14px; 
}

.lr-confirm-submit { 
    flex: 1; 
    padding: 14px; 
    border-radius: 14px; 
    font-family: 'Redotic', sans-serif;
    font-size: 1.4rem; 
    background: var(--green); 
    color: var(--darkgreen); 
}

.lr-confirm-card.reject .lr-confirm-submit { 
    background: var(--red); 
    color: var(--white); 
}

.lr-confirm-cancel { 
    flex: 1; 
    padding: 14px; 
    border-radius: 14px; 
    font-family: 'Redotic', sans-serif;
    font-size: 1.4rem; 
    background: transparent; 
    color: var(--darkblue); 
    border: 2px solid var(--darkblue); 
}

/* ---- Result card ---- */
.lr-result-card {
    background: var(--beige); 
    border-radius: 20px; 
    padding: 30px;
    width: 100%; 
    max-width: 480px; 
    border: 2px solid var(--green); 
    text-align: center;
}

.lr-result-card.reject { 
    border-color: var(--red); 
}

.lr-result-icon { 
    width: 74px;
    height: 74px;
    border-radius: 50%;
    background: var(--green);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px; 
}

.lr-result-card.reject .lr-result-icon { 
    background: var(--red); 
}

.lr-result-icon .material-symbols-rounded { 
    color: var(--white); 
    font-size: 42px; 
    font-weight: 700; 
}

.lr-result-title { 
    font-family: 'Redotic', sans-serif; 
    font-size: 2.2rem; 
    color: var(--darkgreen);
    margin-bottom: 20px; 
    font-weight: 400; 
}

.lr-result-card.reject .lr-result-title { 
    color: var(--red); 
}

.lr-result-gotit { 
    width: 100%; 
    padding: 16px; 
    border-radius: 14px; 
    border: none; 
    cursor: pointer;
    background: var(--darkblue); 
    color: var(--white); 
    font-family: 'Redotic', sans-serif; 
    font-size: 1.5rem; 
}
/* ---- Responsive ---- */
@media (max-width: 600px) {
    .lr-detail-card { 
        padding: 24px 20px; 
    }
    
    .lr-detail-title { 
        font-size: 1.8rem; 
    }
    
    .lr-name, 
    .lr-dept { 
        font-size: 1.8rem; 
    }
    
    .lr-days { 
        font-size: 3rem; 
    }
    
    .lr-daterange { 
        font-size: 1.5rem; 
    }
}
</style>

<div class="records-page">
    <div class="page-header-row">
        <h1 class="page-title">Leave Requests</h1>
        <div class="header-controls">
            <form action="{{ url()->current() }}" method="GET" id="leaveDatePickerForm">
                @php
                    $selectedDateStr = request('date', \Carbon\Carbon::now()->toDateString());
                @endphp
                {{-- Maintain state of active selection tabs within input form tags --}}
                <input type="hidden" name="period" value="{{ $period }}">
                <input type="hidden" name="consumption_view" value="{{ $consumptionView }}">
                
                <div class="date-picker-pill" style="position: relative; cursor: pointer;" onclick="document.getElementById('leaveDatePicker').showPicker()">
                    <span class="material-symbols-rounded">calendar_today</span>
                    <span>{{ \Carbon\Carbon::parse($selectedDateStr)->format('D, j M Y') }}</span>
                    <span class="material-symbols-rounded">expand_more</span>
                    
                    <input type="date" name="date" id="leaveDatePicker" value="{{ $selectedDateStr }}" 
                        style="position: absolute; opacity: 0; width: 100%; height: 100%; left: 0; top: 0; cursor: pointer;"
                        onchange="document.getElementById('leaveDatePickerForm').submit()">
                </div>
            </form>

            {{-- Dynamic Top Filter Pills --}}
            <div class="period-toggle">
                <a href="{{ url()->current().'?date='.$selectedDate->toDateString().'&period=day&consumption_view='.$consumptionView }}" class="period-btn {{ $period === 'day' ? 'active' : '' }}" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">Day</a>
                <a href="{{ url()->current().'?date='.$selectedDate->toDateString().'&period=week&consumption_view='.$consumptionView }}" class="period-btn {{ $period === 'week' ? 'active' : '' }}" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">Week</a>
                <a href="{{ url()->current().'?date='.$selectedDate->toDateString().'&period=month&consumption_view='.$consumptionView }}" class="period-btn {{ $period === 'month' ? 'active' : '' }}" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">Month</a>
            </div>
        </div>
    </div>

    <!-- ======================= Stats + Consumption panel ======================= -->
    <div class="leave-summary-panel">
        <div class="summary-left">
            <div class="total-block">
                <div class="total-number">{{ $stats['total'] }}</div>
                <div class="total-label">Total leave requests</div>
            </div>
            <div class="status-row">
                <div class="status-card">
                    <div class="status-bubble bubble-green"><span class="material-symbols-rounded">check</span></div>
                    <div class="status-count color-darkgreen">{{ $stats['accepted'] }}</div>
                    <div class="status-name">Accepted</div>
                </div>
                <div class="status-card">
                    <div class="status-bubble bubble-red"><span class="material-symbols-rounded">close</span></div>
                    <div class="status-count color-red">{{ $stats['rejected'] }}</div>
                    <div class="status-name">Rejected</div>
                </div>
                <div class="status-card">
                    <div class="status-bubble bubble-yellow"><span class="material-symbols-rounded">schedule</span></div>
                    <div class="status-count color-yellow">{{ $stats['pending'] }}</div>
                    <div class="status-name">Pending</div>
                </div>
            </div>
        </div>

        <div class="summary-right">
            <div class="consumption-header">
                <h3>Leave consumption by category</h3>
                {{-- Dynamic Bottom Filter Pills --}}
                <div class="period-toggle small">
                    <a href="{{ url()->current().'?date='.$selectedDate->toDateString().'&period='.$period.'&consumption_view=weekly' }}" class="period-btn {{ $consumptionView === 'weekly' ? 'active' : '' }}" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">Weekly</a>
                    <a href="{{ url()->current().'?date='.$selectedDate->toDateString().'&period='.$period.'&consumption_view=yearly' }}" class="period-btn {{ $consumptionView === 'yearly' ? 'active' : '' }}" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">Yearly</a>
                </div>
            </div>
            <div class="consumption-bars">
                @foreach($consumption as $cat)
                <div class="bar-row">
                    <div class="bar-label">{{ $cat['name'] }}</div>
                    <div class="bar-track">
                        <div class="bar-fill bar-{{ strtolower($cat['name']) }}" style="width: {{ ($cat['used'] / max($cat['total'],1)) * 100 }}%"></div>
                    </div>
                    <div class="bar-value color-{{ strtolower($cat['name']) }}">
                        <strong>{{ $cat['used'] }}</strong>/<span>{{ $cat['total'] }}d</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ======================= Department tabs + actions ======================= -->
    <div class="filter-bar">
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">All <span class="tab-count">{{ $stats['total'] }}</span></button>
            <button class="filter-tab" data-filter="Pending">Pending <span class="tab-count">{{ $stats['pending'] }}</span></button>
            <button class="filter-tab" data-filter="Approved">Approved <span class="tab-count">{{ $stats['accepted'] }}</span></button>
            <button class="filter-tab" data-filter="Rejected">Rejected <span class="tab-count">{{ $stats['rejected'] }}</span></button>
        </div>
        <div class="filter-actions">
            @if($stats['pending'] > 0)
                <form action="{{ url('/leave/requests/approve-all') }}" method="POST" style="display:inline; margin:0;">
                    @csrf
                    <button type="submit" class="approve-all-btn">
                        <span class="material-symbols-rounded">check</span>
                        Approve All Pending <span class="tab-count">{{ $stats['pending'] }}</span>
                    </button>
                </form>
            @else
                <button class="approve-all-btn" disabled style="opacity: 0.6; cursor: not-allowed;">
                    <span class="material-symbols-rounded">check</span>
                    No Pending Requests
                </button>
            @endif
            <div class="search-box">
                <span class="material-symbols-rounded">search</span>
                <input type="text" placeholder="Search" id="leaveSearch">
            </div>
        </div>
    </div>

    <!-- ======================= Data Table ======================= -->
    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Request ID <span class="sort-icon">⇅</span></th>
                    <th>Name <span class="sort-icon">⇅</span></th>
                    <th>Type</th>
                    <th>Date Range</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                @php
                    // Fetch the department directly from the linked employee structure
                    $empDept = \App\Models\Employee::where('employee_id', $req->employee_id)->value('department');

                    $requestDate = \Carbon\Carbon::parse($req->requested_at ?? now());
                    $year = $requestDate->format('Y');
                    $month = $requestDate->format('m');
                    $paddedSequence = str_pad($req->monthly_sequence_id, 2, '0', STR_PAD_LEFT);
                    $requestCode = 'LR-'.$year.'-'.$month.$paddedSequence;

                    $typeName = match((int)$req->leave_type_id) {
                        1 => 'Annual',
                        2 => 'Sick',
                        3 => 'Maternity',
                        4 => 'Paternity',
                        5 => 'Emergency',
                        6 => 'Unpaid',
                        default => 'Other'
                    };

                    $fromFmt = \Carbon\Carbon::parse($req->leave_from)->format('d M Y');
                    $toFmt   = \Carbon\Carbon::parse($req->leave_to)->format('d M Y');
                @endphp
                <tr data-status="{{ strtolower($req->status) }}"
                    data-id="{{ $req->id }}"
                    data-reqid="{{ $requestCode }}"
                    data-name="{{ $req->name }}"
                    data-dept="{{ $empDept ?? '' }}"
                    data-type="{{ $typeName }}"
                    data-from="{{ $fromFmt }}"
                    data-to="{{ $toFmt }}"
                    data-days="{{ $req->leave_days }}"
                    data-reason="{{ $req->reason ?? 'Just because' }}"
                    data-statuslabel="{{ $req->status }}"
                    data-note="{{ $req->admin_notes ?? '' }}">
                    <td class="cell-id">{{ $requestCode }}</td>
                    <td class="cell-name">{{ $req->name }}</td>
                    <td>
                        <span class="type-pill type-{{ strtolower($typeName) }}">{{ $typeName }}</span>
                    </td>
                    <td class="cell-name">{{ $fromFmt }} - {{ $toFmt }}</td>
                    <td>{{ $req->leave_days }}</td>
                    <td>"{{ $req->reason ?? 'Just because' }}"</td>
                    <td>
                        <span class="status-pill status-{{ strtolower($req->status) }}">{{ $req->status === 'Approved' ? 'Accepted' : $req->status }}</span>
                    </td>
                    <td class="cell-actions">
                        @if($req->status === 'Pending')
                            <button type="button" class="btn-approve js-open-confirm" data-action="approve"><span class="material-symbols-rounded">check</span>Approve</button>
                            <button type="button" class="btn-reject js-open-confirm" data-action="reject"><span class="material-symbols-rounded">close</span></button>
                        @endif
                        <button type="button" class="btn-view js-open-detail"><span class="material-symbols-rounded">visibility</span>View</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="empty-cell">No leave requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ======================= DETAIL MODAL ======================= -->
<div class="lr-modal-overlay" id="leaveDetailModal">
    <div class="lr-detail-card">
        <div class="lr-detail-header">
            <div class="lr-detail-titlewrap">
                <div class="lr-detail-icon"><span class="material-symbols-rounded">calendar_month</span></div>
                <div>
                    <div class="lr-detail-title">Leave Request</div>
                    <div class="lr-detail-sub">
                        <span id="lrReqId">LR-0000-0000</span>
                        <span class="status-pill" id="lrHeaderStatus">Pending</span>
                    </div>
                </div>
            </div>
            <button type="button" class="lr-close" data-close><span class="material-symbols-rounded">close</span></button>
        </div>

        <div class="lr-detail-grid">
            <div>
                <div class="lr-field-label">Employee</div>
                <div class="lr-name" id="lrName">—</div>
            </div>
            <div class="lr-right">
                <div class="lr-field-label">Department</div>
                <div class="lr-dept" id="lrDept">—</div>
            </div>
        </div>

        <hr class="lr-divider">

        <div class="lr-detail-grid">
            <div>
                <div class="lr-field-label">Date Range</div>
                <div class="lr-daterange" id="lrDateRange">—</div>
            </div>
            <div class="lr-right">
                <div class="lr-field-label">Days</div>
                <div class="lr-days" id="lrDays">0</div>
            </div>
        </div>

        <div class="lr-reason-block">
            <div class="lr-field-label">Reason</div>
            <div class="lr-reason" id="lrReason">—</div>
        </div>

        <hr class="lr-divider">

        <div class="lr-detail-grid">
            <div>
                <div class="lr-field-label">Type</div>
                <div class="lr-type" id="lrType">—</div>
            </div>
            <div class="lr-right">
                <div class="lr-field-label">Status</div>
                <div class="lr-status" id="lrStatusText">—</div>
            </div>
        </div>

        <div class="lr-notes-block" id="lrNotesBlock">
            <div class="lr-field-label">Admin notes</div>
            <div class="lr-notes" id="lrNotes">—</div>
        </div>

        <div class="lr-detail-actions" id="lrDetailActions">
            <button type="button" class="lr-btn lr-btn-reject" data-detail-action="reject">
                <span class="material-symbols-rounded">close</span> Reject
            </button>
            <button type="button" class="lr-btn lr-btn-approve" data-detail-action="approve">
                <span class="material-symbols-rounded">check</span> Approve
            </button>
        </div>
    </div>
</div>

<!-- ======================= CONFIRM MODAL ======================= -->
<div class="lr-modal-overlay" id="leaveConfirmModal">
    <div class="lr-confirm-card approve" id="lrConfirmCard">
        <div class="lr-confirm-icon" id="lrConfirmIcon"><span class="material-symbols-rounded">check</span></div>
        <h2 class="lr-confirm-title" id="lrConfirmTitle">Accept this leave request?</h2>
        <form method="POST" action="" id="leaveConfirmForm">
            @csrf
            <textarea name="note" class="lr-note-input" id="lrNoteInput" placeholder="Type a note"></textarea>
            <div class="lr-confirm-actions">
                <button type="submit" class="lr-btn lr-confirm-submit" id="lrConfirmSubmit">
                    <span class="material-symbols-rounded">check</span> <span id="lrConfirmSubmitText">Approve</span>
                </button>
                <button type="button" class="lr-btn lr-confirm-cancel" data-close>Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- ======================= RESULT MODAL ======================= -->
<div class="lr-modal-overlay" id="leaveResultModal">
    <div class="lr-result-card approve" id="lrResultCard">
        <div class="lr-result-icon" id="lrResultIcon"><span class="material-symbols-rounded">check</span></div>
        <h2 class="lr-result-title" id="lrResultTitle">Leave request accepted</h2>
        <button type="button" class="lr-result-gotit" id="lrResultGotit">Got it</button>
    </div>
</div>

@if(session('leave_modal'))
<script>window.__leaveModalResult = "{{ session('leave_modal') }}";</script>
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const base = "{{ url('/leave/requests') }}";

    const deptColors = {
        engineering: 'var(--red)',
        marketing:   'var(--blue)',
        finance:     'var(--darkgreen)',
        hr:          'var(--yellow)',
        design:      '#7A3A6C'
    };
    const typeColors = {
        annual:    'var(--blue)',
        sick:      'var(--red)',
        maternity: '#C7479E',
        paternity: 'var(--yellow)',
        emergency: 'var(--red)',
        unpaid:    'var(--darkgreen)'
    };
    const statusColors = {
        accepted: 'var(--darkgreen)',
        approved: 'var(--darkgreen)',
        pending:  'var(--yellow)',
        rejected: 'var(--red)'
    };

    const detailModal  = document.getElementById('leaveDetailModal');
    const confirmModal = document.getElementById('leaveConfirmModal');
    const resultModal  = document.getElementById('leaveResultModal');

    let currentDetailRow = null;

    const openModal  = (m) => m.classList.add('open');
    const closeModal = (m) => m.classList.remove('open');
    const closeAll   = () => [detailModal, confirmModal, resultModal].forEach(closeModal);

    const statusLabel = (s) => s.toLowerCase() === 'approved' ? 'Accepted' : s;

    function fillDetail(row) {
        const d = row.dataset;
        document.getElementById('lrReqId').textContent = d.reqid;

        const hStatus = document.getElementById('lrHeaderStatus');
        hStatus.textContent = statusLabel(d.statuslabel);
        hStatus.className = 'status-pill status-' + d.statuslabel.toLowerCase();

        document.getElementById('lrName').textContent = d.name;

        const dept = document.getElementById('lrDept');
        dept.textContent = d.dept || '—';
        dept.style.color = deptColors[(d.dept || '').toLowerCase()] || 'var(--darkblue)';

        document.getElementById('lrDateRange').innerHTML = d.from + ' –<br>' + d.to;
        document.getElementById('lrDays').textContent = d.days;
        document.getElementById('lrReason').textContent = '“' + (d.reason || 'Just because') + '”';

        const type = document.getElementById('lrType');
        type.textContent = d.type;
        type.style.color = typeColors[(d.type || '').toLowerCase()] || 'var(--darkblue)';

        const st = document.getElementById('lrStatusText');
        st.textContent = statusLabel(d.statuslabel);
        st.style.color = statusColors[d.statuslabel.toLowerCase()] || 'var(--darkblue)';

        const isPending = d.statuslabel.toLowerCase() === 'pending';
        document.getElementById('lrDetailActions').style.display = isPending ? 'flex' : 'none';

        const notesBlock = document.getElementById('lrNotesBlock');
        if (isPending) {
            notesBlock.style.display = 'none';
        } else {
            notesBlock.style.display = 'block';
            document.getElementById('lrNotes').textContent = d.note ? '“' + d.note + '”' : 'No notes added.';
        }
    }

    function openConfirm(row, action) {
        const form = document.getElementById('leaveConfirmForm');
        form.action = base + '/' + row.dataset.id + '/' + action;
        document.getElementById('lrNoteInput').value = '';

        const card         = document.getElementById('lrConfirmCard');
        const iconSpan     = document.querySelector('#lrConfirmIcon .material-symbols-rounded');
        const title        = document.getElementById('lrConfirmTitle');
        const submitIcon   = document.querySelector('#lrConfirmSubmit .material-symbols-rounded');
        const submitText   = document.getElementById('lrConfirmSubmitText');

        if (action === 'approve') {
            card.classList.remove('reject'); card.classList.add('approve');
            iconSpan.textContent = 'check';
            title.textContent = 'Accept this leave request?';
            submitIcon.textContent = 'check';
            submitText.textContent = 'Approve';
        } else {
            card.classList.remove('approve'); card.classList.add('reject');
            iconSpan.textContent = 'close';
            title.textContent = 'Reject this leave request?';
            submitIcon.textContent = 'close';
            submitText.textContent = 'Reject';
        }
        openModal(confirmModal);
    }

    // View buttons -> detail modal
    document.querySelectorAll('.js-open-detail').forEach(btn => {
        btn.addEventListener('click', function () {
            currentDetailRow = this.closest('tr');
            fillDetail(currentDetailRow);
            openModal(detailModal);
        });
    });

    // Row approve/reject -> confirm modal
    document.querySelectorAll('.js-open-confirm').forEach(btn => {
        btn.addEventListener('click', function () {
            openConfirm(this.closest('tr'), this.dataset.action);
        });
    });

    // Detail modal approve/reject -> confirm modal
    document.querySelectorAll('[data-detail-action]').forEach(btn => {
        btn.addEventListener('click', function () {
            const action = this.dataset.detailAction;
            const row = currentDetailRow;
            closeModal(detailModal);
            if (row) openConfirm(row, action);
        });
    });

    // Close handlers
    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', closeAll);
    });
    
    [detailModal, confirmModal, resultModal].forEach(m => {
        m.addEventListener('click', function (e) {
            if (e.target === m) closeAll();
        });
    });
    
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAll();
    });

    // Reusable result modal
    function showResult(type) {
        const card  = document.getElementById('lrResultCard');
        const icon  = document.querySelector('#lrResultIcon .material-symbols-rounded');
        const title = document.getElementById('lrResultTitle');

        if (type === 'approved') {
            card.classList.remove('reject'); card.classList.add('approve');
            icon.textContent = 'check';
            title.textContent = 'Leave request accepted';
        } else {
            card.classList.remove('approve'); card.classList.add('reject');
            icon.textContent = 'close';
            title.textContent = 'Leave request rejected';
        }
        closeModal(detailModal);
        closeModal(confirmModal);
        openModal(resultModal);
    }

    const confirmForm = document.getElementById('leaveConfirmForm');

    confirmForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const submitBtn = document.getElementById('lrConfirmSubmit');
        const isApprove = confirmForm.action.endsWith('/approve');
        const formData  = new FormData(confirmForm);

        submitBtn.disabled = true;

        try {
            const res = await fetch(confirmForm.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            if (res.status === 419) {
                alert('Your session has expired. The page will reload, please try again.');
                location.reload();
                return;
            }

            if (!res.ok) {
                const text = await res.text();
                console.error('Server responded', res.status, text);
                alert('Save failed (HTTP ' + res.status + ').\n\n' + text.slice(0, 600));
                submitBtn.disabled = false;
                return;
            }

            showResult(isApprove ? 'approved' : 'rejected');

        } catch (err) {
            console.error('Leave action failed:', err);
            alert('Request error: ' + err.message);
            submitBtn.disabled = false;
        }
    });

    // "Got it" -> reload so the table, stats and tabs reflect the new status
    document.getElementById('lrResultGotit').addEventListener('click', function () {
        location.reload();
    });

    if (window.__leaveModalResult) {
        showResult(window.__leaveModalResult === 'approved' ? 'approved' : 'rejected');
    }
});
</script>
@endsection
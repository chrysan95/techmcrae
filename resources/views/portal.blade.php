<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Employee Portal - Tech McRae</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
</head>
<body>

@php
    // Pick balances in display order, with safe fallbacks
    $byId = collect($balances)->keyBy('id');
    $blank = fn($name,$class) => ['name'=>$name,'class'=>$class,'remaining'=>0,'total'=>0,'pct'=>0];
    
    // Determine which parental leave to display on the dashboard card
    $gender = strtolower($employee->gender ?? '');
    $isFemale = in_array($gender, ['f']);
    
    $parentalId = $isFemale ? 3 : 4;
    $parentalName = $isFemale ? 'Maternity' : 'Paternity';
    $parentalClass = $isFemale ? 'maternity' : 'paternity';

    $topOrder = [
        1 => $byId->get(1, $blank('Annual','annual')),
        2 => $byId->get(2, $blank('Sick','sick')),
        5 => $byId->get(5, $blank('Emergency','emergency')),
        $parentalId => $byId->get($parentalId, $blank($parentalName, $parentalClass)),
    ];
@endphp

<!-- ======================= HEADER ======================= -->
<header class="portal-header">
    <div class="ph-date" id="headerDate">{{ $headerDate }}</div>
    <div class="ph-right">
        <div class="ph-name">{{ $employee->name }}{{ $employee->department ? ', '.$employee->department : '' }}</div>
        <div class="ph-actions">
            @if($isAdmin)
                <a href="{{ url('/dashboard') }}" class="ph-btn ph-btn-ghost">Go back to Admin Page</a>
            @endif
            <form action="{{ url('/logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="ph-btn ph-btn-solid">Sign Out</button>
            </form>
        </div>
    </div>
</header>

<!-- ======================= CLOCK CARD ======================= -->
<section class="clock-card">
    <div class="clock-time" id="bigClock">{{ explode(' ', now('Asia/Jakarta')->format('H:i'))[0] }}<span class="wib">WIB</span></div>

    @if($clock['state'] === 'clocked_in')
        <button class="clock-circle state-clocked_in" data-open-modal="clockOutModal" title="Clock Out">
            <span class="material-symbols-rounded">stop</span>
        </button>
    @elseif($clock['state'] === 'day_complete')
        <div class="clock-circle state-day_complete" title="Shift complete">
            <span class="material-symbols-rounded">check</span>
        </div>
    @else
        <button class="clock-circle state-ready_to_clock_in" data-open-modal="clockInModal" title="Clock In">
            <span class="material-symbols-rounded">play_arrow</span>
        </button>
    @endif

    <div class="clock-info">
        <div class="info-box">
            <div class="info-label">CLOCKED IN</div>
            <div class="info-value">{{ $clock['check_in'] }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">WORKED</div>
            <div class="info-value worked" id="workedValue">{{ $clock['worked'] }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">LOCATION</div>
            <div class="info-value">{{ $clock['location'] }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">EXPECTED OUT</div>
            <div class="info-value">
                {{ $clock['expected_out'] }}
                @if($clock['state'] !== 'ready_to_clock_in')
                    <span class="material-symbols-rounded {{ $clock['state'] === 'day_complete' ? 'eo-ok' : 'eo-no' }}" id="expectedIcon">{{ $clock['state'] === 'day_complete' ? 'check' : 'close' }}</span>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- ======================= MID PANEL ======================= -->
<section class="mid-panel">
    <!-- Leave status -->
    <div class="ls-block">
        <div class="mp-title">Leave Status</div>
        @if($leaveStatus)
            @php
                $isPending  = $leaveStatus['status'] === 'Pending';
                $isDecided  = in_array($leaveStatus['status'], ['Approved','Rejected']);
            @endphp
            <div class="ls-range">{{ $leaveStatus['from'] }} &rarr; {{ $leaveStatus['to'] }} <small>· {{ $leaveStatus['days'] }}d</small></div>
            <div class="ls-reason">"{{ $leaveStatus['reason'] }}"</div>
            <div class="ls-pills">
                <span class="type-pill type-{{ $leaveStatus['type_class'] }}">{{ $leaveStatus['type'] }}</span>
                <span class="status-pill status-{{ strtolower($leaveStatus['status']) }}">{{ $leaveStatus['status'] }}</span>
            </div>
            <div class="ls-timeline-wrap">
                <div class="ls-timeline">
                    <div class="ls-steps">
                        <div class="ls-step done">
                            <div class="ls-dot"><span class="material-symbols-rounded">check</span></div>
                            <div class="ls-step-label">Leave requested</div>
                            <div class="ls-step-sub">completed</div>
                        </div>
                        <div class="ls-line done"></div>
                        <div class="ls-step {{ $isPending ? 'active' : 'done' }}">
                            <div class="ls-dot">@if($isPending)<span class="material-symbols-rounded">sync</span>@else<span class="material-symbols-rounded">check</span>@endif</div>
                            <div class="ls-step-label">Admin Review</div>
                            <div class="ls-step-sub">{{ $isPending ? 'on progress' : 'done' }}</div>
                        </div>
                        <div class="ls-line {{ $isDecided ? 'done' : '' }}"></div>
                        <div class="ls-step {{ $isDecided ? 'done' : '' }}">
                            <div class="ls-dot">@if($isDecided)<span class="material-symbols-rounded">check</span>@else 3 @endif</div>
                            <div class="ls-step-label">Completed</div>
                            <div class="ls-step-sub">{{ $isDecided ? strtolower($leaveStatus['status']) : '' }}</div>
                        </div>
                    </div>
                </div>
                <div class="ls-actions">
                    @if($isPending)
                        <form action="{{ url('/portal/leave/'.$leaveStatus['id'].'/withdraw') }}" method="POST" style="margin:0;">
                            @csrf
                            <button type="submit" class="ls-withdraw-btn">Withdraw</button>
                        </form>
                    @endif
                </div>
            </div>
        @else
            <div class="ls-empty-msg">No leave requests yet. Request one to get started.</div>
        @endif
    </div>

    <!-- Request new leave -->
    <button class="request-leave-btn" data-open-modal="requestLeaveModal">
        <span class="plus">+</span>
        <span class="rl-text">request<br>new leave</span>
    </button>

    <!-- Leave balance -->
    <div class="balance-block">
        <div class="mp-title">Leave Balance</div>
        <div class="balance-grid" style="grid-template-columns:1fr 1fr 1fr;">
            @foreach($topOrder as $b)
            <div class="bal-card">
                <div class="bal-name c-{{ $b['class'] }}">{{ $b['name'] }} Leave</div>
                <div class="bal-num c-{{ $b['class'] }}">{{ $b['remaining'] }}<span>/{{ $b['total'] }}d</span></div>
                <div class="bal-track"><div class="bal-fill f-{{ $b['class'] }}" style="width: {{ $b['pct'] }}%"></div></div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ======================= BOTTOM GRID ======================= -->
<section class="bottom-grid">
    <!-- Recent attendance -->
    <div>
        <h2 class="section-title title-dark">Recent Attendance</h2>
        <div class="att-table-wrap">
            <table class="att-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Clock In <span class="sort-icon">⇅</span></th>
                        <th>Clock Out <span class="sort-icon">⇅</span></th>
                        <th>Total Hours <span class="sort-icon">⇅</span></th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAttendances as $r)
                    <tr>
                        <td>{{ $r['date'] }}@if($r['is_today'])<span class="today-badge">TODAY</span>@endif</td>
                        <td>{{ $r['clock_in'] }}</td>
                        <td>{{ $r['clock_out'] }}</td>
                        <td class="{{ $r['hours'] === 'On progress' ? 'on-progress' : '' }}">{{ $r['hours'] }}</td>
                        <td><span class="att-pill att-{{ $r['status_class'] }}">{{ $r['status'] }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;padding:30px;">No attendance records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent leave -->
    <div>
        <h2 class="section-title title-red">Recent Leave</h2>
        <div class="leave-table-wrap">
            <table class="leave-table">
                <thead>
                    <tr>
                        <th>Request ID <span class="sort-icon">⇅</span></th>
                        <th>Type</th>
                        <th>Date Range</th>
                        <th>Days</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLeaves as $lv)
                    <tr>
                        <td class="leave-id">{{ $lv['id'] }}</td>
                        <td><span class="type-pill type-{{ $lv['type_class'] }}">{{ $lv['type'] }}</span></td>
                        <td>{{ $lv['range'] }}</td>
                        <td>{{ $lv['days'] }}</td>
                        <td><button type="button" class="leave-view-btn"
                                data-dbid="{{ $lv['db_id'] }}"
                                data-id="{{ $lv['id'] }}"
                                data-type="{{ $lv['type'] }}"
                                data-range="{{ $lv['range'] }}"
                                data-days="{{ $lv['days'] }}"
                                data-status="{{ $lv['status'] }}"
                                data-reason="{{ $lv['reason'] ?? 'No reason' }}"
                                data-note="{{ $lv['note'] ?? '' }}"> 
                                <span class="material-symbols-rounded">visibility</span>View</button>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;padding:30px;color:rgba(255,244,234,.6);">No leave requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>


<!--  ======================= MODALS ======================= -->
<!-- LEAVE DETAIL -->
<div class="modal-overlay" id="leaveDetailModal">
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
            <button type="button" class="lr-close" data-close-modal><span class="material-symbols-rounded">close</span></button>
        </div>

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

        <div class="lr-notes-block" id="lrNotesBlock" style="margin-top: 22px;">
            <div class="lr-field-label">Admin notes</div>
            <div class="lr-notes" id="lrNotes" style="font-family: 'Redotic', sans-serif; font-style: italic; font-size: 1.5rem; color: var(--darkblue); line-height: 1.3;">—</div>
        </div>

        <div class="lr-detail-actions" id="lrDetailActions">
            <form action="" method="POST" id="lrWithdrawForm" style="width:100%; margin:0; display:flex;">
                @csrf
                <button type="submit" class="lr-btn lr-btn-reject">
                    <span class="material-symbols-rounded">delete</span> Withdraw Request
                </button>
            </form>
        </div>
    </div>
</div>
<!-- CLOCK IN -->
<div class="modal-overlay" id="clockInModal">
    <div class="modal clock-modal">
        <div class="cm-banner cm-banner-green">
            <div class="cm-banner-date" id="headerDateMirror">{{ $headerDate }}</div>
            <div class="cm-banner-time" id="ciNow">{{ now('Asia/Jakarta')->format('H:i') }}</div>
        </div>
        <form action="{{ url('/portal/clock-in') }}" method="POST" id="clockInForm">
            @csrf
            <input type="hidden" name="type" id="ciType" value="WFO">
            <div class="cm-choices">
                <div class="cm-choice selected" data-type="WFO">
                    <div class="cm-choice-pre">Work From</div>
                    <div class="cm-choice-main">Office</div>
                </div>
                <div class="cm-choice" data-type="WFH">
                    <div class="cm-choice-pre">Work From</div>
                    <div class="cm-choice-main">Home</div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" data-close-modal>Cancel</button>
                <button type="submit" class="btn-confirm btn-confirm-green">
                    <span class="material-symbols-rounded">check</span>Confirm Clock In
                </button>
            </div>
        </form>
    </div>
</div>

<!-- CLOCK OUT -->
<div class="modal-overlay" id="clockOutModal">
    <div class="modal clock-modal">
        <div class="cm-banner cm-banner-red">
            <div class="cm-banner-date">{{ $headerDate }}</div>
            <div class="cm-banner-time" id="coNow">{{ now('Asia/Jakarta')->format('H:i') }}</div>
        </div>
        <div class="cm-readout">
            <div class="cm-readbox">
                <div class="lbl">Clock In</div>
                <div class="val">{{ $clock['check_in'] }}</div>
            </div>
            <div class="cm-readbox">
                <div class="lbl">Clock Out</div>
                <div class="val" id="coNow2">{{ now('Asia/Jakarta')->format('H:i') }}</div>
            </div>
        </div>
        <div class="cm-worked-box">
            <div class="lbl">Worked Today</div>
            <div class="val" id="coWorked">{{ $clock['worked'] !== '—' ? $clock['worked'] : '00:00:00' }}</div>
        </div>
        <form action="{{ url('/portal/clock-out') }}" method="POST">
            @csrf
            <div class="modal-actions">
                <button type="button" class="btn-cancel" data-close-modal>Cancel</button>
                <button type="submit" class="btn-confirm btn-confirm-red">
                    <span class="material-symbols-rounded">check</span>Confirm Clock Out
                </button>
            </div>
        </form>
    </div>
</div>

<!-- REQUEST LEAVE -->
<div class="modal-overlay" id="requestLeaveModal">
    <div class="modal">
        <div class="rl-title">Request Leave</div>
        <form action="{{ url('/portal/leave-request') }}" method="POST" id="requestLeaveForm">
            @csrf
            <input type="hidden" name="leave_type_id" id="rlTypeId" value="">
            <div class="rl-balance-grid">
                @foreach($balances as $b)
                <div class="rl-bal b-{{ $b['class'] }}" data-type-id="{{ $b['id'] }}">
                    <div class="rl-bal-name c-{{ $b['class'] }}">{{ $b['name'] }} Leave</div>
                    <div class="rl-bal-num c-{{ $b['class'] }}">{{ $b['remaining'] }}<span>/{{ $b['total'] }}d</span></div>
                    <div class="rl-bal-track"><div class="rl-bal-fill f-{{ $b['class'] }}" style="width: {{ $b['pct'] }}%"></div></div>
                </div>
                @endforeach
            </div>

            <div class="rl-dates">
                <div class="rl-date-block">
                    <label>From</label>
                    <input type="date" name="leave_from" id="rlFrom" value="{{ now('Asia/Jakarta')->toDateString() }}">
                    <label>To</label>
                    <input type="date" name="leave_to" id="rlTo" value="{{ now('Asia/Jakarta')->toDateString() }}">
                </div>
                <div class="rl-days">
                    <div class="lbl">Day(s)</div>
                    <div class="val" id="rlDays">1</div>
                </div>
            </div>

            <textarea class="rl-reason" name="reason" id="rlReason" placeholder="Type a reason"></textarea>
            <div class="rl-error" id="rlError"></div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" data-close-modal>Cancel</button>
                <button type="submit" class="btn-confirm btn-confirm-blue" id="rlConfirm">
                    <span class="material-symbols-rounded">check</span>Confirm Leave Request
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SUCCESS: CLOCK IN -->
<div class="modal-overlay" id="successClockInModal">
    <div class="modal success-modal">
        <div class="success-icon green"><span class="material-symbols-rounded">check</span></div>
        <div class="success-title green">You're clocked in!</div>
        <div class="success-sub">Have a great shift!</div>
        <button class="success-btn" data-close-modal onclick="location.href='{{ url('/portal') }}'">Got it</button>
    </div>
</div>

<!-- SUCCESS: CLOCK OUT -->
<div class="modal-overlay" id="successClockOutModal">
    <div class="modal success-modal">
        <div class="success-icon dark"><span class="material-symbols-rounded">check</span></div>
        <div class="success-title dark">Attendance saved!</div>
        <div class="success-sub">Thank you for your hard work!</div>
        <button class="success-btn" data-close-modal onclick="location.href='{{ url('/portal') }}'">Got it</button>
    </div>
</div>

<!-- SUCCESS: LEAVE -->
<div class="modal-overlay" id="successLeaveModal">
    <div class="modal success-modal">
        <div class="success-icon dark"><span class="material-symbols-rounded">check</span></div>
        <div class="success-title dark">Leave requested!</div>
        <div class="success-sub">Your request is now pending admin review.</div>
        <button class="success-btn" data-close-modal onclick="location.href='{{ url('/portal') }}'">Got it</button>
    </div>
</div>

<script>
    window.PORTAL = {
        state:           @json($clock['state']),
        checkInEpochMs:  @json($clock['check_in_epoch_ms']),
        expectedEpochMs: @json($clock['expected_epoch_ms']),
        openModal:       @json(session('open_modal'))
    };
</script>
<script src="{{ asset('portal.js') }}"></script>
</body>
</html>

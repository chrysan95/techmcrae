@extends('layouts.app')

@section('title', 'Leave Status - Tech McRae')

@section('content')
<script src="{{ asset('script.js') }}"></script>
@yield('scripts')
<div class="leavestatus-page">

    {{-- ======================= Header ======================= --}}
    <div class="ls-header-row">
        <div class="ls-title-group">
            <h1 class="ls-page-title">Leave Status</h1>
            <form action="{{ url()->current() }}" method="GET" id="lsDateForm">
                <input type="hidden" name="view" value="{{ $view }}">
                <div class="date-picker-pill" style="position:relative;cursor:pointer;" onclick="document.getElementById('lsDatePicker').showPicker()">
                    <span class="material-symbols-rounded">calendar_today</span>
                    <span>{{ $selectedDate->format('D, j M Y') }}</span>
                    <span class="material-symbols-rounded">expand_more</span>
                    <input type="date" name="date" id="lsDatePicker" value="{{ $selectedDate->toDateString() }}"
                        style="position:absolute;opacity:0;width:100%;height:100%;left:0;top:0;cursor:pointer;"
                        onchange="document.getElementById('lsDateForm').submit()">
                </div>
            </form>
        </div>
        <div class="period-toggle">
            @foreach(['day'=>'Day','week'=>'Week','month'=>'Month'] as $v => $lbl)
                <a href="{{ url()->current().'?date='.$selectedDate->toDateString().'&view='.$v }}"
                   class="period-btn {{ $view === $v ? 'active' : '' }}"
                   style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;">{{ $lbl }}</a>
            @endforeach
        </div>
    </div>

    {{-- ======================= Top grid ======================= --}}
    <div class="ls-top-grid">
        {{-- Summary --}}
        <div class="ls-card ls-summary">
            <div class="ls-big-line">
                <span class="ls-big-number">{{ $onLeaveCount }}</span>
                <span class="ls-big-text">people currently<br>on leave</span>
            </div>
            <div class="ls-subboxes">
                <div class="ls-subbox">
                    <div class="ls-subbox-label">Upcoming Leave</div>
                    @forelse($upcoming as $u)
                        <div class="ls-mini-item">{{ $u['name'] }} <span>· {{ $u['when'] }}</span></div>
                    @empty
                        <div class="ls-mini-empty">Nothing scheduled.</div>
                    @endforelse
                </div>
                <div class="ls-subbox">
                    <div class="ls-subbox-label">Returning in ≤ 7 days</div>
                    @forelse($returningSoon as $r)
                        <div class="ls-mini-item">{{ $r['name'] }} <span>· {{ $r['dow'] }} {{ $r['dom'] }}</span></div>
                    @empty
                        <div class="ls-mini-empty">No one returning soon.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Team rate --}}
        <div class="ls-card">
            <div class="ls-teamrate-head">
                <h3>On leave rate by team</h3>
                @if($thinCount > 0)
                    <span class="ls-thin-badge"><span class="material-symbols-rounded">warning</span>{{ $thinCount }} TEAM THIN</span>
                @endif
            </div>
            @foreach($teamRates as $t)
                <div class="ls-team-row">
                    <div class="ls-team-name">{{ $t['dept'] }}</div>
                    <div class="ls-team-track">
                        <div class="ls-team-fill {{ $t['thin'] ? 'fill-thin' : ($t['mid'] ? 'fill-mid' : 'fill-ok') }}" style="width:{{ $t['pct'] }}%"></div>
                    </div>
                    <div class="ls-team-val {{ $t['thin'] ? 'is-thin' : '' }}">
                        <span class="frac">{{ $t['on'] }}/{{ $t['tot'] }}</span><span class="pct">{{ $t['pct'] }}%</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ======================= Gantt =======================--}}
    <div class="ls-gantt-card">
        <div class="ls-gantt-head">
            <span class="ls-gantt-title">Leave Gantt Chart</span>
            <div class="date-picker-pill" style="cursor:default;">
                <span class="material-symbols-rounded">calendar_today</span>
                <span>{{ $ganttRange }}</span>
            </div>
        </div>

        <div class="gantt-scroll">
            <div class="gantt">
                <div class="gantt-header">
                    <div class="g-meta">Name</div>
                    <div class="gantt-days">
                        <span class="gantt-month-tag">{{ $ganttMonth }}</span>
                        @foreach($ganttDays as $d)
                            <div class="gantt-day {{ $d['today'] ? 'is-today' : '' }}">{{ $d['num'] }}</div>
                        @endforeach
                    </div>
                </div>

                <div class="gantt-rows">
                    <div class="gantt-today"><div class="gantt-today-line" style="left:{{ $todayPos }}%"></div></div>

                    @forelse($gantt as $g)
                        <div class="gantt-row">
                            <div class="g-meta">
                                <span class="g-name">{{ $g['name'] }}</span>
                                <span class="g-sub">{{ $g['dept'] }} · {{ $g['days'] }}d</span>
                            </div>
                            <div class="g-track">
                                <div class="g-bar {{ $g['pending'] ? 'pending' : 'bar-'.$g['type_class'] }} {{ $g['short'] ? 'tiny' : '' }}"
                                     style="left:{{ $g['left'] }}%;width:{{ $g['width'] }}%;">
                                    {{ $g['short'] ? $g['initial'] : $g['label'] }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="gantt-row"><div class="g-meta"><span class="g-sub">No leave in this window.</span></div><div class="g-track"></div></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ======================= Bottom grid ======================= --}}
    <div class="ls-bottom-grid">
        {{-- Currently on leave (white/blue striped table) --}}
        <div class="ls-col-card">
            <h2 class="ls-section-title">Currently on leave</h2>
            <table class="ls-onleave-table data-table">
                <thead>
                    <tr>
                        <th>Name <span class="sort-icon">⇅</span></th>
                        <th>Days Left</th>
                        <th>Window</th>
                        <th>Approver</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($onLeave as $p)
                        <tr>
                            <td>
                                <div class="ls-cell-name">{{ $p['name'] }}</div>
                                <div class="ls-pill-row">
                                    <span class="type-pill type-{{ $p['type_class'] }}">{{ $p['type'] }}</span>
                                    <span class="dept-pill dept-{{ $p['dept_class'] }}">{{ $p['dept'] }}</span>
                                </div>
                            </td>
                            <td>
                                @if($p['soon'])
                                    <div class="ls-daysleft soon">returns</div>
                                    <div class="ls-daysleft-sub">tomorrow</div>
                                @else
                                    <div class="ls-daysleft">{{ $p['days_left'] }}</div>
                                    <div class="ls-daysleft-sub">of {{ $p['total'] }}d total</div>
                                @endif
                            </td>
                            <td>
                                <div class="ls-window-dates">{{ $p['window'] }}</div>
                                <div class="ls-window-day">day {{ $p['elapsed'] }} of {{ $p['total'] }}</div>
                                <div class="ls-window-track">
                                    <div class="ls-window-fill bar-{{ $p['type_class'] }}" style="width:{{ $p['progress'] }}%"></div>
                                </div>
                            </td>
                            <td class="ls-approver">{{ $p['approver'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="ls-empty">No one is currently on leave.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{-- Returning soon --}}
            <div class="ls-returning-card">
                <h2 class="ls-section-title">Returning soon</h2>
                @forelse($returningSoon as $r)
                    <div class="ls-return-item">
                        <div class="ls-date-chip" style="background:{{ $r['color'] }}">
                            <span class="dow">{{ $r['dow'] }}</span>
                            <span class="dom">{{ $r['dom'] }}</span>
                        </div>
                        <div class="ls-return-name">{{ $r['name'] }} <span>· {{ $r['dept'] }}</span></div>
                        <span class="type-pill type-{{ $r['type_class'] }}">{{ $r['type'] }}</span>
                    </div>
                @empty
                    <div class="ls-empty">No one returning within 7 days.</div>
                @endforelse
            </div>

            {{-- Recent decisions --}}
            <div class="ls-col-card">
                <h2 class="ls-section-title">Recent decisions</h2>
                @forelse($recent as $d)
                    @php $isApproved = $d['status'] === 'Approved'; @endphp
                    <div class="ls-decision-item">
                        <div class="ls-decision-icon {{ $isApproved ? 'approved' : 'rejected' }}">
                            <span class="material-symbols-rounded">{{ $isApproved ? 'check' : 'close' }}</span>
                        </div>
                        <div>
                            <div class="ls-decision-main">
                                {{ $d['name'] }} · {{ $d['type'] }} ·
                                <span class="ls-decision-status {{ $isApproved ? 'approved' : 'rejected' }}">{{ strtoupper($d['status']) }}</span>
                            </div>
                            <div class="ls-decision-reason">{{ $d['reason'] }}</div>
                            @if($d['admin_note'])
                                <div class="ls-decision-reason" style="font-style: italic; color: #666; margin-top: 4px;">
                                    Notes: "{{ $d['admin_note'] }}"
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="ls-empty">No recent decisions.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
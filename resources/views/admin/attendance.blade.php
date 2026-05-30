@extends('layouts.app')

@section('title', 'Attendance Records - Tech McRae')

@section('content')
<div class="records-page">
    <div class="page-header-row">
        <h1 class="page-title">Attendance Records</h1>
        <form action="{{ url()->current() }}" method="GET" id="datePickerForm">
            <div class="date-picker-pill" style="position: relative; cursor: pointer;" onclick="document.getElementById('attendanceDatePicker').showPicker()">
                <span class="material-symbols-rounded">calendar_today</span>
                <span>{{ \Carbon\Carbon::parse($date)->format('D, j M Y') }}</span>
                <span class="material-symbols-rounded">expand_more</span>
                <input type="date" name="date" id="attendanceDatePicker" value="{{ $date }}" 
                    style="position: absolute; opacity: 0; width: 100%; height: 100%; left: 0; top: 0; cursor: pointer;"
                    onchange="document.getElementById('datePickerForm').submit()">
            </div>
        </form>
    </div>

    <div class="filter-bar">
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">All <span class="tab-count">{{ $records->count() }}</span></button>
            <button class="filter-tab" data-filter="wfo">WFO <span class="tab-count">{{ $counts['wfo'] }}</span></button>
            <button class="filter-tab" data-filter="wfh">WFH <span class="tab-count">{{ $counts['wfh'] }}</span></button>
            <button class="filter-tab" data-filter="on-time">On Time <span class="tab-count">{{ $counts['on_time'] }}</span></button>
            <button class="filter-tab" data-filter="late">Late <span class="tab-count">{{ $counts['late'] }}</span></button>
            <button class="filter-tab" data-filter="absent">Absent <span class="tab-count">{{ $counts['absent'] }}</span></button>
            <button class="filter-tab" data-filter="on-leave">On Leave <span class="tab-count">{{ $counts['on_leave'] }}</span></button>
        </div>
        <div class="search-box">
            <span class="material-symbols-rounded">search</span>
            <input type="text" placeholder="Search" id="attendanceSearch">
        </div>
    </div>

    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Employee ID <span class="sort-icon">⇅</span></th>
                    <th>Name <span class="sort-icon">⇅</span></th>
                    <th>Attendance Date</th>
                    <th>Work From</th>
                    <th>Clock In <span class="sort-icon">⇅</span></th>
                    <th>Clock Out <span class="sort-icon">⇅</span></th>
                    <th>Total Hours <span class="sort-icon">⇅</span></th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                <tr>
                    <td class="cell-id">{{ $r['employee_id'] }}</td>
                    <td class="cell-name">{{ $r['name'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($r['date'])->format('d M Y') }}</td>
                    <td>
                        <span class="work-pill work-{{ strtolower($r['type'] === 'WFO' ? 'office' : 'home') }}">
                            {{ $r['type'] === 'WFO' ? 'Office' : 'Home' }}
                        </span>
                    </td>
                    <td class="cell-time">{{ $r['check_in'] ?? '—' }}</td>
                    <td class="cell-time">{{ $r['check_out'] ?? '—' }}</td>
                    <td>{{ $r['total_hours'] ?? '—' }}</td>
                    <td>
                        <span class="status-pill status-{{ str_replace(' ', '-', strtolower($r['status'])) }}">{{ $r['status'] }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-icon"><span class="material-symbols-rounded">error</span></div>
                            <div class="empty-title">No records found</div>
                            <div class="empty-subtitle">Try clearing filters or search for something else.</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
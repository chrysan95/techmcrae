@extends('layouts.app')

@section('title', 'Employee Records - Tech McRae')

@section('content')
<div class="records-page">
    <h1 class="page-title">Employee Records</h1>

    <div class="filter-bar">
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">All <span class="tab-count">{{ $employees->count() }}</span></button>
            @foreach($departments as $dept => $count)
                <button class="filter-tab" data-filter="{{ $dept }}">{{ $dept }} <span class="tab-count">{{ $count }}</span></button>
            @endforeach
        </div>
        <div class="search-box">
            <span class="material-symbols-rounded">search</span>
            <input type="text" placeholder="Search" id="employeeSearch">
        </div>
    </div>

    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Employee ID <span class="sort-icon">⇅</span></th>
                    <th>Name <span class="sort-icon">⇅</span></th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Address</th>
                    <th>Birth Date <span class="sort-icon">⇅</span></th>
                    <th>Department</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr data-department="{{ $emp->department }}">
                    <td class="cell-id">{{ $emp->employee_id }}</td>
                    <td class="cell-name">{{ $emp->name }}</td>
                    <td>{{ $emp->email }}</td>
                    <td>{{ $emp->phone_number ?? '—' }}</td>
                    <td>{{ $emp->address ?? '—' }}</td>
                    <td>{{ $emp->birth_date ? \Carbon\Carbon::parse($emp->birth_date)->format('d M Y') : '—' }}</td>
                    <td>
                        <span class="dept-pill dept-{{ strtolower($emp->department ?? 'none') }}">{{ $emp->department ?? '—' }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="empty-cell">No employees found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
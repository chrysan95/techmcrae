<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $adminName = Auth::user()->name;

        // 1. Get the requested period (today, week, month) and reference date
        $period = $request->input('period', 'today');
        $date = $request->input('date', Attendance::max('attendance_date') ?? now()->toDateString()); 
        $carbonDate = Carbon::parse($date); 

        // Include everyone (Admins + Employees) in the base count
        $totalEmployees = Employee::count(); 

        $leaveQuery = LeaveRequest::where('status', 'Approved'); 
        $attendanceQuery = Attendance::where('status', 'Check In'); 

        // 2. Set date boundaries based on selected period toggle
        if ($period === 'week') {
            $startOfWeek = $carbonDate->copy()->startOfWeek()->toDateString();
            $endOfWeek = $carbonDate->copy()->endOfWeek()->toDateString();

            $onLeaveIds = $leaveQuery->whereBetween('leave_from', [$startOfWeek, $endOfWeek])
                ->orWhereBetween('leave_to', [$startOfWeek, $endOfWeek])
                ->pluck('employee_id')->toArray();

            $checkIns = $attendanceQuery->whereBetween('attendance_date', [$startOfWeek, $endOfWeek])->get();
        } elseif ($period === 'month') {
            $startOfMonth = $carbonDate->copy()->startOfMonth()->toDateString();
            $endOfMonth = $carbonDate->copy()->endOfMonth()->toDateString();

            $onLeaveIds = $leaveQuery->whereBetween('leave_from', [$startOfMonth, $endOfMonth])
                ->orWhereBetween('leave_to', [$startOfMonth, $endOfMonth])
                ->pluck('employee_id')->toArray();

            $checkIns = $attendanceQuery->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])->get();
        } else {
            // Default: today
            $onLeaveIds = $leaveQuery->whereDate('leave_from', '<=', $date) 
                ->whereDate('leave_to', '>=', $date) 
                ->pluck('employee_id')->toArray(); 

            $checkIns = $attendanceQuery->whereDate('attendance_date', $date)->get(); 
        }

        // Process metric totals
        $wfoCount = $checkIns->where('type', 'WFO')->unique('employee_id')->count();
        $wfhCount = $checkIns->where('type', 'WFH')->unique('employee_id')->count();
        $onLeaveCount = count(array_unique($onLeaveIds));

        $checkedInIds = $checkIns->pluck('employee_id')->unique()->toArray();
        $absentCount = Employee::whereNotIn('employee_id', array_merge($checkedInIds, $onLeaveIds))->count();

        // Map stats array with safe fallbacks matching demo parameters
        $stats = [
            'employees' => $totalEmployees, 
            'wfo'       => $wfoCount,
            'wfh'       => $wfhCount,
            'absent'    => $absentCount,
            'on_leave'  => $onLeaveCount,
        ];

        $pieData = [
            'labels' => ['Work From Office', 'Work From Home', 'On Leave', 'Absent'], 
            'values' => [$stats['wfo'], $stats['wfh'], $stats['on_leave'], $stats['absent']] 
        ];

        // 3. Handle AJAX engine request from Frontend Javascript toggle
        if ($request->ajax()) {
            $total = max(array_sum($pieData['values']), 1);
            $colors = ['blue', 'darkgreen', 'yellow', 'red'];
            $htmlLegend = '';

            foreach ($pieData['labels'] as $i => $label) {
                $pct = number_format(($pieData['values'][$i] / $total) * 100, 2);
                $htmlLegend .= "<li>
                    <span class='legend-dot dot-{$colors[$i]}'></span>
                    <span class='legend-name'>{$label}</span>
                    <span class='legend-value'>{$pieData['values'][$i]}</span>
                    <span class='legend-pct'>{$pct}%</span>
                </li>";
            }

            return response()->json([
                'stats'   => $stats,
                'pieData' => [
                    'values'      => $pieData['values'],
                    'html_legend' => $htmlLegend
                ]
            ]);
        }

        // ===== Overall Attendance line chart (Fixed Calendar Alignment) =====
        $currentYear = Carbon::parse($date)->year;
        
        $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $thisYear = [];
        $lastYear = [];
        
        foreach (range(1, 12) as $monthNum) {
            // 1. Calculate This Year's Month data precisely
            $monthCheckIns = Attendance::whereYear('attendance_date', $currentYear)
                ->whereMonth('attendance_date', $monthNum)
                ->where('status', 'Check In')
                ->distinct('employee_id')
                ->count('employee_id');

            $thisYear[] = $totalEmployees > 0
                ? round(($monthCheckIns / $totalEmployees) * 100, 1)
                : 0;

            // 2. Calculate Last Year's Month data precisely
            $lastYearCheckIns = Attendance::whereYear('attendance_date', $currentYear - 1)
                ->whereMonth('attendance_date', $monthNum)
                ->where('status', 'Check In')
                ->distinct('employee_id')
                ->count('employee_id');

            $lastYear[] = $totalEmployees > 0
                ? round(($lastYearCheckIns / $totalEmployees) * 100, 1)
                : 0;
        }

        // Renamed variable to $chartData and adjusted keys to snake_case matching dashboard.blade.php
        $chartData = [
            'labels'     => $months,
            'this_year'  => $thisYear,
            'last_year'  => $lastYear,
            'avg'        => count($thisYear) > 0 ? round(array_sum($thisYear) / count($thisYear), 1) : 0,
            'peak_month' => count($thisYear) > 0 ? $months[array_search(max($thisYear), $thisYear)] : '-',
            'peak_value' => count($thisYear) > 0 ? max($thisYear) : 0,
            'yoy_change' => count($thisYear) > 0 && count($lastYear) > 0 
                ? round(((array_sum($thisYear) / count($thisYear)) - (array_sum($lastYear) / count($lastYear))), 1)
                : 0,
        ];

        // ===== Notifications Processing (Includes All 17 Admins + Staff) =====
        $notifications = [];

        // 1. Fetch the 3 most recent Pending leave requests from anyone
        $pendingLeaves = LeaveRequest::with('employee')
            ->where('status', 'Pending')
            ->orderBy('requested_at', 'desc')
            ->take(3)
            ->get();

        foreach ($pendingLeaves as $lv) {
            // Add safety check in case relationship is missing
            $empName = $lv->employee ? $lv->employee->name : 'An employee';
            $quotaType = $lv->leaveType ? $lv->leaveType->leave_name : 'Unknown';

            $notifications[] = [
                'type'  => 'leave',
                'icon'  => 'person',
                'color' => 'darkblue',
                'html'  => sprintf(
                    '<strong>%s</strong> is asking for a leave from <strong>%s</strong> to <strong>%s</strong> using the <strong>%s</strong> quota.',
                    e($empName),
                    Carbon::parse($lv->leave_from)->format('j F Y'),
                    Carbon::parse($lv->leave_to)->format('j F Y'),
                    e($quotaType)
                ),
            ];
        }

        // 2. Track consecutive absences across your entire staff pool over the last 15 days
        $absentEmployees = Employee::whereNotIn('employee_id', $checkedInIds)
            ->whereNotIn('employee_id', $onLeaveIds)
            ->take(2)
            ->get();

        foreach ($absentEmployees as $emp) {
            $daysCheckedIn = Attendance::where('attendance_date', '>=', Carbon::parse($date)->subDays(15))
                ->where('attendance_date', '<=', $date)
                ->where('status', 'Check In')
                ->where('employee_id', $emp->employee_id)
                ->count();
                
            $absentDays = 15 - $daysCheckedIn;

            $notifications[] = [
                'type'  => 'absent',
                'icon'  => 'person_off',
                'color' => 'red',
                'html'  => sprintf(
                    '<strong>%s</strong> is absent for <strong>%d day(s)</strong>.', 
                    e($emp->name), 
                    $absentDays
                ),
            ];
        }

        // 3. Track staff members currently out on approved leave today
        $currentLeaves = LeaveRequest::with('employee')->where('status', 'Approved')
            ->whereDate('leave_from', '<=', $date)
            ->whereDate('leave_to', '>=', $date)
            ->take(2)
            ->get();

        foreach ($currentLeaves as $lv) {
            $daysUsed = Carbon::parse($lv->leave_from)->diffInDays(Carbon::parse($date)) + 1;
            $empName = $lv->employee ? $lv->employee->name : 'An employee';
            
            $notifications[] = [
                'type'  => 'leave_active',
                'icon'  => 'logout',
                'color' => 'yellow',
                'html'  => sprintf(
                    '<strong>%s</strong> is currently on leave (Day %d).',
                    e($empName),
                    $daysUsed
                ),
            ];
        }

        // Keep a clean feed of the 5 most relevant updates
        $notifications = array_slice($notifications, 0, 5);

        // RETURN MUST ALWAYS HAPPEN AT THE END
        return view('admin.dashboard', compact('adminName', 'stats', 'chartData', 'pieData', 'notifications'));
    }
}
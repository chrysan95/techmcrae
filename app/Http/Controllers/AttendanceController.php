<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', Attendance::max('attendance_date') ?? now()->toDateString());

        $rows = Attendance::where('attendance_date', $date)->get()->groupBy('employee_id');
        
        $employees = Employee::all()->keyBy('employee_id');
        
        $onLeaveIds = LeaveRequest::where('status','Approved')
            ->whereDate('leave_from','<=',$date)
            ->whereDate('leave_to','>=',$date)
            ->pluck('employee_id')->toArray();

        $records = [];
        foreach ($employees as $emp) {
            $group = $rows->get($emp->employee_id);
            $onLeave = in_array($emp->employee_id, $onLeaveIds);

            $checkIn = $group?->firstWhere('status','Check In');
            $checkOut = $group?->firstWhere('status','Check Out');

            $totalHours = '—';
            if ($checkIn && $checkOut) {
                // Parse correctly and force absolute value to prevent negative times
                $ci = Carbon::parse($checkIn->attendance_hour);
                $co = Carbon::parse($checkOut->attendance_hour);
                $diff = abs($ci->diffInMinutes($co, false));
                
                $totalHours = floor($diff / 60) . 'h ' . ($diff % 60) . 'm';
            }

            $status = 'Absent';
            if ($onLeave) $status = 'On Leave';
            elseif ($checkIn) {
                $status = Carbon::parse($checkIn->attendance_hour)->gt(Carbon::parse('08:30:00')) ? 'Late' : 'On Time';
            }

            // Append custom label identifier if user is an admin
            $displayName = $emp->role === 'admin' ? $emp->name . ' (Admin)' : $emp->name;

            $records[] = [
                'employee_id' => $emp->employee_id,
                'name' => $displayName,
                'date' => $date,
                'type' => $checkIn->type ?? 'WFO',
                'check_in' => $checkIn ? Carbon::parse($checkIn->attendance_hour)->format('H:i') : null,
                'check_out' => $checkOut ? Carbon::parse($checkOut->attendance_hour)->format('H:i') : null,
                'total_hours' => $totalHours,
                'status' => $status,
            ];
        }

        $records = collect($records);
        $counts = [
            'wfo' => $records->where('type','WFO')->count(),
            'wfh' => $records->where('type','WFH')->count(),
            'on_time' => $records->where('status','On Time')->count(),
            'late' => $records->where('status','Late')->count(),
            'absent' => $records->where('status','Absent')->count(),
            'on_leave' => $records->where('status','On Leave')->count(),
        ];

        return view('admin.attendance', compact('records','counts','date'));
    }
}
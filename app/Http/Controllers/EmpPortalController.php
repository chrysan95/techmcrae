<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmpPortalController extends Controller
{
    private array $typeMap = [
        1 => 'Annual', 2 => 'Sick', 3 => 'Maternity',
        4 => 'Paternity', 5 => 'Emergency', 6 => 'Unpaid',
    ];

    private array $colorMap = [
        'Annual' => 'annual', 'Sick' => 'sick', 'Maternity' => 'maternity',
        'Paternity' => 'paternity', 'Emergency' => 'emergency', 'Unpaid' => 'unpaid',
    ];

     private function typeAllowed(int $typeId, $employee): bool
    {
        $gender = strtolower($employee->gender ?? '');

        if ($typeId === 3) return $gender === 'f';
        if ($typeId === 4) return $gender === 'm';

        return true;
    }

    private function leavePeriod(LeaveType $type, Carbon $date): array
    {
        return $type->reset_frequency === 'Monthly'
            ? [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()]
            : [$date->copy()->startOfYear(), $date->copy()->endOfYear()];
    }

    // quota = allocation_limit − (approved + pending days) inside the reset period
    // pending requests reserve days, so an employee cant stack requests
    private function leaveBalance(string $employeeId, LeaveType $type, Carbon $date): array
    {
        [$start, $end] = $this->leavePeriod($type, $date);

        $base = LeaveRequest::where('employee_id', $employeeId)
            ->where('leave_type_id', $type->leave_type_id)
            ->whereBetween('leave_from', [$start->toDateString(), $end->toDateString()]);

        $approved = (int) (clone $base)->where('status', 'Approved')->sum('leave_days');
        $pending  = (int) (clone $base)->where('status', 'Pending')->sum('leave_days');
        $total    = (int) ($type->allocation_limit ?? 0);

        return [
            'total'     => $total,
            'approved'  => $approved,
            'pending'   => $pending,
            'available' => max($total - $approved - $pending, 0),
        ];
    }

    public function index()
    {
        $employee = Auth::user();
        $tz       = 'Asia/Jakarta';
        $now      = Carbon::now($tz);
        $today    = $now->toDateString();

        // ============================================================
        //  LIVE CLOCK STATE
        // ============================================================
        $todayRecords = Attendance::where('employee_id', $employee->employee_id)
            ->where('attendance_date', $today)->get();

        $checkIn  = $todayRecords->where('status', 'Check In')->first();
        $checkOut = $todayRecords->where('status', 'Check Out')->first();

        $clock = [
            'state'              => 'ready_to_clock_in',
            'check_in_epoch_ms'  => null,
            'expected_epoch_ms'  => null,
            'check_in'           => '—',
            'check_out'          => '—',
            'location'           => '—',
            'expected_out'       => '—',
            'worked'             => '—',
            'met_expected'       => false,
        ];

        if ($checkIn) {
            $ci = Carbon::parse($today . ' ' . $checkIn->attendance_hour, $tz);
            $expected = $ci->copy()->addHours(8);

            $clock['check_in_epoch_ms'] = $ci->timestamp * 1000;
            $clock['expected_epoch_ms'] = $expected->timestamp * 1000;
            $clock['check_in']          = $ci->format('H:i');
            $clock['expected_out']      = $expected->format('H:i');
            $clock['location']          = $checkIn->type === 'WFO' ? 'Office' : 'Home';

            if ($checkOut) {
                $co  = Carbon::parse($today . ' ' . $checkOut->attendance_hour, $tz);
                $min = $ci->diffInMinutes($co);
                $clock['state']     = 'day_complete';
                $clock['check_out'] = $co->format('H:i');
                $clock['worked']    = sprintf('%02d:%02d:00', intdiv($min, 60), $min % 60);
                $clock['met_expected'] = $co->gte($expected);
            } else {
                $clock['state'] = 'clocked_in';
            }
        }

        // ============================================================
        //  RECENT ATTENDANCE
        // ============================================================
        $approvedLeaves = LeaveRequest::where('employee_id', $employee->employee_id)
            ->where('status', 'Approved')->get();

        $recentAttendances = [];
        for ($i = 0; $i < 14; $i++) {
            $d  = $now->copy()->subDays($i);
            $ds = $d->toDateString();
            $isToday = $ds === $today;

            $recs = Attendance::where('employee_id', $employee->employee_id)
                ->where('attendance_date', $ds)->get();
            $ci = $recs->where('status', 'Check In')->first();
            $co = $recs->where('status', 'Check Out')->first();

            $onLeave = $approvedLeaves->first(function ($lv) use ($ds) {
                return $ds >= Carbon::parse($lv->leave_from)->toDateString()
                    && $ds <= Carbon::parse($lv->leave_to)->toDateString();
            });

            if (!$ci && !$co && !$onLeave && !$isToday) {
                continue;
            }

            $row = [
                'date'         => $d->format('d M Y'),
                'is_today'     => $isToday,
                'clock_in'     => '—',
                'clock_out'    => '—',
                'hours'        => '—',
                'status'       => '—',
                'status_class' => 'none',
            ];

            if ($onLeave) {
                $row['status'] = 'On Leave';
                $row['status_class'] = 'on-leave';
            } elseif ($ci) {
                $cit = Carbon::parse($ds . ' ' . $ci->attendance_hour, $tz);
                $row['clock_in'] = $cit->format('H:i');
                $row['status'] = $ci->type === 'WFO' ? 'Office' : 'Home';
                $row['status_class'] = $ci->type === 'WFO' ? 'office' : 'home';

                if ($co) {
                    $cot = Carbon::parse($ds . ' ' . $co->attendance_hour, $tz);
                    $m   = $cit->diffInMinutes($cot);
                    $row['clock_out'] = $cot->format('H:i');
                    $row['hours'] = floor($m / 60) . 'h ' . str_pad($m % 60, 2, '0', STR_PAD_LEFT) . 'm';
                } elseif ($isToday) {
                    $row['hours'] = 'On progress';
                }
            }
            $recentAttendances[] = $row;
        }

        // ============================================================
        //  RECENT LEAVE
        // ============================================================
        $leaves = LeaveRequest::where('employee_id', $employee->employee_id)
            ->orderBy('requested_at', 'desc')->orderBy('id', 'desc')->get();

        $recentLeaves = [];
        foreach ($leaves as $lv) {
            $rd   = Carbon::parse($lv->requested_at ?? $lv->leave_from);
            $seq  = LeaveRequest::whereYear('requested_at', $rd->year)
                ->where('id', '<=', $lv->id)->count();
            $type = $this->typeMap[(int) $lv->leave_type_id] ?? 'Other';

            $recentLeaves[] = [
                'db_id'      => $lv->id, // <--- ADD THIS LINE
                'id'         => 'LR-' . $rd->format('Y') . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT),
                'type'       => $type,
                'type_class' => $this->colorMap[$type] ?? 'annual',
                'range'      => Carbon::parse($lv->leave_from)->format('d M Y') . ' - ' . Carbon::parse($lv->leave_to)->format('d M Y'),
                'days'       => $lv->leave_days,
                'status'     => $lv->status,
                'reason'     => $lv->reason,
                'note'       => $lv->admin_notes
            ];
        }

        // ============================================================
        //  LEAVE BALANCES (Filtered by Gender & Custom Rules)
        // ============================================================
        $leaveTypes = LeaveType::all();
        $balances   = [];

        $gender   = strtolower($employee->gender ?? ''); 
        $isMale   = in_array($gender, ['m']);
        $isFemale = in_array($gender, ['f']);

        foreach ($leaveTypes as $lt) {
            $tid = (int) $lt->leave_type_id;

            // only show Maternity if F, Paternity if M both hidden if null/unrecognized
            if ($tid === 3 && !$isFemale) continue;
            if ($tid === 4 && !$isMale) continue;

            if (!$this->typeAllowed($tid, $employee)) continue;

            // 3. Get leave balances
            // Ensure $now is defined in your method scope ($now = Carbon::now())
            $bal = $this->leaveBalance($employee->employee_id, $lt, $now);
            
            $name = $this->typeMap[$tid] ?? trim(str_ireplace('Leave', '', $lt->leave_name ?? 'Other'));

            $balances[$tid] = [
                'id'        => $tid,
                'name'      => $name,
                'remaining' => $bal['available'],
                'total'     => $bal['total'],
                'class'     => $this->colorMap[$name] ?? 'annual',
                'pct'       => $bal['total'] > 0 ? min(($bal['available'] / $bal['total']) * 100, 100) : 0,
            ];
        }

        // ============================================================
        //  CURRENT LEAVE STATUS
        // ============================================================
        $current = LeaveRequest::where('employee_id', $employee->employee_id)
            ->where('status', 'Pending')->orderBy('requested_at', 'desc')->first()
            ?? LeaveRequest::where('employee_id', $employee->employee_id)
                ->orderBy('requested_at', 'desc')->first();

        $leaveStatus = null;
        if ($current) {
            $type = $this->typeMap[(int) $current->leave_type_id] ?? 'Other';
            $leaveStatus = [
                'id'         => $current->id,
                'from'       => Carbon::parse($current->leave_from)->format('d M Y'),
                'to'         => Carbon::parse($current->leave_to)->format('d M Y'),
                'days'       => $current->leave_days,
                'type'       => $type,
                'type_class' => $this->colorMap[$type] ?? 'annual',
                'reason'     => $current->reason ?: 'No reason provided',
                'status'     => $current->status,
            ];
        }

        $isAdmin = $employee->role === 'admin';
        $headerDate = $now->format('l, j M Y');

        return view('portal', compact(
            'employee', 'isAdmin', 'headerDate', 'clock',
            'recentAttendances', 'recentLeaves',
            'balances', 'leaveStatus', 'leaveTypes'
        ));
    }

    // ================================================================
    //  CLOCK IN
    // ================================================================
    public function clockIn(Request $request)
    {
        $request->validate(['type' => 'required|in:WFO,WFH']);

        $employeeId = Auth::user()->employee_id;
        $today      = Carbon::today('Asia/Jakarta')->toDateString();

        $alreadyIn = Attendance::where('employee_id', $employeeId)
            ->where('attendance_date', $today)->where('status', 'Check In')->exists();
        if ($alreadyIn) return back()->withErrors(['msg' => 'Already clocked in today.']);

        Attendance::create([
            'employee_id'     => $employeeId,
            'attendance_date' => $today,
            'attendance_hour' => Carbon::now('Asia/Jakarta')->toTimeString(),
            'type'            => $request->type,
            'status'          => 'Check In',
        ]);

        return back()->with('open_modal', 'success_clock_in');
    }

    // ================================================================
    //  CLOCK OUT
    // ================================================================
    public function clockOut()
    {
        $employeeId = Auth::user()->employee_id;
        $today      = Carbon::today('Asia/Jakarta')->toDateString();

        $checkIn = Attendance::where('employee_id', $employeeId)
            ->where('attendance_date', $today)->where('status', 'Check In')->first();
        if (!$checkIn) return back()->withErrors(['msg' => 'No active check-in session found today.']);

        $alreadyOut = Attendance::where('employee_id', $employeeId)
            ->where('attendance_date', $today)->where('status', 'Check Out')->exists();
        if ($alreadyOut) return back()->withErrors(['msg' => 'Already clocked out today.']);

        Attendance::create([
            'employee_id'     => $employeeId,
            'attendance_date' => $today,
            'attendance_hour' => Carbon::now('Asia/Jakarta')->toTimeString(),
            'type'            => $checkIn->type,
            'status'          => 'Check Out',
        ]);

        return back()->with('open_modal', 'success_clock_out');
    }

    // ================================================================
    //  REQUEST LEAVE
    // ================================================================
        public function requestLeave(Request $request)
    {
        $data = $request->validate([
            'leave_type_id' => 'required|integer|exists:leave_types,leave_type_id',
            'leave_from'    => 'required|date',
            'leave_to'      => 'required|date|after_or_equal:leave_from',
            'reason'        => 'nullable|string|max:500',
        ], [
            'leave_type_id.required' => 'Please select a leave type.',
            'leave_to.after_or_equal' => 'End date must be on or after the start date.',
        ]);

        $employee = Auth::user();
        $type     = LeaveType::findOrFail($data['leave_type_id']);
        $typeId   = (int) $type->leave_type_id;
        $typeName = $this->typeMap[$typeId] ?? $type->leave_name;

        $from = Carbon::parse($data['leave_from'], 'Asia/Jakarta')->startOfDay();
        $to   = Carbon::parse($data['leave_to'], 'Asia/Jakarta')->startOfDay();
        $days = $from->diffInDays($to) + 1;

        $fail = fn (string $msg) => back()->withErrors(['leave' => $msg])->withInput();

        if (!$this->typeAllowed($typeId, $employee)) {
            return $fail("You are not eligible for {$typeName} Leave.");
        }

        return DB::transaction(function () use ($employee, $type, $typeId, $typeName, $from, $to, $days, $data, $fail) {
            // Lock this employee's row so two quick submits can't both pass the checks
            Employee::where('employee_id', $employee->employee_id)->lockForUpdate()->first();

            // 1. No overlap with an existing Pending / Approved request
            $overlap = LeaveRequest::where('employee_id', $employee->employee_id)
                ->whereIn('status', ['Pending', 'Approved'])
                ->whereDate('leave_from', '<=', $to->toDateString())
                ->whereDate('leave_to', '>=', $from->toDateString())
                ->orderBy('leave_from')
                ->first();

            if ($overlap) {
                $label = $overlap->status === 'Approved' ? 'an approved' : 'a pending';
                return $fail(sprintf(
                    'You already have %s leave from %s to %s that overlaps these dates.',
                    $label,
                    Carbon::parse($overlap->leave_from)->format('d M Y'),
                    Carbon::parse($overlap->leave_to)->format('d M Y')
                ));
            }

            // 2. Requested days must fit the remaining quota for that period
            $bal = $this->leaveBalance($employee->employee_id, $type, $from);

            if ($days > $bal['available']) {
                $period = $type->reset_frequency === 'Monthly' ? $from->format('F Y') : $from->format('Y');
                $msg = "You requested {$days} day(s) but only have {$bal['available']} day(s) of {$typeName} Leave left for {$period}";
                $msg .= $bal['pending'] > 0 ? " ({$bal['pending']} day(s) already pending)." : '.';
                return $fail($msg);
            }

            LeaveRequest::create([
                'employee_id'   => $employee->employee_id,
                'leave_type_id' => $typeId,
                'leave_from'    => $from->toDateString(),
                'leave_to'      => $to->toDateString(),
                'leave_days'    => $days,
                'reason'        => $data['reason'] ?? null,
                'status'        => 'Pending',
                'requested_at'  => Carbon::now('Asia/Jakarta'),
            ]);

            return back()->with('open_modal', 'success_leave');
        });
    }

    // ================================================================
    //  WITHDRAW (only own, pending requests)
    // ================================================================
    public function withdrawLeave($id)
    {
        $lv = LeaveRequest::where('id', $id)
            ->where('employee_id', Auth::user()->employee_id)
            ->where('status', 'Pending')->first();

        if ($lv) $lv->delete();

        return back();
    }
}
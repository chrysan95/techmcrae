<?php
namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveStatusController extends Controller
{
    private array $typeMap = [
        1 => ['name' => 'Annual',    'class' => 'annual'],
        2 => ['name' => 'Sick',      'class' => 'sick'],
        3 => ['name' => 'Maternity', 'class' => 'maternity'],
        4 => ['name' => 'Paternity', 'class' => 'paternity'],
        5 => ['name' => 'Emergency', 'class' => 'emergency'],
        6 => ['name' => 'Unpaid',    'class' => 'unpaid'],
    ];

    private array $typeColor = [
        'annual'    => '#7EACB5',
        'sick'      => '#BF4646',
        'maternity' => '#E8B33D',
        'paternity' => '#C7479E',
        'emergency' => '#C44545',
        'unpaid'    => '#5B7E3C',
        'personal'  => '#8a63c4',
    ];

    private function type($id): array
    {
        return $this->typeMap[(int) $id] ?? ['name' => 'Personal', 'class' => 'personal'];
    }

    public function index(Request $request)
    {
        $selectedDate = Carbon::parse($request->get('date', Carbon::now()->toDateString()));
        $today        = $selectedDate->copy()->startOfDay();
        $todayStr     = $today->toDateString();
        $view         = $request->get('view', 'week');

        $employees      = Employee::all()->keyBy('employee_id');
        $totalEmployees = $employees->count();

        // ---------- Currently on leave (Approved, spanning selected date) ----------
        $activeLeaves = LeaveRequest::where('status', 'Approved')
            ->whereDate('leave_from', '<=', $todayStr)
            ->whereDate('leave_to', '>=', $todayStr)
            ->orderBy('leave_to', 'asc')
            ->get();

        $onLeave = [];
        foreach ($activeLeaves as $lv) {
            $emp   = $employees->get($lv->employee_id);
            $from  = Carbon::parse($lv->leave_from);
            $to    = Carbon::parse($lv->leave_to);
            $type  = $this->type($lv->leave_type_id);
            $total = (int) ($lv->leave_days ?: ($from->diffInDays($to) + 1));

            $elapsed  = min($total, $from->diffInDays($today) + 1);
            $daysLeft = $today->diffInDays($to);

            $onLeave[] = [
                'name'       => $emp->name ?? 'Unknown',
                'dept'       => $emp->department ?? '—',
                'dept_class' => strtolower($emp->department ?? 'none'),
                'type'       => $type['name'],
                'type_class' => $type['class'],
                'days_left'  => $daysLeft,
                'total'      => $total,
                'elapsed'    => $elapsed,
                'window'     => $total > 60
                    ? $from->format('j M') . ' → ' . $to->format('j M')
                    : $from->format('j M') . '-' . $to->format('j M'),
                'progress'   => $total > 0 ? min(100, round($elapsed / $total * 100)) : 0,
                'approver'   => $lv->admin_name ?: 'Alfred Pennyworth',
                'soon'       => $daysLeft <= 1,
            ];
        }
        $onLeaveCount = count($onLeave);

        // ---------- On-leave rate by team ----------
        $deptTotals   = $employees->groupBy('department')->map(function ($group) {
            return $group->count();
        });
        $deptOnLeave  = [];
        foreach ($activeLeaves as $lv) {
            $d = $employees->get($lv->employee_id)->department ?? 'Other';
            $deptOnLeave[$d] = ($deptOnLeave[$d] ?? 0) + 1;
        }
        $teamRates = [];
        $thinCount = 0;
        foreach ($deptTotals as $dept => $tot) {
            if (!$dept) continue;
            $on   = $deptOnLeave[$dept] ?? 0;
            $pct  = $tot > 0 ? (int) round($on / $tot * 100) : 0;
            $thin = $pct >= 50;
            $mid  = $pct >= 30 && $pct < 50;
            if ($thin) $thinCount++;
            $teamRates[] = compact('dept', 'on', 'tot', 'pct', 'thin', 'mid');
        }
        usort($teamRates, fn ($a, $b) => $b['pct'] <=> $a['pct']);

        // ---------- Gantt window (28 days, today positioned ~day 8) ----------
        $winDays  = $view === 'day' ? 14 : ($view === 'month' ? 42 : 28);
        $winStart = $today->copy()->subDays(7)->startOfDay();
        $winEnd   = $winStart->copy()->addDays($winDays - 1)->endOfDay();

        $ganttLeaves = LeaveRequest::whereIn('status', ['Approved', 'Pending'])
            ->whereDate('leave_from', '<=', $winEnd->toDateString())
            ->whereDate('leave_to', '>=', $winStart->toDateString())
            ->orderBy('leave_from', 'asc')
            ->get();

        $gantt = [];
        foreach ($ganttLeaves as $lv) {
            $emp    = $employees->get($lv->employee_id);
            $from   = Carbon::parse($lv->leave_from);
            $to     = Carbon::parse($lv->leave_to);
            $type   = $this->type($lv->leave_type_id);
            $offset = $winStart->diffInDays($from, false);
            $span   = $from->diffInDays($to) + 1;

            $left  = max(0, $offset) / $winDays * 100;
            $right = min($winDays, $offset + $span) / $winDays * 100;
            $width = max(2.5, $right - $left);

            $label = $span > 60
                ? $type['name'] . ' · ' . $from->format('j M') . ' → ' . $to->format('j M')
                : $type['name'] . ' · ' . $from->format('j') . '-' . $to->format('j M');

            $gantt[] = [
                'name'       => $emp->name ?? 'Unknown',
                'dept'       => $emp->department ?? '—',
                'days'       => (int) ($lv->leave_days ?: $span),
                'type_class' => $type['class'],
                'pending'    => $lv->status === 'Pending', // Flags it for UI styling
                'left'       => round($left, 2),
                'width'      => round($width, 2),
                'label'      => $label,
                'short'      => $span <= 1,
                'initial'    => strtoupper(substr($type['name'], 0, 1)),
            ];
        }

        $todayPos = round($winStart->diffInDays($today, false) / $winDays * 100, 2);
        $ganttDays = [];
        for ($i = 0; $i < $winDays; $i++) {
            $d = $winStart->copy()->addDays($i);
            $ganttDays[] = ['num' => $d->format('j'), 'today' => $d->isSameDay($today)];
        }
        $ganttMonth = $winStart->format('M Y');
        $ganttRange = $winStart->format('j M Y') . ' - ' . $winEnd->format('j M Y');

        // ---------- Returning soon (<= 7 days) ----------
        $returning = LeaveRequest::where('status', 'Approved')
            ->whereDate('leave_to', '>=', $todayStr)
            ->whereDate('leave_to', '<=', $today->copy()->addDays(7)->toDateString())
            ->orderBy('leave_to', 'asc')
            ->take(5)
            ->get();

        $returningSoon = [];
        foreach ($returning as $lv) {
            $emp  = $employees->get($lv->employee_id);
            $back = Carbon::parse($lv->leave_to)->addDay();
            $type = $this->type($lv->leave_type_id);
            $returningSoon[] = [
                'name'       => $emp->name ?? 'Unknown',
                'dept'       => $emp->department ?? '—',
                'type'       => $type['name'],
                'type_class' => $type['class'],
                'color'      => $this->typeColor[$type['class']] ?? '#7EACB5',
                'dow'        => strtoupper($back->format('D')),
                'dom'        => $back->format('d'),
            ];
        }

        // ---------- Upcoming leave (future) ----------
        $upcoming = LeaveRequest::where('status', 'Approved')
            ->whereDate('leave_from', '>', $todayStr)
            ->orderBy('leave_from', 'asc')
            ->take(4)
            ->get()
            ->map(function ($lv) use ($employees) {
                $emp = $employees->get($lv->employee_id);
                return [
                    'name' => $emp->name ?? 'Unknown',
                    'when' => Carbon::parse($lv->leave_from)->format('j M'),
                ];
            });

        // ---------- Recent decisions ----------
        $decisions = LeaveRequest::whereIn('status', ['Approved', 'Rejected'])
            ->orderBy('updated_at', 'desc') // Sorted by decision time, not creation time
            ->take(4)
            ->get();

        $recent = [];
        foreach ($decisions as $lv) {
            $emp  = $employees->get($lv->employee_id);
            $type = $this->type($lv->leave_type_id);
            $recent[] = [
                'name'       => $emp->name ?? 'Unknown',
                'type'       => $type['name'],
                'status'     => $lv->status,
                'reason'     => $lv->reason ?: '—',
                'admin_note' => $lv->admin_note ?? $lv->admin_notes ?? null, 
            ];
        }

        return view('admin.leavestatus', compact(
            'selectedDate', 'view', 'onLeave', 'onLeaveCount', 'teamRates', 'thinCount',
            'gantt', 'ganttDays', 'ganttMonth', 'ganttRange', 'todayPos',
            'returningSoon', 'upcoming', 'recent'
        ));
    }
}
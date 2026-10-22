<?php
namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        // Get date or default to today
        $selectedDate = Carbon::parse($request->input('date', Carbon::now()->toDateString()));
        $period = $request->input('period', 'week'); // default filter period
        $consumptionView = $request->input('consumption_view', 'yearly'); // default graph period

        // --- 1. DETERMINE TIME WINDOW FOR THE LEAVE REQUESTS TABLE & STATS ---
        $startDate = $selectedDate->copy();
        $endDate = $selectedDate->copy();

        if ($period === 'day') {
            $startDate = $startDate->startOfDay();
            $endDate = $endDate->endOfDay();
        } elseif ($period === 'month') {
            $startDate = $startDate->startOfMonth();
            $endDate = $endDate->endOfMonth();
        } else { // default: week
            $startDate = $startDate->startOfWeek();
            $endDate = $endDate->endOfWeek();
        }

        // Fetch requests falling inside the period window based on 'requested_at'
        $requests = LeaveRequest::whereBetween('requested_at', [$startDate, $endDate])
                                ->orderByRaw("FIELD(status, 'Pending', 'Approved', 'Rejected') ASC")
                                ->orderBy('id', 'desc')
                                ->get();

        // 2. Fetch all employees to map names cleanly without heavy nested queries
        $employees = Employee::all()->keyBy('employee_id');

        // 3. Loop to attach names AND calculate monthly sequences
        foreach ($requests as $req) {
            $employee = $employees->get($req->employee_id);
            $req->name = $employee ? $employee->name : 'Unknown Employee';

            $requestDate = Carbon::parse($req->requested_at ?? now());
            
            $monthlySequence = LeaveRequest::whereYear('requested_at', $requestDate->year)
                ->whereMonth('requested_at', $requestDate->month)
                ->where('id', '<=', $req->id)
                ->count();

            $req->monthly_sequence_id = $monthlySequence;
        }

        // 4. Gather metrics for stats panels (scoped to selected period)
        $stats = [
            'total'    => $requests->count(),
            'accepted' => $requests->where('status', 'Approved')->count(),
            'rejected' => $requests->where('status', 'Rejected')->count(),
            'pending'  => $requests->where('status', 'Pending')->count(),
        ];

        // --- 5. DETERMINE TIME WINDOW FOR CONSUMPTION CHART ---
        $consumptionStart = $selectedDate->copy();
        $consumptionEnd = $selectedDate->copy();

        if ($consumptionView === 'weekly') {
            $consumptionStart = $consumptionStart->startOfWeek();
            $consumptionEnd = $consumptionEnd->endOfWeek();
        } else { // default: yearly
            $consumptionStart = $consumptionStart->startOfYear();
            $consumptionEnd = $consumptionEnd->endOfYear();
        }

        // Fetch sum of used days for each specific type matching current window context
        $consumption = [
            [
                'name'  => 'Annual',    
                'used'  => LeaveRequest::where('leave_type_id', 1)->where('status', 'Approved')->whereBetween('requested_at', [$consumptionStart, $consumptionEnd])->sum('leave_days'), 
                'total' => 192
            ],
            [
                'name'  => 'Maternity', 
                'used'  => LeaveRequest::where('leave_type_id', 3)->where('status', 'Approved')->whereBetween('requested_at', [$consumptionStart, $consumptionEnd])->sum('leave_days'), 
                'total' => 1440
            ],
            [
                'name'  => 'Paternity', 
                'used'  => LeaveRequest::where('leave_type_id', 4)->where('status', 'Approved')->whereBetween('requested_at', [$consumptionStart, $consumptionEnd])->sum('leave_days'), 
                'total' => 112
            ],
            [
                'name'  => 'Sick',      
                'used'  => LeaveRequest::where('leave_type_id', 2)->where('status', 'Approved')->whereBetween('requested_at', [$consumptionStart, $consumptionEnd])->sum('leave_days'), 
                'total' => 1344
            ],
            [
                'name'  => 'Emergency', 
                'used'  => LeaveRequest::where('leave_type_id', 5)->where('status', 'Approved')->whereBetween('requested_at', [$consumptionStart, $consumptionEnd])->sum('leave_days'), 
                'total' => 576
            ],
            [
                'name'  => 'Unpaid',    
                'used'  => LeaveRequest::where('leave_type_id', 6)->where('status', 'Approved')->whereBetween('requested_at', [$consumptionStart, $consumptionEnd])->sum('leave_days'), 
                'total' => 8400
            ],
        ];

        return view('admin.leavereq', compact('requests', 'stats', 'consumption', 'period', 'consumptionView', 'selectedDate'));
    }

    public function status() { return $this->index(request()); }

    public function approve(Request $request, $id)
    {
        return $this->decide($request, $id, 'Approved', 'approved');
    }

    public function reject(Request $request, $id)
    {
        return $this->decide($request, $id, 'Rejected', 'rejected');
    }

    private function decide(Request $request, $id, string $status, string $result)
    {
        try {
            $data = LeaveRequest::find($id);

            if ($data) {
                $data->status      = $status;
                $data->admin_id    = $this->resolveAdminId();
                $data->admin_notes = $request->input('note');
                $data->save();
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['result' => $result]);
            }

            return redirect()->back()->with('leave_modal', $result);

        } catch (\Exception $e) {
            return response()->json(['error' => 'PHP Crash: ' . $e->getMessage()], 500);
        }
    }

    public function approveAllPending()
    {
        LeaveRequest::where('status', 'Pending')->update([
            'status'   => 'Approved',
            'admin_id' => $this->resolveAdminId(),
        ]);

        return back()->with('leave_modal', 'approved')
                     ->with('success', 'All pending leave requests approved successfully.');
    }

    
    private function resolveAdminId()
    {
        $candidate = Auth::user()?->employee_id;
        if ($candidate && Employee::where('employee_id', $candidate)->exists()) {
            return $candidate;
        }

        return null;
    }
}

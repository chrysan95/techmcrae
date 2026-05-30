<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $table = 'leave_requests';
    public $timestamps = false; // Handled via database defaults
    protected $guarded = [];

    protected $fillable = [
        'employee_id', 'leave_from', 'leave_to', 'leave_days', 
        'leave_type_id', 'reason', 'status', 'admin_id', 'admin_notes', 'requested_at'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id', 'leave_type_id');
    }
}
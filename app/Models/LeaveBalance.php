<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $table = 'employee_leave_balances';
    public $timestamps = false;
    protected $guarded = [];

    protected $fillable = [
        'employee_id', 'leave_type_id', 'remaining', 'allocated',
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
<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use Notifiable;

    protected $table = 'employees';
    protected $primaryKey = 'employee_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'employee_id', 'name', 'email', 'phone_number', 
        'address', 'birth_date', 'password', 'role', 'department'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    // Relationships
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employee_id', 'employee_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id', 'employee_id');
    }
}
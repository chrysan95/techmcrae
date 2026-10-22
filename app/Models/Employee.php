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
    public $timestamps = false; // Handled via database defaults

    protected $fillable = [
        'employee_id', 'name', 'email', 'phone_number',
        'address', 'birth_date', 'gender', 'password', 'role', 'department'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
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
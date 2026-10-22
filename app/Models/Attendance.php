<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendances';
    public $incrementing = false;
    public $timestamps = false; // Using created_at database default

    protected $fillable = [
        'employee_id', 'attendance_date', 'attendance_hour', 'type', 'status', 'created_at'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}
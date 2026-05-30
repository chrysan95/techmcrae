<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $table = 'leave_types';
    protected $primaryKey = 'leave_type_id';
    public $timestamps = false;

    protected $fillable = ['leave_name', 'allocation_limit', 'reset_frequency'];
}
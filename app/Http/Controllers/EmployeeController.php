<?php
namespace App\Http\Controllers;

use App\Models\Employee;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::orderBy('employee_id')->get();
        
        $departments = $employees->groupBy('department')->map(function($group) {
            return $group->count();
        })->toArray();
        
        return view('admin.employee', compact('employees','departments'));
    }
}
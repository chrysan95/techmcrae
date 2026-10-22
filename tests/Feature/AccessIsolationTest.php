<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AccessIsolationTest extends TestCase
{
    use DatabaseTransactions;

    private Employee $userA;
    private Employee $userB;
    private LeaveRequest $leaveB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userA = Employee::create([
            'employee_id' => 'TMC9001', 'name' => 'User A', 'email' => 'a9001@techmcrae.com',
            'gender' => 'M', 'password' => 'secret123', 'role' => 'employee',
        ]);
        $this->userB = Employee::create([
            'employee_id' => 'TMC9002', 'name' => 'User B', 'email' => 'b9002@techmcrae.com',
            'gender' => 'F', 'password' => 'secret123', 'role' => 'employee',
        ]);

        $this->leaveB = LeaveRequest::create([
            'employee_id' => 'TMC9002', 'leave_type_id' => 2, 'reason' => 'User B check-up',
            'leave_from' => now()->addDays(3)->toDateString(), 'leave_to' => now()->addDays(3)->toDateString(),
            'leave_days' => 1, 'status' => 'Pending',
        ]);
    }

    public function test_user_cannot_withdraw_another_users_leave(): void
    {
        $this->actingAs($this->userA)->post("/portal/leave/{$this->leaveB->id}/withdraw");
        $this->assertDatabaseHas('leave_requests', ['id' => $this->leaveB->id]);
    }

    public function test_owner_can_withdraw_own_pending_leave(): void
    {
        $this->actingAs($this->userB)->post("/portal/leave/{$this->leaveB->id}/withdraw");
        $this->assertDatabaseMissing('leave_requests', ['id' => $this->leaveB->id]);
    }

    public function test_portal_does_not_show_other_users_leave(): void
    {
        $this->actingAs($this->userA)->get('/portal')
            ->assertOk()
            ->assertDontSee('User B check-up');
    }

    public function test_employee_cannot_access_admin_pages(): void
    {
        $this->actingAs($this->userA)->get('/dashboard')->assertForbidden();
        $this->actingAs($this->userA)->get('/leave/requests')->assertForbidden();
        $this->actingAs($this->userA)->post("/leave/requests/{$this->leaveB->id}/approve")->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/portal')->assertRedirect('/');
    }
}
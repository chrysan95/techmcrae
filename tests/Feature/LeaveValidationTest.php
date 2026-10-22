<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LeaveValidationTest extends TestCase
{
    use DatabaseTransactions;

    private Employee $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = Employee::create([
            'employee_id' => 'TMC9001', 'name' => 'Test User', 'email' => 'test9001@techmcrae.com',
            'gender' => 'M', 'password' => 'secret123', 'role' => 'employee',
        ]);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'leave_type_id' => 1,
            'leave_from'    => now()->addDays(2)->toDateString(),
            'leave_to'      => now()->addDays(3)->toDateString(),
            'reason'        => 'Family trip',
        ], $override);
    }

    public function test_end_before_start_is_rejected(): void
    {
        $this->actingAs($this->user)
            ->post('/portal/leave-request', $this->payload([
                'leave_from' => now()->addDays(5)->toDateString(),
                'leave_to'   => now()->addDays(2)->toDateString(),
            ]))
            ->assertSessionHasErrors('leave_to');
    }

    public function test_unknown_leave_type_is_rejected(): void
    {
        $this->actingAs($this->user)
            ->post('/portal/leave-request', $this->payload(['leave_type_id' => 999]))
            ->assertSessionHasErrors('leave_type_id');
    }

    public function test_male_cannot_request_maternity_leave(): void
    {
        $this->actingAs($this->user)
            ->post('/portal/leave-request', $this->payload(['leave_type_id' => 3]))
            ->assertSessionHasErrors('leave');
    }

    public function test_valid_request_is_created(): void
    {
        $this->actingAs($this->user)
            ->post('/portal/leave-request', $this->payload())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => 'TMC9001', 'status' => 'Pending', 'leave_days' => 2,
        ]);
    }
}
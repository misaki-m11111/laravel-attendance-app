<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_status_is_off_duty_when_attendance_does_not_exist(): void
    {
        Carbon::setTestNow('2026-07-30 09:00:00');

        $user = $this->createVerifiedUser();

        $response = $this
            ->actingAs($user, 'web')
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    public function test_status_is_working_when_clocked_in(): void
    {
        Carbon::setTestNow('2026-07-30 09:00:00');

        $user = $this->createVerifiedUser();

        $this->createAttendance($user, [
            'clock_in' => '09:00:00',
            'status' => '出勤中',
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test_status_is_on_break_when_break_has_started(): void
    {
        Carbon::setTestNow('2026-07-30 12:00:00');

        $user = $this->createVerifiedUser();

        $this->createAttendance($user, [
            'clock_in' => '09:00:00',
            'status' => '休憩中',
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    public function test_status_is_clocked_out_when_work_is_finished(): void
    {
        Carbon::setTestNow('2026-07-30 18:00:00');

        $user = $this->createVerifiedUser();

        $this->createAttendance($user, [
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

    private function createVerifiedUser(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }

    private function createAttendance(
        User $user,
        array $attributes = []
    ): Attendance {
        return Attendance::create(array_merge([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today(),
            'clock_in' => null,
            'clock_out' => null,
            'status' => '勤務外',
            'remarks' => null,
        ], $attributes));
    }
}
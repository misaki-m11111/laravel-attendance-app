<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_user_can_clock_out(): void
    {
        Carbon::setTestNow('2026-07-30 18:00:00');

        $user = $this->createVerifiedUser();
        $attendance = $this->createWorkingAttendance($user);

        $response = $this
            ->actingAs($user, 'web')
            ->post('/attendance', [
                'action' => 'clock_out',
            ]);

        $response->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
        $response->assertSee('お疲れ様でした。');
    }

    public function test_clock_out_time_is_displayed_on_attendance_list(): void
    {
        Carbon::setTestNow('2026-07-30 18:00:00');

        $user = $this->createVerifiedUser();
        $this->createWorkingAttendance($user);

        $this
            ->actingAs($user, 'web')
            ->post('/attendance', [
                'action' => 'clock_out',
            ]);

        $response = $this
            ->actingAs($user, 'web')
            ->get('/attendance/list?month=2026-07');

        $response->assertStatus(200);
        $response->assertSee('07/30');
        $response->assertSee('18:00');
    }

    private function createVerifiedUser(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }

    private function createWorkingAttendance(User $user): Attendance
    {
        return Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
            'status' => '出勤中',
            'remarks' => null,
        ]);
    }
}
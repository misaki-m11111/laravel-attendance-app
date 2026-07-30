<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_user_can_start_break(): void
    {
        Carbon::setTestNow('2026-07-30 12:00:00');

        $user = $this->createVerifiedUser();
        $attendance = $this->createWorkingAttendance($user);

        $response = $this
            ->actingAs($user, 'web')
            ->post('/attendance', [
                'action' => 'break_start',
            ]);

        $response->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => null,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => '休憩中',
        ]);
    }

    public function test_user_can_start_break_multiple_times_in_one_day(): void
    {
        Carbon::setTestNow('2026-07-30 12:00:00');

        $user = $this->createVerifiedUser();
        $attendance = $this->createWorkingAttendance($user);

        $this
            ->actingAs($user, 'web')
            ->post('/attendance', [
                'action' => 'break_start',
            ]);

        Carbon::setTestNow('2026-07-30 13:00:00');

        $this
            ->actingAs($user, 'web')
            ->post('/attendance', [
                'action' => 'break_end',
            ]);

        Carbon::setTestNow('2026-07-30 15:00:00');

        $this
            ->actingAs($user, 'web')
            ->post('/attendance', [
                'action' => 'break_start',
            ]);

        $this->assertDatabaseCount('break_times', 2);

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_start' => '15:00:00',
            'break_end' => null,
        ]);
    }

    public function test_user_can_end_break(): void
    {
        Carbon::setTestNow('2026-07-30 13:00:00');

        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
            'status' => '休憩中',
            'remarks' => null,
        ]);

        $breakTime = BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => null,
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->post('/attendance', [
                'action' => 'break_end',
            ]);

        $response->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('break_times', [
            'id' => $breakTime->id,
            'break_end' => '13:00:00',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => '出勤中',
        ]);
    }

    public function test_user_can_end_break_multiple_times_in_one_day(): void
    {
        Carbon::setTestNow('2026-07-30 12:00:00');

        $user = $this->createVerifiedUser();
        $attendance = $this->createWorkingAttendance($user);

        $this
            ->actingAs($user, 'web')
            ->post('/attendance', [
                'action' => 'break_start',
            ]);

        Carbon::setTestNow('2026-07-30 13:00:00');

        $this
            ->actingAs($user, 'web')
            ->post('/attendance', [
                'action' => 'break_end',
            ]);

        Carbon::setTestNow('2026-07-30 15:00:00');

        $this
            ->actingAs($user, 'web')
            ->post('/attendance', [
                'action' => 'break_start',
            ]);

        Carbon::setTestNow('2026-07-30 15:30:00');

        $this
            ->actingAs($user, 'web')
            ->post('/attendance', [
                'action' => 'break_end',
            ]);

        $this->assertDatabaseCount('break_times', 2);

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_start' => '15:00:00',
            'break_end' => '15:30:00',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => '出勤中',
        ]);
    }

    public function test_total_break_time_is_displayed_on_attendance_list(): void
    {
        Carbon::setTestNow('2026-07-30 18:00:00');

        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
            'remarks' => null,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '15:00:00',
            'break_end' => '15:30:00',
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->get('/attendance/list?month=2026-07');

        $response->assertStatus(200);
        $response->assertSee('07/30');
        $response->assertSee('1:30');
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

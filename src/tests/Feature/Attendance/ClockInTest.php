<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_user_can_clock_in(): void
    {
        Carbon::setTestNow('2026-07-30 09:00:00');

        $user = $this->createVerifiedUser();

        $response = $this
            ->actingAs($user, 'web')
            ->post('/attendance', [
                'action' => 'clock_in',
            ]);

        $response->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'attendance_date' => '2026-07-30',
            'clock_in' => '09:00:00',
            'status' => '出勤中',
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test_clock_in_button_is_not_displayed_after_clocking_out(): void
    {
        Carbon::setTestNow('2026-07-30 18:00:00');

        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
            'remarks' => null,
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
        $response->assertSee('お疲れ様でした。');

        $response->assertDontSee(
            'value="clock_in"',
            false
        );
    }

    public function test_clock_in_time_is_displayed_on_attendance_list(): void
    {
        Carbon::setTestNow('2026-07-30 09:00:00');

        $user = $this->createVerifiedUser();

        $this
            ->actingAs($user, 'web')
            ->post('/attendance', [
                'action' => 'clock_in',
            ]);

        $response = $this
            ->actingAs($user, 'web')
            ->get('/attendance/list?month=2026-07');

        $response->assertStatus(200);
        $response->assertSee('07/30');
        $response->assertSee('09:00');
    }

    private function createVerifiedUser(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }
}
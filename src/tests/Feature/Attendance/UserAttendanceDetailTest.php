<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_in_user_name_is_displayed(): void
    {
        $user = $this->createVerifiedUser('勤怠 太郎');
        $attendance = $this->createAttendance($user);

        $response = $this
            ->actingAs($user, 'web')
            ->get(route('attendance.detail', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('勤怠 太郎');
    }

    public function test_selected_attendance_date_is_displayed(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        $response = $this
            ->actingAs($user, 'web')
            ->get(route('attendance.detail', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('2026年');
        $response->assertSee('7月10日');
    }

    public function test_clock_in_and_clock_out_times_are_displayed(): void
    {
        $user = $this->createVerifiedUser();

        $attendance = $this->createAttendance(
            $user,
            '08:45:00',
            '17:30:00'
        );

        $response = $this
            ->actingAs($user, 'web')
            ->get(route('attendance.detail', $attendance->id));

        $response->assertStatus(200);

        $response->assertSee(
            'value="08:45"',
            false
        );

        $response->assertSee(
            'value="17:30"',
            false
        );
    }

    public function test_break_times_are_displayed(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:10:00',
            'break_end' => '13:05:00',
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->get(route('attendance.detail', $attendance->id));

        $response->assertStatus(200);

        $response->assertSee(
            'value="12:10"',
            false
        );

        $response->assertSee(
            'value="13:05"',
            false
        );
    }

    private function createVerifiedUser(
        string $name = 'テストユーザー'
    ): User {
        return User::factory()->create([
            'name' => $name,
            'email_verified_at' => now(),
        ]);
    }

    private function createAttendance(
        User $user,
        string $clockIn = '09:00:00',
        string $clockOut = '18:00:00'
    ): Attendance {
        return Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => '2026-07-10',
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'status' => '退勤済',
            'remarks' => null,
        ]);
    }
}
<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_attendance_information_is_displayed(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser('勤怠 太郎');
        $attendance = $this->createAttendance($user);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:10:00',
            'break_end' => '13:10:00',
        ]);

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('勤怠 太郎');
        $response->assertSee('2026年');
        $response->assertSee('7月10日');

        $response->assertSee('value="09:00"', false);
        $response->assertSee('value="18:00"', false);
        $response->assertSee('value="12:10"', false);
        $response->assertSee('value="13:10"', false);
        $response->assertSee('テスト用備考');
    }

    public function test_error_is_displayed_when_clock_in_is_after_clock_out(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this
            ->actingAs($admin, 'admin')
            ->put(
                route('admin.attendance.update', $attendance->id),
                [
                    'clock_in' => '19:00',
                    'clock_out' => '18:00',
                    'breaks' => [],
                    'remarks' => '出勤時刻修正',
                ]
            );

        $response->assertSessionHasErrors([
            'clock_out'
                => '出勤時間もしくは退勤時間が不適切な値です',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
    }

    public function test_error_is_displayed_when_break_start_is_after_clock_out(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this
            ->actingAs($admin, 'admin')
            ->put(
                route('admin.attendance.update', $attendance->id),
                [
                    'clock_in' => '09:00',
                    'clock_out' => '18:00',
                    'breaks' => [
                        [
                            'break_start' => '19:00',
                            'break_end' => null,
                        ],
                    ],
                    'remarks' => '休憩開始時刻修正',
                ]
            );

        $response->assertSessionHasErrors([
            'breaks.0.break_start'
                => '休憩時間が不適切な値です',
        ]);

        $this->assertDatabaseCount('break_times', 0);
    }

    public function test_error_is_displayed_when_break_end_is_after_clock_out(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this
            ->actingAs($admin, 'admin')
            ->put(
                route('admin.attendance.update', $attendance->id),
                [
                    'clock_in' => '09:00',
                    'clock_out' => '18:00',
                    'breaks' => [
                        [
                            'break_start' => '12:00',
                            'break_end' => '19:00',
                        ],
                    ],
                    'remarks' => '休憩終了時刻修正',
                ]
            );

        $response->assertSessionHasErrors([
            'breaks.0.break_end'
                => '休憩時間もしくは退勤時間が不適切な値です',
        ]);

        $this->assertDatabaseCount('break_times', 0);
    }

    public function test_remarks_is_required(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this
            ->actingAs($admin, 'admin')
            ->put(
                route('admin.attendance.update', $attendance->id),
                [
                    'clock_in' => '09:00',
                    'clock_out' => '18:00',
                    'breaks' => [],
                    'remarks' => '',
                ]
            );

        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'remarks' => 'テスト用備考',
        ]);
    }

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'テスト管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'admin_status' => true,
        ]);
    }

    private function createUser(
        string $name = 'テストユーザー'
    ): User {
        return User::factory()->create([
            'name' => $name,
            'email_verified_at' => now(),
        ]);
    }

    private function createAttendance(User $user): Attendance
    {
        return Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => '2026-07-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
            'remarks' => 'テスト用備考',
        ]);
    }
}
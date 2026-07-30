<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_all_users_attendance_for_selected_date_is_displayed(): void
    {
        Carbon::setTestNow('2026-07-30 10:00:00');

        $admin = $this->createAdmin();
        $firstUser = $this->createUser('勤怠 太郎');
        $secondUser = $this->createUser('勤怠 花子');

        $firstAttendance = $this->createAttendance(
            $firstUser,
            '2026-07-30',
            '09:00:00',
            '18:00:00'
        );

        BreakTime::create([
            'attendance_id' => $firstAttendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $secondAttendance = $this->createAttendance(
            $secondUser,
            '2026-07-30',
            '08:30:00',
            '17:30:00'
        );

        BreakTime::create([
            'attendance_id' => $secondAttendance->id,
            'break_start' => '12:15:00',
            'break_end' => '12:45:00',
        ]);

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list', [
                'date' => '2026-07-30',
            ]));

        $response->assertStatus(200);

        $response->assertSee('勤怠 太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00');
        $response->assertSee('8:00');

        $response->assertSee('勤怠 花子');
        $response->assertSee('08:30');
        $response->assertSee('17:30');
        $response->assertSee('0:30');
        $response->assertSee('8:30');
    }

    public function test_current_date_is_displayed_by_default(): void
    {
        Carbon::setTestNow('2026-07-30 10:00:00');

        $admin = $this->createAdmin();

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('2026年7月30日の勤怠');
        $response->assertSee('value="2026-07-30"', false);
    }

    public function test_previous_date_attendance_is_displayed(): void
    {
        Carbon::setTestNow('2026-07-30 10:00:00');

        $admin = $this->createAdmin();
        $user = $this->createUser('前日ユーザー');

        $this->createAttendance(
            $user,
            '2026-07-29',
            '08:10:00',
            '17:10:00'
        );

        $this->createAttendance(
            $user,
            '2026-07-30',
            '09:10:00',
            '18:10:00'
        );

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list', [
                'date' => '2026-07-29',
            ]));

        $response->assertStatus(200);
        $response->assertSee('2026年7月29日の勤怠');
        $response->assertSee('前日ユーザー');
        $response->assertSee('08:10');
        $response->assertSee('17:10');
        $response->assertDontSee('09:10');
        $response->assertDontSee('18:10');
    }

    public function test_next_date_attendance_is_displayed(): void
    {
        Carbon::setTestNow('2026-07-30 10:00:00');

        $admin = $this->createAdmin();
        $user = $this->createUser('翌日ユーザー');

        $this->createAttendance(
            $user,
            '2026-07-31',
            '10:20:00',
            '19:20:00'
        );

        $this->createAttendance(
            $user,
            '2026-07-30',
            '09:20:00',
            '18:20:00'
        );

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list', [
                'date' => '2026-07-31',
            ]));

        $response->assertStatus(200);
        $response->assertSee('2026年7月31日の勤怠');
        $response->assertSee('翌日ユーザー');
        $response->assertSee('10:20');
        $response->assertSee('19:20');
        $response->assertDontSee('09:20');
        $response->assertDontSee('18:20');
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

    private function createUser(string $name): User
    {
        return User::factory()->create([
            'name' => $name,
            'email_verified_at' => now(),
        ]);
    }

    private function createAttendance(
        User $user,
        string $date,
        string $clockIn,
        string $clockOut
    ): Attendance {
        return Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => $date,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'status' => '退勤済',
            'remarks' => null,
        ]);
    }
}

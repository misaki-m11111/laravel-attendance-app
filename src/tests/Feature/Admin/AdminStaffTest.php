<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_all_users_are_displayed_on_staff_list(): void
    {
        $admin = $this->createAdmin();

        $firstUser = $this->createUser(
            '勤怠 太郎',
            'taro@example.com'
        );

        $secondUser = $this->createUser(
            '勤怠 花子',
            'hanako@example.com'
        );

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.staff.list'));

        $response->assertStatus(200);

        $response->assertSee($firstUser->name);
        $response->assertSee($firstUser->email);

        $response->assertSee($secondUser->name);
        $response->assertSee($secondUser->email);
    }

    public function test_selected_user_monthly_attendance_is_displayed(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = $this->createAttendance(
            $user,
            '2026-07-10',
            '09:00:00',
            '18:00:00'
        );

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.staff.show', [
                'id' => $user->id,
                'month' => '2026-07',
            ]));

        $response->assertStatus(200);
        $response->assertSee($user->name . 'さんの勤怠');
        $response->assertSee('2026/07');
        $response->assertSee('07/10');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00');
        $response->assertSee('8:00');
    }

    public function test_previous_month_attendance_is_displayed(): void
    {
        Carbon::setTestNow('2026-07-15 10:00:00');

        $admin = $this->createAdmin();
        $user = $this->createUser();

        $this->createAttendance(
            $user,
            '2026-06-10',
            '08:10:00',
            '17:10:00'
        );

        $this->createAttendance(
            $user,
            '2026-07-10',
            '09:10:00',
            '18:10:00'
        );

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.staff.show', [
                'id' => $user->id,
                'month' => '2026-06',
            ]));

        $response->assertStatus(200);
        $response->assertSee('2026/06');
        $response->assertSee('06/10');
        $response->assertSee('08:10');
        $response->assertSee('17:10');
        $response->assertDontSee('09:10');
        $response->assertDontSee('18:10');
    }

    public function test_next_month_attendance_is_displayed(): void
    {
        Carbon::setTestNow('2026-07-15 10:00:00');

        $admin = $this->createAdmin();
        $user = $this->createUser();

        $this->createAttendance(
            $user,
            '2026-08-10',
            '10:20:00',
            '19:20:00'
        );

        $this->createAttendance(
            $user,
            '2026-07-10',
            '09:20:00',
            '18:20:00'
        );

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.staff.show', [
                'id' => $user->id,
                'month' => '2026-08',
            ]));

        $response->assertStatus(200);
        $response->assertSee('2026/08');
        $response->assertSee('08/10');
        $response->assertSee('10:20');
        $response->assertSee('19:20');
        $response->assertDontSee('09:20');
        $response->assertDontSee('18:20');
    }

    public function test_detail_link_opens_selected_attendance_detail(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = $this->createAttendance(
            $user,
            '2026-07-10',
            '09:00:00',
            '18:00:00'
        );

        $listResponse = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.staff.show', [
                'id' => $user->id,
                'month' => '2026-07',
            ]));

        $listResponse->assertStatus(200);

        $listResponse->assertSee(
            route('admin.attendance.show', $attendance->id),
            false
        );

        $detailResponse = $this
            ->actingAs($admin, 'admin')
            ->get(route(
                'admin.attendance.show',
                $attendance->id
            ));

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee($user->name);
        $detailResponse->assertSee('value="09:00"', false);
        $detailResponse->assertSee('value="18:00"', false);
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
        string $name = 'テストユーザー',
        string $email = 'user@example.com'
    ): User {
        return User::factory()->create([
            'name' => $name,
            'email' => $email,
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
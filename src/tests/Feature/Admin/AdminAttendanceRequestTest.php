<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\AttendanceRequestBreak;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_pending_requests_are_displayed(): void
    {
        $admin = $this->createAdmin();

        $firstUser = $this->createUser('勤怠 太郎');
        $secondUser = $this->createUser('勤怠 花子');

        $firstAttendance = $this->createAttendance($firstUser);
        $secondAttendance = $this->createAttendance(
            $secondUser,
            '2026-07-11'
        );

        $this->createAttendanceRequest(
            $firstUser,
            $firstAttendance,
            '承認待ち申請1',
            0
        );

        $this->createAttendanceRequest(
            $secondUser,
            $secondAttendance,
            '承認待ち申請2',
            0
        );

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('attendance.request.index', [
                'tab' => 'pending',
            ]));

        $response->assertStatus(200);
        $response->assertSee('承認待ち申請1');
        $response->assertSee('承認待ち申請2');
        $response->assertSee('勤怠 太郎');
        $response->assertSee('勤怠 花子');
        $response->assertSee('承認待ち');
    }

    public function test_all_approved_requests_are_displayed(): void
    {
        $admin = $this->createAdmin();

        $firstUser = $this->createUser('承認済み 太郎');
        $secondUser = $this->createUser('承認済み 花子');

        $firstAttendance = $this->createAttendance($firstUser);
        $secondAttendance = $this->createAttendance(
            $secondUser,
            '2026-07-11'
        );

        $this->createAttendanceRequest(
            $firstUser,
            $firstAttendance,
            '承認済み申請1',
            1
        );

        $this->createAttendanceRequest(
            $secondUser,
            $secondAttendance,
            '承認済み申請2',
            1
        );

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('attendance.request.index', [
                'tab' => 'approved',
            ]));

        $response->assertStatus(200);
        $response->assertSee('承認済み申請1');
        $response->assertSee('承認済み申請2');
        $response->assertSee('承認済み 太郎');
        $response->assertSee('承認済み 花子');
        $response->assertSee('承認済み');
    }

    public function test_approval_updates_attendance_information(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = $this->createAttendance($user);

        $oldBreak = BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $attendanceRequest = $this->createAttendanceRequest(
            $user,
            $attendance,
            '出退勤と休憩時刻の修正',
            0,
            '08:30:00',
            '17:30:00'
        );

        AttendanceRequestBreak::create([
            'attendance_request_id' => $attendanceRequest->id,
            'requested_break_start' => '12:10:00',
            'requested_break_end' => '12:50:00',
        ]);

        $response = $this
            ->actingAs($admin, 'admin')
            ->patch(route(
                'admin.attendance.request.approve',
                $attendanceRequest->id
            ));

        $response->assertRedirect(route(
            'admin.attendance.request.show',
            $attendanceRequest->id
        ));

        $this->assertDatabaseHas('attendance_requests', [
            'id' => $attendanceRequest->id,
            'status' => 1,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
            'remarks' => '出退勤と休憩時刻の修正',
        ]);

        $this->assertDatabaseMissing('break_times', [
            'id' => $oldBreak->id,
        ]);

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_start' => '12:10:00',
            'break_end' => '12:50:00',
        ]);

        $detailResponse = $this
            ->actingAs($admin, 'admin')
            ->get(route(
                'admin.attendance.request.show',
                $attendanceRequest->id
            ));

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('承認済み');
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

    private function createAttendance(
        User $user,
        string $date = '2026-07-10'
    ): Attendance {
        return Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => $date,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
            'remarks' => null,
        ]);
    }

    private function createAttendanceRequest(
        User $user,
        Attendance $attendance,
        string $reason,
        int $status,
        string $clockIn = '08:30:00',
        string $clockOut = '17:30:00'
    ): AttendanceRequest {
        return AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in' => $clockIn,
            'requested_clock_out' => $clockOut,
            'reason' => $reason,
            'status' => $status,
        ]);
    }
}
<?php

namespace Tests\Feature\Attendance;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAttendanceRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_error_is_displayed_when_clock_in_is_after_clock_out(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        $response = $this
            ->actingAs($user, 'web')
            ->post(
                route('attendance.request.store', $attendance->id),
                [
                    'clock_in' => '19:00',
                    'clock_out' => '18:00',
                    'breaks' => [],
                    'reason' => '出勤時刻の修正',
                ]
            );

        $response->assertSessionHasErrors([
            'clock_out'
                => '出勤時間もしくは退勤時間が不適切な値です',
        ]);

        $this->assertDatabaseCount('attendance_requests', 0);
    }

    public function test_error_is_displayed_when_break_start_is_after_clock_out(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        $response = $this
            ->actingAs($user, 'web')
            ->post(
                route('attendance.request.store', $attendance->id),
                [
                    'clock_in' => '09:00',
                    'clock_out' => '18:00',
                    'breaks' => [
                        [
                            'break_start' => '19:00',
                            'break_end' => null,
                        ],
                    ],
                    'reason' => '休憩開始時刻の修正',
                ]
            );

        $response->assertSessionHasErrors([
            'breaks.0.break_start'
                => '休憩時間が不適切な値です',
        ]);

        $this->assertDatabaseCount('attendance_requests', 0);
    }

    public function test_error_is_displayed_when_break_end_is_after_clock_out(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        $response = $this
            ->actingAs($user, 'web')
            ->post(
                route('attendance.request.store', $attendance->id),
                [
                    'clock_in' => '09:00',
                    'clock_out' => '18:00',
                    'breaks' => [
                        [
                            'break_start' => '12:00',
                            'break_end' => '19:00',
                        ],
                    ],
                    'reason' => '休憩終了時刻の修正',
                ]
            );

        $response->assertSessionHasErrors([
            'breaks.0.break_end'
                => '休憩時間もしくは退勤時間が不適切な値です',
        ]);

        $this->assertDatabaseCount('attendance_requests', 0);
    }

    public function test_reason_is_required(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        $response = $this
            ->actingAs($user, 'web')
            ->post(
                route('attendance.request.store', $attendance->id),
                [
                    'clock_in' => '09:00',
                    'clock_out' => '18:00',
                    'breaks' => [
                        [
                            'break_start' => '12:00',
                            'break_end' => '13:00',
                        ],
                    ],
                    'reason' => '',
                ]
            );

        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください',
        ]);

        $this->assertDatabaseCount('attendance_requests', 0);
    }

    public function test_attendance_correction_request_is_displayed_for_admin(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        $response = $this
            ->actingAs($user, 'web')
            ->post(
                route('attendance.request.store', $attendance->id),
                [
                    'clock_in' => '08:30',
                    'clock_out' => '17:30',
                    'breaks' => [
                        [
                            'break_start' => '12:10',
                            'break_end' => '13:10',
                        ],
                    ],
                    'reason' => '出退勤時刻と休憩時刻の修正',
                ]
            );

        $response->assertRedirect(
            route('attendance.detail', $attendance->id)
        );

        $this->assertDatabaseHas('attendance_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '08:30:00',
            'requested_clock_out' => '17:30:00',
            'reason' => '出退勤時刻と休憩時刻の修正',
            'status' => 0,
        ]);

        $attendanceRequestId = (int) $this->app['db']
            ->table('attendance_requests')
            ->where('attendance_id', $attendance->id)
            ->value('id');

        $this->assertDatabaseHas('attendance_request_breaks', [
            'attendance_request_id' => $attendanceRequestId,
            'requested_break_start' => '12:10:00',
            'requested_break_end' => '13:10:00',
        ]);

        $admin = $this->createAdmin();

        $listResponse = $this
            ->actingAs($admin, 'admin')
            ->get(route('attendance.request.index', [
                'tab' => 'pending',
            ]));

        $listResponse->assertStatus(200);
        $listResponse->assertSee($user->name);
        $listResponse->assertSee('出退勤時刻と休憩時刻の修正');

        $detailResponse = $this
            ->actingAs($admin, 'admin')
            ->get(
                route(
                    'admin.attendance.request.show',
                    $attendanceRequestId
                )
            );

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee($user->name);
        $detailResponse->assertSee('08:30');
        $detailResponse->assertSee('17:30');
        $detailResponse->assertSee('12:10');
        $detailResponse->assertSee('13:10');
        $detailResponse->assertSee('出退勤時刻と休憩時刻の修正');
    }

    public function test_pending_request_is_displayed_on_user_request_list(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        $this
            ->actingAs($user, 'web')
            ->post(
                route('attendance.request.store', $attendance->id),
                [
                    'clock_in' => '08:30',
                    'clock_out' => '17:30',
                    'breaks' => [
                        [
                            'break_start' => '12:10',
                            'break_end' => '13:10',
                        ],
                    ],
                    'reason' => '出退勤時刻と休憩時刻の修正',
                ]
            );

        $response = $this
            ->actingAs($user, 'web')
            ->get(route('attendance.request.index', [
                'tab' => 'pending',
            ]));

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('出退勤時刻と休憩時刻の修正');
        $response->assertSee('2026/07/10');
    }

    public function test_approved_request_is_displayed_on_user_request_list(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        $this
            ->actingAs($user, 'web')
            ->post(
                route('attendance.request.store', $attendance->id),
                [
                    'clock_in' => '08:30',
                    'clock_out' => '17:30',
                    'breaks' => [
                        [
                            'break_start' => '12:10',
                            'break_end' => '13:10',
                        ],
                    ],
                    'reason' => '承認済み表示の確認',
                ]
            );

        $attendanceRequestId = (int) $this->app['db']
            ->table('attendance_requests')
            ->where('attendance_id', $attendance->id)
            ->value('id');

        $admin = $this->createAdmin();

        $approveResponse = $this
            ->actingAs($admin, 'admin')
            ->patch(
                route(
                    'admin.attendance.request.approve',
                    $attendanceRequestId
                )
            );

        $approveResponse->assertRedirect();

        $this->assertDatabaseHas('attendance_requests', [
            'id' => $attendanceRequestId,
            'status' => 1,
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->get(route('attendance.request.index', [
                'tab' => 'approved',
            ]));

        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee('承認済み表示の確認');
        $response->assertSee('2026/07/10');
    }

    public function test_attendance_detail_cannot_be_edited_while_request_is_pending(): void
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        $this
            ->actingAs($user, 'web')
            ->post(
                route('attendance.request.store', $attendance->id),
                [
                    'clock_in' => '08:30',
                    'clock_out' => '17:30',
                    'breaks' => [
                        [
                            'break_start' => '12:10',
                            'break_end' => '13:10',
                        ],
                    ],
                    'reason' => '承認待ち状態の確認',
                ]
            );

        $response = $this
            ->actingAs($user, 'web')
            ->get(route('attendance.detail', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('*承認待ちのため修正はできません。');

        $response->assertSee(
            'name="clock_in"',
            false
        );

        $response->assertSee(
            'name="clock_out"',
            false
        );

        $response->assertSee(
            'readonly',
            false
        );

        $response->assertDontSee(
            '<button type="submit" class="attendance-detail__button">',
            false
        );
    }

    private function createVerifiedUser(): User
    {
        return User::factory()->create([
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
            'remarks' => null,
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
}
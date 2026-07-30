<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_all_own_attendance_information_is_displayed(): void
    {
        Carbon::setTestNow('2026-07-15 10:00:00');

        $user = $this->createVerifiedUser();
        $otherUser = $this->createVerifiedUser();

        $firstAttendance = $this->createAttendance(
            $user,
            '2026-07-10',
            '08:30:00',
            '17:30:00'
        );

        BreakTime::create([
            'attendance_id' => $firstAttendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $secondAttendance = $this->createAttendance(
            $user,
            '2026-07-11',
            '09:15:00',
            '18:45:00'
        );

        BreakTime::create([
            'attendance_id' => $secondAttendance->id,
            'break_start' => '12:30:00',
            'break_end' => '13:15:00',
        ]);

        $this->createAttendance(
            $otherUser,
            '2026-07-12',
            '07:07:00',
            '16:07:00'
        );

        $response = $this
            ->actingAs($user, 'web')
            ->get('/attendance/list?month=2026-07');

        $response->assertStatus(200);

        $response->assertSee('07/10');
        $response->assertSee('08:30');
        $response->assertSee('17:30');
        $response->assertSee('1:00');
        $response->assertSee('8:00');

        $response->assertSee('07/11');
        $response->assertSee('09:15');
        $response->assertSee('18:45');
        $response->assertSee('0:45');
        $response->assertSee('8:45');

        $response->assertDontSee('07:07');
        $response->assertDontSee('16:07');
    }

    public function test_current_month_is_displayed_by_default(): void
    {
        Carbon::setTestNow('2026-07-15 10:00:00');

        $user = $this->createVerifiedUser();

        $response = $this
            ->actingAs($user, 'web')
            ->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2026/07');
    }

    public function test_previous_month_information_is_displayed(): void
    {
        Carbon::setTestNow('2026-07-15 10:00:00');

        $user = $this->createVerifiedUser();

        $this->createAttendance(
            $user,
            '2026-06-10',
            '08:20:00',
            '17:20:00'
        );

        $this->createAttendance(
            $user,
            '2026-07-10',
            '09:20:00',
            '18:20:00'
        );

        $response = $this
            ->actingAs($user, 'web')
            ->get('/attendance/list?month=2026-06');

        $response->assertStatus(200);
        $response->assertSee('2026/06');
        $response->assertSee('06/10');
        $response->assertSee('08:20');
        $response->assertDontSee('09:20');
    }

    public function test_next_month_information_is_displayed(): void
    {
        Carbon::setTestNow('2026-07-15 10:00:00');

        $user = $this->createVerifiedUser();

        $this->createAttendance(
            $user,
            '2026-08-10',
            '10:10:00',
            '19:10:00'
        );

        $this->createAttendance(
            $user,
            '2026-07-10',
            '09:10:00',
            '18:10:00'
        );

        $response = $this
            ->actingAs($user, 'web')
            ->get('/attendance/list?month=2026-08');

        $response->assertStatus(200);
        $response->assertSee('2026/08');
        $response->assertSee('08/10');
        $response->assertSee('10:10');
        $response->assertDontSee('09:10');
    }

    public function test_detail_link_opens_selected_attendance_detail(): void
    {
        Carbon::setTestNow('2026-07-15 10:00:00');

        $user = $this->createVerifiedUser();

        $attendance = $this->createAttendance(
            $user,
            '2026-07-10',
            '09:00:00',
            '18:00:00'
        );

        $listResponse = $this
            ->actingAs($user, 'web')
            ->get('/attendance/list?month=2026-07');

        $listResponse->assertStatus(200);
        $listResponse->assertSee(
            route('attendance.detail', $attendance->id),
            false
        );

        $detailResponse = $this
            ->actingAs($user, 'web')
            ->get(route('attendance.detail', $attendance->id));

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee($user->name);
        $detailResponse->assertSee('09:00');
        $detailResponse->assertSee('18:00');
    }

    private function createVerifiedUser(): User
    {
        return User::factory()->create([
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
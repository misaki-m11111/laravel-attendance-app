<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_date_and_time_are_displayed(): void
    {
        Carbon::setTestNow(
            Carbon::create(2026, 7, 30, 9, 15, 0)
        );

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('2026年7月30日');
        $response->assertSee('(木)');
        $response->assertSee('09:15');

        Carbon::setTestNow();
    }
}
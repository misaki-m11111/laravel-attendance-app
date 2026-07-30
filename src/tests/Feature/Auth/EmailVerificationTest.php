<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
  use RefreshDatabase;

  public function test_verification_email_is_sent_after_registration(): void
  {
    Notification::fake();

    $response = $this->post('/register', [
      'name' => 'テストユーザー',
      'email' => 'verification@example.com',
      'password' => 'password',
      'password_confirmation' => 'password',
    ]);

    $user = User::where(
      'email',
      'verification@example.com'
    )->firstOrFail();

    $this->assertNull($user->email_verified_at);

    Notification::assertSentTo(
      $user,
      VerifyEmail::class
    );

    $response->assertRedirect();
  }

  public function test_verification_button_links_to_mailhog(): void
  {
    $user = User::factory()->unverified()->create();

    $response = $this
      ->actingAs($user, 'web')
      ->get(route('verification.notice'));

    $response->assertStatus(200);
    $response->assertSee('認証はこちらから');

    $response->assertSee(
      'href="http://localhost:8026"',
      false
    );
  }

  public function test_user_is_redirected_to_attendance_after_verification(): void
  {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
      'verification.verify',
      now()->addMinutes(60),
      [
        'id' => $user->id,
        'hash' => sha1($user->email),
      ]
    );

    $response = $this
      ->actingAs($user, 'web')
      ->get($verificationUrl);

    $this->assertTrue(
      $user->fresh()->hasVerifiedEmail()
    );

    $response->assertRedirect('/attendance?verified=1');
  }
}

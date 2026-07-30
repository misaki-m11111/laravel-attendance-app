<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_name_is_required(): void
    {
        $data = $this->validUserData();
        unset($data['name']);

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_email_is_required(): void
    {
        $data = $this->validUserData();
        unset($data['email']);

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_password_must_be_at_least_eight_characters(): void
    {
        $data = $this->validUserData();
        $data['password'] = 'pass123';
        $data['password_confirmation'] = 'pass123';

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_password_confirmation_must_match(): void
    {
        $data = $this->validUserData();
        $data['password_confirmation'] = 'different-password';

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_password_is_required(): void
    {
        $data = $this->validUserData();
        unset($data['password']);

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_user_can_register_with_valid_data(): void
    {
        $data = $this->validUserData();

        $response = $this->post('/register', $data);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->assertTrue(
            Hash::check('password', $user->password)
        );
    }

    private function validUserData(): array
    {
        return [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
    }
}

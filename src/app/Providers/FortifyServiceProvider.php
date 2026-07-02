<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LoginResponse;

class FortifyServiceProvider extends ServiceProvider
{
    public function register()
    {
        /**
         * Fortifyのログイン成功後のレスポンスを上書きする。
         *
         * LoginResponse は、
         * 「ログインに成功した後、どこへ遷移するか」
         * を決める処理。
         */
        $this->app->singleton(LoginResponse::class, function () {
            return new class implements LoginResponse {
                public function toResponse($request)
                {
                    /**
                     * 管理者ログイン画面から送信された場合
                     *
                     * /admin/login からログインした場合だけ、
                     * 管理者用の仮ページ /admin/home に遷移する。
                     */
                    if ($request->is('admin/login')) {
                        return redirect()->intended('/admin/home');
                    }

                    /**
                     * 一般ユーザーの場合
                     *
                     * 通常のFortify設定に従って遷移する。
                     * たとえば /home など。
                     */
                    return redirect()->intended(config('fortify.home'));
                }
            };
        });
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::loginView(function () {
            if (request()->is('admin/login')) {
                return view('admin.auth.login');
            }

            return view('auth.login');
        });

        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}

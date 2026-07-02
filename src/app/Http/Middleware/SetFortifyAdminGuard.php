<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\StatefulGuard;

class SetFortifyAdminGuard
{
    public function handle($request, Closure $next)
    {
        /*
         * /admin/login のときだけ、Fortifyが使うguardを admin に切り替える。
         *
         * 通常Fortifyは web guard を使う。
         * web guard は users テーブルを見る。
         *
         * admin guard に切り替えることで、
         * admins テーブルを見に行くようになる。
         */
        config(['fortify.guard' => 'admin']);

        /*
         * ログイン成功後の遷移先を管理者用にする。
         */
        config(['fortify.home' => '/admin/home']);

        /*
         * Fortifyのguard設定が古いまま残らないようにする。
         * これを入れることで、admin guard への切り替えを確実にする。
         */
        app()->forgetInstance(StatefulGuard::class);

        return $next($request);
    }
}

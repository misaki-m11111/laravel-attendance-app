<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * 未認証時のリダイレクト先を決める。
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return $request->is('admin/*')
                || $request->routeIs('admin.*')
                ? route('admin.login')
                : route('login');
        }
    }
}

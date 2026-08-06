<header class="header">
    <div class="header__inner">
        <div class="header__logo">
            <span class="header__logo-mark" aria-hidden="true"></span>

            <span class="header__logo-text">
                Attendly
            </span>
        </div>

        @unless (request()->routeIs('verification.notice'))
            @if (Auth::guard('admin')->check())
                <nav class="header__nav">
                    <a href="{{ route('admin.attendance.list') }}">
                        勤怠一覧
                    </a>

                    <a href="{{ route('admin.staff.list') }}">
                        スタッフ一覧
                    </a>

                    <a href="{{ route('attendance.request.index') }}">
                        申請一覧
                    </a>

                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf

                        <button type="submit">
                            ログアウト
                        </button>
                    </form>
                </nav>
            @elseif (Auth::guard('web')->check())
                <nav class="header__nav">
                    <a href="{{ route('attendance.index') }}">
                        勤怠
                    </a>

                    <a href="{{ route('attendance.list') }}">
                        勤怠一覧
                    </a>

                    <a href="{{ route('attendance.request.index') }}">
                        申請
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit">
                            ログアウト
                        </button>
                    </form>
                </nav>
            @endif
        @endunless
    </div>
</header>

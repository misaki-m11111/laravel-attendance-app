<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '勤怠管理アプリ')</title>
    <link rel="stylesheet" href="{{ asset('css/form/form.css') }}">

    @yield('css')
</head>

<body>
    <main>
        @yield('content')
    </main>
</body>

</html>

@extends('layouts.app')

@section('title', 'ホーム')

@section('content')
    <h1>認証済みユーザー用の仮ページです</h1>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">ログアウト</button>
    </form>
@endsection

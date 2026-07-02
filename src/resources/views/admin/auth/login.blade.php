@extends('layouts.app')

@section('title', '管理者ログイン')

@section('content')
    <div class="form">
        <h1 class="form__title">管理者ログイン</h1>

        <form method="POST" action="{{ route('admin.login.store') }}" class="form__form">
            @csrf

            <div class="form__group">
                <label class="form__label">メールアドレス</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form__input">
                @error('email')
                    <p class="form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form__group">
                <label class="form__label">パスワード</label>
                <input type="password" name="password" class="form__input">
                @error('password')
                    <p class="form__error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="form__button">管理者ログインする</button>
        </form>
    </div>
@endsection

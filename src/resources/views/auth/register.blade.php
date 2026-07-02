@extends('layouts.app')

@section('title', '会員登録')

@section('content')
    <div class="form">
        <h1 class="form__title">会員登録</h1>

        <form method="POST" action={{ route('register') }} class="form__form">
            @csrf

            <div class="form__group">
                <label class="form__label">名前</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form__input">

                @error('name')
                    <p class="form__error">{{ $message }}</p>
                @enderror
            </div>

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

            <div class="form__group">
                <label class="form__label">確認用パスワード</label>
                <input type="password" name="password_confirmation" class="form__input">

                @error('password_confirmation')
                    <p class="form__error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="form__button">登録する</button>
        </form>

        <p class="form__link">
            <a href={{ route('login') }}>ログインはこちら</a>
        </p>
    </div>
@endsection

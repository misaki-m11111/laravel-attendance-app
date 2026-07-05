@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')
    <div class="attendance-detail">
        <h1 class="attendance-detail__title">勤怠詳細</h1>

        <form method="POST" action="{{ route('attendance.request.store', $attendance->id) }}">
            @csrf

            <div class="attendance-detail__card">
                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">名前</div>
                    <div class="attendance-detail__value attendance-detail__name">
                        {{ $user->name }}
                    </div>
                </div>

                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">日付</div>
                    <div class="attendance-detail__value attendance-detail__date">
                        <span>
                            {{ \Carbon\Carbon::parse($attendance->attendance_date)->format('Y年') }}
                        </span>
                        <span>
                            {{ \Carbon\Carbon::parse($attendance->attendance_date)->format('n月j日') }}
                        </span>
                    </div>
                </div>

                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">出勤・退勤</div>

                    <div class="attendance-detail__value attendance-detail__time">
                        <input type="text" name="clock_in"
                            value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}"
                            class="attendance-detail__input" {{ $pendingRequest ? 'readonly' : '' }}>

                        <span class="attendance-detail__separator">〜</span>

                        <input type="text" name="clock_out"
                            value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}"
                            class="attendance-detail__input" {{ $pendingRequest ? 'readonly' : '' }}>
                    </div>
                </div>

                @foreach ($attendance->breakTimes as $index => $breakTime)
                    <div class="attendance-detail__row">
                        <div class="attendance-detail__label">
                            休憩{{ $index === 0 ? '' : $index + 1 }}
                        </div>

                        <div class="attendance-detail__value attendance-detail__time">
                            <input type="text" name="breaks[{{ $index }}][break_start]"
                                value="{{ $breakTime->break_start ? \Carbon\Carbon::parse($breakTime->break_start)->format('H:i') : '' }}"
                                class="attendance-detail__input" {{ $pendingRequest ? 'readonly' : '' }}>

                            <span class="attendance-detail__separator">〜</span>

                            <input type="text" name="breaks[{{ $index }}][break_end]"
                                value="{{ $breakTime->break_end ? \Carbon\Carbon::parse($breakTime->break_end)->format('H:i') : '' }}"
                                class="attendance-detail__input" {{ $pendingRequest ? 'readonly' : '' }}>
                        </div>
                    </div>
                @endforeach

                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">
                        休憩{{ $attendance->breakTimes->count() + 1 }}
                    </div>

                    <div class="attendance-detail__value attendance-detail__time">
                        <input type="text" name="breaks[{{ $attendance->breakTimes->count() }}][break_start]"
                            value="" class="attendance-detail__input" {{ $pendingRequest ? 'readonly' : '' }}>

                        <span class="attendance-detail__separator">〜</span>

                        <input type="text" name="breaks[{{ $attendance->breakTimes->count() }}][break_end]"
                            value="" class="attendance-detail__input" {{ $pendingRequest ? 'readonly' : '' }}>
                    </div>
                </div>

                <div class="attendance-detail__row attendance-detail__row--textarea">
                    <div class="attendance-detail__label">備考</div>

                    <div class="attendance-detail__value">
                        <textarea name="reason" class="attendance-detail__textarea" {{ $pendingRequest ? 'readonly' : '' }}></textarea>
                    </div>
                </div>
            </div>

            <div class="attendance-detail__button-area">
                @if ($pendingRequest)
                    <p class="attendance-detail__pending-message">
                        *承認待ちのため修正はできません。
                    </p>
                @else
                    <button type="submit" class="attendance-detail__button">
                        修正
                    </button>
                @endif
            </div>
        </form>
    </div>
@endsection

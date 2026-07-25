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

                    <div class="attendance-detail__value">
                        <div class="attendance-detail__time">
                            <input type="text" name="clock_in"
                                value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}"
                                class="attendance-detail__input" {{ $pendingRequest ? 'readonly' : '' }}>

                            <span class="attendance-detail__separator">〜</span>

                            <input type="text" name="clock_out"
                                value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}"
                                class="attendance-detail__input" {{ $pendingRequest ? 'readonly' : '' }}>
                        </div>

                        @error('clock_in')
                            <p class="attendance-detail__error">{{ $message }}</p>
                        @enderror

                        @error('clock_out')
                            <p class="attendance-detail__error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @foreach ($attendance->breakTimes as $index => $breakTime)
                    <div class="attendance-detail__row">
                        <div class="attendance-detail__label">
                            休憩{{ $index === 0 ? '' : $index + 1 }}
                        </div>

                        <div class="attendance-detail__value">
                            <div class="attendance-detail__time">
                                <input type="text" name="breaks[{{ $index }}][break_start]"
                                    value="{{ old("breaks.$index.break_start", $breakTime->break_start ? \Carbon\Carbon::parse($breakTime->break_start)->format('H:i') : '') }}"
                                    class="attendance-detail__input" {{ $pendingRequest ? 'readonly' : '' }}>

                                <span class="attendance-detail__separator">〜</span>

                                <input type="text" name="breaks[{{ $index }}][break_end]"
                                    value="{{ old("breaks.$index.break_end", $breakTime->break_end ? \Carbon\Carbon::parse($breakTime->break_end)->format('H:i') : '') }}"
                                    class="attendance-detail__input" {{ $pendingRequest ? 'readonly' : '' }}>
                            </div>

                            @error("breaks.$index.break_start")
                                <p class="attendance-detail__error">{{ $message }}</p>
                            @enderror

                            @error("breaks.$index.break_end")
                                <p class="attendance-detail__error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @endforeach

                @php
                    $newBreakIndex = $attendance->breakTimes->count();
                @endphp

                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">
                        休憩{{ $newBreakIndex === 0 ? '' : $newBreakIndex + 1 }}
                    </div>

                    <div class="attendance-detail__value">
                        <div class="attendance-detail__time">
                            <input type="text" name="breaks[{{ $newBreakIndex }}][break_start]"
                                value="{{ old("breaks.$newBreakIndex.break_start") }}" class="attendance-detail__input"
                                {{ $pendingRequest ? 'readonly' : '' }}>

                            <span class="attendance-detail__separator">〜</span>

                            <input type="text" name="breaks[{{ $newBreakIndex }}][break_end]"
                                value="{{ old("breaks.$newBreakIndex.break_end") }}" class="attendance-detail__input"
                                {{ $pendingRequest ? 'readonly' : '' }}>
                        </div>

                        @error("breaks.$newBreakIndex.break_start")
                            <p class="attendance-detail__error">{{ $message }}</p>
                        @enderror

                        @error("breaks.$newBreakIndex.break_end")
                            <p class="attendance-detail__error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="attendance-detail__row attendance-detail__row--textarea">
                    <div class="attendance-detail__label">備考</div>

                    <div class="attendance-detail__value">
                        <textarea name="reason" class="attendance-detail__textarea" {{ $pendingRequest ? 'readonly' : '' }}>{{ old('reason', $pendingRequest ? $pendingRequest->reason : '') }}</textarea>

                        @error('reason')
                            <p class="attendance-detail__error">{{ $message }}</p>
                        @enderror
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

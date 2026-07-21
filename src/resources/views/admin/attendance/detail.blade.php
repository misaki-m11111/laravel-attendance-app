@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/detail.css') }}">
@endsection

@section('content')
    @php
        $attendanceDate = \Carbon\Carbon::parse($attendance->attendance_date);
        $newBreakIndex = $attendance->breakTimes->count();
    @endphp

    <main class="attendance-detail">
        <div class="attendance-detail__inner">
            <h1 class="attendance-detail__title">勤怠詳細</h1>

            @if (session('error'))
                <p class="attendance-detail__error">
                    {{ session('error') }}
                </p>
            @endif

            <form
                class="attendance-detail__form"
                method="POST"
                action="{{ route('admin.attendance.update', $attendance->id) }}"
            >
                @csrf
                @method('PUT')

                <div class="attendance-detail__card">
                    <div class="attendance-detail__row">
                        <div class="attendance-detail__label">名前</div>

                        <div class="attendance-detail__value attendance-detail__name">
                            {{ $attendance->user->name }}
                        </div>
                    </div>

                    <div class="attendance-detail__row">
                        <div class="attendance-detail__label">日付</div>

                        <div class="attendance-detail__value attendance-detail__date">
                            <span>{{ $attendanceDate->format('Y年') }}</span>
                            <span>{{ $attendanceDate->format('n月j日') }}</span>
                        </div>
                    </div>

                    <div class="attendance-detail__row">
                        <div class="attendance-detail__label">出勤・退勤</div>

                        <div class="attendance-detail__value attendance-detail__time">
                            <input
                                class="attendance-detail__time-input"
                                type="time"
                                name="clock_in"
                                value="{{ old(
                                    'clock_in',
                                    $attendance->clock_in
                                        ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                                        : ''
                                ) }}"
                                @if ($hasPendingRequest) disabled @endif
                            >

                            <span class="attendance-detail__separator">〜</span>

                            <input
                                class="attendance-detail__time-input"
                                type="time"
                                name="clock_out"
                                value="{{ old(
                                    'clock_out',
                                    $attendance->clock_out
                                        ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                                        : ''
                                ) }}"
                                @if ($hasPendingRequest) disabled @endif
                            >
                        </div>
                    </div>

                    @foreach ($attendance->breakTimes as $breakTime)
                        <div class="attendance-detail__row">
                            <div class="attendance-detail__label">
                                休憩{{ $loop->iteration > 1 ? $loop->iteration : '' }}
                            </div>

                            <div class="attendance-detail__value attendance-detail__time">
                                <input
                                    type="hidden"
                                    name="breaks[{{ $loop->index }}][id]"
                                    value="{{ $breakTime->id }}"
                                >

                                <input
                                    class="attendance-detail__time-input"
                                    type="time"
                                    name="breaks[{{ $loop->index }}][break_start]"
                                    value="{{ old(
                                        "breaks.{$loop->index}.break_start",
                                        $breakTime->break_start
                                            ? \Carbon\Carbon::parse($breakTime->break_start)->format('H:i')
                                            : ''
                                    ) }}"
                                    @if ($hasPendingRequest) disabled @endif
                                >

                                <span class="attendance-detail__separator">〜</span>

                                <input
                                    class="attendance-detail__time-input"
                                    type="time"
                                    name="breaks[{{ $loop->index }}][break_end]"
                                    value="{{ old(
                                        "breaks.{$loop->index}.break_end",
                                        $breakTime->break_end
                                            ? \Carbon\Carbon::parse($breakTime->break_end)->format('H:i')
                                            : ''
                                    ) }}"
                                    @if ($hasPendingRequest) disabled @endif
                                >
                            </div>
                        </div>
                    @endforeach

                    <div class="attendance-detail__row">
                        <div class="attendance-detail__label">
                            休憩{{ $newBreakIndex + 1 }}
                        </div>

                        <div class="attendance-detail__value attendance-detail__time">
                            <input
                                class="attendance-detail__time-input"
                                type="time"
                                name="breaks[{{ $newBreakIndex }}][break_start]"
                                value="{{ old("breaks.{$newBreakIndex}.break_start") }}"
                                @if ($hasPendingRequest) disabled @endif
                            >

                            <span class="attendance-detail__separator">〜</span>

                            <input
                                class="attendance-detail__time-input"
                                type="time"
                                name="breaks[{{ $newBreakIndex }}][break_end]"
                                value="{{ old("breaks.{$newBreakIndex}.break_end") }}"
                                @if ($hasPendingRequest) disabled @endif
                            >
                        </div>
                    </div>

                    <div class="attendance-detail__row attendance-detail__row--remarks">
                        <div class="attendance-detail__label">備考</div>

                        <div class="attendance-detail__value">
                            <textarea
                                class="attendance-detail__remarks"
                                id="remarks"
                                name="remarks"
                                @if ($hasPendingRequest) disabled @endif
                            >{{ old('remarks', $attendance->remarks) }}</textarea>
                        </div>
                    </div>
                </div>

                @if ($hasPendingRequest)
                    <p class="attendance-detail__pending-message">
                        承認待ちのため修正はできません
                    </p>
                @else
                    <div class="attendance-detail__button-area">
                        <button class="attendance-detail__button" type="submit">
                            修正
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </main>
@endsection
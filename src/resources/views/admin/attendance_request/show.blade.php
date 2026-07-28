@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/detail.css') }}">
@endsection

@section('content')
    @php
        $attendanceDate = \Carbon\Carbon::parse($attendanceRequest->attendance->attendance_date);
    @endphp

    <main class="attendance-detail">
        <div class="attendance-detail__inner">
            <h1 class="attendance-detail__title">
                勤怠詳細
            </h1>

            <div class="attendance-detail__card">
                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">
                        名前
                    </div>

                    <div class="attendance-detail__value">
                        <span class="attendance-detail__name-text">
                            {{ $attendanceRequest->user->name }}
                        </span>
                    </div>
                </div>

                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">
                        日付
                    </div>

                    <div class="attendance-detail__value">
                        <span class="attendance-detail__date-year">
                            {{ $attendanceDate->format('Y年') }}
                        </span>

                        <span class="attendance-detail__date-day">
                            {{ $attendanceDate->format('n月j日') }}
                        </span>
                    </div>
                </div>

                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">
                        出勤・退勤
                    </div>

                    <div class="attendance-detail__value">
                        <span class="attendance-detail__time-start">
                            {{ $attendanceRequest->requested_clock_in
                                ? \Carbon\Carbon::parse($attendanceRequest->requested_clock_in)->format('H:i')
                                : '' }}
                        </span>

                        <span class="attendance-detail__separator">
                            〜
                        </span>

                        <span class="attendance-detail__time-end">
                            {{ $attendanceRequest->requested_clock_out
                                ? \Carbon\Carbon::parse($attendanceRequest->requested_clock_out)->format('H:i')
                                : '' }}
                        </span>
                    </div>
                </div>

                @php
                    $breakCount = max(2, $attendanceRequest->attendanceRequestBreaks->count());
                @endphp

                @for ($i = 0; $i < $breakCount; $i++)
                    @php
                        $requestBreak = $attendanceRequest->attendanceRequestBreaks->get($i);
                    @endphp

                    <div class="attendance-detail__row">
                        <div class="attendance-detail__label">
                            休憩{{ $i >= 1 ? $i + 1 : '' }}
                        </div>

                        <div class="attendance-detail__value">
                            <span class="attendance-detail__time-start">
                                {{ $requestBreak && $requestBreak->requested_break_start
                                    ? \Carbon\Carbon::parse($requestBreak->requested_break_start)->format('H:i')
                                    : '' }}
                            </span>

                            <span class="attendance-detail__separator">
                                〜
                            </span>

                            <span class="attendance-detail__time-end">
                                {{ $requestBreak && $requestBreak->requested_break_end
                                    ? \Carbon\Carbon::parse($requestBreak->requested_break_end)->format('H:i')
                                    : '' }}
                            </span>
                        </div>
                    </div>
                @endfor

                <div class="attendance-detail__row attendance-detail__row--remarks">
                    <div class="attendance-detail__label">
                        備考
                    </div>

                    <div class="attendance-detail__value">
                        <span class="attendance-detail__remarks-text">
                            {{ $attendanceRequest->reason }}
                        </span>
                    </div>
                </div>
            </div>

            @if (session('error'))
                <p class="attendance-detail__error" role="alert">
                    {{ session('error') }}
                </p>
            @endif

            <div class="attendance-detail__button-area">
                @if ((int) $attendanceRequest->status === 0)
                    <form method="POST" action="{{ route('admin.attendance.request.approve', $attendanceRequest->id) }}">
                        @csrf
                        @method('PATCH')

                        <button class="attendance-detail__button" type="submit">
                            承認
                        </button>
                    </form>
                @else
                    <button class="attendance-detail__button attendance-detail__button--approved" type="button" disabled>
                        承認済み
                    </button>
                @endif
            </div>
        </div>
    </main>
@endsection

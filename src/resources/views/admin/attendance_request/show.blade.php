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
            <h1 class="attendance-detail__title">勤怠詳細</h1>

            <div class="attendance-detail__card">
                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">
                        名前
                    </div>

                    <div class="attendance-detail__value attendance-detail__name">
                        {{ $attendanceRequest->user->name }}
                    </div>
                </div>

                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">
                        日付
                    </div>

                    <div class="attendance-detail__value attendance-detail__date">
                        <span>
                            {{ $attendanceDate->format('Y年') }}
                        </span>

                        <span>
                            {{ $attendanceDate->format('n月j日') }}
                        </span>
                    </div>
                </div>

                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">
                        出勤・退勤
                    </div>

                    <div class="attendance-detail__value attendance-detail__time">
                        <input class="attendance-detail__time-input" type="time"
                            value="{{ $attendanceRequest->requested_clock_in
                                ? \Carbon\Carbon::parse($attendanceRequest->requested_clock_in)->format('H:i')
                                : '' }}"
                            readonly>

                        <span class="attendance-detail__separator">
                            〜
                        </span>

                        <input class="attendance-detail__time-input" type="time"
                            value="{{ $attendanceRequest->requested_clock_out
                                ? \Carbon\Carbon::parse($attendanceRequest->requested_clock_out)->format('H:i')
                                : '' }}"
                            readonly>
                    </div>
                </div>

                @forelse ($attendanceRequest->attendanceRequestBreaks
                        as $requestBreak)
                    <div class="attendance-detail__row">
                        <div class="attendance-detail__label">
                            休憩{{ $loop->iteration > 1 ? $loop->iteration : '' }}
                        </div>

                        <div class="attendance-detail__value attendance-detail__time">
                            <input class="attendance-detail__time-input" type="time"
                                value="{{ $requestBreak->requested_break_start
                                    ? \Carbon\Carbon::parse($requestBreak->requested_break_start)->format('H:i')
                                    : '' }}"
                                readonly>

                            <span class="attendance-detail__separator">
                                〜
                            </span>

                            <input class="attendance-detail__time-input" type="time"
                                value="{{ $requestBreak->requested_break_end
                                    ? \Carbon\Carbon::parse($requestBreak->requested_break_end)->format('H:i')
                                    : '' }}"
                                readonly>
                        </div>
                    </div>
                @empty
                    <div class="attendance-detail__row">
                        <div class="attendance-detail__label">
                            休憩
                        </div>

                        <div class="attendance-detail__value attendance-detail__time">
                            <input class="attendance-detail__time-input" type="time" value="" readonly>

                            <span class="attendance-detail__separator">
                                〜
                            </span>

                            <input class="attendance-detail__time-input" type="time" value="" readonly>
                        </div>
                    </div>
                @endforelse

                <div class="attendance-detail__row attendance-detail__row--remarks">
                    <div class="attendance-detail__label">
                        備考
                    </div>

                    <div class="attendance-detail__value">
                        <textarea class="attendance-detail__remarks" readonly>{{ $attendanceRequest->reason }}</textarea>
                    </div>
                </div>
            </div>

            <div class="attendance-detail__button-area">
                @if ((int) $attendanceRequest->status === 0)
                    <form method="POST"
                        action="{{ route('admin.attendance.request.approve', $attendanceRequest->id) }}">
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

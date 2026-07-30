@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/list.css') }}">
@endsection

@section('content')
    <main class="attendance-list">
        <div class="attendance-list__inner">
            <h1 class="attendance-list__title">勤怠一覧</h1>

            <div class="attendance-list__month-nav">
                <a class="attendance-list__month-link" href="{{ route('attendance.list', ['month' => $previousMonth]) }}">
                    <img class="attendance-list__arrow-icon attendance-list__arrow-icon--prev"
                        src="{{ asset('images/arrow.png') }}" alt="">
                    <span>前月</span>
                </a>

                <div class="attendance-list__current-month">
                    <img class="attendance-list__calendar-icon" src="{{ asset('images/calendar.png') }}" alt="">
                    <span>{{ $currentMonth->format('Y/m') }}</span>
                </div>

                <a class="attendance-list__month-link" href="{{ route('attendance.list', ['month' => $nextMonth]) }}">
                    <span>翌月</span>
                    <img class="attendance-list__arrow-icon attendance-list__arrow-icon--next"
                        src="{{ asset('images/arrow.png') }}" alt="">
                </a>
            </div>

            <table class="attendance-list__table">
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($calendarDates as $date)
                        @php
                            $attendance = $attendanceByDate->get($date->toDateString());
                            $weekDays = ['日', '月', '火', '水', '木', '金', '土'];

                            $breakMinutes = 0;
                            $workMinutes = null;

                            if ($attendance) {
                                $breakMinutes = $attendance->breakTimes
                                    ->filter(function ($breakTime) {
                                        return $breakTime->break_start && $breakTime->break_end;
                                    })
                                    ->sum(function ($breakTime) {
                                        return \Carbon\Carbon::parse($breakTime->break_start)->diffInMinutes(
                                            \Carbon\Carbon::parse($breakTime->break_end),
                                        );
                                    });

                                if ($attendance->clock_in && $attendance->clock_out) {
                                    $workMinutes =
                                        \Carbon\Carbon::parse($attendance->clock_in)->diffInMinutes(
                                            \Carbon\Carbon::parse($attendance->clock_out),
                                        ) - $breakMinutes;
                                }
                            }

                            $breakTimeText =
                                $breakMinutes > 0
                                    ? sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60)
                                    : '';

                            $workTimeText = !is_null($workMinutes)
                                ? sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60)
                                : '';
                        @endphp

                        <tr>
                            <td>
                                {{ $date->format('m/d') }}({{ $weekDays[$date->dayOfWeek] }})
                            </td>

                            <td>
                                {{ $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
                            </td>

                            <td>
                                {{ $attendance && $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
                            </td>

                            <td>
                                {{ $breakTimeText }}
                            </td>

                            <td>
                                {{ $workTimeText }}
                            </td>

                            <td>
                                @if ($attendance)
                                    <a href="{{ route('attendance.detail', $attendance->id) }}"
                                        class="attendance-list__detail-link">
                                        詳細
                                    </a>
                                @else
                                    <span class="attendance-list__detail-link detail-link--disabled">
                                        詳細
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </main>
@endsection

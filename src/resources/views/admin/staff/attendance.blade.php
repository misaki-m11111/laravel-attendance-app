@extends('layouts.app')

@section('title', 'スタッフ別勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/staff/attendance.css') }}">
@endsection

@section('content')
    <main class="staff-attendance">
        <div class="staff-attendance__inner">
            <h1 class="staff-attendance__title">
                {{ $user->name }}さんの勤怠
            </h1>

            <div class="staff-attendance__month-nav">
                <a class="staff-attendance__month-link"
                    href="{{ route('admin.staff.show', [
                        'id' => $user->id,
                        'month' => $selectedMonth->copy()->subMonth()->format('Y-m'),
                    ]) }}">
                    <img class="staff-attendance__arrow-icon" src="{{ asset('images/arrow.png') }}" alt="">
                    <span>前月</span>
                </a>

                <div class="staff-attendance__current-month">
                    <img class="staff-attendance__calendar-icon" src="{{ asset('images/calendar.png') }}" alt="">
                    <span>{{ $selectedMonth->format('Y/m') }}</span>
                </div>

                <a class="staff-attendance__month-link"
                    href="{{ route('admin.staff.show', [
                        'id' => $user->id,
                        'month' => $selectedMonth->copy()->addMonth()->format('Y-m'),
                    ]) }}">
                    <span>翌月</span>
                    <img class="staff-attendance__arrow-icon staff-attendance__arrow-icon--next"
                        src="{{ asset('images/arrow.png') }}" alt="">
                </a>
            </div>

            <table class="staff-attendance__table">
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
                    @foreach ($dates as $date)
                        @php
                            $attendance = $attendancesByDate->get($date->toDateString());

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
                                    $workingMinutes = \Carbon\Carbon::parse($attendance->clock_in)->diffInMinutes(
                                        \Carbon\Carbon::parse($attendance->clock_out),
                                    );

                                    $workMinutes = max($workingMinutes - $breakMinutes, 0);
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

                            <td>{{ $breakTimeText }}</td>

                            <td>{{ $workTimeText }}</td>

                            <td>
                                @if ($attendance)
                                    <a class="staff-attendance__detail-link"
                                        href="{{ route('admin.attendance.show', $attendance->id) }}">
                                        詳細
                                    </a>
                                @else
                                    <span class="staff-attendance__detail-link staff-attendance__detail-link--disabled"
                                        aria-disabled="true">
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

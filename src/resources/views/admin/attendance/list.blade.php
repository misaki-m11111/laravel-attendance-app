@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">
@endsection

@section('content')
    <main class="attendance-list">
        <div class="attendance-list__inner">
            <h1 class="attendance-list__title attendance-list__title--daily">
                {{ $selectedDate->format('Y年n月j日') }}の勤怠
            </h1>

            <div class="attendance-list__date-nav">
                <a class="attendance-list__date-link"
                    href="{{ route('admin.attendance.list', [
                        'date' => $selectedDate->copy()->subDay()->toDateString(),
                    ]) }}">
                    <img class="attendance-list__arrow-icon attendance-list__arrow-icon--prev"
                        src="{{ asset('images/arrow.png') }}" alt="">
                    <span>前日</span>
                </a>

                <form class="attendance-list__date-form" method="GET" action="{{ route('admin.attendance.list') }}">
                    <label class="attendance-list__current-date">
                        <img class="attendance-list__calendar-icon" src="{{ asset('images/calendar.png') }}" alt="">

                        <input class="attendance-list__date-input" type="date" name="date"
                            value="{{ $selectedDate->toDateString() }}" aria-label="表示する日付" onchange="this.form.submit()">
                    </label>
                </form>

                <a class="attendance-list__date-link"
                    href="{{ route('admin.attendance.list', [
                        'date' => $selectedDate->copy()->addDay()->toDateString(),
                    ]) }}">
                    <span>翌日</span>
                    <img class="attendance-list__arrow-icon attendance-list__arrow-icon--next"
                        src="{{ asset('images/arrow.png') }}" alt="">
                </a>
            </div>

            <table class="attendance-list__table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($attendances as $attendance)
                        @php
                            $breakMinutes = $attendance->breakTimes
                                ->filter(function ($breakTime) {
                                    return $breakTime->break_start && $breakTime->break_end;
                                })
                                ->sum(function ($breakTime) {
                                    return \Carbon\Carbon::parse($breakTime->break_start)->diffInMinutes(
                                        \Carbon\Carbon::parse($breakTime->break_end),
                                    );
                                });

                            $formattedBreakTime =
                                $breakMinutes > 0
                                    ? sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60)
                                    : '';

                            $formattedWorkTime = '';

                            if ($attendance->clock_in && $attendance->clock_out) {
                                $totalMinutes = \Carbon\Carbon::parse($attendance->clock_in)->diffInMinutes(
                                    \Carbon\Carbon::parse($attendance->clock_out),
                                );

                                $workMinutes = max($totalMinutes - $breakMinutes, 0);

                                $formattedWorkTime = sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);
                            }
                        @endphp

                        <tr>
                            <td>
                                {{ $attendance->user->name }}
                            </td>

                            <td>
                                {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
                            </td>

                            <td>
                                {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
                            </td>

                            <td>
                                {{ $formattedBreakTime }}
                            </td>

                            <td>
                                {{ $formattedWorkTime }}
                            </td>

                            <td>
                                <a class="attendance-list__detail-link"
                                    href="{{ route('admin.attendance.show', $attendance->id) }}">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="attendance-list__empty" colspan="6">
                                勤怠情報がありません
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
@endsection

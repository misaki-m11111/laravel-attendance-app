@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">
@endsection

@section('content')
    <main class="admin-attendance">
        <h1 class="admin-attendance__title">
            {{ $selectedDate->format('Y年n月j日') }}の勤怠
        </h1>

        <div class="date-switcher">
            <a class="date-switcher__link date-switcher__link--previous"
                href="{{ route('admin.attendance.list', [
                    'date' => $selectedDate->copy()->subDay()->toDateString(),
                ]) }}">
                <span aria-hidden="true">←</span>
                前日
            </a>

            <form class="date-switcher__form" method="GET" action="{{ route('admin.attendance.list') }}">
                <label class="date-switcher__picker">
                    <span class="date-switcher__calendar" aria-hidden="true"></span>

                    <input class="date-switcher__input" type="date" name="date"
                        value="{{ $selectedDate->toDateString() }}" aria-label="表示する日付" onchange="this.form.submit()">
                </label>
            </form>

            <a class="date-switcher__link date-switcher__link--next"
                href="{{ route('admin.attendance.list', [
                    'date' => $selectedDate->copy()->addDay()->toDateString(),
                ]) }}">
                翌日
                <span aria-hidden="true">→</span>
            </a>
        </div>

        <div class="attendance-table-wrapper">
            <table class="attendance-table">
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
                            $breakMinutes = 0;

                            foreach ($attendance->breakTimes as $breakTime) {
                                if ($breakTime->break_start && $breakTime->break_end) {
                                    $breakMinutes += \Carbon\Carbon::parse($breakTime->break_start)->diffInMinutes(
                                        \Carbon\Carbon::parse($breakTime->break_end),
                                    );
                                }
                            }

                            $formattedBreakTime = sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60);

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
                            <td class="attendance-table__name">
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
                                <a href="{{ route('admin.attendance.show', $attendance->id) }}">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="attendance-table__empty" colspan="6">
                                勤怠情報がありません
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
@endsection

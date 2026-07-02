@extends('layouts.app')

@section('title', '勤怠登録')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
    <div class="attendance-page">
        <div class="attendance-card">
            <p class="attendance-status">{{ $status }}</p>

            @php
                $today = now();
                $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
            @endphp

            <p class="attendance-date">
                {{ $today->format('Y年n月j日') }}（{{ $weekdays[$today->dayOfWeek] }}）
            </p>

            <p class="attendance-time">{{ now()->format('H:i') }}</p>

            @if ($status === '勤務外')
                <form method="POST" action="{{ route('attendance.store') }}">
                    @csrf
                    <button class="attendance-button attendance-button--black" type="submit" name="action"
                        value="clock_in">
                        出勤
                    </button>
                </form>
            @endif

            @if ($status === '出勤中')
                <div class="attendance-button-group">
                    <form method="POST" action="{{ route('attendance.store') }}">
                        @csrf
                        <button class="attendance-button attendance-button--black" type="submit" name="action"
                            value="clock_out">
                            退勤
                        </button>
                    </form>

                    <form method="POST" action="{{ route('attendance.store') }}">
                        @csrf
                        <button class="attendance-button attendance-button--white" type="submit" name="action"
                            value="break_start">
                            休憩入
                        </button>
                    </form>
                </div>
            @endif

            @if ($status === '休憩中')
                <form method="POST" action="{{ route('attendance.store') }}">
                    @csrf
                    <button class="attendance-button attendance-button--white" type="submit" name="action"
                        value="break_end">
                        休憩戻
                    </button>
                </form>
            @endif

            @if ($status === '退勤済')
                <p class="attendance-message">お疲れ様でした。</p>
            @endif
        </div>
    </div>
@endsection

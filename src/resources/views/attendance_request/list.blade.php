@extends('layouts.app')

@section('title', '申請一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance_request/list.css') }}">
@endsection

@section('content')
    <div class="request-list">
        <h1 class="request-list__title">申請一覧</h1>

        {{-- 承認待ち・承認済みタブ --}}
        <div class="request-list__tabs">
            <a href="{{ route('attendance.request.index', ['tab' => 'pending']) }}"
                class="request-list__tab {{ $tab === 'pending' ? 'request-list__tab--active' : '' }}">
                承認待ち
            </a>

            <a href="{{ route('attendance.request.index', ['tab' => 'approved']) }}"
                class="request-list__tab {{ $tab === 'approved' ? 'request-list__tab--active' : '' }}">
                承認済み
            </a>
        </div>

        {{-- 申請一覧テーブル --}}
        <div class="request-list__table-wrapper">
            <table class="request-list__table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($attendanceRequests as $attendanceRequest)
                        <tr>
                            <td>
                                {{ $attendanceRequest->status === 0 ? '承認待ち' : '承認済み' }}
                            </td>

                            <td>
                                {{ $attendanceRequest->user->name }}
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($attendanceRequest->attendance->attendance_date)->format('Y/m/d') }}
                            </td>

                            <td>
                                {{ $attendanceRequest->reason }}
                            </td>

                            <td>
                                {{ $attendanceRequest->created_at->format('Y/m/d') }}
                            </td>

                            <td>
                                @if ($isAdmin)
                                    <a
                                        href="{{ route('admin.attendance.request.show', $attendanceRequest->id) }}">
                                        詳細
                                    </a>
                                @else
                                    <a
                                        href="{{ route('attendance.detail', $attendanceRequest->attendance_id) }}">
                                        詳細
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="request-list__empty" colspan="6">
                                申請データはありません。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

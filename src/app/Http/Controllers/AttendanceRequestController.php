<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\AttendanceRequestBreak;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceRequestController extends Controller
{

/**
 * ログイン中の利用者に応じた申請一覧を表示する。
 *
 * 管理者には全ユーザーの申請を表示し、
 * 一般ユーザーには本人の申請のみ表示する。
 *
 * @param Request $request
 * @return View
 */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'pending');
        $status = $tab === 'approved' ? 1 : 0;

        $isAdmin = Auth::guard('admin')->check();

        $query = AttendanceRequest::with([
            'user',
            'attendance'
        ])
            ->where('status', $status);

        if (!$isAdmin) {
            $query->where('user_id', Auth::guard('web')->id());
        }

        $attendanceRequests = $query
            ->latest()
            ->get();

        return view('attendance_request.list', compact('attendanceRequests', 'tab', 'isAdmin'));
    }

    /**
     * 修正申請を保存する。
     *
     * @param AttendanceCorrectionRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function store(AttendanceCorrectionRequest $request, int $id): RedirectResponse
    {
        $validated = $request->validated();

        $attendance = Attendance::where('user_id', Auth::id())
            ->findOrFail($id);

        $attendanceDate = Carbon::parse($attendance->attendance_date)->toDateString();

        $attendanceRequest = AttendanceRequest::create([
            'user_id' => Auth::id(),
            'attendance_id' => $attendance->id,
            'requested_clock_in' => Carbon::parse($attendanceDate . ' ' . $validated['clock_in']),
            'requested_clock_out' => Carbon::parse($attendanceDate . ' ' . $validated['clock_out']),
            'reason' => $validated['reason'],
            'status' => 0,
        ]);

        foreach ($validated['breaks'] ?? [] as $break) {
            if (empty($break['break_start']) && empty($break['break_end'])) {
                continue;
            }

            AttendanceRequestBreak::create([
                'attendance_request_id' => $attendanceRequest->id,
                'requested_break_start' => Carbon::parse($attendanceDate . ' ' . $break['break_start']),
                'requested_break_end' => Carbon::parse($attendanceDate . ' ' . $break['break_end']),
            ]);
        }

        return redirect()->route('attendance.detail', $attendance->id);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminAttendanceController extends Controller
{
    /**
     * 勤怠一覧画面を表示する。
     */
    public function index(Request $request): View
    {
        $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $selectedDate = Carbon::parse(
            $request->input('date', now()->toDateString())
        );

        $attendances = Attendance::with(['user', 'breakTimes'])
            ->whereDate('attendance_date', $selectedDate->toDateString())
            ->get();

        return view('admin.attendance.list', compact('attendances', 'selectedDate'));
    }

    /**
     * 管理者用の勤怠詳細画面を表示する。
     */
    public function show(string $id): View
    {
        $attendance = Attendance::with('user', 'breakTimes')->findOrFail($id);

        $hasPendingRequest = AttendanceRequest::where(
            'attendance_id',
            $attendance->id
        )
            ->where('status', 0)
            ->exists();

        return view('admin.attendance.detail', compact('attendance', 'hasPendingRequest'));
    }

    /**
     * 管理者が勤怠情報を更新する。
     */
    public function update(
        AdminAttendanceUpdateRequest $request,
        string $id
    ): RedirectResponse {
        $validated = $request->validated();

        $attendance = Attendance::findOrFail($id);

        $hasPendingRequest = AttendanceRequest::where(
            'attendance_id',
            $attendance->id
        )
            ->where('status', 0)
            ->exists();

        if ($hasPendingRequest) {
            return redirect()
                ->route('admin.attendance.show', $attendance->id);
        }

        $attendanceDate = Carbon::parse(
            $attendance->attendance_date
        )->toDateString();

        DB::transaction(function () use (
            $validated,
            $attendance,
            $attendanceDate
        ) {
            $attendance->clock_in = Carbon::parse(
                $attendanceDate . ' ' . $validated['clock_in']
            );

            $attendance->clock_out = Carbon::parse(
                $attendanceDate . ' ' . $validated['clock_out']
            );

            $attendance->remarks = $validated['remarks'];

            $attendance->save();

            foreach ($validated['breaks'] ?? [] as $breakData) {
                $breakStart = !empty($breakData['break_start'])
                    ? Carbon::parse(
                        $attendanceDate . ' ' . $breakData['break_start']
                    )
                    : null;

                $breakEnd = !empty($breakData['break_end'])
                    ? Carbon::parse(
                        $attendanceDate . ' ' . $breakData['break_end']
                    )
                    : null;

                if (!empty($breakData['id'])) {
                    $breakTime = $attendance->breakTimes()
                        ->findOrFail($breakData['id']);

                    if ($breakStart === null && $breakEnd === null) {
                        continue;
                    }

                    $breakTime->break_start = $breakStart;
                    $breakTime->break_end = $breakEnd;
                    $breakTime->save();

                    continue;
                }

                if ($breakStart !== null || $breakEnd !== null) {
                    $breakTime = $attendance->breakTimes()->make();

                    $breakTime->break_start = $breakStart;
                    $breakTime->break_end = $breakEnd;

                    $breakTime->save();
                }
            }
        });

        return redirect()->route(
            'admin.attendance.show',
            $attendance->id
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

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

    public function show(Request $request, string $id)
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

    public function update(Request $request, string $id)
    {
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

        DB::transaction(function () use ($request, $attendance, $attendanceDate) {
            $attendance->clock_in = $request->filled('clock_in')
                ? Carbon::parse(
                    $attendanceDate . ' ' . $request->input('clock_in')
                )
                : null;

            $attendance->clock_out = $request->filled('clock_out')
                ? Carbon::parse(
                    $attendanceDate . ' ' . $request->input('clock_out')
                )
                : null;

            $attendance->remarks = $request->input('remarks');

            $attendance->save();

            foreach ($request->input('breaks', []) as $breakData) {
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

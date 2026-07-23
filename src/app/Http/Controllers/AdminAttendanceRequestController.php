<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\DB;

class AdminAttendanceRequestController extends Controller
{
    public function show(string $attendance_correct_request_id)
    {
        $attendanceRequest = AttendanceRequest::with([
            'user',
            'attendance',
            'attendanceRequestBreaks',
        ])->findOrFail($attendance_correct_request_id);

        return view('admin.attendance_request.show', compact('attendanceRequest'));
    }

    public function approve(string $attendance_correct_request_id)
    {
        $approved = DB::transaction(function () use (
            $attendance_correct_request_id
        ) {
            $attendanceRequest = AttendanceRequest::with([
                'attendance.breakTimes',
                'attendanceRequestBreaks',
            ])
                ->lockForUpdate()
                ->findOrFail($attendance_correct_request_id);

            if ((int) $attendanceRequest->status !== 0) {
                return false;
            }

            $attendance = $attendanceRequest->attendance;

            $attendance->update([
                'clock_in' => $attendanceRequest->requested_clock_in,
                'clock_out' => $attendanceRequest->requested_clock_out,
                'remarks' => $attendanceRequest->reason,
            ]);

            if ($attendanceRequest->attendanceRequestBreaks->isNotEmpty()) {
                $attendance->breakTimes()->delete();

                foreach (
                    $attendanceRequest->attendanceRequestBreaks
                    as $requestBreak
                ) {
                    $attendance->breakTimes()->create([
                        'break_start' => $requestBreak->requested_break_start,
                        'break_end' => $requestBreak->requested_break_end,
                    ]);
                }
            }

            $attendanceRequest->update([
                'status' => 1,
            ]);

            return true;
        });

        if (!$approved) {
            return redirect()
                ->route(
                    'admin.attendance.request.show',
                    $attendance_correct_request_id
                )
                ->with('error', 'この申請はすでに承認済みです。');
        }

        return redirect()->route(
            'admin.attendance.request.show',
            $attendance_correct_request_id
        );
    }
}

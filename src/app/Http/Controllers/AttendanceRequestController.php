<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\AttendanceRequestBreak;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceRequestController extends Controller
{
    public function store(Request $request, $id)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->findOrFail($id);

        $attendanceDate = Carbon::parse($attendance->attendance_date)->toDateString();

        $attendanceRequest = AttendanceRequest::create([
            'user_id' => Auth::id(),
            'attendance_id' => $attendance->id,
            'requested_clock_in' => Carbon::parse($attendanceDate . ' ' . $request->clock_in),
            'requested_clock_out' => Carbon::parse($attendanceDate . ' ' . $request->clock_out),
            'reason' => $request->reason,
            'status' => 0,
        ]);

        foreach ($request->breaks ?? [] as $break) {
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

    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pending');
        $status = $tab === 'approved' ? 1 : 0;

        $attendanceRequests = AttendanceRequest::with([
            'user',
            'attendance'
        ])
            ->where('user_id', Auth::id())
            ->where('status', $status)
            ->latest()
            ->get();

            return view('attendance_request.list',compact('attendanceRequests','tab'));
    }
}

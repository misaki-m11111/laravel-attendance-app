<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', $today)
            ->first();

        $status = $attendance ? $attendance->status : '勤務外';

        return view('attendance.index', compact('attendance', 'status'));
    }

    public function store(Request $request)
    {
        $action = $request->input('action');

        $today = Carbon::today();

        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', $today)
            ->first();

        if ($action === 'clock_in') {
            if (!$attendance) {
                Attendance::create([
                    'user_id' => Auth::id(),
                    'attendance_date' => $today,
                    'clock_in' => Carbon::now(),
                    'status' => '出勤中',
                ]);
            }
        }

        if ($action === 'break_start') {
            if ($attendance && $attendance->status === '出勤中') {
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => Carbon::now(),
                ]);

                $attendance->update([
                    'status' => '休憩中',
                ]);
            }
        }

        if ($action === 'break_end') {
            if ($attendance && $attendance->status === '休憩中') {
                $breakTime = BreakTime::where('attendance_id', $attendance->id)
                    ->whereNull('break_end')
                    ->latest()
                    ->first();

                if ($breakTime) {
                    $breakTime->update([
                        'break_end' => Carbon::now(),
                    ]);

                    $attendance->update([
                        'status' => '出勤中',
                    ]);
                }
            }
        }

        if ($action === 'clock_out') {
            if ($attendance && $attendance->status === '出勤中') {
                $attendance->update([
                    'clock_out' => Carbon::now(),
                    'status' => '退勤済',
                ]);
            }
        }

        return redirect()->route('attendance.index');
    }
}

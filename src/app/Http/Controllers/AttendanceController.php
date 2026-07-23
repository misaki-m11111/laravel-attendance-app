<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AttendanceController extends Controller
{
    /**
     * 勤怠登録画面を表示する。
     */
    public function index(): view
    {
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', $today)
            ->first();

        $status = $attendance ? $attendance->status : '勤務外';

        return view('attendance.index', compact('attendance', 'status'));
    }

    /**
     * 出勤・退勤・休憩入・休憩戻の打刻処理を行う。
     */
    public function store(Request $request): RedirectResponse
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

    public function monthlyList(Request $request)
    {
        if ($request->input('month')) {
            $currentMonth = Carbon::parse($request->input('month') . '-01');
        } else {
            $currentMonth = Carbon::now();
        }

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', Auth::id())
            ->whereBetween('attendance_date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->orderBy('attendance_date')
            ->get();

        $attendanceByDate = $attendances->keyBy(function ($attendance) {
            return Carbon::parse($attendance->attendance_date)->toDateString();
        });

        $calendarDates = [];
        $date = $startOfMonth->copy();

        while ($date->lte($endOfMonth)) {
            $calendarDates[] = $date->copy();
            $date->addDay();
        }

        return view('attendance.list', compact(
            'currentMonth',
            'previousMonth',
            'nextMonth',
            'attendanceByDate',
            'calendarDates'
        ));
    }

    public function detail($id)
    {
        $attendance = Attendance::with('breakTimes')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $user = Auth::user();

        $pendingRequest = \App\Models\AttendanceRequest::where('attendance_id', $attendance->id)
            ->where('user_id', Auth::id())
            ->where('status', 0)
            ->latest()
            ->first();

        return view('attendance.detail', compact('attendance', 'user', 'pendingRequest'));
    }
}

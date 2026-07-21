<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AdminStaffController extends Controller
{
    public function index()
    {

        $users = User::all();

        return view('admin.staff.list', compact('users'));
    }

    public function show(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $selectedMonth = Carbon::parse(
            $request->input('month', now()->format('Y-m')) . '-01'
        );

        $startOfMonth = $selectedMonth->copy()->startOfMonth();
        $endOfMonth = $selectedMonth->copy()->endOfMonth();

        $attendancesByDate = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('attendance_date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse(
                    $attendance->attendance_date
                )->toDateString();
            });

        $dates = CarbonPeriod::create(
            $startOfMonth,
            $endOfMonth
        );

        return view(
            'admin.staff.attendance',
            compact(
                'user',
                'selectedMonth',
                'dates',
                'attendancesByDate'
            )
        );
    }
}

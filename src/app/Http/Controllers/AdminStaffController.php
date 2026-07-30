<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminStaffController extends Controller
{
    /**
     * スタッフ一覧画面を表示する。
     */
    public function index(): View
    {

        $users = User::all();

        return view('admin.staff.list', compact('users'));
    }
    /**
     * 対象スタッフの月次勤怠一覧画面を表示する。
     */
    public function show(Request $request, string $id): View
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

    /**
     * 対象スタッフの月次勤怠CSVを出力する。
     */
    public function exportCsv(
        Request $request,
        string $id
    ): StreamedResponse {
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

        $fileName = sprintf(
            '%s_%s_勤怠.csv',
            $user->name,
            $selectedMonth->format('Y-m')
        );

        return response()->streamDownload(
            function () use ($dates, $attendancesByDate) {
                $handle = fopen('php://output', 'w');

                fwrite($handle, "\xEF\xBB\xBF");

                fputcsv($handle, [
                    '日付',
                    '出勤',
                    '退勤',
                    '休憩',
                    '合計',
                ]);

                foreach ($dates as $date) {
                    $attendance = $attendancesByDate->get(
                        $date->toDateString()
                    );

                    $clockIn = '';
                    $clockOut = '';
                    $breakTimeText = '';
                    $workTimeText = '';

                    if ($attendance) {
                        if ($attendance->clock_in) {
                            $clockIn = Carbon::parse(
                                $attendance->clock_in
                            )->format('H:i');
                        }

                        if ($attendance->clock_out) {
                            $clockOut = Carbon::parse(
                                $attendance->clock_out
                            )->format('H:i');
                        }

                        $breakMinutes = $attendance->breakTimes
                            ->filter(function ($breakTime) {
                                return $breakTime->break_start
                                    && $breakTime->break_end;
                            })
                            ->sum(function ($breakTime) {
                                return Carbon::parse(
                                    $breakTime->break_start
                                )->diffInMinutes(
                                    Carbon::parse(
                                        $breakTime->break_end
                                    )
                                );
                            });

                        if ($breakMinutes > 0) {
                            $breakTimeText = sprintf(
                                '%d:%02d',
                                intdiv($breakMinutes, 60),
                                $breakMinutes % 60
                            );
                        }

                        if (
                            $attendance->clock_in
                            && $attendance->clock_out
                        ) {
                            $totalMinutes = Carbon::parse(
                                $attendance->clock_in
                            )->diffInMinutes(
                                Carbon::parse(
                                    $attendance->clock_out
                                )
                            );

                            $workMinutes = max(
                                $totalMinutes - $breakMinutes,
                                0
                            );

                            $workTimeText = sprintf(
                                '%d:%02d',
                                intdiv($workMinutes, 60),
                                $workMinutes % 60
                            );
                        }
                    }

                    fputcsv($handle, [
                        $date->format('Y/m/d'),
                        $clockIn,
                        $clockOut,
                        $breakTimeText,
                        $workTimeText,
                    ]);
                }

                fclose($handle);
            },
            $fileName,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]
        );
    }
}

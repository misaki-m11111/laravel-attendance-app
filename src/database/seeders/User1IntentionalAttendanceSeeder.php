<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class User1IntentionalAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $user = User::where(
                'email',
                'user1@example.com'
            )->firstOrFail();

            $currentMonth = Carbon::now()->startOfMonth();

            Attendance::where('user_id', $user->id)
                ->whereBetween('attendance_date', [
                    $currentMonth->copy()
                        ->subMonths(5)
                        ->startOfMonth()
                        ->toDateString(),
                    $currentMonth->copy()
                        ->endOfMonth()
                        ->toDateString(),
                ])
                ->delete();

            for ($monthsAgo = 5; $monthsAgo >= 1; $monthsAgo--) {
                $targetMonth = $currentMonth->copy()
                    ->subMonths($monthsAgo);

                $weekdays = $this->getWeekdays(
                    $targetMonth
                );

                if (count($weekdays) < 15) {
                    throw new RuntimeException(
                        $targetMonth->format('Y-m')
                            . 'の平日が15日未満です。'
                    );
                }

                foreach (array_slice($weekdays, 0, 15) as $date) {
                    $this->createAttendance(
                        $user->id,
                        $date,
                        '09:00:00',
                        '18:00:00'
                    );
                }
            }

            $currentWeekdays = $this->getWeekdays(
                $currentMonth
            );

            if (count($currentWeekdays) < 17) {
                throw new RuntimeException(
                    '当月の平日が17日未満です。'
                );
            }

            $workPatterns = [
                // 通常勤務：10件
                ['09:00:00', '18:00:00'],
                ['09:00:00', '18:00:00'],
                ['09:00:00', '18:00:00'],
                ['09:00:00', '18:00:00'],
                ['09:00:00', '18:00:00'],
                ['09:00:00', '18:00:00'],
                ['09:00:00', '18:00:00'],
                ['09:00:00', '18:00:00'],
                ['09:00:00', '18:00:00'],
                ['09:00:00', '18:00:00'],

                // 残業：3件
                ['09:00:00', '20:00:00'],
                ['09:00:00', '20:00:00'],
                ['09:00:00', '20:00:00'],

                // 遅刻：2件
                ['09:30:00', '18:00:00'],
                ['09:30:00', '18:00:00'],

                // 早退：1件
                ['09:00:00', '17:00:00'],

                // 長時間勤務：1件
                ['08:00:00', '21:00:00'],
            ];

            foreach ($workPatterns as $index => $workPattern) {
                $this->createAttendance(
                    $user->id,
                    $currentWeekdays[$index],
                    $workPattern[0],
                    $workPattern[1]
                );
            }
        });
    }

    private function getWeekdays(Carbon $targetMonth): array
    {
        $weekdays = [];

        $date = $targetMonth->copy()->startOfMonth();
        $endOfMonth = $targetMonth->copy()->endOfMonth();

        while ($date->lte($endOfMonth)) {
            if ($date->isWeekday()) {
                $weekdays[] = $date->copy();
            }

            $date->addDay();
        }

        return $weekdays;
    }

    private function createAttendance(
        int $userId,
        Carbon $date,
        string $clockIn,
        string $clockOut
    ): void {
        $attendance = Attendance::create([
            'user_id' => $userId,
            'attendance_date' => $date->toDateString(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'status' => '退勤済',
            'remarks' => null,
        ]);

        $attendance->breakTimes()->create([
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);
    }
}

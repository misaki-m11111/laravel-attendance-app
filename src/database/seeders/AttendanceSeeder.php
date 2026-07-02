<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        $now = now();

        $workPatterns = [
            ['clock_in' => '09:00:00', 'clock_out' => '18:00:00'],
            ['clock_in' => '09:05:00', 'clock_out' => '18:10:00'],
            ['clock_in' => '08:55:00', 'clock_out' => '17:55:00'],
            ['clock_in' => '09:10:00', 'clock_out' => '18:00:00'],
            ['clock_in' => '09:00:00', 'clock_out' => '18:15:00'],
        ];

        foreach ($users as $user) {
            $date = now()->startOfMonth();
            $createdCount = 0;

            while ($createdCount < 10) {
                if ($date->isWeekday()) {
                    $pattern = $workPatterns[$createdCount % count($workPatterns)];

                    DB::table('attendances')->updateOrInsert(
                        [
                            'user_id' => $user->id,
                            'attendance_date' => $date->toDateString(),
                        ],
                        [
                            'clock_in' => $pattern['clock_in'],
                            'clock_out' => $pattern['clock_out'],
                            'status' => '退勤済',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );

                    $createdCount++;
                }

                $date->addDay();
            }
        }
    }
}

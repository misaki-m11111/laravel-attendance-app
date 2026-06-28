<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BreakTimeSeeder extends Seeder
{
    public function run()
    {
        $attendances = Attendance::all();
        $now = now();

        foreach ($attendances as $attendance) {
            DB::table('break_times')->updateOrInsert(
                [
                    'attendance_id' => $attendance->id,
                ],
                [
                    'break_start' => '12:00:00',
                    'break_end' => '13:00:00',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
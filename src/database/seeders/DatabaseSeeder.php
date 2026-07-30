<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            AdminSeeder::class,
            AttendanceSeeder::class,
            BreakTimeSeeder::class,
            User1IntentionalAttendanceSeeder::class,
        ]);
    }
}

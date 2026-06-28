<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        DB::table('admins')->updateOrInsert(
            ['email' => 'user3@example.com'],
            [
                'name' => '管理者ユーザー',
                'password' => Hash::make('password'),
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}

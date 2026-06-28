<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{

    public function run()
    {
        $now = now();

        DB::table('users')->updateOrInsert(
            ['email' => 'user1@example.com'],
            [
                'name' => '一般ユーザー1',
                'password' => Hash::make('password'),
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'user2@example.com'],
            [
                'name' => '一般ユーザー2',
                'password' => Hash::make('password'),
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}

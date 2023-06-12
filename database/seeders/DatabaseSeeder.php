<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         \App\Models\Room::factory(10)->create();

         \App\Models\User::factory()->create([
             'name' => 'user 1',
             'email' => 'test1@user.com',
             'password' => Hash::make(1234567),
         ]);
        \App\Models\User::factory()->create([
            'name' => 'user 2',
            'email' => 'test2@user.com',
            'password' => Hash::make(1234567),
        ]);
    }
}

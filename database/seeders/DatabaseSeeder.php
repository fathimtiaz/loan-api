<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'id' => Str::uuid(),
            'name' => 'Test Customer User',
            'email' => 'test.customer@example.com',
            'password' => Hash::make('password'),
        ]);

        \App\Models\User::factory()->create([
            'id' => Str::uuid(),
            'name' => 'Test Admin User',
            'email' => 'test.admin@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}

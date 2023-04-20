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
        \App\Models\User::factory()->count(2)->sequence(
            [
                'id' => Str::uuid(),
                'name' => 'Test Customer User',
                'email' => 'test.customer@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Test Admin User',
                'email' => 'test.admin@example.com',
                'password' => Hash::make('password'),
            ]
        )->create();

        \App\Models\Role::factory()->count(2)->sequence(
            [
                'id' => Str::uuid(),
                'name' => 'customer',
            ],
            [
                'id' => Str::uuid(),
                'name' => 'admin',
            ]
        )->create();
        
        \App\Models\Permission::factory()->count(4)->sequence(
            [
                'id' => Str::uuid(),
                'name' => 'request-loan',
            ],
            [
                'id' => Str::uuid(),
                'name' => 'view-loan',
            ],
            [
                'id' => Str::uuid(),
                'name' => 'approve-loan',
            ],
            [
                'id' => Str::uuid(),
                'name' => 'repay-loan',
            ]
        )->create();

        $user_customer = \App\Models\User::where('email', 'test.customer@example.com')->first();
        $user_admin = \App\Models\User::where('email', 'test.admin@example.com')->first();

        $role_customer = \App\Models\Role::where('name', 'customer')->first();
        $role_admin = \App\Models\Role::where('name', 'admin')->first();

        $permission_request = \App\Models\Permission::where('name', 'request-loan')->first();
        $permission_view = \App\Models\Permission::where('name', 'view-loan')->first();
        $permission_repay = \App\Models\Permission::where('name', 'repay-loan')->first();
        $permission_approve = \App\Models\Permission::where('name', 'approve-loan')->first();

        $user_customer->roles()->save($role_customer);
        $user_admin->roles()->save($role_admin);

        $role_admin->permissions()->save($permission_view);
        $role_admin->permissions()->save($permission_approve);

        $role_customer->permissions()->save($permission_request);
        $role_customer->permissions()->save($permission_view);
        $role_customer->permissions()->save($permission_repay);
    }
}

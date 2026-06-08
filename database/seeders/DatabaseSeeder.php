<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionsSeeder::class,
        ]);

        $admin = User::query()->updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin User',
            'password' => 'password',
        ]);

        $coordinator = User::query()->updateOrCreate([
            'email' => 'coordinator@example.com',
        ], [
            'name' => 'Coordinator User',
            'password' => 'password',
        ]);

        $volunteer = User::query()->updateOrCreate([
            'email' => 'volunteer@example.com',
        ], [
            'name' => 'Volunteer User',
            'password' => 'password',
        ]);

        $admin->syncRoles(['admin']);
        $coordinator->syncRoles(['coordinator']);
        $volunteer->syncRoles(['volunteer']);
    }
}

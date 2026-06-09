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

        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '1234567890',
            'password' => bcrypt('password'),
        ]);

        $volunteerUser = User::factory()->create([
            'name' => 'Volunteer User',
            'email' => 'volunteer@example.com',
            'phone' => '0987654321',
            'password' => bcrypt('password'),
        ]);

        $coordinatorUser = User::factory()->create([
            'name' => 'Coordinator User',
            'email' => 'coordinator@example.com',
            'phone' => '1122334455',
            'password' => bcrypt('password'),
        ]);

        $adminUser->assignRole('admin');
        $volunteerUser->assignRole('volunteer');
        $coordinatorUser->assignRole('coordinator');
    }
}

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
            'address' => '123 Admin St',
            'post_code' => '12345',
            'city' => 'Admin City',
            'country' => 'Adminland',
            'password' => bcrypt('password'),
        ]);

        $volunteerUser = User::factory()->create([
            'name' => 'Volunteer User',
            'email' => 'volunteer@example.com',
            'phone' => '0987654321',
            'address' => '456 Volunteer Ave',
            'post_code' => '54321',
            'city' => 'Volunteer City',
            'country' => 'Volunteerland',
            'password' => bcrypt('password'),
        ]);

        $professionalUser = User::factory()->create([
            'name' => 'Professional User',
            'email' => 'professional@example.com',
            'phone' => '1122334455',
            'address' => '789 Professional Rd',
            'post_code' => '67890',
            'city' => 'Professional City',
            'country' => 'Professionland',
            'password' => bcrypt('password'),
        ]);

        $adminUser->assignRole('admin');
        $volunteerUser->assignRole('volunteer');
        $professionalUser->assignRole('professional');
    }
}

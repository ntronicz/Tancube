<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'super@edvube.com'],
            [
                'id' => Str::uuid(),
                'name' => 'Super Admin',
                'email' => 'super@edvube.com',
                'password' => bcrypt('super@123'),
                'role' => 'SUPER_ADMIN',
                'organization_id' => null,
            ]
        );

        $this->command->info('Super Admin created: super@edvube.com / super@123');
    }
}

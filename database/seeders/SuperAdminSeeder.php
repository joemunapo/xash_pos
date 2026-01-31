<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'superadmin@xashpos.com'],
            [
                'name' => 'Super Admin',
                'phone_number' => '+1234567890',
                'password' => Hash::make('SuperAdmin@123'),
                'role' => User::ROLE_SUPER_ADMIN,
                'is_super_admin' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Super admin created successfully!');
        $this->command->info('Email: superadmin@xashpos.com');
        $this->command->info('Password: SuperAdmin@123');
    }
}

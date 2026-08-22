<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
        $adminPassword = env('ADMIN_PASSWORD', 'password');
        $adminName = env('ADMIN_NAME', 'System Administrator');

        $user = User::where('email', $adminEmail)->first();

        if (!$user) {
            $user = new User([
                'name' => $adminName,
                'email' => $adminEmail,
                'password' => Hash::make($adminPassword),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
            $user->role = 'admin';
            $user->save();
        } else {
            $user->name = $adminName;
            $user->role = 'admin';
            $user->status = 'active';
            $user->password = Hash::make($adminPassword);
            $user->save();
        }

        if (!$user->admin) {
            Admin::create([
                'user_id' => $user->id,
                'position' => 'System Administrator',
                'department' => 'IT',
            ]);
        }
    }
}
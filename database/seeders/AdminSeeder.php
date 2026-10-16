<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /** Creates the first admin from SNL_ADMIN_* in .env. Never overwrites an existing account's password. */
    public function run(): void
    {
        $email = env('SNL_ADMIN_EMAIL', 'admin@snakesandliquor.test');

        User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => env('SNL_ADMIN_NAME', 'Admin'),
                'password' => env('SNL_ADMIN_PASSWORD', 'change-me-now'),
                'is_admin' => true,
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (User::query()->exists()) {
            return;
        }

        User::query()->updateOrCreate(
            ['email' => config('pm.seed.email')],
            [
                'name' => config('pm.seed.name'),
                'password' => config('pm.seed.password'),
            ],
        );
    }
}

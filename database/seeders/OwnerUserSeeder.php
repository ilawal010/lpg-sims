<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class OwnerUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = env('LPG_OWNER_NAME');
        $email = env('LPG_OWNER_EMAIL');
        $password = env('LPG_OWNER_PASSWORD');

        if (
            empty($name) ||
            empty($email) ||
            empty($password)
        ) {
            throw new RuntimeException(
                'Owner credentials are missing from the environment.'
            );
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Invalid owner email address.');
        }

        if (strlen($password) < 8) {
            throw new RuntimeException(
                'Owner password must contain at least 8 characters.'
            );
        }

        DB::transaction(function () use ($name, $email, $password) {

            $role = Role::where('name', 'owner')->firstOrFail();

            $user = User::where('email', $email)->first();

            if (! $user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                ]);
            }

            // Assign the owner role without modifying
            // an existing user's password.
            $user->role()->associate($role);
            $user->save();
        });

        $this->command?->info(
            'LPG-SIMS owner account initialized successfully.'
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@nepalmagar.org.np'],
            ['name' => 'Admin User', 'password' => bcrypt('password'), 'email_verified_at' => now()]
        );
        $admin->assignRole('admin');

        $editor = User::firstOrCreate(
            ['email' => 'editor@nepalmagar.org.np'],
            ['name' => 'Editor User', 'password' => bcrypt('password'), 'email_verified_at' => now()]
        );
        $editor->assignRole('editor');

        $user = User::firstOrCreate(
            ['email' => 'user@nepalmagar.org.np'],
            ['name' => 'Sample Member', 'password' => bcrypt('password'), 'email_verified_at' => now()]
        );
        $user->assignRole('user');

        User::factory(15)->create()->each(function (User $u) {
            $u->assignRole('user');
        });
    }
}

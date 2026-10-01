<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // One permission per dashboard section and action, e.g. "news.create" (see config/admin.php).
        Permissions::sync();

        // Built-in roles. Admin passes every permission check (see AppServiceProvider),
        // "user" is given to everyone who registers. More roles are made in the dashboard.
        Role::firstOrCreate(['name' => Permissions::ADMIN_ROLE, 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => Permissions::USER_ROLE, 'guard_name' => 'web']);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage-users',
            'manage-settings',
            'manage-donations',
            'manage-content',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions);

        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // The site only has two roles: admin and user. Fold any legacy "editor" accounts into "user".
        if ($editor = Role::where('name', 'editor')->where('guard_name', 'web')->first()) {
            User::role('editor')->get()->each(fn (User $u) => $u->syncRoles('user'));
            $editor->delete();
        }
    }
}

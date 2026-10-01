<?php

use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /** Replace the old catch-all permissions with one per dashboard section and action. */
    public function up(): void
    {
        Permission::whereIn('name', ['manage-users', 'manage-settings', 'manage-donations', 'manage-content'])->delete();

        Permissions::sync();

        Role::firstOrCreate(['name' => Permissions::ADMIN_ROLE, 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => Permissions::USER_ROLE, 'guard_name' => 'web']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::whereIn('name', Permissions::names())->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};

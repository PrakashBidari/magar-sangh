<?php

namespace App\Models;

use App\Support\Permissions;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /** Built-in roles cannot be renamed or deleted; "admin" cannot be changed at all. */
    public function isSystem(): bool
    {
        return Permissions::isSystemRole((string) $this->name);
    }

    public function isAdmin(): bool
    {
        return $this->name === Permissions::ADMIN_ROLE;
    }

    public function getLabelAttribute(): string
    {
        return Str::headline($this->name);
    }

    /** Dashboard select options: name => label, admin first then the default user role. */
    public static function options(): array
    {
        return static::query()->where('guard_name', 'web')->get()
            ->sortBy(fn (self $role) => [$role->isAdmin() ? 0 : ($role->isSystem() ? 1 : 2), $role->name])
            ->mapWithKeys(fn (self $role) => [$role->name => $role->label])
            ->all();
    }
}

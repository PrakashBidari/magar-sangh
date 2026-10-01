<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Every dashboard permission, built from config/admin.php.
 * A permission is named "{section}.{action}", e.g. "news.create" or "membership-applications.approve".
 */
class Permissions
{
    public const ADMIN_ROLE = 'admin';

    public const USER_ROLE = 'user';

    public const ACTIONS = [
        'view' => 'View',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'approve' => 'Approve',
        'manage' => 'Manage',
    ];

    /** Sections keyed by name: ['label', 'icon', 'group', 'actions' => [...]]. */
    public static function sections(): Collection
    {
        $resources = collect(config('admin.resources'))->map(fn (array $r) => [
            'label' => $r['label'],
            'icon' => $r['icon'],
            'group' => $r['group'],
            'actions' => array_merge(
                ($r['readonly'] ?? false) ? ['view', 'delete'] : ['view', 'create', 'edit', 'delete'],
                ($r['approvable'] ?? false) ? ['approve'] : [],
            ),
        ]);

        return collect(config('admin.permission_sections'))->merge($resources);
    }

    /** Sections grouped under the sidebar group labels, in sidebar order. */
    public static function grouped(): Collection
    {
        $labels = ['general' => 'General'] + config('admin.groups');
        $sections = static::sections();

        return collect($labels)
            ->map(fn ($label, $group) => [
                'label' => $label,
                'sections' => $sections->filter(fn ($s) => $s['group'] === $group),
            ])
            ->filter(fn ($g) => $g['sections']->isNotEmpty());
    }

    /** @return list<string> */
    public static function names(): array
    {
        return static::sections()
            ->flatMap(fn ($section, $key) => collect($section['actions'])->map(fn ($action) => "{$key}.{$action}"))
            ->values()
            ->all();
    }

    /** Create any permission that is missing from the database (safe to call often). */
    public static function sync(): void
    {
        $existing = Permission::where('guard_name', 'web')->pluck('name')->all();
        $missing = array_diff(static::names(), $existing);

        foreach ($missing as $name) {
            Permission::create(['name' => $name, 'guard_name' => 'web']);
        }

        if ($missing) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    public static function isSystemRole(string $name): bool
    {
        return in_array($name, [static::ADMIN_ROLE, static::USER_ROLE], true);
    }
}

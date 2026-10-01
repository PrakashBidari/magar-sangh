<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Roles and what each role may do in the dashboard.
 * "admin" always has every permission; "user" (members) can be given permissions but not renamed or deleted.
 */
class RoleController extends Controller
{
    public function index(): View
    {
        Permissions::sync();

        return view('dashboard.roles.index', [
            'roles' => $this->roles()->loadCount(['users', 'permissions']),
            'total' => count(Permissions::names()),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Role);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('dashboard.roles.index')->with('dashboard-status', "Role \"{$role->label}\" created successfully.");
    }

    public function edit(Role $role): View|RedirectResponse
    {
        if ($role->isAdmin()) {
            return redirect()->route('dashboard.roles.index')->with('dashboard-error', 'The admin role always has every permission and cannot be changed.');
        }

        return $this->form($role);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->isAdmin(), 403);

        $data = $this->validated($request, $role);

        if (! $role->isSystem()) {
            $role->update(['name' => $data['name']]);
        }

        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('dashboard.roles.index')->with('dashboard-status', "Role \"{$role->label}\" updated successfully.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        $message = match (true) {
            $role->isSystem() => 'Built-in roles cannot be deleted.',
            $role->users()->exists() => 'This role is still given to users. Give them another role first.',
            default => null,
        };

        if ($message) {
            return redirect()->route('dashboard.roles.index')->with('dashboard-error', $message);
        }

        $role->delete();

        return redirect()->route('dashboard.roles.index')->with('dashboard-status', 'Role deleted successfully.');
    }

    /** Every role (except admin) against every permission, saved in one go. */
    public function matrix(): View
    {
        Permissions::sync();

        return view('dashboard.roles.matrix', [
            'roles' => $this->roles()->reject->isAdmin()->values()->load('permissions'),
            'groups' => Permissions::grouped(),
        ]);
    }

    public function updateMatrix(Request $request): RedirectResponse
    {
        $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.*' => ['string', Rule::in(Permissions::names())],
        ]);

        foreach ($this->roles()->reject->isAdmin() as $role) {
            $role->syncPermissions($request->input("permissions.{$role->id}", []));
        }

        return redirect()->route('dashboard.permissions.index')->with('dashboard-status', 'Permissions saved successfully.');
    }

    // ------------------------------------------------------------------ internals

    private function roles()
    {
        return Role::where('guard_name', 'web')->get()
            ->sortBy(fn (Role $role) => [$role->isAdmin() ? 0 : ($role->isSystem() ? 1 : 2), $role->name])
            ->values();
    }

    private function form(Role $role): View
    {
        Permissions::sync();

        return view('dashboard.roles.form', [
            'role' => $role,
            'groups' => Permissions::grouped(),
            'granted' => old('permissions', $role->exists ? $role->permissions->pluck('name')->all() : []),
        ]);
    }

    private function validated(Request $request, ?Role $role): array
    {
        $request->merge(['name' => trim((string) $request->input('name', $role?->name))]);

        return $request->validate([
            'name' => $role?->isSystem() ? ['nullable'] : [
                'required', 'string', 'max:100',
                Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role?->id),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(Permissions::names())],
        ], [], ['name' => 'role name']);
    }
}

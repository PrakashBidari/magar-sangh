<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Users get exactly one role; roles and their permissions are managed under Roles & Permissions. */
class UserController extends ResourceController
{
    protected function query(): Builder
    {
        return parent::query()->with('roles');
    }

    protected function rules(?Model $model): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($model?->getKey())],
            'role' => ['required', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'password' => [$model ? 'nullable' : 'required', 'string', 'min:8'],
        ];
    }

    protected function formValues(?Model $model): array
    {
        return [
            'name' => $model?->name,
            'email' => $model?->email,
            'role' => $model?->getRoleNames()->first() ?? 'user',
            'password' => null,
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        if ($error = $this->adminRoleError($request, null)) {
            return back()->withInput()->withErrors(['role' => $error]);
        }

        return parent::store($request);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $model = $this->find($id);

        if ($model->is($request->user()) && $model->hasRole(Permissions::ADMIN_ROLE) && $request->input('role') !== Permissions::ADMIN_ROLE) {
            return back()->withInput()->withErrors(['role' => 'You cannot remove your own admin role.']);
        }

        if ($error = $this->adminRoleError($request, $model)) {
            return back()->withInput()->withErrors(['role' => $error]);
        }

        return parent::update($request, $id);
    }

    /** Only admins may give the admin role, or change an admin's account. */
    private function adminRoleError(Request $request, ?Model $model): ?string
    {
        if ($request->user()->hasRole(Permissions::ADMIN_ROLE)) {
            return null;
        }

        if ($request->input('role') === Permissions::ADMIN_ROLE || $model?->hasRole(Permissions::ADMIN_ROLE)) {
            return 'Only an admin can give the admin role or change an admin account.';
        }

        return null;
    }

    protected function persist(Request $request, ?Model $model): Model
    {
        $data = $request->only('name', 'email');

        if ($request->filled('password')) {
            $data['password'] = $request->input('password');
        }

        if ($model) {
            $model->update($data);
        } else {
            $model = User::create($data + ['email_verified_at' => now()]);
        }

        $model->syncRoles($request->input('role'));

        return $model;
    }

    protected function deleteBlockedReason(Model $model): ?string
    {
        return match (true) {
            $model->is(request()->user()) => 'You cannot delete your own account.',
            $model->hasRole(Permissions::ADMIN_ROLE) && ! request()->user()->hasRole(Permissions::ADMIN_ROLE) => 'Only an admin can delete an admin account.',
            default => null,
        };
    }
}

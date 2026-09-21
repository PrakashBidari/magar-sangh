<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Users have exactly two roles: admin and user. */
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
            'role' => ['required', Rule::in(['admin', 'user'])],
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

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $model = $this->find($id);

        if ($model->is($request->user()) && $request->input('role') !== 'admin') {
            return back()->withInput()->withErrors(['role' => 'You cannot remove your own admin role.']);
        }

        return parent::update($request, $id);
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
        return $model->is(request()->user()) ? 'You cannot delete your own account.' : null;
    }
}

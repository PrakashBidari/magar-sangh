<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $userId): void
    {
        if ($userId === Auth::id()) {
            session()->flash('dashboard-error', 'You cannot delete your own account.');
            return;
        }

        User::findOrFail($userId)->delete();

        session()->flash('dashboard-status', 'User deleted successfully.');
    }

    public function getUsersProperty()
    {
        return User::query()
            ->with('roles')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->orderByDesc('id')
            ->paginate(10);
    }
};
?>

<div>
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <h1 class="text-xl font-bold text-navy">Users</h1>
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Search name or email" class="w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon sm:w-64">
    </div>

    @if (session('dashboard-status'))
    <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('dashboard-status') }}</div>
    @endif
    @if (session('dashboard-error'))
    <div class="mt-4 rounded-md bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ session('dashboard-error') }}</div>
    @endif

    <div class="mt-6 overflow-x-auto rounded-lg bg-white shadow-md">
        <table class="w-full text-left text-sm">
            <thead class="bg-navy text-white">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Joined</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($this->users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-semibold text-navy">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded-full bg-gold-100 px-2 py-1 text-xs font-semibold text-gold-700">
                            {{ $user->roles->pluck('name')->join(', ') ?: '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $user->created_at->format('d M, Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('dashboard.users.show', $user) }}" class="mr-3 text-xs font-semibold text-navy hover:underline">View</a>
                        <button type="button" wire:click="delete({{ $user->id }})" wire:confirm="Are you sure you want to delete this user?" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $this->users->links('partials.pagination-livewire') }}
    </div>
</div>

<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $userId;

    public function mount(User $user): void
    {
        $this->userId = $user->id;
    }

    public function getUserProperty(): User
    {
        return User::with('roles')->findOrFail($this->userId);
    }

    public function delete(): void
    {
        if ($this->userId === Auth::id()) {
            session()->flash('dashboard-error', 'You cannot delete your own account.');
            return;
        }

        $this->user->delete();

        session()->flash('dashboard-status', 'User deleted successfully.');

        $this->redirectRoute('dashboard.users');
    }
};
?>

<div class="max-w-2xl">
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-bold text-navy">User Details</h1>
        <a href="{{ route('dashboard.users') }}" class="text-sm font-semibold text-maroon hover:underline">← Back to Users</a>
    </div>

    @if (session('dashboard-error'))
    <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ session('dashboard-error') }}</div>
    @endif

    <div class="card">
        <div class="flex items-center gap-4">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($this->user->name) }}&background=001F5B&color=fff&size=128" alt="{{ $this->user->name }}" class="h-20 w-20 rounded-full object-cover">
            <div>
                <div class="text-lg font-bold text-navy">{{ $this->user->name }}</div>
                <div class="text-sm text-gray-500">{{ $this->user->email }}</div>
            </div>
        </div>

        <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase text-gray-400">Role</dt>
                <dd class="mt-1 text-sm font-semibold text-navy">{{ $this->user->roles->pluck('name')->join(', ') ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-gray-400">Joined</dt>
                <dd class="mt-1 text-sm text-gray-700">{{ $this->user->created_at->format('d M, Y') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-gray-400">Email Verified</dt>
                <dd class="mt-1 text-sm text-gray-700">{{ $this->user->email_verified_at ? $this->user->email_verified_at->format('d M, Y') : 'Not verified' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-gray-400">Last Updated</dt>
                <dd class="mt-1 text-sm text-gray-700">{{ $this->user->updated_at->format('d M, Y') }}</dd>
            </div>
        </dl>

        <div class="mt-6 border-t pt-4">
            <button type="button" wire:click="delete" wire:confirm="Are you sure you want to delete this user?" class="rounded-md bg-red-600 px-4 py-2 text-xs font-semibold text-white hover:bg-red-700">Delete User</button>
        </div>
    </div>
</div>

<?php

use App\Models\Donation;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new class extends Component
{
    use WithFileUploads;
    use WithPagination;

    public bool $showForm = false;

    public string $donor_name = '';
    public $donor_image;
    public string $amount = '';
    public string $address = '';
    public string $donate_date = '';

    protected $paginationTheme = 'tailwind';

    protected function rules(): array
    {
        return [
            'donor_name' => ['required', 'string', 'max:255'],
            'donor_image' => ['nullable', 'image', 'max:2048'],
            'amount' => ['required', 'numeric', 'min:1'],
            'address' => ['nullable', 'string', 'max:255'],
            'donate_date' => ['required', 'date'],
        ];
    }

    public function openForm(): void
    {
        $this->reset('donor_name', 'donor_image', 'amount', 'address', 'donate_date');
        $this->donate_date = now()->format('Y-m-d');
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
    }

    public function save(): void
    {
        $data = $this->validate();

        $data['donor_image_url'] = $this->donor_image
            ? Storage::disk('public')->url($this->donor_image->store('donations', 'public'))
            : null;

        unset($data['donor_image']);

        Donation::create($data);

        $this->showForm = false;
        session()->flash('dashboard-status', 'Donation added successfully.');
    }

    public function delete(int $donationId): void
    {
        Donation::findOrFail($donationId)->delete();
        session()->flash('dashboard-status', 'Donation removed.');
    }

    public function getDonationsProperty()
    {
        return Donation::orderByDesc('donate_date')->paginate(10);
    }
};
?>

<div>
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-navy">Donation List</h1>
        <button type="button" wire:click="openForm" class="btn-maroon">+ Add Donation</button>
    </div>

    @if (session('dashboard-status'))
    <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('dashboard-status') }}</div>
    @endif

    @if ($showForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-navy">Add Donation</h2>
                <button type="button" wire:click="closeForm" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="text-sm font-semibold text-gray-700">Donor Image</label>
                    <input type="file" wire:model="donor_image" accept="image/*" class="mt-1 w-full text-sm">
                    @error('donor_image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    @if ($donor_image)
                    <img src="{{ $donor_image->temporaryUrl() }}" class="mt-2 h-16 w-16 rounded-full object-cover">
                    @endif
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Donor Name</label>
                    <input type="text" wire:model="donor_name" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('donor_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Amount (Rs.)</label>
                    <input type="number" step="0.01" wire:model="amount" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Address</label>
                    <input type="text" wire:model="address" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Donate Date</label>
                    <input type="date" wire:model="donate_date" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('donate_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="closeForm" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100">Cancel</button>
                    <button type="submit" wire:loading.attr="disabled" class="btn-maroon">Save Donation</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="mt-6 overflow-x-auto rounded-lg bg-white shadow-md">
        <table class="w-full text-left text-sm">
            <thead class="bg-navy text-white">
                <tr>
                    <th class="px-4 py-3">Image</th>
                    <th class="px-4 py-3">Donor Name</th>
                    <th class="px-4 py-3">Address</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($this->donations as $donor)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3"><img src="{{ $donor->displayImage() }}" alt="{{ $donor->donor_name }}" class="h-10 w-10 rounded-full object-cover"></td>
                    <td class="px-4 py-3 font-semibold text-navy">{{ $donor->donor_name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $donor->address }}</td>
                    <td class="px-4 py-3 font-bold text-maroon">Rs. {{ number_format($donor->amount, 2) }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $donor->donate_date->format('d M, Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <button type="button" wire:click="delete({{ $donor->id }})" wire:confirm="Delete this donation record?" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No donations recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $this->donations->links('partials.pagination-livewire') }}
    </div>
</div>

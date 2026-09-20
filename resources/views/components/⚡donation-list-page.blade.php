<?php

use App\Models\Donation;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    protected $paginationTheme = 'tailwind';

    public function updating($property): void
    {
        if (in_array($property, ['search', 'dateFrom', 'dateTo'])) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'dateFrom', 'dateTo');
        $this->resetPage();
    }

    public function getTopDonorsProperty()
    {
        return Donation::orderByDesc('amount')->take(5)->get();
    }

    public function getDonationsProperty()
    {
        return Donation::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('donor_name', 'like', "%{$this->search}%")
                    ->orWhere('address', 'like', "%{$this->search}%");
            }))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('donate_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('donate_date', '<=', $this->dateTo))
            ->orderByDesc('amount')
            ->paginate(10);
    }
};
?>

<div class="space-y-12">
    {{-- TOP 5 DONORS --}}
    <div>
        <h2 class="text-lg font-bold text-navy">Top Donors</h2>
        <div class="mt-4 overflow-x-auto rounded-lg bg-white shadow-md">
            <table class="w-full text-left text-sm">
                <thead class="bg-navy text-white">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Image</th>
                        <th class="px-4 py-3">Donor Name</th>
                        <th class="px-4 py-3">Address</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($this->topDonors as $i => $donor)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-bold text-gold-600">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <img src="{{ $donor->displayImage() }}" alt="{{ $donor->donor_name }}" class="glightbox-img h-10 w-10 rounded-full object-cover">
                        </td>
                        <td class="px-4 py-3 font-semibold text-navy">{{ $donor->donor_name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $donor->address }}</td>
                        <td class="px-4 py-3 font-bold text-maroon">Rs. {{ number_format($donor->amount, 2) }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $donor->donate_date->format('d M, Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No donations recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- FULL LIST --}}
    <div>
        <h2 class="text-lg font-bold text-navy">All Donors</h2>

        <div class="mt-4 rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_auto]">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Search</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/></svg>
                        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Search name or address" class="w-full rounded-full border-gray-200 bg-gray-50 py-2.5 pl-9 pr-4 text-sm shadow-inner transition focus:border-maroon focus:bg-white focus:ring-2 focus:ring-maroon/20">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">From Date</label>
                    <input type="date" wire:model.live="dateFrom" class="w-full rounded-full border-gray-200 bg-gray-50 py-2.5 px-4 text-sm shadow-inner transition focus:border-maroon focus:bg-white focus:ring-2 focus:ring-maroon/20">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">To Date</label>
                    <input type="date" wire:model.live="dateTo" class="w-full rounded-full border-gray-200 bg-gray-50 py-2.5 px-4 text-sm shadow-inner transition focus:border-maroon focus:bg-white focus:ring-2 focus:ring-maroon/20">
                </div>
                <div class="flex items-end">
                    <button type="button" wire:click="resetFilters" class="flex w-full items-center justify-center gap-1.5 rounded-full border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-600 transition hover:border-maroon hover:bg-maroon hover:text-white lg:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <div class="relative mt-4 overflow-x-auto rounded-lg bg-white shadow-md">
            <div wire:loading class="absolute inset-0 z-10 flex items-center justify-center bg-white/60 text-sm font-semibold text-maroon">Loading...</div>
            <table class="w-full text-left text-sm">
                <thead class="bg-navy text-white">
                    <tr>
                        <th class="px-4 py-3">Image</th>
                        <th class="px-4 py-3">Donor Name</th>
                        <th class="px-4 py-3">Address</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($this->donations as $donor)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <img src="{{ $donor->displayImage() }}" alt="{{ $donor->donor_name }}" class="glightbox-img h-10 w-10 rounded-full object-cover">
                        </td>
                        <td class="px-4 py-3 font-semibold text-navy">{{ $donor->donor_name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $donor->address }}</td>
                        <td class="px-4 py-3 font-bold text-maroon">Rs. {{ number_format($donor->amount, 2) }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $donor->donate_date->format('d M, Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No matching donations found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $this->donations->links('partials.pagination-livewire') }}
        </div>
    </div>
</div>

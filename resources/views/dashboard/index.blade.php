<x-layouts.dashboard title="Overview">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card">
            <div class="text-xs font-semibold uppercase text-gray-400">Total Users</div>
            <div class="mt-2 text-3xl font-extrabold text-navy">{{ number_format($stats['users']) }}</div>
        </div>
        <div class="card">
            <div class="text-xs font-semibold uppercase text-gray-400">Total Donations</div>
            <div class="mt-2 text-3xl font-extrabold text-navy">{{ number_format($stats['donations']) }}</div>
        </div>
        <div class="card">
            <div class="text-xs font-semibold uppercase text-gray-400">Total Donation Amount</div>
            <div class="mt-2 text-3xl font-extrabold text-maroon">Rs. {{ number_format($stats['donation_total'], 2) }}</div>
        </div>
        <div class="card">
            <div class="text-xs font-semibold uppercase text-gray-400">Contact Messages</div>
            <div class="mt-2 text-3xl font-extrabold text-navy">{{ number_format($stats['messages']) }}</div>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2">
        @role('admin')
        <a href="{{ route('dashboard.donations') }}" class="card flex items-center justify-between hover:shadow-lg">
            <span class="font-semibold text-navy">Manage Donation List</span>
            <span>→</span>
        </a>
        <a href="{{ route('dashboard.settings') }}" class="card flex items-center justify-between hover:shadow-lg">
            <span class="font-semibold text-navy">Site Settings</span>
            <span>→</span>
        </a>
        @else
        <div class="card sm:col-span-2">
            <p class="text-sm text-gray-500">No additional modules are assigned to your account yet. More editor tools are coming soon.</p>
        </div>
        @endrole
    </div>
</x-layouts.dashboard>

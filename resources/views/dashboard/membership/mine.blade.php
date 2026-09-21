@php
    $banner = match (true) {
        $current === null => null,
        $current->isPending() => ['tone' => 'border-amber-300 bg-amber-50', 'icon' => '⏳', 'title' => 'Your application is under review', 'text' => 'We received your application on '.$current->applied_at->format('d M, Y').'. You will see your membership here once it is approved.'],
        $current->isRejected() => ['tone' => 'border-red-300 bg-red-50', 'icon' => '⛔', 'title' => 'Your application was not approved', 'text' => $current->rejection_reason ?: 'Please contact the association for details, or apply again with corrected information.'],
        $current->isExpired() => ['tone' => 'border-gray-300 bg-gray-50', 'icon' => '⌛', 'title' => 'Your membership expired on '.$current->expires_at->format('d M, Y'), 'text' => 'Apply again to renew your membership.'],
        default => ['tone' => 'border-green-300 bg-green-50', 'icon' => '✅', 'title' => 'You are a member of the Nepal Magar Association', 'text' => null],
    };
@endphp
<x-layouts.dashboard title="My Membership">
    <div class="mx-auto max-w-4xl">
        <h2 class="text-xl font-bold text-navy">🎫 My Membership</h2>

        @if (session('dashboard-status'))
        <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('dashboard-status') }}</div>
        @endif
        @if (session('dashboard-error'))
        <div class="mt-4 rounded-md bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ session('dashboard-error') }}</div>
        @endif

        @if (! $current)
        <div class="card mt-4 text-center">
            <div class="text-5xl">🎫</div>
            <h3 class="mt-3 text-lg font-bold text-navy">Become a member</h3>
            <p class="mx-auto mt-1 max-w-md text-sm text-gray-600">Join the Nepal Magar Association and be part of the unity, culture and rights of the Magar community.</p>
            <a href="{{ route('dashboard.my-membership.create') }}" class="btn-maroon mt-5 justify-center">Apply for membership</a>
        </div>
        @else
        <div class="mt-4 rounded-lg border-l-4 p-4 sm:p-5 {{ $banner['tone'] }}">
            <div class="flex items-start gap-3">
                <span class="text-2xl">{{ $banner['icon'] }}</span>
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold text-gray-800">{{ $banner['title'] }}</h3>
                    @if ($banner['text'])<p class="mt-1 text-sm text-gray-600">{{ $banner['text'] }}</p>@endif
                </div>
            </div>
        </div>

        <div class="card mt-4 flex flex-col gap-5 sm:flex-row">
            <img src="{{ $current->photo_url }}" alt="" class="h-32 w-28 shrink-0 self-start rounded-md border border-gray-200 object-cover">
            <div class="min-w-0 flex-1">
                <h3 class="text-lg font-extrabold text-navy">{{ $current->displayName() }}</h3>
                <p class="text-sm text-gray-500">{{ $current->type->name_en }} <span class="np">· {{ $current->type->name_np }}</span></p>
                <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-3 text-sm sm:grid-cols-3">
                    <div><dt class="text-xs font-semibold uppercase text-gray-400">Status</dt><dd><span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $current->statusClasses() }}">{{ $current->statusLabel() }}</span></dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-gray-400">Member No.</dt><dd class="font-mono">{{ $current->membership_number ?: '—' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-gray-400">Applied</dt><dd>{{ $current->applied_at->format('d M, Y') }}</dd></div>
                    @if ($current->isApproved())
                    <div><dt class="text-xs font-semibold uppercase text-gray-400">Member since</dt><dd>{{ $current->approved_at->format('d M, Y') }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-gray-400">Valid till</dt><dd>{{ $current->expires_at?->format('d M, Y') ?? 'Lifetime' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-gray-400">Plan</dt><dd>{{ $current->type->durationLabel() }}</dd></div>
                    @endif
                </dl>
                <div class="mt-5 flex flex-wrap gap-2">
                    @if ($current->isApproved())
                    <a href="{{ route('dashboard.membership.card', $current) }}" class="btn-maroon justify-center !px-5 !py-2.5 text-sm">🎫 View &amp; download ID card</a>
                    @endif
                    @if ($canApply)
                    <a href="{{ route('dashboard.my-membership.create') }}" class="btn-maroon justify-center !px-5 !py-2.5 text-sm">{{ $current->isExpired() ? 'Renew membership' : 'Apply again' }}</a>
                    @endif
                </div>
            </div>
        </div>

        @if ($memberships->count() > 1)
        <div class="card mt-4">
            <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-gray-500">Application history</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-gray-400">
                        <tr><th class="py-2 pr-4">Applied</th><th class="py-2 pr-4">Type</th><th class="py-2 pr-4">Member No.</th><th class="py-2">Status</th></tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($memberships as $m)
                        <tr>
                            <td class="whitespace-nowrap py-2 pr-4">{{ $m->applied_at->format('d M, Y') }}</td>
                            <td class="py-2 pr-4">{{ $m->type->name_en }}</td>
                            <td class="py-2 pr-4 font-mono text-xs">{{ $m->membership_number ?: '—' }}</td>
                            <td class="py-2"><span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $m->statusClasses() }}">{{ $m->statusLabel() }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        @endif
    </div>
</x-layouts.dashboard>

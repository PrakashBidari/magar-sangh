@php
    $listRoute = 'dashboard.membership.'.$membership->status;
    $details = [
        'Full name' => $membership->full_name,
        'Surname' => $membership->surname,
        'Date of birth' => $membership->date_of_birth->format('d M, Y'),
        'Mobile' => $membership->mobile,
        'Email' => $membership->email,
        'Occupation / Business' => $membership->occupation,
        'Province' => $membership->province,
        'District' => $membership->district,
        'Municipality / Rural municipality' => $membership->municipality,
        'Ward no.' => $membership->ward_no,
        'Permanent address' => $membership->permanent_address,
        'Current address' => $membership->current_address,
    ];
@endphp
<x-layouts.dashboard :title="$membership->displayName()">
    <div class="mx-auto max-w-5xl">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl font-bold text-navy">Membership Application</h2>
            <a href="{{ route($listRoute) }}" class="text-sm font-semibold text-maroon hover:underline">← Back to list</a>
        </div>

        @if (session('dashboard-status'))
        <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('dashboard-status') }}</div>
        @endif
        @if (session('dashboard-error'))
        <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ session('dashboard-error') }}</div>
        @endif

        {{-- Summary + actions --}}
        <div class="card flex flex-col gap-5 sm:flex-row sm:items-center">
            <img src="{{ $membership->photo_url }}" alt="" class="h-28 w-24 shrink-0 self-start rounded-md border border-gray-200 object-cover sm:self-center">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-lg font-extrabold text-navy">{{ $membership->displayName() }}</h3>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $membership->statusClasses() }}">{{ $membership->statusLabel() }}</span>
                </div>
                <div class="mt-1 text-sm text-gray-600">{{ $membership->type->name_en }} <span class="np text-gray-400">· {{ $membership->type->name_np }}</span></div>
                <dl class="mt-3 grid grid-cols-2 gap-x-6 gap-y-2 text-sm sm:grid-cols-4">
                    <div><dt class="text-xs font-semibold uppercase text-gray-400">Member No.</dt><dd class="font-mono">{{ $membership->membership_number ?: '—' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-gray-400">Applied</dt><dd>{{ $membership->applied_at->format('d M, Y') }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-gray-400">Approved</dt><dd>{{ $membership->approved_at?->format('d M, Y') ?? '—' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-gray-400">Valid till</dt><dd>{{ $membership->isApproved() ? ($membership->expires_at?->format('d M, Y') ?? 'Lifetime') : '—' }}</dd></div>
                </dl>
                @if ($membership->isRejected() && $membership->rejection_reason)
                <p class="mt-3 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700"><strong>Reason:</strong> {{ $membership->rejection_reason }}</p>
                @endif
                @if ($membership->reviewer)
                <p class="mt-2 text-xs text-gray-400">Reviewed by {{ $membership->reviewer->name }}</p>
                @endif
            </div>

            <div class="flex flex-wrap gap-2 sm:w-44 sm:flex-col">
                @unless ($membership->isApproved())
                <form method="POST" action="{{ route('dashboard.membership.approve', $membership) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full rounded-md bg-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-700">✔ Approve</button>
                </form>
                @endunless
                @unless ($membership->isRejected())
                <button type="button" data-reject="{{ route('dashboard.membership.reject', $membership) }}" data-name="{{ $membership->displayName() }}" class="flex-1 rounded-md bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-600">✖ Disapprove</button>
                @endunless
                @if ($membership->isApproved())
                <a href="{{ route('dashboard.membership.card', $membership) }}" class="flex-1 rounded-md bg-navy px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-navy-600">🎫 ID Card</a>
                @endif
                <a href="{{ route('dashboard.membership.edit', $membership) }}" class="flex-1 rounded-md border border-gray-300 px-4 py-2.5 text-center text-sm font-semibold text-navy hover:bg-gray-50">Edit</a>
                <form method="POST" action="{{ route('dashboard.membership.destroy', $membership) }}" class="flex-1" onsubmit="return confirm('Delete this application permanently? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full rounded-md border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">Delete</button>
                </form>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-3">
            <div class="card lg:col-span-2">
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-500">Applicant details</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    @foreach ($details as $label => $value)
                    <div class="min-w-0 {{ in_array($label, ['Permanent address', 'Current address']) ? 'sm:col-span-2' : '' }}">
                        <dt class="text-xs font-semibold uppercase text-gray-400">{{ $label }}</dt>
                        <dd class="mt-0.5 break-words text-sm text-gray-800">{{ $value ?: '—' }}</dd>
                    </div>
                    @endforeach
                    <div class="min-w-0 sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase text-gray-400">Account</dt>
                        <dd class="mt-0.5 text-sm text-gray-800">{{ $membership->user->name }} ({{ $membership->user->email }})</dd>
                    </div>
                </dl>
            </div>

            <div class="space-y-5">
                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-gray-500">Payment voucher</h3>
                    @if ($membership->voucher_path)
                    <a href="{{ route('dashboard.membership.voucher', $membership) }}" target="_blank" rel="noopener" class="block">
                        <img src="{{ route('dashboard.membership.voucher', $membership) }}" alt="Payment voucher" class="max-h-72 w-full rounded-md border border-gray-200 bg-gray-50 object-contain">
                        <span class="mt-2 inline-block text-xs font-semibold text-navy hover:underline">Open full size ↗</span>
                    </a>
                    @else
                    <p class="text-sm text-gray-400">No voucher uploaded{{ $membership->type->requiresPayment() ? ' (this membership has a fee!)' : '' }}.</p>
                    @endif
                    <p class="mt-3 text-xs text-gray-400">Fee: {{ $membership->type->fee_label }}</p>
                </div>

                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-gray-500">Signature</h3>
                    @if ($membership->signature_url)
                    <img src="{{ $membership->signature_url }}" alt="Signature" class="max-h-28 rounded-md border border-gray-200 bg-white object-contain p-2">
                    @else
                    <p class="text-sm text-gray-400">Not provided.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('dashboard.membership._reject-dialog')
</x-layouts.dashboard>

@php
    $details = [
        'Applicant' => $sifaris->applicant_name,
        'Father / husband' => $sifaris->parent_name.' ('.$sifaris->parent_relation.')',
        'Grandfather / father-in-law' => $sifaris->grandparent_name.' ('.$sifaris->grandparent_relation.')',
        'Province' => $sifaris->province,
        'District' => $sifaris->district,
        'Municipality / Rural municipality' => $sifaris->municipality,
        'Ward no.' => $sifaris->ward_no,
        'Mobile' => $sifaris->mobile,
        'Email' => $sifaris->email,
        'Account' => $sifaris->user->name.' ('.$sifaris->user->email.')',
    ];
    $input = 'mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon np';
    $canApprove = auth()->user()->can('sifaris.approve');
    $canEdit = auth()->user()->can('sifaris.edit');
    // Pending / disapproved: the letter fields go with Approve. Approved: they can be changed on their own.
    $letterForm = match (true) {
        ! $sifaris->isApproved() && $canApprove => ['action' => route('dashboard.sifaris.approve', $sifaris), 'method' => 'POST', 'button' => '✔ Approve & issue letter', 'class' => 'bg-green-600 hover:bg-green-700'],
        $sifaris->isApproved() && $canEdit => ['action' => route('dashboard.sifaris.letter.update', $sifaris), 'method' => 'PUT', 'button' => 'Save letter details', 'class' => 'bg-navy hover:bg-navy-600'],
        default => null,
    };
@endphp
<x-layouts.dashboard :title="$sifaris->applicant_name">
    <div class="mx-auto max-w-6xl">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl font-bold text-navy">📜 Sifaris Request <span class="font-mono text-base text-gray-400">{{ $sifaris->reference() }}</span></h2>
            <a href="{{ route('dashboard.sifaris.'.$sifaris->status) }}" class="text-sm font-semibold text-maroon hover:underline">← Back to list</a>
        </div>

        @if (session('dashboard-status'))
        <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('dashboard-status') }}</div>
        @endif
        @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-5">
            <div class="space-y-5 lg:col-span-2">
                {{-- Status + actions --}}
                <div class="card">
                    <div class="flex items-start gap-4">
                        <img src="{{ $sifaris->photo_url }}" alt="" class="h-28 w-[5.5rem] shrink-0 rounded-md border border-gray-200 object-cover">
                        <div class="min-w-0">
                            <h3 class="np text-lg font-extrabold text-navy">{{ $sifaris->applicant_name }}</h3>
                            <span class="mt-1 inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $sifaris->statusClasses() }}">{{ $sifaris->statusLabel() }}</span>
                            <p class="mt-2 text-xs text-gray-500">Applied {{ $sifaris->applied_at->format('d M, Y') }}</p>
                            @if ($sifaris->approved_at)<p class="text-xs text-gray-500">Approved {{ $sifaris->approved_at->format('d M, Y') }}</p>@endif
                            @if ($sifaris->reviewer)<p class="text-xs text-gray-400">Reviewed by {{ $sifaris->reviewer->name }}</p>@endif
                        </div>
                    </div>
                    @if ($sifaris->isRejected() && $sifaris->rejection_reason)
                    <p class="mt-3 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700"><strong>Reason:</strong> {{ $sifaris->rejection_reason }}</p>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($sifaris->isApproved())
                        <a href="{{ route('dashboard.sifaris.letter', $sifaris) }}" class="rounded-md bg-maroon px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-700">⬇ Download letter</a>
                        @endif
                        @if ($canApprove && ! $sifaris->isRejected())
                        <button type="button" data-reject="{{ route('dashboard.sifaris.reject', $sifaris) }}" data-name="{{ $sifaris->applicant_name }}" class="rounded-md bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">✖ Disapprove</button>
                        @endif
                        @if ($canEdit)
                        <a href="{{ route('dashboard.sifaris.edit', $sifaris) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-navy hover:bg-gray-50">Edit details</a>
                        @endif
                        @can('sifaris.delete')
                        <form method="POST" action="{{ route('dashboard.sifaris.destroy', $sifaris) }}" onsubmit="return confirm('Delete this sifaris request permanently? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-md border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">Delete</button>
                        </form>
                        @endcan
                    </div>
                </div>

                {{-- Letter number, dispatch number and date (printed at the top of the letter) --}}
                @if ($letterForm)
                <form method="POST" action="{{ $letterForm['action'] }}" class="card space-y-3">
                    @csrf
                    @if ($letterForm['method'] === 'PUT') @method('PUT') @endif
                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">Letter details</h3>
                    <div>
                        <label for="letter_number" class="text-sm font-semibold text-gray-700">पत्र संख्या (letter no.)</label>
                        <input type="text" id="letter_number" name="letter_number" value="{{ old('letter_number', $sifaris->letter_number) }}" maxlength="50" placeholder="जस्तै: २०८३/०८४" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="dispatch_number" class="text-sm font-semibold text-gray-700">चलानी नं. (dispatch no.)</label>
                        <input type="text" id="dispatch_number" name="dispatch_number" value="{{ old('dispatch_number', $sifaris->dispatch_number) }}" maxlength="50" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="letter_date" class="text-sm font-semibold text-gray-700">मिति (date)</label>
                        <input type="text" id="letter_date" name="letter_date" value="{{ old('letter_date', $sifaris->letter_date) }}" maxlength="50" placeholder="जस्तै: २०८३/०६/१४" class="{{ $input }}">
                        @unless ($sifaris->isApproved())
                        <p class="mt-1 text-xs text-gray-500">Leave blank to use the approval date automatically.</p>
                        @endunless
                    </div>
                    <button type="submit" class="w-full rounded-md px-4 py-2.5 text-sm font-semibold text-white {{ $letterForm['class'] }}">{{ $letterForm['button'] }}</button>
                </form>
                @endif

                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-gray-500">Applicant details</h3>
                    <dl class="space-y-3">
                        @foreach ($details as $label => $value)
                        <div class="min-w-0">
                            <dt class="text-xs font-semibold uppercase text-gray-400">{{ $label }}</dt>
                            <dd class="np mt-0.5 break-words text-sm text-gray-800">{{ $value ?: '—' }}</dd>
                        </div>
                        @endforeach
                    </dl>
                </div>
            </div>

            {{-- The letter as the applicant will get it --}}
            <div class="lg:col-span-3">
                <div class="rounded-lg bg-gradient-to-br from-navy-50 to-white p-3 sm:p-6">
                    @include('dashboard.sifaris._letter')
                </div>
            </div>
        </div>
    </div>

    @include('dashboard.membership._reject-dialog')
</x-layouts.dashboard>

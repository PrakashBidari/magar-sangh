@php
    $title = $committee->name_en ?: $committee->name_np;
    // The first sub type tab is open when the page loads; without sub types every member is shown.
    $initial = $subTypes->first()?->id;
    $inTab = fn ($members) => $initial ? $members->where('committee_sub_type_id', $initial) : $members;
@endphp
<x-layouts.app :title="$title">
    <section class="committee-page mx-auto max-w-7xl px-4 py-14">
        <a href="{{ route('about.committee') }}" class="text-sm font-semibold text-maroon hover:underline">← All committees</a>

        {{-- Committee name, centred --}}
        <div class="mb-8 mt-4 text-center">
            <h1 class="section-title-np">{{ $committee->name_np }}</h1>
            @if ($committee->name_en)
            <p class="section-title-en mt-1">{{ $committee->name_en }}</p>
            @endif
        </div>

        {{-- One tab per sub type; each shows only that sub type's members --}}
        @if ($subTypes->isNotEmpty())
        <div class="mb-10 flex flex-wrap justify-center gap-2" role="tablist">
            @foreach ($subTypes as $sub)
            @php $active = $sub->id === $initial; @endphp
            <button type="button" role="tab" data-sub-tab="{{ $sub->id }}" aria-selected="{{ $active ? 'true' : 'false' }}"
                class="committee-sub-tab np inline-flex h-9 items-center justify-center rounded-full border border-maroon px-4 pt-0.5 text-sm font-semibold leading-none transition {{ $active ? 'bg-maroon text-white' : 'text-maroon hover:bg-gray-100' }}">
                {{ $sub->name_np }}
            </button>
            @endforeach
        </div>
        @endif

        {{-- Current members --}}
        <div data-member-block>
            <div class="grid grid-cols-2 gap-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                @foreach ($present as $member)
                    @include('about._committee-member', ['member' => $member, 'gallery' => 'present', 'initial' => $initial])
                @endforeach
            </div>
            <p data-empty class="np py-8 text-center text-gray-500 {{ $inTab($present)->isEmpty() ? '' : 'hidden' }}">हाल कुनै सदस्य छैनन्।</p>
        </div>

        {{-- Past members --}}
        @if ($past->isNotEmpty())
        <div data-member-block data-hide-when-empty class="mt-16 {{ $inTab($past)->isEmpty() ? 'hidden' : '' }}">
            <div class="mb-6 border-b border-gray-200 pb-3 text-center">
                <h2 class="np text-xl font-bold text-maroon">पूर्व सदस्यहरू</h2>
                <p class="text-sm font-semibold uppercase tracking-wide text-navy">Past Members</p>
            </div>
            <div class="grid grid-cols-2 gap-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                @foreach ($past as $member)
                    @include('about._committee-member', ['member' => $member, 'gallery' => 'past', 'initial' => $initial])
                @endforeach
            </div>
        </div>
        @endif

        @if ($otherCommittees->isNotEmpty())
        <div class="mt-16 border-t border-gray-200 pt-8 text-center">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Other committees</p>
            <div class="mt-3 flex flex-wrap justify-center gap-2">
                @foreach ($otherCommittees as $other)
                <a href="{{ route('about.committee.show', $other) }}" class="np inline-flex h-9 items-center rounded-full border border-navy px-4 pt-0.5 text-sm font-semibold text-navy transition hover:bg-navy hover:text-white">{{ $other->name_np }}</a>
                @endforeach
            </div>
        </div>
        @endif
    </section>
</x-layouts.app>

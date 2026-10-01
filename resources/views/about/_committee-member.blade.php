{{-- One member card; data-sub lets the sub type tabs filter it. --}}
<div class="card text-center {{ ($initial ?? null) && $member->committee_sub_type_id !== $initial ? 'hidden' : '' }}" data-sub="{{ $member->committee_sub_type_id ?? 'none' }}">
    <img src="{{ $member->photo_url }}" alt="{{ $member->name }}" class="mx-auto h-20 w-20 rounded-full object-cover shadow glightbox-img" data-gallery="committee-{{ $gallery }}">
    <div class="np mt-2 text-sm font-bold text-navy">{{ $member->name }}</div>
    <div class="np text-xs text-maroon">{{ $member->position_np }}</div>
    @if (! $member->is_current && $member->term_label)
    <div class="np mt-0.5 text-xs text-gray-500">{{ $member->term_label }}</div>
    @endif
</div>

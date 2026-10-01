<?php

namespace App\Http\Controllers;

use App\Models\CommitteeType;
use App\Models\Setting;

class AboutController extends Controller
{
    public function index()
    {
        $settings = Setting::current();

        return view('about.index', compact('settings'));
    }

    /** Pick a committee: one card per committee that has members. */
    public function committee()
    {
        $committees = CommitteeType::query()
            ->whereHas('members')
            ->orderBy('sort_order')->orderBy('id')
            ->get();

        return view('about.committee', compact('committees'));
    }

    /** One committee: a tab per sub type (plus "All"), current members first and past members below. */
    public function committeeShow(CommitteeType $committeeType)
    {
        $members = $committeeType->members()->with('committeeSubType')->orderBy('sort_order')->orderBy('id')->get();

        // Only sub types that have someone in them get a tab.
        $subTypes = $committeeType->subTypes()->get()
            ->filter(fn ($sub) => $members->contains('committee_sub_type_id', $sub->id))
            ->values();

        $otherCommittees = CommitteeType::query()->whereKeyNot($committeeType->id)->whereHas('members')
            ->orderBy('sort_order')->orderBy('id')->get();

        return view('about.committee-show', [
            'committee' => $committeeType,
            'subTypes' => $subTypes,
            'present' => $members->where('is_current', true)->values(),
            'past' => $members->where('is_current', false)->values(),
            'otherCommittees' => $otherCommittees,
        ]);
    }

    public function constitution()
    {
        $settings = Setting::current();

        return view('about.constitution', compact('settings'));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteeMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'committee_type_id', 'committee_sub_type_id', 'name', 'phone', 'photo_url', 'position_np', 'position_en',
        'term_label', 'term_start', 'term_end',
        'is_past_president', 'is_current', 'show_on_homepage', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'term_start' => 'date',
            'term_end' => 'date',
            'is_past_president' => 'boolean',
            'is_current' => 'boolean',
            'show_on_homepage' => 'boolean',
        ];
    }

    public function committeeType(): BelongsTo
    {
        return $this->belongsTo(CommitteeType::class);
    }

    public function committeeSubType(): BelongsTo
    {
        return $this->belongsTo(CommitteeSubType::class);
    }
}

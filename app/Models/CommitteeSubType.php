<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A part of a committee, e.g. "Kathmandu" under "District Committee". */
class CommitteeSubType extends Model
{
    use HasFactory;

    protected $fillable = ['committee_type_id', 'name_np', 'name_en', 'sort_order'];

    public function committeeType(): BelongsTo
    {
        return $this->belongsTo(CommitteeType::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(CommitteeMember::class);
    }

    public function label(): string
    {
        return $this->name_en ? "{$this->name_np} ({$this->name_en})" : $this->name_np;
    }

    /** Dashboard select options: id => "Sub type (English)", ordered by committee. */
    public static function options(): array
    {
        return static::ordered()->mapWithKeys(fn (self $sub) => [$sub->id => $sub->label()])->all();
    }

    /** Which committee each option belongs to (id => committee_type_id), so the form can filter by committee. */
    public static function optionParents(): array
    {
        return static::ordered()->pluck('committee_type_id', 'id')->all();
    }

    private static function ordered()
    {
        return static::query()->orderBy('committee_type_id')->orderBy('sort_order')->orderBy('id')->get();
    }
}

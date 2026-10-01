<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommitteeType extends Model
{
    use HasFactory;

    protected $fillable = ['name_np', 'name_en', 'sort_order'];

    public function members(): HasMany
    {
        return $this->hasMany(CommitteeMember::class);
    }

    public function subTypes(): HasMany
    {
        return $this->hasMany(CommitteeSubType::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Dashboard select options: id => "Nepali (English)". */
    public static function options(): array
    {
        return static::query()->orderBy('sort_order')->orderBy('id')->get()
            ->mapWithKeys(fn (self $type) => [$type->id => $type->name_en ? "{$type->name_np} ({$type->name_en})" : $type->name_np])
            ->all();
    }
}
